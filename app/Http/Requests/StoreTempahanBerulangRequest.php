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
use Illuminate\Validation\Rule;

class StoreTempahanBerulangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $kategoriKeys = array_keys(config('ibook.kategori_mesyuarat', []));

        return [
            // ── Medan biasa (sama dengan StoreTempahanRequest) ──
            'nama_mesyuarat'      => ['required', 'string', 'max:255'],
            'bilik_id'            => ['required', 'exists:bilik_mesyuarat,id'],
            'sesi'                => ['required', 'array', 'min:1'],
            'sesi.*'              => ['in:' . SesiTempahan::validasiIn()],
            'bilangan_peserta'    => ['required', 'integer', 'min:1'],
            'kategori'            => ['required', 'string', Rule::in($kategoriKeys)],
            'nama_pengerusi'      => ['required', 'string', 'max:255'],
            'tujuan'              => ['nullable', 'string', 'max:1000'],

            // ── Medan berulang ──
            'jenis'               => ['required', Rule::in(['mingguan', 'bulanan'])],
            'setiap_n'            => ['required', 'integer', 'min:1', 'max:12'],
            'hari_dalam_minggu'   => ['nullable', 'required_if:jenis,mingguan', 'array'],
            'hari_dalam_minggu.*' => ['integer', 'between:0,6'],
            'tarikh_mula'         => ['required', 'date', 'after_or_equal:today'],
            'tarikh_tamat'        => [
                'required',
                'date',
                'after:tarikh_mula',
                'before_or_equal:' . now()->addYears(2)->toDateString(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis.required'                => 'Sila pilih jenis ulangan.',
            'jenis.in'                      => 'Jenis ulangan tidak sah.',
            'setiap_n.required'             => 'Sila nyatakan selang ulangan.',
            'setiap_n.min'                  => 'Selang ulangan mesti sekurang-kurangnya 1.',
            'setiap_n.max'                  => 'Selang ulangan tidak boleh melebihi 12.',
            'hari_dalam_minggu.required_if' => 'Sila pilih sekurang-kurangnya satu hari untuk ulangan mingguan.',
            'tarikh_mula.required'          => 'Sila pilih tarikh mula.',
            'tarikh_mula.after_or_equal'    => 'Tarikh mula mesti hari ini atau selepasnya.',
            'tarikh_tamat.required'         => 'Sila pilih tarikh tamat ulangan.',
            'tarikh_tamat.after'            => 'Tarikh tamat mesti selepas tarikh mula.',
            'tarikh_tamat.before_or_equal'  => 'Tarikh tamat tidak boleh melebihi 2 tahun dari sekarang.',
            'sesi.required'                 => 'Sila pilih sekurang-kurangnya satu sesi.',
            'bilik_id.required'             => 'Sila pilih bilik mesyuarat.',
            'bilik_id.exists'               => 'Bilik yang dipilih tidak wujud.',
            'bilangan_peserta.required'     => 'Sila masukkan bilangan peserta.',
            'bilangan_peserta.min'          => 'Bilangan peserta mesti sekurang-kurangnya 1.',
        ];
    }
}
