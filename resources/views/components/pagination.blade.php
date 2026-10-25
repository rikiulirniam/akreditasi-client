@props([
    'paginator' => null,
    'total' => null,
    'perPage' => 10,
    'currentPage' => 1,
    'lastPage' => 1,
    'from' => null,
    'to' => null,
    'alwaysShow' => false,
    'showTotal' => true,
])

@php
    if ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator) {
        $total = $paginator->total();
        $perPage = $paginator->perPage();
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $from = $paginator->firstItem() ?? 0;
        $to = $paginator->lastItem() ?? 0;
    } elseif (is_array($paginator)) {
        $total = $paginator['total'] ?? ($total ?? 0);
        $perPage = $paginator['per_page'] ?? ($perPage ?? 10);
        $currentPage = $paginator['current_page'] ?? ($currentPage ?? 1);
        $lastPage = $paginator['last_page'] ?? ($lastPage ?? 1);
        $from = $paginator['from'] ?? null;
        $to = $paginator['to'] ?? null;
    }

    $total = (int) ($total ?? 0);
    $perPage = max((int) ($perPage ?? 10), 1);
    $currentPage = max((int) ($currentPage ?? 1), 1);
    $lastPage = max((int) ($lastPage ?? 1), 1);

    if ($from === null) {
        $from = $total > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
    }
    if ($to === null) {
        $to = $total > 0 ? min($currentPage * $perPage, $total) : 0;
    }

    $fromFormatted = $total > 0 ? sprintf('%02d', $from) : '0';
    $toFormatted = $total > 0 ? sprintf('%02d', $to) : '0';

    $buildUrl = function (int $page) {
        return request()->fullUrlWithQuery(['page' => $page]);
    };

    $startPage = max(1, $currentPage - 2);
    $endPage = min($lastPage, $currentPage + 3);
    if ($endPage - $startPage < 4 && $lastPage >= 5) {
        if ($startPage === 1) {
            $endPage = min(5, $lastPage);
        } elseif ($endPage === $lastPage) {
            $startPage = max(1, $lastPage - 4);
        }
    }
@endphp

<div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 text-xs sm:text-[13px] text-black">
    <div>
        @if($total > 0)
            <span>Menampilkan {{ $fromFormatted }}–{{ $toFormatted }} Data{{ $showTotal ? ' dari ' . $total : '' }}</span>
        @else
            <span>Menampilkan 0 dari 0 data</span>
        @endif
    </div>

    @if($lastPage > 1 || ($alwaysShow && $total > 0))
        <div class="flex items-center gap-1.5">
            @if($currentPage > 1)
                <a 
                    href="{{ $buildUrl($currentPage - 1) }}" 
                    class="p-1 text-primary hover:text-additional transition"
                    aria-label="Halaman Sebelumnya"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </a>
            @else
                <span class="p-1 text-gray-300 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </span>
            @endif

            @if($startPage > 1)
                <a href="{{ $buildUrl(1) }}" class="px-1.5 py-0.5 font-medium text-gray-700 hover:text-primary transition">1</a>
                @if($startPage > 2)
                    <span class="px-1 text-gray-400">...</span>
                @endif
            @endif

            @for($p = $startPage; $p <= $endPage; $p++)
                @if($p === $currentPage)
                    <span class="w-6 h-6 rounded-full bg-[#01568D] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                        {{ $p }}
                    </span>
                @else
                    <a href="{{ $buildUrl($p) }}" class="px-1.5 py-0.5 font-medium text-gray-700 hover:text-primary transition">
                        {{ $p }}
                    </a>
                @endif
            @endfor

            @if($endPage < $lastPage)
                @if($endPage < $lastPage - 1)
                    <span class="px-1 text-gray-400">...</span>
                @endif
                <a href="{{ $buildUrl($lastPage) }}" class="px-1.5 py-0.5 font-medium text-gray-700 hover:text-primary transition">{{ $lastPage }}</a>
            @endif

            @if($currentPage < $lastPage)
                <a 
                    href="{{ $buildUrl($currentPage + 1) }}" 
                    class="p-1 text-primary hover:text-additional transition"
                    aria-label="Halaman Selanjutnya"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            @else
                <span class="p-1 text-gray-300 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
            @endif
        </div>
    @endif
</div>
