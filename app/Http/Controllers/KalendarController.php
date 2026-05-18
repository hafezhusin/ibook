<?php

namespace App\Http\Controllers;

use App\Models\Tempahan;
use App\Models\BilikMesyuarat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

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
        $cacheVersion = Cache::get('kalendar:events:version', 1);
        $start = $request->filled('start')
            ? Carbon::parse($request->start)->toDateString()
            : null;
        $end = $request->filled('end')
            ? Carbon::parse($request->end)->toDateString()
            : null;
        $bilikId = $request->filled('bilik_id')
            ? (int) $request->bilik_id
            : 0;
        $cacheKey = sprintf(
            'kalendar:events:v%s:u%s:s%s:e%s:b%s',
            $cacheVersion,
            $user->id,
            $start ?? '-',
            $end ?? '-',
            $bilikId
        );

        $events = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($start, $end, $bilikId, $user) {
            // Semua pengguna (termasuk staf) boleh nampak semua tempahan dalam kalendar
            // supaya mereka tahu slot yang dah ditempah sebelum buat permohonan baru
            $query = Tempahan::query()
                ->select([
                    'id',
                    'nama_mesyuarat',
                    'tarikh',
                    'masa_mula',
                    'masa_tamat',
                    'bilik_id',
                    'user_id',
                    'status',
                    'sesi',
                    'bilangan_peserta',
                    'kategori',
                    'nama_pengerusi',
                ])
                ->with([
                    'bilik:id,nama,lokasi',
                    'pengguna:id,name',
                ])
                ->where('status', '!=', Tempahan::STATUS_DITOLAK);

            if ($start) {
                $query->whereDate('tarikh', '>=', $start);
            }
            if ($end) {
                $query->whereDate('tarikh', '<=', $end);
            }
            if ($bilikId > 0) {
                $query->where('bilik_id', $bilikId);
            }

            return $this->formatEvents($query->get(), false, $user->id);
        });

        return response()->json($events);
    }

    // Route awam - tanpa log masuk
    public function publicEvents(Request $request)
    {
        $cacheVersion = Cache::get('kalendar:public-events:version', 1);
        $start = $request->filled('start')
            ? Carbon::parse($request->start)->toDateString()
            : null;
        $end = $request->filled('end')
            ? Carbon::parse($request->end)->toDateString()
            : null;
        $bilikId = $request->filled('bilik_id')
            ? (int) $request->bilik_id
            : 0;
        $cacheKey = sprintf(
            'kalendar:public-events:v%s:s%s:e%s:b%s',
            $cacheVersion,
            $start ?? '-',
            $end ?? '-',
            $bilikId
        );

        $events = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($start, $end, $bilikId) {
            $query = Tempahan::query()
                ->select([
                    'id',
                    'tarikh',
                    'masa_mula',
                    'masa_tamat',
                    'bilik_id',
                    'status',
                    'sesi',
                ])
                ->with('bilik:id,nama,lokasi')
                ->where('status', Tempahan::STATUS_DILULUSKAN);

            if ($start) {
                $query->whereDate('tarikh', '>=', $start);
            }
            if ($end) {
                $query->whereDate('tarikh', '<=', $end);
            }
            if ($bilikId > 0) {
                $query->where('bilik_id', $bilikId);
            }

            return $this->formatEvents($query->get(), true);
        });

        return response()->json($events);
    }

    private function formatEvents($tempahan, bool $awam = false, int $currentUserId = 0): array
    {
        return $tempahan->map(function ($t) use ($awam, $currentUserId) {
            $isOwn = ($t->user_id === $currentUserId);

            if ($awam) {
                // Kalendar awam (halaman login)
                $warna = '#dc2626';
                $title = '🔴 ' . ($t->bilik->nama ?? 'Ditempah');
            } else {
                $warna = $isOwn ? '#16a34a' : '#2563eb'; // hijau=sendiri, biru=orang lain
                $title = $t->nama_mesyuarat;
            }

            return [
                'id'    => $t->id,
                'title' => $title,
                'start' => $t->tarikh->format('Y-m-d') . 'T' . $t->masa_mula,
                'end'   => $t->tarikh->format('Y-m-d') . 'T' . $t->masa_tamat,
                'color' => $warna,
                'extendedProps' => [
                    'tempahan_id'    => $t->id,
                    'bilik'          => $t->bilik->nama ?? '-',
                    'lokasi'         => $t->bilik->lokasi ?? '',
                    'bilik_id'       => $t->bilik_id,
                    'status'         => $t->status,
                    'sesi'           => $t->sesi === 'pagi' ? 'Sesi Pagi (9:00 - 13:00)' : 'Sesi Petang (14:00 - 18:00)',
                    'sesi_key'       => $t->sesi,
                    'tarikh'         => $t->tarikh->format('Y-m-d'),
                    'peserta'        => $t->bilangan_peserta,
                    'kategori'       => $t->kategori,
                    'nama_pengerusi' => $t->nama_pengerusi,
                    'pemohon'        => $t->pengguna->name ?? '-',
                    'is_own'         => $isOwn,
                ],
            ];
        })->toArray();
    }
}
