# CHANGELOG — iBook 2.0

Semua perubahan ketara direkodkan di sini mengikut format [Tariikh] — [Jenis] — [Penerangan].

---

## [2026-05-19] — Penambahbaikan Sistem (v2.1)

### Ditambah
- **Enum PHP**: `StatusTempahan`, `PerananPengguna`, `SesiTempahan` untuk type safety
- **config/ibook.php**: Konfigurasi pusat — masa sesi, cache TTL, had bilangan, kategori
- **Activity Logging**: Jadual `activity_log` + `AuditLogger` service untuk rekod audit trail lengkap
- **Form Requests**: `StoreTempahanRequest`, `UpdateTempahanRequest`, `StoreBilikRequest`, `UpdateBilikRequest`, `StorePenggunaRequest`, `UpdatePenggunaRequest`
- **TempahanPolicy**: Autoriti terpusat guna Laravel Gate/Policy
- **DashboardService**: Logik dashboard diasingkan ke service class dengan file cache (TTL 5 minit)
- **Blade Components**: `x-badge-status`, `x-alert` untuk UI reusable
- **README.md**: Dokumentasi setup, struktur, peranan, config
- **CHANGELOG.md**: Rekod perubahan sistem

### Diubah
- `DashboardController` — dikurangkan dari 100+ baris kepada 10 baris (guna DashboardService)
- `TempahanController::show/edit` — guna `$this->authorize()` gantikan `abort(403)` manual
- `TempahanController::store/update` — guna Form Request gantikan inline validate()
- `BilikController::store/update` — guna Form Request
- `PenggunaController::store/update` — guna Form Request
- `Tempahan::MASA_SESI` — label dikemaskini (buang "1" dan "2")
- `AppServiceProvider` — daftarkan TempahanPolicy

---

## [2026-05-19] — Nama Sistem Dinamik

### Diubah
- `login.blade.php` — nama sistem guna `$tetapan['nama_sistem']` (hapus hardcode)
- `errors/403,404,500.blade.php` — title dinamik
- `tempahan/pdf.blade.php` — header & footer dinamik

---

## [2026-05-19] — Profil Pengguna & Reset Kata Laluan

### Ditambah
- `ProfilController` — kemaskini profil sendiri, tukar kata laluan
- `resources/views/profil/index.blade.php` — halaman profil dengan tab
- Route `/profil` untuk semua peranan

### Diubah
- `app.blade.php` — dropdown avatar dengan "Profil Saya" dan "Log Keluar"
- `pengguna/_kad.blade.php`, `_baris.blade.php` — urus setia boleh reset password
- Sidebar urus setia kini ada akses ke halaman pengguna

---

## [2026-05-18] — Pembetulan Kalendar

### Diubah
- `login.blade.php`, `kalendar/index.blade.php` — guna `URLSearchParams` untuk encode `+` dalam timezone ISO 8601

---

## [2026-05-17] — Audit Trail Tempahan

### Ditambah
- Kolum `dikemaskini_oleh` dan `dikemaskini_pada` dalam jadual `tempahan`

---

## [2026-05-16] — Status Tempahan

### Diubah
- Buang status `menunggu` — tempahan terus diluluskan automatik
- Enum status kini: `diluluskan`, `ditolak` sahaja

---

## [2026-05-15] — Pengurusan Pengguna

### Ditambah
- Tab aktif/nyahaktif pengguna
- Paparan kad & senarai
- Tindakan pukal (bulk aktif/nyahaktif)

---

*Direkodkan oleh: Mohd Hafez bin Husin, BPTM*
