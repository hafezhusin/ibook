# iBook 2.0 — Sistem Tempahan Bilik Mesyuarat

Sistem tempahan bilik mesyuarat dalaman untuk Bahagian Pengurusan Teknologi Maklumat (BPTM).

---

## Stack Teknikal

| Komponen | Versi |
|----------|-------|
| PHP | 8.3 |
| Laravel | 11.x |
| Database | MySQL 8.4 |
| CSS Framework | Tailwind CSS (CDN) |
| Kalendar | FullCalendar 6 |
| PDF Export | barryvdh/laravel-dompdf |
| Excel Export | maatwebsite/laravel-excel |

---

## Setup Tempatan (Laragon)

```bash
# 1. Clone & masuk folder
cd C:\laragon\www\ibook

# 2. Install dependencies
composer install

# 3. Salin .env
cp .env.example .env

# 4. Jana app key
php artisan key:generate

# 5. Tetapkan DB dalam .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=ibook
DB_USERNAME=root
DB_PASSWORD=

# 6. Jalankan migration & seeder
php artisan migrate --seed

# 7. Buka http://ibook.test
```

> **PENTING**: Guna PHP Laragon C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe
> bukan PHP sistem (XAMPP 5.6.3 tidak serasi).

---

## Struktur Folder Penting

```
app/
├── Enums/          ← PHP Backed Enums (StatusTempahan, PerananPengguna, SesiTempahan)
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/   ← Form Request validation classes
├── Models/
├── Policies/       ← TempahanPolicy (Laravel Gate)
└── Services/       ← DashboardService, AuditLogger
config/
└── ibook.php       ← Konfigurasi pusat
```

---

## Peranan & Hak Akses

| Fungsi | Staf | Urus Setia | Pentadbir |
|--------|:----:|:----------:|:---------:|
| Buat Tempahan | v | v | v |
| Edit Tempahan Sendiri | v | v | v |
| Edit Tempahan Unit | v | v | v |
| Eksport PDF/Excel | x | v | v |
| Reset Kata Laluan Pengguna | x | v | v |
| Urus Pengguna | x | x | v |
| Urus Bilik | x | x | v |
| Tetapan Sistem | x | x | v |

---

## Config Pusat (config/ibook.php)

config('ibook.sesi.pagi.mula')       = 09:00
config('ibook.cache.dashboard')      = 300 saat
config('ibook.paginate.tempahan')    = 20
config('ibook.kategori_mesyuarat')   = array kategori

---

## Activity Log

Tindakan direkodkan: buat_tempahan, kemaskini_tempahan, eksport_pdf, eksport_excel,
tambah_pengguna, kemaskini_pengguna, reset_kata_laluan, aktifkan/nyahaktifkan_pengguna,
tambah/kemaskini/padam_bilik, kemaskini_profil, tukar_kata_laluan, kemaskini_tetapan.

Guna: AuditLogger::catat('tindakan', $model, ['data' => 'tambahan']);

---

## Deploy ke InfinityFree

1. Upload fail via File Manager ke htdocs/
2. Tetapkan .env dengan DB credentials InfinityFree (DB_HOST dari cPanel)
3. Padam cache views: htdocs/storage/framework/views/*.php
4. Jalankan SQL migration baru via phpMyAdmin

---

## Nota Penting

- Jangan jalankan php artisan migrate pada hosting — guna phpMyAdmin
- Cache driver: file (tiada Redis pada shared hosting)
- APP_DEBUG=false wajib pada production
- Logout mesti guna POST form dengan @csrf

---

Dibangunkan oleh: Mohd Hafez bin Husin, Unit Aplikasi Gunasama, BPTM
