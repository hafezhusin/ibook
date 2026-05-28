<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Pembangun : Mohd Hafez bin Husin (Unit Aplikasi Gunasama)
 */

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * TempahanFilter — Enkapsulasi semua logik penapisan tempahan.
 *
 * Dikongsi oleh:
 *   - TempahanController::index()
 *   - TempahanController::exportPdf()
 *   - TempahanExport (Excel)
 *
 * Menjamin output eksport sentiasa selari dengan paparan senarai semasa.
 * Guna: TempahanFilter::terapkan($query, $request)
 */
class TempahanFilter
{
    public static function terapkan(Builder $query, Request $request): void
    {
        $user = Auth::user();

        if ($request->filled('bilik_id')) {
            $query->where('bilik_id', $request->bilik_id);
        }

        if ($request->filled('carian')) {
            $query->where('nama_mesyuarat', 'like', '%'.$request->carian.'%');
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

        // Penapis jabatan hanya untuk admin/urus setia — staf tidak boleh tapis unit lain
        if ($request->filled('jabatan') && ! $user->isStaf()) {
            $query->whereHas('pengguna', fn ($q) => $q->where('jabatan', 'like', '%'.$request->jabatan.'%'));
        }

        // Penapis tarikh pintas
        match ($request->get('tarikh_filter')) {
            'hari_ini' => $query->whereDate('tarikh', today()),
            'esok' => $query->whereDate('tarikh', today()->addDay()),
            'baharu' => $query->where('created_at', '>=', now()->subHours(24)),
            '7_hari' => $query->whereBetween('tarikh', [today(), today()->addDays(7)]),
            'bulan_ini' => $query->whereMonth('tarikh', now()->month)->whereYear('tarikh', now()->year),
            'akan_datang' => $query->where('tarikh', '>=', today()),
            default => null,
        };
    }
}
