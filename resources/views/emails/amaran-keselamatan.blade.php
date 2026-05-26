<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Amaran Keselamatan</title>
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

      {{-- Alert banner --}}
      <tr>
        <td style="background:#fef2f2;border-bottom:3px solid #dc2626;padding:18px 32px;text-align:center;">
          <span style="display:inline-block;background:#fee2e2;color:#991b1b;font-size:14px;font-weight:700;padding:8px 24px;border-radius:100px;border:1px solid #fca5a5;">
            &#9888;&nbsp; Amaran Keselamatan — Aktiviti Mencurigai
          </span>
        </td>
      </tr>

      {{-- Body --}}
      <tr>
        <td style="padding:32px;">

          <p style="margin:0 0 6px;font-size:15px;">Salam <strong>Pentadbir Sistem</strong>,</p>
          <p style="margin:0 0 24px;font-size:14px;color:#555;line-height:1.6;">
            Sistem iBook 2.0 telah mengesan <strong>{{ $kiraan }} percubaan log masuk yang gagal</strong>
            dari alamat IP yang sama dalam tempoh <strong>1 jam</strong> yang lepas.
            Ini mungkin merupakan percubaan <em>brute force</em> atau serangan kamus kata laluan.
          </p>

          {{-- Details --}}
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #fee2e2;border-radius:8px;overflow:hidden;font-size:14px;margin-bottom:24px;">
            <tr style="background:#fef2f2;">
              <td colspan="2" style="padding:12px 16px;border-bottom:2px solid #fca5a5;">
                <span style="color:#9ca3af;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Maklumat Ancaman</span>
              </td>
            </tr>
            <tr>
              <td style="padding:10px 16px;border-bottom:1px solid #fef2f2;color:#6b7280;width:45%;vertical-align:top;">Alamat IP</td>
              <td style="padding:10px 16px;border-bottom:1px solid #fef2f2;font-weight:700;color:#991b1b;font-family:monospace;">{{ $ip }}</td>
            </tr>
            <tr style="background:#fef9f9;">
              <td style="padding:10px 16px;border-bottom:1px solid #fef2f2;color:#6b7280;vertical-align:top;">Bilangan Percubaan</td>
              <td style="padding:10px 16px;border-bottom:1px solid #fef2f2;font-weight:700;color:#dc2626;">{{ $kiraan }} percubaan dalam 1 jam</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;border-bottom:1px solid #fef2f2;color:#6b7280;vertical-align:top;">Masa Dikesan</td>
              <td style="padding:10px 16px;border-bottom:1px solid #fef2f2;color:#374151;">{{ now()->locale('ms')->isoFormat('dddd, D MMMM YYYY, HH:mm:ss') }}</td>
            </tr>
            @if(!empty($emelDicuba))
            <tr style="background:#fef9f9;">
              <td style="padding:10px 16px;color:#6b7280;vertical-align:top;">Emel Yang Dicuba</td>
              <td style="padding:10px 16px;color:#374151;">
                @foreach($emelDicuba as $em)
                <span style="display:inline-block;background:#fee2e2;color:#991b1b;font-size:12px;padding:2px 8px;border-radius:4px;margin:2px 2px 2px 0;font-family:monospace;">{{ $em }}</span>
                @endforeach
              </td>
            </tr>
            @endif
          </table>

          {{-- Action needed --}}
          <div style="background:#fff7ed;border:1px solid #fdba74;border-radius:8px;padding:16px 20px;margin-bottom:24px;">
            <p style="margin:0 0 8px;font-weight:700;color:#c2410c;font-size:13px;">&#128270; Tindakan Disyorkan:</p>
            <ul style="margin:0;padding-left:20px;font-size:13px;color:#7c3aed;line-height:1.8;">
              <li style="color:#374151;">Semak Log Audit untuk melihat butiran lengkap percubaan ini</li>
              <li style="color:#374151;">Blok IP <code style="background:#f3f4f6;padding:1px 5px;border-radius:3px;">{{ $ip }}</code> jika disyaki serangan</li>
              <li style="color:#374151;">Semak sama ada mana-mana akaun telah terjejas</li>
              <li style="color:#374151;">Pastikan semua pengguna menggunakan kata laluan yang kukuh</li>
            </ul>
          </div>

          {{-- CTA --}}
          <div style="text-align:center;margin-bottom:8px;">
            <a href="{{ rtrim(config('app.url'), '/') }}/log-audit?tindakan=log_masuk_gagal"
               style="display:inline-block;background:#dc2626;color:#fff;font-weight:700;font-size:14px;padding:12px 32px;border-radius:8px;text-decoration:none;">
              &#128274; Lihat Log Audit Sekarang
            </a>
          </div>

        </td>
      </tr>

      {{-- Footer --}}
      <tr>
        <td style="background:#f8fafc;border-top:1px solid #e5e7eb;padding:20px 32px;text-align:center;">
          <p style="margin:0;font-size:12px;color:#9ca3af;">
            E-mel ini dihantar secara automatik oleh <strong>iBook 2.0</strong> &mdash; {{ $tetapan['nama_jabatan'] ?? '' }}<br>
            Amaran ini tidak memerlukan balasan. Log masuk ke sistem untuk mengambil tindakan.
          </p>
          <p style="margin:8px 0 0;font-size:11px;color:#d1d5db;">
            Satu amaran sahaja dihantar per IP per jam untuk mengelak banjir e-mel.
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>

</body>
</html>
