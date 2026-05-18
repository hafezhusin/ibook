<?php

namespace App\Http\Controllers;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TempahanExport;
use Illuminate\Support\Facades\Cache;

class TempahanController extends Controller
{
    /**
     * Bina query asas yang ditapis mengikut hak akses pengguna:
     * - Staf: rekod sendiri + rekod rakan seunit (jabatan sama)
     * - Pentadbir / Urus Setia: semua rekod
     */
    private function unitQuery()
    {
        $user  = Auth::user();
        $query = Tempahan::query();

        if ($user->isStaf()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->jabatan) {
                    $q->orWhereHas('pengguna', fn ($q2) => $q2->where('jabatan', $user->jabatan));
                }
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = $this->unitQuery()
            ->select([
                'id',
                'nama_mesyuarat',
                'tarikh',
                'masa_mula',
                'masa_tamat',
                'bilik_id',
                'user_id',
                'bilangan_peserta',
                'kategori',
                'status',
                'dikemaskini_oleh',
                'dikemaskini_pada',
                'created_at',
                'updated_at',
            ])
            ->with([
                'bilik:id,nama',
                'pengguna:id,name',
                'pengubah:id,name',
            ]);

        // ── Tapis bilik ───────────────────────────────────────────────
        if ($request->filled('bilik_id')) {
            $query->where('bilik_id', $request->bilik_id);
        }

        // ── Tapis carian nama ────────────────────────────────────────
        if ($request->filled('carian')) {
            $query->where('nama_mesyuarat', 'like', '%' . $request->carian . '%');
        }

        // ── Tapis status ─────────────────────────────────────────────
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ── Tapis kategori mesyuarat ─────────────────────────────────
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // ── Tapis julat tarikh (lanjutan) ─────────────────────────────
        if ($request->filled('tarikh_dari')) {
            $query->whereDate('tarikh', '>=', $request->tarikh_dari);
        }
        if ($request->filled('tarikh_hingga')) {
            $query->whereDate('tarikh', '<=', $request->tarikh_hingga);
        }

        // ── Tapis unit/jabatan (pentadbir sahaja) ────────────────────
        if ($request->filled('jabatan') && !$user->isStaf()) {
            $query->whereHas('pengguna', fn ($q) => $q->where('jabatan', 'like', '%' . $request->jabatan . '%'));
        }

        // ── Tapis tarikh pantas ──────────────────────────────────────
        switch ($request->get('tarikh_filter')) {
            case 'hari_ini':
                $query->whereDate('tarikh', today());
                break;
            case 'esok':
                $query->whereDate('tarikh', today()->addDay());
                break;
            case 'baharu':
                $query->where('created_at', '>=', now()->subHours(24));
                break;
            case '7_hari':
                $query->whereBetween('tarikh', [today(), today()->addDays(7)]);
                break;
            case 'bulan_ini':
                $query->whereMonth('tarikh', now()->month)->whereYear('tarikh', now()->year);
                break;
            case 'akan_datang':
                $query->where('tarikh', '>=', today());
                break;
        }

        $tempahan  = $query->orderByDesc('tarikh')->orderBy('masa_mula')->paginate(20)->withQueryString();
        $bilik     = BilikMesyuarat::where('status', 'aktif')->get();
        $kategori  = Tempahan::KATEGORI;

        // ── Worklist ringkasan ────────────────────────────────────────
        // Dikira berdasarkan skop unit (unit untuk staf, semua untuk admin/urus setia)
        $today = today()->toDateString();
        $esok = today()->addDay()->toDateString();
        $sub24Jam = now()->subHours(24);
        $bulan = now()->month;
        $tahun = now()->year;

        $ringkasanAgg = $this->unitQuery()
            ->selectRaw(
                "SUM(CASE WHEN tarikh = ? AND status = ? THEN 1 ELSE 0 END) AS hari_ini,
                 SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) AS baharu,
                 SUM(CASE WHEN tarikh = ? AND status = ? THEN 1 ELSE 0 END) AS esok,
                 SUM(CASE WHEN MONTH(tarikh) = ? AND YEAR(tarikh) = ? THEN 1 ELSE 0 END) AS bulan_ini",
                [$today, Tempahan::STATUS_DILULUSKAN, $sub24Jam, $esok, Tempahan::STATUS_DILULUSKAN, $bulan, $tahun]
            )
            ->first();

        $ringkasan = [
            'hari_ini'  => (int) ($ringkasanAgg->hari_ini ?? 0),
            'baharu'    => (int) ($ringkasanAgg->baharu ?? 0),
            'esok'      => (int) ($ringkasanAgg->esok ?? 0),
            'bulan_ini' => (int) ($ringkasanAgg->bulan_ini ?? 0),
        ];

