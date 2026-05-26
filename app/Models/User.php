<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const PERANAN_PENTADBIR  = 'pentadbir_sistem';
    const PERANAN_URUS_SETIA = 'urus_setia';
    const PERANAN_STAF       = 'staf';

    const SENARAI_UNIT = [
        'Helpdesk BPTM',
        'Pejabat Pengarah',
        'Seksyen Digital dan Projek Khas',
        'Seksyen Kualiti dan Perancangan',
        'Seksyen Perkhidmatan ICT 1',
        'Sub Unit CM',
        'Sub Unit LMS',
        'Sub Unit TR',
        'Unit Aplikasi Gunasama',
        'Unit Authorization',
        'Unit Bayaran',
        'Unit Gaji',
        'Unit GLFMCO, Aplikasi BPTM',
        'Unit Khidmat Pelanggan',
        'Unit Lejar Am dan Kawalan Data Induk',
        'Unit Maklumat Online (eApps)',
        'Unit Operasi Aplikasi Teras',
        'Unit Pelaporan Strategik (BWBI)',
        'Unit Pengkosan',
        'Unit Pengurusan Antaramuka / Integrasi',
        'Unit Pengurusan Dana, Pinjaman dan Pelaburan',
        'Unit Pengurusan Infrastruktur',
        'Unit Pengurusan Rangkaian dan Keselamatan ICT',
        'Unit Pengurusan Wang Tak Dituntut',
        'Unit Pentadbiran dan Pengurusan Kewangan',
        'Unit Terimaan',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'jabatan',
        'peranan',
        'aktif',
        'last_login_at',
        'dua_faktor_aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'aktif'             => 'boolean',
            'dua_faktor_aktif'  => 'boolean',
            'last_login_at'     => 'datetime',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetKataLaluan($token));
    }

    public function tempahan()
    {
        return $this->hasMany(Tempahan::class);
    }

    public function isPentadbir(): bool
    {
        return $this->peranan === self::PERANAN_PENTADBIR;
    }

    public function isUrusSetia(): bool
    {
        return $this->peranan === self::PERANAN_URUS_SETIA;
    }

    public function isStaf(): bool
    {
        return $this->peranan === self::PERANAN_STAF;
    }

    public function bolehLuluskan(): bool
    {
        return in_array($this->peranan, [self::PERANAN_PENTADBIR, self::PERANAN_URUS_SETIA]);
    }

    public function getLabelPerananAttribute(): string
    {
        return match ($this->peranan) {
            self::PERANAN_PENTADBIR => 'Pentadbir Sistem',
            self::PERANAN_URUS_SETIA => 'Urus Setia',
            self::PERANAN_STAF => 'Staf',
            default => 'Tidak Diketahui',
        };
    }

    public function getMaskedEmailAttribute(): string
    {
        [$nama, $domain] = explode('@', $this->email, 2);
        $prefix = substr($nama, 0, min(3, strlen($nama)));
        return $prefix . '***@' . $domain;
    }
}
