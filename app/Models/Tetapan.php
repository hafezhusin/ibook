<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tetapan extends Model
{
    protected $table = 'tetapan';

    protected $fillable = ['kunci', 'nilai'];

    public static function get(string $kunci, $default = null)
    {
        $tetapan = static::where('kunci', $kunci)->first();
        return $tetapan ? $tetapan->nilai : $default;
    }

    public static function set(string $kunci, $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }

    public static function getAll(): array
    {
        try {
            return static::all()->pluck('nilai', 'kunci')->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
