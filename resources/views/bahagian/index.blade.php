@extends('layouts.app')

@section('title', 'Bahagian')

@section('content')
<div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Bahagian</h1>
        <p class="text-gray-500 text-sm mt-1">
            Urus bahagian/jabatan yang menyertai sistem iBook.
            <span class="inline-flex items-center gap-1 ml-2 text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium">
                <i class="fa-solid fa-lock text-xs" aria-hidden="true"></i>
                Paparan pentadbir sistem sahaja
            </span>
        </p>
    </div>
    <a href="{{ route('bahagian.create') }}" class="btn-primary flex-shrink-0">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> Tambah Bahagian
    </a>
</div>

{{-- Master switch status --}}
@php $crossMaster = \App\Models\Tetapan::get('cross_booking_aktif', '0') === '1'; @endphp
<div class="mb-5 p-4 rounded-xl border flex items-start gap-3
    {{ $crossMaster ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200' }}">
    <i class="fa-solid {{ $crossMaster ? 'fa-toggle-on text-green-500' : 'fa-toggle-off text-amber-500' }} text-xl mt-0.5 flex-shrink-0" aria-hidden="true"></i>
    <div>
        <p class="text-sm font-semibold {{ $crossMaster ? 'text-green-800' : 'text-amber-800' }}">
            Cross-Booking: <strong>{{ $crossMaster ? 'DIAKTIFKAN' : 'DIMATIKAN' }}</strong>
        </p>
        <p class="text-xs {{ $crossMaster ? 'text-green-700' : 'text-amber-700' }} mt-0.5">
            @if($crossMaster)
                Staf boleh melihat dan membooking bilik bahagian lain yang telah diaktifkan cross-booking.
            @else
                Staf hanya nampak bilik bahagian sendiri. Aktifkan master switch dalam
                <a href="{{ route('tetapan.index') }}" class="underline font-medium">Tetapan</a>
                apabila pengurusan atasan telah memberi kelulusan.
            @endif
        </p>
    </div>
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

{{-- Jadual bahagian --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm" aria-label="Senarai bahagian">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50 text-left">
                <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">Kod</th>
                <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama & Lokasi</th>
                <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center w-24">Bilik</th>
                <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center w-28">Status</th>
                <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center w-36">Cross-Booking</th>
                <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right w-28">Tindakan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($bahagian as $b)
            <tr class="hover:bg-gray-50 transition-colors {{ !$b->aktif ? 'opacity-60' : '' }}">
                {{-- Kod --}}
                <td class="px-5 py-4">
                    <span class="font-mono font-bold text-gray-800 text-xs px-2 py-1 bg-gray-100 rounded">{{ $b->kod }}</span>
                </td>

                {{-- Nama & lokasi --}}
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $b->nama }}</p>
                    @if($b->lokasi)
                    <p class="text-xs text-gray-400 mt-0.5">
                        <i class="fa-solid fa-location-dot mr-1" aria-hidden="true"></i>{{ $b->lokasi }}
                    </p>
                    @endif
                    @if($b->emel)
                    <p class="text-xs text-gray-400 mt-0.5">
                        <i class="fa-solid fa-envelope mr-1" aria-hidden="true"></i>{{ $b->emel }}
                    </p>
                    @endif
                </td>

                {{-- Bilangan bilik --}}
                <td class="px-5 py-4 text-center">
                    <span class="font-semibold text-gray-700">{{ $b->bilik_aktif_count }}</span>
                    <span class="text-gray-400 text-xs">/ {{ $b->bilik_count }}</span>
                </td>

                {{-- Toggle Aktif --}}
                <td class="px-5 py-4 text-center">
                    <form method="POST" action="{{ route('bahagian.toggle-aktif', $b) }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full transition-colors
                                {{ $b->aktif
                                    ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                    : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}"
                            title="{{ $b->aktif ? 'Klik untuk nyahaktif' : 'Klik untuk aktifkan' }}"
                            aria-label="{{ $b->aktif ? 'Nyahaktif' : 'Aktifkan' }} bahagian {{ $b->kod }}">
                            <i class="fa-solid {{ $b->aktif ? 'fa-circle-check' : 'fa-circle-xmark' }}" aria-hidden="true"></i>
                            {{ $b->aktif ? 'Aktif' : 'Tidak Aktif' }}
                        </button>
                    </form>
                </td>

                {{-- Toggle Cross-Booking --}}
                <td class="px-5 py-4 text-center">
                    <form method="POST" action="{{ route('bahagian.toggle-cross-booking', $b) }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full transition-colors
                                {{ $b->cross_booking_aktif
                                    ? 'bg-blue-100 text-blue-700 hover:bg-blue-200'
                                    : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}"
                            title="{{ $b->cross_booking_aktif ? 'Cross-booking diaktifkan — klik untuk matikan' : 'Cross-booking dimatikan — klik untuk aktifkan' }}"
                            aria-label="Togol cross-booking bahagian {{ $b->kod }}">
                            <i class="fa-solid {{ $b->cross_booking_aktif ? 'fa-toggle-on' : 'fa-toggle-off' }}" aria-hidden="true"></i>
                            {{ $b->cross_booking_aktif ? 'Dibenarkan' : 'Tertutup' }}
                        </button>
                    </form>
                </td>

                {{-- Tindakan --}}
                <td class="px-5 py-4 text-right">
                    <a href="{{ route('bahagian.edit', $b) }}"
                        class="inline-flex items-center gap-1 text-xs text-amber-600 hover:text-amber-800 font-medium px-2 py-1 rounded hover:bg-amber-50 transition-colors"
                        aria-label="Edit bahagian {{ $b->kod }}">
                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Edit
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                    <i class="fa-solid fa-building-circle-exclamation text-3xl mb-3 block" aria-hidden="true"></i>
                    Tiada bahagian didaftarkan lagi.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Nota --}}
<div class="mt-5 p-4 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-700 space-y-1">
    <p><i class="fa-solid fa-circle-info mr-1.5" aria-hidden="true"></i><strong>Cross-Booking per-bahagian</strong> — hanya berkuat kuasa apabila master switch dalam <a href="{{ route('tetapan.index') }}" class="underline">Tetapan</a> turut diaktifkan.</p>
    <p><i class="fa-solid fa-eye-slash mr-1.5" aria-hidden="true"></i>Halaman ini <strong>tidak kelihatan</strong> kepada staf biasa — hanya pentadbir sistem yang dapat mengaksesnya.</p>
</div>

@endsection
