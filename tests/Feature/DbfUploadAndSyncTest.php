<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\UnitKerja;
use App\Models\UnmatchedNip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DbfUploadAndSyncTest extends TestCase
{
    use RefreshDatabase;

    private ?string $originalManifest = null;

    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());

        $manifestPath = storage_path('app/simgaji/manifest.json');
        if (file_exists($manifestPath)) {
            $this->originalManifest = file_get_contents($manifestPath);
        }
    }

    protected function tearDown(): void
    {
        $manifestPath = storage_path('app/simgaji/manifest.json');
        if ($this->originalManifest !== null) {
            $currentFiles = json_decode(@file_get_contents($manifestPath) ?: '[]', true) ?: [];
            $originalFiles = json_decode($this->originalManifest, true) ?: [];
            $originalPaths = array_column($originalFiles, 'path');

            foreach ($currentFiles as $f) {
                $path = $f['path'] ?? '';
                if ($path && ! in_array($path, $originalPaths) && str_starts_with($path, storage_path('app/simgaji')) && file_exists($path)) {
                    @unlink($path);
                }
            }

            file_put_contents($manifestPath, $this->originalManifest);
        }

        foreach ($this->tempFiles as $tmp) {
            if (file_exists($tmp)) {
                @unlink($tmp);
            }
        }

        parent::tearDown();
    }

    /**
     * Bangun berkas dBASE III (.DBF) valid secara minimal untuk kebutuhan pengujian.
     *
     * @param  array<int, array{name: string, type: string, length: int}>  $columns
     * @param  array<int, array<string, string|int|float>>  $rows
     */
    private function createTestDbf(string $filename, array $columns, array $rows): UploadedFile
    {
        $numRecords = count($rows);
        $headerLength = 32 + (count($columns) * 32) + 1;
        $recordLength = 1;
        foreach ($columns as $col) {
            $recordLength += $col['length'];
        }

        $binary = pack('CCCCVvv', 0x03, 126, 11, 23, $numRecords, $headerLength, $recordLength);
        $binary .= str_repeat("\x00", 20);

        foreach ($columns as $col) {
            $nameBin = str_pad(substr($col['name'], 0, 11), 11, "\x00");
            $binary .= $nameBin.$col['type'].str_repeat("\x00", 4).pack('CC', $col['length'], 0).str_repeat("\x00", 14);
        }

        $binary .= "\x0D";

        foreach ($rows as $row) {
            $binary .= ' ';
            foreach ($columns as $col) {
                $val = (string) ($row[$col['name']] ?? '');
                if ($col['type'] === 'N') {
                    $binary .= str_pad(substr($val, 0, $col['length']), $col['length'], ' ', STR_PAD_LEFT);
                } else {
                    $binary .= str_pad(substr($val, 0, $col['length']), $col['length'], ' ', STR_PAD_RIGHT);
                }
            }
        }

        $binary .= "\x1A";

        $tmpPath = sys_get_temp_dir().'/'.uniqid('test_dbf_').'_'.$filename;
        file_put_contents($tmpPath, $binary);
        $this->tempFiles[] = $tmpPath;

        return new UploadedFile($tmpPath, $filename, 'application/octet-stream', null, true);
    }

    public function test_gaji_dbf_import_supports_spaces_and_parentheses_and_bulk_upserts(): void
    {
        $unitKerja = UnitKerja::create(['skpd' => 'BADAN PENGELOLAAN KEUANGAN DAN ASET DAERAH']);
        $pegawai1 = Pegawai::create([
            'nip' => '198501012010011001',
            'nama' => 'BUDI SANTOSO',
            'status_pegawai' => 'PNS',
            'jenis_pegawai' => 'TEKNIS',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $columns = [
            ['name' => 'NIP', 'type' => 'C', 'length' => 18],
            ['name' => 'NAMA', 'type' => 'C', 'length' => 30],
            ['name' => 'KDSTAPEG', 'type' => 'C', 'length' => 2],
            ['name' => 'TGLGAJI', 'type' => 'C', 'length' => 10],
            ['name' => 'GAPOK', 'type' => 'N', 'length' => 10],
            ['name' => 'PPAJAK', 'type' => 'N', 'length' => 10],
            ['name' => 'PIWP', 'type' => 'N', 'length' => 10],
            ['name' => 'POTONGAN', 'type' => 'N', 'length' => 10],
            ['name' => 'BERSIH', 'type' => 'N', 'length' => 10],
        ];

        $file = $this->createTestDbf('GAJI_2026-12-011600 (1).DBF', $columns, [
            [
                'NIP' => '198501012010011001',
                'NAMA' => 'BUDI SANTOSO',
                'KDSTAPEG' => '4',
                'TGLGAJI' => '2026-12-01',
                'GAPOK' => 5000000,
                'PPAJAK' => 100000,
                'PIWP' => 400000,
                'POTONGAN' => 600000,
                'BERSIH' => 5500000,
            ],
            [
                'NIP' => '199901012024211099',
                'NAMA' => 'PEGAWAI BELUM TERDAFTAR',
                'KDSTAPEG' => '12',
                'TGLGAJI' => '2026-12-01',
                'GAPOK' => 3200000,
                'PPAJAK' => 0,
                'PIWP' => 256000,
                'POTONGAN' => 256000,
                'BERSIH' => 3500000,
            ],
        ]);

        $response = $this->post('/realisasi/gaji/import', [
            'file' => $file,
            'periode_import' => 'Desember 2026',
            'jenis_gaji' => RealisasiGaji::JENIS_GAJI_INDUK,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('realisasi_gajis', [
            'pegawai_id' => $pegawai1->id,
            'periode' => 'Desember 2026',
            'jenis_gaji' => RealisasiGaji::JENIS_GAJI_INDUK,
            'gaji_pokok' => 5000000,
            'pajak' => 100000,
            'iwp' => 400000,
            'potongan_lain' => 100000,
            'gaji_bersih' => 5500000,
        ]);

        $this->assertDatabaseHas('unmatched_nips', [
            'nip' => '199901012024211099',
            'status_pegawai' => 'PPPK',
            'periode' => 'Desember 2026',
        ]);

        // Re-import dengan nilai terbaru harus memperbarui baris, bukan menduplikasi
        $updatedFile = $this->createTestDbf('GAJI_2026-12-011600 (1).DBF', $columns, [
            [
                'NIP' => '198501012010011001',
                'NAMA' => 'BUDI SANTOSO',
                'KDSTAPEG' => '4',
                'TGLGAJI' => '2026-12-01',
                'GAPOK' => 5200000,
                'PPAJAK' => 120000,
                'PIWP' => 420000,
                'POTONGAN' => 600000,
                'BERSIH' => 5800000,
            ],
        ]);

        $this->post('/realisasi/gaji/import', [
            'file' => $updatedFile,
            'periode_import' => 'Desember 2026',
            'jenis_gaji' => RealisasiGaji::JENIS_GAJI_INDUK,
        ])->assertRedirect();

        $this->assertDatabaseCount('realisasi_gajis', 1);
        $this->assertDatabaseHas('realisasi_gajis', [
            'pegawai_id' => $pegawai1->id,
            'gaji_pokok' => 5200000,
            'gaji_bersih' => 5800000,
        ]);
        $this->assertSame(0, UnmatchedNip::where('periode', 'Desember 2026')->count());
    }

    public function test_simgaji_dbf_upload_rejects_gaji_realisasi_file_with_clear_message(): void
    {
        $columns = [
            ['name' => 'NIP', 'type' => 'C', 'length' => 18],
            ['name' => 'TGLGAJI', 'type' => 'C', 'length' => 10],
            ['name' => 'GAPOK', 'type' => 'N', 'length' => 10],
            ['name' => 'BERSIH', 'type' => 'N', 'length' => 10],
        ];

        $file = $this->createTestDbf('GAJI_2026-12-011600 (1).DBF', $columns, [
            [
                'NIP' => '198501012010011001',
                'TGLGAJI' => '2026-12-01',
                'GAPOK' => 5000000,
                'BERSIH' => 5500000,
            ],
        ]);

        $response = $this->postJson(route('master.simgaji_dbf.upload'), [
            'file_dbf' => $file,
            'jenis_dbf' => 'auto',
            'auto_sync' => '1',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
        $this->assertStringContainsString('/realisasi/gaji', (string) $response->json('message'));
    }

    public function test_simgaji_dbf_upload_mst_pgw_with_parentheses_and_auto_sync_updates_pegawai(): void
    {
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS PENDIDIKAN']);
        $pegawai = Pegawai::create([
            'nip' => '198602022011011002',
            'nama' => 'SITI AMINAH',
            'status_pegawai' => 'PNS',
            'jenis_pegawai' => 'TEKNIS',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $columns = [
            ['name' => 'NIP', 'type' => 'C', 'length' => 18],
            ['name' => 'NAMA', 'type' => 'C', 'length' => 30],
            ['name' => 'NOKTP', 'type' => 'C', 'length' => 20],
            ['name' => 'NOREK', 'type' => 'C', 'length' => 25],
            ['name' => 'induk_bank', 'type' => 'C', 'length' => 4],
            ['name' => 'NPWP', 'type' => 'C', 'length' => 20],
            ['name' => 'NOKARPEG', 'type' => 'C', 'length' => 20],
        ];

        $file = $this->createTestDbf('MST_PGW_2026-12-011600 (2).DBF', $columns, [
            [
                'NIP' => '198602022011011002',
                'NAMA' => 'SITI AMINAH',
                'NOKTP' => '6371010202860001',
                'NOREK' => '0010301012345',
                'induk_bank' => '122',
                'NPWP' => '123456789012345',
                'NOKARPEG' => 'P 414436',
            ],
        ]);

        $response = $this->postJson(route('master.simgaji_dbf.upload'), [
            'file_dbf' => $file,
            'jenis_dbf' => 'auto',
            'auto_sync' => '1',
            'keterangan' => 'Test Upload MST_PGW Desember 2026',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'type' => 'mst_pgw',
                'records' => 1,
                'auto_synced' => true,
            ]);

        $pegawai->refresh();
        $this->assertSame('6371010202860001', $pegawai->nik);
        $this->assertSame('0010301012345', $pegawai->no_rekening);
        $this->assertSame('122', $pegawai->nama_bank);
        $this->assertSame('123456789012345', $pegawai->npwp);
        $this->assertSame('P 414436', $pegawai->no_karpeg);
    }

    public function test_realisasi_gaji_import_rejects_mst_pgw_dbf_without_bersih_column(): void
    {
        $columns = [
            ['name' => 'NIP', 'type' => 'C', 'length' => 18],
            ['name' => 'NAMA', 'type' => 'C', 'length' => 30],
            ['name' => 'GAPOK', 'type' => 'N', 'length' => 10],
        ];

        $file = $this->createTestDbf('MST_PGW_2026-12-011600 (2).DBF', $columns, [
            [
                'NIP' => '198602022011011002',
                'NAMA' => 'SITI AMINAH',
                'GAPOK' => 5000000,
            ],
        ]);

        $response = $this->post('/realisasi/gaji/import', [
            'file' => $file,
            'periode_import' => 'Desember 2026',
            'jenis_gaji' => RealisasiGaji::JENIS_GAJI_INDUK,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
