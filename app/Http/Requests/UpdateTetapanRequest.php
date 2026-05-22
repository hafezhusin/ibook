<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTetapanRequest extends FormRequest
{
    /**
     * Hanya Pentadbir Sistem boleh kemaskini tetapan.
     * Pertahanan berlapis — route sudah dilindungi middleware role:pentadbir_sistem.
     */
    public function authorize(): bool
    {
        return $this->user()?->isPentadbir() ?? false;
    }

    /**
     * Peraturan validasi yang ketat untuk tetapan sistem.
     */
    public function rules(): array
    {
        return [
            'nama_sistem'       => ['nullable', 'string', 'max:120'],
            'nama_jabatan'      => ['required', 'string', 'max:150'],
            'logo_jabatan'      => ['nullable', 'string', 'max:500', 'regex:/^https?:\/\/.+/i'],
            'emel_pentadbir'    => ['nullable', 'email:rfc', 'max:150'],
            'emel_notifikasi'   => ['nullable', 'email:rfc', 'max:150'],
            'notif_tempahan_baru'  => ['nullable', 'boolean'],
            'notif_kelulusan'      => ['nullable', 'boolean'],
            'peringatan_mesyuarat' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Sanitize input sebelum validasi:
     * - Trim whitespace dari semua string
     * - Normalisasi emel ke huruf kecil
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama_sistem'     => trim((string) ($this->nama_sistem ?? '')),
            'nama_jabatan'    => trim((string) ($this->nama_jabatan ?? '')),
            'logo_jabatan'    => trim((string) ($this->logo_jabatan ?? '')),
            'emel_pentadbir'  => strtolower(trim((string) ($this->emel_pentadbir ?? ''))),
            'emel_notifikasi' => strtolower(trim((string) ($this->emel_notifikasi ?? ''))),
        ]);
    }

    /**
     * Mesej validasi dalam Bahasa Malaysia.
     */
    public function messages(): array
    {
        return [
            'nama_jabatan.required'   => 'Nama bahagian/jabatan wajib diisi.',
            'nama_jabatan.max'        => 'Nama bahagian tidak boleh melebihi 150 aksara.',
            'nama_sistem.max'         => 'Nama sistem tidak boleh melebihi 120 aksara.',
            'emel_pentadbir.email'    => 'Format emel paparan tidak sah. Contoh: admin@jabatan.gov.my',
            'emel_notifikasi.email'   => 'Format emel notifikasi tidak sah. Contoh: notif@jabatan.gov.my',
            'emel_pentadbir.max'      => 'Emel paparan tidak boleh melebihi 150 aksara.',
            'emel_notifikasi.max'     => 'Emel notifikasi tidak boleh melebihi 150 aksara.',
            'logo_jabatan.regex'      => 'URL logo mesti bermula dengan https:// atau http://',
        ];
    }
}
