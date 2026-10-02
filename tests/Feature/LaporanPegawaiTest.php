<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanPegawaiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_laporan_pegawai(): void
    {
        $this->get('/laporan/pegawai')->assertRedirect('/login');
        $this->get('/laporan/pegawai/export/pdf')->assertRedirect('/login');
        $this->get('/laporan/pegawai/export/excel')->assertRedirect('/login');
        $this->get('/laporan/pegawai/filter-options')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_laporan_pegawai_index_and_tabs(): void
    {
        $user = User::factory()->create();

        $unitKerja = UnitKerja::create([
            'skpd' => 'DINAS KESEHATAN',
            'upt' => 'PUSKESMAS MAWAR',
            'satker' => 'SUB BAGIAN TATA USAHA',
        ]);

        $jabatan = Jabatan::create([
            'nama' => 'DOKTER AHLI PERTAMA',
            'jenis' => 'FUNGSIONAL',
            'eselon' => 'NON ESELON',
        ]);

        Pegawai::create([
            'nip' => '198801012015011001',
            'nama' => 'dr. Budi Santoso',
            'status_pegawai' => 'PNS',
            'jenis_pegawai' => 'KESEHATAN',
            'golru' => 'III/b',
            'unit_kerja_id' => $unitKerja->id,
            'jabatan_id' => $jabatan->id,
        ]);

        // Tab Rinci (Default)
        $responseRinci = $this->actingAs($user)->get('/laporan/pegawai');
        $responseRinci->assertStatus(200);
        $responseRinci->assertSee('Laporan Daftar Pegawai per SKPD / UPT / Satker');
        $responseRinci->assertSee('198801012015011001');
        $responseRinci->assertSee('dr. Budi Santoso');
        $responseRinci->assertSee('DINAS KESEHATAN');
        $responseRinci->assertSee('PUSKESMAS MAWAR');

        // Tab Hierarki
        $responseHierarki = $this->actingAs($user)->get('/laporan/pegawai?tab=hierarki&skpd=DINAS+KESEHATAN');
        $responseHierarki->assertStatus(200);
        $responseHierarki->assertSee('DINAS KESEHATAN');
        $responseHierarki->assertSee('PUSKESMAS MAWAR');
        $responseHierarki->assertSee('dr. Budi Santoso');

        // Tab Rekap
        $responseRekap = $this->actingAs($user)->get('/laporan/pegawai?tab=rekap');
        $responseRekap->assertStatus(200);
        $responseRekap->assertSee('DINAS KESEHATAN');
    }

    public function test_authenticated_user_can_filter_pegawai_by_skpd_and_status(): void
    {
        $user = User::factory()->create();

        $unit1 = UnitKerja::create(['skpd' => 'DINAS KESEHATAN', 'upt' => 'PUSKESMAS A']);
        $unit2 = UnitKerja::create(['skpd' => 'DINAS PENDIDIKAN', 'upt' => 'SMAN 1']);

        Pegawai::create([
            'nip' => '199001012020011001',
            'nama' => 'Ahmad Pegawai Kesehatan',
            'status_pegawai' => 'PNS',
            'unit_kerja_id' => $unit1->id,
        ]);

        Pegawai::create([
            'nip' => '199501012022012002',
            'nama' => 'Siti Guru Honorer',
            'status_pegawai' => 'PPPK',
            'unit_kerja_id' => $unit2->id,
        ]);

        // Filter SKPD Dinas Kesehatan
        $response = $this->actingAs($user)->get('/laporan/pegawai?skpd=DINAS+KESEHATAN');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Pegawai Kesehatan');
        $response->assertDontSee('Siti Guru Honorer');

        // Filter Status PPPK
        $responseStatus = $this->actingAs($user)->get('/laporan/pegawai?status_pegawai=PPPK');
        $responseStatus->assertStatus(200);
        $responseStatus->assertSee('Siti Guru Honorer');
        $responseStatus->assertDontSee('Ahmad Pegawai Kesehatan');
    }

    public function test_authenticated_user_can_fetch_filter_options_json(): void
    {
        $user = User::factory()->create();

        UnitKerja::create([
            'skpd' => 'DINAS KESEHATAN',
            'upt' => 'PUSKESMAS MELATI',
            'satker' => 'SATKER FARMASI',
        ]);

        $response = $this->actingAs($user)->getJson('/laporan/pegawai/filter-options?skpd=DINAS+KESEHATAN');
        $response->assertStatus(200);
        $response->assertJsonFragment(['upts' => ['PUSKESMAS MELATI']]);

        $responseSatker = $this->actingAs($user)->getJson('/laporan/pegawai/filter-options?skpd=DINAS+KESEHATAN&upt=PUSKESMAS+MELATI');
        $responseSatker->assertStatus(200);
        $responseSatker->assertJsonFragment(['satkers' => ['SATKER FARMASI']]);
    }

    public function test_authenticated_user_can_export_pdf(): void
    {
        $user = User::factory()->create();

        $unit = UnitKerja::create([
            'skpd' => 'DINAS PERHUBUNGAN',
            'upt' => 'UPTD TERMINAL',
            'satker' => 'SUB BAGIAN UMUM',
        ]);

        Pegawai::create([
            'nip' => '198505052010011003',
            'nama' => 'Bambang Perhubungan',
            'status_pegawai' => 'PNS',
            'unit_kerja_id' => $unit->id,
        ]);

        $response = $this->actingAs($user)->get('/laporan/pegawai/export/pdf?skpd=DINAS+PERHUBUNGAN');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
