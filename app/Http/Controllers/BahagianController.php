<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara.
 */

namespace App\Http\Controllers;

use App\Mail\PembatalanTempahanOtomatik;
use App\Models\Bahagian;
use App\Models\Tempahan;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BahagianController extends Controller
{
    public function index()
    {
        $bahagian = Bahagian::withCount([
            'bilik',
            'bilik as bilik_aktif_count' => fn ($q) => $q->where('status', 'aktif'),
            'pengguna',
        ])->orderBy('kod')->get();

        return view('bahagian.index', compact('bahagian'));
    }

    public function create()
    {
        return view('bahagian.form', ['bahagian' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kod'   => ['required', 'string', 'max:20', 'unique:bahagian,kod', 'regex:/^[A-Z0-9_-]+$/'],
            'nama'  => ['required', 'string', 'max:150'],
            'lokasi'=> ['nullable', 'string', 'max:500'],
            'telefon'=> ['nullable', 'string', 'max:20'],
            'emel'  => ['nullable', 'email:rfc', 'max:100'],
        ], [
            'kod.required'  => 'Kod bahagian wajib diisi.',
            'kod.unique'    => 'Kod ini sudah digunakan oleh bahagian lain.',
            'kod.regex'     => 'Kod hanya boleh mengandungi huruf besar, nombor, - dan _.',
            'nama.required' => 'Nama bahagian wajib diisi.',
            'emel.email'    => 'Format emel tidak sah.',
        ]);

        $data['aktif']              = true;
        $data['cross_booking_aktif'] = false;

        $bahagian = Bahagian::create($data);
        AuditLogger::catat('tambah_bahagian', $bahagian, ['kod' => $bahagian->kod, 'nama' => $bahagian->nama]);

        return redirect()->route('bahagian.index')
            ->with('success', "Bahagian [{$bahagian->kod}] berjaya ditambah.");
    }

    public function edit(Bahagian $bahagian)
    {
        return view('bahagian.form', compact('bahagian'));
    }

    public function update(Request $request, Bahagian $bahagian)
    {
        $data = $request->validate([
            'kod'    => ['required', 'string', 'max:20', "unique:bahagian,kod,{$bahagian->id}", 'regex:/^[A-Z0-9_-]+$/'],
            'nama'   => ['required', 'string', 'max:150'],
            'lokasi' => ['nullable', 'string', 'max:500'],
            'telefon'=> ['nullable', 'string', 'max:20'],
            'emel'   => ['nullable', 'email:rfc', 'max:100'],
        ], [
            'kod.required'  => 'Kod bahagian wajib diisi.',
            'kod.unique'    => 'Kod ini sudah digunakan oleh bahagian lain.',
            'kod.regex'     => 'Kod hanya boleh mengandungi huruf besar, nombor, - dan _.',
            'nama.required' => 'Nama bahagian wajib diisi.',
            'emel.email'    => 'Format emel tidak sah.',
        ]);

        // Rakam nilai LAMA sebelum kemaskini (before/after snapshot)
        $fieldsDiPantau = ['kod', 'nama', 'lokasi', 'telefon', 'emel'];
        $sebelum = $bahagian->only($fieldsDiPantau);

        $bahagian->update($data);

        // Bina diff sebelum vs selepas
        $selepas = $bahagian->only($fieldsDiPantau);
        $perubahan = [];
        foreach ($fieldsDiPantau as $f) {
            if ((string) ($sebelum[$f] ?? '') !== (string) ($selepas[$f] ?? '')) {
                $perubahan[$f] = ['lama' => $sebelum[$f], 'baru' => $selepas[$f]];
            }
        }

        AuditLogger::catat('kemaskini_bahagian', $bahagian, ['kod' => $bahagian->kod, 'perubahan' => $perubahan]);

        return redirect()->route('bahagian.index')
            ->with('success', "Maklumat bahagian [{$bahagian->kod}] berjaya dikemaskini.");
    }

    /**
     * Togol status aktif/tidak aktif bahagian.
     * Jika bahagian dinyahaktifkan, semua tempahan akan datang (menunggu/diluluskan)
     * bagi bilik-bilik bahagian tersebut akan dibatalkan secara automatik.
     */
    public function toggleAktif(Bahagian $bahagian)
    {
        // Elak nyahaktif bahagian sendiri
        if ($bahagian->id === auth()->user()->bahagian_id) {
            return back()->with('error', 'Anda tidak boleh menyahaktifkan bahagian anda sendiri.');
        }

        $aktifBaru = ! $bahagian->aktif;
        $bahagian->update(['aktif' => $aktifBaru]);

        $jumlahDibatalkan = 0;

        // Auto-batal semua tempahan akan datang bila bahagian dinyahaktifkan
        if (! $aktifBaru) {
            $bilikIds = $bahagian->bilik()->pluck('id');

            if ($bilikIds->isNotEmpty()) {
                // Load dulu sebelum update — supaya boleh hantar emel kepada pemohon
                $tempahanTerjejas = Tempahan::whereIn('bilik_id', $bilikIds)
                    ->where('tarikh', '>=', today())
                    ->whereIn('status', ['menunggu', Tempahan::STATUS_DILULUSKAN])
                    ->with(['pengguna:id,name,email', 'bilik:id,nama'])
                    ->get();

                $jumlahDibatalkan = $tempahanTerjejas->count();

                if ($jumlahDibatalkan > 0) {
                    // Batch update menggunakan ID yang tepat sama dengan yang diload
                    Tempahan::whereIn('id', $tempahanTerjejas->pluck('id'))
                        ->update([
                            'status'            => Tempahan::STATUS_DIBATALKAN,
                            'catatan_penolakan' => "Auto-dibatalkan: Bahagian [{$bahagian->kod}] telah dinyahaktifkan pada " . now()->format('d/m/Y') . '.',
                            'dikemaskini_oleh'  => auth()->id(),
                            'dikemaskini_pada'  => now(),
                        ]);

                    // Hantar emel kepada setiap pemohon — satu emel per pengguna
                    // dengan senarai semua tempahan mereka yang dibatalkan
                    $tarikhBatal = now()->format('d/m/Y');
                    $tempahanTerjejas->groupBy('user_id')
                        ->each(function ($tempahanUser) use ($bahagian, $tarikhBatal) {
                            $pemohon = $tempahanUser->first()->pengguna;
                            if (! $pemohon?->email) {
                                return;
                            }
                            try {
                                Mail::to($pemohon->email)->send(new PembatalanTempahanOtomatik(
                                    pemohonNama: $pemohon->name,
                                    pemohonEmail: $pemohon->email,
                                    tempahanDibatal: $tempahanUser,
                                    bahagianKod: $bahagian->kod,
                                    tarikhBatal: $tarikhBatal,
                                ));
                            } catch (\Throwable) {
                                // Kegagalan emel tidak patut batalkan operasi toggle bahagian
                            }
                        });
                }
            }
        }

        AuditLogger::catat('togol_bahagian_aktif', $bahagian, [
            'kod'              => $bahagian->kod,
            'aktif'            => $bahagian->aktif,
            'tempahan_dibatal' => $jumlahDibatalkan,
        ]);

        $status = $aktifBaru ? 'diaktifkan' : 'dinyahaktifkan';
        $msg    = "Bahagian [{$bahagian->kod}] berjaya {$status}.";

        if ($jumlahDibatalkan > 0) {
            $msg .= " {$jumlahDibatalkan} tempahan akan datang telah dibatalkan secara automatik.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Togol kebenaran cross-booking untuk bahagian ini.
     * Pentadbir sistem boleh mengaktifkan ini bagi setiap bahagian
     * yang telah mendapat kelulusan pengurusan atasan.
     */
    public function toggleCrossBooking(Bahagian $bahagian)
    {
        $bahagian->update(['cross_booking_aktif' => ! $bahagian->cross_booking_aktif]);
        AuditLogger::catat('togol_cross_booking', $bahagian, [
            'kod'                 => $bahagian->kod,
            'cross_booking_aktif' => $bahagian->cross_booking_aktif,
        ]);

        $status = $bahagian->cross_booking_aktif ? 'dibolehkan' : 'dimatikan';

        return back()->with('success', "Cross-booking bilik [{$bahagian->kod}] berjaya {$status}.");
    }
}