        return view('tempahan.index', compact('tempahan', 'bilik', 'ringkasan', 'kategori'));
    }

    public function create(Request $request)
    {
        $bilik    = BilikMesyuarat::where('status', 'aktif')->get();
        $kategori = Tempahan::KATEGORI;
        $sesi     = Tempahan::MASA_SESI;

        $duplikat = null;
        if ($request->filled('duplikat_id')) {
            $asal = Tempahan::find($request->duplikat_id);
            if ($asal) {
                $duplikat = [
                    'nama_mesyuarat'   => $asal->nama_mesyuarat,
                    'bilik_id'         => $asal->bilik_id,
                    'bilangan_peserta' => $asal->bilangan_peserta,
                    'kategori'         => $asal->kategori,
                    'nama_pengerusi'   => $asal->nama_pengerusi,
                    'tujuan'           => $asal->tujuan,
                ];
            }
        }

        return view('tempahan.create', compact('bilik', 'kategori', 'sesi', 'duplikat'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mesyuarat'   => 'required|string|max:255',
            'tarikh'           => 'required|date|after_or_equal:today',
            'bilik_id'         => 'required|exists:bilik_mesyuarat,id',
            'sesi'             => 'required|array|min:1',
            'sesi.*'           => 'in:pagi,petang',
            'bilangan_peserta' => 'required|integer|min:1',
            'kategori'         => 'required|string',
            'nama_pengerusi'   => 'required|string|max:255',
            'tujuan'           => 'nullable|string|max:1000',
        ], [
            'nama_mesyuarat.required'   => 'Sila masukkan nama mesyuarat.',
            'tarikh.required'           => 'Sila pilih tarikh.',
            'tarikh.after_or_equal'     => 'Tarikh mesti hari ini atau selepasnya.',
            'bilik_id.required'         => 'Sila pilih bilik mesyuarat.',
            'sesi.required'             => 'Sila pilih sekurang-kurangnya satu sesi mesyuarat.',
            'sesi.min'                  => 'Sila pilih sekurang-kurangnya satu sesi mesyuarat.',
            'bilangan_peserta.required' => 'Sila masukkan bilangan peserta.',
            'kategori.required'         => 'Sila pilih kategori mesyuarat.',
            'nama_pengerusi.required'   => 'Sila masukkan nama pengerusi.',
        ]);

        $bilik = BilikMesyuarat::findOrFail($validated['bilik_id']);
        if ($validated['bilangan_peserta'] > $bilik->kapasiti) {
            return back()->withInput()->withErrors([
                'bilangan_peserta' => "Bilangan peserta melebihi kapasiti bilik ({$bilik->kapasiti} orang)."
            ]);
        }

        $konflikSesi = [];
        foreach ($validated['sesi'] as $sesi) {
            $konflik = Tempahan::where('bilik_id', $validated['bilik_id'])
                ->where('tarikh', $validated['tarikh'])
                ->where('sesi', $sesi)
                ->where('status', '!=', Tempahan::STATUS_DITOLAK)
                ->exists();
            if ($konflik) {
                $konflikSesi[] = Tempahan::MASA_SESI[$sesi]['label'];
            }
        }

        if (!empty($konflikSesi)) {
            return back()->withInput()->withErrors([
                'sesi' => 'Bilik telah ditempah untuk sesi: ' . implode(', ', $konflikSesi)
            ]);
        }

        foreach ($validated['sesi'] as $sesi) {
            $masaSesi = Tempahan::MASA_SESI[$sesi];
            Tempahan::create([
                'nama_mesyuarat'   => $validated['nama_mesyuarat'],
                'tarikh'           => $validated['tarikh'],
                'bilik_id'         => $validated['bilik_id'],
                'sesi'             => $sesi,
                'masa_mula'        => $masaSesi['mula'],
                'masa_tamat'       => $masaSesi['tamat'],
                'bilangan_peserta' => $validated['bilangan_peserta'],
                'kategori'         => $validated['kategori'],
                'nama_pengerusi'   => $validated['nama_pengerusi'],
                'tujuan'           => $validated['tujuan'] ?? null,
                'user_id'          => Auth::id(),
                'status'           => Tempahan::STATUS_DILULUSKAN,
            ]);
        }
        $this->bumpKalendarCacheVersion();

        $jumlahSesi = count($validated['sesi']);
        return redirect()->route('tempahan.index')
            ->with('success', $jumlahSesi > 1
                ? "Tempahan ({$jumlahSesi} sesi) berjaya dibuat."
                : 'Tempahan berjaya dibuat.');
    }

    public function show(Tempahan $tempahan)
    {
        $user = Auth::user();
        // Staf hanya boleh lihat tempahan unit sendiri
        if ($user->isStaf() && !$tempahan->bolehDiEditOleh($user)) abort(403);

        $tempahan->load(['bilik', 'pengguna', 'pelulus', 'pengubah']);
        return view('tempahan.show', compact('tempahan'));
    }

    public function edit(Tempahan $tempahan)
    {
        $user = Auth::user();
        // Staf boleh edit tempahan sendiri + rakan seunit
        if ($user->isStaf() && !$tempahan->bolehDiEditOleh($user)) abort(403);

        $bilik    = BilikMesyuarat::where('status', 'aktif')->get();
        $kategori = Tempahan::KATEGORI;
        $sesi     = Tempahan::MASA_SESI;

        return view('tempahan.edit', compact('tempahan', 'bilik', 'kategori', 'sesi'));
    }

    public function update(Request $request, Tempahan $tempahan)
    {
        $user = Auth::user();
        // Staf boleh kemaskini tempahan sendiri + rakan seunit
        if ($user->isStaf() && !$tempahan->bolehDiEditOleh($user)) abort(403);

        $validated = $request->validate([
            'nama_mesyuarat'   => 'required|string|max:255',
            'tarikh'           => 'required|date',
            'bilik_id'         => 'required|exists:bilik_mesyuarat,id',
            'sesi'             => 'required|in:pagi,petang',
            'bilangan_peserta' => 'required|integer|min:1',
            'kategori'         => 'required|string',
            'nama_pengerusi'   => 'required|string|max:255',
            'tujuan'           => 'nullable|string|max:1000',
        ]);

        $bilik = BilikMesyuarat::findOrFail($validated['bilik_id']);
        if ($validated['bilangan_peserta'] > $bilik->kapasiti) {
            return back()->withInput()->withErrors([
                'bilangan_peserta' => "Bilangan peserta melebihi kapasiti bilik ({$bilik->kapasiti} orang)."
            ]);
        }

        $konflik = Tempahan::where('bilik_id', $validated['bilik_id'])
            ->where('tarikh', $validated['tarikh'])
            ->where('sesi', $validated['sesi'])
            ->where('status', '!=', Tempahan::STATUS_DITOLAK)
            ->where('id', '!=', $tempahan->id)
            ->exists();

        if ($konflik) {
            return back()->withInput()->withErrors([
                'sesi' => 'Bilik telah ditempah untuk sesi ini pada tarikh tersebut.'
            ]);
        }

        // Rekod sama ada ini adalah pindaan oleh orang lain (audit)
        $adalahPindaanUnit = $tempahan->user_id !== $user->id;

        $masaSesi = Tempahan::MASA_SESI[$validated['sesi']];
        $tempahan->update([
            'nama_mesyuarat'   => $validated['nama_mesyuarat'],
            'tarikh'           => $validated['tarikh'],
            'bilik_id'         => $validated['bilik_id'],
            'sesi'             => $validated['sesi'],
            'masa_mula'        => $masaSesi['mula'],
            'masa_tamat'       => $masaSesi['tamat'],
            'bilangan_peserta' => $validated['bilangan_peserta'],
            'kategori'         => $validated['kategori'],
            'nama_pengerusi'   => $validated['nama_pengerusi'],
            'tujuan'           => $validated['tujuan'] ?? null,
            'dikemaskini_oleh' => $user->id,
            'dikemaskini_pada' => now(),
        ]);
        $this->bumpKalendarCacheVersion();

        $mesej = $adalahPindaanUnit
            ? "Tempahan '{$tempahan->nama_mesyuarat}' berjaya dikemaskini. (Pindaan atas nama {$tempahan->pengguna->name})"
            : 'Tempahan berjaya dikemaskini.';

        return redirect()->route('tempahan.index')->with('success', $mesej);
    }

    public function cekKonflik(Request $request)
    {
        $bilikId = $request->bilik_id;
        $tarikh  = $request->tarikh;

        if (!$bilikId || !$tarikh) {
            return response()->json(['pagi' => false, 'petang' => false]);
        }

        $hasil = [];
        foreach (['pagi', 'petang'] as $sesi) {
            $hasil[$sesi] = Tempahan::where('bilik_id', $bilikId)
                ->where('tarikh', $tarikh)
                ->where('sesi', $sesi)
                ->where('status', '!=', Tempahan::STATUS_DITOLAK)
                ->exists();
        }

        $bilik = BilikMesyuarat::find($bilikId);
        $hasil['kapasiti'] = $bilik?->kapasiti ?? 0;

        return response()->json($hasil);
    }

    public function exportPdf(Request $request)
    {
        $query    = $this->unitQuery()->with(['bilik', 'pengguna']);
        $tempahan = $query->orderByDesc('tarikh')->get();
        $pdf      = Pdf::loadView('tempahan.pdf', compact('tempahan'));
        return $pdf->download('senarai-tempahan.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new TempahanExport($request->all()), 'senarai-tempahan.xlsx');
    }

    private function bumpKalendarCacheVersion(): void
    {
        Cache::add('kalendar:events:version', 1, now()->addDays(30));
        Cache::increment('kalendar:events:version');
        Cache::add('kalendar:public-events:version', 1, now()->addDays(30));
        Cache::increment('kalendar:public-events:version');
    }
}
