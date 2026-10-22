@extends('layouts.app')

@section('browser-title', 'Sertifikasi')

@section('title', 'Sertifikasi')

@section('subtitle')
    @if(!$apiError)
        {{ number_format((int) ($pagination['total'] ?? 0), 0, ',', '.') }} Data ditemukan
    @endif
@endsection

@section('content')
@php
    $ownerQuery = array_filter([
        'tipe_pemilik' => $filters['tipe_pemilik'] ?? null,
        'id_dosen' => $filters['id_dosen'] ?? null,
        'id_tendik' => $filters['id_tendik'] ?? null,
    ], static fn ($value) => $value !== null && $value !== '');
    $createUrl = route('sertifikasi.create', $ownerQuery);
    $startNumber = (int) ($pagination['from'] ?? (((int) ($pagination['current_page'] ?? 1) - 1) * (int) ($pagination['per_page'] ?? 10) + 1));
    $hasActiveFilters = array_filter([
        $filters['search'] ?? null,
        $filters['tahun_sertifikasi'] ?? null,
        $filters['status_sertifikasi'] ?? null,
    ], static fn ($value) => $value !== null && $value !== '') !== [];
    $currentYear = (int) date('Y');
    $yearOptions = range($currentYear + 1, 1900);
    $selectedYear = (string) ($filters['tahun_sertifikasi'] ?? '');
    if ($selectedYear !== '' && ctype_digit($selectedYear) && ! in_array((int) $selectedYear, $yearOptions, true)) {
        $yearOptions[] = (int) $selectedYear;
        rsort($yearOptions);
    }
    $selectedStatus = (string) ($filters['status_sertifikasi'] ?? '');
@endphp

