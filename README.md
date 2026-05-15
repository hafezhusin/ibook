# iBook 2.0 - Sistem Tempahan Bilik Mesyuarat

Sistem tempahan bilik mesyuarat berbasis Laravel 11 dengan MariaDB.

## Ciri-ciri

- **3 Peranan Pengguna**: Pentadbir Sistem, Urus Setia, Staf
- **Dashboard**: Statistik, mesyuarat akan datang, menunggu kelulusan
- **Kalendar**: Paparan bulanan/mingguan/harian (FullCalendar)
- **Tempahan Bilik**: Borang tempahan dengan semakan konflik
- **Aliran Kelulusan**: Urus Setia meluluskan/menolak permohonan
- **Bilik Mesyuarat**: CRUD dengan kemudahan & kapasiti
- **Laporan**: Graf bulanan & kategori, ringkasan penggunaan bilik
- **Export**: PDF (DomPDF) dan Excel (Laravel Excel)
- **Tetapan Sistem**: Maklumat organisasi, waktu operasi, notifikasi

## Keperluan Sistem

- PHP 8.2+
- Composer 2.x
- MariaDB / MySQL 8+
- Node.js (pilihan, untuk Vite)

## Pemasangan

### 1. Clone projek

```bash
git clone https://github.com/USERNAME/ibook.git
cd ibook
```

### 2. Pasang dependencies

```bash
composer install
```

### 3. Sediakan fail .env

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi database dalam .env

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ibook
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Jalankan migrations & seeder

```bash
php artisan migrate --seed
```

### 6. Jana storage link

```bash
php artisan storage:link
```

### 7. Jalankan server

```bash
php artisan serve
```

Buka browser: `http://localhost:8000`

## Akaun Lalai (selepas seeder)

| Peranan | Emel | Kata Laluan |
|---------|------|-------------|
| Pentadbir Sistem | admin@ibook.gov.my | password |
| Urus Setia | siti@ibook.gov.my | password |
| Staf | rizal@ibook.gov.my | password |

> **Penting**: Tukar kata laluan selepas log masuk pertama!

## Struktur Peranan

| Peranan | Dashboard | Tempahan | Kelulusan | Bilik | Pengguna | Tetapan |
|---------|-----------|----------|-----------|-------|----------|---------|
| Pentadbir Sistem | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Urus Setia | ✓ | ✓ | ✓ | — | — | — |
| Staf | ✓ | ✓ (sendiri) | — | — | — | — |

## Teknologi

- **Backend**: Laravel 11, PHP 8.2
- **Database**: MariaDB / MySQL
- **Frontend**: Blade + Tailwind CSS (CDN)
- **Kalendar**: FullCalendar 6
- **Carta**: Chart.js 4
- **PDF**: barryvdh/laravel-dompdf
- **Excel**: maatwebsite/excel

## Lesen

Hak Cipta Terpelihara &copy; 2025
