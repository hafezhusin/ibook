<?php

namespace App\Http\Controllers;

use App\Models\Tempahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KelulusanController extends Controller
{
    public function index()
    {
        $menunggu = Tempahan::with(['bilik', 'pengguna'])
            ->where('status', Tempahan::STATUS_MENUNGGU)
            ->orderBy('tarikh')
            ->get();

        return view('kelulusan.index', compact('menunggu'));
    }

    public function lulus(Tempahan $tempahan)
    {
        if (!$tempahan->isMenunggu()) {
            return back()->with('error', 'Tempahan ini telah diproses.');
        }

        $tempahan->update([
            'status' => Tempahan::STATUS_DILULUSKAN,
            'diluluskan_oleh' => Auth::id(),
            'diluluskan_pada' => now(),
            'catatan_penolakan' => null,
        ]);

        return back()->with('success', "Tempahan '{$tempahan->nama_mesyuarat}' telah diluluskan.");
    }

    public function tolak(Request $request, Tempahan $tempahan)
    {
        if (!$tempahan->isMenunggu()) {
            return back()->with('error', 'Tempahan ini telah diproses.');
        }

        $request->validate([
            'catatan_penolakan' => 'nullable|string|max:500',
        ]);

        $tempahan->update([
            'status' => Tempahan::STATUS_DITOLAK,
            'diluluskan_oleh' => Auth::id(),
            'diluluskan_pada' => now(),
            'catatan_penolakan' => $request->catatan_penolakan,
        ]);

        return back()->with('success', "Tempahan '{$tempahan->nama_mesyuarat}' telah ditolak.");
    }
}
