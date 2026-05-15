<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const PERANAN_PENTADBIR = 'pentadbir_sistem';
    const PERANAN_URUS_SETIA = 'urus_setia';
    const PERANAN_STAF = 'staf';

    protected $fillable = [
        'name',
        'email',
        'password',
        'jabatan',
        'peranan',
        'aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
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
}
