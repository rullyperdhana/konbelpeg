<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
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

class UnitKerjaController extends Controller
{
    /**
     * Get structured tree data grouped by SKPD -> UPT -> Satker.
     */
    private function getTreeData(?string $search = null): array
    {
        $pegawaiCounts = DB::table('pegawais')
            ->select('unit_kerja_id', DB::raw('COUNT(*) as c'))
            ->groupBy('unit_kerja_id')
            ->pluck('c', 'unit_kerja_id');

        $query = UnitKerja::orderBy('skpd')->orderBy('upt')->orderBy('satker');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('skpd', 'like', "%{$search}%")
                    ->orWhere('upt', 'like', "%{$search}%")
                    ->orWhere('satker', 'like', "%{$search}%");
            });
        }

        $unitKerjas = $query->get();

        $tree = [];
        foreach ($unitKerjas as $uk) {
            $skpd = $uk->skpd ?: 'TIDAK DIKETAHUI';
            $upt = $uk->upt && $uk->upt !== '-' ? $uk->upt : 'KANTOR INDUK / SEKRETARIAT';
            $satker = $uk->satker && $uk->satker !== '-' ? $uk->satker : $upt;

            $count = (int) ($pegawaiCounts[$uk->id] ?? 0);
            $uk->pegawais_count = $count;

            if (! isset($tree[$skpd])) {
                $tree[$skpd] = [
                    'total_pegawai' => 0,
                    'total_upt' => 0,
                    'total_satker' => 0,
                    'upts' => [],
                ];
            }

            $tree[$skpd]['total_pegawai'] += $count;
            $tree[$skpd]['total_satker']++;

            if (! isset($tree[$skpd]['upts'][$upt])) {
                $tree[$skpd]['total_upt']++;
                $tree[$skpd]['upts'][$upt] = [
                    'total_pegawai' => 0,
                    'items' => [],
                ];
            }

            $tree[$skpd]['upts'][$upt]['total_pegawai'] += $count;
            $tree[$skpd]['upts'][$upt]['items'][] = $uk;
        }

        return $tree;
    }

    /**
     * Get aggregate summary per SKPD Induk (42 SKPD).
     */
    private function getRekapSkpd(?string $search = null): Collection
    {
        $query = DB::table('unit_kerjas')
            ->leftJoin('pegawais', 'unit_kerjas.id', '=', 'pegawais.unit_kerja_id')
            ->select([
                'unit_kerjas.skpd',
                DB::raw('COUNT(DISTINCT unit_kerjas.id) as total_upt'),
                DB::raw('COUNT(DISTINCT CASE WHEN unit_kerjas.satker IS NOT NULL AND unit_kerjas.satker != "-" THEN unit_kerjas.satker END) as total_satker'),
                DB::raw('COUNT(pegawais.id) as total_pegawai'),
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
     * Display a listing of unit kerjas.
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'tree'); // default to 'tree'
        $search = $request->query('search');
        $skpdFilter = $request->query('skpd_filter');

        $totalUnitKerja = UnitKerja::count();
        $totalSkpdInduk = UnitKerja::distinct()->count('skpd');
        $totalPegawai = Pegawai::count();

        $allSkpd = UnitKerja::whereNotNull('skpd')->pluck('skpd')->unique()->sort()->values();

        if ($tab === 'tree') {
            $tree = $this->getTreeData($search);

            return view('master.skpd', compact(
                'tab',
                'tree',
                'allSkpd',
                'search',
                'skpdFilter',
                'totalUnitKerja',
                'totalSkpdInduk',
                'totalPegawai'
            ));
        }

        if ($tab === 'rekap') {
            $rekaps = $this->getRekapSkpd($search);

            return view('master.skpd', compact(
                'tab',
                'rekaps',
                'allSkpd',
                'search',
                'skpdFilter',
                'totalUnitKerja',
                'totalSkpdInduk',
                'totalPegawai'
            ));
        }

        // Tab Rinci (Flat Table)
        $pegawaiCounts = DB::table('pegawais')
            ->select('unit_kerja_id', DB::raw('COUNT(*) as c'))
            ->groupBy('unit_kerja_id')
            ->pluck('c', 'unit_kerja_id');

        $query = UnitKerja::query();

        if (! empty($skpdFilter)) {
            $query->where('skpd', $skpdFilter);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('skpd', 'like', "%{$search}%")
                    ->orWhere('upt', 'like', "%{$search}%")
                    ->orWhere('satker', 'like', "%{$search}%");
            });
        }

        $unitKerjas = $query->orderBy('skpd')->orderBy('upt')->orderBy('satker')->paginate(25)->withQueryString();

        foreach ($unitKerjas as $uk) {
            $uk->pegawais_count = $pegawaiCounts[$uk->id] ?? 0;
        }

        return view('master.skpd', compact(
            'tab',
            'unitKerjas',
            'allSkpd',
            'search',
            'skpdFilter',
            'totalUnitKerja',
            'totalSkpdInduk',
            'totalPegawai'
        ));
    }

    /**
     * Export master SKPD to official PDF document.
     */
    public function exportPdf(Request $request): Response
    {
        $tab = $request->query('tab', 'tree');
        $search = $request->query('search');
        $skpdFilter = $request->query('skpd_filter');

        $totalSkpdInduk = UnitKerja::distinct()->count('skpd');
        $totalUnitKerja = UnitKerja::count();

        if ($tab === 'tree') {
            $tree = $this->getTreeData($search);

            $pdf = Pdf::loadView('master.skpd_pdf', compact('tab', 'tree', 'search', 'totalSkpdInduk', 'totalUnitKerja'))
                ->setPaper('a4', 'portrait');

            $filename = 'Hierarki_Pohon_SKPD_'.date('Ymd_His').'.pdf';
        } elseif ($tab === 'rekap') {
            $rekaps = $this->getRekapSkpd($search);

            $pdf = Pdf::loadView('master.skpd_pdf', compact('tab', 'rekaps', 'search', 'totalSkpdInduk', 'totalUnitKerja'))
                ->setPaper('a4', 'portrait');

            $filename = 'Rekap_SKPD_Induk_'.date('Ymd_His').'.pdf';
        } else {
            $pegawaiCounts = DB::table('pegawais')
                ->select('unit_kerja_id', DB::raw('COUNT(*) as c'))
                ->groupBy('unit_kerja_id')
                ->pluck('c', 'unit_kerja_id');

            $query = UnitKerja::query();

            if (! empty($skpdFilter)) {
                $query->where('skpd', $skpdFilter);
            }

            if (! empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('skpd', 'like', "%{$search}%")
                        ->orWhere('upt', 'like', "%{$search}%")
                        ->orWhere('satker', 'like', "%{$search}%");
                });
            }

            $unitKerjas = $query->orderBy('skpd')->orderBy('upt')->orderBy('satker')->get();

            foreach ($unitKerjas as $uk) {
                $uk->pegawais_count = $pegawaiCounts[$uk->id] ?? 0;
            }

            $pdf = Pdf::loadView('master.skpd_pdf', compact('tab', 'unitKerjas', 'search', 'skpdFilter', 'totalSkpdInduk', 'totalUnitKerja'))
                ->setPaper('a4', 'portrait');

            $filename = 'Rincian_Master_UnitKerja_'.date('Ymd_His').'.pdf';
        }

        if ($request->query('download') === '1') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Export master SKPD to Excel spreadsheet (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $tab = $request->query('tab', 'tree');
        $search = $request->query('search');
        $skpdFilter = $request->query('skpd_filter');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if ($tab === 'tree' || $tab === 'rinci') {
            $pegawaiCounts = DB::table('pegawais')
                ->select('unit_kerja_id', DB::raw('COUNT(*) as c'))
                ->groupBy('unit_kerja_id')
                ->pluck('c', 'unit_kerja_id');

            $query = UnitKerja::query();

            if (! empty($skpdFilter)) {
                $query->where('skpd', $skpdFilter);
            }

            if (! empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('skpd', 'like', "%{$search}%")
                        ->orWhere('upt', 'like', "%{$search}%")
                        ->orWhere('satker', 'like', "%{$search}%");
                });
            }

            $unitKerjas = $query->orderBy('skpd')->orderBy('upt')->orderBy('satker')->get();

            // Document Title
            $sheet->mergeCells('A1:E1');
            $sheet->setCellValue('A1', 'STRUKTUR HIERARKI MASTER UNIT KERJA (SKPD, UPT, & SATKER)');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A2:E2');
            $sheet->setCellValue('A2', 'TANGGAL CETAK: '.date('d/m/Y H:i').' WITA'.($skpdFilter ? ' | SKPD: "'.$skpdFilter.'"' : '').($search ? ' | CARI: "'.$search.'"' : ''));
            $sheet->getStyle('A2')->getFont()->setSize(10)->setColor(new Color('FF64748B'));
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Table Headers
            $sheet->setCellValue('A4', 'NO');
            $sheet->setCellValue('B4', 'SKPD (INDUK)');
            $sheet->setCellValue('C4', 'UPT / CABANG');
            $sheet->setCellValue('D4', 'SATUAN KERJA (SATKER)');
            $sheet->setCellValue('E4', 'JUMLAH PEGAWAI');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
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
            $sheet->getStyle('A4:E4')->applyFromArray($headerStyle);
            $sheet->getRowDimension(4)->setRowHeight(26);

            $row = 5;
            $no = 1;
            $totalPegawaiSum = 0;
            foreach ($unitKerjas as $item) {
                $count = (int) ($pegawaiCounts[$item->id] ?? 0);
                $totalPegawaiSum += $count;

                $sheet->setCellValue('A'.$row, $no++);
                $sheet->setCellValue('B'.$row, $item->skpd ?? '-');
                $sheet->setCellValue('C'.$row, $item->upt ?? '-');
                $sheet->setCellValue('D'.$row, $item->satker ?? '-');
                $sheet->setCellValueExplicit('E'.$row, $count, DataType::TYPE_NUMERIC);

                $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            // Total Row
            $sheet->mergeCells('A'.$row.':D'.$row);
            $sheet->setCellValue('A'.$row, 'TOTAL SELURUH PEGAWAI');
            $sheet->setCellValue('E'.$row, $totalPegawaiSum);
            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle('A'.$row.':E'.$row)->getFont()->setBold(true);
            $sheet->getStyle('A'.$row.':E'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

            $sheet->getStyle('A4:E'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $fileName = 'Hierarki_Master_UnitKerja_'.date('Ymd_His').'.xlsx';
        } else {
            // Rekap Excel
            $rekaps = $this->getRekapSkpd($search);

            $sheet->mergeCells('A1:E1');
            $sheet->setCellValue('A1', 'REKAPITULASI MASTER DATA SKPD INDUK (42 SKPD)');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A2:E2');
            $sheet->setCellValue('A2', 'TANGGAL CETAK: '.date('d/m/Y H:i').' WITA'.($search ? ' | FILTER: "'.$search.'"' : ''));
            $sheet->getStyle('A2')->getFont()->setSize(10)->setColor(new Color('FF64748B'));
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('A4', 'NO');
            $sheet->setCellValue('B4', 'NAMA SKPD (INDUK)');
            $sheet->setCellValue('C4', 'JUMLAH UPT / SEKOLAH');
            $sheet->setCellValue('D4', 'JUMLAH SATKER');
            $sheet->setCellValue('E4', 'TOTAL PEGAWAI TERDAFTAR');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
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
            $sheet->getStyle('A4:E4')->applyFromArray($headerStyle);
            $sheet->getRowDimension(4)->setRowHeight(26);

            $row = 5;
            $no = 1;
            foreach ($rekaps as $item) {
                $sheet->setCellValue('A'.$row, $no++);
                $sheet->setCellValue('B'.$row, $item->skpd);
                $sheet->setCellValueExplicit('C'.$row, (int) $item->total_upt, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('D'.$row, (int) $item->total_satker, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('E'.$row, (int) $item->total_pegawai, DataType::TYPE_NUMERIC);

                $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C'.$row.':E'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            // Total Row
            $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->setCellValue('A'.$row, 'TOTAL KESELURUHAN ('.count($rekaps).' SKPD)');
            $sheet->setCellValue('C'.$row, $rekaps->sum('total_upt'));
            $sheet->setCellValue('D'.$row, $rekaps->sum('total_satker'));
            $sheet->setCellValue('E'.$row, $rekaps->sum('total_pegawai'));

            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$row.':E'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A'.$row.':E'.$row)->getFont()->setBold(true);
            $sheet->getStyle('A'.$row.':E'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

            $sheet->getStyle('A4:E'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $fileName = 'Rekap_SKPD_Induk_'.date('Ymd_His').'.xlsx';
        }

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    /**
     * Store a newly created unit kerja.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'skpd' => 'required|string|max:255',
            'upt' => 'nullable|string|max:255',
            'satker' => 'nullable|string|max:255',
        ]);

        UnitKerja::create($validated);

        return redirect()->back()->with('success', 'Data SKPD berhasil ditambahkan.');
    }

    /**
     * Update the specified unit kerja.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'skpd' => 'required|string|max:255',
            'upt' => 'nullable|string|max:255',
            'satker' => 'nullable|string|max:255',
        ]);

        $unitKerja = UnitKerja::findOrFail($id);
        $unitKerja->update($validated);

        return redirect()->back()->with('success', 'Data SKPD berhasil diperbarui.');
    }

    /**
     * Remove the specified unit kerja from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $unitKerja = UnitKerja::findOrFail($id);

        if ($unitKerja->pegawais()->count() > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus Unit Kerja karena masih memiliki pegawai terkait.');
        }

        $unitKerja->delete();

        return redirect()->back()->with('success', 'Data SKPD berhasil dihapus.');
    }
}
