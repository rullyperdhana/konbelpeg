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
        $periodeTppKas = RealisasiTpp::whereNotNull('periode_kas')->select('periode_kas as p')->distinct()->pluck('p')->toArray();
        $periodeTpp = RealisasiTpp::select('periode as p')->distinct()->pluck('p')->toArray();
        $allPeriodes = array_values(array_unique(array_merge($periodeGaji, $periodeTppKas, $periodeTpp)));
        sort($allPeriodes);

        $daftarJenisGaji = RealisasiGaji::DAFTAR_JENIS_GAJI;

        return view('setting.data.index', compact('allPeriodes', 'daftarJenisGaji'));
    }

    public function destroy(Request $request)
    {
        $jenis = $request->input('jenis');
        $periode = $request->input('periode');
        $status_pegawai = $request->input('status_pegawai');
        $jenis_gaji = $request->input('jenis_gaji');

        if (! $jenis || ! $periode || ! $status_pegawai) {
            return redirect()->back()->with('error', 'Semua parameter harus dipilih.');
        }

        try {
            DB::beginTransaction();

            $deletedCount = 0;

            if ($jenis == 'GAJI') {
                $query = RealisasiGaji::where('periode', $periode);
                if ($jenis_gaji && $jenis_gaji !== 'SEMUA') {
                    $query->where('jenis_gaji', $jenis_gaji);
                }
                if ($status_pegawai != 'SEMUA') {
                    $query->whereHas('pegawai', function ($q) use ($status_pegawai) {
                        $q->whereRaw('UPPER(status_pegawai) = ?', [strtoupper($status_pegawai)]);
                    });
                }
                $deletedCount = $query->delete();
                $kriteriaLabel = ($jenis_gaji && $jenis_gaji !== 'SEMUA') ? " ({$jenis_gaji})" : '';
                $message = "Berhasil menghapus $deletedCount data Realisasi Gaji{$kriteriaLabel} periode $periode untuk status $status_pegawai.";
            } elseif ($jenis == 'TPP') {
                $query = RealisasiTpp::where(function ($q) use ($periode) {
                    $q->where('periode', $periode)
                        ->orWhere('periode_kas', $periode);
                });
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
