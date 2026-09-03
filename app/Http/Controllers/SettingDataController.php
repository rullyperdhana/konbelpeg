<?php

namespace App\Http\Controllers;

use App\Models\RealisasiGaji;
use App\Models\RealisasiTpp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingDataController extends Controller
{
    public function index()
    {
        $periodeGaji = RealisasiGaji::select('periode')->distinct()->pluck('periode')->toArray();
        $periodeTpp = RealisasiTpp::select('periode')->distinct()->pluck('periode')->toArray();
        $allPeriodes = array_unique(array_merge($periodeGaji, $periodeTpp));
        sort($allPeriodes);

        return view('setting.data.index', compact('allPeriodes'));
    }

    public function destroy(Request $request)
    {
        $jenis = $request->input('jenis');
        $periode = $request->input('periode');
        $status_pegawai = $request->input('status_pegawai');

        if (! $jenis || ! $periode || ! $status_pegawai) {
            return redirect()->back()->with('error', 'Semua parameter harus dipilih.');
        }

        try {
            DB::beginTransaction();

            $deletedCount = 0;

            if ($jenis == 'GAJI') {
                $query = RealisasiGaji::where('periode', $periode);
                if ($status_pegawai != 'SEMUA') {
                    $query->whereHas('pegawai', function ($q) use ($status_pegawai) {
                        $q->whereRaw('UPPER(status_pegawai) = ?', [strtoupper($status_pegawai)]);
                    });
                }
                $deletedCount = $query->delete();
                $message = "Berhasil menghapus $deletedCount data Realisasi Gaji periode $periode untuk status $status_pegawai.";
            } elseif ($jenis == 'TPP') {
                $query = RealisasiTpp::where('periode', $periode);
                if ($status_pegawai != 'SEMUA') {
                    $query->whereHas('pegawai', function ($q) use ($status_pegawai) {
                        $q->whereRaw('UPPER(status_pegawai) = ?', [strtoupper($status_pegawai)]);
                    });
                }
                $deletedCount = $query->delete();
                $message = "Berhasil menghapus $deletedCount data Realisasi TPP periode $periode untuk status $status_pegawai.";
            } else {
                DB::rollBack();

                return redirect()->back()->with('error', 'Jenis data tidak valid.');
            }

            DB::commit();

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }
}
