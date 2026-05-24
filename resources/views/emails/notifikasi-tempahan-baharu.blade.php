<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Notifikasi Tempahan Baru</title>
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

          {{-- Alert badge --}}
          <div style="text-align:center;margin-bottom:24px;">
            @if($berulang)
            <span style="display:inline-block;background:#ede9fe;color:#6d28d9;font-size:13px;font-weight:700;padding:7px 20px;border-radius:100px;">
              &#128260;&nbsp; Tempahan Berulang Baru Diterima
            </span>
            @else
            <span style="display:inline-block;background:#dbeafe;color:#1d4ed8;font-size:13px;font-weight:700;padding:7px 20px;border-radius:100px;">
              &#128276;&nbsp; Tempahan Baru Diterima
            </span>
            @endif
          </div>

          <p style="margin:0 0 6px;font-size:15px;">Salam <strong>Urus Setia</strong>,</p>
          <p style="margin:0 0 24px;font-size:14px;color:#555;line-height:1.6;">
            @if($berulang)
            Terdapat <strong>tempahan berulang baru</strong> yang telah didaftarkan dalam sistem iBook 2.0
            dengan <strong>{{ $jumlahSesi }} sesi</strong> dijadualkan.
            @else
            Terdapat <strong>tempahan baru</strong> yang telah didaftarkan dalam sistem iBook 2.0.
            @endif
          </p>

          {{-- Details table --}}
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;font-size:14px;">
            <tr style="background:#f8fafc;">
              <td colspan="2" style="padding:12px 16px;border-bottom:2px solid #e5e7eb;">
                <span style="color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">No. Rujukan</span><br>
                <strong style="font-size:16px;color:#1A1A2E;">{{ $noRujukan }}</strong>
              </td>
            </tr>
            <tr>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;width:40%;vertical-align:top;">Nama Mesyuarat</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;font-weight:600;color:#111827;">{{ $namaMesyuarat }}</td>
            </tr>
            <tr style="background:#fafafa;">
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Tarikh</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">{{ $tarikhLabel }}</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Sesi</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">
                @foreach($semuaSesi as $sesi)
                  <span style="display:inline-block;background:#f3f4f6;color:#374151;font-size:12px;font-weight:600;padding:2px 10px;border-radius:4px;margin-right:4px;margin-bottom:2px;">{{ $sesi === 'pagi' ? 'Pagi' : 'Petang' }}</span>
                @endforeach
                @if($berulang)
                  <span style="font-size:12px;color:#6b7280;">({{ $jumlahSesi }} sesi jumlah)</span>
                @endif
              </td>
            </tr>
            <tr style="background:#fafafa;">
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Bilik Mesyuarat</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">{{ $bilikNama }}</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Pemohon</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">{{ $pemohonNama }}</td>
            </tr>
            @if($pemohonJabatan)
            <tr style="background:#fafafa;">
              <td style="padding:10px 16px;color:#6b7280;vertical-align:top;">Jabatan / Unit</td>
              <td style="padding:10px 16px;color:#111827;">{{ $pemohonJabatan }}</td>
            </tr>
            @endif
          </table>

          <p style="margin:20px 0 0;font-size:12px;color:#9ca3af;line-height:1.5;">
            E-mel ini dijana secara automatik oleh sistem <strong>iBook 2.0</strong> apabila tempahan baru didaftarkan.
            Log masuk ke sistem untuk melihat butiran penuh.
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
