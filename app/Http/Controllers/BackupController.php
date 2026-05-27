<?php
/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 */

namespace App\Http\Controllers;

use App\Models\BackupLog;
use App\Services\AuditLogger;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function __construct(private BackupService $backup) {}

    // ── Halaman utama backup ──────────────────────────────────────────

    public function index()
    {
        $tetapan   = $this->backup->bacaTetapan();
        $sejarah   = BackupLog::with('dibuatOleh')->latest()->take(20)->get();
        $tertunggak = $this->backup->adaBackupTertunggak();
        $nextBackup = $this->backup->nextBackupCarbon();

        return view('backup.index', compact('tetapan', 'sejarah', 'tertunggak', 'nextBackup'));
    }

    // ── Backup segera (muat turun terus) ─────────────────────────────

    public function instant()
    {
        try {
            $result = $this->backup->simpan('segera');

            BackupLog::create([
                'nama_fail'  => $result['nama'],
                'saiz_bytes' => $result['saiz'],
                'jenis'      => 'segera',
                'dibuat_oleh'=> auth()->id(),
            ]);

            $this->backup->rekodSelesai();

            AuditLogger::catat('backup_database', null, [
                'jenis'     => 'segera',
                'nama_fail' => $result['nama'],
                'saiz'      => $result['saiz'],
            ]);

            return Storage::disk('local')->download($result['path'], $result['nama']);

        } catch (\Throwable $e) {
            return back()->with('error', 'Backup gagal: ' . $e->getMessage());
        }
    }

    // ── Kemaskini jadual backup ───────────────────────────────────────

    public function simpanJadual(Request $request)
    {
        $request->validate([
            'jadual' => 'required|in:tiada,mingguan,bulanan',
        ], [
            'jadual.required' => 'Sila pilih jadual backup.',
            'jadual.in'       => 'Pilihan jadual tidak sah.',
        ]);

        $this->backup->kemaskiniJadual($request->jadual);

        AuditLogger::catat('kemaskini_jadual_backup', null, [
            'jadual' => $request->jadual,
        ]);

        $label = match ($request->jadual) {
            'mingguan' => 'Mingguan',
            'bulanan'  => 'Bulanan',
            default    => 'Tiada',
        };

        return back()->with('success', "Jadual backup berjaya ditetapkan kepada: {$label}.");
    }

    // ── Muat turun backup lama dari sejarah ───────────────────────────

    public function muatTurun(BackupLog $backup)
    {
        if (!$this->backup->failWujud($backup->nama_fail)) {
            return back()->with('error', 'Fail backup tidak dijumpai di pelayan. Mungkin sudah dipadam.');
        }

        AuditLogger::catat('muat_turun_backup', null, [
            'nama_fail' => $backup->nama_fail,
        ]);

        return Storage::disk('local')->download(
            $this->backup->pathFail($backup->nama_fail),
            $backup->nama_fail
        );
    }

    // ── Padam rekod backup ────────────────────────────────────────────

    public function padam(BackupLog $backup)
    {
        $namaFail = $backup->nama_fail;
        $this->backup->padamFail($namaFail);
        $backup->delete();

        AuditLogger::catat('padam_backup', null, [
            'nama_fail' => $namaFail,
        ]);

        return back()->with('success', 'Rekod dan fail backup berjaya dipadam.');
    }
}
