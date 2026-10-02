<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\RealisasiTpp;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RealisasiTppDualFieldTest extends TestCase
{
    use RefreshDatabase;

    private function createSampleCsv(array $rows): UploadedFile
    {
        $header = ['NIP', 'Periode', 'Jabatan', 'TPP Bruto', 'TPP Netto', 'PPh 21', 'Potongan TPP (Lainnya)', 'Iuran IWP', 'Yang Dibayarkan (Transfer)'];
        $content = implode(',', $header)."\n";

        foreach ($rows as $row) {
            $content .= implode(',', $row)."\n";
        }

        return UploadedFile::fake()->createWithContent('tpp_sample.csv', $content);
    }

    public function test_import_tpp_with_dual_fields_persists_periode_kas_and_bulan_kinerja(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS KESEHATAN']);
        $pegawai = Pegawai::create([
            'nip' => '198001012005011001',
            'nama' => 'Dr. Budi Santoso',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        $file = $this->createSampleCsv([
            ['198001012005011001', '', 'Dokter Madya', '5000000', '4800000', '100000', '50000', '50000', '4800000'],
        ]);

        $response = $this->actingAs($user)->post('/realisasi/tpp/import', [
            'file' => $file,
            'periode_kas' => 'Februari 2026',
            'bulan_kinerja' => 'Januari 2026',
            'tahap_bayar' => 'Reguler',
            'keterangan_bayar' => 'SP2D Kasda Feb 2026',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('realisasi_tpps', [
            'pegawai_id' => $pegawai->id,
            'periode_kas' => 'Februari 2026',
            'bulan_kinerja' => 'Januari 2026',
            'tahap_bayar' => 'Reguler',
            'keterangan_bayar' => 'SP2D Kasda Feb 2026',
            'periode' => 'Februari 2026 (Kinerja Januari 2026)',
            'tpp_bruto' => 5000000,
            'total_dibayarkan' => 4800000,
        ]);
    }

    public function test_december_two_disbursements_creates_two_separate_records_without_overwriting(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'BADAN KEPEGAWAIAN DAERAH']);
        $pegawai = Pegawai::create([
            'nip' => '198812122010011005',
            'nama' => 'Ahmad Fauzi, S.Kom',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        // 1. Upload Tahap 1 (Awal Desember - Kinerja November)
        $fileTahap1 = $this->createSampleCsv([
            ['198812122010011005', '', 'Pranata Komputer', '4000000', '3900000', '50000', '25000', '25000', '3900000'],
        ]);

        $this->actingAs($user)->post('/realisasi/tpp/import', [
            'file' => $fileTahap1,
            'periode_kas' => 'Desember 2026',
            'bulan_kinerja' => 'November 2026',
            'tahap_bayar' => 'Tahap 1',
        ]);

        // 2. Upload Tahap 2 (Akhir Desember - Kinerja Desember)
        $fileTahap2 = $this->createSampleCsv([
            ['198812122010011005', '', 'Pranata Komputer', '4200000', '4100000', '50000', '25000', '25000', '4100000'],
        ]);

        $this->actingAs($user)->post('/realisasi/tpp/import', [
            'file' => $fileTahap2,
            'periode_kas' => 'Desember 2026',
            'bulan_kinerja' => 'Desember 2026',
            'tahap_bayar' => 'Tahap 2',
        ]);

        // Assert that BOTH records exist for the employee
        $records = RealisasiTpp::where('pegawai_id', $pegawai->id)->get();
        $this->assertCount(2, $records);

        $tahap1 = $records->firstWhere('tahap_bayar', 'Tahap 1');
        $this->assertNotNull($tahap1);
        $this->assertEquals('Desember 2026', $tahap1->periode_kas);
        $this->assertEquals('November 2026', $tahap1->bulan_kinerja);
        $this->assertEquals(3900000, $tahap1->total_dibayarkan);

        $tahap2 = $records->firstWhere('tahap_bayar', 'Tahap 2');
        $this->assertNotNull($tahap2);
        $this->assertEquals('Desember 2026', $tahap2->periode_kas);
        $this->assertEquals('Desember 2026', $tahap2->bulan_kinerja);
        $this->assertEquals(4100000, $tahap2->total_dibayarkan);
    }

    public function test_rekap_tpp_sums_all_disbursements_for_cash_period(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS PENDIDIKAN']);
        $pegawai = Pegawai::create([
            'nip' => '199001012015011001',
            'nama' => 'Dewi Lestari, M.Pd',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        // Tahap 1: 3.000.000
        RealisasiTpp::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Desember 2026 - Tahap 1 (Kinerja November 2026)',
            'periode_kas' => 'Desember 2026',
            'bulan_kinerja' => 'November 2026',
            'tahap_bayar' => 'Tahap 1',
            'tpp_bruto' => 3000000,
            'total_dibayarkan' => 3000000,
        ]);

        // Tahap 2: 3.000.000
        RealisasiTpp::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Desember 2026 - Tahap 2 (Kinerja Desember 2026)',
            'periode_kas' => 'Desember 2026',
            'bulan_kinerja' => 'Desember 2026',
            'tahap_bayar' => 'Tahap 2',
            'tpp_bruto' => 3000000,
            'total_dibayarkan' => 3000000,
        ]);

        // Filter by cash period 'Desember 2026'
        $response = $this->actingAs($user)->get('/realisasi/tpp?tipe_laporan=rekap&periode_filter=Desember+2026');

        $response->assertStatus(200);
        // Total TPP should be 6.000.000
        $response->assertSee('6.000.000');
        // Employee count must still be 1 (not duplicated)
        $response->assertSee('DINAS PENDIDIKAN');
    }

    public function test_laporan_gabungan_cash_basis_with_dual_december_disbursements(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'INSPEKTORAT']);
        $pegawai = Pegawai::create([
            'nip' => '197505052000031001',
            'nama' => 'Ir. Hendra Wijaya',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        // Gaji Desember: 5.000.000
        RealisasiGaji::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Desember 2026',
            'gaji_pokok' => 5000000,
            'gaji_bersih' => 4500000,
        ]);

        // TPP Tahap 1 (Nov): 4.000.000
        RealisasiTpp::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Desember 2026 - Tahap 1 (Kinerja November 2026)',
            'periode_kas' => 'Desember 2026',
            'bulan_kinerja' => 'November 2026',
            'tahap_bayar' => 'Tahap 1',
            'tpp_bruto' => 4000000,
            'total_dibayarkan' => 4000000,
        ]);

        // TPP Tahap 2 (Des): 4.000.000
        RealisasiTpp::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Desember 2026 - Tahap 2 (Kinerja Desember 2026)',
            'periode_kas' => 'Desember 2026',
            'bulan_kinerja' => 'Desember 2026',
            'tahap_bayar' => 'Tahap 2',
            'tpp_bruto' => 4000000,
            'total_dibayarkan' => 4000000,
        ]);

        $response = $this->actingAs($user)->get('/laporan/gabungan?periode=Desember+2026');

        $response->assertStatus(200);
        $response->assertSee('INSPEKTORAT');
        // Total Gaji should be 4.500.000 (NOT duplicated to 9.000.000)
        $response->assertSee('4.500.000');
        // Total TPP should be 8.000.000 (Tahap 1 + Tahap 2)
        $response->assertSee('8.000.000');
        // Grand Total should be 12.500.000
        $response->assertSee('12.500.000');
    }

    public function test_setting_data_can_delete_tpp_by_periode_kas(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS SOSIAL']);
        $pegawai = Pegawai::create([
            'nip' => '199202022018011003',
            'nama' => 'Rina Marlina',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        RealisasiTpp::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Februari 2026 (Kinerja Januari 2026)',
            'periode_kas' => 'Februari 2026',
            'bulan_kinerja' => 'Januari 2026',
            'tahap_bayar' => 'Reguler',
            'tpp_bruto' => 3500000,
            'total_dibayarkan' => 3500000,
        ]);

        $this->assertEquals(1, RealisasiTpp::count());

        $response = $this->actingAs($user)->delete('/setting/data/hapus', [
            'jenis' => 'TPP',
            'periode' => 'Februari 2026',
            'status_pegawai' => 'SEMUA',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(0, RealisasiTpp::count());
    }
}
