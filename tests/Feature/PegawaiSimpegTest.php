<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PegawaiSimpegTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('master.pegawai_simpeg.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_pegawai_simpeg_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('master.pegawai_simpeg.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Master Pegawai SIMPEG');
        $response->assertSee('Format Kolom Spreadsheet SIMPEG');
        $response->assertSee('Simpan Berkas Excel');
    }

    public function test_user_can_download_simpeg_excel_template(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('master.pegawai_simpeg.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_user_can_upload_simpeg_excel_file_instantly(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['NIP', 'NAMA', 'STATUS', 'GOLRU', 'SKPD', 'UPT', 'JABATAN', 'TGL_LAHIR'],
            ['199001012015011001', 'BUDI SANTOSO, S.Kom', 'PNS', 'III/a', 'DISKOMINFO', 'Bidang TI', 'Pranata Komputer', '01-01-1990'],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_simpeg_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile(
            $tempPath,
            'test_simpeg.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($user)->post(route('master.pegawai_simpeg.upload'), [
            'file' => $uploadedFile,
            'mode' => 'upsert',
            'keterangan' => 'Uji Coba Upload SIMPEG',
        ]);

        $response->assertRedirect(route('master.pegawai_simpeg.index'));
        $response->assertSessionHas('success');

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }

    public function test_user_can_upload_and_import_simpeg_excel(): void
    {
        $user = User::factory()->create();

        // Buat file spreadsheet dummy sementara
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['NIP', 'NAMA', 'STATUS', 'GOLRU', 'SKPD', 'UPT', 'JABATAN', 'TGL_LAHIR'],
            ['199001012015011001', 'BUDI SANTOSO, S.Kom', 'PNS', 'III/a', 'DISKOMINFO', 'Bidang TI', 'Pranata Komputer', '01-01-1990'],
            ['199505052022211002', 'ANDI WIJAYA, S.Pd', 'PPPK', 'IX', 'DINAS PENDIDIKAN', 'SMPN 1', 'Guru TIK', '05-05-1995'],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_simpeg_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile(
            $tempPath,
            'test_simpeg.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($user)->post(route('master.pegawai_simpeg.upload'), [
            'file' => $uploadedFile,
            'mode' => 'upsert',
            'keterangan' => 'Uji Coba Import SIMPEG',
            'auto_sync' => true,
        ]);

        $response->assertRedirect(route('master.pegawai_simpeg.index'));
        $response->assertSessionHas('success');

        // Pastikan pegawai terdaftar di database
        $this->assertDatabaseHas('pegawais', [
            'nip' => '199001012015011001',
            'nama' => 'BUDI SANTOSO, S.Kom',
            'status_pegawai' => 'PNS',
            'golru' => 'III/a',
        ]);

        $this->assertDatabaseHas('pegawais', [
            'nip' => '199505052022211002',
            'nama' => 'ANDI WIJAYA, S.Pd',
            'status_pegawai' => 'PPPK',
            'golru' => 'IX',
        ]);

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }

    public function test_user_can_sync_uploaded_file_via_ajax(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['NIP', 'NAMA', 'STATUS', 'GOLRU', 'TMT_GOLRU', 'SKPD', 'UPT', 'JABATAN', 'TGL_LAHIR'],
            ['198808082012011003', 'HENDRA KURNIAWAN, S.T', 'PNS', 'III/b', '01-04-2020', 'DINAS PUPR', 'Bidang Bina Marga', 'Teknik Jalan & Jembatan', '08-08-1988'],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_simpeg_sync_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile(
            $tempPath,
            'test_simpeg_sync.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $uploadResponse = $this->actingAs($user)->postJson(route('master.pegawai_simpeg.upload'), [
            'file' => $uploadedFile,
            'mode' => 'upsert',
            'keterangan' => 'Upload For Sync Test',
        ]);

        $uploadResponse->assertStatus(200);
        $fileId = $uploadResponse->json('file_id');
        $this->assertNotEmpty($fileId);

        $syncResponse = $this->actingAs($user)->postJson(route('master.pegawai_simpeg.sync', $fileId), [
            'mode' => 'upsert',
        ]);

        $syncResponse->assertStatus(200);
        $syncResponse->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('pegawais', [
            'nip' => '198808082012011003',
            'nama' => 'HENDRA KURNIAWAN, S.T',
            'status_pegawai' => 'PNS',
            'golru' => 'III/b',
            'tmt_golru' => '01-04-2020',
        ]);

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }
}
