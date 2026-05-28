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

use App\Http\Requests\UpdateTetapanRequest;
use App\Models\ActivityLog;
use App\Models\Tetapan;
use App\Services\AuditLogger;

class TetapanController extends Controller
{
    /**
     * Kunci tetapan yang diuruskan melalui UI ini.
     * Mana-mana kunci lain dalam DB tidak akan disentuh.
     */
    private const KUNCI_DIURUS = [
        'nama_sistem',
        'nama_jabatan',
        'logo_jabatan',
        'emel_pentadbir',
        'emel_notifikasi',
        'notif_tempahan_baru',
        'notif_kelulusan',
        'peringatan_mesyuarat',
    ];

    public function index()
    {
        // Pertahanan berlapis — walaupun route dilindungi middleware
        abort_unless(auth()->user()->isPentadbir(), 403, 'Akses terhad kepada Pentadbir Sistem sahaja.');

        $tetapan = Tetapan::getAll();

        // Rekod kemaskini terakhir dari activity_log
        $logTerakhir = ActivityLog::where('tindakan', 'kemaskini_tetapan')
            ->latest('dicipta_pada')
            ->with('pengguna:id,name')
            ->first();

        return view('tetapan.index', compact('tetapan', 'logTerakhir'));
    }

    public function update(UpdateTetapanRequest $request)
    {
        // Pertahanan berlapis
        abort_unless(auth()->user()->isPentadbir(), 403);

        // ── 1. Rakam nilai LAMA sebelum kemaskini (untuk audit trail) ──
        $nilaiLama = [];
        foreach (self::KUNCI_DIURUS as $kunci) {
            $nilaiLama[$kunci] = Tetapan::get($kunci);
        }

        // ── 2. Simpan nilai BARU ──
        Tetapan::set('nama_sistem', $request->validated()['nama_sistem'] ?? '');
        Tetapan::set('nama_jabatan', $request->validated()['nama_jabatan']);
        Tetapan::set('logo_jabatan', $request->validated()['logo_jabatan'] ?? '');
        Tetapan::set('emel_pentadbir', $request->validated()['emel_pentadbir'] ?? '');
        Tetapan::set('emel_notifikasi', $request->validated()['emel_notifikasi'] ?? '');
        Tetapan::set('notif_tempahan_baru', $request->boolean('notif_tempahan_baru') ? '1' : '0');
        Tetapan::set('notif_kelulusan', $request->boolean('notif_kelulusan') ? '1' : '0');
        Tetapan::set('peringatan_mesyuarat', $request->boolean('peringatan_mesyuarat') ? '1' : '0');

        // ── 3. Kosongkan cache supaya nilai terbaru dipaparkan serta-merta ──
        Tetapan::clearCache();

        // ── 4. Bina senarai perubahan (before vs after) untuk audit ──
        $nilaiBaru = [];
        foreach (self::KUNCI_DIURUS as $kunci) {
            $nilaiBaru[$kunci] = Tetapan::get($kunci);
        }

        $perubahan = [];
        foreach (self::KUNCI_DIURUS as $kunci) {
            $lama = $nilaiLama[$kunci] ?? '';
            $baru = $nilaiBaru[$kunci] ?? '';
            if ((string) $lama !== (string) $baru) {
                $perubahan[$kunci] = ['lama' => $lama, 'baru' => $baru];
            }
        }

        // ── 5. Log audit dengan butiran before/after ──
        AuditLogger::catat(
            'kemaskini_tetapan',
            null,
            [
                'perubahan' => $perubahan,
                'jumlah_berubah' => count($perubahan),
            ]
        );

        return redirect()->route('tetapan.index')
            ->with('success', 'Tetapan sistem berjaya disimpan.'.
                (count($perubahan) > 0
                    ? ' '.count($perubahan).' nilai telah dikemaskini.'
                    : ' Tiada perubahan dikesan.'));
    }
}
