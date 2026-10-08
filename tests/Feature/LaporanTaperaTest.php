<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTaperaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/laporan/tapera');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_kalkulator_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/tapera?tab=kalkulator');

        $response->assertStatus(200);
        $response->assertSee('Simulasi Tapera');
        $response->assertSee('Kalkulator Simulasi Interaktif');
        $response->assertSee('Parameter Penghasilan ASN');
    }

    public function test_authenticated_user_can_view_rekap_page(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'BADAN KEPEGAWAIAN DAERAH']);
        $pegawai = Pegawai::create([
            'nip' => '198001012005011001',
            'nama' => 'Budi Santoso',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        RealisasiGaji::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Oktober 2026',
            'gaji_pokok' => 4000000,
            'gaji_bersih' => 4500000,
            'raw_data' => [
                'gapok' => 4000000,
                'tjistri' => 400000,
                'tjanak' => 160000,
                'tjfungsi' => 540000,
            ],
        ]);

        $response = $this->actingAs($user)->get('/laporan/tapera?tab=rekap&periode_filter=Oktober+2026');

        $response->assertStatus(200);
        $response->assertSee('BADAN KEPEGAWAIAN DAERAH');
        // Total dasar = 4.000.000 + 560.000 + 540.000 = 5.100.000
        // Pemda 0.5% = 25.500
        // ASN 2.5% = 127.500
        // Total 3% = 153.000
        $response->assertSee('5.100.000');
        $response->assertSee('25.500');
        $response->assertSee('127.500');
        $response->assertSee('153.000');
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

        RealisasiGaji::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Oktober 2026',
            'gaji_pokok' => 3000000,
            'gaji_bersih' => 3200000,
            'raw_data' => [
                'gapok' => 3000000,
                'tjistri' => 300000,
                'tjanak' => 0,
                'tjfungsi' => 300000,
            ],
        ]);

        $response = $this->actingAs($user)->get('/laporan/tapera?tab=rinci&periode_filter=Oktober+2026');

        $response->assertStatus(200);
        $response->assertSee('Siti Rahmawati, S.Pd');
        $response->assertSee('198505052010011002');
    }

    public function test_authenticated_user_can_export_pdf(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/tapera/export/pdf?tab=rekap&periode_filter=Semua+Periode');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_model_realisasi_gaji_tapera_calculations(): void
    {
        $unitKerja = UnitKerja::create(['skpd' => 'SETDA']);
        $pegawai = Pegawai::create([
            'nip' => '199001012015011003',
            'nama' => 'Ahmad Fauzi',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $gaji = RealisasiGaji::create([
            'pegawai_id' => $pegawai->id,
            'periode' => 'Oktober 2026',
            'gaji_pokok' => 4000000,
            'gaji_bersih' => 4500000,
            'raw_data' => [
                'gapok' => 4000000,
                'tjistri' => 400000,
                'tjanak' => 160000,
                'tjstruk' => 540000,
            ],
        ]);

        $this->assertEquals(560000, $gaji->tunj_keluarga);
        $this->assertEquals(540000, $gaji->tunj_jabatan);
        $this->assertEquals(5100000, $gaji->dasar_tapera);
        $this->assertEquals(127500, $gaji->simulasi_tapera_asn); // 2.5%
        $this->assertEquals(25500, $gaji->simulasi_tapera_pk);   // 0.5%
        $this->assertEquals(153000, $gaji->simulasi_tapera_total); // 3.0%
    }
}
