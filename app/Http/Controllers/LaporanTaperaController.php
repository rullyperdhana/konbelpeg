<?php

namespace App\Http\Controllers;

use App\Models\RealisasiGaji;
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

class LaporanTaperaController extends Controller
{
    /**
     * Ambil data rekapitulasi simulasi Tapera per SKPD berdasarkan data gaji riil.
     */
    private function getRekapData(?string $periode, ?string $skpdFilter = null): Collection
    {
        $gajiSub = DB::table('realisasi_gajis')
            ->select('pegawai_id')
            ->selectRaw('COUNT(id) as count_gaji')
            ->selectRaw('COALESCE(SUM(gaji_pokok), 0) as total_gapok')
            ->selectRaw('COALESCE(SUM(COALESCE(json_extract(raw_data, "$.tjistri"), 0) + COALESCE(json_extract(raw_data, "$.tjanak"), 0)), 0) as total_tj_keluarga')
            ->selectRaw('COALESCE(SUM(COALESCE(json_extract(raw_data, "$.tjstruk"), 0) + COALESCE(json_extract(raw_data, "$.tjfungsi"), 0) + COALESCE(json_extract(raw_data, "$.tjumum"), 0)), 0) as total_tj_jabatan');

        if ($periode && $periode !== 'Semua Periode') {
            $gajiSub->where('periode', $periode);
        }
        $gajiSub->groupBy('pegawai_id');

        $query = DB::table('unit_kerjas')
            ->leftJoin('pegawais', 'unit_kerjas.id', '=', 'pegawais.unit_kerja_id')
            ->leftJoinSub($gajiSub, 'gaji_summary', 'pegawais.id', '=', 'gaji_summary.pegawai_id')
            ->select([
                'unit_kerjas.skpd',
                DB::raw('COUNT(DISTINCT pegawais.id) as total_pegawai'),
                DB::raw('COUNT(DISTINCT gaji_summary.pegawai_id) as count_gaji'),
                DB::raw('COALESCE(SUM(gaji_summary.total_gapok), 0) as total_gapok'),
                DB::raw('COALESCE(SUM(gaji_summary.total_tj_keluarga), 0) as total_tj_keluarga'),
                DB::raw('COALESCE(SUM(gaji_summary.total_tj_jabatan), 0) as total_tj_jabatan'),
            ]);

        if ($skpdFilter) {
            $query->where('unit_kerjas.skpd', $skpdFilter);
        }

        return $query->groupBy('unit_kerjas.skpd')
            ->orderBy('unit_kerjas.skpd')
            ->get()
            ->filter(fn ($row) => $row->total_gapok > 0 || $row->count_gaji > 0)
            ->values()
            ->map(function ($row) {
                $row->dasar_tapera = (float) $row->total_gapok + (float) $row->total_tj_keluarga + (float) $row->total_tj_jabatan;
                $row->tapera_pemda = round($row->dasar_tapera * 0.005);
                $row->tapera_asn = round($row->dasar_tapera * 0.025);
                $row->tapera_total = round($row->dasar_tapera * 0.03);

                return $row;
            });
    }

