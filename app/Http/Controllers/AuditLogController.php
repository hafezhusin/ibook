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

use App\Exports\AuditLogExport;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
                $q->where('penerangan', 'like', '%'.$request->carian.'%')
                    ->orWhere('tindakan', 'like', '%'.$request->carian.'%')
                    ->orWhere('ip_address', 'like', '%'.$request->carian.'%');
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

    /**
     * Timeline aktiviti seorang pengguna — semua tindakan secara kronologi.
     * Boleh ditapis mengikut julat tarikh.
     */
    public function timeline(User $pengguna, Request $request)
    {
        abort_unless(auth()->user()->isPentadbir(), 403, 'Akses terhad kepada Pentadbir Sistem sahaja.');

        $query = ActivityLog::where('pengguna_id', $pengguna->id)
            ->orderByDesc('dicipta_pada')
            ->orderByDesc('id');

        if ($request->filled('tarikh_dari')) {
            $query->whereDate('dicipta_pada', '>=', $request->tarikh_dari);
        }
        if ($request->filled('tarikh_hingga')) {
            $query->whereDate('dicipta_pada', '<=', $request->tarikh_hingga);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Kumpulkan mengikut tarikh untuk paparan timeline berkumpul
        $logsByTarikh = $logs->getCollection()
            ->groupBy(fn ($l) => $l->dicipta_pada->format('Y-m-d'));

        // Statistik ringkas pengguna ini
        $jumlahKeseluruhan = ActivityLog::where('pengguna_id', $pengguna->id)->count();

        $tindakanPopular = ActivityLog::where('pengguna_id', $pengguna->id)
            ->selectRaw('tindakan, COUNT(*) as kiraan')
            ->groupBy('tindakan')
            ->orderByDesc('kiraan')
            ->first();

        $aktivitiTerkini = ActivityLog::where('pengguna_id', $pengguna->id)
            ->latest('dicipta_pada')
            ->value('dicipta_pada');

        $jumlahKeselamatanGagal = ActivityLog::where('pengguna_id', $pengguna->id)
            ->where('tindakan', 'log_masuk_gagal')
            ->count();

        return view('audit.timeline', compact(
            'pengguna',
            'logs',
            'logsByTarikh',
            'jumlahKeseluruhan',
            'tindakanPopular',
            'aktivitiTerkini',
            'jumlahKeselamatanGagal',
        ));
    }

    /**
     * Eksport timeline pengguna ke PDF untuk tujuan audit formal.
     */
    public function timelinePdf(User $pengguna, Request $request)
    {
        abort_unless(auth()->user()->isPentadbir(), 403, 'Akses terhad kepada Pentadbir Sistem sahaja.');

        $query = ActivityLog::where('pengguna_id', $pengguna->id)
            ->orderBy('dicipta_pada')
            ->orderBy('id');

        if ($request->filled('tarikh_dari')) {
            $query->whereDate('dicipta_pada', '>=', $request->tarikh_dari);
        }
        if ($request->filled('tarikh_hingga')) {
            $query->whereDate('dicipta_pada', '<=', $request->tarikh_hingga);
        }

        // Ambil semua rekod (tanpa pagination) untuk PDF
        $logs = $query->get();

        // Kumpulkan mengikut tarikh
        $logsByTarikh = $logs->groupBy(fn ($l) => $l->dicipta_pada->format('Y-m-d'));

        // Statistik
        $jumlahKeseluruhan = ActivityLog::where('pengguna_id', $pengguna->id)->count();

        $tindakanPopular = ActivityLog::where('pengguna_id', $pengguna->id)
            ->selectRaw('tindakan, COUNT(*) as kiraan')
            ->groupBy('tindakan')
            ->orderByDesc('kiraan')
            ->first();

        $aktivitiTerkini = ActivityLog::where('pengguna_id', $pengguna->id)
            ->latest('dicipta_pada')
            ->value('dicipta_pada');

        $jumlahKeselamatanGagal = ActivityLog::where('pengguna_id', $pengguna->id)
            ->where('tindakan', 'log_masuk_gagal')
            ->count();

        AuditLogger::catat('eksport_timeline_pdf', $pengguna, [
            'pengguna_id'   => $pengguna->id,
            'pengguna_nama' => $pengguna->name,
            'jumlah_rekod'  => $logs->count(),
            'tarikh_dari'   => $request->tarikh_dari,
            'tarikh_hingga' => $request->tarikh_hingga,
        ]);

        $pdf = Pdf::loadView('audit.timeline-pdf', compact(
            'pengguna',
            'logs',
            'logsByTarikh',
            'jumlahKeseluruhan',
            'tindakanPopular',
            'aktivitiTerkini',
            'jumlahKeselamatanGagal',
        ))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'defaultFont'         => 'DejaVu Sans',
                'isRemoteEnabled'     => false,
                'isHtml5ParserEnabled'=> true,
                'chroot'              => public_path(),
                'dpi'                 => 96,
            ]);

        $namafail = 'Timeline_'.str_replace(' ', '_', $pengguna->name).'_'.now()->format('Ymd').'.pdf';

        return $pdf->download($namafail);
    }

    /**
     * Eksport log audit ke Excel (.xlsx), menghormati penapis semasa.
     */
    public function exportExcel(Request $request)
    {
        abort_unless(auth()->user()->isPentadbir(), 403);

        $filters = $request->only(['tindakan', 'pengguna_id', 'tarikh_dari', 'tarikh_hingga', 'carian']);

        AuditLogger::catat('eksport_audit_excel', null, array_filter($filters));

        $namafail = 'log-audit-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new AuditLogExport($filters), $namafail);
    }
}
