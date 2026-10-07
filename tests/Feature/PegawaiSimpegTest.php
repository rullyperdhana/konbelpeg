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
        $response->assertSee('Unggah File Excel SIMPEG');
    }

    public function test_user_can_download_simpeg_excel_template(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('master.pegawai_simpeg.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
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
}
