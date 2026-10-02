<?php

namespace App\Http\Controllers;

use App\Models\UnmatchedNip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnmatchedNipController extends Controller
{
    /**
     * Tampilkan halaman daftar log NIP tidak ditemukan saat impor.
     */
    public function index(Request $request): View
    {
        $query = UnmatchedNip::query();

        if ($request->filled('periode')) {
            $query->where('periode', $request->periode);
        }

        if ($request->filled('jenis_file')) {
            $query->where('jenis_file', $request->jenis_file);
        }

        if ($request->filled('status_pegawai')) {
            $query->where('status_pegawai', $request->status_pegawai);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        $availablePeriodes = UnmatchedNip::select('periode')->distinct()->whereNotNull('periode')->pluck('periode');
        $statusOptions = ['PNS', 'PPPK', 'PPPK PARUH WAKTU', 'Pejabat Negara'];

        $countTotal = UnmatchedNip::count();
        $countPns = UnmatchedNip::where('status_pegawai', 'PNS')->count();
        $countPppk = UnmatchedNip::where('status_pegawai', 'PPPK')->count();

        return view('laporan.unmatched-nip.index', compact(
            'logs',
            'availablePeriodes',
            'statusOptions',
            'countTotal',
            'countPns',
            'countPppk'
        ));
    }

    /**
     * Hapus semua riwayat log NIP yang tidak cocok.
     */
    public function destroyAll(): RedirectResponse
    {
        UnmatchedNip::truncate();

        return redirect()->back()->with('success', 'Semua riwayat Log NIP Tidak Ditemukan berhasil dihapus.');
    }
}
