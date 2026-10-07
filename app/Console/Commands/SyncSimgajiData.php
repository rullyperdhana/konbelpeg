<?php

namespace App\Console\Commands;

use App\Http\Controllers\RekonsiliasiSimgajiController;
use App\Models\Pegawai;
use App\Models\SimgajiKeluarga;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use XBase\TableReader;

class SyncSimgajiData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simgaji:sync {--type=all : all, master, keluarga} {--mst= : Path file MST_PGW.DBF} {--kel= : Path file KEL.DBF}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data Master Pegawai (NIK, No Rekening, NPWP) dan Data Keluarga dari file DBF SIMGAJI';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type') ?: 'all';

        if (in_array($type, ['all', 'master'])) {
            $this->syncMasterPegawai();
        }

        if (in_array($type, ['all', 'keluarga'])) {
            $this->syncKeluarga();
        }

        $this->info('Sinkronisasi data SIMGAJI Taspen selesai!');

        return Command::SUCCESS;
    }

    /**
     * Sinkronisasi data identitas finansial pegawai (NIK, No Rekening, NPWP, Bank, No Karpeg).
     */
    public function syncMasterPegawai(): void
    {
        $filePath = $this->resolveDbfPath('mst_pgw', $this->option('mst'), 'MST_PGW');

        if (! $filePath || ! file_exists($filePath)) {
            $this->warn('File MST_PGW.DBF tidak ditemukan, melewati sinkronisasi master pegawai.');

            return;
        }

        $this->info("Memulai sinkronisasi Master Pegawai dari: {$filePath}");

        try {
            $table = new TableReader($filePath, ['encoding' => 'cp850']);
            $totalRecords = $table->getRecordCount();
            $this->info("Total record MST_PGW: {$totalRecords}");

            $this->updateProgress([
                'status' => 'syncing',
                'type' => 'master',
                'current' => 0,
                'total' => $totalRecords,
                'remaining' => $totalRecords,
                'percent' => 0,
                'message' => 'Mempersiapkan data Master Pegawai...',
            ]);

            $processed = 0;
            $updatedCount = 0;
            $updates = [];

            while ($record = $table->nextRecord()) {
                $processed++;
                $nip = trim((string) $record->get('nip'));
                if (empty($nip)) {
                    continue;
                }

                $noktp = trim((string) $record->get('noktp'));
                $norek = trim((string) $record->get('norek'));
                $indukBank = trim((string) $record->get('induk_bank'));
                $npwp = trim((string) $record->get('npwp'));
                $nokarpeg = trim((string) $record->get('nokarpeg'));

                // Hanya simpan jika ada data relevan
                if (! empty($noktp) || ! empty($norek) || ! empty($npwp) || ! empty($indukBank) || ! empty($nokarpeg)) {
                    $updates[$nip] = [
                        'nik' => $noktp ?: null,
                        'no_rekening' => $norek ?: null,
                        'nama_bank' => $indukBank ?: null,
                        'npwp' => $npwp ?: null,
                        'no_karpeg' => $nokarpeg ?: null,
                    ];
                }

                // Batch update per 500 NIP
                if (count($updates) >= 500) {
                    $updatedCount += $this->batchUpdatePegawai($updates);
                    $updates = [];
                    $this->output->write('.');

                    $this->updateProgress([
                        'status' => 'syncing',
                        'type' => 'master',
                        'current' => $processed,
                        'total' => $totalRecords,
                        'remaining' => max(0, $totalRecords - $processed),
                        'percent' => round(($processed / max(1, $totalRecords)) * 100, 1),
                        'message' => 'Memproses data pegawai: '.number_format($processed, 0, ',', '.').' / '.number_format($totalRecords, 0, ',', '.'),
                    ]);
                }
            }

            if (! empty($updates)) {
                $updatedCount += $this->batchUpdatePegawai($updates);
            }

            $this->updateProgress([
                'status' => 'done',
                'type' => 'master',
                'current' => $processed,
                'total' => $totalRecords,
                'remaining' => 0,
                'percent' => 100,
                'message' => "Berhasil memperbarui data finansial & identitas untuk {$updatedCount} pegawai.",
            ]);

            $this->newLine();
            $this->info("Berhasil memperbarui data finansial & identitas untuk {$updatedCount} pegawai.");
        } catch (\Exception $e) {
            $this->updateProgress([
                'status' => 'error',
                'type' => 'master',
                'message' => $e->getMessage(),
            ]);
            $this->error('Gagal memproses file MST_PGW.DBF: '.$e->getMessage());
        }
    }

    /**
     * Sinkronisasi data anggota keluarga & tanggungan dari file KEL_*.DBF.
     */
    public function syncKeluarga(): void
    {
        $filePath = $this->resolveDbfPath('kel', $this->option('kel'), 'KEL_');

        if (! $filePath || ! file_exists($filePath)) {
            $this->warn('File KEL_*.DBF tidak ditemukan, melewati sinkronisasi keluarga.');

            return;
        }

        $this->info("Memulai sinkronisasi Riwayat Keluarga dari: {$filePath}");

        try {
            $table = new TableReader($filePath, ['encoding' => 'cp850']);
            $totalRecords = $table->getRecordCount();
            $this->info("Total record KEL: {$totalRecords}");

            $this->updateProgress([
                'status' => 'syncing',
                'type' => 'keluarga',
                'current' => 0,
                'total' => $totalRecords,
                'remaining' => $totalRecords,
                'percent' => 0,
                'message' => 'Mengosongkan tabel lama & mempersiapkan data baru...',
            ]);

            // Kosongkan tabel simgaji_keluargas sebelum memuat data baru
            SimgajiKeluarga::truncate();

            $batch = [];
            $inserted = 0;
            $now = Carbon::now();

            while ($record = $table->nextRecord()) {
                $nip = trim((string) $record->get('nip'));
                $nmkel = trim((string) $record->get('nmkel'));
                if (empty($nip) || empty($nmkel)) {
                    continue;
                }

                $kdhubkel = trim((string) $record->get('kdhubkel'));
                $kdjenkel = trim((string) $record->get('kdjenkel'));
                $kdtunjang = trim((string) $record->get('kdtunjang'));
                $kdstawin = trim((string) $record->get('kdstawin'));
                $nipsuamiis = trim((string) $record->get('nipsuamiis'));
                $pekerjaan = trim((string) $record->get('pekerjaan'));
                $noaktalahi = trim((string) $record->get('noaktalahi'));
                $nosks = trim((string) $record->get('nosks'));

                $tgllhr = $this->parseDate((string) $record->get('tgllhr'));
                $tglsks = $this->parseDate((string) $record->get('tglsks'));
                $tglnikah = $this->parseDate((string) $record->get('tglnikah'));
                $tglcerai = $this->parseDate((string) $record->get('tglcerai'));
                $tglwafat = $this->parseDate((string) $record->get('tglwafat'));

                $hubungan = $this->mapHubungan($kdhubkel);
                $jenisKelamin = ($kdjenkel === '2') ? 'Perempuan' : (($kdjenkel === '1') ? 'Laki-laki' : '-');
                $statusTunjangan = ($kdtunjang === '2') ? 'Tertunjang' : (($kdtunjang === '1') ? 'Tidak Tertunjang' : '-');

                $batch[] = [
                    'nip' => $nip,
                    'nmkel' => $nmkel,
                    'kdhubkel' => $kdhubkel ?: null,
                    'hubungan' => $hubungan,
                    'kdjenkel' => $kdjenkel ?: null,
                    'jenis_kelamin' => $jenisKelamin,
                    'tgllhr' => $tgllhr,
                    'kdtunjang' => $kdtunjang ?: null,
                    'status_tunjangan' => $statusTunjangan,
                    'kdstawin' => $kdstawin ?: null,
                    'nipsuamiis' => $nipsuamiis ?: null,
                    'pekerjaan' => $pekerjaan ?: null,
                    'noaktalahi' => $noaktalahi ?: null,
                    'nosks' => $nosks ?: null,
                    'tglsks' => $tglsks,
                    'tglnikah' => $tglnikah,
                    'tglcerai' => $tglcerai,
                    'tglwafat' => $tglwafat,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batch) >= 1000) {
                    DB::table('simgaji_keluargas')->insert($batch);
                    $inserted += count($batch);
                    $batch = [];
                    $this->output->write('#');

                    $this->updateProgress([
                        'status' => 'syncing',
                        'type' => 'keluarga',
                        'current' => $inserted,
                        'total' => $totalRecords,
                        'remaining' => max(0, $totalRecords - $inserted),
                        'percent' => round(($inserted / max(1, $totalRecords)) * 100, 1),
                        'message' => 'Menyinkronkan record '.number_format($inserted, 0, ',', '.').' dari '.number_format($totalRecords, 0, ',', '.'),
                    ]);
                }
            }

            if (! empty($batch)) {
                DB::table('simgaji_keluargas')->insert($batch);
                $inserted += count($batch);
            }

            $this->updateProgress([
                'status' => 'done',
                'type' => 'keluarga',
                'current' => $inserted,
                'total' => $totalRecords,
                'remaining' => 0,
                'percent' => 100,
                'message' => "Berhasil menyimpan {$inserted} data anggota keluarga ke tabel simgaji_keluargas.",
            ]);

            $this->newLine();
            $this->info("Berhasil menyimpan {$inserted} data anggota keluarga ke tabel simgaji_keluargas.");
        } catch (\Exception $e) {
            $this->updateProgress([
                'status' => 'error',
                'type' => 'keluarga',
                'message' => $e->getMessage(),
            ]);
            $this->error('Gagal memproses file KEL.DBF: '.$e->getMessage());
        }
    }

    /**
     * Update data pegawai secara efisien.
     */
    private function batchUpdatePegawai(array $updates): int
    {
        $nips = array_keys($updates);
        $existing = Pegawai::whereIn('nip', $nips)->get()->keyBy('nip');
        $count = 0;

        foreach ($updates as $nip => $data) {
            if (isset($existing[$nip])) {
                $pegawai = $existing[$nip];
                $pegawai->update(array_filter($data, fn ($val) => ! is_null($val)));
                $count++;
            }
        }

        return $count;
    }

    /**
     * Resolusi path file DBF dari opsi atau manifest atau direktori root.
     */
    private function resolveDbfPath(string $type, ?string $customPath, string $prefix): ?string
    {
        if ($customPath && file_exists($customPath)) {
            return $customPath;
        }

        $resolveActual = function ($f) {
            $path = is_array($f) ? ($f['path'] ?? '') : $f;
            $storedName = is_array($f) ? ($f['stored_name'] ?? '') : '';
            $filename = is_array($f) ? ($f['filename'] ?? '') : '';

            if (! empty($path) && file_exists($path)) {
                return $path;
            }
            if (! empty($storedName) && file_exists(storage_path('app/simgaji/'.$storedName))) {
                return storage_path('app/simgaji/'.$storedName);
            }
            if (! empty($filename) && file_exists(storage_path('app/simgaji/'.$filename))) {
                return storage_path('app/simgaji/'.$filename);
            }
            if (! empty($path) && file_exists(storage_path('app/simgaji/'.basename($path)))) {
                return storage_path('app/simgaji/'.basename($path));
            }
            if (! empty($filename) && file_exists(base_path($filename))) {
                return base_path($filename);
            }
            if (! empty($path) && file_exists(base_path(basename($path)))) {
                return base_path(basename($path));
            }

            return null;
        };

        // 0. Cek file aktif dari RekonsiliasiSimgajiController
        $activeFile = app(RekonsiliasiSimgajiController::class)->getActiveDbfFile($type);
        if ($activeFile && ! empty($activeFile['path']) && file_exists($activeFile['path'])) {
            return $activeFile['path'];
        }

        // 1. Cek manifest.json
        $manifestPath = storage_path('app/simgaji/manifest.json');
        if (file_exists($manifestPath)) {
            $files = json_decode(file_get_contents($manifestPath), true) ?: [];
            foreach ($files as $f) {
                $actual = $resolveActual($f);
                if (($f['type'] ?? '') === $type && ! empty($f['is_active']) && $actual && file_exists($actual)) {
                    return $actual;
                }
            }
            foreach ($files as $f) {
                $actual = $resolveActual($f);
                if (($f['type'] ?? '') === $type && $actual && file_exists($actual)) {
                    return $actual;
                }
            }
        }

        // 2. Cek di direktori storage/app/simgaji
        $storageFiles = array_merge(
            glob(storage_path("app/simgaji/*{$prefix}*.DBF")) ?: [],
            glob(storage_path("app/simgaji/*{$prefix}*.dbf")) ?: [],
            glob(storage_path('app/simgaji/*'.strtolower($prefix).'*.dbf')) ?: []
        );
        if (! empty($storageFiles)) {
            return $storageFiles[0];
        }

        // 3. Cek di root directory project
        $rootFiles = array_merge(
            glob(base_path("{$prefix}*.DBF")) ?: [],
            glob(base_path(strtolower($prefix).'*.dbf')) ?: [],
            glob(base_path("*{$prefix}*.DBF")) ?: []
        );
        if (! empty($rootFiles)) {
            return $rootFiles[0];
        }

        return null;
    }

    /**
     * Map kode hubungan keluarga SIMGAJI ke label yang mudah dipahami.
     */
    private function mapHubungan(string $code): string
    {
        return match ($code) {
            '00' => 'Pegawai (Diri Sendiri)',
            '10' => 'Istri / Suami',
            '11' => 'Anak ke-1',
            '12' => 'Anak ke-2',
            '13' => 'Anak ke-3',
            '14' => 'Anak ke-4',
            '15' => 'Anak ke-5',
            '16' => 'Anak ke-6',
            '17' => 'Anak ke-7',
            '18' => 'Anak ke-8',
            '20' => 'Suami / Istri (Pasangan)',
            '21' => 'Anak ke-1 (Pasangan)',
            '22' => 'Anak ke-2 (Pasangan)',
            default => ! empty($code) ? "Hubungan ({$code})" : 'Keluarga',
        };
    }

    /**
     * Parse tanggal dari DBF string secara aman.
     */
    private function parseDate(?string $val): ?string
    {
        if (empty($val) || $val === '0000-00-00' || trim($val) === '') {
            return null;
        }

        try {
            return Carbon::parse(trim($val))->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Simpan status progres sinkronisasi real-time ke file JSON dan cache.
     */
    private function updateProgress(array $data): void
    {
        $dir = storage_path('app/simgaji');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $data['updated_at'] = time();
        @file_put_contents($dir.'/sync_progress.json', json_encode($data, JSON_PRETTY_PRINT));
        try {
            Cache::put('simgaji_sync_progress', $data, 300);
        } catch (\Exception $e) {
            // Abaikan jika cache store sedang sibuk
        }
    }
}
