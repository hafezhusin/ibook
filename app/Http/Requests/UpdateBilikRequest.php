<?php

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
            'gambar'      => ['nullable', 'url', 'max:2048'],
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
            'gambar.url'        => 'URL gambar tidak sah. Sila masukkan URL yang betul (bermula dengan https://).',
        ];
    }
}
