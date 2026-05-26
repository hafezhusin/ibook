<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Pendaftaran Baharu SSO</title>
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

      {{-- Alert Banner --}}
      <tr>
        <td style="background:#0ea5e9;padding:14px 32px;text-align:center;">
          <span style="color:#fff;font-weight:bold;font-size:14px;">👤 PENDAFTARAN BAHARU — MENUNGGU KELULUSAN</span>
        </td>
      </tr>

      {{-- Body --}}
      <tr>
        <td style="padding:32px;">
          <p style="margin:0 0 16px;font-size:15px;">Salam pentadbir,</p>
          <p style="margin:0 0 20px;font-size:15px;">
            Seorang pengguna baharu telah mendaftar melalui <strong>Google Workspace SSO</strong> dan sedang menunggu kelulusan anda:
          </p>

          <table width="100%" cellpadding="10" cellspacing="0" style="background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:24px;">
            <tr>
              <td style="font-size:13px;color:#64748b;width:140px;">Nama</td>
              <td style="font-size:14px;font-weight:bold;color:#1e293b;">{{ $namaPengguna }}</td>
            </tr>
            <tr style="border-top:1px solid #e2e8f0;">
              <td style="font-size:13px;color:#64748b;">Emel</td>
              <td style="font-size:14px;color:#1e293b;">{{ $emelPengguna }}</td>
            </tr>
            <tr style="border-top:1px solid #e2e8f0;">
              <td style="font-size:13px;color:#64748b;">Masa Daftar</td>
              <td style="font-size:14px;color:#1e293b;">{{ now()->setTimezone('Asia/Kuala_Lumpur')->format('d M Y, h:i A') }}</td>
            </tr>
            <tr style="border-top:1px solid #e2e8f0;">
              <td style="font-size:13px;color:#64748b;">Status</td>
              <td style="font-size:14px;"><span style="background:#fef9c3;color:#854d0e;padding:2px 10px;border-radius:12px;font-size:12px;font-weight:bold;">Menunggu Kelulusan</span></td>
            </tr>
          </table>

          <p style="margin:0 0 20px;font-size:14px;color:#475569;">
            Jika pengguna ini adalah warga BPTM, sila log masuk ke sistem dan aktifkan akaun mereka melalui modul <strong>Pengurusan Pengguna</strong>.
          </p>

          <div style="text-align:center;margin:24px 0;">
            <a href="{{ config('app.url') }}/pengguna"
               style="background:#f59e0b;color:#1a1a2e;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:bold;font-size:14px;display:inline-block;">
              Urus Pengguna Sekarang →
            </a>
          </div>

          <p style="margin:16px 0 0;font-size:13px;color:#94a3b8;border-top:1px solid #f1f5f9;padding-top:16px;">
            Jika pengguna ini <strong>bukan</strong> warga BPTM, abaikan sahaja e-mel ini. Akaun mereka akan kekal tidak aktif.
          </p>
        </td>
      </tr>

      {{-- Footer --}}
      <tr>
        <td style="background:#f8fafc;padding:20px 32px;text-align:center;border-top:1px solid #e2e8f0;">
          <p style="margin:0;font-size:12px;color:#94a3b8;">
            {{ $tetapan['nama_jabatan'] ?? 'Bahagian Pengurusan Teknologi Maklumat' }} &copy; {{ date('Y') }}
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
