<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9.5px;
    color: #1f2937;
    padding: 0;
}
.header {
    background: #1a1a2e;
    color: #ffffff;
    padding: 18px 24px 14px;
    margin-bottom: 16px;
}
.header-brand { font-size: 20px; font-weight: bold; color: #f59e0b; }
.header-title { font-size: 13px; color: #e5e7eb; margin-top: 3px; }
.header-meta  { font-size: 8.5px; color: #9ca3af; margin-top: 4px; }
.body { padding: 0 24px 20px; }
h2 {
    font-size: 11px;
    font-weight: bold;
    color: #1a1a2e;
    margin: 18px 0 6px;
    padding-bottom: 4px;
    border-bottom: 2px solid #f59e0b;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 4px;
}
th {
    background: #1a1a2e;
    color: #ffffff;
    padding: 5px 8px;
    text-align: left;
    font-size: 8.5px;
    font-weight: bold;
}
td {
    padding: 4px 8px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 9px;
    vertical-align: middle;
}
tr:nth-child(even) td { background: #f9fafb; }
.tr-total td { background: #f3f4f6; font-weight: bold; border-top: 1px solid #d1d5db; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.fw-bold { font-weight: bold; }
.kpi-grid { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
.kpi-grid td { padding: 8px 12px; vertical-align: top; }
.kpi-cell {
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 8px 12px;
    background: #ffffff;
    width: 25%;
}
.kpi-value { font-size: 18px; font-weight: bold; color: #1a1a2e; line-height: 1; }
.kpi-label { font-size: 8px; color: #6b7280; margin-top: 2px; }
.kpi-sub   { font-size: 8px; color: #9ca3af; margin-top: 1px; font-style: italic; }
.badge-green  { color: #15803d; }
.badge-blue   { color: #1d4ed8; }
.badge-amber  { color: #b45309; }
.badge-purple { color: #7e22ce; }
.footer {
    margin: 20px 24px 0;
    padding-top: 8px;
    border-top: 1px solid #e5e7eb;
    font-size: 7.5px;
    color: #9ca3af;
}
</style>
</head>
<body>

{{-- ── Header ─────────────────────────────────────────────────────── --}}
<div class="header">
    <div class="header-brand">iBook 2.0</div>
    <div class="header-title">Laporan Statistik Tempahan Bilik Mesyuarat</div>
    <div class="header-meta">
        Tahun {{ $tahun }}
        &nbsp;&bull;&nbsp;
        Bahagian Pengurusan Teknologi Maklumat, Akaun Negara Malaysia
        &nbsp;&bull;&nbsp;
        Dijana: {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

<div class="body">

{{-- ── KPI Ringkasan ──────────────────────────────────────────────── --}}
<h2>Ringkasan Prestasi</h2>
<table class="kpi-grid">
<tr>
    <td style="width:25%; padding:4px;">
        <div style="border:1px solid #e5e7eb; padding:8px 10px; background:#fff;">
            <div class="kpi-value badge-green">{{ number_format($totalDiluluskan) }}</div>
            <div class="kpi-label">Tempahan Diluluskan</div>
        </div>
    </td>
    <td style="width:25%; padding:4px;">
        <div style="border:1px solid #e5e7eb; padding:8px 10px; background:#fff;">
            <div class="kpi-value badge-blue" style="font-size:12px;">{{ $unitPalingAktif?->unit ?? '—' }}</div>
            <div class="kpi-label">Unit Paling Aktif</div>
            @if($unitPalingAktif)
            <div class="kpi-sub">{{ $unitPalingAktif->jumlah }} tempahan</div>
            @endif
        </div>
    </td>
    <td style="width:25%; padding:4px;">
        <div style="border:1px solid #e5e7eb; padding:8px 10px; background:#fff;">
            <div class="kpi-value badge-amber" style="font-size:12px;">{{ $bilikPalingGuna ? $bilikPalingGuna['nama'] : '—' }}</div>
            <div class="kpi-label">Bilik Paling Digunakan</div>
            @if($bilikPalingGuna)
            <div class="kpi-sub">{{ $bilikPalingGuna['jumlah_tempahan'] }} sesi &bull; {{ $bilikPalingGuna['peratusan'] }}%</div>
            @endif
        </div>
    </td>
    <td style="width:25%; padding:4px;">
        <div style="border:1px solid #e5e7eb; padding:8px 10px; background:#fff;">
            <div class="kpi-value badge-purple">{{ $purataPenggunaan }}%</div>
            <div class="kpi-label">Purata Penggunaan Bilik</div>
        </div>
    </td>
</tr>
</table>

{{-- ── Tempahan Mengikut Bulan ────────────────────────────────────── --}}
<h2>Tempahan Mengikut Bulan</h2>
@php
    $namaBulan = ['Januari','Februari','Mac','April','Mei','Jun','Julai','Ogos','September','Oktober','November','Disember'];
    $jPagi = 0; $jPetang = 0;
@endphp
<table>
    <thead>
        <tr>
            <th>Bulan</th>
            <th class="text-right">Pagi</th>
            <th class="text-right">Petang</th>
            <th class="text-right">Jumlah</th>
        </tr>
    </thead>
    <tbody>
    @foreach($namaBulan as $i => $nm)
        @php
            $p  = $dataBulanSesi['pagi'][$i]   ?? 0;
            $pt = $dataBulanSesi['petang'][$i] ?? 0;
            $jPagi   += $p;
            $jPetang += $pt;
        @endphp
        <tr>
            <td>{{ $nm }}</td>
            <td class="text-right">{{ $p }}</td>
            <td class="text-right">{{ $pt }}</td>
            <td class="text-right fw-bold">{{ $p + $pt }}</td>
        </tr>
    @endforeach
        <tr class="tr-total">
            <td>JUMLAH</td>
            <td class="text-right">{{ $jPagi }}</td>
            <td class="text-right">{{ $jPetang }}</td>
            <td class="text-right">{{ $jPagi + $jPetang }}</td>
        </tr>
    </tbody>
</table>

{{-- ── Tempahan Mengikut Kategori ─────────────────────────────────── --}}
<h2>Tempahan Mengikut Kategori</h2>
<table>
    <thead>
        <tr>
            <th style="width:30px;">#</th>
            <th>Kategori</th>
            <th class="text-right" style="width:120px;">Bilangan Tempahan</th>
        </tr>
    </thead>
    <tbody>
    @foreach($mengikutKategori as $i => $k)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $k->kategori }}</td>
            <td class="text-right">{{ $k->jumlah }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

{{-- ── Tempahan Mengikut Unit ──────────────────────────────────────── --}}
<h2>Tempahan Mengikut Unit (Diluluskan)</h2>
<table>
    <thead>
        <tr>
            <th style="width:30px;">#</th>
            <th>Unit / Seksyen</th>
            <th class="text-right" style="width:140px;">Bilangan Tempahan</th>
        </tr>
    </thead>
    <tbody>
    @foreach($mengikutUnit as $i => $u)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $u->unit }}</td>
            <td class="text-right">{{ $u->jumlah }}</td>
        </tr>
    @endforeach
    @if($mengikutUnit->isEmpty())
        <tr><td colspan="3" class="text-center" style="color:#9ca3af; padding:12px;">Tiada data</td></tr>
    @endif
    </tbody>
</table>

{{-- ── Penggunaan Bilik Mesyuarat ─────────────────────────────────── --}}
<h2>Penggunaan Bilik Mesyuarat</h2>
<table>
    <thead>
        <tr>
            <th>Bilik Mesyuarat</th>
            <th class="text-right" style="width:70px;">Kapasiti</th>
            <th class="text-right" style="width:130px;">Tempahan Diluluskan</th>
            <th class="text-right" style="width:110px;">Kadar Penggunaan</th>
        </tr>
    </thead>
    <tbody>
    @foreach($bilik as $b)
        <tr>
            <td>{{ $b['nama'] }}</td>
            <td class="text-right">{{ $b['kapasiti'] }} pax</td>
            <td class="text-right">{{ $b['jumlah_tempahan'] }}</td>
            <td class="text-right">{{ $b['peratusan'] }}%</td>
        </tr>
    @endforeach
    </tbody>
</table>

{{-- ── Top 10 Pemohon ──────────────────────────────────────────────── --}}
<h2>Top 10 Pemohon Terbanyak</h2>
<table>
    <thead>
        <tr>
            <th style="width:28px;">#</th>
            <th>Nama Pemohon</th>
            <th>Unit</th>
            <th class="text-right" style="width:80px;">Permohonan</th>
            <th class="text-right" style="width:80px;">Diluluskan</th>
            <th class="text-right" style="width:65px;">Ditolak</th>
        </tr>
    </thead>
    <tbody>
    @foreach($top10Pengguna as $i => $p)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $p->name }}</td>
            <td>{{ $p->jabatan }}</td>
            <td class="text-right fw-bold">{{ $p->jumlah }}</td>
            <td class="text-right" style="color:#15803d;">{{ $p->jumlah_diluluskan }}</td>
            <td class="text-right" style="color:#dc2626;">{{ $p->jumlah_ditolak }}</td>
        </tr>
    @endforeach
    @if($top10Pengguna->isEmpty())
        <tr><td colspan="6" class="text-center" style="color:#9ca3af; padding:12px;">Tiada data</td></tr>
    @endif
    </tbody>
</table>

</div>{{-- .body --}}

{{-- ── Footer ──────────────────────────────────────────────────────── --}}
<div class="footer">
    Dokumen ini dijana secara automatik oleh sistem iBook 2.0
    &bull; BPTM, Akaun Negara Malaysia
    &bull; SULIT — Untuk Kegunaan Dalaman Sahaja
</div>

</body>
</html>
