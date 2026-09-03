<?php

namespace App\Console\Commands;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportPegawai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:pegawai {file=data pegawai Pemprov tmt 1 September 2026.xlsx}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Data Pegawai, Jabatan, dan Unit Kerja dari file Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');

        if (! file_exists(base_path($file))) {
            $this->error("File $file tidak ditemukan di root directory!");

            return;
        }

        $this->info("Memulai proses import dari $file...");

        $reader = SimpleExcelReader::create(base_path($file));
        $rowsCount = 0;

        $reader->getRows()->each(function (array $rowProperties) use (&$rowsCount) {
            // 1. Simpan atau Ambil Unit Kerja
            $unitKerja = UnitKerja::firstOrCreate([
                'skpd' => $rowProperties['SKPD'] ?? null,
                'upt' => $rowProperties['UPT'] ?? null,
                'satker' => $rowProperties['SATKER'] ?? null,
            ]);

            // 2. Simpan atau Ambil Jabatan
            $jabatan = Jabatan::firstOrCreate([
                'nama' => $rowProperties['JABATAN'] ?? null,
                'eselon' => $rowProperties['ESELON'] ?? null,
                'jenis' => $rowProperties['JENIS_JABATAN'] ?? null,
            ]);

            // 3. Simpan Pegawai
            // Format Tanggal Lahir (DD-MM-YYYY)
            $tglLahir = null;
            if (! empty($rowProperties['TGL_LAHIR'])) {
                try {
                    $tglLahir = Carbon::createFromFormat('d-m-Y', $rowProperties['TGL_LAHIR'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // Fallback
                }
            }

            Pegawai::updateOrCreate(
                ['nip' => $rowProperties['NIP']],
                [
                    'nama' => $rowProperties['NAMA'] ?? null,
                    'tempat_lahir' => $rowProperties['TEMPAT_LAHIR'] ?? null,
                    'tgl_lahir' => $tglLahir,
                    'jk' => $rowProperties['JK'] ?? null,
                    'agama' => $rowProperties['AGAMA'] ?? null,
                    'status_pegawai' => $rowProperties['STATUS'] ?? null,
                    'golru' => $rowProperties['GOLRU'] ?? null,
                    'tmt_golru' => $rowProperties['TMT_GOLRU'] ?? null,
                    'masa_kerja_tahun' => is_numeric($rowProperties['MK_THN']) ? (int) $rowProperties['MK_THN'] : null,
                    'masa_kerja_bulan' => is_numeric($rowProperties['MK_BLN']) ? (int) $rowProperties['MK_BLN'] : null,
                    'tk_ijazah' => $rowProperties['TK_IJAZAH'] ?? null,
                    'nm_pendidikan' => $rowProperties['NM_PENDIDIKAN'] ?? null,
                    'th_lulus' => is_numeric($rowProperties['TH_LULUS']) ? (int) $rowProperties['TH_LULUS'] : null,
                    'jabatan_id' => $jabatan->id,
                    'unit_kerja_id' => $unitKerja->id,
                ]
            );

            $rowsCount++;
            if ($rowsCount % 100 == 0) {
                $this->info("Berhasil mengimpor $rowsCount data...");
            }
        });

        $this->info("Selesai! Total data yang diimpor: $rowsCount.");
    }
}
