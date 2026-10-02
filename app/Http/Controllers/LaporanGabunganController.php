<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\RealisasiTpp;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanGabunganController extends Controller
{
    private function getRekapData($periode, $jenisGaji = null)
    {
        $kesehatanCond = "pegawais.jenis_pegawai = 'KESEHATAN'";
        $guruCond = "pegawais.jenis_pegawai IN ('GURU', 'TENDIK')";

        $gajiSub = DB::table('realisasi_gajis')
            ->select('pegawai_id')
            ->selectRaw('COUNT(id) as count_gaji')
            ->selectRaw('SUM(gaji_bersih) as gaji_bersih');

        if ($periode != 'Semua Periode' && $periode) {
            $gajiSub->where('periode', $periode);
        }
        if ($jenisGaji && $jenisGaji !== 'Semua') {
            $gajiSub->where('jenis_gaji', $jenisGaji);
        }
        $gajiSub->groupBy('pegawai_id');

        $tppSub = DB::table('realisasi_tpps')
            ->select('pegawai_id')
            ->selectRaw('COUNT(id) as count_tpp')
            ->selectRaw('SUM(total_dibayarkan) as total_dibayarkan');

        if ($periode != 'Semua Periode') {
            $tppSub->where(function ($q) use ($periode) {
                $q->where('periode_kas', $periode)
                    ->orWhere('periode', $periode);
            });
        }
        $tppSub->groupBy('pegawai_id');

        return DB::table('unit_kerjas')
            ->leftJoin('pegawais', 'unit_kerjas.id', '=', 'pegawais.unit_kerja_id')
            ->leftJoinSub($gajiSub, 'gaji_summary', 'pegawais.id', '=', 'gaji_summary.pegawai_id')
            ->leftJoinSub($tppSub, 'tpp_summary', 'pegawais.id', '=', 'tpp_summary.pegawai_id')
            ->select([
                'unit_kerjas.skpd',

                // PEGAWAI COUNTS
                DB::raw('COUNT(DISTINCT pegawais.id) as count_total'),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PNS' THEN 1 ELSE 0 END) as count_pns"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK' THEN 1 ELSE 0 END) as count_pppk"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK PARUH WAKTU' THEN 1 ELSE 0 END) as count_paruh"),

                // BELUM DIBAYAR GAJI
                DB::raw('SUM(CASE WHEN gaji_summary.pegawai_id IS NULL THEN 1 ELSE 0 END) as blm_gaji_total'),
                DB::raw("SUM(CASE WHEN gaji_summary.pegawai_id IS NULL AND UPPER(pegawais.status_pegawai) = 'PNS' THEN 1 ELSE 0 END) as blm_gaji_pns"),
                DB::raw("SUM(CASE WHEN gaji_summary.pegawai_id IS NULL AND UPPER(pegawais.status_pegawai) = 'PPPK' THEN 1 ELSE 0 END) as blm_gaji_pppk"),
                DB::raw("SUM(CASE WHEN gaji_summary.pegawai_id IS NULL AND UPPER(pegawais.status_pegawai) = 'PPPK PARUH WAKTU' THEN 1 ELSE 0 END) as blm_gaji_paruh"),

                // BELUM DIBAYAR TPP
                DB::raw('SUM(CASE WHEN tpp_summary.pegawai_id IS NULL THEN 1 ELSE 0 END) as blm_tpp_total'),
                DB::raw("SUM(CASE WHEN tpp_summary.pegawai_id IS NULL AND UPPER(pegawais.status_pegawai) = 'PNS' THEN 1 ELSE 0 END) as blm_tpp_pns"),
                DB::raw("SUM(CASE WHEN tpp_summary.pegawai_id IS NULL AND UPPER(pegawais.status_pegawai) = 'PPPK' THEN 1 ELSE 0 END) as blm_tpp_pppk"),
                DB::raw("SUM(CASE WHEN tpp_summary.pegawai_id IS NULL AND UPPER(pegawais.status_pegawai) = 'PPPK PARUH WAKTU' THEN 1 ELSE 0 END) as blm_tpp_paruh"),

                // NOMINAL GAJI
                DB::raw('SUM(IFNULL(gaji_summary.gaji_bersih, 0)) as nom_gaji_total'),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PNS' THEN IFNULL(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nom_gaji_pns"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK' AND ($guruCond) THEN IFNULL(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nom_gaji_pppk_guru"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK' AND NOT ($guruCond) AND ($kesehatanCond) THEN IFNULL(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nom_gaji_pppk_kes"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK' AND NOT ($guruCond) AND NOT ($kesehatanCond) THEN IFNULL(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nom_gaji_pppk_teknis"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK PARUH WAKTU' AND ($guruCond) THEN IFNULL(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nom_gaji_paruh_guru"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND ($kesehatanCond) THEN IFNULL(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nom_gaji_paruh_kes"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND NOT ($kesehatanCond) THEN IFNULL(gaji_summary.gaji_bersih, 0) ELSE 0 END) as nom_gaji_paruh_teknis"),

                // NOMINAL TPP
                DB::raw('SUM(IFNULL(tpp_summary.total_dibayarkan, 0)) as nom_tpp_total'),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PNS' THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as nom_tpp_pns"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK' AND ($guruCond) THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as nom_tpp_pppk_guru"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK' AND NOT ($guruCond) AND ($kesehatanCond) THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as nom_tpp_pppk_kes"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK' AND NOT ($guruCond) AND NOT ($kesehatanCond) THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as nom_tpp_pppk_teknis"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK PARUH WAKTU' AND ($guruCond) THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as nom_tpp_paruh_guru"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND ($kesehatanCond) THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as nom_tpp_paruh_kes"),
                DB::raw("SUM(CASE WHEN UPPER(pegawais.status_pegawai) = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND NOT ($kesehatanCond) THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as nom_tpp_paruh_teknis"),
            ])
            ->groupBy('unit_kerjas.skpd')
            ->orderBy('unit_kerjas.skpd')
            ->get();
    }

    public function index(Request $request)
    {
        $periodeDataGaji = RealisasiGaji::select('periode')->distinct()->pluck('periode')->toArray();
        $periodeDataTppKas = RealisasiTpp::whereNotNull('periode_kas')->select('periode_kas as p')->distinct()->pluck('p')->toArray();
        $periodeDataTppLegacy = RealisasiTpp::whereNull('periode_kas')->select('periode as p')->distinct()->pluck('p')->toArray();
        $allPeriodes = array_values(array_unique(array_merge($periodeDataGaji, $periodeDataTppKas, $periodeDataTppLegacy)));
        sort($allPeriodes);

        $periode = $request->input('periode', 'Semua Periode');
        $jenisGaji = $request->input('jenis_gaji', 'Semua');
        $daftarJenisGaji = RealisasiGaji::DAFTAR_JENIS_GAJI;

        $rekaps = $this->getRekapData($periode, $jenisGaji);

        return view('laporan.gabungan.index', compact('rekaps', 'periode', 'allPeriodes', 'jenisGaji', 'daftarJenisGaji'));
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', 'Semua Periode');
        $jenisGaji = $request->input('jenis_gaji', 'Semua');
        $rekaps = $this->getRekapData($periode, $jenisGaji);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $sheet->setCellValue('A1', 'LAPORAN GABUNGAN REALISASI GAJI DAN TPP');
        $subTitle = 'PERIODE: '.strtoupper($periode).($jenisGaji && $jenisGaji !== 'Semua' ? ' | KRITERIA GAJI: '.strtoupper($jenisGaji) : '');
        $sheet->setCellValue('A2', $subTitle);
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);

        // Header Rows
        $sheet->mergeCells('A4:A5');
        $sheet->setCellValue('A4', 'NO');
        $sheet->mergeCells('B4:B5');
        $sheet->setCellValue('B4', 'UNIT KERJA / SKPD');

        // TOTAL PEGAWAI
        $sheet->mergeCells('C4:F4');
        $sheet->setCellValue('C4', 'TOTAL PEGAWAI');
        $sheet->setCellValue('C5', 'PNS');
        $sheet->setCellValue('D5', 'PPPK');
        $sheet->setCellValue('E5', 'PARUH WAKTU');
        $sheet->setCellValue('F5', 'TOTAL');
        $sheet->getStyle('C4:F5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        // BELUM DIBAYAR GAJI
        $sheet->mergeCells('G4:J4');
        $sheet->setCellValue('G4', 'BELUM DIBAYAR GAJI');
        $sheet->setCellValue('G5', 'PNS');
        $sheet->setCellValue('H5', 'PPPK');
        $sheet->setCellValue('I5', 'PARUH WAKTU');
        $sheet->setCellValue('J5', 'TOTAL');
        $sheet->getStyle('G4:J5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEE2E2');

        // BELUM DIBAYAR TPP
        $sheet->mergeCells('K4:N4');
        $sheet->setCellValue('K4', 'BELUM DIBAYAR TPP');
        $sheet->setCellValue('K5', 'PNS');
        $sheet->setCellValue('L5', 'PPPK');
        $sheet->setCellValue('M5', 'PARUH WAKTU');
        $sheet->setCellValue('N5', 'TOTAL');
        $sheet->getStyle('K4:N5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFEDD5');

        // NOMINAL GAJI
        $sheet->mergeCells('O4:V4');
        $sheet->setCellValue('O4', 'TOTAL NOMINAL GAJI');
        $sheet->setCellValue('O5', 'PNS');
        $sheet->setCellValue('P5', 'PPPK GURU');
        $sheet->setCellValue('Q5', 'PPPK KES');
        $sheet->setCellValue('R5', 'PPPK TEKNIS');
        $sheet->setCellValue('S5', 'PARUH GURU');
        $sheet->setCellValue('T5', 'PARUH KES');
        $sheet->setCellValue('U5', 'PARUH TEKNIS');
        $sheet->setCellValue('V5', 'TOTAL GAJI');
        $sheet->getStyle('O4:V5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCFCE7');

        // NOMINAL TPP
        $sheet->mergeCells('W4:AD4');
        $sheet->setCellValue('W4', 'TOTAL NOMINAL TPP');
        $sheet->setCellValue('W5', 'PNS');
        $sheet->setCellValue('X5', 'PPPK GURU');
        $sheet->setCellValue('Y5', 'PPPK KES');
        $sheet->setCellValue('Z5', 'PPPK TEKNIS');
        $sheet->setCellValue('AA5', 'PARUH GURU');
        $sheet->setCellValue('AB5', 'PARUH KES');
        $sheet->setCellValue('AC5', 'PARUH TEKNIS');
        $sheet->setCellValue('AD5', 'TOTAL TPP');
        $sheet->getStyle('W4:AD5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0F2FE');

        // GRAND TOTAL
        $sheet->mergeCells('AE4:AE5');
        $sheet->setCellValue('AE4', 'GRAND TOTAL (GAJI+TPP)');
        $sheet->getStyle('AE4:AE5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEF08A');

        $sheet->getStyle('A4:AE5')->getFont()->setBold(true);
        $sheet->getStyle('A4:AE5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);
        $sheet->getColumnDimension('AC')->setAutoSize(true);
        $sheet->getColumnDimension('AD')->setAutoSize(true);
        $sheet->getColumnDimension('AE')->setAutoSize(true);

        $row = 6;
        $no = 1;
        foreach ($rekaps as $rekap) {
            $sheet->setCellValue('A'.$row, $no++);
            $sheet->setCellValue('B'.$row, $rekap->skpd);

            $sheet->setCellValue('C'.$row, $rekap->count_pns);
            $sheet->setCellValue('D'.$row, $rekap->count_pppk);
            $sheet->setCellValue('E'.$row, $rekap->count_paruh);
            $sheet->setCellValue('F'.$row, $rekap->count_total);

            $sheet->setCellValue('G'.$row, $rekap->blm_gaji_pns);
            $sheet->setCellValue('H'.$row, $rekap->blm_gaji_pppk);
            $sheet->setCellValue('I'.$row, $rekap->blm_gaji_paruh);
            $sheet->setCellValue('J'.$row, $rekap->blm_gaji_total);

            $sheet->setCellValue('K'.$row, $rekap->blm_tpp_pns);
            $sheet->setCellValue('L'.$row, $rekap->blm_tpp_pppk);
            $sheet->setCellValue('M'.$row, $rekap->blm_tpp_paruh);
            $sheet->setCellValue('N'.$row, $rekap->blm_tpp_total);

            $sheet->setCellValue('O'.$row, $rekap->nom_gaji_pns);
            $sheet->setCellValue('P'.$row, $rekap->nom_gaji_pppk_guru);
            $sheet->setCellValue('Q'.$row, $rekap->nom_gaji_pppk_kes);
            $sheet->setCellValue('R'.$row, $rekap->nom_gaji_pppk_teknis);
            $sheet->setCellValue('S'.$row, $rekap->nom_gaji_paruh_guru);
            $sheet->setCellValue('T'.$row, $rekap->nom_gaji_paruh_kes);
            $sheet->setCellValue('U'.$row, $rekap->nom_gaji_paruh_teknis);
            $sheet->setCellValue('V'.$row, $rekap->nom_gaji_total);

            $sheet->setCellValue('W'.$row, $rekap->nom_tpp_pns);
            $sheet->setCellValue('X'.$row, $rekap->nom_tpp_pppk_guru);
            $sheet->setCellValue('Y'.$row, $rekap->nom_tpp_pppk_kes);
            $sheet->setCellValue('Z'.$row, $rekap->nom_tpp_pppk_teknis);
            $sheet->setCellValue('AA'.$row, $rekap->nom_tpp_paruh_guru);
            $sheet->setCellValue('AB'.$row, $rekap->nom_tpp_paruh_kes);
            $sheet->setCellValue('AC'.$row, $rekap->nom_tpp_paruh_teknis);
            $sheet->setCellValue('AD'.$row, $rekap->nom_tpp_total);

            $sheet->setCellValue('AE'.$row, $rekap->nom_gaji_total + $rekap->nom_tpp_total);

            $row++;
        }

        // TOTAL ROW
        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->setCellValue('A'.$row, 'TOTAL KESELURUHAN');
        $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('C'.$row, $rekaps->sum('count_pns'));
        $sheet->setCellValue('D'.$row, $rekaps->sum('count_pppk'));
        $sheet->setCellValue('E'.$row, $rekaps->sum('count_paruh'));
        $sheet->setCellValue('F'.$row, $rekaps->sum('count_total'));

        $sheet->setCellValue('G'.$row, $rekaps->sum('blm_gaji_pns'));
        $sheet->setCellValue('H'.$row, $rekaps->sum('blm_gaji_pppk'));
        $sheet->setCellValue('I'.$row, $rekaps->sum('blm_gaji_paruh'));
        $sheet->setCellValue('J'.$row, $rekaps->sum('blm_gaji_total'));

        $sheet->setCellValue('K'.$row, $rekaps->sum('blm_tpp_pns'));
        $sheet->setCellValue('L'.$row, $rekaps->sum('blm_tpp_pppk'));
        $sheet->setCellValue('M'.$row, $rekaps->sum('blm_tpp_paruh'));
        $sheet->setCellValue('N'.$row, $rekaps->sum('blm_tpp_total'));

        $sheet->setCellValue('O'.$row, $rekaps->sum('nom_gaji_pns'));
        $sheet->setCellValue('P'.$row, $rekaps->sum('nom_gaji_pppk_guru'));
        $sheet->setCellValue('Q'.$row, $rekaps->sum('nom_gaji_pppk_kes'));
        $sheet->setCellValue('R'.$row, $rekaps->sum('nom_gaji_pppk_teknis'));
        $sheet->setCellValue('S'.$row, $rekaps->sum('nom_gaji_paruh_guru'));
        $sheet->setCellValue('T'.$row, $rekaps->sum('nom_gaji_paruh_kes'));
        $sheet->setCellValue('U'.$row, $rekaps->sum('nom_gaji_paruh_teknis'));
        $sheet->setCellValue('V'.$row, $rekaps->sum('nom_gaji_total'));

        $sheet->setCellValue('W'.$row, $rekaps->sum('nom_tpp_pns'));
        $sheet->setCellValue('X'.$row, $rekaps->sum('nom_tpp_pppk_guru'));
        $sheet->setCellValue('Y'.$row, $rekaps->sum('nom_tpp_pppk_kes'));
        $sheet->setCellValue('Z'.$row, $rekaps->sum('nom_tpp_pppk_teknis'));
        $sheet->setCellValue('AA'.$row, $rekaps->sum('nom_tpp_paruh_guru'));
        $sheet->setCellValue('AB'.$row, $rekaps->sum('nom_tpp_paruh_kes'));
        $sheet->setCellValue('AC'.$row, $rekaps->sum('nom_tpp_paruh_teknis'));
        $sheet->setCellValue('AD'.$row, $rekaps->sum('nom_tpp_total'));

        $sheet->setCellValue('AE'.$row, $rekaps->sum('nom_gaji_total') + $rekaps->sum('nom_tpp_total'));

        $sheet->getStyle('A'.$row.':AE'.$row)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row.':AE'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

        $sheet->getStyle('A4:AE'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('O6:AE'.$row)->getNumberFormat()->setFormatCode('#,##0');

        $writer = new Xlsx($spreadsheet);
        $filename = 'Export_Laporan_Gabungan_'.date('Ymd_His').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', 'Semua Periode');
        $jenisGaji = $request->input('jenis_gaji', 'Semua');
        $rekaps = $this->getRekapData($periode, $jenisGaji);

        $pdf = Pdf::loadView('laporan.gabungan.pdf', compact('rekaps', 'periode', 'jenisGaji'));
        $pdf->setPaper('a3', 'landscape');

        return $pdf->download('Export_Laporan_Gabungan_'.date('Ymd_His').'.pdf');
    }
}
