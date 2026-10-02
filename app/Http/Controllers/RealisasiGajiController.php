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
    private function getRekapData($periode, $skpdFilter = null, $jenisGajiFilter = null)
    {
        $kesehatanCond = "pegawais.jenis_pegawai = 'KESEHATAN'";
        $guruCond = "pegawais.jenis_pegawai IN ('GURU', 'TENDIK')";

        $gajiSub = DB::table('realisasi_gajis')
            ->select('pegawai_id')
            ->selectRaw('COUNT(id) as count_rec')
            ->selectRaw('SUM(gaji_pokok) as gaji_pokok')
            ->selectRaw('SUM(pajak) as pajak')
            ->selectRaw('SUM(iwp) as iwp')
            ->selectRaw('SUM(potongan_lain) as potongan_lain')
            ->selectRaw('SUM(gaji_bersih) as gaji_bersih');

        if ($periode) {
            $gajiSub->where('periode', $periode);
        }
        if ($jenisGajiFilter && $jenisGajiFilter !== 'Semua') {
            $gajiSub->where('jenis_gaji', $jenisGajiFilter);
        }
        $gajiSub->groupBy('pegawai_id');

        $query = DB::table('pegawais')
            ->join('unit_kerjas', 'pegawais.unit_kerja_id', '=', 'unit_kerjas.id')
            ->leftJoinSub($gajiSub, 'gaji_summary', 'pegawais.id', '=', 'gaji_summary.pegawai_id')
            ->select(
                'unit_kerjas.skpd',
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PNS' AND gaji_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_pns"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND ($guruCond) AND gaji_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_pppk_guru"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND NOT ($guruCond) AND ($kesehatanCond) AND gaji_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_pppk_kes"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND NOT ($guruCond) AND NOT ($kesehatanCond) AND gaji_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_pppk_teknis"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND ($guruCond) AND gaji_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_paruh_guru"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND ($kesehatanCond) AND gaji_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_paruh_kes"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND NOT ($kesehatanCond) AND gaji_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_paruh_teknis"),
                DB::raw('SUM(CASE WHEN gaji_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_total'),

                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PNS' AND gaji_summary.pegawai_id IS NULL THEN 1 ELSE 0 END) as count_belum_dibayar_pns"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND gaji_summary.pegawai_id IS NULL THEN 1 ELSE 0 END) as count_belum_dibayar_pppk"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND gaji_summary.pegawai_id IS NULL THEN 1 ELSE 0 END) as count_belum_dibayar_paruh"),
                DB::raw('SUM(CASE WHEN gaji_summary.pegawai_id IS NULL THEN 1 ELSE 0 END) as count_belum_dibayar_total'),

                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PNS' THEN COALESCE(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nominal_pns"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND ($guruCond) THEN COALESCE(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nominal_pppk_guru"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND NOT ($guruCond) AND ($kesehatanCond) THEN COALESCE(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nominal_pppk_kes"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND NOT ($guruCond) AND NOT ($kesehatanCond) THEN COALESCE(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nominal_pppk_teknis"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND ($guruCond) THEN COALESCE(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nominal_paruh_guru"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND ($kesehatanCond) THEN COALESCE(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nominal_paruh_kes"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND NOT ($kesehatanCond) THEN COALESCE(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nominal_paruh_teknis"),
                DB::raw('SUM(COALESCE(gaji_summary.gaji_bersih, 0)) as nominal_total')
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
        $jenisGajiFilter = $request->get('jenis_gaji_filter');

        $periodes = RealisasiGaji::select('periode')->distinct()->orderBy('periode', 'desc')->pluck('periode');
        $filterUnitKerjas = UnitKerja::whereNotNull('skpd')->pluck('skpd')->unique()->sort()->values();
        $daftarJenisGaji = RealisasiGaji::DAFTAR_JENIS_GAJI;

        // Calculate Grand Totals across all filtered data
        $gajiQuery = RealisasiGaji::query();
        if ($periode) {
            $gajiQuery->where('periode', $periode);
        }
        if ($jenisGajiFilter && $jenisGajiFilter !== 'Semua') {
            $gajiQuery->where('jenis_gaji', $jenisGajiFilter);
        }
        if ($skpdFilter) {
            $gajiQuery->whereHas('pegawai.unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
        }
        $totalGajiPokok = $gajiQuery->sum('gaji_pokok');
        $totalGajiBersih = $gajiQuery->sum('gaji_bersih');

        if ($tipeLaporan == 'rekap') {
            $rekaps = $this->getRekapData($periode, $skpdFilter, $jenisGajiFilter);

            return view('realisasi.gaji.index', compact(
                'tipeLaporan',
                'rekaps',
                'periodes',
                'filterUnitKerjas',
                'totalGajiPokok',
                'totalGajiBersih',
                'daftarJenisGaji',
                'jenisGajiFilter',
                'periode',
                'skpdFilter'
            ));
        } else {
            $query = Pegawai::with(['unitKerja', 'jabatan', 'realisasiGajis' => function ($q) use ($periode, $jenisGajiFilter) {
                if ($periode) {
                    $q->where('periode', $periode);
                }
                if ($jenisGajiFilter && $jenisGajiFilter !== 'Semua') {
                    $q->where('jenis_gaji', $jenisGajiFilter);
                }
            }]);

            if ($skpdFilter) {
                $query->whereHas('unitKerja', function ($q) use ($skpdFilter) {
                    $q->where('skpd', $skpdFilter);
                });
            }

            $realisasis = $query->paginate(50);

            return view('realisasi.gaji.index', compact(
                'tipeLaporan',
                'realisasis',
                'periodes',
                'filterUnitKerjas',
                'totalGajiPokok',
                'totalGajiBersih',
                'daftarJenisGaji',
                'jenisGajiFilter',
                'periode',
                'skpdFilter'
            ));
        }
    }

    public function exportPdf(Request $request)
    {
        $tipeLaporan = $request->get('tipe_laporan', 'rekap');
        $periode = $request->get('periode_filter');
        $skpdFilter = $request->get('skpd_filter');
        $jenisGajiFilter = $request->get('jenis_gaji_filter');

        if ($tipeLaporan == 'rekap') {
            $rekaps = $this->getRekapData($periode, $skpdFilter, $jenisGajiFilter);
            $pdf = Pdf::loadView('realisasi.gaji.pdf', compact('tipeLaporan', 'rekaps', 'periode', 'skpdFilter', 'jenisGajiFilter'))
                ->setPaper('a4', 'landscape');
        } else {
            $query = Pegawai::with(['unitKerja', 'jabatan', 'realisasiGajis' => function ($q) use ($periode, $jenisGajiFilter) {
                if ($periode) {
                    $q->where('periode', $periode);
                }
                if ($jenisGajiFilter && $jenisGajiFilter !== 'Semua') {
                    $q->where('jenis_gaji', $jenisGajiFilter);
                }
            }]);

            if ($skpdFilter) {
                $query->whereHas('unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
            }
            $realisasis = $query->get();
            $pdf = Pdf::loadView('realisasi.gaji.pdf', compact('tipeLaporan', 'realisasis', 'periode', 'skpdFilter', 'jenisGajiFilter'))
                ->setPaper('a4', 'landscape');
        }

        return $pdf->download('Laporan_Realisasi_Gaji.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tipeLaporan = $request->get('tipe_laporan', 'rekap');
        $periode = $request->get('periode_filter', 'Semua Periode');
        $skpdFilter = $request->get('skpd_filter');
        $jenisGajiFilter = $request->get('jenis_gaji_filter');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if ($tipeLaporan == 'rekap') {
            $rekaps = $this->getRekapData($periode == 'Semua Periode' ? null : $periode, $skpdFilter, $jenisGajiFilter);

            // Title
            $sheet->mergeCells('A1:J1');
            $sheet->setCellValue('A1', 'REKAPITULASI REALISASI GAJI');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A2:J2');
            $subTitle = 'PERIODE: '.$periode.($jenisGajiFilter && $jenisGajiFilter !== 'Semua' ? ' | KRITERIA: '.$jenisGajiFilter : '');
            $sheet->setCellValue('A2', $subTitle);
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
            $query = Pegawai::with(['unitKerja', 'jabatan', 'realisasiGajis' => function ($q) use ($periode, $jenisGajiFilter) {
                if ($periode != 'Semua Periode' && $periode) {
                    $q->where('periode', $periode);
                }
                if ($jenisGajiFilter && $jenisGajiFilter !== 'Semua') {
                    $q->where('jenis_gaji', $jenisGajiFilter);
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
            $sheet->setCellValue('G1', 'KRITERIA GAJI');
            $sheet->setCellValue('H1', 'GAJI POKOK');
            $sheet->setCellValue('I1', 'TOTAL DIBAYARKAN (BERSIH)');

            $sheet->getStyle('A1:I1')->getFont()->setBold(true);
            $sheet->getStyle('A1:I1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

            $row = 2;
            $no = 1;
            foreach ($pegawais as $pegawai) {
                if ($pegawai->realisasiGajis->isEmpty()) {
                    $sheet->setCellValue('A'.$row, $no++);
                    $sheet->setCellValue('B'.$row, $periode);
                    $sheet->setCellValueExplicit('C'.$row, $pegawai->nip ?? '-', DataType::TYPE_STRING);
                    $sheet->setCellValue('D'.$row, $pegawai->nama ?? '-');
                    $sheet->setCellValue('E'.$row, $pegawai->status_pegawai ?? '-');
                    $sheet->setCellValue('F'.$row, $pegawai->unitKerja?->skpd ?? '-');
                    $sheet->setCellValue('G'.$row, '-');
                    $sheet->setCellValue('H'.$row, 0);
                    $sheet->setCellValue('I'.$row, 0);
                    $row++;
                } else {
                    foreach ($pegawai->realisasiGajis as $gaji) {
                        $sheet->setCellValue('A'.$row, $no++);
                        $sheet->setCellValue('B'.$row, $gaji->periode ?? $periode);
                        $sheet->setCellValueExplicit('C'.$row, $pegawai->nip ?? '-', DataType::TYPE_STRING);
                        $sheet->setCellValue('D'.$row, $pegawai->nama ?? '-');
                        $sheet->setCellValue('E'.$row, $pegawai->status_pegawai ?? '-');
                        $sheet->setCellValue('F'.$row, $pegawai->unitKerja?->skpd ?? '-');
                        $sheet->setCellValue('G'.$row, $gaji->jenis_gaji ?? 'Gaji Induk');
                        $sheet->setCellValue('H'.$row, $gaji->gaji_pokok);
                        $sheet->setCellValue('I'.$row, $gaji->gaji_bersih);
                        $row++;
                    }
                }
            }

            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            $sheet->getStyle('H2:I'.($row - 1))->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('A1:I'.($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
            'jenis_gaji' => 'nullable|string|max:50',
            'kelompok_pegawai' => 'nullable|string|max:50',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());
        $uploadId = $request->input('upload_id', uniqid());

        if (! in_array($extension, ['xlsx', 'csv', 'xls', 'dbf'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Format file tidak didukung. Harap unggah file .xlsx, .csv, atau .dbf']);
            }

            return redirect()->back()->with('error', 'Format file tidak didukung. Harap unggah file .xlsx, .csv, atau .dbf');
        }

        $periode = $request->input('periode_import');
        $jenisGaji = $request->input('jenis_gaji', RealisasiGaji::JENIS_GAJI_INDUK) ?: RealisasiGaji::JENIS_GAJI_INDUK;
        $kelompokPegawai = $request->input('kelompok_pegawai');

        $processImport = function ($sendProgress = null) use ($path, $extension, $periode, $jenisGaji, $kelompokPegawai, $uploadId) {
            // Delete old unmatched NIP logs for this period & criteria
            UnmatchedNip::where('periode', $periode)
                ->where('jenis_file', 'Gaji')
                ->where(function ($q) use ($jenisGaji) {
                    $q->where('keterangan', 'LIKE', "%{$jenisGaji}%")
                        ->orWhereNull('keterangan');
                })
                ->delete();

            $importedCount = 0;
            $failedCount = 0;

            DB::beginTransaction();
            try {
                if ($extension === 'dbf') {
                    $table = new TableReader($path);
                    $totalRows = $table->getRecordCount();

                    if ($sendProgress) {
                        $sendProgress('start', ['total' => $totalRows, 'message' => "Membaca file DBF: {$totalRows} baris ditemukan..."]);
                    }
                    Cache::store('file')->put('upload_progress_'.$uploadId, [
                        'progress' => 0,
                        'total' => $totalRows,
                        'percent' => 0,
                        'success' => 0,
                        'failed' => 0,
                    ], 120);

                    $updateInterval = $totalRows > 1000 ? 25 : ($totalRows > 100 ? 10 : 2);

                    while ($record = $table->nextRecord()) {
                        $nip = trim($record->get('NIP'));
                        if (! $nip) {
                            continue;
                        }

                        $pegawai = Pegawai::where('nip', $nip)->first();
                        if (! $pegawai) {
                            $failedCount++;
                            $importedCount++;

                            $namaPgw = trim((string) ($record->get('NAMA') ?? ''));
                            $kdstapeg = trim((string) ($record->get('KDSTAPEG') ?? $record->get('kdstapeg') ?? ''));
                            $statusPgw = null;
                            if ($kdstapeg === '12') {
                                $statusPgw = 'PPPK';
                            } elseif ($kdstapeg === '13') {
                                $statusPgw = 'PPPK PARUH WAKTU';
                            } elseif ($kdstapeg === '1') {
                                $statusPgw = 'Pejabat Negara';
                            } elseif (in_array($kdstapeg, ['4', '2', '3', '23', '24'])) {
                                $statusPgw = 'PNS';
                            }

                            if (! $statusPgw) {
                                if ($kelompokPegawai) {
                                    $statusPgw = $kelompokPegawai;
                                } elseif (str_starts_with($nip, 'GUB')) {
                                    $statusPgw = 'Pejabat Negara';
                                } elseif (strlen($nip) === 18 && substr($nip, 12, 2) === '21') {
                                    $statusPgw = 'PPPK';
                                } else {
                                    $statusPgw = 'PNS';
                                }
                            }

                            UnmatchedNip::create([
                                'nip' => $nip,
                                'nama' => $namaPgw ?: null,
                                'status_pegawai' => $statusPgw,
                                'jenis_file' => 'Gaji',
                                'periode' => $periode,
                                'keterangan' => "NIP dari file {$jenisGaji} DBF tidak ditemukan di Master Data Pegawai",
                            ]);

                            if ($importedCount % $updateInterval === 0 || $importedCount === $totalRows) {
                                $percent = $totalRows > 0 ? min(100, round(($importedCount / $totalRows) * 100)) : 0;
                                $pData = [
                                    'progress' => $importedCount,
                                    'total' => $totalRows,
                                    'percent' => $percent,
                                    'success' => $importedCount - $failedCount,
                                    'failed' => $failedCount,
                                ];
                                if ($sendProgress) {
                                    $sendProgress('progress', $pData);
                                }
                                Cache::store('file')->put('upload_progress_'.$uploadId, $pData, 120);
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

                        RealisasiGaji::updateOrCreate(
                            [
                                'pegawai_id' => $pegawai->id,
                                'periode' => $periode,
                                'jenis_gaji' => $jenisGaji,
                            ],
                            [
                                'gaji_pokok' => $gajiPokok,
                                'pajak' => $pajak,
                                'iwp' => $iwp,
                                'potongan_lain' => $potonganLain,
                                'gaji_bersih' => $gajiBersih,
                                'sub_kegiatan' => null,
                                'raw_data' => array_merge($rawData, [
                                    'kelompok_upload' => $kelompokPegawai,
                                    'jenis_gaji' => $jenisGaji,
                                ]),
                            ]
                        );
                        $importedCount++;

                        if ($importedCount % $updateInterval === 0 || $importedCount === $totalRows) {
                            $percent = $totalRows > 0 ? min(100, round(($importedCount / $totalRows) * 100)) : 0;
                            $pData = [
                                'progress' => $importedCount,
                                'total' => $totalRows,
                                'percent' => $percent,
                                'success' => $importedCount - $failedCount,
                                'failed' => $failedCount,
                            ];
                            if ($sendProgress) {
                                $sendProgress('progress', $pData);
                            }
                            Cache::store('file')->put('upload_progress_'.$uploadId, $pData, 120);
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

                    if ($sendProgress) {
                        $sendProgress('start', ['total' => $totalRows, 'message' => "Membaca file Excel: {$totalRows} baris ditemukan..."]);
                    }
                    Cache::store('file')->put('upload_progress_'.$uploadId, [
                        'progress' => 0,
                        'total' => $totalRows,
                        'percent' => 0,
                        'success' => 0,
                        'failed' => 0,
                    ], 120);

                    $updateInterval = $totalRows > 1000 ? 25 : ($totalRows > 100 ? 10 : 2);

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

                            $statusPgw = $row['Status'] ?? $row['status'] ?? $row['STATUS'] ?? $row['Status Pegawai'] ?? $row['status_pegawai'] ?? $row['STATUS PEGAWAI'] ?? $row['Kelompok Pegawai'] ?? $row['kelompok_pegawai'] ?? null;
                            if (! $statusPgw) {
                                if ($kelompokPegawai) {
                                    $statusPgw = $kelompokPegawai;
                                } elseif (str_starts_with($nip, 'GUB')) {
                                    $statusPgw = 'Pejabat Negara';
                                } elseif (strlen($nip) === 18 && substr($nip, 12, 2) === '21') {
                                    $statusPgw = 'PPPK';
                                } else {
                                    $statusPgw = 'PNS';
                                }
                            }

                            UnmatchedNip::create([
                                'nip' => $nip,
                                'nama' => $row['Nama'] ?? $row['nama'] ?? $row['NAMA'] ?? null,
                                'status_pegawai' => $statusPgw,
                                'jenis_file' => 'Gaji',
                                'periode' => $periode,
                                'keterangan' => "NIP dari file {$jenisGaji} Excel tidak ditemukan di Master Data Pegawai",
                            ]);

                            if ($importedCount % $updateInterval === 0 || $importedCount === $totalRows) {
                                $percent = $totalRows > 0 ? min(100, round(($importedCount / $totalRows) * 100)) : 0;
                                $pData = [
                                    'progress' => $importedCount,
                                    'total' => $totalRows,
                                    'percent' => $percent,
                                    'success' => $importedCount - $failedCount,
                                    'failed' => $failedCount,
                                ];
                                if ($sendProgress) {
                                    $sendProgress('progress', $pData);
                                }
                                Cache::store('file')->put('upload_progress_'.$uploadId, $pData, 120);
                            }

                            continue;
                        }

                        $gajiPokok = (float) str_replace(',', '', $row['Gaji Pokok'] ?? 0);
                        $pajak = (float) str_replace(',', '', $row['Pajak'] ?? 0);
                        $iwp = (float) str_replace(',', '', $row['IWP'] ?? 0);
                        $potonganLain = (float) str_replace(',', '', $row['Potongan Lain'] ?? 0);
                        $gajiBersih = (float) str_replace(',', '', $row['Bersih'] ?? 0);
                        $subKegiatan = $row['Sub Kegiatan'] ?? null;

                        RealisasiGaji::updateOrCreate(
                            [
                                'pegawai_id' => $pegawai->id,
                                'periode' => $periode,
                                'jenis_gaji' => $jenisGaji,
                            ],
                            [
                                'gaji_pokok' => $gajiPokok,
                                'pajak' => $pajak,
                                'iwp' => $iwp,
                                'potongan_lain' => $potonganLain,
                                'gaji_bersih' => $gajiBersih,
                                'sub_kegiatan' => $subKegiatan,
                                'raw_data' => array_merge($row, [
                                    'kelompok_upload' => $kelompokPegawai,
                                    'jenis_gaji' => $jenisGaji,
                                ]),
                            ]
                        );
                        $importedCount++;

                        if ($importedCount % $updateInterval === 0 || $importedCount === $totalRows) {
                            $percent = $totalRows > 0 ? min(100, round(($importedCount / $totalRows) * 100)) : 0;
                            $pData = [
                                'progress' => $importedCount,
                                'total' => $totalRows,
                                'percent' => $percent,
                                'success' => $importedCount - $failedCount,
                                'failed' => $failedCount,
                            ];
                            if ($sendProgress) {
                                $sendProgress('progress', $pData);
                            }
                            Cache::store('file')->put('upload_progress_'.$uploadId, $pData, 120);
                        }
                    }
                }

                DB::commit();
                Cache::store('file')->forget('upload_progress_'.$uploadId);

                $berhasilCount = $importedCount - $failedCount;
                $successMsg = "Berhasil mengimpor {$berhasilCount} data realisasi {$jenisGaji} (Periode: {$periode}).".($failedCount > 0 ? " {$failedCount} data gagal (NIP tidak ditemukan)." : '');
                session()->flash('success', $successMsg);

                if ($sendProgress) {
                    $sendProgress('done', [
                        'success' => true,
                        'imported' => $importedCount,
                        'berhasil' => $berhasilCount,
                        'failed' => $failedCount,
                        'message' => $successMsg,
                    ]);
                }

                return ['success' => true, 'message' => $successMsg];
            } catch (\Exception $e) {
                DB::rollBack();
                Cache::store('file')->forget('upload_progress_'.$uploadId);

                if ($sendProgress) {
                    $sendProgress('error', [
                        'success' => false,
                        'message' => $e->getMessage(),
                    ]);
                }

                return ['success' => false, 'message' => $e->getMessage()];
            }
        };

        if ($request->ajax() || $request->wantsJson()) {
            return response()->stream(function () use ($processImport) {
                if (session()->isStarted()) {
                    session()->save();
                }

                $sendProgress = function ($type, $data) {
                    echo 'data: '.json_encode(array_merge(['type' => $type], $data))."\n\n";
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                };

                $processImport($sendProgress);
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache, no-transform',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        $result = $processImport();
        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: '.$result['message']);
    }
}
