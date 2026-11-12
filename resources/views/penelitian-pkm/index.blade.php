@extends('layouts.app')

@section('meta_title', $halaman)
@section('title', $halaman)

@section('subtitle')
    @if(!$apiError){{ number_format($pagination['total'], 0, ',', '.') }} Data ditemukan @endif
@endsection

@section('content')
@php
    $startNumber = $pagination['from'] ?? 1;
    $selectedStatus = $filters['status_kegiatan'] ?? '';
    $hasFilter = filled($filters['search'] ?? null) || $selectedStatus !== '';
    $statusOptions = ['Pengajuan', 'Berjalan', 'Selesai', 'Ditolak'];
    $th = '!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300';
    $td = '!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm text-gray-900';
@endphp

<div class="space-y-5 pb-8">
    @if($apiError)
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700" role="alert">{{ $apiError }}</div>
    @elseif(count($kegiatan) > 0 || $hasFilter)
        <form method="GET" action="{{ route($rute . '.index') }}" id="kegiatan-filter-form" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex flex-1 flex-wrap items-center gap-2">
                <div class="flex h-[34px] w-[248px] max-w-full shrink-0 overflow-hidden rounded-md border border-primary bg-white-form p-[2px]">
                    <label class="sr-only" for="kegiatan-search">Cari nama</label>
                    <input id="kegiatan-search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama"
                        class="min-w-0 flex-1 border-0 bg-transparent px-3 py-1 text-xs text-gray-900 placeholder:text-gray-600 focus:outline-none focus:ring-0">
                    <button type="submit" class="inline-flex h-full w-[34px] shrink-0 items-center justify-center rounded-[5px] text-black" aria-label="Cari">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </button>
                </div>

                <input type="hidden" name="status_kegiatan" value="{{ $selectedStatus }}">
                <div class="relative">
                    <button type="button" class="flex h-[34px] w-[150px] shrink-0 items-center justify-between overflow-hidden rounded-md border border-primary bg-white-form text-left text-[11px] text-gray-900" data-filter-trigger>
                        <span class="min-w-0 flex-1 truncate px-2">{{ $selectedStatus !== '' ? $selectedStatus : 'Status' }}</span>
                        <span class="mx-[2px] my-[2px] flex h-[26px] w-7 shrink-0 items-center justify-center rounded-[5px] border border-primary bg-[#ffc800]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </button>
                    <div class="absolute left-0 top-full z-20 mt-1 hidden w-full rounded-md border border-primary bg-white-form p-1.5 shadow-sm" data-filter-menu>
                        <button type="button" class="block w-full rounded px-1.5 py-0.5 text-left text-[10px] text-gray-700 hover:underline" data-filter-value="">All Data</button>
                        @foreach($statusOptions as $status)
                            <button type="button" class="block w-full rounded px-1.5 py-0.5 text-left text-[10px] text-gray-700 hover:underline" data-filter-value="{{ $status }}">{{ $status }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($canManage)
                <a href="{{ route($rute . '.create') }}" class="inline-flex h-[34px] shrink-0 items-center justify-center gap-1.5 rounded-md bg-additional px-3 text-xs font-bold text-white transition hover:bg-primary">
                    <span class="inline-flex h-3.5 w-3.5 items-center justify-center rounded-full bg-white text-additional">
                        <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" />
                        </svg>
                    </span>
                    Tambah {{ $halaman }}
                </a>
            @endif
        </form>

        @if(count($kegiatan) > 0)
            <x-table class="!border-collapse !border-spacing-0 text-left text-[13px]">
                <x-slot:header>
                    <x-table.th class="{{ $th }} !text-center">No</x-table.th>
                    <x-table.th class="{{ $th }}"> Judul {{ $halaman }}</x-table.th>
                    <x-table.th class="{{ $th }}">Dosen</x-table.th>
                    <x-table.th class="{{ $th }}">Jenis {{ $halaman }}ian</x-table.th>
                    <x-table.th class="{{ $th }}">Status</x-table.th>
                    <x-table.th class="{{ $th }}">Tahun Kegiatan</x-table.th>
                    <x-table.th class="{{ $th }} !text-center">Action</x-table.th>
                </x-slot:header>

                @foreach($kegiatan as $index => $item)
                    <x-table.row class="bg-white transition-colors hover:bg-gray-50/40">
                        <x-table.cell class="{{ $td }} text-center">{{ $startNumber + $index }}.</x-table.cell>
                        <x-table.cell class="{{ $td }} font-medium leading-[1.2]">{{ $item['judul_kegiatan'] ?? '-' }}</x-table.cell>
                        <x-table.cell class="{{ $td }}">{{ $item['ketua'] }}</x-table.cell>
                        <x-table.cell class="{{ $td }}">{{ filled($item['jenis_skim'] ?? null) ? $item['jenis_skim'] : ($item['jenis_kegiatan']['nama_jenis_kegiatan'] ?? '-') }}</x-table.cell>
                        <x-table.cell class="{{ $td }}">{{ $item['status_kegiatan'] ?? '-' }}</x-table.cell>
                        <x-table.cell class="{{ $td }}">{{ $item['tahun_kegiatan'] ?? '-' }}</x-table.cell>
                        <x-table.cell class="{{ $td }}">
                            <div class="flex items-center justify-center gap-4">
                                @if($canManage)
                                    <a href="{{ route($rute . '.edit', $item['id_kegiatan']) }}" class="text-amber-500 transition hover:text-amber-700" title="Edit kegiatan" aria-label="Edit kegiatan">
                                        <x-icon.edit class="h-5 w-5" />
                                    </a>
                                    <form method="POST" action="{{ route($rute . '.destroy', $item['id_kegiatan']) }}" data-confirm-delete data-title="Hapus Data {{ $halaman }}?" data-message="Apakah Anda yakin ingin menghapus data {{ $halaman }} ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 transition hover:text-red-800" title="Hapus kegiatan" aria-label="Hapus kegiatan">
                                            <x-icon.delete class="h-5 w-5" />
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route($rute . '.show', $item['id_kegiatan']) }}" class="text-primary transition hover:text-additional" title="Lihat detail" aria-label="Lihat detail">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.5 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </a>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            <x-pagination :paginator="$pagination" />
        @else
            <p class="py-10 text-center text-sm text-gray-600">Data tidak ditemukan.</p>
        @endif

        <script>
            (function () {
                const form = document.getElementById('kegiatan-filter-form');
                const trigger = form.querySelector('[data-filter-trigger]');
                const menu = form.querySelector('[data-filter-menu]');
                const input = form.querySelector('[name="status_kegiatan"]');

                trigger.addEventListener('click', () => menu.classList.toggle('hidden'));
                menu.querySelectorAll('[data-filter-value]').forEach((option) => {
                    option.addEventListener('click', () => {
                        input.value = option.dataset.filterValue;
                        form.requestSubmit();
                    });
                });
                document.addEventListener('click', (event) => {
                    if (!form.contains(event.target)) menu.classList.add('hidden');
                });
            })();
        </script>
    @else
        <div class="flex min-h-[420px] flex-col items-center justify-center rounded-2xl border border-gray-100 bg-white px-5 py-12 text-center">
            <h2 class="text-2xl font-extrabold tracking-tight text-black sm:text-3xl">Belum Ada {{ $halaman }}</h2>
            @if($canManage)
                <p class="mt-1 text-sm text-gray-600">Tekan tombol di bawah untuk menambah data</p>
                <a href="{{ route($rute . '.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-additional px-6 py-2.5 text-xs font-semibold text-white transition hover:bg-primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah {{ $halaman }}
                </a>
            @else
                <p class="mt-1 text-sm text-gray-600">Belum ada data kegiatan.</p>
            @endif
        </div>
    @endif
</div>
@endsection