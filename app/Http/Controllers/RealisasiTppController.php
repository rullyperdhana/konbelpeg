<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\RealisasiTpp;
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

class RealisasiTppController extends Controller
{
    private function getRekapData($periode, $skpdFilter = null)
    {
        $kesehatanCond = "pegawais.jenis_pegawai = 'KESEHATAN'";
        $guruCond = "pegawais.jenis_pegawai IN ('GURU', 'TENDIK')";

        $tppSub = DB::table('realisasi_tpps')
            ->select('pegawai_id')
            ->selectRaw('COUNT(id) as count_rec')
            ->selectRaw('SUM(total_dibayarkan) as total_dibayarkan');

        if ($periode && $periode !== 'Semua Periode') {
            $tppSub->where(function ($q) use ($periode) {
                $q->where('periode', $periode)
                    ->orWhere('periode_kas', $periode);
            });
        }
        $tppSub->groupBy('pegawai_id');

        $query = DB::table('pegawais')
            ->join('unit_kerjas', 'pegawais.unit_kerja_id', '=', 'unit_kerjas.id')
            ->leftJoinSub($tppSub, 'tpp_summary', function ($join) {
                $join->on('pegawais.id', '=', 'tpp_summary.pegawai_id');
            })
            ->select(
                'unit_kerjas.skpd',
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PNS' AND tpp_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_pns"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND ($guruCond) AND tpp_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_pppk_guru"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND NOT ($guruCond) AND ($kesehatanCond) AND tpp_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_pppk_kes"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND NOT ($guruCond) AND NOT ($kesehatanCond) AND tpp_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_pppk_teknis"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND ($guruCond) AND tpp_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_paruh_guru"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND ($kesehatanCond) AND tpp_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_paruh_kes"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND NOT ($kesehatanCond) AND tpp_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_paruh_teknis"),
                DB::raw('SUM(CASE WHEN tpp_summary.pegawai_id IS NOT NULL THEN 1 ELSE 0 END) as count_total'),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PNS' AND tpp_summary.pegawai_id IS NULL THEN 1 ELSE 0 END) as count_belum_dibayar_pns"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND tpp_summary.pegawai_id IS NULL THEN 1 ELSE 0 END) as count_belum_dibayar_pppk"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND tpp_summary.pegawai_id IS NULL THEN 1 ELSE 0 END) as count_belum_dibayar_paruh"),
                DB::raw('SUM(CASE WHEN tpp_summary.pegawai_id IS NULL THEN 1 ELSE 0 END) as count_belum_dibayar_total'),

                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PNS' THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as tpp_pns"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND ($guruCond) THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as tpp_pppk_guru"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND NOT ($guruCond) AND ($kesehatanCond) THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as tpp_pppk_kes"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' AND NOT ($guruCond) AND NOT ($kesehatanCond) THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as tpp_pppk_teknis"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND ($guruCond) THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as tpp_paruh_guru"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND ($kesehatanCond) THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as tpp_paruh_kes"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK PARUH WAKTU' AND NOT ($guruCond) AND NOT ($kesehatanCond) THEN IFNULL(tpp_summary.total_dibayarkan, 0) ELSE 0 END) as tpp_paruh_teknis"),
                DB::raw('SUM(IFNULL(tpp_summary.total_dibayarkan, 0)) as tpp_total')
            );

        if ($skpdFilter) {
            $query->where('unit_kerjas.skpd', $skpdFilter);
        }

