<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\RealisasiTpp;
use App\Models\UnitKerja;
use App\Models\UnmatchedNip;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPegawai = Pegawai::count();
        $totalSkpd = UnitKerja::whereNotNull('skpd')->distinct('skpd')->count();
        $totalUnitKerja = UnitKerja::count();

        // Status Pegawai
        $pegawaiByStatus = Pegawai::groupBy('status_pegawai')
            ->selectRaw('status_pegawai, count(*) as total')
            ->pluck('total', 'status_pegawai');

        $pnsCount = $pegawaiByStatus['PNS'] ?? 0;
        $pppkCount = $pegawaiByStatus['PPPK'] ?? 0;
        $paruhWaktuCount = $pegawaiByStatus['PPPK PARUH WAKTU'] ?? 0;

        // Kelompok / Jenis Pegawai
        $pegawaiByJenis = Pegawai::groupBy('jenis_pegawai')
            ->selectRaw('jenis_pegawai, count(*) as total')
            ->pluck('total', 'jenis_pegawai');

        // Gaji Summary
        $gajiSummary = RealisasiGaji::selectRaw('
            sum(gaji_pokok) as total_gapok,
            sum(pajak) as total_pajak,
            sum(iwp) as total_iwp,
            sum(potongan_lain) as total_potongan,
            sum(gaji_bersih) as total_bersih,
            count(*) as total_transaksi
        ')->first();

        // TPP Summary
        $tppSummary = RealisasiTpp::selectRaw('
            sum(tpp_bruto) as total_bruto,
            sum(tpp_netto) as total_netto,
            sum(pph_21) as total_pph,
            sum(potongan_lainnya) as total_potongan,
            sum(iuran_iwp) as total_iwp,
            sum(total_dibayarkan) as total_dibayarkan,
            count(*) as total_transaksi
        ')->first();

        $totalRealisasiGaji = (float) ($gajiSummary->total_bersih ?? 0);
        $totalRealisasiTpp = (float) ($tppSummary->total_dibayarkan ?? 0);
        $totalBelanjaPegawai = $totalRealisasiGaji + $totalRealisasiTpp;

        // Gaji per Periode
        $gajiPerPeriode = RealisasiGaji::groupBy('periode')
            ->selectRaw('periode, count(*) as total_pegawai, sum(gaji_pokok) as gapok, sum(gaji_bersih) as bersih')
            ->orderBy('periode')
            ->get();

        // Gaji per Kriteria
        $gajiPerKriteria = RealisasiGaji::groupBy('jenis_gaji')
            ->selectRaw('coalesce(jenis_gaji, "Gaji Induk") as kriteria, count(*) as total_transaksi, sum(gaji_bersih) as total_bersih')
            ->orderByDesc('total_bersih')
            ->get();

        // Top 5 SKPD Belanja Terbesar
        $topSkpdGaji = RealisasiGaji::join('pegawais', 'realisasi_gajis.pegawai_id', '=', 'pegawais.id')
            ->join('unit_kerjas', 'pegawais.unit_kerja_id', '=', 'unit_kerjas.id')
            ->groupBy('unit_kerjas.skpd')
            ->selectRaw('unit_kerjas.skpd, count(realisasi_gajis.id) as total_transaksi, sum(realisasi_gajis.gaji_bersih) as total_gaji')
            ->orderByDesc('total_gaji')
            ->limit(5)
            ->get();

        // Unmatched NIP
        $unmatchedCount = UnmatchedNip::count();
        $unmatchedRecent = UnmatchedNip::latest()->limit(5)->get();

        return view('dashboard', compact(
            'totalPegawai',
            'totalSkpd',
            'totalUnitKerja',
            'pnsCount',
            'pppkCount',
            'paruhWaktuCount',
            'pegawaiByJenis',
            'pegawaiByStatus',
            'gajiSummary',
            'tppSummary',
            'totalRealisasiGaji',
            'totalRealisasiTpp',
            'totalBelanjaPegawai',
            'gajiPerPeriode',
            'gajiPerKriteria',
            'topSkpdGaji',
            'unmatchedCount',
            'unmatchedRecent'
        ));
    }
}
