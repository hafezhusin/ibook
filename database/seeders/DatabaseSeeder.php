<?php

namespace Database\Seeders;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\Tetapan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tetapan sistem
        Tetapan::set('nama_jabatan', 'Bahagian Pengurusan Teknologi Maklumat');
        Tetapan::set('singkatan', 'BPTM');
        Tetapan::set('masa_mula', '08:00');
        Tetapan::set('masa_tamat', '17:00');
        Tetapan::set('notif_tempahan_baru', '1');
        Tetapan::set('notif_kelulusan', '1');
        Tetapan::set('peringatan_mesyuarat', '1');

        // Pengguna
        $admin = User::create([
            'name' => 'Ahmad bin Ismail',
            'email' => 'admin@ibook.gov.my',
            'password' => Hash::make('password'),
            'jabatan' => 'Pentadbiran',
            'peranan' => User::PERANAN_PENTADBIR,
        ]);

        $urusSetia = User::create([
            'name' => 'Siti Aminah binti Hassan',
            'email' => 'siti@ibook.gov.my',
            'password' => Hash::make('password'),
            'jabatan' => 'Kewangan',
            'peranan' => User::PERANAN_URUS_SETIA,
        ]);

        $staf = User::create([
            'name' => 'Mohd Rizal bin Abdullah',
            'email' => 'rizal@ibook.gov.my',
            'password' => Hash::make('password'),
            'jabatan' => 'ICT',
            'peranan' => User::PERANAN_STAF,
        ]);

        // Bilik Mesyuarat
        $bilikUtama = BilikMesyuarat::create([
            'nama' => 'Bilik Mesyuarat Utama',
            'kapasiti' => 40,
            'kemudahan' => ['Projektor', 'Papan Putih', 'Sistem Audio', 'Video Conferencing'],
            'status' => 'aktif',
            'lokasi' => 'Tingkat 3',
        ]);

        $bilik1 = BilikMesyuarat::create([
            'nama' => 'Bilik Perbincangan 1',
            'kapasiti' => 15,
            'kemudahan' => ['Projektor', 'Papan Putih'],
            'status' => 'aktif',
            'lokasi' => 'Tingkat 2',
        ]);

        $bilik2 = BilikMesyuarat::create([
            'nama' => 'Bilik Perbincangan 2',
            'kapasiti' => 10,
            'kemudahan' => ['Papan Putih'],
            'status' => 'aktif',
            'lokasi' => 'Tingkat 2',
        ]);

        $dewan = BilikMesyuarat::create([
            'nama' => 'Dewan Serbaguna',
            'kapasiti' => 100,
            'kemudahan' => ['Projektor', 'Sistem Audio', 'Pendingin Hawa', 'WiFi'],
            'status' => 'aktif',
            'lokasi' => 'Tingkat 1',
        ]);

        // Sampel Tempahan
        Tempahan::create([
            'nama_mesyuarat' => 'Mesyuarat Pengurusan Bil. 3/2026',
            'tarikh' => now()->toDateString(),
            'sesi' => 'pagi',
            'masa_mula' => '09:00',
            'masa_tamat' => '13:00',
            'bilik_id' => $bilikUtama->id,
            'user_id' => $admin->id,
            'bilangan_peserta' => 25,
            'kategori' => 'pengurusan',
            'nama_pengerusi' => 'Ahmad bin Ismail',
            'tujuan' => 'Mesyuarat pengurusan bulanan',
            'status' => 'diluluskan',
            'diluluskan_oleh' => $admin->id,
            'diluluskan_pada' => now()->subDay(),
        ]);

        Tempahan::create([
            'nama_mesyuarat' => 'Perbincangan Projek ICT',
            'tarikh' => now()->addDay()->toDateString(),
            'sesi' => 'pagi',
            'masa_mula' => '09:00',
            'masa_tamat' => '13:00',
            'bilik_id' => $bilik1->id,
            'user_id' => $staf->id,
            'bilangan_peserta' => 10,
            'kategori' => 'teknikal',
            'nama_pengerusi' => 'Mohd Rizal bin Abdullah',
            'tujuan' => 'Perbincangan pembangunan sistem baharu',
            'status' => 'diluluskan',
        ]);

        Tempahan::create([
            'nama_mesyuarat' => 'Taklimat Belanjawan 2027',
            'tarikh' => now()->addDay()->toDateString(),
            'sesi' => 'petang',
            'masa_mula' => '14:00',
            'masa_tamat' => '18:00',
            'bilik_id' => $bilikUtama->id,
            'user_id' => $urusSetia->id,
            'bilangan_peserta' => 30,
            'kategori' => 'taklimat',
            'nama_pengerusi' => 'Siti Aminah binti Hassan',
            'tujuan' => 'Taklimat perancangan belanjawan tahunan',
            'status' => 'diluluskan',
            'diluluskan_oleh' => $admin->id,
            'diluluskan_pada' => now()->subDay(),
        ]);
    }
}
