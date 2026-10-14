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

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $reader = SimpleExcelReader::create(base_path($file), $extension);
        $rowsCount = 0;

        $reader->getRows()->each(function (array $rawRow) use (&$rowsCount) {
            // Normalisasi kunci header & strip tanda petik tunggal
            $rowProperties = [];
            foreach ($rawRow as $k => $v) {
                $cleanKey = strtoupper(trim((string) $k));
                $cleanKey = trim($cleanKey, "\xEF\xBB\xBF\'\" \t\n\r\0\x0B");
                $cleanKey = str_replace([' ', '-', '.'], '_', $cleanKey);
                if ($v instanceof \DateTimeInterface) {
                    $rowProperties[$cleanKey] = $v;
                } elseif (is_string($v)) {
                    $rowProperties[$cleanKey] = trim($v, "'\" \t\n\r\0\x0B");
                } else {
                    $rowProperties[$cleanKey] = $v;
                }
            }

            $nip = ! empty($rowProperties['NIP']) ? preg_replace('/[^0-9]/', '', (string) $rowProperties['NIP']) : null;
            if (empty($nip)) {
                return;
            }

            // 1. Simpan atau Ambil Unit Kerja
            $skpd = ! empty($rowProperties['SKPD']) ? trim((string) $rowProperties['SKPD']) : null;
            $upt = ! empty($rowProperties['UPT']) ? trim((string) $rowProperties['UPT']) : null;
            $satker = ! empty($rowProperties['SATKER']) ? trim((string) $rowProperties['SATKER']) : null;

            $unitKerja = UnitKerja::firstOrCreate([
                'skpd' => $skpd,
                'upt' => $upt,
                'satker' => $satker,
            ]);

            // 2. Simpan atau Ambil Jabatan
            $namaJabatan = ! empty($rowProperties['JABATAN']) ? trim((string) $rowProperties['JABATAN']) : null;
            $eselon = ! empty($rowProperties['ESELON']) ? trim((string) $rowProperties['ESELON']) : null;
            $jenisJabatan = ! empty($rowProperties['JENIS_JABATAN']) ? trim((string) $rowProperties['JENIS_JABATAN']) : null;

            $jabatan = Jabatan::firstOrCreate([
                'nama' => $namaJabatan,
                'eselon' => $eselon,
                'jenis' => $jenisJabatan,
            ]);

            // 3. Simpan Pegawai
            // Format Tanggal Lahir (DD-MM-YYYY)
            $tglLahir = null;
            $rawTgl = $rowProperties['TGL_LAHIR'] ?? null;
            if (! empty($rawTgl) && $rawTgl !== '-') {
                if ($rawTgl instanceof \DateTimeInterface) {
                    $tglLahir = $rawTgl->format('Y-m-d');
                } else {
                    $formats = ['d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d', 'd-m-y', 'd/m/y'];
                    foreach ($formats as $fmt) {
                        try {
                            $tglLahir = Carbon::createFromFormat($fmt, (string) $rawTgl)->format('Y-m-d');
                            break;
                        } catch (\Exception $e) {
                        }
                    }
                }
            }

            // TMT Golru & TMT Jabatan
            $tmtGolru = ! empty($rowProperties['TMT_GOLRU']) ? trim((string) $rowProperties['TMT_GOLRU']) : null;
            $tmtJabatan = ! empty($rowProperties['TMT_JABATAN']) && $rowProperties['TMT_JABATAN'] !== '-' ? trim((string) $rowProperties['TMT_JABATAN']) : null;

            // Inferensi Jenis Pegawai
            $jenisPegawai = null;
            if (! empty($rowProperties['JENIS'])) {
                $jenisPegawai = trim((string) $rowProperties['JENIS']);
            } else {
                $jabUpper = strtoupper($namaJabatan ?? '');
                $skpdUpper = strtoupper($skpd ?? '');
                $uptUpper = strtoupper($upt ?? '');
                if (str_contains($jabUpper, 'GURU') || str_contains($jabUpper, 'PENGAWAS SEKOLAH')) {
                    $jenisPegawai = 'GURU';
                } elseif (str_contains($skpdUpper, 'PENDIDIKAN') && (str_contains($uptUpper, 'SMA') || str_contains($uptUpper, 'SMK') || str_contains($uptUpper, 'SLB') || str_contains($uptUpper, 'SEKOLAH'))) {
                    $jenisPegawai = 'TENDIK';
                } elseif (str_contains($skpdUpper, 'KESEHATAN') || str_contains($skpdUpper, 'RUMAH SAKIT') || str_contains($skpdUpper, 'RSUD') || str_contains($jabUpper, 'DOKTER') || str_contains($jabUpper, 'PERAWAT') || str_contains($jabUpper, 'BIDAN') || str_contains($jabUpper, 'APOTEKER')) {
                    $jenisPegawai = 'KESEHATAN';
                } else {
                    $jenisPegawai = 'TEKNIS';
                }
            }

            $updatePayload = [
                'nama' => ! empty($rowProperties['NAMA']) ? trim((string) $rowProperties['NAMA']) : null,
                'tempat_lahir' => ! empty($rowProperties['TEMPAT_LAHIR']) ? trim((string) $rowProperties['TEMPAT_LAHIR']) : null,
                'tgl_lahir' => $tglLahir,
                'jk' => ! empty($rowProperties['JK']) ? trim((string) $rowProperties['JK']) : null,
                'status_pegawai' => ! empty($rowProperties['STATUS']) ? trim((string) $rowProperties['STATUS']) : null,
                'golru' => ! empty($rowProperties['GOLRU']) ? trim((string) $rowProperties['GOLRU']) : null,
                'tmt_golru' => $tmtGolru,
                'tmt_jabatan' => $tmtJabatan,
                'masa_kerja_tahun' => isset($rowProperties['MK_THN']) && is_numeric($rowProperties['MK_THN']) ? (int) $rowProperties['MK_THN'] : null,
                'masa_kerja_bulan' => isset($rowProperties['MK_BLN']) && is_numeric($rowProperties['MK_BLN']) ? (int) $rowProperties['MK_BLN'] : null,
                'tk_ijazah' => ! empty($rowProperties['TK_IJAZAH']) ? trim((string) $rowProperties['TK_IJAZAH']) : null,
                'nm_pendidikan' => ! empty($rowProperties['NM_PENDIDIKAN']) ? trim((string) $rowProperties['NM_PENDIDIKAN']) : null,
                'th_lulus' => isset($rowProperties['TH_LULUS']) && is_numeric($rowProperties['TH_LULUS']) ? (int) $rowProperties['TH_LULUS'] : null,
                'jabatan_id' => $jabatan->id,
                'unit_kerja_id' => $unitKerja->id,
            ];

            if (! empty($rowProperties['AGAMA'])) {
                $updatePayload['agama'] = trim((string) $rowProperties['AGAMA']);
            }

            if (! empty($rowProperties['JENIS'])) {
                $updatePayload['jenis_pegawai'] = $jenisPegawai;
            }

            $pegawai = Pegawai::where('nip', $nip)->first();
            if ($pegawai) {
                $cleanUpdate = array_filter($updatePayload, fn ($val) => $val !== null);
                $pegawai->update($cleanUpdate);
            } else {
                $updatePayload['nip'] = $nip;
                $updatePayload['jenis_pegawai'] = $jenisPegawai;
                Pegawai::create($updatePayload);
            }

            $rowsCount++;
            if ($rowsCount % 100 == 0) {
                $this->info("Berhasil mengimpor $rowsCount data...");
            }
        });

        $this->info("Selesai! Total data yang diimpor: $rowsCount.");
    }
}
