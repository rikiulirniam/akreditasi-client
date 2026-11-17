@extends('layouts.app')

@section('browser-title', 'Luaran')

@section('title')
    <span class="inline-flex items-center gap-2.5 sm:gap-3.5 flex-wrap">
        <span>Luaran</span>
        <svg class="h-5 w-5 shrink-0 stroke-[3] text-black sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <span>{{ $kategoriLabel }}</span>
    </span>
@endsection

@section('subtitle')
    @if(!$apiError)
        {{ number_format((int) ($pagination['total'] ?? count($rows)), 0, ',', '.') }} Data ditemukan
    @endif
@endsection

@section('content')
@php
    $startNumber = (int) ($pagination['from'] ?? (((int) ($pagination['current_page'] ?? 1) - 1) * (int) ($pagination['per_page'] ?? 10) + 1));
    $hasActiveFilters = array_filter([
        $filters['search'] ?? null,
        $filters['tahun'] ?? null,
        $filters['id_kegiatan'] ?? null,
    ], static fn ($value) => $value !== null && $value !== '') !== [];
    $yearOptions = range((int) date('Y') + 1, 2000);
    $selectedYear = (string) ($filters['tahun'] ?? '');
    $selectClass = 'h-[34px] rounded-md border border-primary bg-white-form px-2 text-[11px] text-gray-900 focus:outline-none focus:ring-0';
    $exportQuery = array_filter(\Illuminate\Support\Arr::except($filters, ['page']), static fn ($value) => $value !== null && $value !== '');
    $luaranRoute = request()->routeIs('luaran.tridharma.index') ? 'luaran.tridharma.index' : 'luaran.index';
    $exportRoute = request()->routeIs('luaran.tridharma.index') ? 'luaran.tridharma.export' : 'luaran.export';
@endphp

<div class="space-y-5 pb-8">
    @if($apiError)
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700" role="alert">
            {{ $apiError }}
        </div>
    @else
        <form method="GET" action="{{ route($luaranRoute, request()->routeIs('luaran.tridharma.index') ? ['jenis' => $jenis, 'kategori' => $kategori] : ['kategori' => $kategori]) }}" class="flex flex-wrap items-center gap-2" id="luaran-filter-form">
            <div class="flex h-[34px] w-[248px] max-w-full shrink-0 overflow-hidden rounded-md border border-primary bg-white-form p-[2px]">
                <label class="sr-only" for="luaran-search">Cari {{ $kategoriLabel }}</label>
                <input
                    id="luaran-search"
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Cari judul"
                    class="min-w-0 flex-1 border-0 bg-transparent px-3 py-1 text-xs text-gray-900 placeholder:text-gray-600 focus:outline-none focus:ring-0"
                >
                <button type="submit" class="inline-flex h-full w-[34px] shrink-0 items-center justify-center rounded-[5px] border border-transparent text-black" aria-label="Cari">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </div>

            <label class="sr-only" for="luaran-kategori">Kategori luaran</label>
            <select
                id="luaran-kategori"
                class="{{ $selectClass }} w-[140px]"
                onchange="this.form.action = this.value; this.form.requestSubmit()"
            >
                @foreach($tabs as $slug => $label)
                    <option
                        value="{{ route($luaranRoute, request()->routeIs('luaran.tridharma.index') ? ['jenis' => $jenis, 'kategori' => $slug] : ['kategori' => $slug]) }}"
                        @selected($slug === $kategori)
                    >{{ $label }}</option>
                @endforeach
            </select>

            <label class="sr-only" for="luaran-tahun">Tahun</label>
            <select id="luaran-tahun" name="tahun" class="{{ $selectClass }} w-[120px]" onchange="this.form.requestSubmit()">
                <option value="">Semua Tahun</option>
                @foreach($yearOptions as $year)
                    <option value="{{ $year }}" @selected($selectedYear === (string) $year)>{{ $year }}</option>
                @endforeach
            </select>

            @if($hasActiveFilters)
                <a href="{{ route($luaranRoute, request()->routeIs('luaran.tridharma.index') ? ['jenis' => $jenis, 'kategori' => $kategori] : ['kategori' => $kategori]) }}" class="text-xs font-semibold text-gray-600 underline hover:text-additional">Reset</a>
            @endif

            <a href="{{ route('luaran.create', ['jenis' => $jenis, 'kategori' => $kategori, 'id_kegiatan' => $filters['id_kegiatan'] ?? null]) }}" class="ml-auto inline-flex h-[34px] items-center rounded-md bg-primary px-4 text-xs font-bold text-white transition hover:bg-additional">Tambah Luaran</a>

            <a href="{{ route($exportRoute, (request()->routeIs('luaran.tridharma.index') ? ['jenis' => $jenis, 'kategori' => $kategori] : ['kategori' => $kategori]) + $exportQuery) }}" class="inline-flex h-[34px] shrink-0 items-center justify-center gap-1.5 rounded-md bg-additional px-3 text-xs font-bold text-white transition hover:bg-primary" data-export-excel>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download Excel
            </a>
        </form>


        @if(count($rows) > 0)
            <x-table id="luaran-table" class="!border-collapse !border-spacing-0 text-left text-[13px]">
                <x-slot:header>
                    <x-table.th class="!rounded-none !bg-white !px-3 !py-2.5 !text-center !text-[13px] !font-bold border-y border-gray-300">No</x-table.th>
                    @foreach($headers as $header)
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">{{ $header }}</x-table.th>
                    @endforeach
                    <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-center !text-[13px] !font-bold border-y border-gray-300">Detail</x-table.th>
                </x-slot:header>

                @foreach($rows as $index => $row)
                    <x-table.row class="bg-white transition-colors hover:bg-gray-50/40">
                        <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-3 !py-3.5 text-center text-sm text-gray-900">{{ $startNumber + $index }}.</x-table.cell>
                        @foreach($row['cells'] as $cellIndex => $cell)
                            <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm text-gray-900 {{ $cellIndex === 1 ? 'font-medium' : '' }}">{{ $cell }}</x-table.cell>
                        @endforeach
                        <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 text-center">
                            @if($row['id'])
                                <a href="{{ route('luaran.tridharma.show', ['jenis' => $jenis, 'kategori' => $kategori, 'id' => $row['id']]) }}" class="text-primary transition hover:text-additional" title="Lihat detail" aria-label="Lihat detail">
                                    <svg class="mx-auto h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.5 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </a>
                            @endif
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            <x-pagination :paginator="$pagination" />
        @else
            <div class="rounded-2xl border border-gray-100 bg-white px-5 py-12 text-center text-sm text-gray-600">
                {{ $hasActiveFilters ? 'Tidak ada data '.$kategoriLabel.' yang cocok dengan filter.' : 'Belum ada data '.$kategoriLabel.'.' }}
            </div>
        @endif
    @endif
</div>
@endsection
