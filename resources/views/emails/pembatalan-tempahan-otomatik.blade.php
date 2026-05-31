<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Pembatalan Tempahan Automatik</title>
</head>
<body style="margin:0;padding:0;background:#f0f2f5;font-family:Arial,Helvetica,sans-serif;color:#333;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f2f5;padding:32px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);max-width:600px;">

      {{-- Header --}}
      <tr>
        <td style="background:#1A1A2E;padding:28px 32px;text-align:center;">
          <div style="color:#f59e0b;font-size:20px;font-weight:bold;letter-spacing:1px;">iBook 2.0</div>
          <div style="color:#94a3b8;font-size:12px;margin-top:4px;">{{ $tetapan['nama_jabatan'] ?? 'Sistem Tempahan Bilik Mesyuarat' }}</div>
        </td>
      </tr>

      {{-- Body --}}
      <tr>
        <td style="padding:32px;">

          {{-- Status badge --}}
          <div style="text-align:center;margin-bottom:24px;">
            <span style="display:inline-block;background:#fee2e2;color:#b91c1c;font-size:13px;font-weight:700;padding:7px 20px;border-radius:100px;letter-spacing:0.3px;">
              &#9888;&nbsp; Tempahan Dibatalkan Secara Automatik
            </span>
          </div>

          <p style="margin:0 0 6px;font-size:15px;">Salam <strong>{{ $pemohonNama }}</strong>,</p>
          <p style="margin:0 0 16px;font-size:14px;color:#555;line-height:1.6;">
            Kami ingin memaklumkan bahawa
            @if($tempahanDibatal->count() > 1)
              <strong>{{ $tempahanDibatal->count() }} tempahan</strong> anda telah
            @else
              tempahan anda telah
            @endif
            dibatalkan secara automatik oleh sistem kerana <strong>Bahagian [{{ $bahagianKod }}]</strong>
            telah dinyahaktifkan pada <strong>{{ $tarikhBatal }}</strong>.
          </p>

          {{-- Alert box --}}
          <div style="margin-bottom:24px;padding:14px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;">
            <p style="margin:0;font-size:13px;color:#991b1b;line-height:1.5;">
              <strong>&#9432; Nota:</strong>
              Pembatalan ini adalah automatik dan bukan disebabkan oleh kesalahan anda.
              Sila hubungi Urus Setia untuk membuat tempahan semula di bilik lain jika perlu.
            </p>
          </div>

          {{-- Senarai tempahan dibatalkan --}}
          <p style="margin:0 0 10px;font-size:13px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px;">
            Butiran Tempahan Dibatalkan
          </p>
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;font-size:14px;">
            <tr style="background:#fef2f2;">
              <th style="padding:10px 12px;text-align:left;color:#6b7280;font-weight:600;font-size:12px;border-bottom:2px solid #fecaca;">Nama Mesyuarat</th>
              <th style="padding:10px 12px;text-align:left;color:#6b7280;font-weight:600;font-size:12px;border-bottom:2px solid #fecaca;">Tarikh</th>
              <th style="padding:10px 12px;text-align:left;color:#6b7280;font-weight:600;font-size:12px;border-bottom:2px solid #fecaca;">Sesi</th>
              <th style="padding:10px 12px;text-align:left;color:#6b7280;font-weight:600;font-size:12px;border-bottom:2px solid #fecaca;">Bilik</th>
            </tr>
            @foreach($tempahanDibatal as $i => $t)
            <tr style="{{ $i % 2 === 0 ? 'background:#ffffff;' : 'background:#fafafa;' }}">
              <td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;color:#111827;font-weight:600;">{{ $t->nama_mesyuarat }}</td>
              <td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;color:#374151;white-space:nowrap;">
                {{ \Carbon\Carbon::parse($t->tarikh)->locale('ms')->isoFormat('D MMM YYYY') }}
              </td>
              <td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;">
                @if($t->sesi === 'pagi')
                  <span style="display:inline-block;background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:600;padding:2px 8px;border-radius:4px;">Pagi</span>
                @else
                  <span style="display:inline-block;background:#fef3c7;color:#b45309;font-size:11px;font-weight:600;padding:2px 8px;border-radius:4px;">Petang</span>
                @endif
              </td>
              <td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;color:#374151;">{{ $t->bilik->nama ?? '-' }}</td>
            </tr>
            @endforeach
          </table>

          <p style="margin:20px 0 0;font-size:14px;color:#555;line-height:1.6;">
            Untuk membuat tempahan baharu atau memerlukan sebarang bantuan, sila log masuk ke sistem
            <strong>iBook 2.0</strong> atau hubungi Urus Setia terus.
          </p>

          <p style="margin:20px 0 0;font-size:12px;color:#9ca3af;line-height:1.5;">
            E-mel ini dijana secara automatik oleh sistem <strong>iBook 2.0</strong>.
            Sila jangan balas e-mel ini. Untuk pertanyaan, hubungi Urus Setia di
            <a href="mailto:{{ $tetapan['emel_pentadbir'] ?? '' }}" style="color:#f59e0b;text-decoration:none;">{{ $tetapan['emel_pentadbir'] ?? 'urus setia' }}</a>.
          </p>

        </td>
      </tr>

      {{-- Footer --}}
      <tr>
        <td style="background:#f8fafc;padding:16px 32px;border-top:1px solid #e5e7eb;text-align:center;">
          <p style="margin:0;font-size:11px;color:#9ca3af;">
            {{ $tetapan['nama_sistem'] ?? 'iBook 2.0' }} &mdash; {{ $tetapan['nama_jabatan'] ?? '' }}
          </p>
          <p style="margin:4px 0 0;font-size:11px;color:#d1d5db;">E-mel ini dijana secara automatik. Sila jangan balas.</p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>

</body>
</html>
