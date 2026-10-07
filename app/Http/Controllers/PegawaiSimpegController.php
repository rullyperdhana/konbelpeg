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
use Illuminate\Support\Facades\Log;
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
     * Proses unggah berkas Excel SIMPEG dan simpan ke server.
     */
    public function upload(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:30720',
            'mode' => 'nullable|string|in:upsert,insert_only',
            'keterangan' => 'nullable|string|max:255',
            'auto_sync' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        $mode = $request->input('mode', 'upsert');
        $keterangan = $request->input('keterangan') ?: 'Unggah Berkas SIMPEG ('.date('d M Y H:i').')';

        $targetDir = storage_path('app/simpeg');
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $cleanFilename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
        $fileId = uniqid('simpeg_');
        $storedName = time().'_'.$cleanFilename;
        $file->move($targetDir, $storedName);
        $fullPath = $targetDir.'/'.$storedName;

        // Perbarui manifest berkas
        $manifest = $this->getManifest();

        // Nonaktifkan status aktif berkas lainnya
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
            'total_rows' => 0,
            'inserted_count' => 0,
            'updated_count' => 0,
            'skipped_count' => 0,
            'status' => 'uploaded',
            'is_active' => true,
        ];

        // Jika auto_sync diminta (misalnya unit test atau pilihan pengguna)
        if ($request->boolean('auto_sync')) {
            $uploadId = $request->input('upload_id', uniqid());
            $result = $this->processImportFile($fullPath, $extension, $mode, $uploadId);
            $manifestItem['total_rows'] = $result['total_rows'];
            $manifestItem['inserted_count'] = $result['inserted_count'];
            $manifestItem['updated_count'] = $result['updated_count'];
            $manifestItem['skipped_count'] = $result['skipped_count'];
            $manifestItem['status'] = 'synced';
            $manifestItem['last_synced_at'] = date('Y-m-d H:i:s');
        }

        array_unshift($manifest, $manifestItem);
        $this->saveManifest($manifest);

        $msg = $request->boolean('auto_sync')
            ? "Impor data pegawai SIMPEG berhasil! Total: {$manifestItem['total_rows']} data."
            : "Berkas '{$originalName}' berhasil diterima dan disimpan di server. Silakan klik tombol 'Proses & Sinkronkan Data' untuk memperbarui database.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'file_id' => $fileId,
                'filename' => $originalName,
                'is_synced' => $request->boolean('auto_sync'),
            ]);
        }

        return redirect()->route('master.pegawai_simpeg.index')->with('success', $msg);
    }

    /**
     * Aktifkan berkas tertentu sebagai berkas aktif.
     */
    public function activate(Request $request, string $id): RedirectResponse
    {
        $manifest = $this->getManifest();
        $targetFound = false;

        foreach ($manifest as &$item) {
            if ($item['id'] === $id) {
                $item['is_active'] = true;
                $targetFound = true;
            } else {
                $item['is_active'] = false;
            }
        }

        if (! $targetFound) {
            return redirect()->back()->with('error', 'Berkas tidak ditemukan.');
        }

        $this->saveManifest($manifest);

        return redirect()->route('master.pegawai_simpeg.index')->with('success', 'Berkas aktif berhasil diubah.');
    }

    /**
     * Impor / sinkronkan data dari berkas yang tersimpan di server ke database.
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

        @ini_set('max_execution_time', 0);
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        try {
            $result = $this->processImportFile($filePath, $extension, $mode, $uploadId);

            // Tandai sebagai file aktif dan perbarui statistik
            foreach ($manifest as &$m) {
                $m['is_active'] = false;
            }
            $manifest[$targetIndex]['is_active'] = true;
            $manifest[$targetIndex]['status'] = 'synced';
            $manifest[$targetIndex]['last_synced_at'] = date('Y-m-d H:i:s');
            $manifest[$targetIndex]['total_rows'] = $result['total_rows'];
            $manifest[$targetIndex]['inserted_count'] = $result['inserted_count'];
            $manifest[$targetIndex]['updated_count'] = $result['updated_count'];
            $manifest[$targetIndex]['skipped_count'] = $result['skipped_count'];
            $this->saveManifest($manifest);

            $msg = "Sinkronisasi berhasil! Total: {$result['total_rows']} data diproses (Baru: {$result['inserted_count']}, Diperbarui: {$result['updated_count']}, Dilewati: {$result['skipped_count']}).";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'result' => $result,
                ]);
            }

            return redirect()->route('master.pegawai_simpeg.index')->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error('SIMPEG Sync Error: '.$e->getMessage(), [
                'file' => $filePath,
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memproses sinkronisasi: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses sinkronisasi: '.$e->getMessage());
        }
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
     * Memproses baris demi baris berkas Excel menggunakan SimpleExcelReader dengan single-pass memory optimization & chunked transactions.
     */
    private function processImportFile(string $filePath, string $extension, string $mode, string $uploadId): array
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        // Set progress awal
        $this->updateProgress($uploadId, 0, 0);

        // Preload master lookup ke memori RAM untuk kecepatan tinggi
        $unitKerjaMap = [];
        foreach (UnitKerja::all() as $uk) {
            $unitKerjaMap["{$uk->skpd}|{$uk->upt}|{$uk->satker}"] = $uk->id;
        }

        $jabatanMap = [];
        foreach (Jabatan::all() as $jab) {
            $jabatanMap["{$jab->nama}|{$jab->eselon}|{$jab->jenis}"] = $jab->id;
        }

        $pegawaiMap = Pegawai::pluck('id', 'nip')->toArray();

        $insertedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $processedCount = 0;

        DB::disableQueryLog();

        $reader = SimpleExcelReader::create($filePath, $extension);

        // Chunk transaksi DB per 250 baris untuk akselerasi performa disk SQLite
        $chunkSize = 250;
        $currentChunkCount = 0;

        DB::beginTransaction();

        try {
            $reader->getRows()->each(function (array $rawRow) use (
                &$insertedCount,
                &$updatedCount,
                &$skippedCount,
                &$processedCount,
                &$unitKerjaMap,
                &$jabatanMap,
                &$pegawaiMap,
                &$currentChunkCount,
                $chunkSize,
                $mode,
                $uploadId
            ) {
                try {
                    // Normalisasi kunci header (Case-insensitive & Trim & safe DateTime handling)
                    $row = [];
                    foreach ($rawRow as $k => $v) {
                        $cleanKey = strtoupper(trim((string) $k));
                        $cleanKey = str_replace([' ', '-', '.'], '_', $cleanKey);
                        if ($v instanceof \DateTimeInterface) {
                            $row[$cleanKey] = $v;
                        } elseif (is_string($v)) {
                            $row[$cleanKey] = trim($v);
                        } else {
                            $row[$cleanKey] = $v;
                        }
                    }

                    $nip = ! empty($row['NIP']) ? trim((string) $row['NIP']) : null;
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

                    $unitKerjaId = null;
                    if ($skpd || $upt || $satker) {
                        $ukKey = "{$skpd}|{$upt}|{$satker}";
                        if (! isset($unitKerjaMap[$ukKey])) {
                            $unitKerja = UnitKerja::firstOrCreate([
                                'skpd' => $skpd,
                                'upt' => $upt,
                                'satker' => $satker,
                            ]);
                            $unitKerjaMap[$ukKey] = $unitKerja->id;
                        }
                        $unitKerjaId = $unitKerjaMap[$ukKey];
                    }

                    // 2. Simpan atau Ambil Jabatan
                    $namaJabatan = ! empty($row['JABATAN']) ? trim((string) $row['JABATAN']) : null;
                    $eselon = ! empty($row['ESELON']) ? trim((string) $row['ESELON']) : null;
                    $jenisJabatan = ! empty($row['JENIS_JABATAN']) ? trim((string) $row['JENIS_JABATAN']) : null;

                    $jabatanId = null;
                    if ($namaJabatan || $eselon || $jenisJabatan) {
                        $jabKey = "{$namaJabatan}|{$eselon}|{$jenisJabatan}";
                        if (! isset($jabatanMap[$jabKey])) {
                            $jabatan = Jabatan::firstOrCreate([
                                'nama' => $namaJabatan,
                                'eselon' => $eselon,
                                'jenis' => $jenisJabatan,
                            ]);
                            $jabatanMap[$jabKey] = $jabatan->id;
                        }
                        $jabatanId = $jabatanMap[$jabKey];
                    }

                    // 3. Format Tanggal Lahir (Mendukung DateTimeInterface, serial date, string formats)
                    $tglLahir = null;
                    $rawTglLahir = $row['TGL_LAHIR'] ?? null;
                    if (! empty($rawTglLahir)) {
                        if ($rawTglLahir instanceof \DateTimeInterface) {
                            $tglLahir = $rawTglLahir->format('Y-m-d');
                        } elseif (is_numeric($rawTglLahir) && (int) $rawTglLahir > 1000) {
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
                                }
                            }
                        }
                    }

                    // Format TMT Golru (bisa DateTimeInterface atau string)
                    $tmtGolru = null;
                    $rawTmtGolru = $row['TMT_GOLRU'] ?? null;
                    if (! empty($rawTmtGolru)) {
                        if ($rawTmtGolru instanceof \DateTimeInterface) {
                            $tmtGolru = $rawTmtGolru->format('d-m-Y');
                        } elseif (is_numeric($rawTmtGolru) && (int) $rawTmtGolru > 1000) {
                            try {
                                $tmtGolru = Carbon::instance(Date::excelToDateTimeObject($rawTmtGolru))->format('d-m-Y');
                            } catch (\Throwable $e) {
                                $tmtGolru = (string) $rawTmtGolru;
                            }
                        } else {
                            $tmtGolru = trim((string) $rawTmtGolru);
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
                        'tmt_golru' => $tmtGolru,
                        'masa_kerja_tahun' => isset($row['MK_THN']) && is_numeric($row['MK_THN']) ? (int) $row['MK_THN'] : null,
                        'masa_kerja_bulan' => isset($row['MK_BLN']) && is_numeric($row['MK_BLN']) ? (int) $row['MK_BLN'] : null,
                        'tk_ijazah' => ! empty($row['TK_IJAZAH']) ? trim((string) $row['TK_IJAZAH']) : null,
                        'nm_pendidikan' => ! empty($row['NM_PENDIDIKAN']) ? trim((string) $row['NM_PENDIDIKAN']) : null,
                        'th_lulus' => isset($row['TH_LULUS']) && is_numeric($row['TH_LULUS']) ? (int) $row['TH_LULUS'] : null,
                        'jabatan_id' => $jabatanId,
                        'unit_kerja_id' => $unitKerjaId,
                        'updated_at' => now(),
                    ];

                    if (isset($pegawaiMap[$nip])) {
                        if ($mode === 'insert_only') {
                            $skippedCount++;
                        } else {
                            $cleanData = array_filter($pegawaiData, fn ($val) => $val !== null);
                            if (! empty($cleanData)) {
                                DB::table('pegawais')->where('nip', $nip)->update($cleanData);
                            }
                            $updatedCount++;
                        }
                    } else {
                        $pegawaiData['nip'] = $nip;
                        $pegawaiData['created_at'] = now();
                        $newId = DB::table('pegawais')->insertGetId($pegawaiData);
                        $pegawaiMap[$nip] = $newId;
                        $insertedCount++;
                    }

                    $processedCount++;
                    $currentChunkCount++;

                    // Commit transaksi bertahap setiap kelipatan chunk agar performa SQLite super cepat
                    if ($currentChunkCount >= $chunkSize) {
                        DB::commit();
                        $this->updateProgress($uploadId, $processedCount, 0);
                        DB::beginTransaction();
                        $currentChunkCount = 0;
                    }
                } catch (\Throwable $rowError) {
                    $skippedCount++;
                    $processedCount++;
                }
            });

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->updateProgress($uploadId, $processedCount, $processedCount);

        return [
            'total_rows' => $processedCount,
            'processed_count' => $processedCount,
            'inserted_count' => $insertedCount,
            'updated_count' => $updatedCount,
            'skipped_count' => $skippedCount,
        ];
    }

    /**
     * Memperbarui progress proses ke cache sistem untuk polling frontend (hanya file cache agar tidak mengunci SQLite).
     */
    private function updateProgress(string $uploadId, int $progress, int $total): void
    {
        $percent = $total > 0 ? min(100, round(($progress / $total) * 100)) : 0;
        $payload = [
            'progress' => $progress,
            'total' => $total,
            'percent' => $percent,
        ];

        try {
            Cache::store('file')->put('upload_progress_'.$uploadId, $payload, 180);
        } catch (\Throwable $e) {
            // Abaikan kegagalan cache minor
        }
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
