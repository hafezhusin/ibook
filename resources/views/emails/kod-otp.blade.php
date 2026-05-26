<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Kod Pengesahan iBook</title>
</head>
<body style="margin:0;padding:0;background:#f0f2f5;font-family:Arial,Helvetica,sans-serif;color:#333;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f2f5;padding:32px 0;">
  <tr><td align="center">
    <table width="520" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,0.09);max-width:520px;">

      {{-- Header --}}
      <tr>
        <td style="background:#1A1A2E;padding:28px 32px;text-align:center;">
          <div style="color:#f59e0b;font-size:20px;font-weight:bold;letter-spacing:1px;">iBook 2.0</div>
          <div style="color:#94a3b8;font-size:12px;margin-top:4px;">{{ $tetapan['nama_jabatan'] ?? 'Sistem Tempahan Bilik Mesyuarat' }}</div>
        </td>
      </tr>

      {{-- Body --}}
      <tr>
        <td style="padding:36px 32px 28px;">

          <p style="margin:0 0 6px;font-size:15px;">Salam <strong>{{ $namaPengguna }}</strong>,</p>
          <p style="margin:0 0 28px;font-size:14px;color:#555;line-height:1.6;">
            Berikut adalah kod pengesahan log masuk anda untuk sistem iBook 2.0.
            Kod ini <strong>sah selama 10 minit</strong> dan hanya boleh digunakan sekali sahaja.
          </p>

          {{-- OTP display --}}
          <div style="text-align:center;margin:0 0 28px;">
            <div style="display:inline-block;background:#fef3c7;border:2px dashed #f59e0b;border-radius:12px;padding:20px 40px;">
              <div style="font-size:11px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:10px;">Kod Pengesahan</div>
              <div style="font-size:42px;font-weight:900;letter-spacing:10px;color:#1A1A2E;font-family:monospace;">{{ $otp }}</div>
              <div style="font-size:11px;color:#b45309;margin-top:10px;">Sah selama 10 minit</div>
            </div>
          </div>

          {{-- Warning --}}
          <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:14px 18px;margin-bottom:20px;">
            <p style="margin:0;font-size:13px;color:#7c2d12;line-height:1.6;">
              <strong>&#9888; Amaran Keselamatan:</strong> Jangan kongsikan kod ini dengan sesiapa.
              iBook tidak akan meminta kod anda melalui panggilan telefon atau sembang.
              Jika anda tidak membuat permintaan ini, abaikan emel ini.
            </p>
          </div>

        </td>
      </tr>

      {{-- Footer --}}
      <tr>
        <td style="background:#f8fafc;border-top:1px solid #e5e7eb;padding:18px 32px;text-align:center;">
          <p style="margin:0;font-size:12px;color:#9ca3af;">
            E-mel ini dihantar secara automatik oleh <strong>iBook 2.0</strong><br>
            {{ $tetapan['nama_jabatan'] ?? '' }}<br>
            <span style="font-size:11px;">Jangan balas emel ini.</span>
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>

</body>
</html>
