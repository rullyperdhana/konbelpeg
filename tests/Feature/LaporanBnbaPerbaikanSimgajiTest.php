<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LaporanBnbaPerbaikanSimgajiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $unitKerja = UnitKerja::create([
            'skpd' => 'DINAS KESEHATAN',
            'upt' => 'UPTD INSTALASI FARMASI',
            'satker' => 'DINAS KESEHATAN',
        ]);

        Pegawai::create([
            'nip' => '197608142006042010',
            'nama' => 'RUSLENA, S.Kep., M.M.',
            'golru' => 'IV/a',
            'status_pegawai' => 'PNS',
            'unit_kerja_id' => $unitKerja->id,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/laporan/perbaikan-simgaji-skpd');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_bnba_perbaikan_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/perbaikan-simgaji-skpd');

        $response->assertStatus(200);
        $response->assertSee('Laporan BNBA &amp; Pemetaan Master SKPD SIMGAJI', false);
        $response->assertSee('ACUAN RESMI: SIMPEG / KONBELPEG');
        $response->assertSee('Daftar Nominatif BNBA Usulan Perbaikan Data SIMGAJI');
    }

    public function test_authenticated_user_can_access_master_skpd_tab(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/perbaikan-simgaji-skpd?tab=master_skpd');

        $response->assertStatus(200);
        $response->assertSee('Master SKPD SIMGAJI vs SIMPEG');
        $response->assertSee('DINAS PENDIDIKAN DAN KEBUDAYAAN');
    }

    public function test_authenticated_user_can_access_master_satker_tab(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/perbaikan-simgaji-skpd?tab=master_satker');

        $response->assertStatus(200);
        $response->assertSee('Daftar Master 551 Satuan Kerja (Satker) SIMGAJI');
        $response->assertSee('Total Master: 551 Satker');
    }

    public function test_refresh_cache_redirects_back_with_success(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/perbaikan-simgaji-skpd/refresh');

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    public function test_export_pdf_returns_pdf_document(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/perbaikan-simgaji-skpd/export/pdf');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_export_pdf_master_skpd_returns_pdf_document(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/perbaikan-simgaji-skpd/export/pdf?tab=master_skpd');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_export_pdf_master_satker_returns_pdf_document(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/perbaikan-simgaji-skpd/export/pdf?tab=master_satker');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_filter_kategori_and_search(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/perbaikan-simgaji-skpd?status=perlu_perbaikan&kategori=beda_skpd&search=19760814');

        $response->assertStatus(200);
        $response->assertSee('RUSLENA');
    }

    public function test_filter_status_semua_displays_records(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/perbaikan-simgaji-skpd?status=semua&per_page=50');

        $response->assertStatus(200);
        $response->assertSee('RUSLENA');
    }

    public function test_authenticated_user_can_access_simpeg_lengkap_tab(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/perbaikan-simgaji-skpd?tab=simpeg_lengkap');

        $response->assertStatus(200);
        $response->assertSee('Laporan BNBA Pegawai SIMPEG Lengkap Disandingkan Data SIMGAJI');
        $response->assertSee('Kd SKPD');
        $response->assertSee('Nama SKPD SIMGAJI');
        $response->assertSee('Kode Satker');
        $response->assertSee('Nama Satker Resmi SIMGAJI');
        $response->assertSee('RUSLENA');
    }

    public function test_authenticated_user_can_export_simpeg_lengkap_excel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/laporan/perbaikan-simgaji-skpd/export/simpeg-lengkap');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
