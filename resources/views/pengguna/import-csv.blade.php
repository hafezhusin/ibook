@extends('layouts.app')

@section('title', 'Import CSV Pengguna')

@section('content')

{{-- ── Header ───────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('pengguna.index') }}" class="hover:text-amber-600 transition-colors">Pengguna</a>
            <i class="fa-solid fa-chevron-right text-xs text-gray-300" aria-hidden="true"></i>
            <span class="text-gray-800 font-medium">Import CSV</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Import Pengguna daripada CSV</h1>
        <p class="text-gray-500 text-sm mt-1">
            Muat naik senarai pengguna bahagian anda dalam format CSV untuk diaktifkan secara pukal.
        </p>
    </div>
    <a href="{{ route('pengguna.import-csv.templat') }}"
       class="inline-flex items-center gap-2 text-sm font-medium px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-colors shadow-sm flex-shrink-0">
        <i class="fa-solid fa-file-csv text-green-600" aria-hidden="true"></i>
        Muat Turun Templat CSV
    </a>
</div>

{{-- ── Flash messages ───────────────────────────────────────────────── --}}
@if(session('error'))
<div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm flex items-center gap-2" role="alert">
    <i class="fa-solid fa-circle-xmark text-red-500 flex-shrink-0" aria-hidden="true"></i>
    {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm" role="alert">
    <p class="font-semibold mb-1">Sila betulkan ralat berikut:</p>
    <ul class="list-disc list-inside">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- FASA 1: Borang muat naik (tunjuk bila tiada pratonton) --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@if(!$pratonton)

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Borang muat naik --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-800">
                    <i class="fa-solid fa-upload text-amber-500 mr-2" aria-hidden="true"></i>
                    Langkah 1: Muat Naik Fail CSV
                </h2>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('pengguna.import-csv.pratonton') }}"
                      enctype="multipart/form-data" novalidate>
                    @csrf

                    {{-- Pilih Bahagian --}}
                    <div class="mb-5">
                        <label for="bahagian_id" class="form-label">
                            Bahagian <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        @if(auth()->user()->isPentadbir())
                        <select id="bahagian_id" name="bahagian_id" class="form-input" required>
                            <option value="">— Pilih Bahagian —</option>
                            @foreach($bahagian as $b)
                            <option value="{{ $b->id }}" {{ old('bahagian_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->kod }} — {{ $b->nama }}
                            </option>
                            @endforeach
                        </select>
                        @else
                            {{-- Urus setia: auto-set ke bahagian sendiri --}}
                            @php $b = $bahagian->first(); @endphp
                            <input type="hidden" name="bahagian_id" value="{{ $b?->id }}">
                            <div class="form-input bg-gray-50 text-gray-600 cursor-not-allowed">
                                <i class="fa-solid fa-building-columns text-amber-500 mr-2" aria-hidden="true"></i>
                                {{ $b?->kod }} — {{ $b?->nama }}
                            </div>
                            <p class="form-hint">Pengguna akan dimasukkan ke bahagian anda.</p>
                        @endif
                    </div>

                    {{-- Dropzone muat naik CSV --}}
                    <div class="mb-6">
                        <label for="csv_fail" class="form-label">
                            Fail CSV <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <div id="csv-dropzone"
                             class="relative border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer transition-colors hover:border-amber-400 hover:bg-amber-50">
                            <input type="file" id="csv_fail" name="csv_fail"
                                   accept=".csv,text/csv"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                   required aria-required="true">
                            <div id="csv-placeholder">
                                <i class="fa-solid fa-file-csv text-4xl text-gray-300 mb-3 block" aria-hidden="true"></i>
                                <p class="text-sm font-medium text-gray-600">Klik atau seret fail CSV ke sini</p>
                                <p class="text-xs text-gray-400 mt-1">Format: .csv • Maksimum: 2MB • Maksimum: 500 baris</p>
                            </div>
                            <div id="csv-selected" class="hidden">
                                <i class="fa-solid fa-circle-check text-3xl text-green-500 mb-2 block" aria-hidden="true"></i>
                                <p id="csv-nama-fail" class="text-sm font-semibold text-green-700"></p>
                                <p class="text-xs text-gray-400 mt-1">Klik untuk tukar fail</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        Pratonton Data CSV
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Panel panduan --}}
    <div class="space-y-4">
        {{-- Format CSV --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 pt-4 pb-3 border-b border-gray-100">
                <h3 class="font-bold text-gray-800 text-sm">
                    <i class="fa-solid fa-table text-amber-500 mr-1.5" aria-hidden="true"></i>
                    Format CSV
                </h3>
            </div>
            <div class="p-5 text-xs text-gray-600 space-y-2">
                <p>Baris pertama mestilah <strong>pengepala lajur</strong>:</p>
                <div class="bg-gray-50 rounded-lg p-3 font-mono text-xs text-gray-700 overflow-x-auto">
                    <div class="text-amber-600 font-bold">nama,emel,unit,peranan</div>
                    <div class="text-gray-500 mt-1">Ahmad Ali,ahmad@anm.gov.my,Unit Gaji,staf</div>
                    <div class="text-gray-500">Siti Hassan,siti@anm.gov.my,Unit ICT,urus_setia</div>
                </div>
                <ul class="space-y-1 mt-2">
                    <li><span class="font-semibold text-red-600">nama</span> — wajib</li>
                    <li><span class="font-semibold text-red-600">emel</span> — wajib, mestilah unik</li>
                    <li><span class="font-semibold text-gray-600">unit</span> — pilihan (jabatan/unit)</li>
                    <li><span class="font-semibold text-gray-600">peranan</span> — pilihan, lalai: <code class="bg-gray-100 px-1 rounded">staf</code></li>
                </ul>
            </div>
        </div>

        {{-- Nilai peranan sah --}}
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs text-blue-700 space-y-1">
            <p class="font-semibold mb-2">
                <i class="fa-solid fa-circle-info mr-1" aria-hidden="true"></i>
                Nilai Peranan Sah
            </p>
            <p><code class="bg-blue-100 px-1 rounded">staf</code> — Staf biasa (lalai)</p>
            <p><code class="bg-blue-100 px-1 rounded">urus_setia</code> — Urus Setia</p>
            <p><code class="bg-blue-100 px-1 rounded">pentadbir_sistem</code> — Pentadbir</p>
        </div>

        {{-- Status yang akan ditetapkan --}}
        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-xs text-amber-800 space-y-1.5">
            <p class="font-semibold mb-2">
                <i class="fa-solid fa-triangle-exclamation mr-1" aria-hidden="true"></i>
                Nota Penting
            </p>
            <p>• Pengguna <strong>baru</strong> akan dicipta dengan kata laluan lalai: <code class="bg-amber-100 px-1 rounded">iBook@{{ date('Y') }}</code></p>
            <p>• Pengguna yang <strong>sudah aktif</strong> tidak akan diubah.</p>
            <p>• Padanan dilakukan berdasarkan <strong>alamat emel</strong>.</p>
            <p>• Maksimum <strong>500 baris</strong> setiap import.</p>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- FASA 2: Pratonton (tunjuk bila ada data dalam session) --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@else

@php
    $jumlahBaru       = collect($pratonton)->where('status', 'baru')->count();
    $jumlahTidakAktif = collect($pratonton)->where('status', 'tidak_aktif')->count();
    $jumlahAktif      = collect($pratonton)->where('status', 'aktif')->count();
    $jumlahDuplikat   = collect($pratonton)->where('status', 'duplikat')->count();
    $jumlahRalat      = collect($pratonton)->where('status', 'ralat')->count();
    $bolehDipilih     = $jumlahBaru + $jumlahTidakAktif;
    $bahagianNama     = $bahagian->firstWhere('id', $bahagianId)?->nama ?? '—';
@endphp

{{-- Ringkasan stat --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-400">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Baru</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $jumlahBaru }}</p>
        <p class="text-xs text-gray-400 mt-0.5">akan dicipta</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-amber-400">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Tidak Aktif</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $jumlahTidakAktif }}</p>
        <p class="text-xs text-gray-400 mt-0.5">akan diaktifkan</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-400">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Sudah Aktif</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $jumlahAktif }}</p>
        <p class="text-xs text-gray-400 mt-0.5">tiada tindakan</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-purple-400">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Duplikat</p>
        <p class="text-2xl font-bold text-purple-600 mt-1">{{ $jumlahDuplikat }}</p>
        <p class="text-xs text-gray-400 mt-0.5">emel pendua dalam CSV</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-red-400">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Ralat</p>
        <p class="text-2xl font-bold text-red-600 mt-1">{{ $jumlahRalat }}</p>
        <p class="text-xs text-gray-400 mt-0.5">tidak akan diproses</p>
    </div>
</div>

{{-- Info bahagian + arahan --}}
<div class="mb-5 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3 text-sm text-amber-800">
    <i class="fa-solid fa-building-columns text-amber-500 mt-0.5 flex-shrink-0" aria-hidden="true"></i>
    <div>
        <p class="font-semibold">Langkah 2: Semak & Pilih Pengguna untuk Diproses</p>
        <p class="text-xs mt-0.5">Bahagian: <strong>{{ $bahagianNama }}</strong> &nbsp;•&nbsp;
        Tandakan pengguna yang ingin diaktifkan, kemudian klik <strong>Proses Pengguna Dipilih</strong>.</p>
    </div>
</div>

<form method="POST" action="{{ route('pengguna.import-csv.proses') }}" id="form-proses">
    @csrf

    {{-- Toolbar pilih semua + submit --}}
    <div class="flex items-center justify-between gap-4 mb-3 flex-wrap">
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                <input type="checkbox" id="pilih-semua" class="rounded" style="accent-color:#f59e0b">
                <span>Pilih Semua ({{ $bolehDipilih }} boleh dipilih)</span>
            </label>
            <span id="kiraan-dipilih" class="text-sm text-gray-400"></span>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('pengguna.import-csv') }}"
               onclick="return confirm('Batalkan pratonton? Data tidak disimpan.')"
               class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-rotate-left mr-1" aria-hidden="true"></i> Muat Naik Semula
            </a>
            <button type="submit" class="btn-primary" id="btn-proses" disabled>
                <i class="fa-solid fa-user-check" aria-hidden="true"></i>
                Proses Pengguna Dipilih
            </button>
        </div>
    </div>

    @if($errors->has('pilihan'))
    <p class="text-red-500 text-sm mb-3">
        <i class="fa-solid fa-circle-xmark mr-1" aria-hidden="true"></i>
        {{ $errors->first('pilihan') }}
    </p>
    @endif

    {{-- Jadual pratonton --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" aria-label="Pratonton import CSV pengguna">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-left">
                        <th class="px-4 py-3 w-10"></th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-8">#</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Emel</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Unit / Jabatan</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center w-28">Peranan</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center w-36">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($pratonton as $i => $b)
                    @php
                        $bolehPilih = in_array($b['status'], ['baru', 'tidak_aktif']);
                        $rowCls = match($b['status']) {
                            'ralat'     => 'bg-red-50/50',
                            'aktif'     => 'opacity-60',
                            'duplikat'  => 'bg-purple-50/50 opacity-70',
                            default     => '',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors {{ $rowCls }}">
                        {{-- Checkbox --}}
                        <td class="px-4 py-3 text-center">
                            @if($bolehPilih)
                            <input type="checkbox"
                                   name="pilihan[]"
                                   value="{{ $i }}"
                                   class="checkbox-baris rounded"
                                   style="accent-color:#f59e0b"
                                   aria-label="Pilih {{ $b['nama'] }}">
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Bil --}}
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $b['baris'] }}</td>

                        {{-- Nama --}}
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $b['nama'] }}</td>

                        {{-- Emel --}}
                        <td class="px-4 py-3 text-gray-600 text-xs font-mono">{{ $b['emel'] }}</td>

                        {{-- Unit --}}
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $b['unit'] ?: '—' }}</td>

                        {{-- Peranan --}}
                        <td class="px-4 py-3 text-center">
                            @php
                                $pCls = match($b['peranan']) {
                                    'pentadbir_sistem' => 'bg-purple-100 text-purple-700',
                                    'urus_setia'       => 'bg-blue-100 text-blue-700',
                                    default            => 'bg-gray-100 text-gray-600',
                                };
                                $pLabel = match($b['peranan']) {
                                    'pentadbir_sistem' => 'Pentadbir',
                                    'urus_setia'       => 'Urus Setia',
                                    default            => 'Staf',
                                };
                            @endphp
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $pCls }}">{{ $pLabel }}</span>
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3 text-center">
                            @php
                                $sCls = match($b['status']) {
                                    'baru'        => 'bg-green-100 text-green-700',
                                    'tidak_aktif' => 'bg-amber-100 text-amber-700',
                                    'aktif'       => 'bg-blue-100 text-blue-700',
                                    'duplikat'    => 'bg-purple-100 text-purple-700',
                                    'ralat'       => 'bg-red-100 text-red-700',
                                    default       => 'bg-gray-100 text-gray-500',
                                };
                                $sIkon = match($b['status']) {
                                    'baru'        => 'fa-user-plus',
                                    'tidak_aktif' => 'fa-user-clock',
                                    'aktif'       => 'fa-circle-check',
                                    'duplikat'    => 'fa-copy',
                                    'ralat'       => 'fa-circle-xmark',
                                    default       => 'fa-circle',
                                };
                                $sLabel = match($b['status']) {
                                    'baru'        => 'Baru',
                                    'tidak_aktif' => 'Akan Diaktif',
                                    'aktif'       => 'Sudah Aktif',
                                    'duplikat'    => 'Duplikat',
                                    'ralat'       => 'Ralat',
                                    default       => '—',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full {{ $sCls }}"
                                  title="{{ $b['mesej'] }}">
                                <i class="fa-solid {{ $sIkon }} text-[10px]" aria-hidden="true"></i>
                                {{ $sLabel }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Nota di bawah jadual --}}
    @if($jumlahBaru > 0)
    <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-xs text-green-700 flex items-start gap-2">
        <i class="fa-solid fa-key mt-0.5 flex-shrink-0" aria-hidden="true"></i>
        <span>Kata laluan lalai bagi pengguna baru yang akan dicipta: <strong>iBook@{{ date('Y') }}</strong> — sila maklumkan kepada pengguna berkenaan untuk tukar kata laluan selepas log masuk pertama.</span>
    </div>
    @endif

