<?php

namespace App\Http\Controllers;

use App\Models\BilikMesyuarat;
use Illuminate\Http\Request;

class BilikController extends Controller
{
    // Route awam - senarai bilik tanpa log masuk
    public function publicList()
    {
        $bilik = BilikMesyuarat::where('status', 'aktif')
            ->get(['id', 'nama', 'kapasiti', 'kemudahan', 'lokasi']);
        return response()->json($bilik);
    }

    public function index()
    {
        $bilik = BilikMesyuarat::all();
        return view('bilik.index', compact('bilik'));
    }

    public function create()
    {
        return view('bilik.form', ['bilik' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kapasiti' => 'required|integer|min:1',
            'lokasi' => 'nullable|string|max:255',
            'kemudahan' => 'nullable|array',
            'kemudahan.*' => 'string',
            'status' => 'required|in:aktif,tidak_aktif',
        ], [
            'nama.required' => 'Sila masukkan nama bilik.',
            'kapasiti.required' => 'Sila masukkan kapasiti bilik.',
        ]);

        BilikMesyuarat::create($validated);

        return redirect()->route('bilik.index')
            ->with('success', 'Bilik mesyuarat berjaya ditambah.');
    }

    public function edit(BilikMesyuarat $bilik)
    {
        return view('bilik.form', compact('bilik'));
    }

    public function update(Request $request, BilikMesyuarat $bilik)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kapasiti' => 'required|integer|min:1',
            'lokasi' => 'nullable|string|max:255',
            'kemudahan' => 'nullable|array',
            'kemudahan.*' => 'string',
            'status' => 'required|in:aktif,tidak_aktif',
        ]);

        $bilik->update($validated);

        return redirect()->route('bilik.index')
            ->with('success', 'Maklumat bilik mesyuarat berjaya dikemaskini.');
    }

    public function destroy(BilikMesyuarat $bilik)
    {
        if ($bilik->tempahan()->where('status', '!=', 'ditolak')->exists()) {
            return back()->with('error', 'Bilik tidak boleh dipadam kerana mempunyai tempahan aktif.');
        }

        $bilik->delete();
        return redirect()->route('bilik.index')
            ->with('success', 'Bilik mesyuarat berjaya dipadam.');
    }
}
