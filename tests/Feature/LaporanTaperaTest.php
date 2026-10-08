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

    public function test_authenticated_user_can_view_rekap_page_with_matrix_separation(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'BADAN KEPEGAWAIAN DAERAH']);

        // 1 PNS
        $pns = Pegawai::create([
            'nip' => '198001012005011001',
            'nama' => 'Budi Santoso',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);
        RealisasiGaji::create([
            'pegawai_id' => $pns->id,
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

        // 1 PPPK
        $pppk = Pegawai::create([
            'nip' => '199201012022011002',
            'nama' => 'Ahmad PPPK',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PPPK',
        ]);
        RealisasiGaji::create([
            'pegawai_id' => $pppk->id,
            'periode' => 'Oktober 2026',
            'gaji_pokok' => 3000000,
            'gaji_bersih' => 3300000,
            'raw_data' => [
                'gapok' => 3000000,
                'tjistri' => 300000,
                'tjanak' => 0,
                'tjfungsi' => 300000,
            ],
        ]);

        // 1 PPPK Paruh Waktu
        $paruh = Pegawai::create([
            'nip' => '199501012023011003',
            'nama' => 'Dewi Paruh Waktu',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PPPK PARUH WAKTU',
        ]);
        RealisasiGaji::create([
            'pegawai_id' => $paruh->id,
            'periode' => 'Oktober 2026',
            'gaji_pokok' => 2000000,
            'gaji_bersih' => 2000000,
            'raw_data' => [
                'gapok' => 2000000,
                'tjistri' => 0,
                'tjanak' => 0,
                'tjfungsi' => 0,
                'kelompok_upload' => 'PPPK PARUH WAKTU',
            ],
        ]);

        $response = $this->actingAs($user)->get('/laporan/tapera?tab=rekap&periode_filter=Oktober+2026&kategori_filter=all');

        $response->assertStatus(200);
        $response->assertSee('BADAN KEPEGAWAIAN DAERAH');
        $response->assertSee('Matriks Lengkap');
        // PNS Dasar = 5.100.000, Pemda = 25.500, ASN = 127.500
        $response->assertSee('5.100.000');
        $response->assertSee('25.500');
        $response->assertSee('127.500');
        // PPPK Dasar = 3.600.000, Pemda = 18.000, ASN = 90.000
        $response->assertSee('3.600.000');
        $response->assertSee('18.000');
        $response->assertSee('90.000');
        // Paruh Waktu Dasar = 2.000.000, Pemda = 10.000, ASN = 50.000
        $response->assertSee('2.000.000');
        $response->assertSee('10.000');
        $response->assertSee('50.000');
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

    public function test_model_realisasi_gaji_tapera_calculations_and_kategori_accessor(): void
    {
        $unitKerja = UnitKerja::create(['skpd' => 'SETDA']);
        $pegawaiPns = Pegawai::create([
            'nip' => '199001012015011003',
            'nama' => 'Ahmad Fauzi',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        $gajiPns = RealisasiGaji::create([
            'pegawai_id' => $pegawaiPns->id,
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

        $this->assertEquals(560000, $gajiPns->tunj_keluarga);
        $this->assertEquals(540000, $gajiPns->tunj_jabatan);
        $this->assertEquals(5100000, $gajiPns->dasar_tapera);
        $this->assertEquals(127500, $gajiPns->simulasi_tapera_asn); // 2.5%
        $this->assertEquals(25500, $gajiPns->simulasi_tapera_pk);   // 0.5%
        $this->assertEquals(153000, $gajiPns->simulasi_tapera_total); // 3.0%
        $this->assertEquals('PNS', $gajiPns->kategori_asn);

        // Test PPPK Paruh Waktu Accessor
        $pegawaiParuh = Pegawai::create([
            'nip' => '199501012023011004',
            'nama' => 'Dewi Lestari',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PPPK PARUH WAKTU',
        ]);
        $gajiParuh = RealisasiGaji::create([
            'pegawai_id' => $pegawaiParuh->id,
            'periode' => 'Oktober 2026',
            'gaji_pokok' => 2500000,
            'gaji_bersih' => 2500000,
            'raw_data' => ['gapok' => 2500000],
        ]);
        $this->assertEquals('PPPK PARUH WAKTU', $gajiParuh->kategori_asn);
    }
}
