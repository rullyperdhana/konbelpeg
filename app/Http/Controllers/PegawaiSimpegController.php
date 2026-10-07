<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\SimpleExcel\SimpleExcelReader;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PegawaiSimpegController extends Controller
{
    /**
     * Tampilkan halaman manajemen dan unggah berkas Pegawai SIMPEG.
     */
    public function index(): View
    {
        $manifest = $this->getManifest();

        $totalPegawai = Pegawai::count();
        $totalPns = Pegawai::where('status_pegawai', 'like', '%PNS%')->count();
        $totalPppk = Pegawai::where('status_pegawai', 'like', '%PPPK%')
            ->where('status_pegawai', 'not like', '%PARUH%')
            ->count();
        $totalParuhWaktu = Pegawai::where('status_pegawai', 'like', '%PARUH%')->count();
        $totalSkpd = UnitKerja::whereNotNull('skpd')->distinct('skpd')->count('skpd');
        $totalJabatan = Jabatan::count();

        // Cari berkas yang aktif / terakhir diunggah
        $activeFile = null;
        if (! empty($manifest)) {
            $activeFiles = array_filter($manifest, fn ($f) => ! empty($f['is_active']));
            $activeFile = ! empty($activeFiles) ? reset($activeFiles) : $manifest[0];
        }

        return view('master.pegawai_simpeg', [
            'files' => $manifest,
            'activeFile' => $activeFile,
            'totalPegawai' => $totalPegawai,
            'totalPns' => $totalPns,
            'totalPppk' => $totalPppk,
            'totalParuhWaktu' => $totalParuhWaktu,
            'totalSkpd' => $totalSkpd,
            'totalJabatan' => $totalJabatan,
        ]);
    }

    /**
     * Proses unggah berkas Excel SIMPEG dan lakukan impor data ke database.
     */
    public function upload(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:30720',
            'mode' => 'nullable|string|in:upsert,insert_only',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        $mode = $request->input('mode', 'upsert');
        $keterangan = $request->input('keterangan') ?: 'Unggah Berkas SIMPEG ('.date('d M Y H:i').')';
        $uploadId = $request->input('upload_id', uniqid());

        $targetDir = storage_path('app/simpeg');
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $cleanFilename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
        $fileId = uniqid('simpeg_');
        $storedName = time().'_'.$cleanFilename;
        $file->move($targetDir, $storedName);
        $fullPath = $targetDir.'/'.$storedName;

        // Jalankan proses impor
        $result = $this->processImportFile($fullPath, $extension, $mode, $uploadId);

        // Perbarui manifest berkas
        $manifest = $this->getManifest();

        // Nonaktifkan status aktif berkas lainnya jika berkas baru berhasil diimpor
        foreach ($manifest as &$m) {
            $m['is_active'] = false;
        }

        $manifestItem = [
            'id' => $fileId,
            'filename' => $storedName,
            'original_name' => $originalName,
            'uploaded_at' => date('Y-m-d H:i:s'),
            'file_size' => filesize($fullPath),
            'keterangan' => $keterangan,
            'mode' => $mode,
            'total_rows' => $result['total_rows'],
            'inserted_count' => $result['inserted_count'],
            'updated_count' => $result['updated_count'],
            'skipped_count' => $result['skipped_count'],
            'is_active' => true,
        ];

        array_unshift($manifest, $manifestItem);
        $this->saveManifest($manifest);

        $msg = "Impor data pegawai SIMPEG berhasil! Total: {$result['total_rows']} data (Baru: {$result['inserted_count']}, Diperbarui: {$result['updated_count']}).";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'result' => $result,
            ]);
        }

        return redirect()->route('master.pegawai_simpeg.index')->with('success', $msg);
    }

    /**
     * Impor ulang data dari berkas yang tersimpan di server.
     */
    public function sync(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $manifest = $this->getManifest();
        $targetFile = null;
        $targetIndex = null;

        foreach ($manifest as $index => $item) {
            if ($item['id'] === $id) {
                $targetFile = $item;
                $targetIndex = $index;
                break;
            }
        }

        if (! $targetFile) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Berkas tidak ditemukan dalam daftar riwayat.'], 404);
            }

            return redirect()->back()->with('error', 'Berkas tidak ditemukan dalam daftar riwayat.');
        }

        $filePath = storage_path('app/simpeg/'.$targetFile['filename']);
        if (! file_exists($filePath)) {
            // Cek apakah ada di root project
            if (file_exists(base_path($targetFile['filename']))) {
                $filePath = base_path($targetFile['filename']);
            } else {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'File fisik berkas tidak ditemukan di server.'], 404);
                }

                return redirect()->back()->with('error', 'File fisik berkas tidak ditemukan di server.');
            }
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mode = $request->input('mode', $targetFile['mode'] ?? 'upsert');
        $uploadId = $request->input('upload_id', uniqid());

        $result = $this->processImportFile($filePath, $extension, $mode, $uploadId);

        // Tandai sebagai file aktif
        foreach ($manifest as &$m) {
            $m['is_active'] = false;
        }
        $manifest[$targetIndex]['is_active'] = true;
        $manifest[$targetIndex]['last_synced_at'] = date('Y-m-d H:i:s');
        $manifest[$targetIndex]['inserted_count'] = $result['inserted_count'];
        $manifest[$targetIndex]['updated_count'] = $result['updated_count'];
        $manifest[$targetIndex]['skipped_count'] = $result['skipped_count'];
        $this->saveManifest($manifest);

        $msg = "Sinkronisasi ulang berhasil! Total: {$result['total_rows']} data (Baru: {$result['inserted_count']}, Diperbarui: {$result['updated_count']}).";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'result' => $result,
            ]);
        }

        return redirect()->route('master.pegawai_simpeg.index')->with('success', $msg);
    }

    /**
     * Hapus berkas dari riwayat dan disk server.
     */
    public function destroy(string $id): RedirectResponse
    {
        $manifest = $this->getManifest();
        $newManifest = [];

        foreach ($manifest as $item) {
            if ($item['id'] === $id) {
                $filePath = storage_path('app/simpeg/'.$item['filename']);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            } else {
                $newManifest[] = $item;
            }
        }

        $this->saveManifest($newManifest);

        return redirect()->route('master.pegawai_simpeg.index')->with('success', 'Berkas berhasil dihapus dari riwayat.');
    }

    /**
     * Download berkas template Excel untuk impor data SIMPEG.
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Pegawai SIMPEG');

        // Headers
        $headers = [
            'NIP', 'NAMA', 'STATUS', 'GOLRU', 'TMT_GOLRU',
            'SKPD', 'UPT', 'SATKER',
            'JABATAN', 'ESELON', 'JENIS_JABATAN',
            'TEMPAT_LAHIR', 'TGL_LAHIR', 'JK', 'AGAMA',
            'MK_THN', 'MK_BLN', 'TK_IJAZAH', 'NM_PENDIDIKAN', 'TH_LULUS',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}1", $header);
            $col++;
        }

        // Style header baris 1
        $sheet->getStyle('A1:T1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1D4ED8'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Baris Contoh 1 (PNS)
        $sampleRow1 = [
            '198501152010011005', 'Drs. AHMAD FAUZI, M.Si', 'PNS', 'IV/a', '01-04-2022',
            'BADAN PENGELOLAAN KEUANGAN DAN ASET DAERAH', 'Sekretariat BPKAD', 'Sub Bagian Perencanaan',
            'Kepala Sub Bagian Perencanaan', 'IV.a', 'Struktural',
            'Banjarmasin', '15-01-1985', 'L', 'Islam',
            '14', '0', 'S2', 'Magister Sains Manajemen', '2015',
        ];

        // Baris Contoh 2 (PPPK)
        $sampleRow2 = [
            '199208202022212008', 'SITI RAHMAWATI, S.Pd', 'PPPK', 'IX', '01-02-2022',
            'DINAS PENDIDIKAN', 'SMAN 1 BANJARMASIN', 'Guru Matematika',
            'Ahli Pertama - Guru Matematika', '-', 'Fungsional',
            'Martapura', '20-08-1992', 'P', 'Islam',
            '2', '0', 'S1', 'Pendidikan Matematika', '2016',
        ];

        $col = 'A';
        foreach ($sampleRow1 as $val) {
            $sheet->setCellValueExplicit("{$col}2", $val, DataType::TYPE_STRING);
            $col++;
        }

        $col = 'A';
        foreach ($sampleRow2 as $val) {
            $sheet->setCellValueExplicit("{$col}3", $val, DataType::TYPE_STRING);
            $col++;
        }

        // Auto width untuk seluruh kolom
        foreach (range('A', 'T') as $colId) {
            $sheet->getColumnDimension($colId)->setAutoSize(true);
        }

        $tempPath = storage_path('app/template_import_pegawai_simpeg.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, 'Template_Import_Pegawai_SIMPEG.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Memproses baris demi baris berkas Excel menggunakan SimpleExcelReader.
     */
    private function processImportFile(string $filePath, string $extension, string $mode, string $uploadId): array
    {
        // Hitung total baris terlebih dahulu
        $totalRows = 0;
        $counterReader = SimpleExcelReader::create($filePath, $extension);
        $counterReader->getRows()->each(function () use (&$totalRows) {
            $totalRows++;
        });

        // Set progress awal
        $this->updateProgress($uploadId, 0, $totalRows);

        $reader = SimpleExcelReader::create($filePath, $extension);

        $insertedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $processedCount = 0;

        // Cache UnitKerja & Jabatan di memori untuk akselerasi eksekusi
        $unitKerjaCache = [];
        $jabatanCache = [];

        DB::beginTransaction();

        try {
            $reader->getRows()->each(function (array $rawRow) use (
                &$insertedCount,
                &$updatedCount,
                &$skippedCount,
                &$processedCount,
                &$unitKerjaCache,
                &$jabatanCache,
                $mode,
                $uploadId,
                $totalRows
            ) {
                // Normalisasi kunci header (Case-insensitive & Trim)
                $row = [];
                foreach ($rawRow as $k => $v) {
                    $cleanKey = strtoupper(trim((string) $k));
                    $cleanKey = str_replace([' ', '-', '.'], '_', $cleanKey);
                    $row[$cleanKey] = is_string($v) ? trim($v) : $v;
                }

                $nip = ! empty($row['NIP']) ? trim((string) $row['NIP']) : null;
                // Bersihkan karakter non-digit/kutip pada NIP
                if ($nip) {
                    $nip = preg_replace('/[^0-9]/', '', $nip);
                }

                if (empty($nip)) {
                    $skippedCount++;
                    $processedCount++;

                    return;
                }

                // 1. Simpan atau Ambil Unit Kerja
                $skpd = ! empty($row['SKPD']) ? trim((string) $row['SKPD']) : null;
                $upt = ! empty($row['UPT']) ? trim((string) $row['UPT']) : null;
                $satker = ! empty($row['SATKER']) ? trim((string) $row['SATKER']) : null;

                $ukKey = "{$skpd}|{$upt}|{$satker}";
                if (! isset($unitKerjaCache[$ukKey])) {
                    $unitKerja = UnitKerja::firstOrCreate([
                        'skpd' => $skpd,
                        'upt' => $upt,
                        'satker' => $satker,
                    ]);
                    $unitKerjaCache[$ukKey] = $unitKerja->id;
                }
                $unitKerjaId = $unitKerjaCache[$ukKey];

                // 2. Simpan atau Ambil Jabatan
                $namaJabatan = ! empty($row['JABATAN']) ? trim((string) $row['JABATAN']) : null;
                $eselon = ! empty($row['ESELON']) ? trim((string) $row['ESELON']) : null;
                $jenisJabatan = ! empty($row['JENIS_JABATAN']) ? trim((string) $row['JENIS_JABATAN']) : null;

                $jabKey = "{$namaJabatan}|{$eselon}|{$jenisJabatan}";
                if (! isset($jabatanCache[$jabKey])) {
                    $jabatan = Jabatan::firstOrCreate([
                        'nama' => $namaJabatan,
                        'eselon' => $eselon,
                        'jenis' => $jenisJabatan,
                    ]);
                    $jabatanCache[$jabKey] = $jabatan->id;
                }
                $jabatanId = $jabatanCache[$jabKey];

                // 3. Format Tanggal Lahir (Mendukung DD-MM-YYYY, YYYY-MM-DD, Excel Serial Date)
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
                        $formats = ['d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d', 'd-m-y', 'd/m/y'];
                        foreach ($formats as $fmt) {
                            try {
                                $tglLahir = Carbon::createFromFormat($fmt, (string) $rawTglLahir)->format('Y-m-d');
                                break;
                            } catch (\Throwable $e) {
                                // Coba format berikutnya
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

                // Cek keberadaan pegawai
                $existingPegawai = Pegawai::where('nip', $nip)->first();

                if ($existingPegawai) {
                    if ($mode === 'insert_only') {
                        $skippedCount++;
                    } else {
                        // Perbarui data pegawai, jaga agar data finansial SIMGAJI tidak tertimpa kosong
                        $existingPegawai->update(array_filter($pegawaiData, fn ($val) => $val !== null));
                        $updatedCount++;
                    }
                } else {
                    $pegawaiData['nip'] = $nip;
                    Pegawai::create($pegawaiData);
                    $insertedCount++;
                }

                $processedCount++;

                if ($processedCount % 50 === 0 || $processedCount === $totalRows) {
                    $this->updateProgress($uploadId, $processedCount, $totalRows);
                }
            });

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->updateProgress($uploadId, $totalRows, $totalRows);

        return [
            'total_rows' => $totalRows,
            'processed_count' => $processedCount,
            'inserted_count' => $insertedCount,
            'updated_count' => $updatedCount,
            'skipped_count' => $skippedCount,
        ];
    }

    /**
     * Memperbarui progress proses ke cache sistem untuk polling frontend.
     */
    private function updateProgress(string $uploadId, int $progress, int $total): void
    {
        $percent = $total > 0 ? min(100, round(($progress / $total) * 100)) : 0;
        $payload = [
            'progress' => $progress,
            'total' => $total,
            'percent' => $percent,
        ];

        Cache::store('file')->put('upload_progress_'.$uploadId, $payload, 180);
        Cache::put('upload_progress_'.$uploadId, $payload, 180);
    }

    /**
     * Membaca daftar riwayat berkas dari storage_path('app/simpeg/manifest.json').
     */
    private function getManifest(): array
    {
        $manifestPath = storage_path('app/simpeg/manifest.json');
        if (! file_exists($manifestPath)) {
            // Cek apakah berkas bawaan ada di root
            $defaultFile = 'data pegawai Pemprov tmt 1 September 2026.xlsx';
            if (file_exists(base_path($defaultFile))) {
                return [[
                    'id' => 'default_initial_simpeg',
                    'filename' => $defaultFile,
                    'original_name' => $defaultFile,
                    'uploaded_at' => date('Y-m-d H:i:s', filemtime(base_path($defaultFile))),
                    'file_size' => filesize(base_path($defaultFile)),
                    'keterangan' => 'Master Pegawai Bawaan Awal (1 September 2026)',
                    'mode' => 'upsert',
                    'total_rows' => Pegawai::count(),
                    'inserted_count' => Pegawai::count(),
                    'updated_count' => 0,
                    'skipped_count' => 0,
                    'is_active' => true,
                ]];
            }

            return [];
        }

        $data = json_decode(file_get_contents($manifestPath), true);

        return is_array($data) ? $data : [];
    }

    /**
     * Menyimpan data riwayat berkas ke storage_path('app/simpeg/manifest.json').
     */
    private function saveManifest(array $manifest): void
    {
        $targetDir = storage_path('app/simpeg');
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        file_put_contents($targetDir.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
    }
}
