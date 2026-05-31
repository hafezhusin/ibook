<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Timeline Aktiviti — {{ $pengguna->name }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 9pt;
        color: #1a1a2e;
        background: #ffffff;
    }

    /* ── Header ── */
    .header {
        background: #1a1a2e;
        color: #ffffff;
        padding: 14px 20px;
        margin-bottom: 0;
    }
    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }
    .system-name {
        font-size: 13pt;
        font-weight: bold;
        color: #f59e0b;
        letter-spacing: 0.5px;
    }
    .system-sub {
        font-size: 7.5pt;
        color: #94a3b8;
        margin-top: 2px;
    }
    .report-title {
        font-size: 9pt;
        color: #cbd5e1;
        text-align: right;
    }
    .report-title strong {
        display: block;
        font-size: 11pt;
        color: #f59e0b;
        margin-bottom: 2px;
    }
    .header-divider {
        border-top: 1px solid #334155;
        margin: 10px 0;
    }
    .user-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .user-name {
        font-size: 12pt;
        font-weight: bold;
        color: #ffffff;
    }
    .user-meta {
        font-size: 8pt;
        color: #94a3b8;
        margin-top: 2px;
    }
    .badges {
        text-align: right;
    }
    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 7.5pt;
        font-weight: bold;
        margin-left: 4px;
    }
    .badge-aktif   { background: #d1fae5; color: #065f46; }
    .badge-nyahaktif { background: #fee2e2; color: #991b1b; }
    .badge-peranan { background: #e2e8f0; color: #334155; }

    /* ── Stat cards ── */
    .stats-row {
        display: flex;
        gap: 8px;
        padding: 12px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .stat-card {
        flex: 1;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 8px 10px;
        border-left: 3px solid #f59e0b;
    }
    .stat-card.blue  { border-left-color: #3b82f6; }
    .stat-card.green { border-left-color: #10b981; }
    .stat-card.red   { border-left-color: #ef4444; }
    .stat-label {
        font-size: 6.5pt;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .stat-value {
        font-size: 14pt;
        font-weight: bold;
        color: #1a1a2e;
        line-height: 1;
    }
    .stat-value.small {
        font-size: 9pt;
    }
    .stat-sub {
        font-size: 7pt;
        color: #94a3b8;
        margin-top: 2px;
    }

    /* ── Filter info ── */
    .filter-bar {
        padding: 6px 20px;
        background: #fffbeb;
        border-bottom: 1px solid #fde68a;
        font-size: 7.5pt;
        color: #92400e;
    }

    /* ── Content ── */
    .content {
        padding: 14px 20px;
    }

    /* ── Date group ── */
    .date-group {
        margin-bottom: 14px;
        page-break-inside: avoid;
    }
    .date-heading {
        background: #1a1a2e;
        color: #f59e0b;
        font-size: 8pt;
        font-weight: bold;
        padding: 4px 10px;
        border-radius: 4px;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ── Log table ── */
    .log-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
    }
    .log-table thead tr {
        background: #f1f5f9;
    }
    .log-table th {
        padding: 5px 8px;
        text-align: left;
        font-size: 7pt;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        border-bottom: 1px solid #e2e8f0;
        font-weight: bold;
    }
    .log-table td {
        padding: 5px 8px;
        vertical-align: top;
        border-bottom: 1px solid #f1f5f9;
    }
    .log-table tr:last-child td {
        border-bottom: none;
    }
    .log-table tr.bahaya {
        background: #fff5f5;
    }
    .log-table tr.bahaya td {
        border-bottom-color: #fee2e2;
    }

    .time-cell {
        font-family: 'DejaVu Sans Mono', monospace;
        font-size: 7.5pt;
        color: #64748b;
        white-space: nowrap;
        width: 52px;
    }
    .ip-cell {
        font-family: 'DejaVu Sans Mono', monospace;
        font-size: 7pt;
        color: #94a3b8;
        white-space: nowrap;
        width: 95px;
    }
    .desc-cell {
        color: #374151;
    }

    /* ── Action badges ── */
    .action-badge {
        display: inline-block;
        padding: 1px 5px;
        border-radius: 3px;
        font-size: 7pt;
        font-weight: bold;
        font-family: 'DejaVu Sans Mono', monospace;
        white-space: nowrap;
    }
    .ab-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .ab-login   { background: #d1fae5; color: #065f46; }
    .ab-logout  { background: #f1f5f9; color: #475569; }
    .ab-create  { background: #dcfce7; color: #166534; }
    .ab-update  { background: #dbeafe; color: #1e40af; }
    .ab-delete  { background: #fee2e2; color: #991b1b; }
    .ab-export  { background: #f3e8ff; color: #6b21a8; }
    .ab-deact   { background: #fff7ed; color: #9a3412; }
    .ab-act     { background: #ccfbf1; color: #115e59; }
    .ab-default { background: #f1f5f9; color: #475569; }

    /* ── Diff table ── */
    .diff-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 7pt;
        margin-top: 4px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
    }
    .diff-table th {
        padding: 3px 6px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 6.5pt;
        text-align: left;
        font-weight: bold;
    }
    .diff-table td {
        padding: 3px 6px;
        border-top: 1px solid #e2e8f0;
        font-family: 'DejaVu Sans Mono', monospace;
    }
    .diff-lama { background: #fff5f5; color: #991b1b; }
    .diff-baru { background: #f0fdf4; color: #166534; }
    .diff-field { color: #64748b; font-weight: bold; }

    /* ── Empty state ── */
    .empty {
        text-align: center;
        padding: 30px;
        color: #94a3b8;
        font-size: 9pt;
    }

    /* ── Footer ── */
    .footer {
        margin-top: 16px;
        padding-top: 8px;
        border-top: 1px solid #e2e8f0;
        font-size: 7pt;
        color: #94a3b8;
        display: flex;
        justify-content: space-between;
    }

    /* ── Page break ── */
    .page-break { page-break-after: always; }
</style>
</head>
<body>

{{-- ── Header ── --}}
<div class="header">
    <div class="header-top">
        <div>
            <div class="system-name">Sistem iBOOK v2 - BPTM</div>
            <div class="system-sub">Sistem Tempahan Bilik Mesyuarat</div>
        </div>
        <div class="report-title">
            <strong>Laporan Timeline Aktiviti</strong>
            Dijana: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
    <div class="header-divider"></div>
    <div class="user-info">
        <div>
            <div class="user-name">{{ $pengguna->name }}</div>
            <div class="user-meta">
                {{ $pengguna->email }}
                @if($pengguna->jabatan) &mdash; {{ $pengguna->jabatan }} @endif
            </div>
        </div>
        <div class="badges">
            <span class="badge {{ $pengguna->aktif ? 'badge-aktif' : 'badge-nyahaktif' }}">
                {{ $pengguna->aktif ? 'Aktif' : 'Tidak Aktif' }}
            </span>
            <span class="badge badge-peranan">
                {{ ucfirst(str_replace('_', ' ', $pengguna->peranan)) }}
            </span>
        </div>
    </div>
</div>

{{-- ── Stat cards ── --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-label">Jumlah Tindakan</div>
        <div class="stat-value">{{ number_format($jumlahKeseluruhan) }}</div>
        <div class="stat-sub">rekod keseluruhan</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Tindakan Terbanyak</div>
        @if($tindakanPopular)
        <div class="stat-value small">{{ $tindakanPopular->tindakan }}</div>
        <div class="stat-sub">{{ number_format($tindakanPopular->kiraan) }} kali</div>
        @else
        <div class="stat-value small">—</div>
        @endif
    </div>
    <div class="stat-card green">
        <div class="stat-label">Tindakan Terkini</div>
        @if($aktivitiTerkini)
        <div class="stat-value small">{{ \Carbon\Carbon::parse($aktivitiTerkini)->format('d/m/Y') }}</div>
        <div class="stat-sub">{{ \Carbon\Carbon::parse($aktivitiTerkini)->format('H:i:s') }}</div>
        @else
        <div class="stat-value small">—</div>
        @endif
    </div>
    <div class="stat-card {{ $jumlahKeselamatanGagal > 0 ? 'red' : '' }}">
        <div class="stat-label">Log Masuk Gagal</div>
        <div class="stat-value" style="{{ $jumlahKeselamatanGagal > 0 ? 'color:#dc2626' : '' }}">
            {{ $jumlahKeselamatanGagal }}
        </div>
        <div class="stat-sub">percubaan gagal</div>
    </div>
</div>

{{-- ── Filter info ── --}}
@php
    $tarikhDari   = request('tarikh_dari');
    $tarikhHingga = request('tarikh_hingga');
@endphp
@if($tarikhDari || $tarikhHingga)
<div class="filter-bar">
    <strong>Penapis tarikh:</strong>
    {{ $tarikhDari ? \Carbon\Carbon::parse($tarikhDari)->format('d/m/Y') : 'awal' }}
    &rarr;
    {{ $tarikhHingga ? \Carbon\Carbon::parse($tarikhHingga)->format('d/m/Y') : 'kini' }}
    &nbsp;&bull;&nbsp;
    {{ number_format($logs->count()) }} rekod dalam tempoh ini
</div>
@endif

{{-- ── Timeline content ── --}}
<div class="content">

@if($logs->isEmpty())
<div class="empty">Tiada rekod aktiviti untuk pengguna ini.</div>
@else

@foreach($logsByTarikh as $tarikh => $logsHariIni)
@php $tarikhObj = \Carbon\Carbon::parse($tarikh); @endphp

<div class="date-group">
    <div class="date-heading">
        {{ $tarikhObj->locale('ms')->isoFormat('dddd') }}, {{ $tarikhObj->format('d M Y') }}
        <span style="font-weight:normal; color:#94a3b8; font-size:7pt;">({{ count($logsHariIni) }} tindakan)</span>
    </div>

    <table class="log-table">
        <thead>
            <tr>
                <th style="width:52px;">Masa</th>
                <th style="width:130px;">Tindakan</th>
                <th>Penerangan</th>
                <th style="width:95px;">IP</th>
            </tr>
        </thead>
        <tbody>
        @foreach($logsHariIni as $log)
        @php
            $isBahaya = in_array($log->tindakan, ['log_masuk_gagal', 'percubaan_akaun_nyahaktif']);
            $badgeClass = match(true) {
                $log->tindakan === 'log_masuk_gagal'           => 'ab-danger',
                $log->tindakan === 'percubaan_akaun_nyahaktif' => 'ab-danger',
                $log->tindakan === 'log_masuk_berjaya'         => 'ab-login',
                str_contains($log->tindakan, 'log_masuk')      => 'ab-login',
                $log->tindakan === 'log_keluar'                => 'ab-logout',
                str_starts_with($log->tindakan, 'buat_')       => 'ab-create',
                str_starts_with($log->tindakan, 'kemaskini_')  => 'ab-update',
                str_starts_with($log->tindakan, 'padam_')      => 'ab-delete',
                str_starts_with($log->tindakan, 'eksport_')    => 'ab-export',
                str_contains($log->tindakan, 'nyahaktifkan')   => 'ab-deact',
                str_contains($log->tindakan, 'aktifkan')       => 'ab-act',
                default                                         => 'ab-default',
            };
            $hasPerubahan = !empty($log->butiran['perubahan']) && is_array($log->butiran['perubahan']);
        @endphp
        <tr class="{{ $isBahaya ? 'bahaya' : '' }}">
            <td class="time-cell">{{ $log->dicipta_pada->format('H:i:s') }}</td>
            <td>
                <span class="action-badge {{ $badgeClass }}">{{ $log->tindakan }}</span>
            </td>
            <td class="desc-cell">
                {{ $log->penerangan }}
                {{-- Diff sebelum/selepas jika ada --}}
                @if($hasPerubahan)
                <table class="diff-table" style="margin-top:4px;">
                    <thead>
                        <tr>
                            <th style="width:25%;">Medan</th>
                            <th style="width:37.5%;">Sebelum</th>
                            <th style="width:37.5%;">Selepas</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($log->butiran['perubahan'] as $medan => $nilai)
                    <tr>
                        <td class="diff-field">{{ $medan }}</td>
                        <td class="diff-lama">{{ $nilai['lama'] ?? '—' }}</td>
                        <td class="diff-baru">{{ $nilai['baru'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </td>
            <td class="ip-cell">{{ $log->ip_address ?? '—' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endforeach

@endif

{{-- ── Footer ── --}}
<div class="footer">
    <span>
        Dijana oleh: {{ auth()->user()->name }} &bull; {{ now()->format('d/m/Y H:i:s') }}
    </span>
    <span>
        Sistem iBOOK v2 &copy; {{ date('Y') }} Bahagian Pengurusan Teknologi Maklumat
    </span>
</div>

</div>
</body>
</html>
