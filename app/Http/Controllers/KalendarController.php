<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Pembangun : Mohd Hafez bin Husin (Unit Aplikasi Gunasama)
 *
 * Unauthorized copying, modification, distribution, or use of this software,
 * via any medium, is strictly prohibited. Proprietary and confidential.
 */

namespace App\Http\Controllers;

use App\Models\Bahagian;
use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class KalendarController extends Controller
{
    public function index()
    {
        $bilik = BilikMesyuarat::where('status', 'aktif')
            ->untukPengguna(auth()->user())
            ->with('bahagian:id,kod,nama')
            ->orderBy('nama')->get();

        // Senarai bahagian yang ada bilik aktif boleh dilihat oleh pengguna ini
        // Diperolehi terus dari $bilik supaya konsisten — tanpa query tambahan
        $bahagian = $bilik
            ->pluck('bahagian')
            ->filter()
            ->unique('id')
            ->sortBy('kod')
            ->values();

        return response()
            ->view('kalendar.index', compact('bilik', 'bahagian'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('X-LiteSpeed-Cache-Control', 'no-cache');
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
        $bahagianId = $request->filled('bahagian_id')
            ? (int) $request->bahagian_id
            : 0;
        $cacheKey = sprintf(
            'kalendar:events:v%s:u%s:s%s:e%s:b%s:bhg%s',
            $cacheVersion,
            $user->id,
            $start ?? '-',
            $end ?? '-',
            $bilikId,
            $bahagianId
        );

        $events = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($start, $end, $bilikId, $bahagianId, $user) {
            // Semua pengguna (termasuk staf) boleh nampak semua tempahan dalam kalendar
            // supaya mereka tahu slot yang dah ditempah sebelum buat permohonan baru
            $query = Tempahan::query()
                ->select([
                    'id',
                    'ulid',
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
                    'tujuan',
                ])
                ->with([
                    'bilik:id,nama,lokasi',
                    'pengguna:id,name',
                ])
                ->whereNotIn('status', [Tempahan::STATUS_DITOLAK, Tempahan::STATUS_DIBATALKAN]);

            if ($start) {
                $query->whereDate('tarikh', '>=', $start);
            }
            if ($end) {
                $query->whereDate('tarikh', '<=', $end);
            }
            if ($bilikId > 0) {
                $query->where('bilik_id', $bilikId);
            } elseif ($bahagianId > 0) {
                // Tapis event mengikut bahagian — tunjuk semua bilik bahagian tersebut
                $query->whereHas('bilik', fn ($q) => $q->where('bahagian_id', $bahagianId));
            }

            return $this->formatEvents($query->get(), false, $user->id);
        });

        return response()->json($events)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('X-LiteSpeed-Cache-Control', 'no-cache');
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
                    'nama_mesyuarat',
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

        return response()->json($events)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('X-LiteSpeed-Cache-Control', 'no-cache');
    }

    private function formatEvents($tempahan, bool $awam = false, int $currentUserId = 0): array
    {
        return $tempahan->map(function ($t) use ($awam, $currentUserId) {
            $isOwn = ($t->user_id === $currentUserId);

            if ($awam) {
                // ── Kalendar AWAM (halaman login — tanpa log masuk) ──────────────
                // IDOR mitigation: jangan dedah tempahan_id (DB integer PK).
                // Guna ID bukan-sequential yang hanya bermakna untuk FullCalendar.
                // Format: b{bilik_id}_{sesi}_{tarikh} — tidak boleh digunakan untuk enumeration.
                $idAwam = 'b'.$t->bilik_id.'_'.$t->sesi.'_'.$t->tarikh->format('Ymd');

                return [
                    'id' => $idAwam,
                    'title' => $t->nama_mesyuarat,
                    'start' => $t->tarikh->format('Y-m-d').'T'.$t->masa_mula,
                    'end' => $t->tarikh->format('Y-m-d').'T'.$t->masa_tamat,
                    'color' => '#dc2626',
                    // extendedProps awam: hanya maklumat minimum untuk paparan
                    // TIADA tempahan_id, TIADA nama pemohon, TIADA kategori
                    'extendedProps' => [
                        'nama' => $t->nama_mesyuarat,
                        'bilik' => $t->bilik->nama ?? '-',
                        'bilik_id' => $t->bilik_id,
                        'sesi_key' => $t->sesi,
                        'tarikh' => $t->tarikh->format('Y-m-d'),
                    ],
                ];
            }

            // ── Kalendar LOG MASUK — pengguna disahkan ──────────────────────
            $warna = $isOwn ? '#16a34a' : '#2563eb'; // hijau=sendiri, biru=orang lain

            return [
                'id' => $t->id,
                'title' => $t->nama_mesyuarat,
                'start' => $t->tarikh->format('Y-m-d').'T'.$t->masa_mula,
                'end' => $t->tarikh->format('Y-m-d').'T'.$t->masa_tamat,
                'color' => $warna,
                'extendedProps' => [
                    'tempahan_id' => $t->id,
                    'tempahan_ulid' => $t->ulid,
                    'bilik' => $t->bilik->nama ?? '-',
                    'lokasi' => $t->bilik->lokasi ?? '',
                    'bilik_id' => $t->bilik_id,
                    'status' => $t->status,
                    'sesi' => $t->sesi === 'pagi' ? 'Sesi Pagi (9:00 - 13:00)' : 'Sesi Petang (14:00 - 18:00)',
                    'sesi_key' => $t->sesi,
                    'tarikh' => $t->tarikh->format('Y-m-d'),
                    'peserta' => $t->bilangan_peserta,
                    'kategori' => $t->kategori,
                    'nama_pengerusi' => $t->nama_pengerusi,
                    'pemohon' => $t->pengguna->name ?? '-',
                    'tujuan' => $t->tujuan ?? '',
                    'is_own' => $isOwn,
                ],
            ];
        })->toArray();
    }
}
