<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Ralat Pelayan | iBook 2.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
<div class="text-center p-10 max-w-md">
    <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6" style="background:#1a1a2e">
        <span class="text-4xl font-black" style="color:#f59e0b">500</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Ralat Pelayan</h1>
    <p class="text-gray-500 mb-6">Berlaku ralat teknikal. Sila cuba sebentar lagi atau hubungi Pentadbir Sistem.</p>
    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
       class="inline-block px-6 py-3 rounded-lg font-semibold text-white"
       style="background:#f59e0b">
        &larr; Kembali
    </a>
</div>
</body>
</html>
