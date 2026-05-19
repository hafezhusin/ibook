@props(['status'])

@php
    $warna = match($status) {
        'diluluskan' => 'bg-green-100 text-green-700',
        'ditolak'    => 'bg-red-100 text-red-700',
        default      => 'bg-gray-100 text-gray-600',
    };
    $ikon = match($status) {
        'diluluskan' => 'fa-circle-check',
        'ditolak'    => 'fa-ban',
        default      => 'fa-circle',
    };
    $label = match($status) {
        'diluluskan' => 'Sah',
        'ditolak'    => 'Ditolak',
        default      => ucfirst($status),
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full $warna"]) }}>
    <i class="fa-solid {{ $ikon }}" aria-hidden="true"></i>
    {{ $label }}
</span>