    /**
     * Tampilkan halaman simulasi & rekapitulasi Tapera.
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'rekap'); // 'kalkulator', 'rekap', 'rinci'
        $skpdFilter = $request->get('skpd_filter');
        $search = $request->get('search');

        // Daftar periode dari data penggajian
        $periodes = RealisasiGaji::select('periode')
            ->distinct()
            ->whereNotNull('periode')
            ->pluck('periode')
            ->toArray();
        rsort($periodes);

        $periode = $request->get('periode_filter') ?: ($periodes[0] ?? null);
        $filterUnitKerjas = UnitKerja::whereNotNull('skpd')->pluck('skpd')->unique()->sort()->values();

        // Hitung KPI ringkasan secara terpusat
        $kpiQuery = DB::table('realisasi_gajis')
            ->leftJoin('pegawais', 'realisasi_gajis.pegawai_id', '=', 'pegawais.id')
            ->leftJoin('unit_kerjas', 'pegawais.unit_kerja_id', '=', 'unit_kerjas.id');

        if ($periode && $periode !== 'Semua Periode') {
            $kpiQuery->where('realisasi_gajis.periode', $periode);
        }
        if ($skpdFilter) {
            $kpiQuery->where('unit_kerjas.skpd', $skpdFilter);
        }

        $kpiStats = $kpiQuery->selectRaw('
            COUNT(DISTINCT realisasi_gajis.pegawai_id) as total_pegawai,
            COALESCE(SUM(realisasi_gajis.gaji_pokok), 0) as total_gapok,
            COALESCE(SUM(COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjistri"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjanak"), 0)), 0) as total_tj_keluarga,
            COALESCE(SUM(COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjstruk"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjfungsi"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjumum"), 0)), 0) as total_tj_jabatan
        ')->first();

        $totalPegawai = (int) ($kpiStats->total_pegawai ?? 0);
        $totalGapok = (float) ($kpiStats->total_gapok ?? 0);
        $totalTjKeluarga = (float) ($kpiStats->total_tj_keluarga ?? 0);
        $totalTjJabatan = (float) ($kpiStats->total_tj_jabatan ?? 0);
        $totalDasarTapera = $totalGapok + $totalTjKeluarga + $totalTjJabatan;
        $totalTaperaPemda = round($totalDasarTapera * 0.005);
        $totalTaperaAsn = round($totalDasarTapera * 0.025);
        $grandTotalTapera = round($totalDasarTapera * 0.03);

        $rekaps = collect();
        $realisasis = null;

        if ($tab === 'rekap') {
            $rekaps = $this->getRekapData($periode, $skpdFilter);
        } elseif ($tab === 'rinci') {
            $query = RealisasiGaji::with(['pegawai.unitKerja', 'pegawai.jabatan'])
                ->when($periode && $periode !== 'Semua Periode', fn ($q) => $q->where('periode', $periode))
                ->when($skpdFilter, fn ($q) => $q->whereHas('pegawai.unitKerja', fn ($u) => $u->where('skpd', $skpdFilter)))
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->whereHas('pegawai', function ($p) use ($search) {
                            $p->where('nama', 'LIKE', "%{$search}%")
                                ->orWhere('nip', 'LIKE', "%{$search}%");
                        })->orWhereRaw('json_extract(raw_data, "$.nama") LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('json_extract(raw_data, "$.nip") LIKE ?', ["%{$search}%"]);
                    });
                });

            $realisasis = $query->paginate(50)->withQueryString();
        }

        return view('laporan.tapera.index', compact(
            'tab',
            'periode',
            'periodes',
            'filterUnitKerjas',
            'skpdFilter',
            'search',
            'totalPegawai',
            'totalGapok',
            'totalTjKeluarga',
            'totalTjJabatan',
            'totalDasarTapera',
            'totalTaperaPemda',
            'totalTaperaAsn',
            'grandTotalTapera',
            'rekaps',
            'realisasis'
        ));
    }

    /**
     * Ekspor laporan simulasi Tapera ke format Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $tab = $request->get('tab', 'rekap');
        $periode = $request->get('periode_filter', 'Semua Periode');
        $skpdFilter = $request->get('skpd_filter');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if ($tab === 'rekap') {
            $rekaps = $this->getRekapData($periode === 'Semua Periode' ? null : $periode, $skpdFilter);

            // Title
            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', 'SIMULASI & REKAPITULASI PROYEKSI TAPERA ASN & PEMBERI KERJA');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A2:I2');
            $sheet->setCellValue('A2', 'PERIODE PENGGAJIAN: '.strtoupper($periode).' (DASAR HUKUM: PP NO. 21 TAHUN 2024)');
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Headers
            $sheet->mergeCells('A4:A5');
            $sheet->setCellValue('A4', 'NO');
            $sheet->mergeCells('B4:B5');
            $sheet->setCellValue('B4', 'NAMA SKPD / SATUAN KERJA');
            $sheet->mergeCells('C4:C5');
            $sheet->setCellValue('C4', 'JUMLAH PEGAWAI');

            $sheet->mergeCells('D4:F4');
            $sheet->setCellValue('D4', 'KOMPONEN DASAR PERHITUNGAN');
            $sheet->setCellValue('D5', 'GAJI POKOK');
            $sheet->setCellValue('E5', 'TUNJ. KELUARGA');
            $sheet->setCellValue('F5', 'TUNJ. JABATAN/FUNGSI');

            $sheet->mergeCells('G4:G5');
            $sheet->setCellValue('G4', 'TOTAL DASAR TAPERA (100%)');

            $sheet->mergeCells('H4:H5');
            $sheet->setCellValue('H4', 'BEBAN PEMDA (0,5%)');

            $sheet->mergeCells('I4:I5');
            $sheet->setCellValue('I4', 'POTONGAN ASN (2,5%)');

            $sheet->mergeCells('J4:J5');
            $sheet->setCellValue('J4', 'TOTAL SETORAN (3,0%)');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E293B'],
                ],
            ];
            $sheet->getStyle('A4:J5')->applyFromArray($headerStyle);

            $row = 6;
            $no = 1;
            foreach ($rekaps as $rekap) {
                $sheet->setCellValue('A'.$row, $no++);
                $sheet->setCellValue('B'.$row, $rekap->skpd ?? 'Lainnya');
                $sheet->setCellValue('C'.$row, $rekap->count_gaji);
                $sheet->setCellValue('D'.$row, $rekap->total_gapok);
                $sheet->setCellValue('E'.$row, $rekap->total_tj_keluarga);
                $sheet->setCellValue('F'.$row, $rekap->total_tj_jabatan);
                $sheet->setCellValue('G'.$row, $rekap->dasar_tapera);
                $sheet->setCellValue('H'.$row, $rekap->tapera_pemda);
                $sheet->setCellValue('I'.$row, $rekap->tapera_asn);
                $sheet->setCellValue('J'.$row, $rekap->tapera_total);
                $row++;
            }

            // Summary row
            $sheet->setCellValue('A'.$row, 'TOTAL');
            $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->setCellValue('C'.$row, '=SUM(C6:C'.($row - 1).')');
            $sheet->setCellValue('D'.$row, '=SUM(D6:D'.($row - 1).')');
            $sheet->setCellValue('E'.$row, '=SUM(E6:E'.($row - 1).')');
            $sheet->setCellValue('F'.$row, '=SUM(F6:F'.($row - 1).')');
            $sheet->setCellValue('G'.$row, '=SUM(G6:G'.($row - 1).')');
            $sheet->setCellValue('H'.$row, '=SUM(H6:H'.($row - 1).')');
            $sheet->setCellValue('I'.$row, '=SUM(I6:I'.($row - 1).')');
            $sheet->setCellValue('J'.$row, '=SUM(J6:J'.($row - 1).')');

            $summaryStyle = [
                'font' => ['bold' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'],
                ],
            ];
            $sheet->getStyle('A'.$row.':J'.$row)->applyFromArray($summaryStyle);

            $sheet->getStyle('A6:J'.($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('C6:J'.$row)->getNumberFormat()->setFormatCode('#,##0');

            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        } else {
            // Rinci Export
            $query = RealisasiGaji::with(['pegawai.unitKerja', 'pegawai.jabatan'])
                ->when($periode && $periode !== 'Semua Periode', fn ($q) => $q->where('periode', $periode))
                ->when($skpdFilter, fn ($q) => $q->whereHas('pegawai.unitKerja', fn ($u) => $u->where('skpd', $skpdFilter)));

            $realisasis = $query->limit(2000)->get();

            $sheet->mergeCells('A1:J1');
            $sheet->setCellValue('A1', 'DAFTAR NOMINATIF SIMULASI TAPERA ASN & PEMDA');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

            $headers = ['NO', 'NIP', 'NAMA PEGAWAI', 'SKPD', 'GAJI POKOK', 'TUNJ. KELUARGA', 'TUNJ. JABATAN', 'DASAR TAPERA', 'POTONGAN ASN (2.5%)', 'BEBAN PEMDA (0.5%)', 'TOTAL (3%)'];
            $colIdx = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($colIdx.'3', $h);
                $colIdx++;
            }
            $sheet->getStyle('A3:K3')->getFont()->setBold(true);
            $sheet->getStyle('A3:K3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCBD5E1');

            $row = 4;
            $no = 1;
            foreach ($realisasis as $item) {
                $nip = $item->pegawai->nip ?? ($item->raw_data['nip'] ?? $item->raw_data['NIP'] ?? '');
                $nama = $item->pegawai->nama ?? ($item->raw_data['nama'] ?? $item->raw_data['Nama'] ?? '');
                $skpd = $item->pegawai->unitKerja->skpd ?? ($item->raw_data['SKPD'] ?? '');

                $sheet->setCellValue('A'.$row, $no++);
                $sheet->setCellValueExplicit('B'.$row, $nip, DataType::TYPE_STRING);
                $sheet->setCellValue('C'.$row, $nama);
                $sheet->setCellValue('D'.$row, $skpd);
                $sheet->setCellValue('E'.$row, $item->gaji_pokok);
                $sheet->setCellValue('F'.$row, $item->tunj_keluarga);
                $sheet->setCellValue('G'.$row, $item->tunj_jabatan);
                $sheet->setCellValue('H'.$row, $item->dasar_tapera);
                $sheet->setCellValue('I'.$row, $item->simulasi_tapera_asn);
                $sheet->setCellValue('J'.$row, $item->simulasi_tapera_pk);
                $sheet->setCellValue('K'.$row, $item->simulasi_tapera_total);
                $row++;
            }

            $sheet->getStyle('A3:K'.($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('E4:K'.($row - 1))->getNumberFormat()->setFormatCode('#,##0');

            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Simulasi_Tapera_ASN_Pemda_'.date('Ymd_His').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    /**
     * Ekspor laporan simulasi Tapera ke format PDF.
     */
    public function exportPdf(Request $request)
    {
        $tab = $request->get('tab', 'rekap');
        $periode = $request->get('periode_filter', 'Semua Periode');
        $skpdFilter = $request->get('skpd_filter');

        if ($tab === 'rekap') {
            $rekaps = $this->getRekapData($periode === 'Semua Periode' ? null : $periode, $skpdFilter);

            $pdf = Pdf::loadView('laporan.tapera.pdf', compact('tab', 'rekaps', 'periode', 'skpdFilter'))
                ->setPaper('a4', 'landscape');
        } else {
            $query = RealisasiGaji::with(['pegawai.unitKerja', 'pegawai.jabatan'])
                ->when($periode && $periode !== 'Semua Periode', fn ($q) => $q->where('periode', $periode))
                ->when($skpdFilter, fn ($q) => $q->whereHas('pegawai.unitKerja', fn ($u) => $u->where('skpd', $skpdFilter)));

            $realisasis = $query->limit(500)->get();

            $pdf = Pdf::loadView('laporan.tapera.pdf', compact('tab', 'realisasis', 'periode', 'skpdFilter'))
                ->setPaper('a4', 'landscape');
        }

        return $pdf->download('Simulasi_Tapera_ASN_Pemda_'.date('Ymd_His').'.pdf');
    }
}
