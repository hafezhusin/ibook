<?php

namespace App\Http\Controllers;

use App\Models\Tempahan;
use App\Models\BilikMesyuarat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KalendarController extends Controller
{
    public function index()
    {
        $bilik = BilikMesyuarat::where('status', 'aktif')->orderBy('nama')->get();
        return view('kalendar.index', compact('bilik'));
    }

    public function events(Request $request)
    {
        $user = Auth::user();

        // Semua pengguna (termasuk staf) boleh nampak semua tempahan dalam kalendar
        // supaya mereka tahu slot yang dah ditempah sebelum buat permohonan baru
        $query = Tempahan::with(['bilik', 'pengguna'])
            ->where('status', '!=', Tempahan::STATUS_DITOLAK);

        if ($request->filled('start')) {
            $query->where('tarikh', '>=', $request->start);
        }
        if ($request->filled('end')) {
            $query->where('tarikh', '<=', $request->end);
        }
        if ($request->filled('bilik_id')) {
            $query->where('bilik_id', $request->bilik_id);
        }

        return response()->json($this->formatEvents($query->get(), false, $user->id));
    }

    // Route awam - tanpa log masuk
    public function publicEvents(Request $request)
    {
        $query = Tempahan::with('bilik')
            ->where('status', Tempahan::STATUS_DILULUSKAN);

        if ($request->filled('start')) {
            $query->where('tarikh', '>=', $request->start);
        }
        if ($request->filled('end')) {
            $query->where('tarikh', '<=', $request->end);
        }

        if ($request->filled('bilik_id')) {
            $query->where('bilik_id', $request->bilik_id);
        }

        return response()->json($this->formatEvents($query->get(), true));
    }

    private function formatEvents($tempahan, bool $awam = false, int $currentUserId = 0): array
    {
        return $tempahan->map(function ($t) use ($awam, $currentUserId) {
            $isOwn = ($t->user_id === $currentUserId);

            if ($awam) {
                // Kalendar awam (halaman login)
                $warna = '#dc2626';
                $title = '🔴 ' . ($t->bilik->nama ?? 'Ditempah');
            } elseif ($t->status === 'diluluskan') {
                $warna = $isOwn ? '#16a34a' : '#2563eb'; // hijau=sendiri, biru=orang lain
                $title = $t->nama_mesyuarat;
            } else {
                $warna = '#d97706'; // oren = menunggu
                $title = $t->nama_mesyuarat;
            }

            return [
                'id'    => $t->id,
                'title' => $title,
                'start' => $t->tarikh->format('Y-m-d') . 'T' . $t->masa_mula,
                'end'   => $t->tarikh->format('Y-m-d') . 'T' . $t->masa_tamat,
                'color' => $warna,
                'extendedProps' => [
                    'bilik'          => $t->bilik->nama ?? '-',
                    'lokasi'         => $t->bilik->lokasi ?? '',
                    'status'         => $t->status,
                    'sesi'           => $t->sesi === 'pagi' ? 'Sesi Pagi (9:00 - 13:00)' : 'Sesi Petang (14:00 - 18:00)',
                    'peserta'        => $t->bilangan_peserta,
                    'kategori'       => $t->kategori,
                    'nama_pengerusi' => $t->nama_pengerusi,
                    'tujuan'         => $t->tujuan,
                    'pemohon'        => $t->pengguna->name ?? '-',
                    'is_own'         => $isOwn,
                ],
            ];
        })->toArray();
    }
}
