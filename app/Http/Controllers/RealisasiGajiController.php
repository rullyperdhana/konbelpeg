<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\UnitKerja;
use App\Models\UnmatchedNip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\SimpleExcel\SimpleExcelReader;
use XBase\TableReader;

class RealisasiGajiController extends Controller
{
    private function getRekapData($periode, $skpdFilter = null)
    {
        $kesehatanCond = "pegawais.jenis_pegawai = 'KESEHATAN'";
        $guruCond = "pegawais.jenis_pegawai IN ('GURU', 'TENDIK')";

        $query = DB::table('pegawais')
            ->join('unit_kerjas', 'pegawais.unit_kerja_id', '=', 'unit_kerjas.id')
            ->leftJoin('realisasi_gajis', function ($join) use ($periode) {
                $join->on('pegawais.id', '=', 'realisasi_gajis.pegawai_id');
                if ($periode) {
                    $join->where('realisasi_gajis.periode', '=', $periode);
                }
            })
            ->select(
                'unit_kerjas.skpd',
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PNS' AND realisasi_gajis.id IS NOT NULL THEN 1 ELSE 0 END) as count_pns"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND ($guruCond) AND realisasi_gajis.id IS NOT NULL THEN 1 ELSE 0 END) as count_pppk_guru"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND NOT ($guruCond) AND ($kesehatanCond) AND realisasi_gajis.id IS NOT NULL THEN 1 ELSE 0 END) as count_pppk_kes"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND NOT ($guruCond) AND NOT ($kesehatanCond) AND realisasi_gajis.id IS NOT NULL THEN 1 ELSE 0 END) as count_pppk_teknis"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND ($guruCond) AND realisasi_gajis.id IS NOT NULL THEN 1 ELSE 0 END) as count_paruh_guru"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND ($kesehatanCond) AND realisasi_gajis.id IS NOT NULL THEN 1 ELSE 0 END) as count_paruh_kes"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND NOT ($kesehatanCond) AND realisasi_gajis.id IS NOT NULL THEN 1 ELSE 0 END) as count_paruh_teknis"),
                DB::raw('SUM(CASE WHEN realisasi_gajis.id IS NOT NULL THEN 1 ELSE 0 END) as count_total'),

                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PNS' AND realisasi_gajis.id IS NULL THEN 1 ELSE 0 END) as count_belum_dibayar_pns"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND realisasi_gajis.id IS NULL THEN 1 ELSE 0 END) as count_belum_dibayar_pppk"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND realisasi_gajis.id IS NULL THEN 1 ELSE 0 END) as count_belum_dibayar_paruh"),
                DB::raw('SUM(CASE WHEN realisasi_gajis.id IS NULL THEN 1 ELSE 0 END) as count_belum_dibayar_total'),

                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PNS' THEN realisasi_gajis.gaji_bersih ELSE 0 END) as nominal_pns"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND ($guruCond) THEN realisasi_gajis.gaji_bersih ELSE 0 END) as nominal_pppk_guru"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND NOT ($guruCond) AND ($kesehatanCond) THEN realisasi_gajis.gaji_bersih ELSE 0 END) as nominal_pppk_kes"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND NOT ($guruCond) AND NOT ($kesehatanCond) THEN realisasi_gajis.gaji_bersih ELSE 0 END) as nominal_pppk_teknis"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND ($guruCond) THEN realisasi_gajis.gaji_bersih ELSE 0 END) as nominal_paruh_guru"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND ($kesehatanCond) THEN realisasi_gajis.gaji_bersih ELSE 0 END) as nominal_paruh_kes"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND NOT ($kesehatanCond) THEN realisasi_gajis.gaji_bersih ELSE 0 END) as nominal_paruh_teknis"),
                DB::raw('SUM(realisasi_gajis.gaji_bersih) as nominal_total')
            );

        if ($skpdFilter) {
            $query->where('unit_kerjas.skpd', $skpdFilter);
        }

        return $query->groupBy('unit_kerjas.skpd')->orderBy('unit_kerjas.skpd')->get();
    }

