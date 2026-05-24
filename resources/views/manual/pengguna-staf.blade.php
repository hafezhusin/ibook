<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Manual Pengguna Staf — Sistem iBOOK v2</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 11pt;
    color: #1a202c;
    background: #ffffff;
    line-height: 1.5;
  }

  /* ── Page breaks ───────────────────────────────────── */
  .page-break { page-break-after: always; }
  .no-break   { page-break-inside: avoid; }

  /* ── Cover Page ─────────────────────────────────────── */
  .cover {
    background: #1a1a2e;
    color: #ffffff;
    height: 100%;
    min-height: 740px;
    padding: 60px 50px;
    text-align: center;
    page-break-after: always;
  }
  .cover-logo { width: 90px; margin: 0 auto 20px; }
  .cover-logo img { width: 90px; }
  .cover-badge {
    display: inline-block;
    background: #f59e0b;
    color: #1a1a2e;
    font-size: 9pt;
    font-weight: bold;
    padding: 4px 14px;
    border-radius: 20px;
    letter-spacing: 1px;
    margin-bottom: 30px;
  }
  .cover h1 {
    font-size: 28pt;
    font-weight: bold;
    color: #f59e0b;
    margin-bottom: 8px;
  }
  .cover h2 {
    font-size: 16pt;
    color: #e2e8f0;
    font-weight: normal;
    margin-bottom: 6px;
  }
  .cover h3 {
    font-size: 12pt;
    color: #94a3b8;
    font-weight: normal;
    margin-bottom: 40px;
  }
  .cover-divider {
    width: 60px;
    height: 3px;
    background: #f59e0b;
    margin: 30px auto;
  }
  .cover-meta {
    font-size: 9pt;
    color: #94a3b8;
    margin-top: 20px;
  }
  .cover-meta strong { color: #f59e0b; }
  .cover-footer {
    position: absolute;
    bottom: 40px;
    left: 0;
    right: 0;
    font-size: 8.5pt;
    color: #64748b;
  }
  .cover-warning {
    margin-top: 30px;
    background: rgba(245,158,11,0.15);
    border: 1px solid rgba(245,158,11,0.4);
    border-radius: 8px;
    padding: 12px 20px;
    font-size: 9pt;
    color: #fcd34d;
  }

  /* ── TOC ─────────────────────────────────────────────── */
  .toc { padding: 40px 50px; page-break-after: always; }
  .toc h2 { font-size: 16pt; color: #1a1a2e; border-bottom: 2px solid #f59e0b; padding-bottom: 8px; margin-bottom: 20px; }
  .toc-entry { display: flex; align-items: baseline; margin-bottom: 6px; font-size: 10.5pt; }
  .toc-num { color: #f59e0b; font-weight: bold; width: 30px; flex-shrink: 0; }
  .toc-title { flex: 1; }
  .toc-dots { flex: 1; border-bottom: 1px dotted #cbd5e1; margin: 0 8px; }
  .toc-page { color: #64748b; font-size: 9.5pt; }
  .toc-sub { padding-left: 28px; font-size: 9.5pt; color: #475569; margin-bottom: 3px; }

  /* ── Section header ─────────────────────────────────── */
  .section-header {
    background: #1a1a2e;
    color: #ffffff;
    padding: 18px 30px;
    margin-bottom: 24px;
    border-left: 5px solid #f59e0b;
  }
  .section-header .num {
    font-size: 9pt;
    color: #f59e0b;
    font-weight: bold;
    letter-spacing: 1px;
    margin-bottom: 4px;
  }
  .section-header h2 {
    font-size: 15pt;
    font-weight: bold;
    margin: 0;
  }

  /* ── Content container ────────────────────────────────── */
  .content { padding: 0 40px 30px; }

  /* ── Screenshot box ──────────────────────────────────── */
  .screenshot-box {
    margin: 16px 0;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
  }
  .screenshot-box img {
    width: 100%;
    display: block;
  }
  .screenshot-caption {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 6px 12px;
    font-size: 8.5pt;
    color: #64748b;
    font-style: italic;
  }

  /* ── Steps ───────────────────────────────────────────── */
  .steps { margin: 12px 0; }
  .step {
    display: flex;
    align-items: flex-start;
    margin-bottom: 10px;
    page-break-inside: avoid;
  }
  .step-num {
    background: #f59e0b;
    color: #1a1a2e;
    font-weight: bold;
    font-size: 9.5pt;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    text-align: center;
    line-height: 24px;
    flex-shrink: 0;
    margin-right: 12px;
    margin-top: 1px;
  }
  .step-body { flex: 1; font-size: 10.5pt; }
  .step-body strong { color: #1a1a2e; }
  .step-body .hint {
    font-size: 9pt;
    color: #64748b;
    margin-top: 2px;
  }

  /* ── Info / tip boxes ──────────────────────────────── */
  .info-box {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    padding: 10px 14px;
    margin: 14px 0;
    font-size: 10pt;
    border-radius: 0 4px 4px 0;
  }
  .tip-box {
    background: #fefce8;
    border-left: 4px solid #f59e0b;
    padding: 10px 14px;
    margin: 14px 0;
    font-size: 10pt;
    border-radius: 0 4px 4px 0;
  }
  .warn-box {
    background: #fff7ed;
    border-left: 4px solid #f97316;
    padding: 10px 14px;
    margin: 14px 0;
    font-size: 10pt;
    border-radius: 0 4px 4px 0;
  }
  .info-box strong, .tip-box strong, .warn-box strong { font-weight: bold; }

  /* ── Table ───────────────────────────────────────────── */
  table.info-table {
    width: 100%;
    border-collapse: collapse;
    margin: 14px 0;
    font-size: 10pt;
  }
  table.info-table th {
    background: #1a1a2e;
    color: #f59e0b;
    padding: 7px 12px;
    text-align: left;
    font-size: 9.5pt;
  }
  table.info-table td {
    padding: 7px 12px;
    border-bottom: 1px solid #e2e8f0;
  }
  table.info-table tr:nth-child(even) td { background: #f8fafc; }

  /* ── Page number footer (DomPDF workaround — static) ─ */
  p { margin-bottom: 8px; }
  ul { margin: 8px 0 8px 20px; }
  ul li { margin-bottom: 4px; font-size: 10.5pt; }

  .sub-section { margin-top: 20px; margin-bottom: 10px; }
  .sub-section h3 {
    font-size: 11.5pt;
    font-weight: bold;
    color: #1a1a2e;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 5px;
    margin-bottom: 10px;
  }

  .badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 8.5pt;
    font-weight: bold;
  }
  .badge-green { background: #dcfce7; color: #166534; }
  .badge-amber { background: #fef3c7; color: #92400e; }
  .badge-red   { background: #fee2e2; color: #991b1b; }

  .label-inline {
    font-weight: bold;
    color: #1a1a2e;
    background: #f1f5f9;
    padding: 1px 6px;
    border-radius: 3px;
    font-size: 9.5pt;
  }
</style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- COVER PAGE                                                 --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="cover" style="position:relative;">
  <div class="cover-logo">
    @if(file_exists(public_path('images/logo.png')))
    <img src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/logo.png'))) }}" alt="Logo BPTM">
    @endif
  </div>

  <div class="cover-badge">MANUAL PENGGUNA</div>

  <h1>Sistem iBOOK v2</h1>
  <h2>Sistem Tempahan Bilik Mesyuarat</h2>
  <h3>Bahagian Pengurusan Teknologi Maklumat (BPTM)</h3>

  <div class="cover-divider"></div>

  <div style="font-size:11pt; color:#cbd5e1; margin-bottom:16px;">
    Panduan Lengkap Penggunaan Sistem bagi Pengguna Staf
  </div>

  <div class="cover-warning">
    <strong>Nota Penting:</strong> Manual ini khusus untuk pengguna berperanan <strong>Staf</strong>.
    Fungsi pentadbiran dan pengurusan adalah di luar skop manual ini.
  </div>

  <div class="cover-meta" style="margin-top:40px;">
    <strong>Versi:</strong> 2.0 &nbsp;|&nbsp;
    <strong>Tarikh:</strong> {{ now()->format('d/m/Y') }} &nbsp;|&nbsp;
    <strong>Disediakan oleh:</strong> Unit ICT, BPTM
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- TABLE OF CONTENTS                                          --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="toc">
  <h2>Isi Kandungan</h2>

  <div class="toc-entry">
    <span class="toc-num">1.</span>
    <span class="toc-title">Pengenalan</span>
    <span class="toc-dots"></span>
    <span class="toc-page">3</span>
  </div>
  <div class="toc-entry">
    <span class="toc-num">2.</span>
    <span class="toc-title">Log Masuk ke Sistem</span>
    <span class="toc-dots"></span>
    <span class="toc-page">3</span>
  </div>
  <div class="toc-entry">
    <span class="toc-num">3.</span>
    <span class="toc-title">Papan Pemuka (Dashboard)</span>
    <span class="toc-dots"></span>
    <span class="toc-page">4</span>
  </div>
  <div class="toc-entry">
    <span class="toc-num">4.</span>
    <span class="toc-title">Senarai Tempahan</span>
    <span class="toc-dots"></span>
    <span class="toc-page">5</span>
  </div>
  <div class="toc-entry">
    <span class="toc-num">5.</span>
    <span class="toc-title">Membuat Tempahan Baru</span>
    <span class="toc-dots"></span>
    <span class="toc-page">6</span>
  </div>
  <div class="toc-sub">5.1. Tempahan Biasa (Satu Tarikh)</div>
  <div class="toc-sub">5.2. Tempahan Berulang (Mingguan / Bulanan)</div>
  <div class="toc-entry">
    <span class="toc-num">6.</span>
    <span class="toc-title">Semak Ketersediaan Bilik</span>
    <span class="toc-dots"></span>
    <span class="toc-page">9</span>
  </div>
  <div class="toc-entry">
    <span class="toc-num">7.</span>
    <span class="toc-title">Laporan</span>
    <span class="toc-dots"></span>
    <span class="toc-page">10</span>
  </div>
  <div class="toc-entry">
    <span class="toc-num">8.</span>
    <span class="toc-title">Kalendar Tempahan</span>
    <span class="toc-dots"></span>
    <span class="toc-page">11</span>
  </div>
  <div class="toc-entry">
    <span class="toc-num">9.</span>
    <span class="toc-title">Profil Pengguna</span>
    <span class="toc-dots"></span>
    <span class="toc-page">12</span>
  </div>
  <div class="toc-entry">
    <span class="toc-num">10.</span>
    <span class="toc-title">Soalan Lazim (FAQ)</span>
    <span class="toc-dots"></span>
    <span class="toc-page">13</span>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- 1. PENGENALAN                                              --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="section-header">
  <div class="num">BAB 1</div>
  <h2>Pengenalan</h2>
</div>
<div class="content">
  <p>Sistem <strong>iBOOK v2</strong> adalah sistem tempahan bilik mesyuarat dalam talian yang membolehkan
  warga Bahagian Pengurusan Teknologi Maklumat (BPTM) membuat, melihat, dan mengurus tempahan bilik
  mesyuarat dengan mudah dan cepat.</p>

  <div class="info-box" style="margin-top:14px;">
    <strong>Fungsi Utama bagi Pengguna Staf:</strong>
    <ul>
      <li>Membuat tempahan bilik mesyuarat (biasa dan berulang)</li>
      <li>Melihat dan mengurus senarai tempahan sendiri</li>
      <li>Menyemak ketersediaan bilik pada tarikh tertentu</li>
      <li>Melihat laporan penggunaan bilik</li>
      <li>Melihat kalendar tempahan keseluruhan</li>
    </ul>
  </div>

  <table class="info-table" style="margin-top:16px;">
    <tr>
      <th>Maklumat Sistem</th>
      <th>Butiran</th>
    </tr>
    <tr><td>Nama Sistem</td><td>Sistem iBOOK v2</td></tr>
    <tr><td>Versi</td><td>2.0</td></tr>
    <tr><td>Pelayar Disyorkan</td><td>Google Chrome, Microsoft Edge (versi terkini)</td></tr>
    <tr><td>Resolusi Skrin</td><td>Minimum 1280 x 768 piksel</td></tr>
    <tr><td>URL Sistem</td><td>Hubungi Unit ICT untuk URL terkini</td></tr>
  </table>
</div>
<div class="page-break"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- 2. LOG MASUK                                               --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="section-header">
  <div class="num">BAB 2</div>
  <h2>Log Masuk ke Sistem</h2>
</div>
<div class="content">
  <div class="steps">
    <div class="step">
      <div class="step-num">1</div>
      <div class="step-body">
        <strong>Buka pelayar web</strong> dan navigasi ke URL sistem iBOOK v2 yang diberikan oleh Unit ICT.
      </div>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <div class="step-body">
        <strong>Masukkan Emel</strong> jabatan anda dalam ruangan <span class="label-inline">Emel</span>
        (contoh: nama@jabatan.gov.my).
      </div>
    </div>
    <div class="step">
      <div class="step-num">3</div>
      <div class="step-body">
        <strong>Masukkan Kata Laluan</strong> dalam ruangan <span class="label-inline">Kata Laluan</span>.
        Klik ikon mata (<strong>👁</strong>) untuk papar/sembunyi kata laluan.
      </div>
    </div>
    <div class="step">
      <div class="step-num">4</div>
      <div class="step-body">
        (Pilihan) Tandakan <span class="label-inline">Ingat saya</span> supaya sesi log masuk diingat pada
        komputer yang sama.
      </div>
    </div>
    <div class="step">
      <div class="step-num">5</div>
      <div class="step-body">
        Klik butang <span class="label-inline">Log Masuk</span>. Anda akan dibawa ke Papan Pemuka selepas log
        masuk berjaya.
        <div class="hint">Jika kata laluan terlupa, klik pautan <em>Lupa kata laluan?</em> untuk menetapkan semula.</div>
      </div>
    </div>
  </div>

  <div class="screenshot-box no-break">
    <img src="{{ 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('docs/screenshots/01-login.jpg'))) }}">
    <div class="screenshot-caption">Rajah 2.1 — Halaman Log Masuk Sistem iBOOK v2. Bahagian kanan menunjukkan ketersediaan bilik semasa tanpa perlu log masuk.</div>
  </div>

  <div class="warn-box">
    <strong>Penting:</strong> Jangan kongsikan kata laluan anda dengan sesiapa. Hubungi Unit ICT jika anda
    mengesyaki akaun anda telah diakses oleh pihak tidak bertanggungjawab.
  </div>
</div>
<div class="page-break"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- 3. PAPAN PEMUKA                                            --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="section-header">
  <div class="num">BAB 3</div>
  <h2>Papan Pemuka (Dashboard)</h2>
</div>
<div class="content">
  <p>Selepas log masuk, anda akan melihat <strong>Papan Pemuka</strong> yang memaparkan gambaran keseluruhan
  sistem dan aktiviti tempahan anda.</p>

  <div class="screenshot-box no-break">
    <img src="{{ 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('docs/screenshots/02-dashboard.jpg'))) }}">
    <div class="screenshot-caption">Rajah 3.1 — Papan Pemuka menunjukkan ringkasan aktiviti tempahan, bilik tersedia, dan pautan pintas.</div>
  </div>

  <div class="sub-section">
    <h3>Maklumat pada Papan Pemuka</h3>
    <table class="info-table">
      <tr>
        <th>Elemen</th>
        <th>Penerangan</th>
      </tr>
      <tr>
        <td>Selamat Datang</td>
        <td>Paparan nama pengguna dan tarikh semasa</td>
      </tr>
      <tr>
        <td>Esok Pagi / Esok Petang</td>
        <td>Bilangan bilik kosong untuk esok hari</td>
      </tr>
      <tr>
        <td>Jumlah Tempahan</td>
        <td>Bilangan tempahan anda pada bulan ini</td>
      </tr>
      <tr>
        <td>Bilik Tersedia Hari Ini</td>
        <td>Bilangan bilik yang masih kosong hari ini</td>
      </tr>
      <tr>
        <td>Semak Bilik Kosong</td>
        <td>Carian pantas ketersediaan bilik mengikut tarikh dan sesi</td>
      </tr>
      <tr>
        <td>Mesyuarat Akan Datang</td>
        <td>Senarai tempahan anda dalam 7 hari akan datang</td>
      </tr>
    </table>
  </div>

  <div class="screenshot-box no-break" style="margin-top:16px;">
    <img src="{{ 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('docs/screenshots/03-dashboard-bawah.jpg'))) }}">
    <div class="screenshot-caption">Rajah 3.2 — Bahagian bawah Papan Pemuka menunjukkan Mesyuarat Akan Datang dan ketersediaan bilik hari ini.</div>
  </div>

  <div class="tip-box">
    <strong>Tips:</strong> Gunakan menu navigasi di sebelah kiri untuk beralih antara modul. Menu menunjukkan
    fungsi yang dibenarkan mengikut peranan anda.
  </div>
</div>
<div class="page-break"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- 4. SENARAI TEMPAHAN                                        --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="section-header">
  <div class="num">BAB 4</div>
  <h2>Senarai Tempahan</h2>
</div>
<div class="content">
  <p>Modul <strong>Senarai Tempahan</strong> memaparkan semua tempahan yang telah dibuat. Klik
  <span class="label-inline">Senarai Tempahan</span> pada menu navigasi kiri untuk mengaksesnya.</p>

  <div class="screenshot-box no-break">
    <img src="{{ 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('docs/screenshots/04-senarai-tempahan.jpg'))) }}">
    <div class="screenshot-caption">Rajah 4.1 — Senarai Tempahan dengan ciri tapis, carian, dan butang tindakan.</div>
  </div>

  <div class="sub-section">
    <h3>Ciri-ciri Halaman Senarai Tempahan</h3>
    <table class="info-table">
      <tr>
        <th>Ciri</th>
        <th>Penerangan</th>
      </tr>
      <tr>
        <td>Kad Pintasan (Hari Ini / Baharu 24j / Esok / Bulan Ini)</td>
        <td>Klik untuk menapis rekod mengikut tempoh pilihan</td>
      </tr>
      <tr>
        <td>Carian Nama Mesyuarat</td>
        <td>Taip nama mesyuarat untuk mencari rekod tertentu</td>
      </tr>
      <tr>
        <td>Tapis Bilik</td>
        <td>Tapis mengikut bilik mesyuarat tertentu</td>
      </tr>
      <tr>
        <td>Tapis Status</td>
        <td>Tapis mengikut status: <span class="badge badge-green">Sah</span> atau <span class="badge badge-red">Ditolak</span></td>
      </tr>
      <tr>
        <td>Butang Tindakan</td>
        <td>Lihat butiran, edit, atau padam tempahan</td>
      </tr>
      <tr>
        <td>Eksport PDF / Excel</td>
        <td>Muat turun senarai tempahan dalam format PDF atau Excel</td>
      </tr>
    </table>
  </div>

  <div class="sub-section">
    <h3>Status Tempahan</h3>
    <ul>
      <li><span class="badge badge-green">Sah</span> — Tempahan telah disahkan dan diluluskan</li>
      <li><span class="badge badge-red">Ditolak</span> — Tempahan telah ditolak (terdapat konflik atau sebab lain)</li>
    </ul>
  </div>

  <div class="tip-box">
    <strong>Pintasan Penapis:</strong> Gunakan butang <span class="label-inline">Hari Ini</span>,
    <span class="label-inline">Esok</span>, atau <span class="label-inline">Akan Datang</span> untuk melihat
    tempahan mengikut tempoh tertentu dengan cepat.
  </div>
</div>
<div class="page-break"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- 5. MEMBUAT TEMPAHAN BARU                                   --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="section-header">
  <div class="num">BAB 5</div>
  <h2>Membuat Tempahan Baru</h2>
</div>
<div class="content">
  <p>Untuk membuat tempahan baru, klik <span class="label-inline">+ Tempahan Baru</span> pada menu navigasi
  kiri atau butang di sudut atas kanan halaman Senarai Tempahan.</p>

  <div class="sub-section">
    <h3>5.1 — Tempahan Biasa (Satu Tarikh)</h3>
  </div>

  <div class="screenshot-box no-break">
    <img src="{{ 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('docs/screenshots/05-tempahan-baru-atas.jpg'))) }}">
    <div class="screenshot-caption">Rajah 5.1 — Borang Tempahan Baru — Bahagian Maklumat Mesyuarat dan Slot & Lokasi.</div>
  </div>

  <div class="steps" style="margin-top:16px;">
    <div class="step">
      <div class="step-num">1</div>
      <div class="step-body">
        <strong>Bahagian 1 — Maklumat Mesyuarat:</strong><br>
        • <span class="label-inline">Nama Mesyuarat</span> — Masukkan nama mesyuarat (contoh: Mesyuarat Pengurusan Bil. 4/2026).<br>
        • <span class="label-inline">Kategori Mesyuarat</span> — Pilih kategori yang sesuai dari senarai juntai.
      </div>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <div class="step-body">
        <strong>Bahagian 2 — Slot &amp; Lokasi:</strong><br>
        • <span class="label-inline">Tarikh</span> — Pilih tarikh mesyuarat (format YYYY-MM-DD atau klik ikon kalendar).<br>
        • <span class="label-inline">Bilik Mesyuarat</span> — Pilih bilik dari senarai juntai (kapasiti dipaparkan).<br>
        • <span class="label-inline">Sesi Mesyuarat</span> — Pilih Sesi Pagi (9:00 AM – 1:00 PM) atau Sesi Petang (2:00 PM – 6:00 PM), atau klik <span class="label-inline">Sehari Penuh</span> untuk kedua-dua sesi.
        <div class="hint">Sistem akan menyemak konflik secara automatik apabila anda menukar bilik atau tarikh.</div>
      </div>
    </div>
  </div>

  <div class="screenshot-box no-break">
    <img src="{{ 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('docs/screenshots/06-tempahan-baru-tengah.jpg'))) }}">
    <div class="screenshot-caption">Rajah 5.2 — Bahagian Sesi Mesyuarat dan togol Ulang Tempahan.</div>
  </div>

  <div class="steps" style="margin-top:12px;">
    <div class="step">
      <div class="step-num">3</div>
      <div class="step-body">
        <strong>Bahagian 3 — Butiran Penganjur:</strong><br>
        • <span class="label-inline">Bilangan Peserta</span> — Masukkan anggaran bilangan peserta.<br>
        • <span class="label-inline">Nama Pengerusi</span> — Masukkan nama pengerusi mesyuarat.<br>
        • <span class="label-inline">Tujuan / Agenda</span> (Pilihan) — Nyatakan tujuan atau agenda mesyuarat.
      </div>
    </div>
    <div class="step">
      <div class="step-num">4</div>
      <div class="step-body">
        Klik butang <span class="label-inline">Hantar Permohonan</span> untuk menyerahkan tempahan.
        <div class="hint">Tempahan akan diluluskan secara automatik. E-mel pengesahan akan dihantar ke emel anda.</div>
      </div>
    </div>
  </div>

  <div class="screenshot-box no-break">
    <img src="{{ 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('docs/screenshots/07-tempahan-baru-bawah.jpg'))) }}">
    <div class="screenshot-caption">Rajah 5.3 — Bahagian Butiran Penganjur dan butang Hantar Permohonan.</div>
  </div>
</div>
<div class="page-break"></div>

<div class="section-header" style="background:#2d3748;">
  <div class="num" style="color:#f59e0b;">BAB 5 (SAMBUNGAN)</div>
  <h2>5.2 — Tempahan Berulang (Mingguan / Bulanan)</h2>
</div>
<div class="content">
  <p>Fungsi <strong>Ulang Tempahan</strong> membolehkan anda menempah bilik mesyuarat secara berulang untuk
  mesyuarat tetap seperti taklimat mingguan atau mesyuarat pengurusan bulanan.
  <strong>Maksimum 12 kejadian</strong> bagi setiap kumpulan tempahan berulang.</p>

  <div class="info-box">
    <strong>Cara mengaktifkan:</strong> Pada borang Tempahan Baru, tatal ke bawah ke bahagian
    <em>ULANG TEMPAHAN</em> dan klik togol <span class="label-inline">Aktifkan</span>.
  </div>

  <div class="screenshot-box no-break">
    <img src="{{ 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('docs/screenshots/08-tempahan-berulang.jpg'))) }}">
    <div class="screenshot-caption">Rajah 5.4 — Panel Ulang Tempahan apabila togol diaktifkan, menunjukkan pilihan Jenis Ulangan, Hari dalam Minggu, Tarikh Mula dan Tarikh Tamat.</div>
  </div>

  <div class="steps" style="margin-top:16px;">
    <div class="step">
      <div class="step-num">1</div>
      <div class="step-body">
        <strong>Aktifkan togol</strong> <span class="label-inline">Aktifkan</span> dalam bahagian ULANG TEMPAHAN.
      </div>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <div class="step-body">
        <strong>Pilih Jenis Ulangan:</strong><br>
        • <span class="label-inline">Mingguan</span> — Berulang setiap minggu pada hari yang dipilih.<br>
        • <span class="label-inline">Bulanan</span> — Berulang setiap bulan pada tarikh yang sama.
      </div>
    </div>
    <div class="step">
      <div class="step-num">3</div>
      <div class="step-body">
        Tetapkan <span class="label-inline">Ulang Setiap</span> — contoh: "setiap 2 minggu" atau "setiap 1 bulan".
      </div>
    </div>
    <div class="step">
      <div class="step-num">4</div>
      <div class="step-body">
        <strong>(Mingguan sahaja)</strong> Pilih <span class="label-inline">Hari dalam Minggu</span> — klik satu atau lebih hari (Isnin, Selasa, dll.).
      </div>
    </div>
    <div class="step">
      <div class="step-num">5</div>
      <div class="step-body">
        Tetapkan <span class="label-inline">Tarikh Mula</span> dan <span class="label-inline">Tarikh Tamat Ulangan</span>.
        <div class="hint">Sistem akan menjana pratonton senarai tarikh secara automatik untuk pengesahan anda.</div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">6</div>
      <div class="step-body">
        Lengkapkan bahagian <strong>Butiran Penganjur</strong> dan klik <span class="label-inline">Hantar Permohonan</span>.
        Semua sesi akan dicipta serentak dalam satu transaksi.
      </div>
    </div>
  </div>

  <div class="warn-box">
    <strong>Amaran Konflik:</strong> Jika mana-mana tarikh dalam kumpulan berulang mempunyai konflik (bilik
    telah ditempah), sistem akan menolak kesemua permohonan dan memaparkan senarai tarikh bermasalah.
    Tukar bilik atau laraskan tarikh.
  </div>

  <div class="tip-box">
    <strong>Edit atau Padam Tempahan Berulang:</strong> Anda boleh memilih untuk mengedit/memadam
    "Tempahan ini sahaja" atau "Semua dalam kumpulan" semasa mengedit rekod dari Senarai Tempahan.
  </div>
</div>
<div class="page-break"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- 6. SEMAK KETERSEDIAAN                                      --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="section-header">
  <div class="num">BAB 6</div>
  <h2>Semak Ketersediaan Bilik</h2>
</div>
<div class="content">
  <p>Modul <strong>Semak Bilik Kosong</strong> membolehkan anda menyemak bilik mesyuarat yang tersedia pada
  tarikh dan bilangan peserta tertentu sebelum membuat tempahan.</p>

  <div class="steps">
    <div class="step">
      <div class="step-num">1</div>
      <div class="step-body">
        Klik <span class="label-inline">Semak Bilik Kosong</span> pada menu navigasi kiri.
      </div>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <div class="step-body">
        Pilih <span class="label-inline">Tarikh</span> yang hendak disemak menggunakan pemilih tarikh.
      </div>
    </div>
    <div class="step">
      <div class="step-num">3</div>
      <div class="step-body">
        Pilih <span class="label-inline">Sesi</span> — Pagi, Petang, atau Kedua-dua Sesi.
      </div>
    </div>
    <div class="step">
      <div class="step-num">4</div>
      <div class="step-body">
        Masukkan <span class="label-inline">Bilangan Peserta</span> — sistem hanya akan menunjukkan bilik
        yang mencukupi kapasiti.
      </div>
    </div>
    <div class="step">
      <div class="step-num">5</div>
      <div class="step-body">
        Klik butang <span class="label-inline">Semak</span> untuk melihat senarai bilik yang tersedia.
        <div class="hint">Bilik akan ditunjukkan dengan status <span class="badge badge-green">Tersedia</span> atau <span class="badge badge-red">Ditempah</span>.</div>
      </div>
    </div>
  </div>

  <div class="screenshot-box no-break">
    <img src="{{ 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('docs/screenshots/09-semak-ketersediaan.jpg'))) }}">
    <div class="screenshot-caption">Rajah 6.1 — Halaman Semak Ketersediaan Bilik dengan borang carian dan senarai keputusan.</div>
  </div>

  <div class="tip-box">
    <strong>Tips:</strong> Gunakan fungsi Semak Ketersediaan sebelum membuat tempahan untuk memastikan bilik
    yang dikehendaki masih kosong. Klik terus pada bilik tersedia untuk beralih ke borang Tempahan Baru.
  </div>
</div>
<div class="page-break"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- 7. LAPORAN                                                 --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="section-header">
  <div class="num">BAB 7</div>
  <h2>Laporan</h2>
</div>
<div class="content">
  <p>Modul <strong>Laporan</strong> memaparkan statistik dan analitik penggunaan bilik mesyuarat bagi tempoh
  masa tertentu. Klik <span class="label-inline">Laporan</span> pada menu navigasi kiri.</p>

  <div class="screenshot-box no-break">
    <img src="{{ 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('docs/screenshots/10-laporan.jpg'))) }}">
    <div class="screenshot-caption">Rajah 7.1 — Halaman Laporan menunjukkan statistik penggunaan bilik, carta, dan senarai tempahan.</div>
  </div>

  <div class="sub-section">
    <h3>Maklumat dalam Laporan</h3>
    <table class="info-table">
      <tr>
        <th>Jenis Laporan</th>
        <th>Penerangan</th>
      </tr>
      <tr>
        <td>Ringkasan Tempahan</td>
        <td>Jumlah tempahan mengikut status (Sah / Ditolak) bagi tempoh dipilih</td>
      </tr>
      <tr>
        <td>Penggunaan Mengikut Bilik</td>
        <td>Perbandingan kadar penggunaan setiap bilik mesyuarat</td>
      </tr>
      <tr>
        <td>Penggunaan Mengikut Kategori</td>
        <td>Pecahan tempahan mengikut jenis mesyuarat</td>
      </tr>
      <tr>
        <td>Tempahan Akan Datang</td>
        <td>Senarai tempahan yang dijadualkan dalam tempoh tertentu</td>
      </tr>
    </table>
  </div>

  <div class="info-box">
    <strong>Tapis Laporan:</strong> Gunakan pemilih bulan/tahun di bahagian atas laporan untuk melihat data
    bagi tempoh tertentu.
  </div>
</div>
<div class="page-break"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- 8. KALENDAR                                                --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="section-header">
  <div class="num">BAB 8</div>
  <h2>Kalendar Tempahan</h2>
</div>
<div class="content">
  <p>Modul <strong>Kalendar</strong> memaparkan semua tempahan dalam paparan visual bentuk kalendar bulanan,
  mingguan, atau harian. Klik <span class="label-inline">Kalendar</span> pada menu navigasi kiri.</p>

  <div class="screenshot-box no-break">
    <img src="{{ 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('docs/screenshots/11-kalendar.jpg'))) }}">
    <div class="screenshot-caption">Rajah 8.1 — Paparan Kalendar Bulanan menunjukkan semua tempahan yang telah dibuat.</div>
  </div>

  <div class="sub-section">
    <h3>Cara Menggunakan Kalendar</h3>
    <table class="info-table">
      <tr>
        <th>Tindakan</th>
        <th>Cara</th>
      </tr>
      <tr>
        <td>Tukar paparan</td>
        <td>Klik butang <span class="label-inline">Bulan</span>, <span class="label-inline">Minggu</span>, atau <span class="label-inline">Hari</span> di sudut kanan atas</td>
      </tr>
      <tr>
        <td>Navigasi bulan/minggu</td>
        <td>Klik anak panah &lt; atau &gt; untuk beralih ke tarikh sebelum/selepas</td>
      </tr>
      <tr>
        <td>Kembali ke hari ini</td>
        <td>Klik butang <span class="label-inline">Hari Ini</span></td>
      </tr>
      <tr>
        <td>Lihat butiran tempahan</td>
        <td>Klik pada rekod tempahan dalam kalendar untuk melihat butiran lanjut</td>
      </tr>
      <tr>
        <td>Tapis mengikut bilik</td>
        <td>Gunakan senarai bilik di sebelah kiri untuk menapis paparan</td>
      </tr>
    </table>
  </div>

  <div class="tip-box">
    <strong>Tips:</strong> Paparan Kalendar boleh diakses tanpa log masuk melalui halaman utama sistem.
    Ia sesuai untuk semakan cepat jadual mesyuarat.
  </div>
</div>
<div class="page-break"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- 9. PROFIL                                                  --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="section-header">
  <div class="num">BAB 9</div>
  <h2>Profil Pengguna</h2>
</div>
<div class="content">
  <p>Halaman <strong>Profil</strong> membolehkan anda mengemas kini maklumat peribadi dan menukar kata laluan.
  Klik nama pengguna di sudut kanan atas, kemudian pilih <span class="label-inline">Profil</span>.</p>

  <div class="screenshot-box no-break">
    <img src="{{ 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('docs/screenshots/12-profil.jpg'))) }}">
    <div class="screenshot-caption">Rajah 9.1 — Halaman Profil Pengguna untuk kemaskini maklumat dan tukar kata laluan.</div>
  </div>

  <div class="sub-section">
    <h3>Kemaskini Maklumat Profil</h3>
    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <div class="step-body">Klik nama pengguna di sudut kanan atas, pilih <span class="label-inline">Profil</span>.</div>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-body">Kemaskini maklumat yang diperlukan: <strong>Nama</strong>, <strong>No. Telefon</strong>, atau <strong>Jabatan</strong>.</div>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-body">Klik <span class="label-inline">Simpan Perubahan</span> untuk menyimpan maklumat baru.</div>
      </div>
    </div>
  </div>

  <div class="sub-section">
    <h3>Tukar Kata Laluan</h3>
    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <div class="step-body">Pada halaman Profil, tatal ke bahagian <strong>Tukar Kata Laluan</strong>.</div>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-body">Masukkan <span class="label-inline">Kata Laluan Semasa</span>.</div>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-body">Masukkan <span class="label-inline">Kata Laluan Baru</span> (minimum 8 aksara) dan sahkan dalam ruangan <span class="label-inline">Sahkan Kata Laluan Baru</span>.</div>
      </div>
      <div class="step">
        <div class="step-num">4</div>
        <div class="step-body">Klik <span class="label-inline">Tukar Kata Laluan</span> untuk menyimpan.</div>
      </div>
    </div>
  </div>

  <div class="warn-box">
    <strong>Keselamatan Kata Laluan:</strong> Gunakan kombinasi huruf besar, huruf kecil, nombor, dan
    simbol untuk kata laluan yang kuat. Tukar kata laluan secara berkala untuk keselamatan akaun anda.
  </div>

  <div class="sub-section">
    <h3>Log Keluar</h3>
    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <div class="step-body">Klik nama pengguna di sudut kanan atas skrin.</div>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-body">Klik <span class="label-inline">Log Keluar</span> dari menu yang muncul.</div>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-body">Anda akan dihalakan semula ke halaman Log Masuk. Tutup tetingkap pelayar selepas log keluar di komputer awam.</div>
      </div>
    </div>
  </div>
</div>
<div class="page-break"></div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- 10. FAQ                                                    --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="section-header">
  <div class="num">BAB 10</div>
  <h2>Soalan Lazim (FAQ)</h2>
</div>
<div class="content">
  <table class="info-table">
    <tr>
      <th style="width:40%">Soalan</th>
      <th>Jawapan</th>
    </tr>
    <tr>
      <td>Bagaimana jika bilik yang dikehendaki sudah ditempah?</td>
      <td>Sistem akan menunjukkan konflik semasa membuat tempahan. Cuba bilik lain atau tarikh/sesi yang berbeza. Guna Semak Ketersediaan terlebih dahulu.</td>
    </tr>
    <tr>
      <td>Bolehkah saya mengedit tempahan yang sudah dibuat?</td>
      <td>Ya. Pergi ke Senarai Tempahan, klik <span class="label-inline">Tindakan</span> pada rekod berkenaan, dan pilih <em>Edit</em>. Anda boleh kemaskini maklumat kecuali tarikh dan bilik (untuk tempahan berulang).</td>
    </tr>
    <tr>
      <td>Bagaimana cara memadam tempahan?</td>
      <td>Klik <span class="label-inline">Tindakan</span> pada Senarai Tempahan dan pilih <em>Padam</em>. Untuk tempahan berulang, anda boleh memilih padam satu atau semua dalam kumpulan.</td>
    </tr>
    <tr>
      <td>Adakah saya akan terima e-mel pengesahan?</td>
      <td>Ya, sistem akan menghantar e-mel pengesahan ke alamat emel anda selepas tempahan berjaya dibuat, jika tetapan notifikasi sistem aktif.</td>
    </tr>
    <tr>
      <td>Berapa maksimum kejadian untuk tempahan berulang?</td>
      <td>Maksimum 12 kejadian bagi setiap kumpulan tempahan berulang.</td>
    </tr>
    <tr>
      <td>Kenapa saya tidak boleh log masuk?</td>
      <td>Pastikan emel dan kata laluan betul. Jika lupa kata laluan, klik <em>Lupa kata laluan?</em>. Hubungi Unit ICT jika masalah berterusan.</td>
    </tr>
    <tr>
      <td>Bagaimana saya tahu bilik mana yang kosong hari ini?</td>
      <td>Papan Pemuka memaparkan bilangan bilik kosong hari ini. Gunakan modul Semak Bilik Kosong untuk maklumat terperinci.</td>
    </tr>
    <tr>
      <td>Bolehkah saya melihat tempahan orang lain?</td>
      <td>Anda hanya boleh melihat tempahan sendiri dalam Senarai Tempahan. Namun, Kalendar menunjukkan semua tempahan yang ada untuk rujukan jadual.</td>
    </tr>
  </table>

  <div style="margin-top:30px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:20px; text-align:center;">
    <p style="font-size:12pt; font-weight:bold; color:#1a1a2e; margin-bottom:8px;">Hubungi Sokongan Teknikal</p>
    <p style="font-size:10pt; color:#64748b;">Untuk sebarang pertanyaan teknikal atau bantuan berkaitan sistem iBOOK v2,</p>
    <p style="font-size:10pt; color:#64748b;">sila hubungi <strong>Unit ICT, Bahagian Pengurusan Teknologi Maklumat (BPTM)</strong>.</p>
    <div style="margin-top:16px; padding-top:16px; border-top:1px solid #e2e8f0; font-size:9pt; color:#94a3b8;">
      Sistem iBOOK v2 &copy; {{ now()->year }} Bahagian Pengurusan Teknologi Maklumat &mdash; Hak Cipta Terpelihara
    </div>
  </div>
</div>

</body>
</html>
