<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraceGajiPegawaiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/laporan/trace-gaji');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_trace_gaji_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/trace-gaji');

        $response->assertStatus(200);
        $response->assertSee('Trace Riwayat & Daftar Penggajian Per Orang', false);
    }

    public function test_can_search_pegawai_by_nip_and_view_payroll_trace(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'BADAN KEPEGAWAIAN DAERAH']);
        $pegawai = Pegawai::create([
            'nip' => '198501012010011005',
            'nama' => 'AHMAD FAUZI, S.Kom',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
            'golru' => 'III/b',
        ]);

        RealisasiGaji::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Oktober 2026',
            'jenis_gaji' => 'Gaji Induk',
            'gaji_pokok' => 3500000,
            'pajak' => 50000,
            'iwp' => 350000,
            'potongan_lain' => 20000,
            'gaji_bersih' => 4200000,
            'raw_data' => [
                'gapok' => 3500000,
                'tjistri' => 350000,
                'tjanak' => 140000,
                'tjberas' => 217260,
                'tjeselon' => 0,
                'tjstruk' => 0,
                'tjfungsi' => 400000,
                'kotor' => 4620000,
                'ppajak' => 50000,
                'piwp' => 350000,
                'piwp2' => 70000,
                'piwp8' => 280000,
                'potongan' => 420000,
                'bersih' => 4200000,
                'norek' => '1234567890',
            ],
        ]);

        $response = $this->actingAs($user)->get('/laporan/trace-gaji?q=198501012010011005');

        $response->assertStatus(200);
        $response->assertSee('AHMAD FAUZI, S.Kom');
        $response->assertSee('198501012010011005');
        $response->assertSee('BADAN KEPEGAWAIAN DAERAH');
        $response->assertSee('4.200.000');
        $response->assertSee('Oktober 2026');
    }

    public function test_can_search_pegawai_by_name(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS PENDIDIKAN']);
        $pegawai = Pegawai::create([
            'nip' => '199002022015022002',
            'nama' => 'SITI NURHALIZA, M.Pd',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PPPK',
        ]);

        $response = $this->actingAs($user)->get('/laporan/trace-gaji?q=Nurhaliza');

        $response->assertStatus(200);
        $response->assertSee('SITI NURHALIZA, M.Pd');
        $response->assertSee('199002022015022002');
        $response->assertSee('DINAS PENDIDIKAN');
    }

    public function test_can_export_pdf_trace_rekap(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS KESEHATAN']);
        $pegawai = Pegawai::create([
            'nip' => '198703032011011003',
            'nama' => 'dr. HENDRA WIJAYA',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        RealisasiGaji::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Oktober 2026',
            'jenis_gaji' => 'Gaji Induk',
            'gaji_pokok' => 4500000,
            'gaji_bersih' => 5200000,
        ]);

        $response = $this->actingAs($user)->get("/laporan/trace-gaji/{$pegawai->id}/export-pdf");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_can_download_monthly_salary_slip_pdf(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'INSPEKTORAT']);
        $pegawai = Pegawai::create([
            'nip' => '197505052000031001',
            'nama' => 'BAMBANG HERMANTO',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        $gaji = RealisasiGaji::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'September 2026',
            'jenis_gaji' => 'Gaji Induk',
            'gaji_pokok' => 5000000,
            'gaji_bersih' => 6100000,
            'raw_data' => [
                'gapok' => 5000000,
                'kotor' => 6500000,
                'potongan' => 400000,
                'bersih' => 6100000,
            ],
        ]);

        $response = $this->actingAs($user)->get("/laporan/trace-gaji/{$pegawai->id}/slip-pdf/{$gaji->id}");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }
}
