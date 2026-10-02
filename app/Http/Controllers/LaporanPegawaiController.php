<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanPegawaiController extends Controller
{
    /**
     * Build base query for Pegawai filtering.
     */
    private function buildBaseQuery(Request $request): Builder
    {
        $query = Pegawai::with(['jabatan', 'unitKerja']);

        $skpd = $request->query('skpd') ?: $request->query('skpd_filter');
        $upt = $request->query('upt') ?: $request->query('upt_filter');
        $satker = $request->query('satker') ?: $request->query('satker_filter');
        $status = $request->query('status_pegawai') ?: $request->query('status_filter');
        $jenis = $request->query('jenis_pegawai');
        $search = $request->query('search');

        if (! empty($skpd)) {
            $query->whereHas('unitKerja', function (Builder $q) use ($skpd) {
                $q->where('skpd', $skpd);
            });
        }

        if (! empty($upt)) {
            $query->whereHas('unitKerja', function (Builder $q) use ($upt) {
                if ($upt === 'KANTOR INDUK / SEKRETARIAT' || $upt === '-') {
                    $q->where(function (Builder $sub) {
                        $sub->whereNull('upt')->orWhere('upt', '-');
                    });
                } else {
                    $q->where('upt', $upt);
                }
            });
        }

        if (! empty($satker)) {
            $query->whereHas('unitKerja', function (Builder $q) use ($satker) {
                $q->where('satker', $satker);
            });
        }

        if (! empty($status)) {
            $query->where('status_pegawai', $status);
        }

        if (! empty($jenis)) {
            $query->where('jenis_pegawai', $jenis);
        }

        if (! empty($search)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhereHas('jabatan', function (Builder $jq) use ($search) {
                        $jq->where('nama', 'like', "%{$search}%");
                    })
                    ->orWhereHas('unitKerja', function (Builder $uq) use ($search) {
                        $uq->where('upt', 'like', "%{$search}%")
                            ->orWhere('satker', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    /**
     * Get aggregate rekapitulasi data.
     */
    private function getRekapData(?string $skpdFilter = null, ?string $search = null): Collection
    {
        $query = DB::table('unit_kerjas')
            ->leftJoin('pegawais', 'unit_kerjas.id', '=', 'pegawais.unit_kerja_id');

        if (! empty($skpdFilter)) {
            // Breakdown per UPT di bawah SKPD ini
            $query->select([
                DB::raw('COALESCE(NULLIF(unit_kerjas.upt, "-"), "KANTOR INDUK / SEKRETARIAT") as nama_grup'),
                DB::raw('COUNT(DISTINCT unit_kerjas.id) as total_satker'),
                DB::raw('COUNT(pegawais.id) as total_pegawai'),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PNS' THEN 1 ELSE 0 END) as total_pns"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' THEN 1 ELSE 0 END) as total_pppk"),
                DB::raw("SUM(CASE WHEN pegawais.status_pegawai LIKE '%PARUH WAKTU%' THEN 1 ELSE 0 END) as total_pppk_pw"),
            ])
                ->where('unit_kerjas.skpd', $skpdFilter);

            if (! empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('unit_kerjas.upt', 'like', "%{$search}%")
                        ->orWhere('unit_kerjas.satker', 'like', "%{$search}%");
                });
            }

            return $query->groupBy('nama_grup')
                ->orderBy('nama_grup')
                ->get();
        }

        // Breakdown per SKPD Induk (42 SKPD)
        $query->select([
            'unit_kerjas.skpd as nama_grup',
            DB::raw('COUNT(DISTINCT unit_kerjas.id) as total_satker'),
            DB::raw('COUNT(pegawais.id) as total_pegawai'),
            DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PNS' THEN 1 ELSE 0 END) as total_pns"),
            DB::raw("SUM(CASE WHEN pegawais.status_pegawai = 'PPPK' THEN 1 ELSE 0 END) as total_pppk"),
            DB::raw("SUM(CASE WHEN pegawais.status_pegawai LIKE '%PARUH WAKTU%' THEN 1 ELSE 0 END) as total_pppk_pw"),
        ])
            ->whereNotNull('unit_kerjas.skpd');

        if (! empty($search)) {
            $query->where('unit_kerjas.skpd', 'like', "%{$search}%");
        }

        return $query->groupBy('unit_kerjas.skpd')
            ->orderBy('unit_kerjas.skpd')
            ->get();
    }

    /**
     * Display the employee list report.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'rinci'); // 'rinci', 'hierarki', 'rekap'
        $skpd = $request->query('skpd') ?: $request->query('skpd_filter');
        $upt = $request->query('upt') ?: $request->query('upt_filter');
        $satker = $request->query('satker') ?: $request->query('satker_filter');
        $status = $request->query('status_pegawai') ?: $request->query('status_filter');
        $jenis = $request->query('jenis_pegawai');
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 50);
        if (! in_array($perPage, [25, 50, 100, 200])) {
            $perPage = 50;
        }

        // Filter options for dropdowns
        $allSkpd = UnitKerja::whereNotNull('skpd')->pluck('skpd')->unique()->sort()->values();

        $availableUpts = collect();
        if (! empty($skpd)) {
            $availableUpts = UnitKerja::where('skpd', $skpd)
                ->whereNotNull('upt')
                ->where('upt', '!=', '-')
                ->pluck('upt')
                ->unique()
                ->sort()
                ->values();
        }

        $availableSatkers = collect();
        if (! empty($skpd) && ! empty($upt)) {
            $satkerQuery = UnitKerja::where('skpd', $skpd);
            if ($upt === 'KANTOR INDUK / SEKRETARIAT' || $upt === '-') {
                $satkerQuery->where(function ($q) {
                    $q->whereNull('upt')->orWhere('upt', '-');
                });
            } else {
                $satkerQuery->where('upt', $upt);
            }
            $availableSatkers = $satkerQuery->whereNotNull('satker')
                ->where('satker', '!=', '-')
                ->pluck('satker')
                ->unique()
                ->sort()
                ->values();
        }

        $allStatus = ['PNS', 'PPPK', 'PPPK PARUH WAKTU'];
        $allJenis = ['GURU', 'KESEHATAN', 'TEKNIS', 'TENDIK'];

        // Base query with filters applied
        $baseQuery = $this->buildBaseQuery($request);

        // Overall stats based on filters
        $totalPegawai = (clone $baseQuery)->count();
        $totalPns = (clone $baseQuery)->where('status_pegawai', 'PNS')->count();
        $totalPppk = (clone $baseQuery)->where('status_pegawai', 'PPPK')->count();
        $totalPppkPw = (clone $baseQuery)->where('status_pegawai', 'PPPK PARUH WAKTU')->count();

        // Count distinct unit kerjas in the filtered result
        $totalUnitKerja = (clone $baseQuery)->distinct('unit_kerja_id')->count('unit_kerja_id');

        $pegawais = null;
        $treeData = [];
        $rekaps = collect();

        if ($tab === 'rinci') {
            $pegawais = $baseQuery
                ->orderBy('unit_kerja_id')
                ->orderBy('nama')
                ->paginate($perPage)
                ->withQueryString();
        } elseif ($tab === 'hierarki') {
            // Group pegawais hierarchically: SKPD -> UPT -> Satker -> Pegawais
            // To maintain high performance, load items up to 500 or for selected SKPD
            $hierarkiQuery = clone $baseQuery;
            if (empty($skpd) && empty($search)) {
                // If no filter selected, default to first SKPD so page loads in 20ms
                $firstSkpd = $allSkpd->first();
                $hierarkiQuery->whereHas('unitKerja', fn ($q) => $q->where('skpd', $firstSkpd));
                $activeHierarkiSkpd = $firstSkpd;
            } else {
                $activeHierarkiSkpd = $skpd;
            }

            $items = $hierarkiQuery->orderBy('unit_kerja_id')->orderBy('nama')->get();

            foreach ($items as $p) {
                $uk = $p->unitKerja;
                $sName = $uk?->skpd ?: 'TIDAK DIKETAHUI';
                $uName = $uk?->upt && $uk->upt !== '-' ? $uk->upt : 'KANTOR INDUK / SEKRETARIAT';
                $satName = $uk?->satker && $uk->satker !== '-' ? $uk->satker : $uName;

                if (! isset($treeData[$sName])) {
                    $treeData[$sName] = [
                        'total_pegawai' => 0,
                        'upts' => [],
                    ];
                }
                $treeData[$sName]['total_pegawai']++;

                if (! isset($treeData[$sName]['upts'][$uName])) {
                    $treeData[$sName]['upts'][$uName] = [
                        'total_pegawai' => 0,
                        'satkers' => [],
                    ];
                }
                $treeData[$sName]['upts'][$uName]['total_pegawai']++;

                if (! isset($treeData[$sName]['upts'][$uName]['satkers'][$satName])) {
                    $treeData[$sName]['upts'][$uName]['satkers'][$satName] = [
                        'total_pegawai' => 0,
                        'items' => [],
                    ];
                }
                $treeData[$sName]['upts'][$uName]['satkers'][$satName]['total_pegawai']++;
                $treeData[$sName]['upts'][$uName]['satkers'][$satName]['items'][] = $p;
            }
        } elseif ($tab === 'rekap') {
            $rekaps = $this->getRekapData($skpd, $search);
        }

        return view('laporan.pegawai', compact(
            'tab',
            'pegawais',
            'treeData',
            'rekaps',
            'allSkpd',
            'availableUpts',
            'availableSatkers',
            'allStatus',
            'allJenis',
            'skpd',
            'upt',
            'satker',
            'status',
            'jenis',
            'search',
            'perPage',
            'totalPegawai',
            'totalPns',
            'totalPppk',
            'totalPppkPw',
            'totalUnitKerja'
        ));
    }

    /**
     * AJAX endpoint to get dependent UPT and Satker filter options.
     */
    public function filterOptions(Request $request): JsonResponse
    {
        $skpd = $request->query('skpd');
        $upt = $request->query('upt');

        $upts = [];
        $satkers = [];

        if (! empty($skpd)) {
            $upts = UnitKerja::where('skpd', $skpd)
                ->whereNotNull('upt')
                ->where('upt', '!=', '-')
                ->pluck('upt')
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        }

        if (! empty($skpd) && ! empty($upt)) {
            $satkerQuery = UnitKerja::where('skpd', $skpd);
            if ($upt === 'KANTOR INDUK / SEKRETARIAT' || $upt === '-') {
                $satkerQuery->where(function ($q) {
                    $q->whereNull('upt')->orWhere('upt', '-');
                });
            } else {
                $satkerQuery->where('upt', $upt);
            }

            $satkers = $satkerQuery->whereNotNull('satker')
                ->where('satker', '!=', '-')
                ->pluck('satker')
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        }

        return response()->json([
            'upts' => $upts,
            'satkers' => $satkers,
        ]);
    }

    /**
     * Export employee list to official PDF document.
     */
    public function exportPdf(Request $request): Response
    {
        $skpd = $request->query('skpd') ?: $request->query('skpd_filter');
        $upt = $request->query('upt') ?: $request->query('upt_filter');
        $satker = $request->query('satker') ?: $request->query('satker_filter');
        $status = $request->query('status_pegawai') ?: $request->query('status_filter');
        $jenis = $request->query('jenis_pegawai');
        $search = $request->query('search');

        $query = $this->buildBaseQuery($request);

        // Limit to 1,000 for PDF generation stability
        $pegawais = $query->orderBy('unit_kerja_id')->orderBy('nama')->take(1000)->get();
        $totalPegawai = $query->count();

        $pdf = Pdf::loadView('laporan.pegawai_pdf', compact(
            'pegawais',
            'totalPegawai',
            'skpd',
            'upt',
            'satker',
            'status',
            'jenis',
            'search'
        ))->setPaper('a4', 'landscape');

        $filename = 'Laporan_Pegawai_'.($skpd ? str_replace(' ', '_', substr($skpd, 0, 20)).'_' : '').date('Ymd_His').'.pdf';

        if ($request->query('download') === '1') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Export employee list to Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $skpd = $request->query('skpd') ?: $request->query('skpd_filter');
        $upt = $request->query('upt') ?: $request->query('upt_filter');
        $satker = $request->query('satker') ?: $request->query('satker_filter');
        $status = $request->query('status_pegawai') ?: $request->query('status_filter');
        $jenis = $request->query('jenis_pegawai');
        $search = $request->query('search');

        $query = $this->buildBaseQuery($request);
        $pegawais = $query->orderBy('unit_kerja_id')->orderBy('nama')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Title Block
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'PEMERINTAH PROVINSI - BADAN PENGELOLAAN KEUANGAN DAN ASET DAERAH');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'LAPORAN DAFTAR PEGAWAI PER SKPD / UPT / SATUAN KERJA');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Filter details
        $filterTexts = [];
        if ($skpd) {
            $filterTexts[] = 'SKPD: '.$skpd;
        }
        if ($upt) {
            $filterTexts[] = 'UPT: '.$upt;
        }
        if ($satker) {
            $filterTexts[] = 'Satker: '.$satker;
        }
        if ($status) {
            $filterTexts[] = 'Status: '.$status;
        }
        if ($jenis) {
            $filterTexts[] = 'Jenis: '.$jenis;
        }
        if ($search) {
            $filterTexts[] = 'Cari: "'.$search.'"';
        }
        $filterSubtitle = 'TANGGAL CETAK: '.date('d/m/Y H:i').' WITA'.($filterTexts ? ' | '.implode(' | ', $filterTexts) : ' | SEMUA UNIT KERJA');

        $sheet->mergeCells('A3:J3');
        $sheet->setCellValue('A3', $filterSubtitle);
        $sheet->getStyle('A3')->getFont()->setSize(9)->setColor(new Color('FF64748B'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Row
        $sheet->setCellValue('A5', 'NO');
        $sheet->setCellValue('B5', 'NIP');
        $sheet->setCellValue('C5', 'NAMA PEGAWAI');
        $sheet->setCellValue('D5', 'GOL/RUANG');
        $sheet->setCellValue('E5', 'JABATAN');
        $sheet->setCellValue('F5', 'JENIS');
        $sheet->setCellValue('G5', 'STATUS');
        $sheet->setCellValue('H5', 'SKPD (INDUK)');
        $sheet->setCellValue('I5', 'UPT / CABANG');
        $sheet->setCellValue('J5', 'SATUAN KERJA (SATKER)');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '0F172A'], 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE2E8F0'],
            ],
        ];
        $sheet->getStyle('A5:J5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(26);

        $row = 6;
        $no = 1;
        foreach ($pegawais as $p) {
            $sheet->setCellValue('A'.$row, $no++);
            $sheet->setCellValueExplicit('B'.$row, (string) ($p->nip ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$row, $p->nama ?? '-');
            $sheet->setCellValue('D'.$row, $p->golru ?? '-');
            $sheet->setCellValue('E'.$row, $p->jabatan?->nama ?? '-');
            $sheet->setCellValue('F'.$row, $p->jenis_pegawai ?? '-');
            $sheet->setCellValue('G'.$row, $p->status_pegawai ?? '-');
            $sheet->setCellValue('H'.$row, $p->unitKerja?->skpd ?? '-');
            $sheet->setCellValue('I'.$row, $p->unitKerja?->upt ?? '-');
            $sheet->setCellValue('J'.$row, $p->unitKerja?->satker ?? '-');

            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // Summary Total Row
        $sheet->mergeCells('A'.$row.':C'.$row);
        $sheet->setCellValue('A'.$row, 'TOTAL SELURUH PEGAWAI TERDATA: '.count($pegawais).' ORANG');
        $sheet->getStyle('A'.$row.':J'.$row)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row.':J'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        $sheet->getStyle('A5:J'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Daftar_Pegawai_'.($skpd ? str_replace(' ', '_', substr($skpd, 0, 20)).'_' : '').date('Ymd_His').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }
}
