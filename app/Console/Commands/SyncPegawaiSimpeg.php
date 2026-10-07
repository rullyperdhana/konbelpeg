<?php

namespace App\Console\Commands;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Spatie\SimpleExcel\SimpleExcelReader;

class SyncPegawaiSimpeg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simpeg:sync {--file= : Path atau nama file Excel di storage/app/simpeg atau root} {--mode=upsert : upsert atau insert_only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data Master Pegawai dari file Excel SIMPEG BKD ke database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->resolveFilePath();

        if (! $filePath || ! file_exists($filePath)) {
            $this->error('File Excel SIMPEG tidak ditemukan! Pastikan file sudah diunggah di web atau letakkan di root project.');

            return Command::FAILURE;
        }

        $mode = $this->option('mode') ?: 'upsert';
        $this->info("Memulai sinkronisasi data SIMPEG dari: {$filePath} (Mode: {$mode})...");

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // 1. Preload lookup tables ke memori RAM
        $this->info('Memuat data master referensi ke memori...');
        $unitKerjaMap = [];
        foreach (UnitKerja::all() as $uk) {
            $unitKerjaMap["{$uk->skpd}|{$uk->upt}|{$uk->satker}"] = $uk->id;
        }

        $jabatanMap = [];
        foreach (Jabatan::all() as $jab) {
            $jabatanMap["{$jab->nama}|{$jab->eselon}|{$jab->jenis}"] = $jab->id;
        }

        $pegawaiMap = Pegawai::pluck('id', 'nip')->toArray();
        $this->info('Referensi siap ('.count($unitKerjaMap).' Unit Kerja, '.count($jabatanMap).' Jabatan, '.count($pegawaiMap).' Pegawai).');

        $insertedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $processedCount = 0;

        $reader = SimpleExcelReader::create($filePath, $extension);
        $chunkSize = 200;
        $chunk = [];

