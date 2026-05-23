<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTempahanRequest;
use App\Http\Requests\UpdateTempahanRequest;
use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TempahanExport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
                'ulid',            // diperlukan oleh getRouteKeyName() untuk jana URL
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

        $this->terapiFilters($query, $request);

        // Isih: mengikut tarikh mesyuarat apabila ada tarikh_filter aktif,
        // selain itu isih mengikut tarikh dibuat (terbaru dahulu) supaya
        // tempahan baharu mudah dijumpai tanpa perlu tapis.
        $tarikhFilterAktif = in_array($request->get('tarikh_filter'), ['hari_ini','esok','7_hari','bulan_ini','akan_datang']);
        if ($tarikhFilterAktif) {
            $query->orderBy('tarikh')->orderBy('masa_mula');
        } else {
            $query->orderByDesc('created_at');
        }
        $tempahan  = $query->paginate(20)->withQueryString();
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
            // Semak pengguna berhak akses tempahan asal sebelum duplikat
            if ($asal && $asal->bolehDiEditOleh(Auth::user())) {
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

    public function store(StoreTempahanRequest $request)
    {
        $validated = $request->validated();

        // Gantikan 'lain' dengan teks kategori yang dimasukkan pengguna
        if ($validated['kategori'] === 'lain' && !empty($validated['kategori_lain'])) {
            $validated['kategori'] = trim($validated['kategori_lain']);
        }
        unset($validated['kategori_lain']);

        $bilik = BilikMesyuarat::findOrFail($validated['bilik_id']);
        if ($validated['bilangan_peserta'] > $bilik->kapasiti) {
            return back()->withInput()->withErrors([
                'bilangan_peserta' => "Bilangan peserta melebihi kapasiti bilik ({$bilik->kapasiti} orang)."
            ]);
        }

        // Semak konflik & cipta tempahan dalam satu transaksi atomik
        // lockForUpdate() mengunci baris yang berkaitan supaya dua permintaan
        // serentak tidak boleh melalui semakan konflik secara bersamaan.
        $konflikSesi = [];

        try {
            DB::transaction(function () use ($validated, &$konflikSesi) {
                foreach ($validated['sesi'] as $sesi) {
                    $konflik = Tempahan::where('bilik_id', $validated['bilik_id'])
                        ->whereDate('tarikh', $validated['tarikh'])
                        ->where('sesi', $sesi)
                        ->where('status', '!=', Tempahan::STATUS_DITOLAK)
                        ->lockForUpdate()
                        ->exists();
                    if ($konflik) {
                        $konflikSesi[] = Tempahan::MASA_SESI[$sesi]['label'];
                    }
                }

                if (!empty($konflikSesi)) {
                    // Lempar exception untuk rollback transaksi & keluar
                    throw new \RuntimeException('konflik_sesi');
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
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'konflik_sesi') {
                return back()->withInput()->withErrors([
                    'sesi' => 'Bilik telah ditempah untuk sesi: ' . implode(', ', $konflikSesi)
                ]);
            }
            throw $e;
        }

        $this->bumpKalendarCacheVersion();

        AuditLogger::catat('buat_tempahan', null, [
            'nama_mesyuarat' => $validated['nama_mesyuarat'],
            'tarikh'         => $validated['tarikh'],
            'bilik_id'       => $validated['bilik_id'],
            'sesi'           => $validated['sesi'],
        ]);

        $jumlahSesi = count($validated['sesi']);
        return redirect()->route('tempahan.index', ['tarikh_filter' => 'baharu'])
            ->with('success', $jumlahSesi > 1
                ? "Tempahan ({$jumlahSesi} sesi) berjaya dibuat."
                : 'Tempahan berjaya dibuat.');
    }

    public function show(Tempahan $tempahan)
    {
        $this->authorize('view', $tempahan);

        $tempahan->load(['bilik', 'pengguna', 'pelulus', 'pengubah']);
        return view('tempahan.show', compact('tempahan'));
    }

    public function edit(Tempahan $tempahan)
    {
        $this->authorize('update', $tempahan);

        $bilik    = BilikMesyuarat::where('status', 'aktif')->get();
        $kategori = Tempahan::KATEGORI;
        $sesi     = Tempahan::MASA_SESI;

        return view('tempahan.edit', compact('tempahan', 'bilik', 'kategori', 'sesi'));
    }

    public function update(UpdateTempahanRequest $request, Tempahan $tempahan)
    {
        // Autoriti dikendalikan oleh UpdateTempahanRequest::authorize()
        $user      = Auth::user();
        $validated = $request->validated();

        // Gantikan 'lain' dengan teks kategori yang dimasukkan pengguna
        if ($validated['kategori'] === 'lain' && !empty($validated['kategori_lain'])) {
            $validated['kategori'] = trim($validated['kategori_lain']);
        }
        unset($validated['kategori_lain']);

        $bilik = BilikMesyuarat::findOrFail($validated['bilik_id']);
        if ($validated['bilangan_peserta'] > $bilik->kapasiti) {
            return back()->withInput()->withErrors([
                'bilangan_peserta' => "Bilangan peserta melebihi kapasiti bilik ({$bilik->kapasiti} orang)."
            ]);
        }

        $konflik = Tempahan::where('bilik_id', $validated['bilik_id'])
            ->whereDate('tarikh', $validated['tarikh'])
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

        AuditLogger::catat('kemaskini_tempahan', $tempahan, [
            'pindaan_unit' => $adalahPindaanUnit,
            'user_id_asal' => $tempahan->user_id,
        ]);

        $mesej = $adalahPindaanUnit
            ? "Tempahan '{$tempahan->nama_mesyuarat}' berjaya dikemaskini. (Pindaan atas nama {$tempahan->pengguna->name})"
            : 'Tempahan berjaya dikemaskini.';

        return redirect()->route('tempahan.index')->with('success', $mesej);
    }

    public function cekKonflik(Request $request)
    {
        $request->validate([
            'bilik_id' => ['required', 'integer', 'exists:bilik_mesyuarat,id'],
            'tarikh'   => ['required', 'date'],
        ]);

        $bilikId = (int) $request->bilik_id;
        $tarikh  = $request->tarikh;

        $hasil = [];
        foreach (['pagi', 'petang'] as $sesi) {
            $hasil[$sesi] = Tempahan::where('bilik_id', $bilikId)
                ->whereDate('tarikh', $tarikh)
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
        $query = $this->unitQuery()->with(['bilik', 'pengguna']);
        // Export hormat filter semasa — sama seperti paparan senarai
        $this->terapiFilters($query, $request);
        $tempahan = $query->orderByDesc('tarikh')->get();
        AuditLogger::catat('eksport_pdf', null, [
            'jumlah_rekod' => $tempahan->count(),
            'parameter'    => $request->only(['bilik_id', 'status', 'tarikh_dari', 'tarikh_hingga', 'tarikh_filter', 'kategori', 'carian']),
        ]);
        $pdf = Pdf::loadView('tempahan.pdf', compact('tempahan'));
        return $pdf->download('senarai-tempahan.pdf');
    }

    public function exportExcel(Request $request)
    {
        AuditLogger::catat('eksport_excel', null, ['parameter' => $request->only(['bilik_id', 'status', 'tarikh_dari', 'tarikh_hingga'])]);
        return Excel::download(new TempahanExport($request->all()), 'senarai-tempahan.xlsx');
    }

    /**
     * Terapi semua parameter filter permintaan pada query tempahan.
     * Digunakan bersama oleh index() dan exportPdf() supaya output
     * eksport sentiasa selari dengan paparan senarai semasa.
     */
    private function terapiFilters($query, Request $request): void
    {
        $user = Auth::user();

        if ($request->filled('bilik_id')) {
            $query->where('bilik_id', $request->bilik_id);
        }
        if ($request->filled('carian')) {
            $query->where('nama_mesyuarat', 'like', '%' . $request->carian . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('tarikh_dari')) {
            $query->whereDate('tarikh', '>=', $request->tarikh_dari);
        }
        if ($request->filled('tarikh_hingga')) {
            $query->whereDate('tarikh', '<=', $request->tarikh_hingga);
        }
        if ($request->filled('jabatan') && !$user->isStaf()) {
            $query->whereHas('pengguna', fn ($q) => $q->where('jabatan', 'like', '%' . $request->jabatan . '%'));
        }

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
    }

    private function bumpKalendarCacheVersion(): void
    {
        Cache::add('kalendar:events:version', 1, now()->addDays(30));
        Cache::increment('kalendar:events:version');
        Cache::add('kalendar:public-events:version', 1, now()->addDays(30));
        Cache::increment('kalendar:public-events:version');
    }
}
