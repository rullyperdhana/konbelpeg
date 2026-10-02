<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\RealisasiTpp;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IwpJamkesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/laporan/iwp-jamkes');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_rekap_page(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS KESEHATAN']);
        $pegawai = Pegawai::create([
            'nip' => '198001012005011001',
            'nama' => 'Dr. Budi Santoso',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        RealisasiGaji::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Juli 2026',
            'gaji_pokok' => 5000000,
            'iwp' => 500000,
            'gaji_bersih' => 4500000,
            'raw_data' => [
                'piwp' => 500000,
                'piwp2' => 100000,
                'piwp8' => 400000,
            ],
        ]);

        RealisasiTpp::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Juli 2026',
            'tpp_bruto' => 10000000,
            'iuran_iwp' => 100000,
            'total_dibayarkan' => 9900000,
        ]);

        $response = $this->actingAs($user)->get('/laporan/iwp-jamkes?tipe_laporan=rekap&periode_filter=Juli+2026');

        $response->assertStatus(200);
        $response->assertSee('Laporan IWP');
        $response->assertSee('DINAS KESEHATAN');
        $response->assertSee('100.000');
    }

    public function test_authenticated_user_can_view_rinci_page(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS PENDIDIKAN']);
        $pegawai = Pegawai::create([
            'nip' => '198505052010011002',
            'nama' => 'Siti Rahmawati, S.Pd',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        $response = $this->actingAs($user)->get('/laporan/iwp-jamkes?tipe_laporan=rinci');

        $response->assertStatus(200);
        $response->assertSee('Siti Rahmawati, S.Pd');
        $response->assertSee('198505052010011002');
    }

    public function test_authenticated_user_can_export_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/iwp-jamkes/export/pdf?tipe_laporan=rekap&periode_filter=Juli+2026');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
