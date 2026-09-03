<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Jabatan::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama', 'like', "%{$search}%")
                ->orWhere('jenis', 'like', "%{$search}%")
                ->orWhere('eselon', 'like', "%{$search}%");
        }

        $jabatans = $query->paginate(20);

        return view('master.jabatan', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jenis' => 'nullable|string|max:255',
            'eselon' => 'nullable|string|max:255',
        ]);

        Jabatan::create($validated);

        return redirect()->back()->with('success', 'Data Jabatan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jenis' => 'nullable|string|max:255',
            'eselon' => 'nullable|string|max:255',
        ]);

        $jabatan = Jabatan::findOrFail($id);
        $jabatan->update($validated);

        return redirect()->back()->with('success', 'Data Jabatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jabatan = Jabatan::findOrFail($id);

        if ($jabatan->pegawais()->count() > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus Jabatan karena masih ada pegawai yang mengembannya.');
        }

        $jabatan->delete();

        return redirect()->back()->with('success', 'Data Jabatan berhasil dihapus.');
    }
}
