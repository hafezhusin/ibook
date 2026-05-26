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


namespace App\Http\Requests;

use App\Enums\SesiTempahan;
use Illuminate\Foundation\Http\FormRequest;

class StoreTempahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Semua pengguna log masuk boleh buat tempahan
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nama_mesyuarat'   => ['required', 'string', 'max:255'],
            'tarikh'           => ['required', 'date', 'after_or_equal:today'],
            'bilik_id'         => ['required', 'exists:bilik_mesyuarat,id'],
            'sesi'             => ['required', 'array', 'min:1'],
            'sesi.*'           => ['in:' . SesiTempahan::validasiIn()],
            'bilangan_peserta' => ['required', 'integer', 'min:1'],
            'kategori'         => ['required', 'string', 'in:' . implode(',', array_keys(config('ibook.kategori_mesyuarat', [])))],
            'nama_pengerusi'   => ['required', 'string', 'max:255'],
            'tujuan'           => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_mesyuarat.required'   => 'Sila masukkan nama mesyuarat.',
            'tarikh.required'           => 'Sila pilih tarikh.',
            'tarikh.after_or_equal'     => 'Tarikh mesti hari ini atau selepasnya.',
            'bilik_id.required'         => 'Sila pilih bilik mesyuarat.',
            'bilik_id.exists'           => 'Bilik yang dipilih tidak wujud.',
            'sesi.required'             => 'Sila pilih sekurang-kurangnya satu sesi.',
            'sesi.min'                  => 'Sila pilih sekurang-kurangnya satu sesi.',
            'sesi.*.in'                 => 'Sesi yang dipilih tidak sah.',
            'bilangan_peserta.required' => 'Sila masukkan bilangan peserta.',
            'bilangan_peserta.min'      => 'Bilangan peserta mestilah sekurang-kurangnya 1 orang.',
            'kategori.required'         => 'Sila pilih kategori mesyuarat.',
            'kategori.in'               => 'Kategori yang dipilih tidak sah.',
            'nama_pengerusi.required'   => 'Sila masukkan nama pengerusi.',
        ];
    }
}
