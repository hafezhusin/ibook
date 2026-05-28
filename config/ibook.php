<?php

/**
 * Konfigurasi Pusat — iBook 2.0
 * Semua magic numbers dan tetapan sistem disimpan di sini.
 * Guna: config('ibook.kunci') untuk akses.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Masa Sesi Bilik Mesyuarat
    |--------------------------------------------------------------------------
    */
    'sesi' => [
        'pagi' => [
            'mula' => '09:00',
            'tamat' => '13:00',
            'label' => 'SESI PAGI (9:00 AM - 1:00 PM)',
        ],
        'petang' => [
            'mula' => '14:00',
            'tamat' => '18:00',
            'label' => 'SESI PETANG (2:00 PM - 6:00 PM)',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (dalam saat)
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'dashboard' => 300,   // 5 minit
        'kalendar_events' => 180,   // 3 minit
        'laporan' => 600,   // 10 minit
    ],

    /*
    |--------------------------------------------------------------------------
    | Paginasi
    |--------------------------------------------------------------------------
    */
    'paginate' => [
        'tempahan' => 20,
        'pengguna' => 50,
        'laporan' => 25,
    ],

    /*
    |--------------------------------------------------------------------------
    | Had Bilangan
    |--------------------------------------------------------------------------
    */
    'had' => [
        'mesyuarat_akan_datang_dashboard' => 10,
        'carian_global' => 15,
        'laporan_eksport_baris' => 5000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Kategori Mesyuarat
    |--------------------------------------------------------------------------
    */
    'kategori_mesyuarat' => [
        'mesyuarat' => 'Mesyuarat',
        'perbincangan' => 'Perbincangan',
        'taklimat' => 'Taklimat',
        'bengkel' => 'Bengkel/Workshop',
        'latihan' => 'Latihan/Kursus',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tempahan Berulang
    |--------------------------------------------------------------------------
    */
    'berulang' => [
        'had_kejadian' => 12,   // Maksimum bilangan kejadian per kumpulan
        'had_setiap_n' => 12,   // Maksimum nilai setiap_n (setiap N minggu/bulan)
        'had_tahun' => 2,    // Tarikh tamat maksimum dari hari ini (tahun)
    ],

    /*
    |--------------------------------------------------------------------------
    | Versi Sistem
    |--------------------------------------------------------------------------
    */
    'versi' => '2.0',
    'tarikh_kemaskini' => 'Mei 2026',

];
