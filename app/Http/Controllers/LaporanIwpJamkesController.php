<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\RealisasiTpp;
use App\Models\UnitKerja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanIwpJamkesController extends Controller
{
    /**
     * Get aggregate summary per SKPD for IWP Gaji & Jamkes TPP.
     */
    private function getRekapData(?string $periode, ?string $skpdFilter = null): Collection
    {
        $gajiSub = DB::table('realisasi_gajis')
            ->select('pegawai_id')
            ->selectRaw('COUNT(id) as count_gaji')
            ->selectRaw('COALESCE(SUM(json_extract(raw_data, "$.piwp2")), 0) as iwp_gaji_jamkes')
            ->selectRaw('COALESCE(SUM(json_extract(raw_data, "$.piwp8")), 0) as iwp_gaji_pensiun')
            ->selectRaw('COALESCE(SUM(iwp), 0) as total_iwp_gaji');

        if ($periode) {
            $gajiSub->where('periode', $periode);
        }
        $gajiSub->groupBy('pegawai_id');

        $tppSub = DB::table('realisasi_tpps')
            ->select('pegawai_id')
            ->selectRaw('COUNT(id) as count_tpp')
            ->selectRaw('COALESCE(SUM(iuran_iwp), 0) as iwp_tpp_jamkes');

        if ($periode) {
            $tppSub->where(function ($q) use ($periode) {
                $q->where('periode_kas', $periode)
                    ->orWhere('periode', $periode);
            });
        }
        $tppSub->groupBy('pegawai_id');

        $query = DB::table('unit_kerjas')
            ->leftJoin('pegawais', 'unit_kerjas.id', '=', 'pegawais.unit_kerja_id')
            ->leftJoinSub($gajiSub, 'gaji_summary', 'pegawais.id', '=', 'gaji_summary.pegawai_id')
            ->leftJoinSub($tppSub, 'tpp_summary', 'pegawais.id', '=', 'tpp_summary.pegawai_id')
            ->select([
                'unit_kerjas.skpd',
                DB::raw('COUNT(DISTINCT pegawais.id) as total_pegawai'),
                DB::raw('COUNT(DISTINCT gaji_summary.pegawai_id) as count_gaji'),
                DB::raw('COUNT(DISTINCT tpp_summary.pegawai_id) as count_tpp'),
                DB::raw('COALESCE(SUM(gaji_summary.iwp_gaji_jamkes), 0) as iwp_gaji_jamkes'),
                DB::raw('COALESCE(SUM(gaji_summary.iwp_gaji_pensiun), 0) as iwp_gaji_pensiun'),
                DB::raw('COALESCE(SUM(gaji_summary.total_iwp_gaji), 0) as total_iwp_gaji'),
                DB::raw('COALESCE(SUM(tpp_summary.iwp_tpp_jamkes), 0) as iwp_tpp_jamkes'),
            ]);

        if ($skpdFilter) {
            $query->where('unit_kerjas.skpd', $skpdFilter);
        }

        return $query->groupBy('unit_kerjas.skpd')
            ->orderBy('unit_kerjas.skpd')
            ->get()
            ->map(function ($row) {
                $row->total_jamkes = (float) $row->iwp_gaji_jamkes + (float) $row->iwp_tpp_jamkes;
                $row->total_seluruh_iwp = (float) $row->total_iwp_gaji + (float) $row->iwp_tpp_jamkes;

                return $row;
            });
    }

    /**
     * Display the IWP & Jamkes report.
     */
    public function index(Request $request): View
    {
        $tipeLaporan = $request->get('tipe_laporan', 'rekap'); // 'rekap' or 'rinci'
        $skpdFilter = $request->get('skpd_filter');
        $search = $request->get('search');

        // Available periods
        $periodeGaji = RealisasiGaji::select('periode')->distinct()->pluck('periode')->toArray();
        $periodeTppKas = RealisasiTpp::whereNotNull('periode_kas')->select('periode_kas as p')->distinct()->pluck('p')->toArray();
        $periodeTppLegacy = RealisasiTpp::whereNull('periode_kas')->select('periode as p')->distinct()->pluck('p')->toArray();
        $periodes = array_values(array_unique(array_filter(array_merge($periodeGaji, $periodeTppKas, $periodeTppLegacy))));
        rsort($periodes);

        $periode = $request->get('periode_filter') ?: ($periodes[0] ?? null);
        $filterUnitKerjas = UnitKerja::whereNotNull('skpd')->pluck('skpd')->unique()->sort()->values();

        // Global KPI Stats
        $kpiQueryGaji = RealisasiGaji::query();
        $kpiQueryTpp = RealisasiTpp::query();

        if ($periode) {
            $kpiQueryGaji->where('periode', $periode);
            $kpiQueryTpp->where(function ($q) use ($periode) {
                $q->where('periode_kas', $periode)
                    ->orWhere('periode', $periode);
            });
        }
        if ($skpdFilter) {
            $kpiQueryGaji->whereHas('pegawai.unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
            $kpiQueryTpp->whereHas('pegawai.unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
        }

        $totalIwpGaji = (float) $kpiQueryGaji->sum('iwp');
        $totalIwpGajiJamkes = (float) $kpiQueryGaji->selectRaw('SUM(json_extract(raw_data, "$.piwp2")) as total')->value('total');
        $totalIwpGajiPensiun = (float) $kpiQueryGaji->selectRaw('SUM(json_extract(raw_data, "$.piwp8")) as total')->value('total');
        $totalIwpTppJamkes = (float) $kpiQueryTpp->sum('iuran_iwp');
        $grandTotalJamkes = $totalIwpGajiJamkes + $totalIwpTppJamkes;
        $grandTotalIwp = $totalIwpGaji + $totalIwpTppJamkes;

        if ($tipeLaporan === 'rekap') {
            $rekaps = $this->getRekapData($periode, $skpdFilter);

            return view('laporan.iwp_jamkes.index', compact(
                'tipeLaporan',
                'rekaps',
                'periode',
                'periodes',
                'filterUnitKerjas',
                'skpdFilter',
                'totalIwpGaji',
                'totalIwpGajiJamkes',
                'totalIwpGajiPensiun',
                'totalIwpTppJamkes',
                'grandTotalJamkes',
                'grandTotalIwp'
            ));
        }

        // Rinci Mode
        $pegawaiQuery = Pegawai::with([
            'unitKerja',
            'jabatan',
            'realisasiGajis' => fn ($q) => $periode ? $q->where('periode', $periode) : $q,
            'realisasiTpps' => fn ($q) => $periode ? $q->where(fn ($sub) => $sub->where('periode_kas', $periode)->orWhere('periode', $periode)) : $q,
        ]);

        if ($skpdFilter) {
            $pegawaiQuery->whereHas('unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
        }

        if ($search) {
            $pegawaiQuery->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('nip', 'LIKE', "%{$search}%");
            });
        }

        $realisasis = $pegawaiQuery->paginate(50)->withQueryString();

        return view('laporan.iwp_jamkes.index', compact(
            'tipeLaporan',
            'realisasis',
            'periode',
            'periodes',
            'filterUnitKerjas',
            'skpdFilter',
            'search',
            'totalIwpGaji',
            'totalIwpGajiJamkes',
            'totalIwpGajiPensiun',
            'totalIwpTppJamkes',
            'grandTotalJamkes',
            'grandTotalIwp'
        ));
    }

    /**
     * Export IWP & Jamkes report to Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $tipeLaporan = $request->get('tipe_laporan', 'rekap');
        $periode = $request->get('periode_filter', 'Semua Periode');
        $skpdFilter = $request->get('skpd_filter');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if ($tipeLaporan === 'rekap') {
            $rekaps = $this->getRekapData($periode === 'Semua Periode' ? null : $periode, $skpdFilter);

            // Title
            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', 'REKAPITULASI IURAN WAJIB PEGAWAI (IWP) & JAMINAN KESEHATAN (JAMKES BPJS)');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A2:I2');
            $sheet->setCellValue('A2', 'PERIODE: '.strtoupper($periode));
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Headers
            $sheet->mergeCells('A4:A5');
            $sheet->setCellValue('A4', 'NO');
            $sheet->mergeCells('B4:B5');
            $sheet->setCellValue('B4', 'NAMA SKPD / SATUAN KERJA');

            $sheet->mergeCells('C4:E4');
            $sheet->setCellValue('C4', 'IWP DARI GAJI (SIMGAJI)');
            $sheet->setCellValue('C5', 'IWP 2% (JAMKES)');
            $sheet->setCellValue('D5', 'IWP 8% (PENSIUN & THT)');
            $sheet->setCellValue('E5', 'TOTAL IWP GAJI');

            $sheet->mergeCells('F4:F5');
            $sheet->setCellValue('F4', 'IWP 1% DARI TPP');

            $sheet->mergeCells('G4:G5');
            $sheet->setCellValue('G4', 'TOTAL IURAN JAMKES (GAJI 2% + TPP 1%)');

            $sheet->mergeCells('H4:H5');
            $sheet->setCellValue('H4', 'TOTAL IWP KESELURUHAN');

            $sheet->mergeCells('I4:I5');
            $sheet->setCellValue('I4', 'JUMLAH PEGAWAI');

            $headerStyle = [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'],
                ],
            ];
            $sheet->getStyle('A4:I5')->applyFromArray($headerStyle);

            $row = 6;
            $no = 1;
            foreach ($rekaps as $rekap) {
                $sheet->setCellValue('A'.$row, $no++);
                $sheet->setCellValue('B'.$row, $rekap->skpd);
                $sheet->setCellValue('C'.$row, (float) $rekap->iwp_gaji_jamkes);
                $sheet->setCellValue('D'.$row, (float) $rekap->iwp_gaji_pensiun);
                $sheet->setCellValue('E'.$row, (float) $rekap->total_iwp_gaji);
                $sheet->setCellValue('F'.$row, (float) $rekap->iwp_tpp_jamkes);
                $sheet->setCellValue('G'.$row, (float) $rekap->total_jamkes);
                $sheet->setCellValue('H'.$row, (float) $rekap->total_seluruh_iwp);
                $sheet->setCellValue('I'.$row, $rekap->total_pegawai);
                $row++;
            }

            // TOTAL ROW
            $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->setCellValue('A'.$row, 'TOTAL KESELURUHAN');
            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);

            $sheet->setCellValue('C'.$row, $rekaps->sum('iwp_gaji_jamkes'));
            $sheet->setCellValue('D'.$row, $rekaps->sum('iwp_gaji_pensiun'));
            $sheet->setCellValue('E'.$row, $rekaps->sum('total_iwp_gaji'));
            $sheet->setCellValue('F'.$row, $rekaps->sum('iwp_tpp_jamkes'));
            $sheet->setCellValue('G'.$row, $rekaps->sum('total_jamkes'));
            $sheet->setCellValue('H'.$row, $rekaps->sum('total_seluruh_iwp'));
            $sheet->setCellValue('I'.$row, $rekaps->sum('total_pegawai'));

            $sheet->getStyle('A'.$row.':I'.$row)->getFont()->setBold(true);
            $sheet->getStyle('A'.$row.':I'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

            $sheet->getStyle('A6:I'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('C6:H'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('I6:I'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        } else {
            // Rinci Excel Logic
            $query = Pegawai::with([
                'unitKerja',
                'jabatan',
                'realisasiGajis' => fn ($q) => $periode !== 'Semua Periode' ? $q->where('periode', $periode) : $q,
                'realisasiTpps' => fn ($q) => $periode !== 'Semua Periode' ? $q->where(fn ($sub) => $sub->where('periode_kas', $periode)->orWhere('periode', $periode)) : $q,
            ]);

            if ($skpdFilter) {
                $query->whereHas('unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
            }

            $pegawais = $query->get();

            $sheet->setCellValue('A1', 'NO');
            $sheet->setCellValue('B1', 'PERIODE');
            $sheet->setCellValue('C1', 'NIP');
            $sheet->setCellValue('D1', 'NAMA PEGAWAI');
            $sheet->setCellValue('E1', 'STATUS');
            $sheet->setCellValue('F1', 'UNIT KERJA / SKPD');
            $sheet->setCellValue('G1', 'GAJI POKOK');
            $sheet->setCellValue('H1', 'IWP GAJI 2% (JAMKES)');
            $sheet->setCellValue('I1', 'IWP GAJI 8% (PENSIUN)');
            $sheet->setCellValue('J1', 'TOTAL IWP GAJI');
            $sheet->setCellValue('K1', 'IWP TPP 1% (JAMKES)');
            $sheet->setCellValue('L1', 'TOTAL IURAN JAMKES (GAJI + TPP)');

            $sheet->getStyle('A1:L1')->getFont()->setBold(true);
            $sheet->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

            $row = 2;
            $no = 1;
            foreach ($pegawais as $pegawai) {
                $gajiPokok = (float) $pegawai->realisasiGajis->sum('gaji_pokok');
                $iwpTppJamkes = (float) $pegawai->realisasiTpps->sum('iuran_iwp');
                $iwpGajiJamkes = (float) $pegawai->realisasiGajis->sum(fn ($g) => $g->raw_data['piwp2'] ?? 0);
                $iwpGajiPensiun = (float) $pegawai->realisasiGajis->sum(fn ($g) => $g->raw_data['piwp8'] ?? 0);
                $totalIwpGaji = (float) $pegawai->realisasiGajis->sum('iwp');
                $totalJamkes = $iwpGajiJamkes + $iwpTppJamkes;

                $sheet->setCellValue('A'.$row, $no++);
                $sheet->setCellValue('B'.$row, $periode);
                $sheet->setCellValueExplicit('C'.$row, $pegawai->nip ?? '-', DataType::TYPE_STRING);
                $sheet->setCellValue('D'.$row, $pegawai->nama ?? '-');
                $sheet->setCellValue('E'.$row, $pegawai->status_pegawai ?? '-');
                $sheet->setCellValue('F'.$row, $pegawai->unitKerja?->skpd ?? '-');
                $sheet->setCellValue('G'.$row, $gajiPokok);
                $sheet->setCellValue('H'.$row, $iwpGajiJamkes);
                $sheet->setCellValue('I'.$row, $iwpGajiPensiun);
                $sheet->setCellValue('J'.$row, $totalIwpGaji);
                $sheet->setCellValue('K'.$row, $iwpTppJamkes);
                $sheet->setCellValue('L'.$row, $totalJamkes);
                $row++;
            }

            $sheet->getStyle('A1:L'.($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('G2:L'.($row - 1))->getNumberFormat()->setFormatCode('#,##0');

            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Rekonsiliasi_IWP_Jamkes_BPJS_'.date('Ymd_His').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export IWP & Jamkes report to PDF.
     */
    public function exportPdf(Request $request)
    {
        $tipeLaporan = $request->get('tipe_laporan', 'rekap');
        $periode = $request->get('periode_filter', 'Semua Periode');
        $skpdFilter = $request->get('skpd_filter');

        if ($tipeLaporan === 'rekap') {
            $rekaps = $this->getRekapData($periode === 'Semua Periode' ? null : $periode, $skpdFilter);

            $pdf = Pdf::loadView('laporan.iwp_jamkes.pdf', compact('tipeLaporan', 'rekaps', 'periode', 'skpdFilter'))
                ->setPaper('a4', 'landscape');
        } else {
            $query = Pegawai::with([
                'unitKerja',
                'jabatan',
                'realisasiGajis' => fn ($q) => $periode !== 'Semua Periode' ? $q->where('periode', $periode) : $q,
                'realisasiTpps' => fn ($q) => $periode !== 'Semua Periode' ? $q->where(fn ($sub) => $sub->where('periode_kas', $periode)->orWhere('periode', $periode)) : $q,
            ]);

            if ($skpdFilter) {
                $query->whereHas('unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
            }

            $realisasis = $query->limit(500)->get();

            $pdf = Pdf::loadView('laporan.iwp_jamkes.pdf', compact('tipeLaporan', 'realisasis', 'periode', 'skpdFilter'))
                ->setPaper('a4', 'landscape');
        }

        return $pdf->download('Laporan_Rekonsiliasi_IWP_Jamkes_'.date('Ymd_His').'.pdf');
    }
}
