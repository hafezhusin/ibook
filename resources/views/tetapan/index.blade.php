@extends('layouts.app')

@section('title', 'Tetapan')

@section('content')
<div class="mb-6 flex items-start justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Tetapan</h1>
        <p class="text-gray-500 text-sm mt-1">Konfigurasi sistem</p>
    </div>

    {{-- ── Dikemaskini terakhir oleh (Item 2) ── --}}
    @if($logTerakhir)
    <div class="text-right text-xs text-gray-400 hidden sm:block">
        <i class="fa-solid fa-clock-rotate-left mr-1" aria-hidden="true"></i>
        Dikemaskini oleh
        <span class="font-semibold text-gray-600">{{ $logTerakhir->pengguna?->name ?? 'Sistem' }}</span><br>
        <span>{{ $logTerakhir->dicipta_pada->format('d M Y, h:i A') }}</span>
        @if(!empty($logTerakhir->butiran['jumlah_berubah']))
        <span class="ml-1 text-amber-500">({{ $logTerakhir->butiran['jumlah_berubah'] }} perubahan)</span>
        @endif
    </div>
    @endif
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm flex items-center gap-2" role="alert">
    <i class="fa-solid fa-circle-check text-green-500" aria-hidden="true"></i>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm flex items-center gap-2" role="alert">
    <i class="fa-solid fa-circle-xmark text-red-500" aria-hidden="true"></i>
    {{ session('error') }}
</div>
@endif

