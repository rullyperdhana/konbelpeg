<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\SimgajiKeluarga;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimgajiKeluargaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pegawai_can_have_financial_attributes_and_family_relations(): void
    {
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS PENDIDIKAN']);

        $pegawai = Pegawai::create([
            'nip' => '198501012010011005',
            'nama' => 'AHMAD FAUZI, S.Kom',
            'nik' => '6371010101850001',
            'no_rekening' => '0300301010966',
            'nama_bank' => 'Bank Kalsel',
            'npwp' => '6371013108640002',
            'status_pegawai' => 'PNS',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $keluarga1 = SimgajiKeluarga::create([
            'nip' => $pegawai->nip,
            'nmkel' => 'SITI AMINAH',
            'kdhubkel' => '10',
            'hubungan' => 'Istri / Suami',
            'kdjenkel' => '2',
            'jenis_kelamin' => 'Perempuan',
            'tgllhr' => '1988-05-15',
            'kdtunjang' => '2',
            'status_tunjangan' => 'Tertunjang',
        ]);

        $keluarga2 = SimgajiKeluarga::create([
            'nip' => $pegawai->nip,
            'nmkel' => 'MUHAMMAD RIZKY',
            'kdhubkel' => '11',
            'hubungan' => 'Anak ke-1',
            'kdjenkel' => '1',
            'jenis_kelamin' => 'Laki-laki',
            'tgllhr' => '2015-08-20',
            'kdtunjang' => '2',
            'status_tunjangan' => 'Tertunjang',
        ]);

        $this->assertEquals(2, $pegawai->simgajiKeluargas()->count());
        $this->assertTrue($keluarga1->is_tertunjang);
        $this->assertNotNull($keluarga2->usia);
        $this->assertEquals('AHMAD FAUZI, S.Kom', $keluarga1->pegawai->nama);
    }

    public function test_authenticated_user_can_view_pegawai_index_with_nik_and_rekening(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'BADAN KEPEGAWAIAN DAERAH']);

        Pegawai::create([
            'nip' => '198501012010011005',
            'nama' => 'AHMAD FAUZI, S.Kom',
            'nik' => '6371010101850001',
            'no_rekening' => '0300301010966',
            'nama_bank' => 'Bank Kalsel',
            'status_pegawai' => 'PNS',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $response = $this->actingAs($user)->get('/pegawai');

        $response->assertStatus(200);
        $response->assertSee('198501012010011005');
        $response->assertSee('AHMAD FAUZI, S.Kom');
        $response->assertSee('6371010101850001');
        $response->assertSee('0300301010966');
    }

    public function test_authenticated_user_can_view_family_section_in_trace_gaji(): void
    {
        $user = User::factory()->create();
        $unitKerja = UnitKerja::create(['skpd' => 'DINAS KESEHATAN']);

        $pegawai = Pegawai::create([
            'nip' => '198501012010011005',
            'nama' => 'AHMAD FAUZI, S.Kom',
            'nik' => '6371010101850001',
            'no_rekening' => '0300301010966',
            'nama_bank' => 'Bank Kalsel',
            'status_pegawai' => 'PNS',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        SimgajiKeluarga::create([
            'nip' => $pegawai->nip,
            'nmkel' => 'SITI AMINAH',
            'kdhubkel' => '10',
            'hubungan' => 'Istri / Suami',
            'kdjenkel' => '2',
            'jenis_kelamin' => 'Perempuan',
            'tgllhr' => '1988-05-15',
            'kdtunjang' => '2',
            'status_tunjangan' => 'Tertunjang',
        ]);

        $response = $this->actingAs($user)->get('/laporan/trace-gaji?pegawai_id='.$pegawai->id);

        $response->assertStatus(200);
        $response->assertSee('Daftar Anggota Keluarga');
        $response->assertSee('SITI AMINAH');
        $response->assertSee('Istri / Suami');
        $response->assertSee('Tertunjang');
        $response->assertSee('6371010101850001'); // NIK
    }

    public function test_master_simgaji_dbf_page_displays_kel_card(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/master/simgaji-dbf');

        $response->assertStatus(200);
        $response->assertSee('Riwayat Keluarga');
        $response->assertSee('Master Pegawai');
        $response->assertSee('Histori Gaji Pokok &amp; SK', false);
        $response->assertSee('KEL_*.DBF');
    }

    public function test_audit_tunjangan_page_displays_kel_dbf_status_and_modal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('laporan.audit_tunjangan.index'));

        $response->assertStatus(200);
        $response->assertSee('Sumber Berkas DBF Riwayat Keluarga');
        $response->assertSee('Unggah / Ganti KEL_*.DBF');
        $response->assertSee('modalUploadKelDbf');
    }

    public function test_reconciliation_upload_modal_includes_kel_option(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('laporan.rekonsiliasi_simgaji.index'));

        $response->assertStatus(200);
        $response->assertSee('value="kel"', false);
        $response->assertSee('KEL_*.DBF');
    }
}
