<?php

namespace App\Http\Controllers;

use App\Models\UnmatchedNip;
use Illuminate\Http\Request;

class UnmatchedNipController extends Controller
{
    public function index(Request $request)
    {
        $query = UnmatchedNip::query();

        if ($request->filled('periode')) {
            $query->where('periode', $request->periode);
        }

        if ($request->filled('jenis_file')) {
            $query->where('jenis_file', $request->jenis_file);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('laporan.unmatched-nip.index', compact('logs'));
    }

    public function destroyAll()
    {
        UnmatchedNip::truncate();

        return redirect()->back()->with('success', 'Semua riwayat Log NIP Tidak Ditemukan berhasil dihapus.');
    }
}
