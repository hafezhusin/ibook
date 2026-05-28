<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Pembangun : Mohd Hafez bin Husin (Unit Aplikasi Gunasama)
 */

namespace App\Services;

use App\Mail\NotifikasiTempahanBaharu;
use App\Mail\PengesahanTempahan;
use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\Tetapan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Servis e-mel tempahan.
 *
 * Memisahkan logik penghantaran e-mel daripada TempahanController supaya
 * controller hanya bertanggungjawab terhadap aliran HTTP, bukan pembinaan Mailable.
 *
 * Semua kaedah dibungkus try/catch — kegagalan e-mel tidak patut
 * mengganggu aliran utama tempahan.
 */
class TempahanMailService
{
    /**
     * Hantar e-mel pengesahan kepada pemohon dan notifikasi kepada Urus Setia.
     *
     * @param  Tempahan[]  $tempahanDibuat  rekod yang baru dicipta
     * @param  array<string, mixed>  $validated  data dari FormRequest
     */
    public function hantarSelepasStore(array $tempahanDibuat, array $validated, BilikMesyuarat $bilik, User $user): void
    {
        if (empty($tempahanDibuat)) {
            return;
        }

        $tarikhLabel = Carbon::parse($validated['tarikh'])->locale('ms')->isoFormat('dddd, D MMMM YYYY');
        $kategoriLabel = Tempahan::KATEGORI[$validated['kategori']] ?? $validated['kategori'];
        $noRujukan = $tempahanDibuat[0]->no_rujukan;

        // 1. Pengesahan kepada pemohon
        if (Tetapan::get('notif_kelulusan', '1') === '1' && $user->email) {
            try {
                Mail::to($user->email)->send(new PengesahanTempahan(
                    noRujukan: $noRujukan,
                    namaMesyuarat: $validated['nama_mesyuarat'],
                    tarikhLabel: $tarikhLabel,
                    semuaSesi: $validated['sesi'],
                    bilikNama: $bilik->nama,
                    bilanganPeserta: $validated['bilangan_peserta'],
                    kategoriLabel: $kategoriLabel,
                    namaPengerusi: $validated['nama_pengerusi'],
                    tujuan: $validated['tujuan'] ?? '',
                    pemohonNama: $user->name,
                    pemohonEmail: $user->email,
                ));
            } catch (\Throwable $e) {
                Log::warning('E-mel pengesahan tempahan gagal dihantar: '.$e->getMessage());
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
                        noRujukan: $noRujukan,
                        berulang: false,
                        jumlahSesi: count($tempahanDibuat),
                    ));
                } catch (\Throwable $e) {
                    Log::warning('E-mel notifikasi Urus Setia gagal dihantar: '.$e->getMessage());
                }
            }
        }
    }
}
