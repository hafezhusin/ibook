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

use App\Http\Requests\StoreBilikRequest;
use App\Http\Requests\UpdateBilikRequest;
use App\Models\Bahagian;
use App\Models\BilikMesyuarat;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BilikController extends Controller
{
    // Route awam - senarai bilik tanpa log masuk
    public function publicList()
    {
        $bilik = BilikMesyuarat::where('status', 'aktif')
            ->get(['id', 'nama', 'kapasiti', 'kemudahan', 'lokasi']);

        return response()->json($bilik);
    }

    public function index()
    {
        // Pra-kira kiraan tempahan bulan ini dalam satu query (elak N+1)
        $bulan = now()->month;
        $tahun = now()->year;
        $maxSesi = now()->daysInMonth * 2;

        $bilik = BilikMesyuarat::withCount([
            'tempahan as tempahan_bulan_ini' => function ($q) use ($bulan, $tahun) {
                $q->whereMonth('tarikh', $bulan)
                    ->whereYear('tarikh', $tahun)
                    ->where('status', 'diluluskan');
            },
        ])->get();

        // Tetapkan peratus penggunaan sebagai atribut dinamik
        $bilik->each(function ($b) use ($maxSesi) {
            $b->peratus_penggunaan = $maxSesi > 0
                ? (int) round(($b->tempahan_bulan_ini / $maxSesi) * 100)
                : 0;
        });

        return view('bilik.index', compact('bilik'));
    }

    public function create()
    {
        $bahagian = Bahagian::where('aktif', true)->orderBy('kod')->get();
        return view('bilik.form', ['bilik' => null, 'bahagian' => $bahagian]);
    }

    public function store(StoreBilikRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $this->simpanGambarBilik($request->file('gambar'));
        } else {
            unset($data['gambar']);
        }

        $bilik = BilikMesyuarat::create($data);
        AuditLogger::catat('tambah_bilik', $bilik, ['nama' => $bilik->nama]);

        return redirect()->route('bilik.index')
            ->with('success', 'Bilik mesyuarat berjaya ditambah.');
    }

    public function edit(BilikMesyuarat $bilik)
    {
        $bahagian = Bahagian::where('aktif', true)->orderBy('kod')->get();
        return view('bilik.form', compact('bilik', 'bahagian'));
    }

    public function update(UpdateBilikRequest $request, BilikMesyuarat $bilik)
    {
        $data = $request->validated();
        $data['dikemaskini_oleh'] = auth()->user()->name;
        $data['dikemaskini_pada'] = now();

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $this->simpanGambarBilik($request->file('gambar'), $bilik->gambar);
        } else {
            unset($data['gambar']);
        }

        $bilik->update($data);
        AuditLogger::catat('kemaskini_bilik', $bilik, ['nama' => $bilik->nama]);

        return redirect()->route('bilik.index')
            ->with('success', 'Maklumat bilik mesyuarat berjaya dikemaskini.');
    }

    /**
     * Simpan gambar bilik — resize/crop cover ke 800×352px menggunakan GD.
     * Kembalikan path relatif bermula dengan /uploads/bilik/...
     */
    private function simpanGambarBilik($file, ?string $lamaGambar = null): string
    {
        $dir = public_path('uploads/bilik');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Padam gambar lama jika ada
        if ($lamaGambar && str_starts_with($lamaGambar, '/uploads/bilik/')) {
            $lamaPath = public_path(ltrim($lamaGambar, '/'));
            if (file_exists($lamaPath)) {
                @unlink($lamaPath);
            }
        }

        // Buat nama fail unik menggunakan ULID (lebih selamat dari uniqid)
        $namaFail = Str::ulid().'.jpg';
        $targetPath = $dir.'/'.$namaFail;

        // Load imej sumber mengikut MIME
        $mime = $file->getMimeType();
        $src = match (true) {
            str_contains($mime, 'png') => imagecreatefrompng($file->getRealPath()),
            str_contains($mime, 'webp') => imagecreatefromwebp($file->getRealPath()),
            default => imagecreatefromjpeg($file->getRealPath()),
        };

        $srcW = imagesx($src);
        $srcH = imagesy($src);

        $targetW = 800;
        $targetH = 352;

        // Cover crop: skala supaya gambar memenuhi sasaran, kemudian potong tengah
        $scale = max($targetW / $srcW, $targetH / $srcH);
        $cropW = (int) ceil($targetW / $scale);
        $cropH = (int) ceil($targetH / $scale);
        $offsetX = (int) floor(($srcW - $cropW) / 2);
        $offsetY = (int) floor(($srcH - $cropH) / 2);

        $dst = imagecreatetruecolor($targetW, $targetH);
        // Latar putih (untuk PNG lutsinar)
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);

        imagecopyresampled(
            $dst, $src,
            0, 0,
            $offsetX, $offsetY,
            $targetW, $targetH,
            $cropW, $cropH
        );

        imagejpeg($dst, $targetPath, 90);
        imagedestroy($src);
        imagedestroy($dst);

        return '/uploads/bilik/'.$namaFail;
    }

    public function destroy(BilikMesyuarat $bilik)
    {
        // Blok padam jika ada SEBARANG rekod tempahan (aktif ATAU sejarah)
        if ($bilik->tempahan()->exists()) {
            return back()->with('error',
                'Bilik tidak boleh dipadam kerana mempunyai rekod tempahan. '.
                'Sila nyahaktifkan bilik ini sebagai gantinya.'
            );
        }

        // Padam fail gambar jika ada (sebelum soft-delete)
        if ($bilik->gambar && str_starts_with($bilik->gambar, '/uploads/bilik/')) {
            $lamaPath = public_path(ltrim($bilik->gambar, '/'));
            if (file_exists($lamaPath)) {
                @unlink($lamaPath);
            }
        }

        AuditLogger::catat('padam_bilik', null, ['nama' => $bilik->nama, 'id' => $bilik->id]);
        $bilik->delete(); // SoftDelete — rekod kekal dalam DB

        return redirect()->route('bilik.index')
            ->with('success', 'Bilik mesyuarat berjaya dipadam.');
    }
}