<div class="max-w-2xl space-y-5">

    <form method="POST" action="{{ route('tetapan.update') }}" novalidate aria-label="Borang tetapan sistem">
        @csrf

        {{-- ════════════════════════════ --}}
        {{-- BAHAGIAN 1: Maklumat Organisasi --}}
        {{-- ════════════════════════════ --}}
        <fieldset class="bg-white rounded-xl shadow-sm overflow-hidden">
            <legend class="w-full">
                <div class="flex items-center gap-2 px-6 pt-5 pb-4 border-b border-gray-100">
                    <span class="w-7 h-7 rounded-full bg-amber-400 text-white text-xs font-bold flex items-center justify-center flex-shrink-0" aria-hidden="true">1</span>
                    <span class="font-bold text-gray-800">Maklumat Organisasi</span>
                </div>
            </legend>
            <div class="p-6 space-y-5">

                <div>
                    <label for="nama_sistem" class="form-label">Nama Sistem</label>
                    <input type="text" id="nama_sistem" name="nama_sistem"
                        value="{{ old('nama_sistem', $tetapan['nama_sistem'] ?? '') }}"
                        class="form-input"
                        placeholder="cth: iBook 2.0"
                        maxlength="120"
                        @error('nama_sistem') aria-invalid="true" aria-describedby="ralat-nama_sistem" @enderror>
                    <p class="form-hint">Nama ini akan dipaparkan dalam header dan tab pelayar.</p>
                    @error('nama_sistem')
                    <p id="ralat-nama_sistem" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nama_jabatan" class="form-label">
                        Nama Bahagian / Jabatan <span class="text-red-500" aria-hidden="true">*</span>
                        <span class="sr-only">(wajib)</span>
                    </label>
                    <input type="text" id="nama_jabatan" name="nama_jabatan"
                        value="{{ old('nama_jabatan', $tetapan['nama_jabatan'] ?? '') }}"
                        class="form-input"
                        placeholder="cth: Bahagian Pengurusan Teknologi Maklumat"
                        required aria-required="true"
                        maxlength="150"
                        @error('nama_jabatan') aria-invalid="true" aria-describedby="ralat-nama_jabatan" @enderror>
                    <p class="form-hint">Akan dipaparkan di header dan footer sistem.</p>
                    @error('nama_jabatan')
                    <p id="ralat-nama_jabatan" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Logo Jabatan --}}
                <div>
                    <label for="logo_jabatan" class="form-label">
                        <i class="fa-solid fa-image text-gray-400 mr-1" aria-hidden="true"></i>
                        Logo Jabatan
                        <span class="ml-1 text-xs font-normal text-gray-400">(pilihan)</span>
                    </label>
                    @php $logoSemasa = $tetapan['logo_jabatan'] ?? ''; @endphp
                    @if($logoSemasa)
                    <div class="mb-3 flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <img src="{{ $logoSemasa }}" alt="Logo jabatan semasa" class="h-10 w-10 object-contain">
                        <div>
                            <p class="text-xs font-medium text-gray-600">Logo semasa</p>
                            <p class="text-xs text-gray-400 break-all">{{ $logoSemasa }}</p>
                        </div>
                    </div>
                    @endif
                    <input type="url" id="logo_jabatan" name="logo_jabatan"
                        value="{{ old('logo_jabatan', $logoSemasa) }}"
                        class="form-input"
                        placeholder="cth: /images/logo-bptm.png"
                        maxlength="255"
                        @error('logo_jabatan') aria-invalid="true" aria-describedby="ralat-logo_jabatan" @enderror>
                    <p class="form-hint">URL logo jabatan — akan dipaparkan di sidebar dan header. Saiz cadangan: 120×120px (PNG/WebP dengan latar telus).</p>
                    @error('logo_jabatan')
                    <p id="ralat-logo_jabatan" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </fieldset>

        {{-- ════════════════════════════ --}}
        {{-- BAHAGIAN 2: Emel (dipisahkan — Item 6) --}}
        {{-- ════════════════════════════ --}}
        <fieldset class="bg-white rounded-xl shadow-sm overflow-hidden">
            <legend class="w-full">
                <div class="flex items-center gap-2 px-6 pt-5 pb-4 border-b border-gray-100">
                    <span class="w-7 h-7 rounded-full bg-amber-400 text-white text-xs font-bold flex items-center justify-center flex-shrink-0" aria-hidden="true">2</span>
                    <span class="font-bold text-gray-800">Alamat Emel</span>
                </div>
            </legend>
            <div class="p-6 space-y-5">

                {{-- Emel Paparan (footer) --}}
                <div>
                    <label for="emel_pentadbir" class="form-label">
                        <i class="fa-solid fa-eye text-gray-400 mr-1" aria-hidden="true"></i>Emel Paparan Footer
                    </label>
                    <input type="email" id="emel_pentadbir" name="emel_pentadbir"
                        value="{{ old('emel_pentadbir', $tetapan['emel_pentadbir'] ?? '') }}"
                        class="form-input"
                        placeholder="cth: admin@jabatan.gov.my"
                        maxlength="150"
                        @error('emel_pentadbir') aria-invalid="true" aria-describedby="ralat-emel_pentadbir" @enderror>
                    <p class="form-hint">Emel <strong>paparan awam</strong> — dipaparkan di footer untuk dihubungi oleh pengguna.</p>
                    @error('emel_pentadbir')
                    <p id="ralat-emel_pentadbir" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Emel Notifikasi (penerima sistem) --}}
                <div>
                    <label for="emel_notifikasi" class="form-label">
                        <i class="fa-solid fa-bell text-gray-400 mr-1" aria-hidden="true"></i>Emel Penerima Notifikasi Sistem
                    </label>
                    <input type="email" id="emel_notifikasi" name="emel_notifikasi"
                        value="{{ old('emel_notifikasi', $tetapan['emel_notifikasi'] ?? '') }}"
                        class="form-input"
                        placeholder="cth: urussetia@jabatan.gov.my"
                        maxlength="150"
                        @error('emel_notifikasi') aria-invalid="true" aria-describedby="ralat-emel_notifikasi" @enderror>
                    <p class="form-hint">Emel <strong>dalaman operasi</strong> — menerima notifikasi tempahan baru dan peringatan sistem.</p>
                    @error('emel_notifikasi')
                    <p id="ralat-emel_notifikasi" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Info box pemisahan --}}
                <div class="flex items-start gap-2 p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-700">
                    <i class="fa-solid fa-circle-info mt-0.5 flex-shrink-0" aria-hidden="true"></i>
                    <span>Emel paparan dan emel notifikasi <strong>boleh berbeza</strong>. Pemisahan ini memastikan pertukaran emel paparan tidak mengganggu aliran notifikasi sistem.</span>
                </div>

            </div>
        </fieldset>

        {{-- ════════════════════════════ --}}
        {{-- BAHAGIAN 3: Notifikasi --}}
        {{-- ════════════════════════════ --}}
        <fieldset class="bg-white rounded-xl shadow-sm overflow-hidden">
            <legend class="w-full">
                <div class="flex items-center gap-2 px-6 pt-5 pb-4 border-b border-gray-100">
                    <span class="w-7 h-7 rounded-full bg-amber-400 text-white text-xs font-bold flex items-center justify-center flex-shrink-0" aria-hidden="true">3</span>
                    <span class="font-bold text-gray-800">Notifikasi</span>
                </div>
            </legend>
            <div class="p-6 space-y-4">

                <div>
                    <label class="flex items-start gap-3 cursor-pointer" for="notif-tempahan-baru">
                        <input type="checkbox" id="notif-tempahan-baru" name="notif_tempahan_baru" value="1"
                            class="w-4 h-4 rounded flex-shrink-0 mt-0.5" style="accent-color:#f59e0b"
                            {{ ($tetapan['notif_tempahan_baru'] ?? '1') === '1' ? 'checked' : '' }}>
                        <div>
                            <div class="font-semibold text-sm text-gray-700">E-mel notifikasi untuk tempahan baru</div>
                            <div class="text-xs text-gray-400 mt-0.5">Hantar emel kepada penerima notifikasi apabila ada tempahan baru diterima</div>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="flex items-start gap-3 cursor-pointer" for="notif-kelulusan">
                        <input type="checkbox" id="notif-kelulusan" name="notif_kelulusan" value="1"
                            class="w-4 h-4 rounded flex-shrink-0 mt-0.5" style="accent-color:#f59e0b"
                            {{ ($tetapan['notif_kelulusan'] ?? '1') === '1' ? 'checked' : '' }}>
                        <div>
                            <div class="font-semibold text-sm text-gray-700">E-mel notifikasi kelulusan / penolakan</div>
                            <div class="text-xs text-gray-400 mt-0.5">Maklumkan pemohon apabila tempahan diluluskan atau ditolak</div>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="flex items-start gap-3 cursor-pointer" for="peringatan-mesyuarat">
                        <input type="checkbox" id="peringatan-mesyuarat" name="peringatan_mesyuarat" value="1"
                            class="w-4 h-4 rounded flex-shrink-0 mt-0.5" style="accent-color:#f59e0b"
                            {{ ($tetapan['peringatan_mesyuarat'] ?? '1') === '1' ? 'checked' : '' }}>
                        <div>
                            <div class="font-semibold text-sm text-gray-700">Peringatan mesyuarat (1 jam sebelum)</div>
                            <div class="text-xs text-gray-400 mt-0.5">Hantar peringatan emel 1 jam sebelum mesyuarat bermula</div>
                        </div>
                    </label>
                </div>

            </div>
        </fieldset>

        {{-- Butang Simpan --}}
        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Simpan Tetapan
            </button>
            @if($logTerakhir)
            <span class="text-xs text-gray-400 sm:hidden">
                Kemaskini terakhir: {{ $logTerakhir->dicipta_pada->diffForHumans() }}
            </span>
            @endif
        </div>

    </form>

    {{-- ════════════════════════════ --}}
    {{-- PANEL MAKLUMAT PERSEKITARAN — Pentadbir Sistem sahaja --}}
    {{-- ════════════════════════════ --}}
    @if(auth()->user()->isPentadbir())
    <section class="bg-white rounded-xl shadow-sm overflow-hidden" aria-labelledby="heading-env">
        <div class="flex items-center gap-2 px-6 pt-5 pb-4 border-b border-gray-100">
            <i class="fa-solid fa-server text-gray-400" aria-hidden="true"></i>
            <h2 id="heading-env" class="font-bold text-gray-800">Maklumat Persekitaran</h2>
            <span class="ml-auto text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 font-medium">Baca Sahaja</span>
        </div>
        <div class="p-6">
            <p class="text-xs text-gray-400 mb-4">
                Nilai berikut dikawal oleh fail konfigurasi pelayan (.env) dan tidak boleh ditukar melalui antara muka ini.
                Hubungi pentadbir pelayan untuk sebarang perubahan.
            </p>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">

                <div class="flex flex-col">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Persekitaran Aplikasi</dt>
                    <dd>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded
                            {{ config('app.env') === 'production' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                            <i class="fa-solid fa-circle text-xs" aria-hidden="true"></i>
                            {{ strtoupper(config('app.env', 'unknown')) }}
                        </span>
                    </dd>
                </div>

                <div class="flex flex-col">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Debug Mode</dt>
                    <dd>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded
                            {{ config('app.debug') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{ config('app.debug') ? 'AKTIF — Matikan sebelum production' : 'Dimatikan' }}
                        </span>
                    </dd>
                </div>

                <div class="flex flex-col">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Versi PHP</dt>
                    <dd class="font-mono text-gray-700 text-sm">{{ PHP_VERSION }}</dd>
                </div>

                <div class="flex flex-col">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Versi Laravel</dt>
                    <dd class="font-mono text-gray-700 text-sm">{{ app()->version() }}</dd>
                </div>

                <div class="flex flex-col">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Enjin Cache</dt>
                    <dd class="font-mono text-gray-700 text-sm">{{ strtoupper(config('cache.default', 'file')) }}</dd>
                </div>

                <div class="flex flex-col">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Tempoh Sesi</dt>
                    <dd class="font-mono text-gray-700 text-sm">{{ config('session.lifetime', 120) }} minit</dd>
                </div>

                <div class="flex flex-col">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Pemandu E-mel</dt>
                    <dd class="font-mono text-gray-700 text-sm">{{ strtoupper(config('mail.default', 'smtp')) }}</dd>
                </div>

                <div class="flex flex-col">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Zon Masa</dt>
                    <dd class="font-mono text-gray-700 text-sm">{{ config('app.timezone', 'UTC') }}</dd>
                </div>

            </dl>

            {{-- Amaran debug --}}
            @if(config('app.debug'))
            <div class="mt-4 flex items-start gap-2 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700" role="alert">
                <i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0" aria-hidden="true"></i>
                <span><strong>Amaran:</strong> Debug mode sedang aktif. Ini mendedahkan maklumat sistem sensitif kepada pengguna. Matikan dalam .env: <code class="bg-red-100 px-1 rounded">APP_DEBUG=false</code></span>
            </div>
            @endif
        </div>
    </section>
    @endif {{-- isPentadbir — Maklumat Persekitaran --}}

    {{-- ════════════════════════════ --}}
    {{-- LOG PERUBAHAN TERKINI --}}
    {{-- ════════════════════════════ --}}
    @php
        $logPerubahan = \App\Models\ActivityLog::where('tindakan', 'kemaskini_tetapan')
            ->latest('dicipta_pada')
            ->with('pengguna:id,name')
            ->take(5)
            ->get();
    @endphp

    @if($logPerubahan->isNotEmpty())
    <section class="bg-white rounded-xl shadow-sm overflow-hidden" aria-labelledby="heading-log">
        <div class="flex items-center gap-2 px-6 pt-5 pb-4 border-b border-gray-100">
            <i class="fa-solid fa-history text-gray-400" aria-hidden="true"></i>
            <h2 id="heading-log" class="font-bold text-gray-800">Log Perubahan Terkini</h2>
        </div>
        <ul class="divide-y divide-gray-50">
            @foreach($logPerubahan as $log)
            <li class="px-6 py-3 flex items-start justify-between gap-4">
                <div class="flex items-start gap-2 min-w-0">
                    <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fa-solid fa-pen text-amber-500 text-xs" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-gray-700">
                            {{ $log->pengguna?->name ?? 'Sistem' }}
                            <span class="text-gray-400 font-normal">mengemaskini tetapan sistem</span>
                        </div>
                        @if(!empty($log->butiran['jumlah_berubah']))
                        <div class="text-xs text-gray-400 mt-0.5">
                            {{ $log->butiran['jumlah_berubah'] }} nilai dikemaskini
                            @if(!empty($log->butiran['perubahan']))
                            &middot;
                            <span class="text-gray-500">{{ implode(', ', array_keys($log->butiran['perubahan'])) }}</span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                <div class="text-xs text-gray-400 flex-shrink-0 text-right">
                    <div>{{ $log->dicipta_pada->format('d M Y') }}</div>
                    <div>{{ $log->dicipta_pada->format('h:i A') }}</div>
                </div>
            </li>
            @endforeach
        </ul>
    </section>
    @endif

</div>
@endsection