        $reader->getRows()->each(function (array $rawRow) use (
            &$insertedCount,
            &$updatedCount,
            &$skippedCount,
            &$processedCount,
            &$unitKerjaMap,
            &$jabatanMap,
            &$pegawaiMap,
            &$chunk,
            $mode

        ) {
            $row = [];
            foreach ($rawRow as $k => $v) {
                $cleanKey = strtoupper(trim((string) $k));
                $cleanKey = str_replace([' ', '-', '.'], '_', $cleanKey);
                $row[$cleanKey] = is_string($v) ? trim($v) : $v;
            }

            $nip = ! empty($row['NIP']) ? preg_replace('/[^0-9]/', '', (string) $row['NIP']) : null;
            if (empty($nip)) {
                $skippedCount++;
                $processedCount++;

                return;
            }

            // Unit Kerja
            $skpd = ! empty($row['SKPD']) ? trim((string) $row['SKPD']) : null;
            $upt = ! empty($row['UPT']) ? trim((string) $row['UPT']) : null;
            $satker = ! empty($row['SATKER']) ? trim((string) $row['SATKER']) : null;
            $ukKey = "{$skpd}|{$upt}|{$satker}";

            if (! isset($unitKerjaMap[$ukKey])) {
                $uk = UnitKerja::firstOrCreate(['skpd' => $skpd, 'upt' => $upt, 'satker' => $satker]);
                $unitKerjaMap[$ukKey] = $uk->id;
            }
            $unitKerjaId = $unitKerjaMap[$ukKey];

            // Jabatan
            $namaJabatan = ! empty($row['JABATAN']) ? trim((string) $row['JABATAN']) : null;
            $eselon = ! empty($row['ESELON']) ? trim((string) $row['ESELON']) : null;
            $jenisJabatan = ! empty($row['JENIS_JABATAN']) ? trim((string) $row['JENIS_JABATAN']) : null;
            $jabKey = "{$namaJabatan}|{$eselon}|{$jenisJabatan}";

            if (! isset($jabatanMap[$jabKey])) {
                $jab = Jabatan::firstOrCreate(['nama' => $namaJabatan, 'eselon' => $eselon, 'jenis' => $jenisJabatan]);
                $jabatanMap[$jabKey] = $jab->id;
            }
            $jabatanId = $jabatanMap[$jabKey];

            // Tanggal Lahir
            $tglLahir = null;
            $rawTglLahir = $row['TGL_LAHIR'] ?? null;
            if (! empty($rawTglLahir)) {
                if (is_numeric($rawTglLahir) && (int) $rawTglLahir > 1000) {
                    try {
                        $tglLahir = Carbon::instance(Date::excelToDateTimeObject($rawTglLahir))->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $tglLahir = null;
                    }
                } else {
                    $formats = ['d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d'];
                    foreach ($formats as $fmt) {
                        try {
                            $tglLahir = Carbon::createFromFormat($fmt, (string) $rawTglLahir)->format('Y-m-d');
                            break;
                        } catch (\Throwable $e) {
                        }
                    }
                }
            }

            $pegawaiData = [
                'nama' => ! empty($row['NAMA']) ? trim((string) $row['NAMA']) : null,
                'tempat_lahir' => ! empty($row['TEMPAT_LAHIR']) ? trim((string) $row['TEMPAT_LAHIR']) : null,
                'tgl_lahir' => $tglLahir,
                'jk' => ! empty($row['JK']) ? trim((string) $row['JK']) : null,
                'agama' => ! empty($row['AGAMA']) ? trim((string) $row['AGAMA']) : null,
                'status_pegawai' => ! empty($row['STATUS']) ? trim((string) $row['STATUS']) : null,
                'golru' => ! empty($row['GOLRU']) ? trim((string) $row['GOLRU']) : null,
                'tmt_golru' => ! empty($row['TMT_GOLRU']) ? trim((string) $row['TMT_GOLRU']) : null,
                'masa_kerja_tahun' => isset($row['MK_THN']) && is_numeric($row['MK_THN']) ? (int) $row['MK_THN'] : null,
                'masa_kerja_bulan' => isset($row['MK_BLN']) && is_numeric($row['MK_BLN']) ? (int) $row['MK_BLN'] : null,
                'tk_ijazah' => ! empty($row['TK_IJAZAH']) ? trim((string) $row['TK_IJAZAH']) : null,
                'nm_pendidikan' => ! empty($row['NM_PENDIDIKAN']) ? trim((string) $row['NM_PENDIDIKAN']) : null,
                'th_lulus' => isset($row['TH_LULUS']) && is_numeric($row['TH_LULUS']) ? (int) $row['TH_LULUS'] : null,
                'jabatan_id' => $jabatanId,
                'unit_kerja_id' => $unitKerjaId,
            ];

            if (isset($pegawaiMap[$nip])) {
                if ($mode === 'insert_only') {
                    $skippedCount++;
                } else {
                    $cleanData = array_filter($pegawaiData, fn ($v) => $v !== null);
                    if (! empty($cleanData)) {
                        DB::table('pegawais')->where('nip', $nip)->update($cleanData);
                    }
                    $updatedCount++;
                }
            } else {
                $pegawaiData['nip'] = $nip;
                $newId = DB::table('pegawais')->insertGetId($pegawaiData);
                $pegawaiMap[$nip] = $newId;
                $insertedCount++;
            }

            $processedCount++;
            if ($processedCount % 500 === 0) {
                $this->info("Telah memproses {$processedCount} baris (Baru: {$insertedCount}, Diperbarui: {$updatedCount})...");
            }
        });

        $this->info("Selesai! Total: {$processedCount} baris diproses (Baru: {$insertedCount}, Diperbarui: {$updatedCount}, Dilewati: {$skippedCount}).");

        return Command::SUCCESS;
    }

    /**
     * Cari lokasi file Excel target dari opsi atau manifest aktif.
     */
    private function resolveFilePath(): ?string
    {
        $opt = $this->option('file');
        if ($opt) {
            if (file_exists($opt)) {
                return $opt;
            }
            if (file_exists(storage_path('app/simpeg/'.$opt))) {
                return storage_path('app/simpeg/'.$opt);
            }
            if (file_exists(base_path($opt))) {
                return base_path($opt);
            }
        }

        // Cari file aktif dari manifest
        $manifestPath = storage_path('app/simpeg/manifest.json');
        if (file_exists($manifestPath)) {
            $files = json_decode(file_get_contents($manifestPath), true) ?: [];
            foreach ($files as $f) {
                if (! empty($f['is_active'])) {
                    $p = storage_path('app/simpeg/'.$f['filename']);
                    if (file_exists($p)) {
                        return $p;
                    }
                }
            }
            if (! empty($files[0]['filename'])) {
                $p = storage_path('app/simpeg/'.$files[0]['filename']);
                if (file_exists($p)) {
                    return $p;
                }
            }
        }

        // Fallback ke file bawaan di root
        $defaultFile = base_path('data pegawai Pemprov tmt 1 September 2026.xlsx');
        if (file_exists($defaultFile)) {
            return $defaultFile;
        }

        return null;
    }
}
