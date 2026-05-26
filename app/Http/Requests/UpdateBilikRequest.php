<?php
/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Unauthorized copying, modification, distribution, or use of this software,
 * via any medium, is strictly prohibited. Proprietary and confidential.
 */


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBilikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPentadbir() ?? false;
    }

    public function rules(): array
    {
        return [
            'nama'        => ['required', 'string', 'max:255'],
            'kapasiti'    => ['required', 'integer', 'min:1', 'max:500'],
            'lokasi'      => ['nullable', 'string', 'max:255'],
            'kemudahan'   => ['nullable', 'array'],
            'kemudahan.*' => ['string', 'max:100'],
            'status'      => ['required', 'in:aktif,tidak_aktif'],
            'gambar'      => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'     => 'Sila masukkan nama bilik.',
            'kapasiti.required' => 'Sila masukkan kapasiti bilik.',
            'kapasiti.min'      => 'Kapasiti mestilah sekurang-kurangnya 1 orang.',
            'kapasiti.max'      => 'Kapasiti tidak boleh melebihi 500 orang.',
            'status.required'   => 'Sila pilih status bilik.',
            'status.in'         => 'Status tidak sah.',
            'gambar.image'      => 'Fail yang dimuat naik mestilah gambar.',
            'gambar.mimes'      => 'Gambar mestilah dalam format JPG, PNG atau WebP.',
            'gambar.max'        => 'Saiz gambar tidak boleh melebihi 5MB.',
        ];
    }
}
