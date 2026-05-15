<?php

namespace App\Http\Controllers;

use App\Models\Tempahan;
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

        $events = $query->get()->map(function ($t) {
            $warna = match ($t->status) {
                'diluluskan' => '#16a34a',
                'menunggu' => '#d97706',
                default => '#dc2626',
            };

            return [
                'id' => $t->id,
                'title' => $t->nama_mesyuarat,
                'start' => $t->tarikh->format('Y-m-d') . 'T' . $t->masa_mula,
                'end' => $t->tarikh->format('Y-m-d') . 'T' . $t->masa_tamat,
                'color' => $warna,
                'extendedProps' => [
                    'bilik' => $t->bilik->nama ?? '-',
                    'status' => $t->status,
                    'peserta' => $t->bilangan_peserta,
                ],
            ];
        });

        return response()->json($events);
    }
}
