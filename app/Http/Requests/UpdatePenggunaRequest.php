<?php

namespace App\Http\Requests;

use App\Enums\PerananPengguna;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPentadbir() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'peranan' => ['required', 'in:' . PerananPengguna::validasiIn()],
            'aktif'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Sila masukkan nama pengguna.',
            'peranan.required' => 'Sila pilih peranan.',
            'peranan.in'       => 'Peranan yang dipilih tidak sah.',
        ];
    }
}
