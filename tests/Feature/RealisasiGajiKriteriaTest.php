<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealisasiGajiKriteriaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_can_store_multiple_salary_criteria_for_same_employee_and_period(): void
    {
        $unitKerja = UnitKerja::create([
            'skpd' => 'Dinas Pendidikan',
        ]);

        $pegawai = Pegawai::create([
            'nip' => '198501012010011001',
            'nama' => 'Budi Santoso',
            'status_pegawai' => 'PNS',
            'jenis_pegawai' => 'TEKNIS',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        // 1. Simpan Gaji Induk
        $gajiInduk = RealisasiGaji::updateOrCreate(
            [
                'pegawai_id' => $pegawai->id,
                'periode' => 'Juni 2026',
                'jenis_gaji' => RealisasiGaji::JENIS_GAJI_INDUK,
            ],
            [
                'gaji_pokok' => 4000000,
                'pajak' => 100000,
                'iwp' => 320000,
                'potongan_lain' => 80000,
                'gaji_bersih' => 3500000,
            ]
        );

        // 2. Simpan Gaji 13 di bulan yang sama
        $gaji13 = RealisasiGaji::updateOrCreate(
            [
                'pegawai_id' => $pegawai->id,
                'periode' => 'Juni 2026',
                'jenis_gaji' => RealisasiGaji::JENIS_GAJI_13,
            ],
            [
                'gaji_pokok' => 4000000,
                'pajak' => 0,
                'iwp' => 0,
                'potongan_lain' => 0,
                'gaji_bersih' => 4000000,
            ]
        );

        $this->assertDatabaseCount('realisasi_gajis', 2);
        $this->assertEquals(RealisasiGaji::JENIS_GAJI_INDUK, $gajiInduk->jenis_gaji);
        $this->assertEquals(RealisasiGaji::JENIS_GAJI_13, $gaji13->jenis_gaji);

        // Employee count in rekapitulasi must be 1, NOT 2!
        $response = $this->get('/realisasi/gaji?tipe_laporan=rekap&periode_filter=Juni+2026');
        $response->assertStatus(200);
        $rekaps = $response->viewData('rekaps');
        $this->assertCount(1, $rekaps);
        $this->assertEquals(1, $rekaps[0]->count_pns);
        $this->assertEquals(1, $rekaps[0]->count_total);
        // Nominal total should be 3500000 + 4000000 = 7500000
        $this->assertEquals(7500000, $rekaps[0]->nominal_pns);
        $this->assertEquals(7500000, $rekaps[0]->nominal_total);

        // Filter by Gaji 13 only
        $responseGaji13 = $this->get('/realisasi/gaji?tipe_laporan=rekap&periode_filter=Juni+2026&jenis_gaji_filter=Gaji+13');
        $responseGaji13->assertStatus(200);
        $rekaps13 = $responseGaji13->viewData('rekaps');
        $this->assertEquals(1, $rekaps13[0]->count_pns);
        $this->assertEquals(4000000, $rekaps13[0]->nominal_pns);

        // Filter by Gaji Induk only
        $responseInduk = $this->get('/realisasi/gaji?tipe_laporan=rekap&periode_filter=Juni+2026&jenis_gaji_filter=Gaji+Induk');
        $responseInduk->assertStatus(200);
        $rekapsInduk = $responseInduk->viewData('rekaps');
        $this->assertEquals(1, $rekapsInduk[0]->count_pns);
        $this->assertEquals(3500000, $rekapsInduk[0]->nominal_pns);
    }

    public function test_can_selectively_delete_salary_criteria(): void
    {
        $unitKerja = UnitKerja::create(['skpd' => 'Dinas Kesehatan']);
        $pegawai = Pegawai::create([
            'nip' => '199001012015011002',
            'nama' => 'Siti Nurhaliza',
            'status_pegawai' => 'PNS',
            'jenis_pegawai' => 'KESEHATAN',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        RealisasiGaji::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Juni 2026',
            'jenis_gaji' => RealisasiGaji::JENIS_GAJI_INDUK,
            'gaji_pokok' => 4000000,
            'pajak' => 100000,
            'iwp' => 320000,
            'potongan_lain' => 0,
            'gaji_bersih' => 3580000,
        ]);

        RealisasiGaji::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Juni 2026',
            'jenis_gaji' => RealisasiGaji::JENIS_GAJI_13,
            'gaji_pokok' => 4000000,
            'pajak' => 0,
            'iwp' => 0,
            'potongan_lain' => 0,
            'gaji_bersih' => 4000000,
        ]);

        $this->assertDatabaseCount('realisasi_gajis', 2);

        // Delete ONLY Gaji 13
        $response = $this->delete(route('setting.data.destroy'), [
            'jenis' => 'GAJI',
            'periode' => 'Juni 2026',
            'status_pegawai' => 'SEMUA',
            'jenis_gaji' => RealisasiGaji::JENIS_GAJI_13,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('realisasi_gajis', 1);
        $this->assertDatabaseHas('realisasi_gajis', [
            'pegawai_id' => $pegawai->id,
            'jenis_gaji' => RealisasiGaji::JENIS_GAJI_INDUK,
        ]);
        $this->assertDatabaseMissing('realisasi_gajis', [
            'pegawai_id' => $pegawai->id,
            'jenis_gaji' => RealisasiGaji::JENIS_GAJI_13,
        ]);
    }
}