<div class="space-y-5 pb-8">
    @if($apiError)
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700" role="alert">
            {{ $apiError }}
        </div>
    @else
        @if(count($certifications) > 0 || $hasActiveFilters)
        <form method="GET" action="{{ route('sertifikasi.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center" id="sertifikasi-filter-form">
            @foreach($ownerQuery as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="flex flex-1 flex-wrap items-center gap-2">
                <div class="group flex h-[34px] w-[248px] max-w-full shrink-0 overflow-hidden rounded-md border border-primary bg-white-form p-[2px]">
                    <label class="sr-only" for="sertifikasi-search">Cari sertifikasi</label>
                    <input
                        id="sertifikasi-search"
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Cari sertifikasi"
                        class="min-w-0 flex-1 border-0 bg-transparent px-3 py-1 text-xs text-gray-900 placeholder:text-gray-600 focus:outline-none focus:ring-0"
                    >
                    <button type="submit" class="inline-flex h-full w-[34px] shrink-0 items-center justify-center rounded-[5px] text-black transition {{ filled($filters['search'] ?? null) ? 'border border-primary bg-[#ffc800]' : 'border border-transparent bg-transparent' }}" data-search-button aria-label="Cari">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </button>
                </div>

                <input type="hidden" name="tahun_sertifikasi" value="{{ $selectedYear }}" id="sertifikasi-year-value">
                <div class="relative">
                    <button type="button" class="flex h-[34px] w-[140px] shrink-0 items-center justify-between overflow-hidden rounded-md border border-primary bg-white-form text-left text-[11px] text-gray-900" data-filter-trigger aria-expanded="false" aria-controls="sertifikasi-year-menu">
                        <span class="min-w-0 flex-1 truncate px-2">{{ $selectedYear !== '' ? $selectedYear : 'Tahun Sertifikasi' }}</span>
                        <span class="mx-[2px] my-[2px] flex h-[26px] w-7 shrink-0 items-center justify-center rounded-[5px] border border-primary bg-[#ffc800]">
                            <svg class="h-4 w-4 transition-transform" data-filter-chevron fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </button>
                    <div id="sertifikasi-year-menu" class="absolute left-0 top-full z-20 mt-1 hidden max-h-40 w-full grid-cols-2 overflow-y-auto rounded-md border border-primary bg-white-form p-1.5 shadow-sm" data-filter-menu>
                        <button type="button" class="col-span-2 block w-full rounded px-1.5 py-0.5 text-left text-[10px] text-gray-700 hover:text-gray-950 hover:underline" data-filter-value="" data-filter-label="Tahun Sertifikasi">All Data</button>
                        @foreach($yearOptions as $year)
                            <button type="button" class="rounded px-1.5 py-0.5 text-left text-[10px] text-gray-700 hover:text-gray-950 hover:underline" data-filter-value="{{ $year }}" data-filter-label="{{ $year }}">{{ $year }}</button>
                        @endforeach
                    </div>
                </div>

                <input type="hidden" name="status_sertifikasi" value="{{ $selectedStatus }}" id="sertifikasi-status-value">
                <div class="relative">
                    <button type="button" class="flex h-[34px] w-[150px] shrink-0 items-center justify-between overflow-hidden rounded-md border border-primary bg-white-form text-left text-[11px] text-gray-900" data-filter-trigger aria-expanded="false" aria-controls="sertifikasi-status-menu">
                        <span class="min-w-0 flex-1 truncate px-2">{{ $selectedStatus !== '' ? $selectedStatus : 'Status Sertifikasi' }}</span>
                        <span class="mx-[2px] my-[2px] flex h-[26px] w-7 shrink-0 items-center justify-center rounded-[5px] border border-primary bg-[#ffc800]">
                            <svg class="h-4 w-4 transition-transform" data-filter-chevron fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </button>
                    <div id="sertifikasi-status-menu" class="absolute left-0 top-full z-20 mt-1 hidden w-full rounded-md border border-primary bg-white-form p-1.5 shadow-sm" data-filter-menu>
                        <button type="button" class="block w-full rounded px-1.5 py-0.5 text-left text-[10px] text-gray-700 hover:text-gray-950 hover:underline" data-filter-value="" data-filter-label="Status Sertifikasi">All Data</button>
                        @foreach(['Aktif', 'Kedaluwarsa'] as $status)
                            <button type="button" class="block w-full rounded px-1.5 py-0.5 text-left text-[10px] text-gray-700 hover:text-gray-950 hover:underline" data-filter-value="{{ $status }}" data-filter-label="{{ $status }}">{{ $status }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <a href="{{ $createUrl }}" class="inline-flex h-[34px] shrink-0 items-center justify-center gap-1.5 rounded-md bg-additional px-3 text-xs font-bold text-white transition hover:bg-primary">
                <span class="inline-flex h-3.5 w-3.5 items-center justify-center rounded-full bg-white text-additional">
                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" />
                    </svg>
                </span>
                Tambah Sertifikasi
            </a>
        </form>

            <x-table id="sertifikasi-table" class="!border-collapse !border-spacing-0 text-left text-[13px]">
                <x-slot:header>
                    <x-table.th class="!rounded-none !bg-white !px-3 !py-2.5 !text-center !text-[13px] !font-bold border-y border-gray-300">No</x-table.th>
                    <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Jenis Sertifikasi</x-table.th>
                    <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Nomor Sertifikasi</x-table.th>
                    <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Status Sertifikasi</x-table.th>
                    <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Tahun Sertifikasi</x-table.th>
                    <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-center !text-[13px] !font-bold border-y border-gray-300">Action</x-table.th>
                </x-slot:header>

                @foreach($certifications as $index => $certification)
                    @php
                        $certificationId = $certification['id'] ?? $certification['id_sertifikasi'] ?? null;
                        $certificationType = trim((string) ($certification['jenis_sertifikasi'] ?? '-'));
                        $certificationTypeLines = array_chunk(preg_split('/\s+/', $certificationType) ?: [], 2);
                        $status = $certification['status_sertifikasi'] ?? '-';
                        $year = $certification['tahun_sertifikasi'] ?? '-';
                        $year = preg_match('/^\d{4}/', (string) $year) ? substr((string) $year, 0, 4) : $year;
                    @endphp
                    <x-table.row class="bg-white transition-colors hover:bg-gray-50/40">
                        <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-3 !py-3.5 text-center text-sm text-gray-900">{{ $startNumber + $index }}.</x-table.cell>
                        <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm font-medium leading-[1.2] text-gray-900">
                            @foreach($certificationTypeLines as $line)
                                <span class="block break-words">{{ implode(' ', $line) }}</span>
                            @endforeach
                        </x-table.cell>
                        <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm text-gray-900">{{ $certification['nomor_sertifikat'] ?? '-' }}</x-table.cell>
                        <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm">
                            <span class="font-medium {{ strtolower($status) === 'aktif' ? 'text-emerald-600' : (strtolower($status) === 'kedaluwarsa' ? 'text-red-700' : 'text-gray-700') }}">
                                {{ $status }}
                            </span>
                        </x-table.cell>
                        <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm text-gray-900">{{ $year }}</x-table.cell>
                        <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5">
                            @if($certificationId)
                                <div class="flex items-center justify-center gap-4">
                                    <a href="{{ route('sertifikasi.edit', $certificationId) }}" class="text-amber-500 transition hover:text-amber-700" title="Edit sertifikasi" aria-label="Edit sertifikasi">
                                        <x-icon.edit class="h-5 w-5" />
                                    </a>
                                    <form method="POST" action="{{ route('sertifikasi.destroy', ['sertifikasi' => $certificationId] + request()->only(['search', 'tipe_pemilik', 'id_dosen', 'id_tendik', 'tahun_sertifikasi', 'status_sertifikasi'])) }}" data-confirm-delete data-title="Hapus Sertifikasi?" data-message="Apakah Anda yakin ingin menghapus sertifikasi ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.">
                                        @csrf
                                        @method('DELETE')
                                        @foreach(request()->only(['search', 'tipe_pemilik', 'id_dosen', 'id_tendik', 'tahun_sertifikasi', 'status_sertifikasi']) as $key => $value)
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endforeach
                                        <button type="submit" class="text-red-600 transition hover:text-red-800" title="Hapus sertifikasi" aria-label="Hapus sertifikasi">
                                            <x-icon.delete class="h-5 w-5" />
                                        </button>
                                    </form>
                                    <a href="{{ route('sertifikasi.show', $certificationId) }}" class="text-primary transition hover:text-additional" title="Lihat detail sertifikasi" aria-label="Lihat detail sertifikasi">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.5 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            <x-pagination :paginator="$pagination" />
            <script>
                (function () {
                    const form = document.getElementById('sertifikasi-filter-form');
                    if (!form) return;

                    const searchInput = document.getElementById('sertifikasi-search');
                    const searchButton = form.querySelector('[data-search-button]');
                    if (searchInput && searchButton) {
                        const updateSearchButton = function () {
                            const isSearching = searchInput.value.trim() !== '';
                            searchButton.classList.toggle('bg-[#ffc800]', isSearching);
                            searchButton.classList.toggle('bg-transparent', !isSearching);
                            searchButton.classList.toggle('border-primary', isSearching);
                            searchButton.classList.toggle('border-transparent', !isSearching);
                        };

                        searchInput.addEventListener('input', function () {
                            updateSearchButton();
                        });
                        searchInput.addEventListener('focus', updateSearchButton);
                        searchInput.addEventListener('blur', updateSearchButton);
                    }

                    const closeMenus = function () {
                        form.querySelectorAll('[data-filter-menu]').forEach(function (menu) {
                            menu.classList.add('hidden');
                            menu.classList.remove('grid');
                        });
                        form.querySelectorAll('[data-filter-trigger]').forEach(function (trigger) {
                            trigger.setAttribute('aria-expanded', 'false');
                            trigger.querySelector('[data-filter-chevron]')?.classList.remove('rotate-180');
                        });
                    };

                    form.querySelectorAll('[data-filter-trigger]').forEach(function (trigger) {
                        trigger.addEventListener('click', function () {
                            const menu = document.getElementById(trigger.getAttribute('aria-controls'));
                            const shouldOpen = menu.classList.contains('hidden');
                            closeMenus();
                            if (shouldOpen) {
                                menu.classList.remove('hidden');
                                if (menu.id === 'sertifikasi-year-menu') menu.classList.add('grid');
                                trigger.setAttribute('aria-expanded', 'true');
                                trigger.querySelector('[data-filter-chevron]')?.classList.add('rotate-180');
                            }
                        });
                    });

                    form.querySelectorAll('[data-filter-menu] [data-filter-value]').forEach(function (option) {
                        option.addEventListener('click', function () {
                            const menu = option.closest('[data-filter-menu]');
                            const field = menu.id === 'sertifikasi-year-menu' ? 'tahun_sertifikasi' : 'status_sertifikasi';
                            const input = document.getElementById('sertifikasi-' + (field === 'tahun_sertifikasi' ? 'year' : 'status') + '-value');
                            const trigger = form.querySelector('[aria-controls="' + menu.id + '"]');
                            input.value = option.dataset.filterValue;
                            trigger.querySelector('span').textContent = option.dataset.filterLabel;
                            form.requestSubmit();
                        });
                    });

                    document.addEventListener('click', function (event) {
                        if (!form.contains(event.target)) closeMenus();
                    });
                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') closeMenus();
                    });
                })();
            </script>
        @else
            <div class="flex min-h-[420px] flex-col items-center justify-center rounded-2xl border border-gray-100 bg-white px-5 py-12 text-center">
                <h2 class="text-2xl font-extrabold tracking-tight text-black sm:text-3xl">Belum Ada Data Sertifikasi</h2>
                <p class="mt-1 text-sm text-gray-600">Tekan tombol di bawah untuk menambah data</p>
                <a href="{{ $createUrl }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-additional px-6 py-2.5 text-xs font-semibold text-white transition hover:bg-primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Sertifikasi
                </a>
            </div>
        @endif
    @endif
</div>
@endsection
