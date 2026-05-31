<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Tempahan Tidak Diluluskan</title>
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
              &#10007;&nbsp; Tempahan Tidak Diluluskan
            </span>
          </div>

          <p style="margin:0 0 6px;font-size:15px;">Salam <strong>{{ $tempahan->pengguna->name ?? 'Pemohon' }}</strong>,</p>
          <p style="margin:0 0 24px;font-size:14px;color:#555;line-height:1.6;">
            Mohon maaf, tempahan bilik mesyuarat anda <strong style="color:#b91c1c;">tidak diluluskan</strong> oleh
            <strong>{{ $penolak->name }}</strong>. Berikut adalah butiran tempahan yang dimaksudkan.
          </p>

          {{-- Details table --}}
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;font-size:14px;">
            <tr style="background:#f8fafc;">
              <td colspan="2" style="padding:12px 16px;border-bottom:2px solid #e5e7eb;">
                <span style="color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">No. Rujukan</span><br>
                <strong style="font-size:16px;color:#1A1A2E;letter-spacing:0.5px;">{{ $tempahan->no_rujukan }}</strong>
              </td>
            </tr>
            <tr>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;width:40%;vertical-align:top;">Nama Mesyuarat</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;font-weight:600;color:#111827;">{{ $tempahan->nama_mesyuarat }}</td>
            </tr>
            <tr style="background:#fafafa;">
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Tarikh</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">
                {{ $tempahan->tarikh->locale('ms')->isoFormat('dddd, D MMMM YYYY') }}
              </td>
            </tr>
            <tr>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Sesi</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">
                @if($tempahan->sesi === 'pagi')
                  <span style="display:inline-block;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:600;padding:2px 10px;border-radius:4px;">Pagi (9:00 AM – 1:00 PM)</span>
                @else
                  <span style="display:inline-block;background:#fef3c7;color:#b45309;font-size:12px;font-weight:600;padding:2px 10px;border-radius:4px;">Petang (2:00 PM – 6:00 PM)</span>
                @endif
              </td>
            </tr>
            <tr style="background:#fafafa;">
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Bilik Mesyuarat</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">{{ $tempahan->bilik->nama ?? '-' }}</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Bilangan Peserta</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">{{ $tempahan->bilangan_peserta }} orang</td>
            </tr>
            <tr style="background:#fafafa;">
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Kategori</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">{{ $tempahan->kategori_label }}</td>
            </tr>
            <tr style="background:#fee2e2;">
              <td style="padding:10px 16px;color:#6b7280;vertical-align:top;">Status</td>
              <td style="padding:10px 16px;font-weight:700;color:#b91c1c;">&#10007; Tidak Diluluskan</td>
            </tr>
          </table>

          {{-- Catatan penolakan --}}
          @if($catatan)
          <div style="margin-top:16px;padding:14px 16px;background:#fff1f2;border:1px solid #fecdd3;border-radius:8px;">
            <p style="margin:0 0 4px;font-size:12px;font-weight:700;color:#9f1239;text-transform:uppercase;letter-spacing:0.5px;">Sebab Penolakan</p>
            <p style="margin:0;font-size:14px;color:#be123c;line-height:1.5;">{{ $catatan }}</p>
          </div>
          @endif

          <div style="margin-top:20px;padding:14px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;">
            <p style="margin:0;font-size:13px;color:#92400e;line-height:1.5;">
              <strong>Langkah Seterusnya:</strong>
              Anda boleh membuat tempahan baru melalui sistem iBook 2.0 atau menghubungi Urus Setia untuk maklumat lanjut.
            </p>
          </div>

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
