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
use App\Models\Tempahan;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTempahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Tempahan $tempahan */
        $tempahan = $this->route('tempahan');
        $user     = $this->user();

        if ($user->isStaf() && !$tempahan->bolehDiEditOleh($user)) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'nama_mesyuarat'   => ['required', 'string', 'max:255'],
            'tarikh'           => ['required', 'date', 'after_or_equal:today'],
            'bilik_id'         => ['required', 'exists:bilik_mesyuarat,id'],
            'sesi'             => ['required', 'in:' . SesiTempahan::validasiIn()],
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
            'tarikh.after_or_equal'     => 'Tarikh tidak boleh kurang dari hari ini.',
            'bilik_id.required'         => 'Sila pilih bilik mesyuarat.',
            'bilik_id.exists'           => 'Bilik yang dipilih tidak wujud.',
            'sesi.required'             => 'Sila pilih sesi mesyuarat.',
            'sesi.in'                   => 'Sesi yang dipilih tidak sah.',
            'bilangan_peserta.required' => 'Sila masukkan bilangan peserta.',
            'bilangan_peserta.min'      => 'Bilangan peserta mestilah sekurang-kurangnya 1 orang.',
            'kategori.required'         => 'Sila pilih kategori mesyuarat.',
            'kategori.in'               => 'Kategori yang dipilih tidak sah.',
            'nama_pengerusi.required'   => 'Sila masukkan nama pengerusi.',
        ];
    }
}
