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
        return view('kalendar.index');
    }

    public function events(Request $request)
    {
        $user = Auth::user();
        $query = Tempahan::with('bilik')
            ->where('status', '!=', Tempahan::STATUS_DITOLAK);

        if ($user->isStaf()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('start')) {
            $query->where('tarikh', '>=', $request->start);
        }
        if ($request->filled('end')) {
            $query->where('tarikh', '<=', $request->end);
        }

        return response()->json($this->formatEvents($query->get()));
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

    private function formatEvents($tempahan, bool $awam = false): array
    {
        return $tempahan->map(function ($t) use ($awam) {
            $warna = match ($t->status) {
                'diluluskan' => '#dc2626',  // merah = dah ditempah
                'menunggu'   => '#d97706',  // oren = dalam proses
                default      => '#6b7280',
            };

            return [
                'id'    => $t->id,
                'title' => $awam ? '🔴 ' . $t->bilik->nama ?? 'Ditempah' : $t->nama_mesyuarat,
                'start' => $t->tarikh->format('Y-m-d') . 'T' . $t->masa_mula,
                'end'   => $t->tarikh->format('Y-m-d') . 'T' . $t->masa_tamat,
                'color' => $warna,
                'extendedProps' => [
                    'bilik'   => $t->bilik->nama ?? '-',
                    'status'  => $t->status,
                    'sesi'    => $t->sesi === 'pagi' ? 'Sesi Pagi (9:00 - 13:00)' : 'Sesi Petang (14:00 - 18:00)',
                    'peserta' => $awam ? null : $t->bilangan_peserta,
                ],
            ];
        })->toArray();
    }
}
