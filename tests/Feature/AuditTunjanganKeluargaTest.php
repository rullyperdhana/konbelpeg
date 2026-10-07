<?php

namespace Tests\Feature;

use App\Models\AuditTunjanganResolusi;
use App\Models\Pegawai;
use App\Models\SimgajiKeluarga;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTunjanganKeluargaTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_audit_tunjangan_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('laporan.audit_tunjangan.index'));

        $response->assertStatus(200);
        $response->assertSee('Audit Tunjangan Keluarga SIMGAJI');
        $response->assertSee('Dobel Tunjangan Anak');
        $response->assertSee('Pasangan Saling Menunjang');
        $response->assertSee('Melebihi Batas Kuota');
    }

    public function test_audit_detects_double_child_allowance(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS KESEHATAN']);

        // Ayah (PNS)
        $ayah = Pegawai::create([
            'nip' => '199101012015011001',
            'nama' => 'BAMBANG HERMANTO',
            'status_pegawai' => 'PNS',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        // Ibu (PNS)
        $ibu = Pegawai::create([
            'nip' => '199202022016022002',
            'nama' => 'RATNA SARI',
            'status_pegawai' => 'PNS',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        // Anak diklaim tertunjang oleh Ayah
        SimgajiKeluarga::create([
            'nip' => $ayah->nip,
            'nmkel' => 'KEVIN HERMANTO',
            'kdhubkel' => '11',
            'hubungan' => 'Anak ke-1',
            'tgllhr' => '2016-04-10',
            'kdtunjang' => '2',
            'status_tunjangan' => 'Tertunjang',
        ]);

        // Anak yang sama diklaim tertunjang oleh Ibu
        SimgajiKeluarga::create([
            'nip' => $ibu->nip,
            'nmkel' => 'KEVIN HERMANTO',
            'kdhubkel' => '11',
            'hubungan' => 'Anak ke-1',
            'tgllhr' => '2016-04-10',
            'kdtunjang' => '2',
            'status_tunjangan' => 'Tertunjang',
        ]);

        $response = $this->actingAs($user)->get(route('laporan.audit_tunjangan.index', ['tab' => 'anak']));

        $response->assertStatus(200);
        $response->assertSee('KEVIN HERMANTO');
        $response->assertSee('BAMBANG HERMANTO');
        $response->assertSee('RATNA SARI');
        $response->assertSee('Tertunjang Ganda (2% + 2%)');
    }

    public function test_audit_detects_double_spouse_allowance(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS PENDIDIKAN']);

        $suami = Pegawai::create([
            'nip' => '198505052010011003',
            'nama' => 'SURYA DARMAWAN',
            'status_pegawai' => 'PNS',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $istri = Pegawai::create([
            'nip' => '198606062011012004',
            'nama' => 'DEWI ANGGRAENI',
            'status_pegawai' => 'PNS',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        // Pasangan di data Suami -> Istri tertunjang 10%
        SimgajiKeluarga::create([
            'nip' => $suami->nip,
            'nmkel' => 'DEWI ANGGRAENI',
            'kdhubkel' => '10',
            'hubungan' => 'Istri / Suami',
            'tgllhr' => '1986-06-06',
            'kdtunjang' => '2',
            'status_tunjangan' => 'Tertunjang',
            'nipsuamiis' => $istri->nip,
        ]);

        // Pasangan di data Istri -> Suami tertunjang 10%
        SimgajiKeluarga::create([
            'nip' => $istri->nip,
            'nmkel' => 'SURYA DARMAWAN',
            'kdhubkel' => '10',
            'hubungan' => 'Istri / Suami',
            'tgllhr' => '1985-05-05',
            'kdtunjang' => '2',
            'status_tunjangan' => 'Tertunjang',
            'nipsuamiis' => $suami->nip,
        ]);

        $response = $this->actingAs($user)->get(route('laporan.audit_tunjangan.index', ['tab' => 'pasangan']));

        $response->assertStatus(200);
        $response->assertSee('SURYA DARMAWAN');
        $response->assertSee('DEWI ANGGRAENI');
        $response->assertSee('Saling Menunjang (10% + 10%)');
    }

    public function test_audit_detects_over_quota_children(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'BAPENDA']);

        $pegawai = Pegawai::create([
            'nip' => '198003032005011002',
            'nama' => 'HENDRA WIJAYA',
            'status_pegawai' => 'PNS',
            'golru' => 'IV/a',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        // 3 children with kdtunjang = 2
        for ($i = 1; $i <= 3; $i++) {
            SimgajiKeluarga::create([
                'nip' => $pegawai->nip,
                'nmkel' => 'ANAK KE '.$i.' HENDRA',
                'kdhubkel' => '1'.$i,
                'hubungan' => 'Anak ke-'.$i,
                'tgllhr' => '201'.$i.'-01-01',
                'kdtunjang' => '2',
                'status_tunjangan' => 'Tertunjang',
            ]);
        }

        $response = $this->actingAs($user)->get(route('laporan.audit_tunjangan.index', ['tab' => 'kuota']));

        $response->assertStatus(200);
        $response->assertSee('HENDRA WIJAYA');
        $response->assertSee('3 Anak');
        $response->assertSee('+1 Anak');
    }

    public function test_export_pdf_and_excel(): void
    {
        $user = User::factory()->create();

        $resPdf = $this->actingAs($user)->get(route('laporan.audit_tunjangan.export_pdf', ['tab' => 'anak']));
        $resPdf->assertStatus(200);

        $resExcel = $this->actingAs($user)->get(route('laporan.audit_tunjangan.export_excel'));
        $resExcel->assertStatus(200);
    }

    public function test_can_store_resolution_with_sts_and_notes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('laporan.audit_tunjangan.store_resolusi'), [
            'kategori' => 'anak',
            'kunci_kasus' => 'anak_test_case_key_123',
            'status' => 'selesai',
            'no_sts' => '900/123/BPKAD/2026',
            'tgl_sts' => '2026-09-15',
            'nominal_pengembalian' => 370000,
            'catatan' => 'Sudah diselesaikan dengan pengembalian ke Kasda via STS',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('audit_tunjangan_resolusis', [
            'kunci_kasus' => 'anak_test_case_key_123',
            'no_sts' => '900/123/BPKAD/2026',
            'nominal_pengembalian' => 370000,
            'status' => 'selesai',
        ]);
    }

    public function test_can_filter_by_resolution_status(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS KESEHATAN']);

        $ayah = Pegawai::create([
            'nip' => '199101012015011001',
            'nama' => 'BAMBANG HERMANTO',
            'status_pegawai' => 'PNS',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $ibu = Pegawai::create([
            'nip' => '199202022016022002',
            'nama' => 'RATNA SARI',
            'status_pegawai' => 'PNS',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        SimgajiKeluarga::create([
            'nip' => $ayah->nip,
            'nmkel' => 'KEVIN HERMANTO',
            'kdhubkel' => '11',
            'hubungan' => 'Anak ke-1',
            'tgllhr' => '2016-04-10',
            'kdtunjang' => '2',
            'status_tunjangan' => 'Tertunjang',
        ]);

        SimgajiKeluarga::create([
            'nip' => $ibu->nip,
            'nmkel' => 'KEVIN HERMANTO',
            'kdhubkel' => '11',
            'hubungan' => 'Anak ke-1',
            'tgllhr' => '2016-04-10',
            'kdtunjang' => '2',
            'status_tunjangan' => 'Tertunjang',
        ]);

        // Key for this child case
        $caseKey = 'anak_'.md5('KEVIN HERMANTO_2016-04-10_199101012015011001_199202022016022002');

        // Check before resolution (pending)
        $resPending = $this->actingAs($user)->get(route('laporan.audit_tunjangan.index', ['tab' => 'anak', 'status' => 'pending']));
        $resPending->assertStatus(200);
        $resPending->assertSee('KEVIN HERMANTO');

        // Check in selesai filter (should not be in selesai yet)
        $resSelesaiEmpty = $this->actingAs($user)->get(route('laporan.audit_tunjangan.index', ['tab' => 'anak', 'status' => 'selesai']));
        $resSelesaiEmpty->assertStatus(200);
        $resSelesaiEmpty->assertDontSee('KEVIN HERMANTO');

        // Resolve case with STS
        $this->actingAs($user)->post(route('laporan.audit_tunjangan.store_resolusi'), [
            'kategori' => 'anak',
            'kunci_kasus' => $caseKey,
            'status' => 'selesai',
            'no_sts' => 'STS-KEVIN-001',
            'tgl_sts' => '2026-09-20',
            'nominal_pengembalian' => 148000,
            'catatan' => 'Selesai bayar kembali via STS',
        ]);

        // Now check in selesai filter (should appear in selesai)
        $resSelesaiFound = $this->actingAs($user)->get(route('laporan.audit_tunjangan.index', ['tab' => 'anak', 'status' => 'selesai']));
        $resSelesaiFound->assertStatus(200);
        $resSelesaiFound->assertSee('KEVIN HERMANTO');
        $resSelesaiFound->assertSee('STS-KEVIN-001');

        // Check in pending filter (should disappear from pending)
        $resPendingGone = $this->actingAs($user)->get(route('laporan.audit_tunjangan.index', ['tab' => 'anak', 'status' => 'pending']));
        $resPendingGone->assertStatus(200);
        $resPendingGone->assertDontSee('KEVIN HERMANTO');
    }

    public function test_can_destroy_resolution(): void
    {
        $user = User::factory()->create();

        $resolusi = AuditTunjanganResolusi::create([
            'kategori' => 'anak',
            'kunci_kasus' => 'anak_case_to_delete',
            'status' => 'selesai',
            'no_sts' => 'STS-DEL-001',
        ]);

        $response = $this->actingAs($user)->delete(route('laporan.audit_tunjangan.destroy_resolusi', $resolusi->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('audit_tunjangan_resolusis', [
            'id' => $resolusi->id,
        ]);
    }
}