    public function index(Request $request)
    {
        $tipeLaporan = $request->get('tipe_laporan', 'rekap');
        $periode = $request->get('periode_filter');
        $skpdFilter = $request->get('skpd_filter');

        $periodes = RealisasiGaji::select('periode')->distinct()->orderBy('periode', 'desc')->pluck('periode');
        $filterUnitKerjas = UnitKerja::whereNotNull('skpd')->pluck('skpd')->unique()->sort()->values();

        // Calculate Grand Totals across all filtered data
        $gajiQuery = RealisasiGaji::query();
        if ($periode) {
            $gajiQuery->where('periode', $periode);
        }
        if ($skpdFilter) {
            $gajiQuery->whereHas('pegawai.unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
        }
        $totalGajiPokok = $gajiQuery->sum('gaji_pokok');
        $totalGajiBersih = $gajiQuery->sum('gaji_bersih');

        if ($tipeLaporan == 'rekap') {
            $rekaps = $this->getRekapData($periode, $skpdFilter);

            return view('realisasi.gaji.index', compact('tipeLaporan', 'rekaps', 'periodes', 'filterUnitKerjas', 'totalGajiPokok', 'totalGajiBersih'));
        } else {
            $query = Pegawai::with(['unitKerja', 'jabatan', 'realisasiGajis' => function ($q) use ($periode) {
                if ($periode) {
                    $q->where('periode', $periode);
                }
            }]);

            if ($skpdFilter) {
                $query->whereHas('unitKerja', function ($q) use ($skpdFilter) {
                    $q->where('skpd', $skpdFilter);
                });
            }

            $realisasis = $query->paginate(50);

            return view('realisasi.gaji.index', compact('tipeLaporan', 'realisasis', 'periodes', 'filterUnitKerjas', 'totalGajiPokok', 'totalGajiBersih', 'periode'));
        }
    }

    public function exportPdf(Request $request)
    {
        $tipeLaporan = $request->get('tipe_laporan', 'rekap');
        $periode = $request->get('periode_filter');
        $skpdFilter = $request->get('skpd_filter');

        if ($tipeLaporan == 'rekap') {
            $rekaps = $this->getRekapData($periode, $skpdFilter);
            $pdf = Pdf::loadView('realisasi.gaji.pdf', compact('tipeLaporan', 'rekaps', 'periode', 'skpdFilter'))
                ->setPaper('a4', 'landscape');
        } else {
            $query = Pegawai::with(['unitKerja', 'jabatan', 'realisasiGajis' => function ($q) use ($periode) {
                if ($periode) {
                    $q->where('periode', $periode);
                }
            }]);

            if ($skpdFilter) {
                $query->whereHas('unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
            }
            $realisasis = $query->get();
            $pdf = Pdf::loadView('realisasi.gaji.pdf', compact('tipeLaporan', 'realisasis', 'periode', 'skpdFilter'))
                ->setPaper('a4', 'landscape');
        }

        return $pdf->download('Laporan_Realisasi_Gaji.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tipeLaporan = $request->get('tipe_laporan', 'rekap');
        $periode = $request->get('periode_filter', 'Semua Periode');
        $skpdFilter = $request->get('skpd_filter');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if ($tipeLaporan == 'rekap') {
            $rekaps = $this->getRekapData($periode == 'Semua Periode' ? null : $periode, $skpdFilter);

            // Title
            $sheet->mergeCells('A1:J1');
            $sheet->setCellValue('A1', 'REKAPITULASI REALISASI GAJI');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A2:J2');
            $sheet->setCellValue('A2', 'PERIODE: '.$periode);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Headers
            $sheet->mergeCells('A4:A5');
            $sheet->setCellValue('A4', 'NO');
            $sheet->mergeCells('B4:B5');
            $sheet->setCellValue('B4', 'UNIT KERJA');

            $sheet->mergeCells('C4:J4');
            $sheet->setCellValue('C4', 'JUMLAH PEGAWAI (YANG DIBAYARKAN GAJI)');
            $sheet->setCellValue('C5', 'PNS');
            $sheet->setCellValue('D5', 'PPPK GURU DAN TENAGA KEPENDIDIKAN');
            $sheet->setCellValue('E5', 'PPPK TENAGA KESEHATAN');
            $sheet->setCellValue('F5', 'PPPK TENAGA TEKNIS');
            $sheet->setCellValue('G5', 'PPPK PARUH WAKTU GURU DAN TENAGA KEPENDIDIKAN');
            $sheet->setCellValue('H5', 'PPPK PARUH WAKTU TENAGA KESEHATAN');
            $sheet->setCellValue('I5', 'PPPK PARUH WAKTU TENAGA TEKNIS');
            $sheet->setCellValue('J5', 'JUMLAH PEGAWAI');

            $sheet->mergeCells('K4:N4');
            $sheet->setCellValue('K4', 'BELUM DIBAYAR');
            $sheet->setCellValue('K5', 'PNS');
            $sheet->setCellValue('L5', 'PPPK');
            $sheet->setCellValue('M5', 'PARUH WAKTU');
            $sheet->setCellValue('N5', 'TOTAL');

            $sheet->mergeCells('O4:V4');
            $sheet->setCellValue('O4', 'TOTAL REALISASI GAJI');
            $sheet->setCellValue('O5', 'PNS');
            $sheet->setCellValue('P5', 'PPPK GURU DAN TENAGA KEPENDIDIKAN');
            $sheet->setCellValue('Q5', 'PPPK TENAGA KESEHATAN');
            $sheet->setCellValue('R5', 'PPPK TENAGA TEKNIS');
            $sheet->setCellValue('S5', 'PPPK PARUH WAKTU GURU DAN TENAGA KEPENDIDIKAN');
            $sheet->setCellValue('T5', 'PPPK PARUH WAKTU TENAGA KESEHATAN');
            $sheet->setCellValue('U5', 'PPPK PARUH WAKTU TENAGA TEKNIS');
            $sheet->setCellValue('V5', 'JUMLAH GAJI');

            $headerStyle = [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'],
                ],
            ];
            $sheet->getStyle('A4:V5')->applyFromArray($headerStyle);

            foreach (range('A', 'V') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $row = 6;
            $no = 1;
            foreach ($rekaps as $rekap) {
                $sheet->setCellValue('A'.$row, $no++);
                $sheet->setCellValue('B'.$row, $rekap->skpd);

                $sheet->setCellValue('C'.$row, $rekap->count_pns);
                $sheet->setCellValue('D'.$row, $rekap->count_pppk_guru);
                $sheet->setCellValue('E'.$row, $rekap->count_pppk_kes);
                $sheet->setCellValue('F'.$row, $rekap->count_pppk_teknis);
                $sheet->setCellValue('G'.$row, $rekap->count_paruh_guru);
                $sheet->setCellValue('H'.$row, $rekap->count_paruh_kes);
                $sheet->setCellValue('I'.$row, $rekap->count_paruh_teknis);
                $sheet->setCellValue('J'.$row, $rekap->count_total);

                $sheet->setCellValue('K'.$row, $rekap->count_belum_dibayar_pns);
                $sheet->setCellValue('L'.$row, $rekap->count_belum_dibayar_pppk);
                $sheet->setCellValue('M'.$row, $rekap->count_belum_dibayar_paruh);
                $sheet->setCellValue('N'.$row, $rekap->count_belum_dibayar_total);

                $sheet->setCellValue('O'.$row, $rekap->nominal_pns);
                $sheet->setCellValue('P'.$row, $rekap->nominal_pppk_guru);
                $sheet->setCellValue('Q'.$row, $rekap->nominal_pppk_kes);
                $sheet->setCellValue('R'.$row, $rekap->nominal_pppk_teknis);
                $sheet->setCellValue('S'.$row, $rekap->nominal_paruh_guru);
                $sheet->setCellValue('T'.$row, $rekap->nominal_paruh_kes);
                $sheet->setCellValue('U'.$row, $rekap->nominal_paruh_teknis);
                $sheet->setCellValue('V'.$row, $rekap->nominal_total);

                $row++;
            }

            // TOTAL ROW
            $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->setCellValue('A'.$row, 'TOTAL KESELURUHAN');
            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('C'.$row, $rekaps->sum('count_pns'));
            $sheet->setCellValue('D'.$row, $rekaps->sum('count_pppk_guru'));
            $sheet->setCellValue('E'.$row, $rekaps->sum('count_pppk_kes'));
            $sheet->setCellValue('F'.$row, $rekaps->sum('count_pppk_teknis'));
            $sheet->setCellValue('G'.$row, $rekaps->sum('count_paruh_guru'));
            $sheet->setCellValue('H'.$row, $rekaps->sum('count_paruh_kes'));
            $sheet->setCellValue('I'.$row, $rekaps->sum('count_paruh_teknis'));
            $sheet->setCellValue('J'.$row, $rekaps->sum('count_total'));

            $sheet->setCellValue('K'.$row, $rekaps->sum('count_belum_dibayar_pns'));
            $sheet->setCellValue('L'.$row, $rekaps->sum('count_belum_dibayar_pppk'));
            $sheet->setCellValue('M'.$row, $rekaps->sum('count_belum_dibayar_paruh'));
            $sheet->setCellValue('N'.$row, $rekaps->sum('count_belum_dibayar_total'));

            $sheet->setCellValue('O'.$row, $rekaps->sum('nominal_pns'));
            $sheet->setCellValue('P'.$row, $rekaps->sum('nominal_pppk_guru'));
            $sheet->setCellValue('Q'.$row, $rekaps->sum('nominal_pppk_kes'));
            $sheet->setCellValue('R'.$row, $rekaps->sum('nominal_pppk_teknis'));
            $sheet->setCellValue('S'.$row, $rekaps->sum('nominal_paruh_guru'));
            $sheet->setCellValue('T'.$row, $rekaps->sum('nominal_paruh_kes'));
            $sheet->setCellValue('U'.$row, $rekaps->sum('nominal_paruh_teknis'));
            $sheet->setCellValue('V'.$row, $rekaps->sum('nominal_total'));

            $sheet->getStyle('A'.$row.':V'.$row)->getFont()->setBold(true);
            $sheet->getStyle('A'.$row.':V'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

            $sheet->getStyle('A6:V'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('O6:V'.$row)->getNumberFormat()->setFormatCode('#,##0');

        } else {
            // Rinci
            $query = Pegawai::with(['unitKerja', 'jabatan', 'realisasiGajis' => function ($q) use ($periode) {
                if ($periode != 'Semua Periode') {
                    $q->where('periode', $periode);
                }
            }]);
            if ($skpdFilter) {
                $query->whereHas('unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
            }
            $pegawais = $query->get();

            $sheet->setCellValue('A1', 'NO');
            $sheet->setCellValue('B1', 'PERIODE');
            $sheet->setCellValue('C1', 'NIP');
            $sheet->setCellValue('D1', 'NAMA');
            $sheet->setCellValue('E1', 'STATUS');
            $sheet->setCellValue('F1', 'UNIT KERJA');
            $sheet->setCellValue('G1', 'GAJI POKOK');
            $sheet->setCellValue('H1', 'TOTAL DIBAYARKAN (BERSIH)');

            $sheet->getStyle('A1:H1')->getFont()->setBold(true);

            $row = 2;
            $no = 1;
            foreach ($pegawais as $pegawai) {
                $gaji = $pegawai->realisasiGajis->first();
                $sheet->setCellValue('A'.$row, $no++);
                $sheet->setCellValue('B'.$row, $periode);
                $sheet->setCellValueExplicit('C'.$row, $pegawai->nip ?? '-', DataType::TYPE_STRING);
                $sheet->setCellValue('D'.$row, $pegawai->nama ?? '-');
                $sheet->setCellValue('E'.$row, $pegawai->status_pegawai ?? '-');
                $sheet->setCellValue('F'.$row, $pegawai->unitKerja?->skpd ?? '-');
                $sheet->setCellValue('G'.$row, $gaji ? $gaji->gaji_pokok : 0);
                $sheet->setCellValue('H'.$row, $gaji ? $gaji->gaji_bersih : 0);
                $row++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Realisasi_Gaji.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480',
            'periode_import' => 'required|string|max:50',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());
        $uploadId = $request->input('upload_id', uniqid());

        if (! in_array($extension, ['xlsx', 'csv', 'xls', 'dbf'])) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Harap unggah file .xlsx, .csv, atau .dbf');
        }

        $periode = $request->input('periode_import');

        // Delete old unmatched NIP logs for this period
        UnmatchedNip::where('periode', $periode)
            ->where('jenis_file', 'Gaji')
            ->delete();

        $importedCount = 0;
        $failedCount = 0;

        DB::beginTransaction();
        try {
            if ($extension === 'dbf') {
                $table = new TableReader($path);
                $totalRows = $table->getRecordCount();
                while ($record = $table->nextRecord()) {
                    $nip = trim($record->get('NIP'));
                    if (! $nip) {
                        continue;
                    }

                    $pegawai = Pegawai::where('nip', $nip)->first();
                    if (! $pegawai) {
                        $failedCount++;
                        $importedCount++;

                        UnmatchedNip::create([
                            'nip' => $nip,
                            'nama' => null,
                            'jenis_file' => 'Gaji',
                            'periode' => $periode,
                            'keterangan' => 'NIP dari file Gaji DBF tidak ditemukan di Master Data Pegawai',
                        ]);

                        if ($importedCount % 50 === 0) {
                            Cache::put('upload_progress_'.$uploadId, ['progress' => $importedCount, 'total' => $totalRows], 120);
                        }

                        continue;
                    }

                    $gajiPokok = (float) $record->get('GAPOK');
                    $pajak = (float) ($record->get('PPAJAK') ?? $record->get('TJPAJAK') ?? 0);
                    $iwp = (float) $record->get('PIWP');
                    $potonganLain = (float) $record->get('POTONGAN') - ($pajak + $iwp);
                    if ($potonganLain < 0) {
                        $potonganLain = 0;
                    } // fallback if negative
                    $gajiBersih = (float) $record->get('BERSIH');

                    // Extract all columns for raw_data
                    $rawData = [];
                    foreach ($table->getColumns() as $column) {
                        $colName = $column->getName();
                        $rawData[$colName] = $record->get($colName);
                    }

                    $kelompokPegawai = $request->input('kelompok_pegawai');

                    RealisasiGaji::updateOrCreate(
                        [
                            'pegawai_id' => $pegawai->id,
                            'periode' => $periode,
                        ],
                        [
                            'gaji_pokok' => $gajiPokok,
                            'pajak' => $pajak,
                            'iwp' => $iwp,
                            'potongan_lain' => $potonganLain,
                            'gaji_bersih' => $gajiBersih,
                            'sub_kegiatan' => null,
                            'raw_data' => array_merge($rawData, ['kelompok_upload' => $kelompokPegawai]),
                        ]
                    );
                    $importedCount++;
                    if ($importedCount % 50 === 0) {
                        Cache::put('upload_progress_'.$uploadId, ['progress' => $importedCount, 'total' => $totalRows], 120);
                    }
                }
                $table->close();
            } else {
                // Excel / CSV fallback
                $totalRows = 0;
                $reader = SimpleExcelReader::create($path, $extension);
                $reader->getRows()->each(function () use (&$totalRows) {
                    $totalRows++;
                });

                $rows = SimpleExcelReader::create($path, $extension)->getRows();
                foreach ($rows as $row) {
                    $nip = trim($row['NIP'] ?? '');
                    if (! $nip) {
                        continue;
                    }

                    $pegawai = Pegawai::where('nip', $nip)->first();
                    if (! $pegawai) {
                        $failedCount++;
                        $importedCount++;

                        UnmatchedNip::create([
                            'nip' => $nip,
                            'nama' => $row['Nama'] ?? $row['nama'] ?? $row['NAMA'] ?? null,
                            'jenis_file' => 'Gaji',
                            'periode' => $periode,
                            'keterangan' => 'NIP dari file Gaji Excel tidak ditemukan di Master Data Pegawai',
                        ]);

                        if ($importedCount % 50 === 0) {
                            Cache::put('upload_progress_'.$uploadId, ['progress' => $importedCount, 'total' => $totalRows], 120);
                        }

                        continue;
                    }

                    $gajiPokok = (float) str_replace(',', '', $row['Gaji Pokok'] ?? 0);
                    $pajak = (float) str_replace(',', '', $row['Pajak'] ?? 0);
                    $iwp = (float) str_replace(',', '', $row['IWP'] ?? 0);
                    $potonganLain = (float) str_replace(',', '', $row['Potongan Lain'] ?? 0);
                    $gajiBersih = (float) str_replace(',', '', $row['Bersih'] ?? 0);
                    $subKegiatan = $row['Sub Kegiatan'] ?? null;

                    $kelompokPegawai = $request->input('kelompok_pegawai');

                    RealisasiGaji::updateOrCreate(
                        [
                            'pegawai_id' => $pegawai->id,
                            'periode' => $periode,
                        ],
                        [
                            'gaji_pokok' => $gajiPokok,
                            'pajak' => $pajak,
                            'iwp' => $iwp,
                            'potongan_lain' => $potonganLain,
                            'gaji_bersih' => $gajiBersih,
                            'sub_kegiatan' => $subKegiatan,
                            'raw_data' => array_merge($row, ['kelompok_upload' => $kelompokPegawai]),
                        ]
                    );
                    $importedCount++;
                    if ($importedCount % 50 === 0) {
                        Cache::put('upload_progress_'.$uploadId, ['progress' => $importedCount, 'total' => $totalRows], 120);
                    }
                }
            }

            DB::commit();
            Cache::forget('upload_progress_'.$uploadId);

            if ($request->ajax() || $request->wantsJson()) {
                session()->flash('success', "Berhasil mengimpor $importedCount data realisasi Gaji. $failedCount data gagal (NIP tidak ditemukan).");

                return response()->json(['success' => true]);
            }

            return redirect()->back()->with('success', "Berhasil mengimpor $importedCount data realisasi Gaji. $failedCount data gagal (NIP tidak ditemukan).");
        } catch (\Exception $e) {
            DB::rollBack();
            Cache::forget('upload_progress_'.$uploadId);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: '.$e->getMessage());
        }
    }
}
