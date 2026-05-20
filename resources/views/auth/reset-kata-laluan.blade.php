<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Semula Kata Laluan — iBook 2.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(160deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%); min-height: 100vh; }
        .form-input { width:100%; border:1.5px solid rgba(255,255,255,0.2); border-radius:8px; padding:11px 14px; font-size:14px; outline:none; transition:border .2s; background:rgba(255,255,255,0.08); color:white; }
        .form-input::placeholder { color: rgba(255,255,255,0.35); }
        .form-input:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.2); }
        .form-input:disabled { opacity: 0.45; cursor: not-allowed; }
        *:focus-visible { outline: 3px solid #f59e0b; outline-offset: 2px; }
    </style>
</head>
<body class="flex items-center justify-center p-6">
<div class="w-full max-w-sm">

    {{-- Logo --}}
    <div class="flex items-center gap-3 mb-8">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg" style="background:#f59e0b">
            <i class="fa-solid fa-book-open text-white" aria-hidden="true"></i>
        </div>
        <span class="text-white font-bold text-xl">iBook 2.0</span>
    </div>

    <h1 class="text-white text-2xl font-bold mb-1">Set Semula Kata Laluan</h1>
    <p class="text-slate-400 text-sm mb-7">Masukkan kata laluan baharu anda di bawah.</p>

    @if($errors->any())
    <div class="bg-red-900/40 border border-red-500/40 text-red-300 rounded-lg p-3 mb-5 text-sm flex items-center gap-2" role="alert">
        <i class="fa-solid fa-circle-xmark flex-shrink-0" aria-hidden="true"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Emel (readonly) --}}
        <div class="mb-4">
            <label for="email" class="block text-sm font-semibold text-slate-300 mb-2">
                <i class="fa-solid fa-envelope text-amber-400 mr-1" aria-hidden="true"></i> Emel
            </label>
            <input type="email" id="email" name="email"
                value="{{ old('email', $email) }}"
                required aria-required="true"
                autocomplete="email"
                readonly
                class="form-input"
                placeholder="nama@jabatan.gov.my">
        </div>

        {{-- Kata Laluan Baharu --}}
        <div class="mb-4">
            <label for="password" class="block text-sm font-semibold text-slate-300 mb-2">
                <i class="fa-solid fa-lock text-amber-400 mr-1" aria-hidden="true"></i> Kata Laluan Baharu
            </label>
            <div class="relative">
                <input type="password" id="password" name="password"
                    required aria-required="true"
                    autocomplete="new-password"
                    class="form-input pr-10"
                    placeholder="••••••••">
                <button type="button" id="btn-toggle-password"
                    aria-label="Tunjuk atau sembunyikan kata laluan baharu"
                    aria-pressed="false"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                    <i class="fa-solid fa-eye" id="eye-password" aria-hidden="true"></i>
                </button>
            </div>

            {{-- Meter kekuatan kata laluan --}}
            <div id="kekuatan-wrap" class="hidden mt-2 space-y-2">
                <div class="flex gap-1" aria-hidden="true">
                    <div class="h-1.5 flex-1 rounded-full bg-gray-600 overflow-hidden">
                        <div id="bar-1" class="h-full w-full rounded-full transition-colors duration-300" style="background:#4b5563"></div>
                    </div>
                    <div class="h-1.5 flex-1 rounded-full bg-gray-600 overflow-hidden">
                        <div id="bar-2" class="h-full w-full rounded-full transition-colors duration-300" style="background:#4b5563"></div>
                    </div>
                    <div class="h-1.5 flex-1 rounded-full bg-gray-600 overflow-hidden">
                        <div id="bar-3" class="h-full w-full rounded-full transition-colors duration-300" style="background:#4b5563"></div>
                    </div>
                    <div class="h-1.5 flex-1 rounded-full bg-gray-600 overflow-hidden">
                        <div id="bar-4" class="h-full w-full rounded-full transition-colors duration-300" style="background:#4b5563"></div>
                    </div>
                </div>
                <p id="label-kekuatan" class="text-xs font-semibold" aria-live="polite"></p>
                <ul class="space-y-0.5 text-xs" aria-label="Syarat kata laluan">
                    <li id="syarat-panjang" class="flex items-center gap-1.5 text-slate-400"><i class="fa-solid fa-circle w-2.5 text-[8px]"></i> Sekurang-kurangnya 8 aksara</li>
                    <li id="syarat-besar"   class="flex items-center gap-1.5 text-slate-400"><i class="fa-solid fa-circle w-2.5 text-[8px]"></i> Huruf besar (A–Z)</li>
                    <li id="syarat-kecil"   class="flex items-center gap-1.5 text-slate-400"><i class="fa-solid fa-circle w-2.5 text-[8px]"></i> Huruf kecil (a–z)</li>
                    <li id="syarat-nombor"  class="flex items-center gap-1.5 text-slate-400"><i class="fa-solid fa-circle w-2.5 text-[8px]"></i> Nombor (0–9)</li>
                    <li id="syarat-simbol"  class="flex items-center gap-1.5 text-slate-400"><i class="fa-solid fa-circle w-2.5 text-[8px]"></i> Simbol (!@#$...)</li>
                </ul>
            </div>
        </div>

        {{-- Sahkan Kata Laluan --}}
        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-300 mb-2">
                <i class="fa-solid fa-lock text-amber-400 mr-1" aria-hidden="true"></i> Sahkan Kata Laluan Baharu
            </label>
            <div class="relative">
                <input type="password" id="password_confirmation" name="password_confirmation"
                    required aria-required="true"
                    autocomplete="new-password"
                    class="form-input pr-10"
                    placeholder="••••••••">
                <button type="button" id="btn-toggle-password_confirmation"
                    aria-label="Tunjuk atau sembunyikan pengesahan kata laluan"
                    aria-pressed="false"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                    <i class="fa-solid fa-eye" id="eye-password_confirmation" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <button type="submit"
            class="w-full font-bold py-3 rounded-lg text-white shadow-lg mb-4 transition-colors"
            style="background:#f59e0b"
            onmouseover="this.style.background='#d97706'"
            onmouseout="this.style.background='#f59e0b'">
            <i class="fa-solid fa-key mr-2" aria-hidden="true"></i>
            Tetapkan Semula Kata Laluan
        </button>
    </form>

    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 text-sm text-slate-400 hover:text-amber-400 transition-colors">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali ke halaman log masuk
    </a>

</div>

<script nonce="{{ $cspNonce }}">
// ── Tunjuk/sembunyi kata laluan ────────────────────────────────────
['password', 'password_confirmation'].forEach(function(fieldId) {
    var btn = document.getElementById('btn-toggle-' + fieldId);
    if (btn) btn.addEventListener('click', function() { togglePwd(fieldId); });
});

function togglePwd(fieldId) {
    var input = document.getElementById(fieldId);
    var icon  = document.getElementById('eye-' + fieldId);
    var btn   = document.getElementById('btn-toggle-' + fieldId);
    var isShowing = input.type === 'text';
    input.type = isShowing ? 'password' : 'text';
    icon.className = isShowing ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
    btn.setAttribute('aria-pressed', isShowing ? 'false' : 'true');
}

// ── Meter Kekuatan Kata Laluan ─────────────────────────────────────
(function () {
    var pwInput  = document.getElementById('password');
    var wrap     = document.getElementById('kekuatan-wrap');
    var barIds   = ['bar-1', 'bar-2', 'bar-3', 'bar-4'];
    var labelEl  = document.getElementById('label-kekuatan');

    var syarat = {
        panjang: { el: document.getElementById('syarat-panjang'), fn: function(v) { return v.length >= 8; } },
        besar:   { el: document.getElementById('syarat-besar'),   fn: function(v) { return /[A-Z]/.test(v); } },
        kecil:   { el: document.getElementById('syarat-kecil'),   fn: function(v) { return /[a-z]/.test(v); } },
        nombor:  { el: document.getElementById('syarat-nombor'),  fn: function(v) { return /[0-9]/.test(v); } },
        simbol:  { el: document.getElementById('syarat-simbol'),  fn: function(v) { return /[^A-Za-z0-9]/.test(v); } },
    };

    var tahap = [
        { label: '',            warna: '' },
        { label: 'Lemah',       warna: '#dc2626' },
        { label: 'Sederhana',   warna: '#d97706' },
        { label: 'Kuat',        warna: '#16a34a' },
        { label: 'Sangat Kuat', warna: '#15803d' },
    ];

    if (!pwInput) return;

    pwInput.addEventListener('input', function () {
        var val  = this.value;
        var skor = 0;

        Object.values(syarat).forEach(function(s) {
            var lulus = s.fn(val);
            if (lulus) skor++;
            if (s.el) {
                s.el.className = lulus
                    ? 'flex items-center gap-1.5 text-green-400'
                    : 'flex items-center gap-1.5 text-slate-400';
                s.el.querySelector('i').className = lulus
                    ? 'fa-solid fa-circle-check w-2.5 text-[8px]'
                    : 'fa-solid fa-circle w-2.5 text-[8px]';
            }
        });

        if (val.length > 0) {
            wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
            return;
        }

        var warna = tahap[skor] ? tahap[skor].warna : '';
        barIds.forEach(function(id, i) {
            var bar = document.getElementById(id);
            if (!bar) return;
            bar.style.background = i < skor ? warna : '#4b5563';
        });

        if (labelEl) {
            labelEl.textContent = tahap[skor] ? tahap[skor].label : '';
            labelEl.style.color = warna;
        }
    });
})();
</script>
</body>
</html>