        return $query->groupBy('unit_kerjas.skpd')->orderBy('unit_kerjas.skpd')->get();
    }

    public function index(Request $request)
    {
        $tipeLaporan = $request->get('tipe_laporan', 'rekap'); // default to rekap
        $periode = $request->get('periode_filter');
        $skpdFilter = $request->get('skpd_filter');

        $periodesLegacy = RealisasiTpp::whereNull('periode_kas')->select('periode')->distinct()->pluck('periode')->toArray();
        $periodesSpecific = RealisasiTpp::whereNotNull('periode_kas')->select('periode')->distinct()->pluck('periode')->toArray();
        $periodesKas = RealisasiTpp::whereNotNull('periode_kas')->select('periode_kas')->distinct()->pluck('periode_kas')->toArray();
        $periodes = array_values(array_unique(array_merge($periodesKas, $periodesSpecific, $periodesLegacy)));
        rsort($periodes);

        $filterUnitKerjas = UnitKerja::whereNotNull('skpd')->pluck('skpd')->unique()->sort()->values();

        // Calculate Grand Totals across all filtered data
        $tppQuery = RealisasiTpp::query();
        if ($periode) {
            $tppQuery->where(function ($q) use ($periode) {
                $q->where('periode', $periode)
                    ->orWhere('periode_kas', $periode);
            });
        }
        if ($skpdFilter) {
            $tppQuery->whereHas('pegawai.unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
        }
        $totalTppBruto = $tppQuery->sum('tpp_bruto');
        $totalDibayarkan = $tppQuery->sum('total_dibayarkan');

        if ($tipeLaporan == 'rekap') {
            $rekaps = $this->getRekapData($periode, $skpdFilter);

            return view('realisasi.tpp.index', compact('tipeLaporan', 'rekaps', 'periodes', 'filterUnitKerjas', 'totalTppBruto', 'totalDibayarkan'));
        } else {
            $query = Pegawai::with(['unitKerja', 'jabatan', 'realisasiTpps' => function ($q) use ($periode) {
                if ($periode) {
                    $q->where(function ($sub) use ($periode) {
                        $sub->where('periode', $periode)
                            ->orWhere('periode_kas', $periode);
                    });
                }
            }]);

            if ($skpdFilter) {
                $query->whereHas('unitKerja', function ($q) use ($skpdFilter) {
                    $q->where('skpd', $skpdFilter);
                });
            }

            $realisasis = $query->paginate(50);

            return view('realisasi.tpp.index', compact('tipeLaporan', 'realisasis', 'periodes', 'filterUnitKerjas', 'totalTppBruto', 'totalDibayarkan', 'periode'));
        }
    }

    public function exportPdf(Request $request)
    {
        $tipeLaporan = $request->get('tipe_laporan', 'rekap');
        $periode = $request->get('periode_filter');
        $skpdFilter = $request->get('skpd_filter');

        if ($tipeLaporan == 'rekap') {
            $rekaps = $this->getRekapData($periode, $skpdFilter);
            $pdf = Pdf::loadView('realisasi.tpp.pdf', compact('tipeLaporan', 'rekaps', 'periode', 'skpdFilter'))
                ->setPaper('a4', 'landscape');
        } else {
            $query = Pegawai::with(['unitKerja', 'jabatan', 'realisasiTpps' => function ($q) use ($periode) {
                if ($periode) {
                    $q->where(function ($sub) use ($periode) {
                        $sub->where('periode', $periode)
                            ->orWhere('periode_kas', $periode);
                    });
                }
            }]);

            if ($skpdFilter) {
                $query->whereHas('unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
            }
            $realisasis = $query->get();
            $pdf = Pdf::loadView('realisasi.tpp.pdf', compact('tipeLaporan', 'realisasis', 'periode', 'skpdFilter'))
                ->setPaper('a4', 'landscape');
        }

        return $pdf->download('Laporan_Realisasi_TPP.pdf');
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
            $sheet->setCellValue('A1', 'REKAPITULASI REALISASI TPP');
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
            $sheet->setCellValue('C4', 'JUMLAH PEGAWAI (YANG DIBAYARKAN TPP)');
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
            $sheet->setCellValue('O4', 'TOTAL REALISASI TPP');
            $sheet->setCellValue('O5', 'PNS');
            $sheet->setCellValue('P5', 'PPPK GURU DAN TENAGA KEPENDIDIKAN');
            $sheet->setCellValue('Q5', 'PPPK TENAGA KESEHATAN');
            $sheet->setCellValue('R5', 'PPPK TENAGA TEKNIS');
            $sheet->setCellValue('S5', 'PPPK PARUH WAKTU GURU DAN TENAGA KEPENDIDIKAN');
            $sheet->setCellValue('T5', 'PPPK PARUH WAKTU TENAGA KESEHATAN');
            $sheet->setCellValue('U5', 'PPPK PARUH WAKTU TENAGA TEKNIS');
            $sheet->setCellValue('V5', 'JUMLAH TPP');

            // Header styling
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

            // Auto size columns
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

                $sheet->setCellValue('O'.$row, $rekap->tpp_pns);
                $sheet->setCellValue('P'.$row, $rekap->tpp_pppk_guru);
                $sheet->setCellValue('Q'.$row, $rekap->tpp_pppk_kes);
                $sheet->setCellValue('R'.$row, $rekap->tpp_pppk_teknis);
                $sheet->setCellValue('S'.$row, $rekap->tpp_paruh_guru);
                $sheet->setCellValue('T'.$row, $rekap->tpp_paruh_kes);
                $sheet->setCellValue('U'.$row, $rekap->tpp_paruh_teknis);
                $sheet->setCellValue('V'.$row, $rekap->tpp_total);

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

            $sheet->setCellValue('O'.$row, $rekaps->sum('tpp_pns'));
            $sheet->setCellValue('P'.$row, $rekaps->sum('tpp_pppk_guru'));
            $sheet->setCellValue('Q'.$row, $rekaps->sum('tpp_pppk_kes'));
            $sheet->setCellValue('R'.$row, $rekaps->sum('tpp_pppk_teknis'));
            $sheet->setCellValue('S'.$row, $rekaps->sum('tpp_paruh_guru'));
            $sheet->setCellValue('T'.$row, $rekaps->sum('tpp_paruh_kes'));
            $sheet->setCellValue('U'.$row, $rekaps->sum('tpp_paruh_teknis'));
            $sheet->setCellValue('V'.$row, $rekaps->sum('tpp_total'));

            $sheet->getStyle('A'.$row.':V'.$row)->getFont()->setBold(true);
            $sheet->getStyle('A'.$row.':V'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');

            // Data styling
            $sheet->getStyle('A6:V'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('O6:V'.$row)->getNumberFormat()->setFormatCode('#,##0');

        } else {
            // Rinci Excel Logic
            $query = Pegawai::with(['unitKerja', 'jabatan', 'realisasiTpps' => function ($q) use ($periode) {
                if ($periode != 'Semua Periode') {
                    $q->where(function ($sub) use ($periode) {
                        $sub->where('periode', $periode)
                            ->orWhere('periode_kas', $periode);
                    });
                }
            }]);
            if ($skpdFilter) {
                $query->whereHas('unitKerja', fn ($q) => $q->where('skpd', $skpdFilter));
            }
            $pegawais = $query->get();

            $sheet->setCellValue('A1', 'NO');
            $sheet->setCellValue('B1', 'PERIODE KAS');
            $sheet->setCellValue('C1', 'HAK KINERJA');
            $sheet->setCellValue('D1', 'TAHAP');
            $sheet->setCellValue('E1', 'NIP');
            $sheet->setCellValue('F1', 'NAMA');
            $sheet->setCellValue('G1', 'STATUS');
            $sheet->setCellValue('H1', 'UNIT KERJA');
            $sheet->setCellValue('I1', 'TPP BRUTO');
            $sheet->setCellValue('J1', 'NOMINAL PLT');
            $sheet->setCellValue('K1', 'TOTAL DIBAYARKAN');

            $sheet->getStyle('A1:K1')->getFont()->setBold(true);

            $row = 2;
            $no = 1;
            foreach ($pegawais as $pegawai) {
                if ($pegawai->realisasiTpps->isEmpty()) {
                    $sheet->setCellValue('A'.$row, $no++);
                    $sheet->setCellValue('B'.$row, $periode);
                    $sheet->setCellValue('C'.$row, '-');
                    $sheet->setCellValue('D'.$row, '-');
                    $sheet->setCellValueExplicit('E'.$row, $pegawai->nip ?? '-', DataType::TYPE_STRING);
                    $sheet->setCellValue('F'.$row, $pegawai->nama ?? '-');
                    $sheet->setCellValue('G'.$row, $pegawai->status_pegawai ?? '-');
                    $sheet->setCellValue('H'.$row, $pegawai->unitKerja?->skpd ?? '-');
                    $sheet->setCellValue('I'.$row, 0);
                    $sheet->setCellValue('J'.$row, 0);
                    $sheet->setCellValue('K'.$row, 0);
                    $row++;
                } else {
                    foreach ($pegawai->realisasiTpps as $tpp) {
                        $sheet->setCellValue('A'.$row, $no++);
                        $sheet->setCellValue('B'.$row, $tpp->periode_kas ?: $tpp->periode);
                        $sheet->setCellValue('C'.$row, $tpp->bulan_kinerja ?: '-');
                        $sheet->setCellValue('D'.$row, $tpp->tahap_bayar ?: 'Reguler');
                        $sheet->setCellValueExplicit('E'.$row, $pegawai->nip ?? '-', DataType::TYPE_STRING);
                        $sheet->setCellValue('F'.$row, $pegawai->nama ?? '-');
                        $sheet->setCellValue('G'.$row, $pegawai->status_pegawai ?? '-');
                        $sheet->setCellValue('H'.$row, $pegawai->unitKerja?->skpd ?? '-');
                        $sheet->setCellValue('I'.$row, $tpp->tpp_bruto);
                        $sheet->setCellValue('J'.$row, $tpp->nominal_plt);
                        $sheet->setCellValue('K'.$row, $tpp->total_dibayarkan);
                        $row++;
                    }
                }
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Realisasi_TPP.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls|max:10240',
            'periode_kas' => 'nullable|string|max:100',
            'bulan_kinerja' => 'nullable|string|max:100',
            'tahap_bayar' => 'nullable|string|max:100',
            'keterangan_bayar' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = $file->getClientOriginalExtension();
        $uploadId = $request->input('upload_id', uniqid());

        $reader = SimpleExcelReader::create($path, $extension);
        $totalRows = 0;
        $reader->getRows()->each(function () use (&$totalRows) {
            $totalRows++;
        });

        // Re-create reader for actual processing
        $rows = SimpleExcelReader::create($path, $extension)->getRows();

        $periodeKas = $request->input('periode_kas');
        $bulanKinerja = $request->input('bulan_kinerja');
        $tahapBayar = $request->input('tahap_bayar', 'Reguler');
        $keteranganBayar = $request->input('keterangan_bayar');

        if ($periodeKas && $bulanKinerja) {
            if ($tahapBayar && $tahapBayar !== 'Reguler') {
                $periode = "{$periodeKas} - {$tahapBayar} (Kinerja {$bulanKinerja})";
            } else {
                $periode = "{$periodeKas} (Kinerja {$bulanKinerja})";
            }
        } else {
            $periode = $request->input('periode_import');
        }

        // Delete old unmatched NIP logs for this period
        UnmatchedNip::where('jenis_file', 'TPP')
            ->where(function ($q) use ($periode, $periodeKas) {
                if ($periode) {
                    $q->where('periode', $periode);
                }
                if ($periodeKas) {
                    $q->orWhere('periode', $periodeKas);
                }
            })
            ->delete();

        // If batch already exists, reset records for this specific batch before processing rows
        // to prevent duplicate accumulation when re-importing the same batch
        if ($periode) {
            RealisasiTpp::where(function ($q) use ($periode, $periodeKas, $bulanKinerja, $tahapBayar) {
                $q->where('periode', $periode);
                if ($periodeKas && $bulanKinerja) {
                    $q->orWhere(function ($sub) use ($periodeKas, $bulanKinerja, $tahapBayar) {
                        $sub->where('periode_kas', $periodeKas)
                            ->where('bulan_kinerja', $bulanKinerja);
                        if ($tahapBayar) {
                            $sub->where('tahap_bayar', $tahapBayar);
                        }
                    });
                }
            })->delete();
        }

        $importedCount = 0;
        $failedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                // Ensure array keys exist (case sensitive based on excel headers)
                $nip = trim($row['NIP'] ?? '');
                if (! $nip) {
                    continue;
                }

                $pegawai = Pegawai::where('nip', $nip)->first();
                if (! $pegawai) {
                    $failedCount++;
                    $importedCount++;

                    $statusPgw = $row['Status'] ?? $row['status'] ?? $row['STATUS'] ?? $row['Status Pegawai'] ?? $row['status_pegawai'] ?? $row['STATUS PEGAWAI'] ?? null;
                    if (! $statusPgw) {
                        if (str_starts_with($nip, 'GUB')) {
                            $statusPgw = 'Pejabat Negara';
                        } elseif (strlen($nip) === 18 && substr($nip, 12, 2) === '21') {
                            $statusPgw = 'PPPK';
                        } else {
                            $statusPgw = 'PNS';
                        }
                    }

                    UnmatchedNip::create([
                        'nip' => $nip,
                        'nama' => $row['NAMA'] ?? $row['Nama'] ?? $row['nama'] ?? null,
                        'status_pegawai' => $statusPgw,
                        'jenis_file' => 'TPP',
                        'periode' => $periode ?: ($row['Periode'] ?? 'Tidak Diketahui'),
                        'keterangan' => 'NIP dari file Excel TPP tidak ditemukan di Master Data Pegawai',
                    ]);

                    if ($importedCount % 50 === 0) {
                        Cache::store('file')->put('upload_progress_'.$uploadId, ['progress' => $importedCount, 'total' => $totalRows], 120);
                    }

                    continue;
                }

                $rowPeriode = $periode ?: ($row['Periode'] ?? 'Tidak Diketahui');
                $rowPeriodeKas = $periodeKas ?: ($row['Periode'] ?? null);
                $rowBulanKinerja = $bulanKinerja ?: ($row['Bulan Kinerja'] ?? null);
                $rowTahapBayar = $tahapBayar ?: 'Reguler';

                // Extract known columns
                $tppBruto = (int) ($row['TPP Bruto'] ?? 0);
                $tppNetto = (int) ($row['TPP Netto'] ?? 0);
                $pph21 = (int) ($row['PPh 21'] ?? 0);
                $potonganLainnya = (int) ($row['Potongan TPP (Lainnya)'] ?? 0);
                $iuranIwp = (int) ($row['Iuran IWP'] ?? 0);
                $totalDibayarkan = (int) ($row['Yang Dibayarkan (Transfer)'] ?? 0);

                // Cek apakah ini baris PLT dari string Jabatan
                $jabatanExcel = strtoupper($row['Jabatan'] ?? '');
                $isPlt = str_contains($jabatanExcel, '(PLT)') || str_contains($jabatanExcel, 'PLT.');

                $realisasi = RealisasiTpp::where('pegawai_id', $pegawai->id)
                    ->where('periode', $rowPeriode)
                    ->first();

                if ($realisasi) {
                    // Update existing record within the current import batch (e.g. employee has a PLT row)
                    if ($isPlt) {
                        $realisasi->nominal_plt += $tppBruto;
                        $realisasi->tpp_netto += $tppNetto;
                        $realisasi->pph_21 += $pph21;
                        $realisasi->potongan_lainnya += $potonganLainnya;
                        $realisasi->iuran_iwp += $iuranIwp;
                        $realisasi->total_dibayarkan += $totalDibayarkan;
                    } else {
                        $realisasi->tpp_bruto += $tppBruto;
                        $realisasi->tpp_netto += $tppNetto;
                        $realisasi->pph_21 += $pph21;
                        $realisasi->potongan_lainnya += $potonganLainnya;
                        $realisasi->iuran_iwp += $iuranIwp;
                        $realisasi->total_dibayarkan += $totalDibayarkan;
                    }
                    $realisasi->save();
                } else {
                    // Create new record
                    RealisasiTpp::create([
                        'pegawai_id' => $pegawai->id,
                        'periode' => $rowPeriode,
                        'periode_kas' => $rowPeriodeKas,
                        'bulan_kinerja' => $rowBulanKinerja,
                        'tahap_bayar' => $rowTahapBayar,
                        'keterangan_bayar' => $keteranganBayar,
                        'tpp_bruto' => $isPlt ? 0 : $tppBruto,
                        'nominal_plt' => $isPlt ? $tppBruto : 0,
                        'tpp_netto' => $tppNetto,
                        'pph_21' => $pph21,
                        'potongan_lainnya' => $potonganLainnya,
                        'iuran_iwp' => $iuranIwp,
                        'total_dibayarkan' => $totalDibayarkan,
                        'raw_data' => $row,
                    ]);
                }

                $importedCount++;
                if ($importedCount % 50 === 0) {
                    Cache::store('file')->put('upload_progress_'.$uploadId, ['progress' => $importedCount, 'total' => $totalRows], 120);
                }
            }
            DB::commit();
            Cache::store('file')->forget('upload_progress_'.$uploadId);

            if ($request->ajax() || $request->wantsJson()) {
                session()->flash('success', "Berhasil mengimpor data realisasi. $failedCount data gagal (NIP tidak ditemukan).");

                return response()->json(['success' => true]);
            }

            return redirect()->back()->with('success', "Berhasil mengimpor data realisasi. $failedCount data gagal (NIP tidak ditemukan).");
        } catch (\Exception $e) {
            DB::rollBack();
            Cache::store('file')->forget('upload_progress_'.$uploadId);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: '.$e->getMessage());
        }
    }
}
