<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\RealisasiGaji;
use App\Models\RealisasiTpp;
use App\Models\UnitKerja;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPegawai = Pegawai::count();
        $totalSkpd = UnitKerja::whereNotNull('skpd')->distinct('skpd')->count();
        $totalRealisasiTpp = RealisasiTpp::sum('total_dibayarkan');

        // Now we can sum gaji_bersih from realisasi_gajis
        $totalRealisasiGaji = RealisasiGaji::sum('gaji_bersih');

        return view('dashboard', compact('totalPegawai', 'totalSkpd', 'totalRealisasiTpp', 'totalRealisasiGaji'));
    }
}
