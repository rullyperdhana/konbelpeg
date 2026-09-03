<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.index');
    }

    public function pegawai(Request $request)
    {
        $query = Pegawai::with(['jabatan', 'unitKerja']);

        if ($request->has('skpd_filter') && $request->skpd_filter != '') {
            $skpd = $request->skpd_filter;
            $query->whereHas('unitKerja', function ($q) use ($skpd) {
                $q->where('skpd', $skpd);
            });
        }

        if ($request->has('status_filter') && $request->status_filter != '') {
            $query->where('status_pegawai', $request->status_filter);
        }

        // For reporting, we might want to paginate heavily or get all. We'll use heavy pagination.
        $pegawais = $query->paginate(100);
        $filterUnitKerjas = UnitKerja::select('skpd')->whereNotNull('skpd')->distinct()->orderBy('skpd')->get();

        return view('laporan.pegawai', compact('pegawais', 'filterUnitKerjas'));
    }
}
