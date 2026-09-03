<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    public function index(Request $request)
    {
        $query = UnitKerja::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('skpd', 'like', "%{$search}%")
                ->orWhere('upt', 'like', "%{$search}%")
                ->orWhere('satker', 'like', "%{$search}%");
        }

        $unitKerjas = $query->paginate(20);

        return view('master.skpd', compact('unitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'skpd' => 'required|string|max:255',
            'upt' => 'nullable|string|max:255',
            'satker' => 'nullable|string|max:255',
        ]);

        UnitKerja::create($validated);

        return redirect()->back()->with('success', 'Data SKPD berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'skpd' => 'required|string|max:255',
            'upt' => 'nullable|string|max:255',
            'satker' => 'nullable|string|max:255',
        ]);

        $unitKerja = UnitKerja::findOrFail($id);
        $unitKerja->update($validated);

        return redirect()->back()->with('success', 'Data SKPD berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $unitKerja = UnitKerja::findOrFail($id);

        if ($unitKerja->pegawais()->count() > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus Unit Kerja karena masih memiliki pegawai terkait.');
        }

        $unitKerja->delete();

        return redirect()->back()->with('success', 'Data SKPD berhasil dihapus.');
    }
}
