@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Profil Saya</h1>
        <p class="text-gray-500 text-sm mt-0.5">Urus maklumat peribadi dan kata laluan anda</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm flex items-center gap-2" role="alert">
    <i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('success') }}
</div>
@endif

@if(session('success_password'))
<div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm flex items-center gap-2" role="alert">
    <i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('success_password') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Sidebar kad profil --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            {{-- Avatar --}}
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4"
                 style="background: var(--accent); color: #1a1a2e;"
                 aria-hidden="true">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h2 class="font-bold text-gray-800 text-lg">{{ $user->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $user->email }}</p>
            <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold"
                  style="background: rgba(245,158,11,0.15); color: #92400e;">
                {{ $user->label_peranan }}
            </span>
            @if($user->jabatan)
            <p class="text-xs text-gray-400 mt-2">
                <i class="fa-solid fa-building mr-1" aria-hidden="true"></i>{{ $user->jabatan }}
            </p>
            @endif
            <p class="text-xs text-gray-400 mt-1">
                <i class="fa-solid fa-calendar mr-1" aria-hidden="true"></i>Ahli sejak {{ $user->created_at->format('M Y') }}
            </p>
        </div>
    </div>

    {{-- Borang --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- ── Kemaskini Maklumat ── --}}
        <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-maklumat">
            <h2 id="heading-maklumat" class="font-bold text-gray-800 text-base mb-5 pb-3 border-b border-gray-100">
                <i class="fa-solid fa-user-pen text-amber-500 mr-2" aria-hidden="true"></i>Kemaskini Maklumat
            </h2>

            <form method="POST" action="{{ route('profil.update') }}" novalidate>
                @csrf
                <div class="space-y-4">

                    {{-- Nama --}}
                    <div>
                        <label for="name" class="form-label">
                            Nama Penuh <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                            value="{{ old('name', $user->name) }}"
                            class="form-input"
                            required aria-required="true"
                            autocomplete="name"
                            @error('name') aria-invalid="true" aria-describedby="ralat-name" @enderror>
                        @error('name')
                        <p id="ralat-name" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Emel (readonly) --}}
                    <div>
                        <label for="email" class="form-label">Emel</label>
                        <input type="email" id="email" value="{{ $user->email }}"
                            class="form-input bg-gray-50 text-gray-400 cursor-not-allowed"
                            readonly disabled aria-readonly="true"
                            autocomplete="email">
                        <p class="form-hint">Emel tidak boleh diubah. Hubungi pentadbir jika perlu.</p>
                    </div>

                    {{-- Unit/Jabatan --}}
                    <div>
                        <label for="jabatan" class="form-label">Unit / Jabatan</label>
                        <input type="text" id="jabatan" name="jabatan"
                            value="{{ old('jabatan', $user->jabatan) }}"
                            class="form-input"
                            placeholder="cth: Unit Aplikasi Gunasama"
                            autocomplete="organization"
                            @error('jabatan') aria-invalid="true" aria-describedby="ralat-jabatan" @enderror>
                        @error('jabatan')
                        <p id="ralat-jabatan" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="mt-5 flex gap-3">
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Simpan Maklumat
                    </button>
                </div>
            </form>
        </section>

        {{-- ── Tukar Kata Laluan ── --}}
        <section class="bg-white rounded-xl shadow-sm p-6" aria-labelledby="heading-password">
            <h2 id="heading-password" class="font-bold text-gray-800 text-base mb-5 pb-3 border-b border-gray-100">
                <i class="fa-solid fa-lock text-amber-500 mr-2" aria-hidden="true"></i>Tukar Kata Laluan
            </h2>

            <form method="POST" action="{{ route('profil.password') }}" novalidate>
                @csrf
                <div class="space-y-4">

                    {{-- Kata laluan semasa --}}
                    <div>
                        <label for="kata_laluan_semasa" class="form-label">
                            Kata Laluan Semasa <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="kata_laluan_semasa" name="kata_laluan_semasa"
                                class="form-input pr-10"
                                required aria-required="true"
                                autocomplete="current-password"
                                @error('kata_laluan_semasa') aria-invalid="true" aria-describedby="ralat-kls" @enderror>
                            <button type="button" id="btn-toggle-kata_laluan_semasa"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                aria-label="Tunjuk/sembunyi kata laluan semasa">
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('kata_laluan_semasa')
                        <p id="ralat-kls" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kata laluan baharu --}}
                    <div>
                        <label for="password" class="form-label">
                            Kata Laluan Baharu <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password"
                                class="form-input pr-10"
                                required aria-required="true"
                                autocomplete="new-password"
                                @error('password') aria-invalid="true" aria-describedby="ralat-pw" @enderror>
                            <button type="button" id="btn-toggle-password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                aria-label="Tunjuk/sembunyi kata laluan baharu">
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        {{-- Meter kekuatan kata laluan --}}
                        <div id="kekuatan-wrap" class="hidden mt-2 space-y-2">
                            {{-- Bar kekuatan --}}
                            <div class="flex gap-1" aria-hidden="true">
                                <div class="h-1.5 flex-1 rounded-full bg-gray-200 overflow-hidden">
                                    <div id="bar-1" class="h-full w-full rounded-full transition-colors duration-300 bg-gray-200"></div>
                                </div>
                                <div class="h-1.5 flex-1 rounded-full bg-gray-200 overflow-hidden">
                                    <div id="bar-2" class="h-full w-full rounded-full transition-colors duration-300 bg-gray-200"></div>
                                </div>
                                <div class="h-1.5 flex-1 rounded-full bg-gray-200 overflow-hidden">
                                    <div id="bar-3" class="h-full w-full rounded-full transition-colors duration-300 bg-gray-200"></div>
                                </div>
                                <div class="h-1.5 flex-1 rounded-full bg-gray-200 overflow-hidden">
                                    <div id="bar-4" class="h-full w-full rounded-full transition-colors duration-300 bg-gray-200"></div>
                                </div>
                            </div>
                            <p id="label-kekuatan" class="text-xs font-semibold" aria-live="polite"></p>
                            {{-- Senarai syarat --}}
                            <ul class="space-y-0.5 text-xs" aria-label="Syarat kata laluan">
                                <li id="syarat-panjang"  class="flex items-center gap-1.5 text-gray-400"><i class="fa-solid fa-circle w-2.5 text-[8px]"></i> Sekurang-kurangnya 8 aksara</li>
                                <li id="syarat-besar"    class="flex items-center gap-1.5 text-gray-400"><i class="fa-solid fa-circle w-2.5 text-[8px]"></i> Huruf besar (A–Z)</li>
                                <li id="syarat-kecil"    class="flex items-center gap-1.5 text-gray-400"><i class="fa-solid fa-circle w-2.5 text-[8px]"></i> Huruf kecil (a–z)</li>
                                <li id="syarat-nombor"   class="flex items-center gap-1.5 text-gray-400"><i class="fa-solid fa-circle w-2.5 text-[8px]"></i> Nombor (0–9)</li>
                                <li id="syarat-simbol"   class="flex items-center gap-1.5 text-gray-400"><i class="fa-solid fa-circle w-2.5 text-[8px]"></i> Simbol (!@#$...)</li>
                            </ul>
                        </div>
                        <p class="form-hint">Minimum 8 aksara, huruf besar & kecil, nombor dan simbol.</p>
                        @error('password')
                        <p id="ralat-pw" class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sahkan kata laluan baharu --}}
                    <div>
                        <label for="password_confirmation" class="form-label">
                            Sahkan Kata Laluan Baharu <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="form-input pr-10"
                                required aria-required="true"
                                autocomplete="new-password">
                            <button type="button" id="btn-toggle-password_confirmation"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                aria-label="Tunjuk/sembunyi pengesahan kata laluan">
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                </div>

                <div class="mt-5">
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-key" aria-hidden="true"></i> Tukar Kata Laluan
                    </button>
                </div>
            </form>
        </section>

    </div>
