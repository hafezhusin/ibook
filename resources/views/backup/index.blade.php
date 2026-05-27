@extends('layouts.app')

@section('title', 'Backup Database')

@section('content')

{{-- ── Header ── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Backup Database</h1>
        <p class="text-gray-500 text-sm mt-0.5">Urus backup dan pemulihan data sistem iBook</p>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm flex items-center gap-2" role="alert">
    <i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-center gap-2" role="alert">
    <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> {{ session('error') }}
</div>
@endif

{{-- ── Banner: Backup Tertunggak ── --}}
@if($tertunggak)
<div class="mb-5 p-4 rounded-xl flex items-start gap-3"
     style="background:#fef9c3; border:1.5px solid #fde68a; color:#92400e" role="alert">
    <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 flex-shrink-0 text-lg" aria-hidden="true"></i>
    <div class="flex-1">
        <p class="font-semibold text-sm">Backup Jadual Tertunggak!</p>
        <p class="text-xs mt-0.5">
            Backup
            @if($tetapan['jadual'] === 'mingguan') mingguan @else bulanan @endif
            anda sudah tamat tempoh.
            Klik <strong>Backup Sekarang</strong> untuk muat turun backup terkini.
        </p>
    </div>
    <form method="POST" action="{{ route('backup.instant') }}" class="flex-shrink-0">
        @csrf
        <button type="submit"
            class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg transition"
            style="background:#f59e0b; color:#1a1a2e; border:none">
            <i class="fa-solid fa-database" aria-hidden="true"></i> Backup Sekarang
        </button>
    </form>
</div>
@endif

{{-- ── Grid Utama ── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- ── Kad 1: Backup Segera ── --}}
    <div class="lg:col-span-1">
        <section class="bg-white rounded-xl shadow-sm p-6 h-full flex flex-col" aria-labelledby="heading-segera">
            <h2 id="heading-segera" class="font-bold text-gray-800 text-base mb-1 flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-500" aria-hidden="true"></i>
                Backup Segera
            </h2>
            <p class="text-sm text-gray-500 mb-5 leading-relaxed">
                Jana dan muat turun fail backup database <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">.sql</code>
                terus sekarang.
            </p>

            <div class="mt-auto">
                <form method="POST" action="{{ route('backup.instant') }}" id="form-backup-segera">
                    @csrf
                    <button type="submit" id="btn-backup"
                        class="w-full inline-flex items-center justify-center gap-2 font-bold text-sm px-5 py-3.5 rounded-xl transition-all"
                        style="background:#f59e0b; color:#1a1a2e;">
                        <i class="fa-solid fa-database" aria-hidden="true"></i>
                        Backup Sekarang
                    </button>
                </form>
                <p class="text-xs text-gray-400 text-center mt-3">
                    <i class="fa-solid fa-info-circle mr-1" aria-hidden="true"></i>
                    Fail akan dimuat turun terus ke peranti anda.
                </p>
            </div>
        </section>
    </div>

    {{-- ── Kad 2: Jadual Backup ── --}}
    <div class="lg:col-span-2">
        <section class="bg-white rounded-xl shadow-sm p-6 h-full" aria-labelledby="heading-jadual">
            <h2 id="heading-jadual" class="font-bold text-gray-800 text-base mb-4 pb-3 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-amber-500" aria-hidden="true"></i>
                Jadual Backup Automatik
            </h2>

            <form method="POST" action="{{ route('backup.jadual') }}">
                @csrf

                <p class="text-sm text-gray-500 mb-4">
                    Tetapkan peringatan backup berkala. Sistem akan memaklumkan anda apabila masa backup tiba.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">

                    {{-- Tiada --}}
                    <label class="relative cursor-pointer">
                        <input type="radio" name="jadual" value="tiada"
                            class="sr-only peer"
                            {{ $tetapan['jadual'] === 'tiada' ? 'checked' : '' }}>
                        <div class="p-4 rounded-xl border-2 transition peer-checked:border-amber-400 peer-checked:bg-amber-50 border-gray-200 hover:border-gray-300 text-center">
                            <i class="fa-solid fa-ban text-gray-400 text-xl mb-2 block peer-checked:text-amber-500" aria-hidden="true"></i>
                            <p class="font-semibold text-sm text-gray-700">Tiada</p>
                            <p class="text-xs text-gray-400 mt-0.5">Backup manual sahaja</p>
                        </div>
                    </label>

                    {{-- Mingguan --}}
                    <label class="relative cursor-pointer">
                        <input type="radio" name="jadual" value="mingguan"
                            class="sr-only peer"
                            {{ $tetapan['jadual'] === 'mingguan' ? 'checked' : '' }}>
                        <div class="p-4 rounded-xl border-2 transition peer-checked:border-amber-400 peer-checked:bg-amber-50 border-gray-200 hover:border-gray-300 text-center">
                            <i class="fa-solid fa-calendar-week text-gray-400 text-xl mb-2 block" aria-hidden="true"></i>
                            <p class="font-semibold text-sm text-gray-700">Mingguan</p>
                            <p class="text-xs text-gray-400 mt-0.5">Peringatan setiap 7 hari</p>
                        </div>
                    </label>

                    {{-- Bulanan --}}
                    <label class="relative cursor-pointer">
                        <input type="radio" name="jadual" value="bulanan"
                            class="sr-only peer"
                            {{ $tetapan['jadual'] === 'bulanan' ? 'checked' : '' }}>
                        <div class="p-4 rounded-xl border-2 transition peer-checked:border-amber-400 peer-checked:bg-amber-50 border-gray-200 hover:border-gray-300 text-center">
                            <i class="fa-solid fa-calendar-days text-gray-400 text-xl mb-2 block" aria-hidden="true"></i>
                            <p class="font-semibold text-sm text-gray-700">Bulanan</p>
                            <p class="text-xs text-gray-400 mt-0.5">Peringatan setiap 30 hari</p>
                        </div>
                    </label>

                </div>

                {{-- Status jadual --}}
                @if($tetapan['jadual'] !== 'tiada')
                <div class="text-xs text-gray-500 mb-4 flex flex-wrap gap-4">
                    @if($tetapan['last_backup_at'])
                    <span>
                        <i class="fa-solid fa-clock-rotate-left mr-1 text-green-500" aria-hidden="true"></i>
                        Backup terakhir: <strong>{{ \Carbon\Carbon::parse($tetapan['last_backup_at'])->diffForHumans() }}</strong>
                    </span>
                    @endif
                    @if($nextBackup)
                    <span>
                        <i class="fa-solid fa-clock mr-1 {{ $tertunggak ? 'text-red-400' : 'text-amber-500' }}" aria-hidden="true"></i>
                        Backup seterusnya: <strong>{{ $nextBackup->format('d M Y') }}</strong>
                        @if($tertunggak)
                        <span class="text-red-500 font-semibold">(Tertunggak!)</span>
                        @endif
                    </span>
                    @endif
                </div>
                @endif

                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Simpan Jadual
                </button>
            </form>
        </section>
    </div>

</div>

{{-- ── Sejarah Backup ── --}}
<section class="bg-white rounded-xl shadow-sm overflow-hidden" aria-labelledby="heading-sejarah">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 id="heading-sejarah" class="font-bold text-gray-800 text-base flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-amber-500" aria-hidden="true"></i>
            Sejarah Backup
        </h2>
        <span class="text-xs text-gray-400">{{ $sejarah->count() }} rekod terkini</span>
    </div>

    @if($sejarah->isEmpty())
    <div class="py-12 text-center text-gray-400">
        <i class="fa-solid fa-database text-3xl mb-3" aria-hidden="true"></i>
        <p class="text-sm">Belum ada backup dibuat.</p>
        <p class="text-xs mt-1">Klik <strong>Backup Sekarang</strong> untuk memulakan.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm" role="grid">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-bold text-gray-400 uppercase tracking-wide">
                    <th class="px-6 py-3">Fail</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">Saiz</th>
                    <th class="px-4 py-3">Dibuat Oleh</th>
                    <th class="px-4 py-3">Tarikh</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($sejarah as $log)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-file-code text-amber-400 text-base flex-shrink-0" aria-hidden="true"></i>
                            <span class="font-mono text-xs text-gray-700 truncate max-w-[220px]" title="{{ $log->nama_fail }}">
                                {{ $log->nama_fail }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                            {{ $log->jenis === 'segera' ? 'bg-blue-100 text-blue-700' :
                               ($log->jenis === 'mingguan' ? 'bg-amber-100 text-amber-700' : 'bg-purple-100 text-purple-700') }}">
                            {{ $log->label_jenis }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs font-mono">
                        {{ $log->saiz_format }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        {{ $log->dibuatOleh?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $log->created_at->format('d M Y, H:i') }}
                        <div class="text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Muat Turun --}}
                            <form method="POST" action="{{ route('backup.muat-turun', $log) }}">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 font-semibold"
                                    title="Muat turun {{ $log->nama_fail }}">
                                    <i class="fa-solid fa-download" aria-hidden="true"></i> Muat Turun
                                </button>
                            </form>
                            {{-- Padam --}}
                            <form method="POST" action="{{ route('backup.padam', $log) }}"
                                onsubmit="return confirm('Padam rekod backup {{ addslashes($log->nama_fail) }}? Tindakan ini tidak boleh diundur.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center gap-1 text-xs text-red-400 hover:text-red-600 font-semibold">
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i> Padam
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</section>

{{-- Nota bawah --}}
<div class="mt-4 text-xs text-gray-400 flex items-start gap-2">
    <i class="fa-solid fa-circle-info mt-0.5" aria-hidden="true"></i>
    <span>
        Fail backup disimpan di pelayan dalam folder terlindung. Fail yang sudah dipadam tidak boleh dipulihkan.
        Disyorkan simpan salinan di luar pelayan (Google Drive, storan tempatan).
    </span>
</div>

@push('scripts')
<script nonce="{{ $cspNonce }}">
// Tunjuk loading state semasa backup
document.getElementById('form-backup-segera')?.addEventListener('submit', function() {
    const btn = document.getElementById('btn-backup');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Menjana backup...';
    btn.style.opacity = '0.75';
    // Re-enable after 30s in case of error
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-database" aria-hidden="true"></i> Backup Sekarang';
        btn.style.opacity = '1';
    }, 30000);
});

// Visual feedback untuk radio button selection
document.querySelectorAll('input[name="jadual"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="jadual"] + div').forEach(div => {
            div.querySelector('i')?.classList.remove('text-amber-500');
            div.querySelector('i')?.classList.add('text-gray-400');
        });
        if (this.checked) {
            const icon = this.nextElementSibling?.querySelector('i');
            if (icon) {
                icon.classList.remove('text-gray-400');
                icon.classList.add('text-amber-500');
            }
        }
    });
    // Apply on load for checked state
    if (radio.checked) {
        const icon = radio.nextElementSibling?.querySelector('i');
        if (icon) {
            icon.classList.remove('text-gray-400');
            icon.classList.add('text-amber-500');
        }
    }
});
</script>
@endpush

@endsection
