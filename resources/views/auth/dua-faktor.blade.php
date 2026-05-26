<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengesahan Dua Faktor — iBook 2.0</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <style>
        body { font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; background: #0f172a; }
        *:focus-visible { outline: 3px solid #f59e0b; outline-offset: 2px; }

        /* OTP input boxes */
        .otp-box {
            width: 52px; height: 62px; border: 2px solid #374151;
            border-radius: 10px; background: #1e293b; color: #f1f5f9;
            font-size: 26px; font-weight: 700; text-align: center;
            caret-color: #f59e0b; transition: border-color .15s, box-shadow .15s;
            -moz-appearance: textfield; /* Firefox */
        }
        .otp-box::-webkit-inner-spin-button,
        .otp-box::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        .otp-box:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245,158,11,.25);
            outline: none;
        }
        .otp-box.error { border-color: #dc2626; background: #1f1016; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#f59e0b">
                <i class="fa-solid fa-shield-halved text-slate-900 text-lg" aria-hidden="true"></i>
            </div>
            <span class="text-2xl font-extrabold text-white tracking-tight">iBook 2.0</span>
        </div>
    </div>

    {{-- Card --}}
    <div class="bg-slate-800 rounded-2xl shadow-2xl p-8 border border-slate-700">

        {{-- Heading --}}
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-amber-500/10 border-2 border-amber-500/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-envelope-circle-check text-amber-400 text-2xl" aria-hidden="true"></i>
            </div>
            <h1 class="text-xl font-bold text-white">Pengesahan Dua Faktor</h1>
            <p class="text-slate-400 text-sm mt-2 leading-relaxed">
                Kod pengesahan 6 digit telah dihantar ke emel<br>
                <strong class="text-amber-400 font-mono">{{ $emailSembunyi }}</strong>
            </p>
        </div>

        {{-- Alerts --}}
        @if(session('success_otp'))
        <div class="mb-4 flex items-center gap-2 bg-green-900/40 border border-green-700 rounded-lg px-4 py-3 text-green-300 text-sm" role="alert">
            <i class="fa-solid fa-circle-check flex-shrink-0" aria-hidden="true"></i>
            {{ session('success_otp') }}
        </div>
        @endif

        @if($errors->has('kod'))
        <div class="mb-4 flex items-center gap-2 bg-red-900/40 border border-red-700 rounded-lg px-4 py-3 text-red-300 text-sm" role="alert" aria-live="assertive">
            <i class="fa-solid fa-circle-xmark flex-shrink-0" aria-hidden="true"></i>
            {{ $errors->first('kod') }}
        </div>
        @endif

        {{-- OTP Form --}}
        <form method="POST" action="{{ route('dua-faktor.verify') }}" id="form-otp" novalidate>
            @csrf

            {{-- Hidden field yang dikemas kini oleh JS --}}
            <input type="hidden" name="kod" id="input-kod">

            {{-- 6 kotak OTP --}}
            <div class="flex justify-center gap-3 mb-6" role="group" aria-label="Kod pengesahan 6 digit">
                @for($i = 0; $i < 6; $i++)
                <input type="number" inputmode="numeric" pattern="\d*"
                    class="otp-box {{ $errors->has('kod') ? 'error' : '' }}"
                    id="otp-{{ $i }}"
                    maxlength="1"
                    min="0" max="9"
                    autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                    aria-label="Digit {{ $i + 1 }} dari 6"
                    {{ $i === 0 ? 'autofocus' : '' }}>
                @endfor
            </div>

            {{-- Timer countdown --}}
            <p id="timer-otp" class="text-center text-xs text-slate-500 mb-5" aria-live="polite"></p>

            {{-- Butang sahkan --}}
            <button type="submit" id="btn-sahkan"
                class="w-full py-3 rounded-xl font-bold text-slate-900 text-sm transition-all"
                style="background:#f59e0b"
                onmouseover="this.style.background='#d97706'"
                onmouseout="this.style.background='#f59e0b'">
                <i class="fa-solid fa-check mr-2" aria-hidden="true"></i> Sahkan Kod
            </button>
        </form>

        {{-- Hantar semula --}}
        <div class="mt-5 text-center">
            <p class="text-slate-500 text-xs mb-2">Tidak terima kod?</p>
            <form method="POST" action="{{ route('dua-faktor.hantar-semula') }}" id="form-resend">
                @csrf
                <button type="submit" id="btn-resend"
                    class="text-amber-400 hover:text-amber-300 text-sm font-semibold underline underline-offset-2 transition-colors">
                    <i class="fa-solid fa-paper-plane mr-1" aria-hidden="true"></i> Hantar Semula Kod
                </button>
            </form>
        </div>

        {{-- Kembali ke log masuk --}}
        <div class="mt-6 pt-5 border-t border-slate-700 text-center">
            <a href="{{ route('login') }}" class="text-slate-400 hover:text-slate-300 text-xs transition-colors">
                <i class="fa-solid fa-arrow-left mr-1" aria-hidden="true"></i> Kembali ke Log Masuk
            </a>
        </div>

    </div>

    <p class="text-center text-slate-600 text-xs mt-6">
        iBook 2.0 &mdash; {{ now()->year }}
    </p>
</div>

<script>
// ── OTP input: auto-advance, paste, backspace ─────────────────────────
(function () {
    const boxes = Array.from({ length: 6 }, (_, i) => document.getElementById('otp-' + i));
    const hiddenInput = document.getElementById('input-kod');
    const form        = document.getElementById('form-otp');

    function syncKod() {
        hiddenInput.value = boxes.map(b => b.value || '').join('');
    }

    boxes.forEach(function(box, idx) {
        // Hanyas allow digit
        box.addEventListener('input', function () {
            // Ambil aksara terakhir sahaja jika lebih dari satu
            const val = this.value.replace(/\D/g, '').slice(-1);
            this.value = val;
            syncKod();
            if (val && idx < 5) boxes[idx + 1].focus();
        });

        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace') {
                if (!this.value && idx > 0) {
                    boxes[idx - 1].value = '';
                    boxes[idx - 1].focus();
                } else {
                    this.value = '';
                }
                syncKod();
                e.preventDefault();
            } else if (e.key === 'ArrowLeft' && idx > 0) {
                boxes[idx - 1].focus();
            } else if (e.key === 'ArrowRight' && idx < 5) {
                boxes[idx + 1].focus();
            }
        });

        // Paste support: paste 6 digits from clipboard
        box.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData)
                .getData('text').replace(/\D/g, '').slice(0, 6);
            pasted.split('').forEach(function(ch, i) {
                if (boxes[i]) boxes[i].value = ch;
            });
            syncKod();
            const nextIdx = Math.min(pasted.length, 5);
            boxes[nextIdx].focus();
        });
    });

    // Submit bila semua 6 digit diisi
    boxes[5].addEventListener('input', function () {
        const all = boxes.every(b => b.value.length === 1);
        if (all) {
            syncKod();
            // Delay kecil supaya digit kelihatan sebelum submit
            setTimeout(function() { form.submit(); }, 200);
        }
    });

    // Butang sahkan: update hidden input sebelum submit
    form.addEventListener('submit', function () {
        syncKod();
    });

    // ── Countdown timer 10 minit ──────────────────────────────────────
    const timerEl = document.getElementById('timer-otp');
    let saat = 600; // 10 minit
    function kemaskiniTimer() {
        if (saat <= 0) {
            timerEl.textContent = 'Kod telah tamat tempoh. Minta kod baru.';
            timerEl.style.color = '#f87171';
            return;
        }
        const m = Math.floor(saat / 60).toString().padStart(2, '0');
        const s = (saat % 60).toString().padStart(2, '0');
        timerEl.textContent = 'Kod tamat dalam ' + m + ':' + s;
        timerEl.style.color = saat <= 60 ? '#f59e0b' : '#64748b';
        saat--;
        setTimeout(kemaskiniTimer, 1000);
    }
    kemaskiniTimer();

    // ── Throttle resend button ──────────────────────────────────────
    const btnResend = document.getElementById('btn-resend');
    const formResend = document.getElementById('form-resend');
    if (formResend && btnResend) {
        formResend.addEventListener('submit', function() {
            btnResend.disabled = true;
            btnResend.textContent = 'Menghantar...';
            setTimeout(function() {
                btnResend.disabled = false;
                btnResend.innerHTML = '<i class="fa-solid fa-paper-plane mr-1"></i> Hantar Semula Kod';
            }, 61000);
        });
    }
})();
</script>

</body>
</html>
