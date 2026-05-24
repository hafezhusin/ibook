<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Pengesahan Tempahan</title>
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
            <span style="display:inline-block;background:#dcfce7;color:#15803d;font-size:13px;font-weight:700;padding:7px 20px;border-radius:100px;letter-spacing:0.3px;">
              &#10003;&nbsp; Tempahan Berjaya Didaftarkan
            </span>
          </div>

          <p style="margin:0 0 6px;font-size:15px;">Salam <strong>{{ $pemohonNama }}</strong>,</p>
          <p style="margin:0 0 24px;font-size:14px;color:#555;line-height:1.6;">
            Tempahan bilik mesyuarat anda telah berjaya didaftarkan dalam sistem <strong>iBook 2.0</strong>.
            Berikut adalah butiran tempahan anda untuk simpanan rekod.
          </p>

          {{-- Details table --}}
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;font-size:14px;">
            <tr style="background:#f8fafc;">
              <td colspan="2" style="padding:12px 16px;border-bottom:2px solid #e5e7eb;">
                <span style="color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">No. Rujukan</span><br>
                <strong style="font-size:16px;color:#1A1A2E;letter-spacing:0.5px;">{{ $noRujukan }}</strong>
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
                  @if($sesi === 'pagi')
                    <span style="display:inline-block;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:600;padding:2px 10px;border-radius:4px;margin-right:4px;">Pagi (9:00 AM – 1:00 PM)</span>
                  @else
                    <span style="display:inline-block;background:#fef3c7;color:#b45309;font-size:12px;font-weight:600;padding:2px 10px;border-radius:4px;margin-right:4px;">Petang (2:00 PM – 6:00 PM)</span>
                  @endif
                @endforeach
              </td>
            </tr>
            <tr style="background:#fafafa;">
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Bilik Mesyuarat</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">{{ $bilikNama }}</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Bilangan Peserta</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">{{ $bilanganPeserta }} orang</td>
            </tr>
            <tr style="background:#fafafa;">
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Kategori</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">{{ $kategoriLabel }}</td>
            </tr>
            <tr>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#6b7280;vertical-align:top;">Pengerusi</td>
              <td style="padding:10px 16px;border-bottom:1px solid #f3f4f6;color:#111827;">{{ $namaPengerusi }}</td>
            </tr>
            <tr style="background:#fafafa;">
              <td style="padding:10px 16px;color:#6b7280;vertical-align:top;">Status</td>
              <td style="padding:10px 16px;font-weight:700;color:#15803d;">&#10003; Diluluskan</td>
            </tr>
          </table>

          @if($tujuan)
          <p style="margin:16px 0 0;font-size:13px;color:#6b7280;line-height:1.5;">
            <strong style="color:#374151;">Tujuan:</strong> {{ $tujuan }}
          </p>
          @endif

          <div style="margin-top:24px;padding:14px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;">
            <p style="margin:0;font-size:13px;color:#92400e;line-height:1.5;">
              <strong>&#9888; Peringatan:</strong>
              Tempahan ini adalah tertakluk kepada ketersediaan bilik. Sila pastikan bilik digunakan mengikut tempoh yang ditetapkan.
              Sebarang pembatalan perlu dibuat melalui sistem iBook 2.0.
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
