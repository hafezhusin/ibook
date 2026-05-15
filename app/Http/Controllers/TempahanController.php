<?php

namespace App\Http\Controllers;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TempahanExport;

class TempahanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Tempahan::with(['bilik', 'pengguna']);

        if ($user->isStaf()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('bilik_id')) {
            $query->where('bilik_id', $request->bilik_id);
        }

        if ($request->filled('carian')) {
            $carian = $request->carian;
            $query->where('nama_mesyuarat', 'like', "%$carian%");
        }

        $tempahan = $query->orderByDesc('tarikh')->orderBy('masa_mula')->paginate(15);
        $bilik = BilikMesyuarat::where('status', 'aktif')->get();

        return view('tempahan.index', compact('tempahan', 'bilik'));
    }

    public function create()
    {
        $bilik = BilikMesyuarat::where('status', 'aktif')->get();
        $kategori = Tempahan::KATEGORI;
        $sesi = Tempahan::MASA_SESI;
        return view('tempahan.create', compact('bilik', 'kategori', 'sesi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mesyuarat' => 'required|string|max:255',
            'tarikh' => 'required|date|after_or_equal:today',
            'bilik_id' => 'required|exists:bilik_mesyuarat,id',
            'sesi' => 'required|in:pagi,petang',
            'bilangan_peserta' => 'required|integer|min:1',
            'kategori' => 'required|string',
            'nama_pengerusi' => 'required|string|max:255',
            'tujuan' => 'nullable|string|max:1000',
        ], [
            'nama_mesyuarat.required' => 'Sila masukkan nama mesyuarat.',
            'tarikh.required' => 'Sila pilih tarikh.',
            'tarikh.after_or_equal' => 'Tarikh mesti hari ini atau selepasnya.',
            'bilik_id.required' => 'Sila pilih bilik mesyuarat.',
            'sesi.required' => 'Sila pilih sesi mesyuarat.',
            'bilangan_peserta.required' => 'Sila masukkan bilangan peserta.',
            'kategori.required' => 'Sila pilih kategori mesyuarat.',
            'nama_pengerusi.required' => 'Sila masukkan nama pengerusi.',
        ]);

        // Semak pertindihan tempahan
        $masaSesi = Tempahan::MASA_SESI[$validated['sesi']];
        $konflik = Tempahan::where('bilik_id', $validated['bilik_id'])
            ->where('tarikh', $validated['tarikh'])
            ->where('sesi', $validated['sesi'])
            ->where('status', '!=', Tempahan::STATUS_DITOLAK)
            ->exists();

        if ($konflik) {
            return back()->withInput()->withErrors([
                'sesi' => 'Bilik telah ditempah untuk sesi ini pada tarikh tersebut.'
            ]);
        }

        // Semak kapasiti
        $bilik = BilikMesyuarat::findOrFail($validated['bilik_id']);
        if ($validated['bilangan_peserta'] > $bilik->kapasiti) {
            return back()->withInput()->withErrors([
                'bilangan_peserta' => "Bilangan peserta melebihi kapasiti bilik ({$bilik->kapasiti} orang)."
            ]);
        }

        Tempahan::create([
            ...$validated,
            'masa_mula' => $masaSesi['mula'],
            'masa_tamat' => $masaSesi['tamat'],
            'user_id' => Auth::id(),
            'status' => Tempahan::STATUS_MENUNGGU,
        ]);

        return redirect()->route('tempahan.index')
            ->with('success', 'Permohonan tempahan telah dihantar dan menunggu kelulusan.');
    }

    public function show(Tempahan $tempahan)
    {
        $user = Auth::user();
        if ($user->isStaf() && $tempahan->user_id !== $user->id) {
            abort(403);
        }
        $tempahan->load(['bilik', 'pengguna', 'pelulus']);
        return view('tempahan.show', compact('tempahan'));
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $query = Tempahan::with(['bilik', 'pengguna']);

        if ($user->isStaf()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tempahan = $query->orderByDesc('tarikh')->get();
        $pdf = Pdf::loadView('tempahan.pdf', compact('tempahan'));
        return $pdf->download('senarai-tempahan.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new TempahanExport($request->all()), 'senarai-tempahan.xlsx');
    }
}
