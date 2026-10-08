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
     * Ambil data rekapitulasi simulasi Tapera per SKPD dengan pemisahan PNS, PPPK Full Waktu, dan PPPK Paruh Waktu.
     */
    private function getRekapData(?string $periode, ?string $skpdFilter = null, ?string $kategoriFilter = 'all'): Collection
    {
        $gajiSub = DB::table('realisasi_gajis')
            ->select('pegawai_id')
            ->selectRaw('COUNT(id) as count_gaji')
            ->selectRaw('COALESCE(SUM(gaji_pokok), 0) as gapok')
            ->selectRaw('COALESCE(SUM(COALESCE(json_extract(raw_data, "$.tjistri"), 0) + COALESCE(json_extract(raw_data, "$.tjanak"), 0)), 0) as tj_keluarga')
            ->selectRaw('COALESCE(SUM(COALESCE(json_extract(raw_data, "$.tjstruk"), 0) + COALESCE(json_extract(raw_data, "$.tjfungsi"), 0) + COALESCE(json_extract(raw_data, "$.tjumum"), 0)), 0) as tj_jabatan')
            ->selectRaw('COALESCE(MAX(json_extract(raw_data, "$.kelompok_upload")), "") as kelompok_upload');

        if ($periode && $periode !== 'Semua Periode') {
            $gajiSub->where('periode', $periode);
        }

        $gajiSub->groupBy('pegawai_id');

        $isPw = '(gaji_summary.pegawai_id IS NOT NULL AND (UPPER(COALESCE(pegawais.status_pegawai, "")) LIKE "%PARUH WAKTU%" OR UPPER(COALESCE(gaji_summary.kelompok_upload, "")) LIKE "%PARUH WAKTU%"))';
        $isPppk = '(gaji_summary.pegawai_id IS NOT NULL AND UPPER(COALESCE(pegawais.status_pegawai, "")) = "PPPK" AND UPPER(COALESCE(gaji_summary.kelompok_upload, "")) NOT LIKE "%PARUH WAKTU%")';
        $isPns = '(gaji_summary.pegawai_id IS NOT NULL AND UPPER(COALESCE(pegawais.status_pegawai, "")) != "PPPK" AND UPPER(COALESCE(pegawais.status_pegawai, "")) NOT LIKE "%PARUH WAKTU%" AND UPPER(COALESCE(gaji_summary.kelompok_upload, "")) NOT LIKE "%PARUH WAKTU%")';

        $query = DB::table('unit_kerjas')
            ->leftJoin('pegawais', 'unit_kerjas.id', '=', 'pegawais.unit_kerja_id')
            ->leftJoinSub($gajiSub, 'gaji_summary', 'pegawais.id', '=', 'gaji_summary.pegawai_id')
            ->select([
                'unit_kerjas.skpd',

                // Jumlah Pegawai per Kategori
                DB::raw("COUNT(DISTINCT CASE WHEN {$isPns} THEN pegawais.id END) as count_pns"),
                DB::raw("COUNT(DISTINCT CASE WHEN {$isPppk} THEN pegawais.id END) as count_pppk"),
                DB::raw("COUNT(DISTINCT CASE WHEN {$isPw} THEN pegawais.id END) as count_pppk_pw"),
                DB::raw('COUNT(DISTINCT gaji_summary.pegawai_id) as count_total'),

                // Gaji Pokok
                DB::raw("COALESCE(SUM(CASE WHEN {$isPns} THEN gaji_summary.gapok ELSE 0 END), 0) as gapok_pns"),
                DB::raw("COALESCE(SUM(CASE WHEN {$isPppk} THEN gaji_summary.gapok ELSE 0 END), 0) as gapok_pppk"),
                DB::raw("COALESCE(SUM(CASE WHEN {$isPw} THEN gaji_summary.gapok ELSE 0 END), 0) as gapok_pppk_pw"),
                DB::raw('COALESCE(SUM(gaji_summary.gapok), 0) as gapok_total'),

                // Tunjangan Keluarga
                DB::raw("COALESCE(SUM(CASE WHEN {$isPns} THEN gaji_summary.tj_keluarga ELSE 0 END), 0) as tj_keluarga_pns"),
                DB::raw("COALESCE(SUM(CASE WHEN {$isPppk} THEN gaji_summary.tj_keluarga ELSE 0 END), 0) as tj_keluarga_pppk"),
                DB::raw("COALESCE(SUM(CASE WHEN {$isPw} THEN gaji_summary.tj_keluarga ELSE 0 END), 0) as tj_keluarga_pppk_pw"),
                DB::raw('COALESCE(SUM(gaji_summary.tj_keluarga), 0) as tj_keluarga_total'),

                // Tunjangan Jabatan / Fungsional
                DB::raw("COALESCE(SUM(CASE WHEN {$isPns} THEN gaji_summary.tj_jabatan ELSE 0 END), 0) as tj_jabatan_pns"),
                DB::raw("COALESCE(SUM(CASE WHEN {$isPppk} THEN gaji_summary.tj_jabatan ELSE 0 END), 0) as tj_jabatan_pppk"),
                DB::raw("COALESCE(SUM(CASE WHEN {$isPw} THEN gaji_summary.tj_jabatan ELSE 0 END), 0) as tj_jabatan_pppk_pw"),
                DB::raw('COALESCE(SUM(gaji_summary.tj_jabatan), 0) as tj_jabatan_total'),

                // Dasar Tapera
                DB::raw("COALESCE(SUM(CASE WHEN {$isPns} THEN gaji_summary.gapok + gaji_summary.tj_keluarga + gaji_summary.tj_jabatan ELSE 0 END), 0) as dasar_pns"),
                DB::raw("COALESCE(SUM(CASE WHEN {$isPppk} THEN gaji_summary.gapok + gaji_summary.tj_keluarga + gaji_summary.tj_jabatan ELSE 0 END), 0) as dasar_pppk"),
                DB::raw("COALESCE(SUM(CASE WHEN {$isPw} THEN gaji_summary.gapok + gaji_summary.tj_keluarga + gaji_summary.tj_jabatan ELSE 0 END), 0) as dasar_pppk_pw"),
                DB::raw('COALESCE(SUM(gaji_summary.gapok + gaji_summary.tj_keluarga + gaji_summary.tj_jabatan), 0) as dasar_total'),
            ]);

        if ($skpdFilter) {
            $query->where('unit_kerjas.skpd', $skpdFilter);
        }

        return $query->groupBy('unit_kerjas.skpd')
            ->orderBy('unit_kerjas.skpd')
            ->get()
            ->filter(fn ($row) => $row->count_total > 0 || $row->gapok_total > 0)
            ->values()
            ->map(function ($row) use ($kategoriFilter) {
                // Perhitungan Beban Pemda (0,5%)
                $row->pemda_pns = round($row->dasar_pns * 0.005);
                $row->pemda_pppk = round($row->dasar_pppk * 0.005);
                $row->pemda_pppk_pw = round($row->dasar_pppk_pw * 0.005);
                $row->pemda_total = round($row->dasar_total * 0.005);

                // Perhitungan Potongan ASN (2,5%)
                $row->asn_pns = round($row->dasar_pns * 0.025);
                $row->asn_pppk = round($row->dasar_pppk * 0.025);
                $row->asn_pppk_pw = round($row->dasar_pppk_pw * 0.025);
                $row->asn_total = round($row->dasar_total * 0.025);

                // Perhitungan Total Iuran (3,0%)
                $row->total_pns = round($row->dasar_pns * 0.03);
                $row->total_pppk = round($row->dasar_pppk * 0.03);
                $row->total_pppk_pw = round($row->dasar_pppk_pw * 0.03);
                $row->total_all = round($row->dasar_total * 0.03);

                // Fallback kompatibilitas nama field lama (berdasarkan filter aktif)
                if ($kategoriFilter === 'pns') {
                    $row->count_gaji = $row->count_pns;
                    $row->total_gapok = $row->gapok_pns;
                    $row->total_tj_keluarga = $row->tj_keluarga_pns;
                    $row->total_tj_jabatan = $row->tj_jabatan_pns;
                    $row->dasar_tapera = $row->dasar_pns;
                    $row->tapera_pemda = $row->pemda_pns;
                    $row->tapera_asn = $row->asn_pns;
                    $row->tapera_total = $row->total_pns;
                } elseif ($kategoriFilter === 'pppk') {
                    $row->count_gaji = $row->count_pppk;
                    $row->total_gapok = $row->gapok_pppk;
                    $row->total_tj_keluarga = $row->tj_keluarga_pppk;
                    $row->total_tj_jabatan = $row->tj_jabatan_pppk;
                    $row->dasar_tapera = $row->dasar_pppk;
                    $row->tapera_pemda = $row->pemda_pppk;
                    $row->tapera_asn = $row->asn_pppk;
                    $row->tapera_total = $row->total_pppk;
                } elseif ($kategoriFilter === 'pppk_pw') {
                    $row->count_gaji = $row->count_pppk_pw;
                    $row->total_gapok = $row->gapok_pppk_pw;
                    $row->total_tj_keluarga = $row->tj_keluarga_pppk_pw;
                    $row->total_tj_jabatan = $row->tj_jabatan_pppk_pw;
                    $row->dasar_tapera = $row->dasar_pppk_pw;
                    $row->tapera_pemda = $row->pemda_pppk_pw;
                    $row->tapera_asn = $row->asn_pppk_pw;
                    $row->tapera_total = $row->total_pppk_pw;
                } else {
                    $row->count_gaji = $row->count_total;
                    $row->total_gapok = $row->gapok_total;
                    $row->total_tj_keluarga = $row->tj_keluarga_total;
                    $row->total_tj_jabatan = $row->tj_jabatan_total;
                    $row->dasar_tapera = $row->dasar_total;
                    $row->tapera_pemda = $row->pemda_total;
                    $row->tapera_asn = $row->asn_total;
                    $row->tapera_total = $row->total_all;
                }

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
        $kategoriFilter = $request->get('kategori_filter', 'all'); // 'all', 'pns', 'pppk', 'pppk_pw'
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

        // Hitung KPI ringkasan secara terpusat dengan pemisahan status
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
            -- PNS
            COUNT(DISTINCT CASE WHEN UPPER(COALESCE(pegawais.status_pegawai, "")) != "PPPK" AND UPPER(COALESCE(pegawais.status_pegawai, "")) NOT LIKE "%PARUH WAKTU%" AND UPPER(COALESCE(json_extract(realisasi_gajis.raw_data, "$.kelompok_upload"), "")) NOT LIKE "%PARUH WAKTU%" THEN realisasi_gajis.pegawai_id END) as pns_pegawai,
            SUM(CASE WHEN UPPER(COALESCE(pegawais.status_pegawai, "")) != "PPPK" AND UPPER(COALESCE(pegawais.status_pegawai, "")) NOT LIKE "%PARUH WAKTU%" AND UPPER(COALESCE(json_extract(realisasi_gajis.raw_data, "$.kelompok_upload"), "")) NOT LIKE "%PARUH WAKTU%" THEN realisasi_gajis.gaji_pokok + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjistri"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjanak"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjstruk"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjfungsi"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjumum"), 0) ELSE 0 END) as pns_dasar,

            -- PPPK FULL WAKTU
            COUNT(DISTINCT CASE WHEN UPPER(COALESCE(pegawais.status_pegawai, "")) = "PPPK" AND UPPER(COALESCE(json_extract(realisasi_gajis.raw_data, "$.kelompok_upload"), "")) NOT LIKE "%PARUH WAKTU%" THEN realisasi_gajis.pegawai_id END) as pppk_pegawai,
            SUM(CASE WHEN UPPER(COALESCE(pegawais.status_pegawai, "")) = "PPPK" AND UPPER(COALESCE(json_extract(realisasi_gajis.raw_data, "$.kelompok_upload"), "")) NOT LIKE "%PARUH WAKTU%" THEN realisasi_gajis.gaji_pokok + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjistri"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjanak"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjstruk"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjfungsi"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjumum"), 0) ELSE 0 END) as pppk_dasar,

            -- PPPK PARUH WAKTU
            COUNT(DISTINCT CASE WHEN UPPER(COALESCE(pegawais.status_pegawai, "")) LIKE "%PARUH WAKTU%" OR UPPER(COALESCE(json_extract(realisasi_gajis.raw_data, "$.kelompok_upload"), "")) LIKE "%PARUH WAKTU%" THEN realisasi_gajis.pegawai_id END) as pppk_pw_pegawai,
            SUM(CASE WHEN UPPER(COALESCE(pegawais.status_pegawai, "")) LIKE "%PARUH WAKTU%" OR UPPER(COALESCE(json_extract(realisasi_gajis.raw_data, "$.kelompok_upload"), "")) LIKE "%PARUH WAKTU%" THEN realisasi_gajis.gaji_pokok + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjistri"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjanak"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjstruk"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjfungsi"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjumum"), 0) ELSE 0 END) as pppk_pw_dasar,

            -- TOTAL KESELURUHAN
            COUNT(DISTINCT realisasi_gajis.pegawai_id) as total_pegawai,
            COALESCE(SUM(realisasi_gajis.gaji_pokok), 0) as total_gapok,
            COALESCE(SUM(COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjistri"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjanak"), 0)), 0) as total_tj_keluarga,
            COALESCE(SUM(COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjstruk"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjfungsi"), 0) + COALESCE(json_extract(realisasi_gajis.raw_data, "$.tjumum"), 0)), 0) as total_tj_jabatan
        ')->first();

        // Rincian PNS
        $pnsPegawai = (int) ($kpiStats->pns_pegawai ?? 0);
        $pnsDasar = (float) ($kpiStats->pns_dasar ?? 0);
        $pnsPemda = round($pnsDasar * 0.005);
        $pnsAsn = round($pnsDasar * 0.025);
        $pnsTotal = round($pnsDasar * 0.03);

        // Rincian PPPK Full Waktu
        $pppkPegawai = (int) ($kpiStats->pppk_pegawai ?? 0);
        $pppkDasar = (float) ($kpiStats->pppk_dasar ?? 0);
        $pppkPemda = round($pppkDasar * 0.005);
        $pppkAsn = round($pppkDasar * 0.025);
        $pppkTotal = round($pppkDasar * 0.03);

        // Rincian PPPK Paruh Waktu
        $pppkPwPegawai = (int) ($kpiStats->pppk_pw_pegawai ?? 0);
        $pppkPwDasar = (float) ($kpiStats->pppk_pw_dasar ?? 0);
        $pppkPwPemda = round($pppkPwDasar * 0.005);
        $pppkPwAsn = round($pppkPwDasar * 0.025);
        $pppkPwTotal = round($pppkPwDasar * 0.03);

        // Total Gabungan
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
            $rekaps = $this->getRekapData($periode, $skpdFilter, $kategoriFilter);
        } elseif ($tab === 'rinci') {
            $query = RealisasiGaji::with(['pegawai.unitKerja', 'pegawai.jabatan'])
                ->when($periode && $periode !== 'Semua Periode', fn ($q) => $q->where('periode', $periode))
                ->when($skpdFilter, fn ($q) => $q->whereHas('pegawai.unitKerja', fn ($u) => $u->where('skpd', $skpdFilter)));

            // Filter Kategori ASN pada Nominatif
            if ($kategoriFilter === 'pns') {
                $query->whereHas('pegawai', fn ($p) => $p->where('status_pegawai', 'PNS'))
                    ->whereRaw('UPPER(COALESCE(json_extract(raw_data, "$.kelompok_upload"), "")) NOT LIKE "%PARUH WAKTU%"');
            } elseif ($kategoriFilter === 'pppk') {
                $query->whereHas('pegawai', fn ($p) => $p->where('status_pegawai', 'PPPK'))
                    ->whereRaw('UPPER(COALESCE(json_extract(raw_data, "$.kelompok_upload"), "")) NOT LIKE "%PARUH WAKTU%"');
            } elseif ($kategoriFilter === 'pppk_pw') {
                $query->where(function ($q) {
                    $q->whereHas('pegawai', fn ($p) => $p->where('status_pegawai', 'LIKE', '%PARUH WAKTU%'))
                        ->orWhereRaw('UPPER(COALESCE(json_extract(raw_data, "$.kelompok_upload"), "")) LIKE "%PARUH WAKTU%"');
                });
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->whereHas('pegawai', function ($p) use ($search) {
                            $p->where('nama', 'LIKE', "%{$search}%")
                                ->orWhere('nip', 'LIKE', "%{$search}%");
                        })->orWhereRaw('json_extract(raw_data, "$.nama") LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('json_extract(raw_data, "$.nip") LIKE ?', ["%{$search}%"]);
                    });
                });
            }

            $realisasis = $query->paginate(50)->withQueryString();
        }

        return view('laporan.tapera.index', compact(
            'tab',
            'periode',
            'periodes',
            'filterUnitKerjas',
            'skpdFilter',
            'kategoriFilter',
            'search',
            'pnsPegawai',
            'pnsDasar',
            'pnsPemda',
            'pnsAsn',
            'pnsTotal',
            'pppkPegawai',
            'pppkDasar',
            'pppkPemda',
            'pppkAsn',
            'pppkTotal',
            'pppkPwPegawai',
            'pppkPwDasar',
            'pppkPwPemda',
            'pppkPwAsn',
            'pppkPwTotal',
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
     * Ekspor laporan simulasi Tapera ke format Excel (.xlsx) dengan pemisahan kategori.
     */
    public function exportExcel(Request $request)
    {
        $tab = $request->get('tab', 'rekap');
        $periode = $request->get('periode_filter', 'Semua Periode');
        $skpdFilter = $request->get('skpd_filter');
        $kategoriFilter = $request->get('kategori_filter', 'all');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if ($tab === 'rekap') {
            $rekaps = $this->getRekapData($periode === 'Semua Periode' ? null : $periode, $skpdFilter, $kategoriFilter);

            if ($kategoriFilter === 'all') {
                // EXCEL MATRIKS LENGKAP (PNS, PPPK FULL WAKTU, PPPK PARUH WAKTU)
                $sheet->mergeCells('A1:R1');
                $sheet->setCellValue('A1', 'MATRIKS REKAPITULASI PROYEKSI TAPERA PER SKPD (PNS, PPPK-FULL, PPPK-PARUH WAKTU)');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:R2');
                $sheet->setCellValue('A2', 'PERIODE: '.strtoupper($periode).' (DASAR HUKUM: PP NO. 21 TAHUN 2024)');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Row 4 & 5 Headers
                $sheet->mergeCells('A4:A5');
                $sheet->setCellValue('A4', 'NO');
                $sheet->mergeCells('B4:B5');
                $sheet->setCellValue('B4', 'NAMA SKPD / SATKER');

                // JUMLAH PEGAWAI
                $sheet->mergeCells('C4:F4');
                $sheet->setCellValue('C4', 'JUMLAH PEGAWAI (ASN)');
                $sheet->setCellValue('C5', 'PNS');
                $sheet->setCellValue('D5', 'PPPK FULL');
                $sheet->setCellValue('E5', 'PPPK PARUH');
                $sheet->setCellValue('F5', 'TOTAL');

                // DASAR TAPERA
                $sheet->mergeCells('G4:J4');
                $sheet->setCellValue('G4', 'DASAR PERHITUNGAN TAPERA (100%)');
                $sheet->setCellValue('G5', 'PNS');
                $sheet->setCellValue('H5', 'PPPK FULL');
                $sheet->setCellValue('I5', 'PPPK PARUH');
                $sheet->setCellValue('J5', 'TOTAL DASAR');

                // BEBAN PEMDA 0.5%
                $sheet->mergeCells('K4:N4');
                $sheet->setCellValue('K4', 'BEBAN PEMDA / APBD (0,5%)');
                $sheet->setCellValue('K5', 'PNS');
                $sheet->setCellValue('L5', 'PPPK FULL');
                $sheet->setCellValue('M5', 'PPPK PARUH');
                $sheet->setCellValue('N5', 'TOTAL PEMDA');

                // POTONGAN ASN 2.5%
                $sheet->mergeCells('O4:R4');
                $sheet->setCellValue('O4', 'POTONGAN GAJI ASN (2,5%)');
                $sheet->setCellValue('O5', 'PNS');
                $sheet->setCellValue('P5', 'PPPK FULL');
                $sheet->setCellValue('Q5', 'PPPK PARUH');
                $sheet->setCellValue('R5', 'TOTAL ASN');

                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
                ];
                $sheet->getStyle('A4:R5')->applyFromArray($headerStyle);

                $row = 6;
                $no = 1;
                foreach ($rekaps as $r) {
                    $sheet->setCellValue('A'.$row, $no++);
                    $sheet->setCellValue('B'.$row, $r->skpd ?: 'Lainnya');

                    $sheet->setCellValue('C'.$row, $r->count_pns);
                    $sheet->setCellValue('D'.$row, $r->count_pppk);
                    $sheet->setCellValue('E'.$row, $r->count_pppk_pw);
                    $sheet->setCellValue('F'.$row, $r->count_total);

                    $sheet->setCellValue('G'.$row, $r->dasar_pns);
                    $sheet->setCellValue('H'.$row, $r->dasar_pppk);
                    $sheet->setCellValue('I'.$row, $r->dasar_pppk_pw);
                    $sheet->setCellValue('J'.$row, $r->dasar_total);

                    $sheet->setCellValue('K'.$row, $r->pemda_pns);
                    $sheet->setCellValue('L'.$row, $r->pemda_pppk);
                    $sheet->setCellValue('M'.$row, $r->pemda_pppk_pw);
                    $sheet->setCellValue('N'.$row, $r->pemda_total);

                    $sheet->setCellValue('O'.$row, $r->asn_pns);
                    $sheet->setCellValue('P'.$row, $r->asn_pppk);
                    $sheet->setCellValue('Q'.$row, $r->asn_pppk_pw);
                    $sheet->setCellValue('R'.$row, $r->asn_total);
                    $row++;
                }

                // Grand Total Row
                $sheet->setCellValue('A'.$row, 'GRAND TOTAL');
                $sheet->mergeCells('A'.$row.':B'.$row);
                foreach (range('C', 'R') as $col) {
                    $sheet->setCellValue($col.$row, '=SUM('.$col.'6:'.$col.($row - 1).')');
                }

                $summaryStyle = [
                    'font' => ['bold' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2E8F0']],
                ];
                $sheet->getStyle('A'.$row.':R'.$row)->applyFromArray($summaryStyle);
                $sheet->getStyle('A6:R'.($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('C6:R'.$row)->getNumberFormat()->setFormatCode('#,##0');

                foreach (range('A', 'R') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            } else {
                // EXCEL KATEGORI SPESIFIK
                $judulKat = $kategoriFilter === 'pns' ? 'PNS' : ($kategoriFilter === 'pppk' ? 'PPPK (PENUH WAKTU)' : 'PPPK (PARUH WAKTU)');
                $sheet->mergeCells('A1:J1');
                $sheet->setCellValue('A1', 'REKAPITULASI PROYEKSI TAPERA KHUSUS '.$judulKat);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->mergeCells('A2:J2');
                $sheet->setCellValue('A2', 'PERIODE: '.strtoupper($periode));

                $headers = ['NO', 'SKPD / UNIT KERJA', 'JML PEGAWAI', 'GAJI POKOK', 'TUNJ. KELUARGA', 'TUNJ. JABATAN', 'DASAR TAPERA', 'BEBAN PEMDA (0,5%)', 'POTONGAN ASN (2,5%)', 'TOTAL (3%)'];
                $colIdx = 'A';
                foreach ($headers as $h) {
                    $sheet->setCellValue($colIdx.'4', $h);
                    $colIdx++;
                }
                $sheet->getStyle('A4:J4')->getFont()->setBold(true);
                $sheet->getStyle('A4:J4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
                $sheet->getStyle('A4:J4')->getFont()->getColor()->setARGB('FFFFFFFF');

                $row = 5;
                $no = 1;
                foreach ($rekaps as $r) {
                    $sheet->setCellValue('A'.$row, $no++);
                    $sheet->setCellValue('B'.$row, $r->skpd);
                    $sheet->setCellValue('C'.$row, $r->count_gaji);
                    $sheet->setCellValue('D'.$row, $r->total_gapok);
                    $sheet->setCellValue('E'.$row, $r->total_tj_keluarga);
                    $sheet->setCellValue('F'.$row, $r->total_tj_jabatan);
                    $sheet->setCellValue('G'.$row, $r->dasar_tapera);
                    $sheet->setCellValue('H'.$row, $r->tapera_pemda);
                    $sheet->setCellValue('I'.$row, $r->tapera_asn);
                    $sheet->setCellValue('J'.$row, $r->tapera_total);
                    $row++;
                }

                $sheet->setCellValue('A'.$row, 'TOTAL');
                $sheet->mergeCells('A'.$row.':B'.$row);
                foreach (range('C', 'J') as $col) {
                    $sheet->setCellValue($col.$row, '=SUM('.$col.'5:'.$col.($row - 1).')');
                }
                $sheet->getStyle('A'.$row.':J'.$row)->getFont()->setBold(true);
                $sheet->getStyle('A4:J'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('C5:J'.$row)->getNumberFormat()->setFormatCode('#,##0');

                foreach (range('A', 'J') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        } else {
            // Rinci Export
            $query = RealisasiGaji::with(['pegawai.unitKerja', 'pegawai.jabatan'])
                ->when($periode && $periode !== 'Semua Periode', fn ($q) => $q->where('periode', $periode))
                ->when($skpdFilter, fn ($q) => $q->whereHas('pegawai.unitKerja', fn ($u) => $u->where('skpd', $skpdFilter)));

            if ($kategoriFilter === 'pns') {
                $query->whereHas('pegawai', fn ($p) => $p->where('status_pegawai', 'PNS'))
                    ->whereRaw('UPPER(COALESCE(json_extract(raw_data, "$.kelompok_upload"), "")) NOT LIKE "%PARUH WAKTU%"');
            } elseif ($kategoriFilter === 'pppk') {
                $query->whereHas('pegawai', fn ($p) => $p->where('status_pegawai', 'PPPK'))
                    ->whereRaw('UPPER(COALESCE(json_extract(raw_data, "$.kelompok_upload"), "")) NOT LIKE "%PARUH WAKTU%"');
            } elseif ($kategoriFilter === 'pppk_pw') {
                $query->where(function ($q) {
                    $q->whereHas('pegawai', fn ($p) => $p->where('status_pegawai', 'LIKE', '%PARUH WAKTU%'))
                        ->orWhereRaw('UPPER(COALESCE(json_extract(raw_data, "$.kelompok_upload"), "")) LIKE "%PARUH WAKTU%"');
                });
            }

            $realisasis = $query->limit(3000)->get();

            $sheet->mergeCells('A1:L1');
            $sheet->setCellValue('A1', 'DAFTAR NOMINATIF SIMULASI TAPERA ASN & PEMDA');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

            $headers = ['NO', 'NIP', 'NAMA PEGAWAI', 'KATEGORI ASN', 'SKPD', 'GAJI POKOK', 'TUNJ. KELUARGA', 'TUNJ. JABATAN', 'DASAR TAPERA', 'POTONGAN ASN (2.5%)', 'BEBAN PEMDA (0.5%)', 'TOTAL (3%)'];
            $colIdx = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($colIdx.'3', $h);
                $colIdx++;
            }
            $sheet->getStyle('A3:L3')->getFont()->setBold(true);
            $sheet->getStyle('A3:L3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCBD5E1');

            $row = 4;
            $no = 1;
            foreach ($realisasis as $item) {
                $nip = $item->pegawai->nip ?? ($item->raw_data['nip'] ?? $item->raw_data['NIP'] ?? '');
                $nama = $item->pegawai->nama ?? ($item->raw_data['nama'] ?? $item->raw_data['Nama'] ?? '');
                $skpd = $item->pegawai->unitKerja->skpd ?? ($item->raw_data['SKPD'] ?? '');

                $sheet->setCellValue('A'.$row, $no++);
                $sheet->setCellValueExplicit('B'.$row, $nip, DataType::TYPE_STRING);
                $sheet->setCellValue('C'.$row, $nama);
                $sheet->setCellValue('D'.$row, $item->kategori_asn);
                $sheet->setCellValue('E'.$row, $skpd);
                $sheet->setCellValue('F'.$row, $item->gaji_pokok);
                $sheet->setCellValue('G'.$row, $item->tunj_keluarga);
                $sheet->setCellValue('H'.$row, $item->tunj_jabatan);
                $sheet->setCellValue('I'.$row, $item->dasar_tapera);
                $sheet->setCellValue('J'.$row, $item->simulasi_tapera_asn);
                $sheet->setCellValue('K'.$row, $item->simulasi_tapera_pk);
                $sheet->setCellValue('L'.$row, $item->simulasi_tapera_total);
                $row++;
            }

            $sheet->getStyle('A3:L'.($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('F4:L'.($row - 1))->getNumberFormat()->setFormatCode('#,##0');

            foreach (range('A', 'L') as $col) {
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
        $kategoriFilter = $request->get('kategori_filter', 'all');

        if ($tab === 'rekap') {
            $rekaps = $this->getRekapData($periode === 'Semua Periode' ? null : $periode, $skpdFilter, $kategoriFilter);

            $pdf = Pdf::loadView('laporan.tapera.pdf', compact('tab', 'rekaps', 'periode', 'skpdFilter', 'kategoriFilter'))
                ->setPaper('a4', 'landscape');
        } else {
            $query = RealisasiGaji::with(['pegawai.unitKerja', 'pegawai.jabatan'])
                ->when($periode && $periode !== 'Semua Periode', fn ($q) => $q->where('periode', $periode))
                ->when($skpdFilter, fn ($q) => $q->whereHas('pegawai.unitKerja', fn ($u) => $u->where('skpd', $skpdFilter)));

            if ($kategoriFilter === 'pns') {
                $query->whereHas('pegawai', fn ($p) => $p->where('status_pegawai', 'PNS'))
                    ->whereRaw('UPPER(COALESCE(json_extract(raw_data, "$.kelompok_upload"), "")) NOT LIKE "%PARUH WAKTU%"');
            } elseif ($kategoriFilter === 'pppk') {
                $query->whereHas('pegawai', fn ($p) => $p->where('status_pegawai', 'PPPK'))
                    ->whereRaw('UPPER(COALESCE(json_extract(raw_data, "$.kelompok_upload"), "")) NOT LIKE "%PARUH WAKTU%"');
            } elseif ($kategoriFilter === 'pppk_pw') {
                $query->where(function ($q) {
                    $q->whereHas('pegawai', fn ($p) => $p->where('status_pegawai', 'LIKE', '%PARUH WAKTU%'))
                        ->orWhereRaw('UPPER(COALESCE(json_extract(raw_data, "$.kelompok_upload"), "")) LIKE "%PARUH WAKTU%"');
                });
            }

            $realisasis = $query->limit(500)->get();

            $pdf = Pdf::loadView('laporan.tapera.pdf', compact('tab', 'realisasis', 'periode', 'skpdFilter', 'kategoriFilter'))
                ->setPaper('a4', 'landscape');
        }

        return $pdf->download('Simulasi_Tapera_ASN_Pemda_'.date('Ymd_His').'.pdf');
    }
}
