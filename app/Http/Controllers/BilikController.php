<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBilikRequest;
use App\Http\Requests\UpdateBilikRequest;
use App\Models\BilikMesyuarat;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // Pra-kira kiraan tempahan bulan ini dalam satu query (elak N+1)
        $bulan = now()->month;
        $tahun = now()->year;
        $maxSesi = now()->daysInMonth * 2;

        $bilik = BilikMesyuarat::withCount([
            'tempahan as tempahan_bulan_ini' => function ($q) use ($bulan, $tahun) {
                $q->whereMonth('tarikh', $bulan)
                  ->whereYear('tarikh', $tahun)
                  ->where('status', 'diluluskan');
            },
        ])->get();

        // Tetapkan peratus penggunaan sebagai atribut dinamik
        $bilik->each(function ($b) use ($maxSesi) {
            $b->peratus_penggunaan = $maxSesi > 0
                ? (int) round(($b->tempahan_bulan_ini / $maxSesi) * 100)
                : 0;
        });

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
        $data = $request->validated();
        $data['dikemaskini_oleh'] = auth()->user()->name;
        $data['dikemaskini_pada'] = now();

        $bilik->update($data);
        AuditLogger::catat('kemaskini_bilik', $bilik, ['nama' => $bilik->nama]);

        return redirect()->route('bilik.index')
            ->with('success', 'Maklumat bilik mesyuarat berjaya dikemaskini.');
    }

    public function destroy(BilikMesyuarat $bilik)
    {
        // Blok padam jika ada SEBARANG rekod tempahan (aktif ATAU sejarah)
        if ($bilik->tempahan()->exists()) {
            return back()->with('error',
                'Bilik tidak boleh dipadam kerana mempunyai rekod tempahan. ' .
                'Sila nyahaktifkan bilik ini sebagai gantinya.'
            );
        }

        AuditLogger::catat('padam_bilik', null, ['nama' => $bilik->nama, 'id' => $bilik->id]);
        $bilik->delete(); // SoftDelete — rekod kekal dalam DB
        return redirect()->route('bilik.index')
            ->with('success', 'Bilik mesyuarat berjaya dipadam.');
    }
}
