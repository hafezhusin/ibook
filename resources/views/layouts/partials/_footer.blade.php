{{-- ── Footer ────────────────────────────────────────────────────────── --}}
@php
    $namaBahagian  = $tetapan['nama_jabatan'] ?? '';
    $emelPentadbir = $tetapan['emel_pentadbir'] ?? '';
    $tahunSemasa   = date('Y');
@endphp
@if($namaBahagian || $emelPentadbir)
<footer class="border-t border-gray-200 px-6 py-4 text-center"
        style="background:#f9fafb"
        role="contentinfo">
    <div class="flex flex-col sm:flex-row items-center justify-center gap-2 text-xs text-gray-400">
        @if($namaBahagian)
        <span>Hak Cipta &copy; {{ $namaBahagian }} {{ $tahunSemasa }}</span>
        @endif
        @if($namaBahagian && $emelPentadbir)
        <span class="hidden sm:inline text-gray-300" aria-hidden="true">|</span>
        @endif
        @if($emelPentadbir)
        <span>
            <i class="fa-solid fa-envelope text-gray-300 mr-1" aria-hidden="true"></i>
            <a href="mailto:{{ $emelPentadbir }}"
               class="hover:text-amber-500 transition-colors"
               aria-label="Hubungi pentadbir sistem">{{ $emelPentadbir }}</a>
        </span>
        @endif
    </div>
</footer>
@endif
