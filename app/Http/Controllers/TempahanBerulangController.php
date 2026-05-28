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
use App\Mail\PengesahanTempahanBerulang;
use App\Models\Tempahan;
use App\Models\TempahanBerulang;
use App\Services\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class TempahanBerulangController extends Controller
{
    /**
     * AJAX pratonton — kembalikan senarai tarikh yang akan dijana.
     * GET /tempahan-berulang/pratonton
     */
    public function pratonton(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jenis' => ['required', 'in:mingguan,bulanan'],
            'setiap_n' => ['required', 'integer', 'min:1', 'max:12'],
            'hari_dalam_minggu' => ['nullable', 'array'],
            'hari_dalam_minggu.*' => ['integer', 'between:0,6'],
            'tarikh_mula' => ['required', 'date'],
            'tarikh_tamat' => ['required', 'date', 'after_or_equal:tarikh_mula'],
        ]);

        // Bina model sementara (tidak disimpan) untuk panggil janaTarikh()
        $kumpulan = new TempahanBerulang($data);
        $tarikh = $kumpulan->janaTarikh();
        $had = (int) config('ibook.berulang.had_kejadian', 12);

        return response()->json([
            'tarikh' => $tarikh->map(fn (CarbonImmutable $t) => $t->format('d/m/Y'))->values(),
            'jumlah' => $tarikh->count(),
            'had' => $had,
            'tercapai_had' => $tarikh->count() >= $had,
        ]);
    }

    /**
     * Buat kumpulan berulang + semua tempahan individu dalam satu transaksi.
     * POST /tempahan-berulang
     */
    public function store(StoreTempahanBerulangRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // 1. Jana semua tarikh menggunakan model sementara
        $kumpulanSementara = new TempahanBerulang($validated);
        $semuaTarikh = $kumpulanSementara->janaTarikh();

        if ($semuaTarikh->isEmpty()) {
            throw ValidationException::withMessages([
                'tarikh_tamat' => 'Tiada tarikh dijana berdasarkan tetapan yang dipilih.',
            ]);
        }

        // 2. Bina semua slot [tarikh × sesi]
        $sesiList = (array) ($validated['sesi'] ?? []);

        // 3. Kumpul konflik dulu (sebelum transaksi) — elak melempar exception dalam closure
        //    kerana SQLite (ujian) dan MySQL berkelakuan berbeza untuk nested transaction + lockForUpdate.
        //    Rujukan: https://www.php.net/manual/en/language.variables.variable.php
        $konflikDitemui = [];

        try {
            $kumpulan = DB::transaction(function () use ($validated, $user, $semuaTarikh, $sesiList, &$konflikDitemui): ?TempahanBerulang {

                // Semak konflik untuk setiap slot (lockForUpdate untuk MySQL production)
                // Guna whereDate() bukan where() — SQLite menyimpan tarikh sebagai datetime penuh
                // ('2026-06-01 00:00:00'), maka perbandingan string biasa gagal.
                foreach ($semuaTarikh as $tarikh) {
                    foreach ($sesiList as $sesi) {
                        if (Tempahan::where('bilik_id', $validated['bilik_id'])
                            ->whereDate('tarikh', $tarikh->toDateString())
                            ->where('sesi', $sesi)
                            ->where('status', '!=', Tempahan::STATUS_DITOLAK)
                            ->lockForUpdate()
                            ->exists()) {
                            $konflikDitemui[] = $tarikh->format('d/m/Y').' ('.$sesi.')';
                        }
                    }
                }

                // Jika ada konflik, keluar tanpa insert — commit kosong (idempoten)
                if (! empty($konflikDitemui)) {
                    return null;
                }

                // Cipta rekod kumpulan induk
                $kumpulan = TempahanBerulang::create([
                    'jenis' => $validated['jenis'],
                    'setiap_n' => $validated['setiap_n'],
                    'hari_dalam_minggu' => $validated['hari_dalam_minggu'] ?? null,
                    'tarikh_mula' => $validated['tarikh_mula'],
                    'tarikh_tamat' => $validated['tarikh_tamat'],
                    'sesi' => $sesiList,
                    'bilik_id' => $validated['bilik_id'],
                    'user_id' => $user->id,
                    'nama_mesyuarat' => $validated['nama_mesyuarat'],
                    'bilangan_peserta' => $validated['bilangan_peserta'],
                    'kategori' => $validated['kategori'],
                    'nama_pengerusi' => $validated['nama_pengerusi'],
                    'tujuan' => $validated['tujuan'] ?? null,
                ]);

                // Cipta setiap tempahan individu
                foreach ($semuaTarikh as $tarikh) {
                    foreach ($sesiList as $sesi) {
                        $masaSesi = Tempahan::masaSesi($sesi);

                        Tempahan::create([
                            'tempahan_berulang_id' => $kumpulan->id,
                            'bilik_id' => $validated['bilik_id'],
                            'user_id' => $user->id,
                            'tarikh' => $tarikh->toDateString(),
                            'sesi' => $sesi,
                            'masa_mula' => $masaSesi['mula'] ?? '09:00',
                            'masa_tamat' => $masaSesi['tamat'] ?? '13:00',
                            'nama_mesyuarat' => $validated['nama_mesyuarat'],
                            'bilangan_peserta' => $validated['bilangan_peserta'],
                            'kategori' => $validated['kategori'],
                            'nama_pengerusi' => $validated['nama_pengerusi'],
                            'tujuan' => $validated['tujuan'] ?? null,
                            'status' => Tempahan::STATUS_DILULUSKAN,
                        ]);
                    }
                }

                return $kumpulan;
            });
        } catch (QueryException $e) {
            report($e);

            return back()->withInput()->with('error', 'Ralat pangkalan data. Sila cuba semula.');
        }

        // Periksa konflik selepas transaksi — lempar ValidationException di luar closure
        if (! empty($konflikDitemui)) {
            throw ValidationException::withMessages([
                'tarikh_mula' => 'Konflik tempahan pada: '.implode(', ', $konflikDitemui).'. Sila pilih tarikh atau bilik yang lain.',
            ]);
        }

        // Selepas konflik ditapis, $kumpulan pasti bukan null
        /** @var TempahanBerulang $kumpulan */

        // 4. Hantar emel pengesahan (gagal emel tidak batalkan tempahan)
        try {
            $bilik = $kumpulan->bilik;

            Mail::to($user->email)->send(new PengesahanTempahanBerulang(
                namaMesyuarat: $kumpulan->nama_mesyuarat,
                jenisLabel: $kumpulan->jenis === 'mingguan' ? 'Mingguan' : 'Bulanan',
                tarikhMulaLabel: CarbonImmutable::parse($kumpulan->tarikh_mula)->translatedFormat('d F Y'),
                tarikhTamatLabel: CarbonImmutable::parse($kumpulan->tarikh_tamat)->translatedFormat('d F Y'),
                semuaSesi: $sesiList,
                bilikNama: $bilik->nama,
                bilanganPeserta: $kumpulan->bilangan_peserta,
                kategoriLabel: $kumpulan->kategori,
                namaPengerusi: $kumpulan->nama_pengerusi,
                pemohonNama: $user->name,
                pemohonEmail: $user->email,
                jumlahTarikh: $semuaTarikh->count(),
                jumlahSesi: count($sesiList),
            ));
        } catch (\Throwable) {
            // Kegagalan emel tidak patut batalkan tempahan yang berjaya dibuat
        }

        // 5. Log audit
        AuditLogger::catat('buat_tempahan_berulang', $kumpulan, [
            'kumpulan_id' => $kumpulan->id,
            'jumlah_tarikh' => $semuaTarikh->count(),
            'jumlah_sesi' => count($sesiList),
        ]);

        return redirect('/tempahan?tarikh_filter=akan_datang')
            ->with('success', 'Tempahan berulang berjaya dibuat ('.$semuaTarikh->count().' tarikh).');
    }

    /**
     * Kemaskini medan bukan-slot untuk semua tempahan dalam kumpulan.
     * PUT /tempahan-berulang/{kumpulan}
     */
    public function update(Request $request, TempahanBerulang $kumpulan): RedirectResponse
    {
        $user = $request->user();

        // Hanya pemilik kumpulan atau pentadbir boleh kemaskini
        if ((int) $kumpulan->user_id !== (int) $user->id && ! $user->isPentadbir()) {
            abort(403, 'Anda tidak dibenarkan mengemaskini kumpulan ini.');
        }

        $validated = $request->validate([
            'nama_mesyuarat' => ['required', 'string', 'max:255'],
            'bilangan_peserta' => ['required', 'integer', 'min:1'],
            'kategori' => ['required', 'string', 'max:255'],
            'nama_pengerusi' => ['required', 'string', 'max:255'],
            'tujuan' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($kumpulan, $validated): void {
            // Kemaskini rekod kumpulan induk
            $kumpulan->update($validated);

            // Kemaskini semua tempahan aktif (yang belum ditolak)
            $kumpulan->tempahanAktif()->update([
                'nama_mesyuarat' => $validated['nama_mesyuarat'],
                'bilangan_peserta' => $validated['bilangan_peserta'],
                'kategori' => $validated['kategori'],
                'nama_pengerusi' => $validated['nama_pengerusi'],
                'tujuan' => $validated['tujuan'] ?? null,
            ]);
        });

        AuditLogger::catat('kemaskini_kumpulan_berulang', $kumpulan, [
            'kumpulan_id' => $kumpulan->id,
        ]);

        return redirect()->route('tempahan.index')
            ->with('success', 'Semua tempahan dalam kumpulan berjaya dikemaskini.');
    }

    /**
     * Padam tempahan berulang dengan skop (ini sahaja / semua dalam kumpulan).
     * DELETE /tempahan/{tempahan}/padam-berulang
     */
    public function destroy(Request $request, Tempahan $tempahan): RedirectResponse
    {
        $this->authorize('delete', $tempahan);

        $skop = $request->input('skop', 'ini');
        $kumpulan = $tempahan->kumpulanBerulang;

        DB::transaction(function () use ($tempahan, $kumpulan, $skop): void {
            if ($skop === 'semua' && $kumpulan) {
                // Padam semua tempahan individu, kemudian kumpulan induk
                $kumpulan->tempahan()->delete();
                $kumpulan->delete();
            } else {
                // Padam tempahan ini sahaja
                $tempahan->delete();

                // Jika kumpulan kini tiada tempahan langsung, buang juga rekod kumpulan
                if ($kumpulan && $kumpulan->tempahan()->doesntExist()) {
                    $kumpulan->delete();
                }
            }
        });

        AuditLogger::catat('padam_tempahan', $tempahan, [
            'skop' => $skop,
            'kumpulan_id' => $kumpulan?->id,
        ]);

        $mesej = $skop === 'semua'
            ? 'Semua tempahan dalam kumpulan berjaya dipadam.'
            : 'Tempahan berjaya dipadam.';

        return redirect()->route('tempahan.index')->with('success', $mesej);
    }
}
