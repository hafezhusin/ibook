@props([
    'type'    => 'success',  // success | error | warning | info
    'message' => null,
    'dismissible' => true,
])

@php
    $config = match($type) {
        'success' => ['bg' => 'bg-green-50',  'border' => 'border-green-400', 'text' => 'text-green-800', 'ikon' => 'fa-circle-check'],
        'error'   => ['bg' => 'bg-red-50',    'border' => 'border-red-400',   'text' => 'text-red-800',   'ikon' => 'fa-circle-xmark'],
        'warning' => ['bg' => 'bg-amber-50',  'border' => 'border-amber-400', 'text' => 'text-amber-800', 'ikon' => 'fa-triangle-exclamation'],
        'info'    => ['bg' => 'bg-blue-50',   'border' => 'border-blue-400',  'text' => 'text-blue-800',  'ikon' => 'fa-circle-info'],
        default   => ['bg' => 'bg-gray-50',   'border' => 'border-gray-400',  'text' => 'text-gray-800',  'ikon' => 'fa-circle-info'],
    };
    $teks = $message ?? $slot;
@endphp

@if($teks)
<div role="alert"
     {{ $attributes->merge(['class' => "flex items-start gap-3 border-l-4 rounded-lg px-4 py-3 text-sm {$config['bg']} {$config['border']} {$config['text']}"]) }}>
    <i class="fa-solid {{ $config['ikon'] }} mt-0.5 flex-shrink-0" aria-hidden="true"></i>
    <span class="flex-1">{{ $teks }}</span>
    @if($dismissible)
    <button type="button" onclick="this.closest('[role=alert]').remove()"
            class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity"
            aria-label="Tutup notifikasi">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </button>
    @endif
</div>
@endif
