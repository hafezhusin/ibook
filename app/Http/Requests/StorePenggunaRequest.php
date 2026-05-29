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

use App\Enums\PerananPengguna;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StorePenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPentadbir() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'peranan'     => ['required', 'in:'.PerananPengguna::validasiIn()],
            'bahagian_id' => ['required', 'integer', 'exists:bahagian,id'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Sila masukkan nama pengguna.',
            'email.required' => 'Sila masukkan emel.',
            'email.unique' => 'Emel ini telah digunakan.',
            'email.email' => 'Format emel tidak sah.',
            'peranan.required' => 'Sila pilih peranan.',
            'peranan.in'         => 'Peranan yang dipilih tidak sah.',
            'bahagian_id.required' => 'Sila pilih bahagian.',
            'bahagian_id.exists'   => 'Bahagian yang dipilih tidak sah.',
            'password.required' => 'Sila masukkan kata laluan.',
            'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
        ];
    }
}
