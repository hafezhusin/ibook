<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
    h1 { color: #1a1a2e; font-size: 18px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th { background: #1a1a2e; color: #fff; padding: 8px 10px; text-align: left; font-size: 11px; }
    td { padding: 7px 10px; border-bottom: 1px solid #eee; font-size: 11px; }
    .badge-lulus { color: #065f46; font-weight: bold; }
    .badge-tolak { color: #991b1b; font-weight: bold; }
    .header { border-bottom: 2px solid #f59e0b; padding-bottom: 10px; margin-bottom: 15px; }
</style>
</head>
<body>
<div class="header">
    <h1>iBook 2.0 - Senarai Tempahan Bilik Mesyuarat</h1>
    <p>Tarikh Cetak: {{ now()->format('d/m/Y H:i') }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nama Mesyuarat</th>
            <th>Tarikh</th>
            <th>Masa</th>
            <th>Bilik</th>
            <th>Pemohon</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($tempahan as $i => $t)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $t->nama_mesyuarat }}</td>
            <td>{{ $t->tarikh->format('d/m/Y') }}</td>
            <td>{{ $t->masa_label }}</td>
            <td>{{ $t->bilik->nama ?? '-' }}</td>
            <td>{{ $t->pengguna->name ?? '-' }}</td>
            <td class="badge-{{ $t->status === 'diluluskan' ? 'lulus' : 'tolak' }}">
                {{ ucfirst($t->status) }}
            </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;">Tiada rekod</td></tr>
        @endforelse
    </tbody>
</table>

<p style="margin-top:20px; text-align:center; color:#999; font-size:10px;">
    iBook 2.0 &copy; {{ date('Y') }}
</p>
</body>
</html>
