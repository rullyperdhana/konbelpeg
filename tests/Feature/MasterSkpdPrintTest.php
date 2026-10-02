<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterSkpdPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_skpd_exports(): void
    {
        $this->get('/master/skpd/export/pdf')->assertRedirect('/login');
        $this->get('/master/skpd/export/excel')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_skpd_index_with_two_tabs(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create([
            'skpd' => 'BADAN KEUANGAN DAERAH',
            'upt' => 'UPT PENDAPATAN',
            'satker' => 'SATKER KEUANGAN',
        ]);

        Pegawai::create([
            'nip' => '199001012020011001',
            'nama' => 'Ahmad Fauzi',
            'unit_kerja_id' => $unitKerja->id,
            'status_pegawai' => 'PNS',
        ]);

        // Tab Tree (Default)
        $responseTree = $this->actingAs($user)->get('/master/skpd');
        $responseTree->assertStatus(200);
        $responseTree->assertSee('Pohon Hierarki (Tree View)');
        $responseTree->assertSee('BADAN KEUANGAN DAERAH');
        $responseTree->assertSee('UPT PENDAPATAN');
        $responseTree->assertSee('SATKER KEUANGAN');

        // Tab Rekap
        $responseRekap = $this->actingAs($user)->get('/master/skpd?tab=rekap');
        $responseRekap->assertStatus(200);
        $responseRekap->assertSee('Ringkasan 42 SKPD Induk');
        $responseRekap->assertSee('BADAN KEUANGAN DAERAH');

        // Tab Rinci
        $responseRinci = $this->actingAs($user)->get('/master/skpd?tab=rinci');
        $responseRinci->assertStatus(200);
        $responseRinci->assertSee('Rincian 1.638 Unit Kerja');
        $responseRinci->assertSee('UPT PENDAPATAN');
    }

    public function test_authenticated_user_can_export_pdf_tree_view(): void
    {
        $user = User::factory()->create();
        UnitKerja::create([
            'skpd' => 'BADAN RISET DAERAH',
            'upt' => 'UPT INOVASI',
            'satker' => 'SATKER RISET',
        ]);

        $response = $this->actingAs($user)->get('/master/skpd/export/pdf?tab=tree');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_authenticated_user_can_export_pdf_rekap_and_rinci(): void
    {
        $user = User::factory()->create();
        UnitKerja::create([
            'skpd' => 'DINAS KESEHATAN',
            'upt' => 'PUSKESMAS MAWAR',
            'satker' => 'SATKER KESEHATAN',
        ]);

        // PDF Rekap
        $responseRekap = $this->actingAs($user)->get('/master/skpd/export/pdf?tab=rekap');
        $responseRekap->assertStatus(200);
        $responseRekap->assertHeader('content-type', 'application/pdf');

        // PDF Rinci
        $responseRinci = $this->actingAs($user)->get('/master/skpd/export/pdf?tab=rinci&skpd_filter=DINAS+KESEHATAN');
        $responseRinci->assertStatus(200);
        $responseRinci->assertHeader('content-type', 'application/pdf');
    }

    public function test_authenticated_user_can_export_pdf_with_search_filter(): void
    {
        $user = User::factory()->create();
        UnitKerja::create([
            'skpd' => 'DINAS PERHUBUNGAN',
            'upt' => 'UPTD TERMINAL',
        ]);

        $response = $this->actingAs($user)->get('/master/skpd/export/pdf?search=PERHUBUNGAN');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
