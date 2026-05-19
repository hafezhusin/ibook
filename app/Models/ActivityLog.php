<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    public $timestamps = false; // Guna 'dicipta_pada' sahaja

    protected $fillable = [
        'pengguna_id',
        'tindakan',
        'model_jenis',
        'model_id',
        'penerangan',
        'butiran',
        'ip_address',
        'dicipta_pada',
    ];

    protected $casts = [
        'butiran'      => 'array',
        'dicipta_pada' => 'datetime',
    ];

    public function pengguna()
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}
