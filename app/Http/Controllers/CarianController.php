<?php
/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Unauthorized copying, modification, distribution, or use of this software,
 * via any medium, is strictly prohibited. Proprietary and confidential.
 */


namespace App\Http\Controllers;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarianController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return view('carian.index', [
                'q'        => $q,
                'tempahan' => collect(),
                'bilik'    => collect(),
                'pengguna' => collect(),
            ]);
        }

        $user = Auth::user();

        // Carian tempahan
        $queryTempahan = Tempahan::with(['bilik', 'pengguna'])
            ->where(function ($query) use ($q) {
                $query->where('nama_mesyuarat', 'like', "%{$q}%")
                      ->orWhere('nama_pengerusi', 'like', "%{$q}%")
                      ->orWhere('tujuan', 'like', "%{$q}%")
                      ->orWhereHas('bilik', fn($b) => $b->where('nama', 'like', "%{$q}%"));
            });

        if ($user->isStaf()) {
            $queryTempahan->where('user_id', $user->id);
        }

        $tempahan = $queryTempahan->orderByDesc('tarikh')->limit(10)->get();

        // Carian bilik (pentadbir sahaja)
        $bilik = collect();
        if ($user->isPentadbir()) {
            $bilik = BilikMesyuarat::where('nama', 'like', "%{$q}%")
                ->orWhere('lokasi', 'like', "%{$q}%")
                ->limit(10)->get();
        }

        // Carian pengguna (pentadbir sahaja)
        $pengguna = collect();
        if ($user->isPentadbir()) {
            $pengguna = User::where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('jabatan', 'like', "%{$q}%")
                ->limit(10)->get();
        }

        return view('carian.index', compact('q', 'tempahan', 'bilik', 'pengguna'));
    }
}
