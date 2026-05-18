<?php

namespace App\Http\Controllers;

use App\Models\Tetapan;
use Illuminate\Http\Request;

class TetapanController extends Controller
{
    public function index()
    {
        $tetapan = Tetapan::getAll();
        return view('tetapan.index', compact('tetapan'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_sistem'    => 'nullable|string|max:100',
            'nama_jabatan'   => 'required|string|max:255',
            'emel_pentadbir' => 'nullable|email|max:255',
        ], [
            'nama_jabatan.required' => 'Sila masukkan nama bahagian.',
            'emel_pentadbir.email'  => 'Format emel pentadbir tidak sah.',
        ]);

        Tetapan::set('nama_sistem',    $request->nama_sistem ?? '');
        Tetapan::set('nama_jabatan',   $request->nama_jabatan);
        Tetapan::set('emel_pentadbir', $request->emel_pentadbir ?? '');
        Tetapan::set('notif_tempahan_baru',   $request->boolean('notif_tempahan_baru') ? '1' : '0');
        Tetapan::set('peringatan_mesyuarat',   $request->boolean('peringatan_mesyuarat') ? '1' : '0');

        return redirect()->route('tetapan.index')
            ->with('success', 'Tetapan sistem berjaya disimpan.');
    }
}
