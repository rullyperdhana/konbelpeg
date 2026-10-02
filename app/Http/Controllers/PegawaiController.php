<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $query = Pegawai::with(['jabatan', 'unitKerja', 'simgajiKeluargas']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        if ($request->has('skpd_filter') && $request->skpd_filter != '') {
            $skpd = $request->skpd_filter;
            $query->whereHas('unitKerja', function ($q) use ($skpd) {
                $q->where('skpd', $skpd);
            });
        }

        $pegawais = $query->paginate(20);
        $jabatans = Jabatan::orderBy('nama')->get();
        // Load distinct SKPD names for the filter dropdown
        $filterUnitKerjas = UnitKerja::select('skpd')->whereNotNull('skpd')->distinct()->orderBy('skpd')->get();
        // Load all unit kerjas for the Add/Edit form
        $unitKerjas = UnitKerja::orderBy('skpd')->get();

        return view('pegawai.index', compact('pegawais', 'jabatans', 'unitKerjas', 'filterUnitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:pegawais',
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:30',
            'no_rekening' => 'nullable|string|max:50',
            'nama_bank' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:30',
            'no_karpeg' => 'nullable|string|max:30',
            'tempat_lahir' => 'nullable|string',
            'tgl_lahir' => 'nullable|date',
            'jk' => 'nullable|string',
            'agama' => 'nullable|string',
            'status_pegawai' => 'nullable|string',
            'golru' => 'nullable|string',
            'tmt_golru' => 'nullable|string',
            'tk_ijazah' => 'nullable|string',
            'nm_pendidikan' => 'nullable|string',
            'jabatan_id' => 'nullable|exists:jabatans,id',
            'unit_kerja_id' => 'nullable|exists:unit_kerjas,id',
        ]);

        Pegawai::create($validated);

        return redirect()->back()->with('success', 'Data Pegawai berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $validated = $request->validate([
            'nip' => 'required|string|unique:pegawais,nip,'.$pegawai->id,
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:30',
            'no_rekening' => 'nullable|string|max:50',
            'nama_bank' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:30',
            'no_karpeg' => 'nullable|string|max:30',
            'tempat_lahir' => 'nullable|string',
            'tgl_lahir' => 'nullable|date',
            'jk' => 'nullable|string',
            'agama' => 'nullable|string',
            'status_pegawai' => 'nullable|string',
            'golru' => 'nullable|string',
            'tmt_golru' => 'nullable|string',
            'tk_ijazah' => 'nullable|string',
            'nm_pendidikan' => 'nullable|string',
            'jabatan_id' => 'nullable|exists:jabatans,id',
            'unit_kerja_id' => 'nullable|exists:unit_kerjas,id',
        ]);

        $pegawai->update($validated);

        return redirect()->back()->with('success', 'Data Pegawai berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->delete();

        return redirect()->back()->with('success', 'Data Pegawai berhasil dihapus.');
    }
}
