<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara.
 */

namespace App\Http\Controllers;

use App\Models\Bahagian;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

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

        $bahagian->update($data);
        AuditLogger::catat('kemaskini_bahagian', $bahagian, ['kod' => $bahagian->kod]);

        return redirect()->route('bahagian.index')
            ->with('success', "Maklumat bahagian [{$bahagian->kod}] berjaya dikemaskini.");
    }

    /**
     * Togol status aktif/tidak aktif bahagian.
     */
    public function toggleAktif(Bahagian $bahagian)
    {
        // Elak nyahaktif bahagian sendiri
        if ($bahagian->id === auth()->user()->bahagian_id) {
            return back()->with('error', 'Anda tidak boleh menyahaktifkan bahagian anda sendiri.');
        }

        $bahagian->update(['aktif' => ! $bahagian->aktif]);
        AuditLogger::catat('togol_bahagian_aktif', $bahagian, [
            'kod'    => $bahagian->kod,
            'aktif'  => $bahagian->aktif,
        ]);

        $status = $bahagian->aktif ? 'diaktifkan' : 'dinyahaktifkan';

        return back()->with('success', "Bahagian [{$bahagian->kod}] berjaya {$status}.");
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
