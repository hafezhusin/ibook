<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk - iBook 2.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 100vh; }
        .login-card { background: #fff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
        .form-input { width:100%; border:1.5px solid #d1d5db; border-radius:8px; padding:11px 14px; font-size:14px; outline:none; transition:border .2s; }
        .form-input:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.15); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background:#f59e0b">
                <i class="fa-solid fa-book-open text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white">iBook <span style="color:#f59e0b">2.0</span></h1>
            <p class="text-slate-400 text-sm mt-1">Sistem Tempahan Bilik Mesyuarat</p>
        </div>

        <div class="login-card p-8">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Log Masuk</h2>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-5 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-xmark"></i>
                {{ $errors->first() }}
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-5 text-sm">
                {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fa-solid fa-envelope text-gray-400 mr-1"></i> Emel
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="form-input" placeholder="nama@jabatan.gov.my">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fa-solid fa-lock text-gray-400 mr-1"></i> Kata Laluan
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                            class="form-input pr-10" placeholder="••••••••">
                        <button type="button" onclick="togglePwd()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded" style="accent-color:#f59e0b">
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="w-full text-white font-bold py-3 rounded-lg transition-colors"
                    style="background:#f59e0b" onmouseover="this.style.background='#d97706'" onmouseout="this.style.background='#f59e0b'">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i> Log Masuk
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-gray-100 text-center text-xs text-gray-400">
                iBook 2.0 &copy; {{ date('Y') }} &mdash; Hak Cipta Terpelihara
            </div>
        </div>
    </div>

    <script>
        function togglePwd() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.className = 'fa-solid fa-eye-slash';
            } else {
                pwd.type = 'password';
                icon.className = 'fa-solid fa-eye';
            }
        }
    </script>
</body>
</html>
