<?php

/**
 * iBook — Sistem Pengurusan Bilik Mesyuarat
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

use App\Http\Requests\StoreTempahanBerulangRequest;
use App\Mail\NotifikasiTempahanBaharu;
use App\Mail\PengesahanTempahanBerulang;
use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\TempahanBerulang;
use App\Models\Tetapan;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TempahanBerulangController extends Controller
{
    // ── STORE — cipta kumpulan + semua tempahan individu ────────────

    public function store(StoreTempahanBerulangRequest $request)
    {
        $validated = $request->validated();

        // Semak kapasiti bilik
        $bilik = BilikMesyuarat::findOrFail($validated['bilik_id']);
        if ($validated['bilangan_peserta'] > $bilik->kapasiti) {
            return back()->withInput()->withErrors([
                'bilangan_peserta' => "Bilangan peserta melebihi kapasiti bilik ({$bilik->kapasiti} orang).",
            ]);
        }

        // Jana semua tarikh (sebelum transaksi supaya lebih pantas)
        $kumpulanSementara = new TempahanBerulang([
            'jenis' => $validated['jenis'],
            'setiap_n' => $validated['setiap_n'],
            'hari_dalam_minggu' => $validated['hari_dalam_minggu'] ?? null,
            'tarikh_mula' => $validated['tarikh_mula'],
            'tarikh_tamat' => $validated['tarikh_tamat'],
            'sesi' => $validated['sesi'],
        ]);
        $semuaTarikh = $kumpulanSementara->janaTarikh();

        if ($semuaTarikh->isEmpty()) {
            return back()->withInput()->withErrors([
                'tarikh_tamat' => 'Tiada tarikh dijana dengan tetapan ini. Semak hari dipilih dan julat tarikh.',
            ]);
        }

        // Bina senarai semua [tarikh, sesi] yang akan dicipta
        $slotDiperlukan = [];
        foreach ($semuaTarikh as $tarikh) {
            foreach ($validated['sesi'] as $sesi) {
                $slotDiperlukan[] = ['tarikh' => $tarikh->toDateString(), 'sesi' => $sesi];
            }
        }

        $konflikDijumpai = [];

        try {
            DB::transaction(function () use ($validated, $semuaTarikh, $slotDiperlukan, &$konflikDijumpai) {

                // Semak konflik untuk semua slot dalam satu transaksi
                // lockForUpdate() cegah race condition (sama seperti TempahanController::store)
                foreach ($slotDiperlukan as $slot) {
                    $konflik = Tempahan::where('bilik_id', $validated['bilik_id'])
                        ->whereDate('tarikh', $slot['tarikh'])
                        ->where('sesi', $slot['sesi'])
                        ->where('status', '!=', Tempahan::STATUS_DITOLAK)
                        ->lockForUpdate()
                        ->exists();

                    if ($konflik) {
                        $labelSesi = $slot['sesi'] === 'pagi' ? 'Pagi' : 'Petang';
                        $konflikDijumpai[] = $slot['tarikh'].' (Sesi '.$labelSesi.')';
                    }
                }

                if (! empty($konflikDijumpai)) {
                    throw new \RuntimeException('konflik');
                }

                // Cipta rekod kumpulan
                $kumpulan = TempahanBerulang::create([
                    'jenis' => $validated['jenis'],
                    'setiap_n' => $validated['setiap_n'],
                    'hari_dalam_minggu' => $validated['hari_dalam_minggu'] ?? null,
                    'tarikh_mula' => $validated['tarikh_mula'],
                    'tarikh_tamat' => $validated['tarikh_tamat'],
                    'sesi' => $validated['sesi'],
                    'bilik_id' => $validated['bilik_id'],
                    'user_id' => Auth::id(),
                    'nama_mesyuarat' => $validated['nama_mesyuarat'],
                    'bilangan_peserta' => $validated['bilangan_peserta'],
                    'kategori' => $validated['kategori'],
                    'nama_pengerusi' => $validated['nama_pengerusi'],
                    'tujuan' => $validated['tujuan'] ?? null,
                ]);

                // Cipta semua tempahan individu
                foreach ($semuaTarikh as $tarikh) {
                    foreach ($validated['sesi'] as $sesi) {
                        $masaSesi = Tempahan::masaSesi($sesi);
                        Tempahan::create([
                            'tempahan_berulang_id' => $kumpulan->id,
                            'nama_mesyuarat' => $validated['nama_mesyuarat'],
                            'tarikh' => $tarikh->toDateString(),
                            'bilik_id' => $validated['bilik_id'],
                            'sesi' => $sesi,
                            'masa_mula' => $masaSesi['mula'],
                            'masa_tamat' => $masaSesi['tamat'],
                            'bilangan_peserta' => $validated['bilangan_peserta'],
                            'kategori' => $validated['kategori'],
                            'nama_pengerusi' => $validated['nama_pengerusi'],
                            'tujuan' => $validated['tujuan'] ?? null,
                            'user_id' => Auth::id(),
                            'status' => Tempahan::STATUS_DILULUSKAN,
                        ]);
                    }
                }
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'konflik') {
                $papar = array_slice($konflikDijumpai, 0, 5);
                $lebih = count($konflikDijumpai) > 5
                    ? ' ...dan '.(count($konflikDijumpai) - 5).' lagi.'
                    : '.';

                return back()->withInput()->withErrors([
                    'tarikh_mula' => 'Konflik ditemui: '.implode(', ', $papar).$lebih
                        .' Tukar bilik atau laraskan tarikh.',
                ]);
            }
            throw $e;
        }

        $this->bumpKalendarCache();

        $jumlahSesi = $semuaTarikh->count() * count($validated['sesi']);

        AuditLogger::catat('buat_tempahan_berulang', null, [
            'nama_mesyuarat' => $validated['nama_mesyuarat'],
            'jenis' => $validated['jenis'],
            'tarikh_mula' => $validated['tarikh_mula'],
            'tarikh_tamat' => $validated['tarikh_tamat'],
            'bilik_id' => $validated['bilik_id'],
            'jumlah_tarikh' => $semuaTarikh->count(),
            'jumlah_sesi' => count($validated['sesi']),
        ]);

        // Hantar e-mel — kegagalan tidak patut halang aliran tempahan
        $this->hantarEmelBerulang($validated, $bilik, $semuaTarikh->count(), $jumlahSesi);

        return redirect()->route('tempahan.index', ['tarikh_filter' => 'akan_datang'])
            ->with('success', "Tempahan berulang berjaya dibuat — {$jumlahSesi} sesi dijadualkan.");
    }

    // ── UPDATE — kemaskini satu atau semua dalam kumpulan ───────────

    public function update(Request $request, TempahanBerulang $kumpulan)
    {
        // Authorize: semak menggunakan tempahan pertama dalam kumpulan
        $tempahanContoh = $kumpulan->tempahan()->firstOrFail();
        $this->authorize('update', $tempahanContoh);

        $validated = $request->validate([
            'nama_mesyuarat' => ['required', 'string', 'max:255'],
            'bilangan_peserta' => ['required', 'integer', 'min:1'],
            'kategori' => ['required', 'string'],
            'nama_pengerusi' => ['required', 'string', 'max:255'],
            'tujuan' => ['nullable', 'string', 'max:1000'],
        ]);

        // Semak kapasiti bilik — bilangan peserta tidak boleh melebihi kapasiti
        $bilik = $kumpulan->bilik;
        if ($bilik && $validated['bilangan_peserta'] > $bilik->kapasiti) {
            return back()->withErrors([
                'bilangan_peserta' => "Bilangan peserta ({$validated['bilangan_peserta']}) melebihi kapasiti bilik {$bilik->nama} ({$bilik->kapasiti} orang).",
            ])->withInput();
        }

        DB::transaction(function () use ($kumpulan, $validated) {
            $kumpulan->update($validated);
            $kumpulan->tempahan()
                ->where('status', '!=', Tempahan::STATUS_DITOLAK)
                ->update([
                    'nama_mesyuarat' => $validated['nama_mesyuarat'],
                    'bilangan_peserta' => $validated['bilangan_peserta'],
                    'kategori' => $validated['kategori'],
                    'nama_pengerusi' => $validated['nama_pengerusi'],
                    'tujuan' => $validated['tujuan'] ?? null,
                    'dikemaskini_oleh' => Auth::id(),
                    'dikemaskini_pada' => now(),
                    'updated_at' => now(),
                ]);
        });

        $this->bumpKalendarCache();
        AuditLogger::catat('kemaskini_kumpulan_berulang', $kumpulan, ['skop' => 'semua']);

        return redirect()->route('tempahan.index')
            ->with('success', 'Semua tempahan dalam kumpulan berjaya dikemaskini.');
    }

    // ── DESTROY — padam satu atau semua dalam kumpulan ──────────────

    public function destroy(Request $request, Tempahan $tempahan)
    {
        $this->authorize('delete', $tempahan);

        $skop = $request->input('skop', 'ini');
        $kumpulan = $tempahan->kumpulanBerulang;

        DB::transaction(function () use ($tempahan, $kumpulan, $skop) {
            if ($skop === 'semua' && $kumpulan) {
                // Padam semua tempahan dalam kumpulan, kemudian kumpulan itu sendiri
                $kumpulan->tempahan()->delete();
                $kumpulan->delete();
            } else {
                // Padam tempahan ini sahaja
                $tempahan->delete();
                // Jika ini adalah satu-satunya yang tinggal, padam kumpulan juga
                if ($kumpulan && $kumpulan->tempahan()->count() === 0) {
                    $kumpulan->delete();
                }
            }
        });

        $this->bumpKalendarCache();
        AuditLogger::catat('padam_tempahan', $tempahan, ['skop' => $skop]);

        return redirect()->route('tempahan.index')
            ->with('success', $skop === 'semua'
                ? 'Semua tempahan dalam kumpulan berjaya dipadam.'
                : 'Tempahan berjaya dipadam.');
    }

    // ── PRATONTON — AJAX pratonton tarikh yang akan dijana ──────────

    public function pratonton(Request $request)
    {
        $request->validate([
            'jenis' => ['required', 'in:mingguan,bulanan'],
            'setiap_n' => ['required', 'integer', 'min:1', 'max:12'],
            'hari_dalam_minggu' => ['nullable', 'array'],
            'hari_dalam_minggu.*' => ['integer', 'between:0,6'],
            'tarikh_mula' => ['required', 'date'],
            'tarikh_tamat' => ['required', 'date', 'after:tarikh_mula'],
        ]);

        $kumpulan = new TempahanBerulang([
            'jenis' => $request->jenis,
            'setiap_n' => $request->setiap_n,
            'hari_dalam_minggu' => $request->hari_dalam_minggu,
            'tarikh_mula' => $request->tarikh_mula,
            'tarikh_tamat' => $request->tarikh_tamat,
            'sesi' => ['pagi'], // placeholder — tarikh tidak bergantung pada sesi
        ]);

        $senaraiTarikh = $kumpulan->janaTarikh()->map(fn ($t) => [
            'tarikh' => $t->toDateString(),
            'label' => $t->locale('ms')->isoFormat('dddd, D MMMM YYYY'),
        ]);

        return response()->json([
            'tarikh' => $senaraiTarikh,
            'jumlah' => $senaraiTarikh->count(),
            'had' => TempahanBerulang::MAX_KEJADIAN,
            'tercapai_had' => $senaraiTarikh->count() >= TempahanBerulang::MAX_KEJADIAN,
        ]);
    }

    // ── Helper ───────────────────────────────────────────────────────

    private function hantarEmelBerulang(array $validated, BilikMesyuarat $bilik, int $jumlahTarikh, int $jumlahSesi): void
    {
        $user = Auth::user();
        $jenisLabel = $validated['jenis'] === 'mingguan' ? 'Mingguan' : 'Bulanan';
        $mulaLabel = Carbon::parse($validated['tarikh_mula'])->locale('ms')->isoFormat('D MMMM YYYY');
        $tamatLabel = Carbon::parse($validated['tarikh_tamat'])->locale('ms')->isoFormat('D MMMM YYYY');
        $kategoriLabel = Tempahan::KATEGORI[$validated['kategori']] ?? $validated['kategori'];
        $tarikhLabel = $mulaLabel.' — '.$tamatLabel;

        // 1. Pengesahan kepada pemohon
        if (Tetapan::get('notif_kelulusan', '1') === '1' && $user->email) {
            try {
                Mail::to($user->email)->send(new PengesahanTempahanBerulang(
                    namaMesyuarat: $validated['nama_mesyuarat'],
                    jenisLabel: $jenisLabel,
                    tarikhMulaLabel: $mulaLabel,
                    tarikhTamatLabel: $tamatLabel,
                    semuaSesi: $validated['sesi'],
                    bilikNama: $bilik->nama,
                    bilanganPeserta: $validated['bilangan_peserta'],
                    kategoriLabel: $kategoriLabel,
                    namaPengerusi: $validated['nama_pengerusi'],
                    pemohonNama: $user->name,
                    pemohonEmail: $user->email,
                    jumlahTarikh: $jumlahTarikh,
                    jumlahSesi: $jumlahSesi,
                ));
            } catch (\Throwable $e) {
                Log::warning('E-mel pengesahan tempahan berulang gagal: '.$e->getMessage());
            }
        }

        // 2. Notifikasi kepada Urus Setia
        if (Tetapan::get('notif_tempahan_baru', '1') === '1') {
            $emelNotifikasi = Tetapan::get('emel_notifikasi');
            if ($emelNotifikasi) {
                try {
                    Mail::to($emelNotifikasi)->send(new NotifikasiTempahanBaharu(
                        namaMesyuarat: $validated['nama_mesyuarat'],
                        tarikhLabel: $tarikhLabel,
                        semuaSesi: $validated['sesi'],
                        bilikNama: $bilik->nama,
                        pemohonNama: $user->name,
                        pemohonJabatan: $user->jabatan ?? '',
                        noRujukan: strtoupper($validated['jenis']).' / '.$mulaLabel,
                        berulang: true,
                        jumlahSesi: $jumlahSesi,
                    ));
                } catch (\Throwable $e) {
                    Log::warning('E-mel notifikasi berulang Urus Setia gagal: '.$e->getMessage());
                }
            }
        }
    }

    private function bumpKalendarCache(): void
    {
        Cache::add('kalendar:events:version', 1, now()->addDays(30));
        Cache::increment('kalendar:events:version');
        Cache::add('kalendar:public-events:version', 1, now()->addDays(30));
        Cache::increment('kalendar:public-events:version');
    }
}
