@if ($paginator->hasPages())
<nav role="navigation" aria-label="Navigasi halaman"
     class="flex items-center justify-between gap-4 flex-wrap">

    {{-- Maklumat rekod --}}
    <p class="text-sm text-gray-500">
        @if ($paginator->firstItem())
            Paparan rekod
            <strong class="text-gray-700">{{ $paginator->firstItem() }}</strong>
            hingga
            <strong class="text-gray-700">{{ $paginator->lastItem() }}</strong>
            daripada
            <strong class="text-gray-700">{{ $paginator->total() }}</strong>
            rekod
        @else
            {{ $paginator->count() }} rekod
        @endif
    </p>

    {{-- Butang halaman --}}
    <div class="flex items-center gap-1 flex-wrap">

        {{-- Halaman sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-300 bg-white border border-gray-200 rounded-lg cursor-not-allowed select-none"
                  aria-disabled="true">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Sebelum
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-amber-50 hover:border-amber-300 hover:text-amber-700 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
               aria-label="Halaman sebelumnya">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Sebelum
            </a>
        @endif

        {{-- Nombor halaman --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 py-1.5 text-xs text-gray-400 select-none" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-amber-800 bg-amber-100 border-2 border-amber-400 rounded-lg"
                              aria-current="page" aria-label="Halaman {{ $page }}, semasa">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-amber-50 hover:border-amber-300 hover:text-amber-700 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
                           aria-label="Pergi ke halaman {{ $page }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Halaman seterusnya --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-amber-50 hover:border-amber-300 hover:text-amber-700 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
               aria-label="Halaman seterusnya">
                Seterus
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
            </a>
        @else
            <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-300 bg-white border border-gray-200 rounded-lg cursor-not-allowed select-none"
                  aria-disabled="true">
                Seterus
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
            </span>
        @endif
    </div>
</nav>
@endif
