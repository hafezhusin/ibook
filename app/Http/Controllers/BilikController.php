<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBilikRequest;
use App\Http\Requests\UpdateBilikRequest;
use App\Models\BilikMesyuarat;
use App\Services\AuditLogger;
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

    public function store(StoreBilikRequest $request)
    {
        $bilik = BilikMesyuarat::create($request->validated());
        AuditLogger::catat('tambah_bilik', $bilik, ['nama' => $bilik->nama]);

        return redirect()->route('bilik.index')
            ->with('success', 'Bilik mesyuarat berjaya ditambah.');
    }

    public function edit(BilikMesyuarat $bilik)
    {
        return view('bilik.form', compact('bilik'));
    }

    public function update(UpdateBilikRequest $request, BilikMesyuarat $bilik)
    {
        $bilik->update($request->validated());
        AuditLogger::catat('kemaskini_bilik', $bilik, ['nama' => $bilik->nama]);

        return redirect()->route('bilik.index')
            ->with('success', 'Maklumat bilik mesyuarat berjaya dikemaskini.');
    }

    public function destroy(BilikMesyuarat $bilik)
    {
        if ($bilik->tempahan()->where('status', '!=', 'ditolak')->exists()) {
            return back()->with('error', 'Bilik tidak boleh dipadam kerana mempunyai tempahan aktif.');
        }

        AuditLogger::catat('padam_bilik', null, ['nama' => $bilik->nama, 'id' => $bilik->id]);
        $bilik->delete();
        return redirect()->route('bilik.index')
            ->with('success', 'Bilik mesyuarat berjaya dipadam.');
    }
}