</div>

<script nonce="{{ $cspNonce }}">
// ── Tunjuk/sembunyi kata laluan ────────────────────────────────────
['kata_laluan_semasa', 'password', 'password_confirmation'].forEach(function(fieldId) {
    const btn = document.getElementById('btn-toggle-' + fieldId);
    if (btn) btn.addEventListener('click', function() { togglePwd(fieldId, this); });
});

function togglePwd(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}

// ── Meter Kekuatan Kata Laluan ─────────────────────────────────────
(function () {
    const pwInput   = document.getElementById('password');
    const wrap      = document.getElementById('kekuatan-wrap');
    const barIds    = ['bar-1', 'bar-2', 'bar-3', 'bar-4'];
    const labelEl   = document.getElementById('label-kekuatan');

    const syarat = {
        panjang : { el: document.getElementById('syarat-panjang'), fn: v => v.length >= 8 },
        besar   : { el: document.getElementById('syarat-besar'),   fn: v => /[A-Z]/.test(v) },
        kecil   : { el: document.getElementById('syarat-kecil'),   fn: v => /[a-z]/.test(v) },
        nombor  : { el: document.getElementById('syarat-nombor'),  fn: v => /[0-9]/.test(v) },
        simbol  : { el: document.getElementById('syarat-simbol'),  fn: v => /[^A-Za-z0-9]/.test(v) },
    };

    const tahap = [
        { label: '',              warna: '' },
        { label: 'Lemah',         warna: '#dc2626' },  // merah
        { label: 'Sederhana',     warna: '#d97706' },  // oren
        { label: 'Kuat',          warna: '#16a34a' },  // hijau
        { label: 'Sangat Kuat',   warna: '#15803d' },  // hijau gelap
    ];

    if (!pwInput) return;

    pwInput.addEventListener('input', function () {
        const val   = this.value;
        let skor    = 0;

        // Kemas kini setiap syarat
        Object.values(syarat).forEach(s => {
            const lulus = s.fn(val);
            if (lulus) skor++;
            if (s.el) {
                s.el.className = lulus
                    ? 'flex items-center gap-1.5 text-green-600'
                    : 'flex items-center gap-1.5 text-gray-400';
                s.el.querySelector('i').className = lulus
                    ? 'fa-solid fa-circle-check w-2.5 text-[8px]'
                    : 'fa-solid fa-circle w-2.5 text-[8px]';
            }
        });

        // Tunjuk/sembunyi wrap
        if (val.length > 0) {
            wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
            return;
        }

        // Warna bar mengikut skor
        const warna = tahap[skor]?.warna || '';
        barIds.forEach((id, i) => {
            const bar = document.getElementById(id);
            if (!bar) return;
            bar.style.background = i < skor ? warna : '#e5e7eb';
        });

        // Label
        if (labelEl) {
            labelEl.textContent = tahap[skor]?.label || '';
            labelEl.style.color = warna;
        }
    });
})();
</script>
@endsection
