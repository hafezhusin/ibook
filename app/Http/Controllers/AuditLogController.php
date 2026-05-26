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

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->isPentadbir(), 403, 'Akses terhad kepada Pentadbir Sistem sahaja.');

        $query = ActivityLog::with('pengguna:id,name')
            ->orderByDesc('dicipta_pada')
            ->orderByDesc('id');

        if ($request->filled('tindakan')) {
            $query->where('tindakan', $request->tindakan);
        }
        if ($request->filled('pengguna_id')) {
            $query->where('pengguna_id', $request->pengguna_id);
        }
        if ($request->filled('tarikh_dari')) {
            $query->whereDate('dicipta_pada', '>=', $request->tarikh_dari);
        }
        if ($request->filled('tarikh_hingga')) {
            $query->whereDate('dicipta_pada', '<=', $request->tarikh_hingga);
        }
        if ($request->filled('carian')) {
            $query->where(function ($q) use ($request) {
                $q->where('penerangan', 'like', '%' . $request->carian . '%')
                  ->orWhere('tindakan', 'like', '%' . $request->carian . '%')
                  ->orWhere('ip_address', 'like', '%' . $request->carian . '%');
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Senarai tindakan unik untuk dropdown filter
        $senaraiTindakan = ActivityLog::select('tindakan')
            ->distinct()
            ->orderBy('tindakan')
            ->pluck('tindakan');

        // Senarai pengguna yang ada rekod log
        $senaraiPengguna = User::whereIn('id',
            ActivityLog::select('pengguna_id')->distinct()->whereNotNull('pengguna_id')
        )->orderBy('name')->get(['id', 'name']);

        $jumlahKeseluruhan = ActivityLog::count();

        // Kiraan peristiwa keselamatan dalam 24 jam — untuk banner amaran
        $amalanBahaya = ActivityLog::whereIn('tindakan', [
                'log_masuk_gagal',
                'percubaan_akaun_nyahaktif',
            ])
            ->where('dicipta_pada', '>=', now()->subHours(24))
            ->count();

        // Top 5 IP dengan paling banyak log masuk gagal dalam 24 jam (untuk paparan)
        $ipMencurigai = ActivityLog::select('ip_address')
            ->selectRaw('COUNT(*) as kiraan')
            ->where('tindakan', 'log_masuk_gagal')
            ->where('dicipta_pada', '>=', now()->subHours(24))
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->orderByDesc('kiraan')
            ->having('kiraan', '>=', 3)
            ->limit(5)
            ->get();

        return view('audit.index', compact(
            'logs',
            'senaraiTindakan',
            'senaraiPengguna',
            'jumlahKeseluruhan',
            'amalanBahaya',
            'ipMencurigai',
        ));
    }
}
