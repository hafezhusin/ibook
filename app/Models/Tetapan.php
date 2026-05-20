<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Tetapan extends Model
{
    protected $table = 'tetapan';

    protected $fillable = ['kunci', 'nilai'];

    /** Kunci cache untuk semua tetapan */
    const CACHE_KEY = 'tetapan_sistem_all';

    /** TTL cache: 24 jam */
    const CACHE_TTL = 86400;

    /**
     * Ambil satu nilai tetapan mengikut kunci.
     * Guna cache getAll() supaya tidak ada query tambahan.
     */
    public static function get(string $kunci, $default = null): mixed
    {
        $all = static::getAll();
        return $all[$kunci] ?? $default;
    }

    /**
     * Simpan atau kemaskini satu tetapan.
     * Cache akan dikosongkan secara berasingan selepas semua set() dipanggil.
     */
    public static function set(string $kunci, $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }

    /**
     * Ambil semua tetapan sebagai array kunci => nilai.
     * Hasil dicache selama 24 jam — dikosongkan bila ada kemaskini.
     */
    public static function getAll(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
                return static::all()->pluck('nilai', 'kunci')->toArray();
            });
        } catch (\Exception $e) {
            // Jangan biarkan kegagalan cache atau DB pecahkan aplikasi
            try {
                return static::all()->pluck('nilai', 'kunci')->toArray();
            } catch (\Exception $e2) {
                return [];
            }
        }
    }

    /**
     * Kosongkan cache tetapan.
     * Dipanggil selepas sebarang kemaskini tetapan.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
