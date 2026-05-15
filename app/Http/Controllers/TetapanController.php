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
            'nama_jabatan' => 'required|string|max:255',
            'singkatan' => 'required|string|max:20',
            'masa_mula' => 'required',
            'masa_tamat' => 'required',
        ], [
            'nama_jabatan.required' => 'Sila masukkan nama jabatan.',
            'singkatan.required' => 'Sila masukkan singkatan jabatan.',
            'masa_mula.required' => 'Sila tetapkan masa mula operasi.',
            'masa_tamat.required' => 'Sila tetapkan masa tamat operasi.',
        ]);

        Tetapan::set('nama_jabatan', $request->nama_jabatan);
        Tetapan::set('singkatan', $request->singkatan);
        Tetapan::set('masa_mula', $request->masa_mula);
        Tetapan::set('masa_tamat', $request->masa_tamat);
        Tetapan::set('notif_tempahan_baru', $request->boolean('notif_tempahan_baru') ? '1' : '0');
        Tetapan::set('notif_kelulusan', $request->boolean('notif_kelulusan') ? '1' : '0');
        Tetapan::set('peringatan_mesyuarat', $request->boolean('peringatan_mesyuarat') ? '1' : '0');

        return redirect()->route('tetapan.index')
            ->with('success', 'Tetapan sistem berjaya disimpan.');
    }
}
