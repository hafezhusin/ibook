<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Laluan — iBook 2.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(160deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%); min-height: 100vh; }
        .form-input { width:100%; border:1.5px solid rgba(255,255,255,0.2); border-radius:8px; padding:11px 14px; font-size:14px; outline:none; transition:border .2s; background:rgba(255,255,255,0.08); color:white; }
        .form-input::placeholder { color: rgba(255,255,255,0.35); }
        .form-input:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.2); }
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

    <h1 class="text-white text-2xl font-bold mb-1">Lupa Kata Laluan?</h1>
    <p class="text-slate-400 text-sm mb-7">Masukkan emel anda dan kami akan hantar pautan untuk menetapkan semula kata laluan.</p>

    @if(session('status'))
    <div class="bg-green-900/40 border border-green-500/40 text-green-300 rounded-lg p-4 mb-6 text-sm flex items-start gap-2" role="alert">
        <i class="fa-solid fa-circle-check mt-0.5 flex-shrink-0" aria-hidden="true"></i>
        <span>{{ session('status') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-900/40 border border-red-500/40 text-red-300 rounded-lg p-3 mb-5 text-sm flex items-center gap-2" role="alert">
        <i class="fa-solid fa-circle-xmark flex-shrink-0" aria-hidden="true"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <div class="mb-5">
            <label for="email" class="block text-sm font-semibold text-slate-300 mb-2">
                <i class="fa-solid fa-envelope text-amber-400 mr-1" aria-hidden="true"></i> Emel
            </label>
            <input type="email" id="email" name="email"
                value="{{ old('email') }}"
                required aria-required="true"
                autocomplete="email"
                class="form-input"
                placeholder="nama@jabatan.gov.my">
        </div>

        <button type="submit"
            class="w-full font-bold py-3 rounded-lg text-white shadow-lg mb-4 transition-colors"
            style="background:#f59e0b"
            onmouseover="this.style.background='#d97706'"
            onmouseout="this.style.background='#f59e0b'">
            <i class="fa-solid fa-paper-plane mr-2" aria-hidden="true"></i>
            Hantar Pautan Set Semula
        </button>
    </form>

    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 text-sm text-slate-400 hover:text-amber-400 transition-colors">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali ke halaman log masuk
    </a>

</div>
</body>
</html>