</form>

@endif {{-- pratonton --}}

@push('scripts')
<script nonce="{{ $cspNonce }}">
(function () {
    // ── Dropzone CSV ──
    const input  = document.getElementById('csv_fail');
    const zone   = document.getElementById('csv-dropzone');
    const ph     = document.getElementById('csv-placeholder');
    const sel    = document.getElementById('csv-selected');
    const namaFail = document.getElementById('csv-nama-fail');

    if (input) {
        input.addEventListener('change', function () {
            const f = this.files[0];
            if (!f) return;
            if (namaFail) namaFail.textContent = f.name + ' (' + (f.size / 1024).toFixed(1) + ' KB)';
            if (ph) ph.classList.add('hidden');
            if (sel) sel.classList.remove('hidden');
            if (zone) {
                zone.classList.remove('border-gray-300');
                zone.classList.add('border-green-400', 'bg-green-50');
            }
        });
    }

    // ── Pilih semua + kiraan ──
    const pilihSemua = document.getElementById('pilih-semua');
    const btnProses  = document.getElementById('btn-proses');
    const kiraanEl   = document.getElementById('kiraan-dipilih');

    function kemasSKini() {
        const checkboxes = document.querySelectorAll('.checkbox-baris');
        const dipilih    = document.querySelectorAll('.checkbox-baris:checked').length;

        if (kiraanEl) {
            kiraanEl.textContent = dipilih > 0 ? dipilih + ' dipilih' : '';
        }
        if (btnProses) {
            btnProses.disabled = dipilih === 0;
        }
        if (pilihSemua) {
            pilihSemua.indeterminate = dipilih > 0 && dipilih < checkboxes.length;
            pilihSemua.checked = dipilih === checkboxes.length && checkboxes.length > 0;
        }
    }

    if (pilihSemua) {
        pilihSemua.addEventListener('change', function () {
            document.querySelectorAll('.checkbox-baris').forEach(cb => {
                cb.checked = this.checked;
            });
            kemasSKini();
        });
    }

    document.querySelectorAll('.checkbox-baris').forEach(cb => {
        cb.addEventListener('change', kemasSKini);
    });

    kemasSKini();

    // ── Confirm sebelum proses ──
    const formProses = document.getElementById('form-proses');
    if (formProses) {
        formProses.addEventListener('submit', function (e) {
            const dipilih = document.querySelectorAll('.checkbox-baris:checked').length;
            if (!confirm(dipilih + ' pengguna akan diproses. Teruskan?')) {
                e.preventDefault();
            }
        });
    }
})();
</script>
@endpush

@endsection
