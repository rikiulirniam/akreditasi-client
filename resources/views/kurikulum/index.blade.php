@extends('layouts.app')

@section('title', 'Manajemen Kurikulum')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <!-- Search & Prodi Filter Group -->
            <div class="flex items-center gap-3 flex-1 max-w-xl">
                <!-- Search Input -->
                <form method="GET" action="{{ route('kurikulum.index') }}" id="filterForm" class="flex items-center gap-3 flex-1">
                    <div class="relative flex-1 max-w-sm">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </span>
                        <x-input
                            type="text"
                            name="search"
                            id="searchKurikulumInput"
                            placeholder="Cari kurikulum..."
                            value="{{ $search }}"
                            class="pl-10 text-xs"
                        />
                    </div>

                    <!-- Prodi Filter Dropdown -->
                    @if(!empty($prodiList))
                    <div class="relative w-48">
                        <select
                            name="prodi_id"
                            id="filterProdiSelect"
                            onchange="document.getElementById('filterForm').submit()"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                        >
                            <option value="">Semua Prodi</option>
                            @foreach($prodiList as $prodi)
                                <option value="{{ $prodi['id'] }}" {{ $selectedProdi == $prodi['id'] ? 'selected' : '' }}>
                                    {{ $prodi['nama_prodi'] ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>
                    @endif
                </form>
            </div>

            <!-- Tambah Kurikulum Button -->
            @haspermission('kurikulum.create')
            <div>
                <a
                    href="{{ route('kurikulum.create') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Kurikulum
                </a>
            </div>
            @endhaspermission
        </div>

        <!-- Table -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-12 whitespace-nowrap">No</x-table.th>
                <x-table.th class="whitespace-nowrap">Nama Kurikulum</x-table.th>
                <x-table.th class="whitespace-nowrap">SK Kurikulum</x-table.th>
                <x-table.th class="text-center whitespace-nowrap">Tahun Mulai</x-table.th>
                <x-table.th class="text-center whitespace-nowrap">Berlaku Hingga</x-table.th>
                <x-table.th class="whitespace-nowrap">Prodi</x-table.thz>
                <x-table.th class="text-center whitespace-nowrap">Dokumen</x-table.th>
                <x-table.th class="text-right whitespace-nowrap">Action</x-table.th>
            </x-slot:header>

            @forelse($kurikulumList as $index => $item)
                @php
                    $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                @endphp
                <x-table.row class="kurikulum-row">
                    <x-table.cell class="text-gray-500 font-medium">
                        {{ $rowNumber }}
                    </x-table.cell>
                    <x-table.cell class="min-w-[200px] max-w-[260px]">
                        <span class="font-extrabold text-sm text-gray-900 tracking-tight leading-snug block">
                            {{ $item['nama_kurikulum'] ?? '-' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="max-w-[180px]">
                        <span 
                            class="inline-block max-w-full truncate px-3 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 align-middle"
                            title="{{ $item['sk_kurikulum'] ?? '-' }}"
                        >
                            {{ $item['sk_kurikulum'] ?? '-' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="text-center whitespace-nowrap">
                        <span class="text-sm font-semibold text-gray-700">
                            {{ $item['tahun_mulai'] ?? '-' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="text-center whitespace-nowrap">
                        <span class="text-sm font-semibold text-gray-700">
                            {{ $item['berlaku_sampai'] ?? '-' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="min-w-[160px]">
                        <span class="text-sm font-medium text-gray-600 leading-snug block">
                            {{ $item['nama_prodi'] ?? '-' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="text-center whitespace-nowrap">
                        @if(!empty($item['link_dokumen']))
                            <a href="{{ $item['link_dokumen'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline">
                                Lihat ↗
                            </a>
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </x-table.cell>
                    <x-table.cell class="whitespace-nowrap">
                        <div class="flex items-center justify-end gap-2.5">
                            @haspermission('kurikulum.update')
                            <!-- Edit -->
                            <a
                                href="{{ route('kurikulum.edit', $item['id']) }}"
                                class="text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Kurikulum"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </a>
                            @endhaspermission

                            @haspermission('kurikulum.delete')
                            <!-- Delete -->
                            <button
                                type="button"
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Kurikulum"
                                data-confirm-delete
                                data-action="{{ route('kurikulum.destroy', $item['id']) }}"
                                data-title="Hapus Kurikulum?"
                                data-message="Apakah Anda yakin ingin menghapus kurikulum '{{ $item['nama_kurikulum'] }}'? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>
                            @endhaspermission

                            <!-- Detail / View -->
                            <a
                                href="{{ route('kurikulum.show', $item['id']) }}"
                                class="text-primary hover:text-additional transition p-1 cursor-pointer"
                                title="Lihat Detail"
                            >
                                <x-icon.eye class="w-5 h-5" />
                            </a>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="6" message="Belum ada data Kurikulum" submessage="Silakan klik tombol Tambah Kurikulum untuk membuat kurikulum baru." />
            @endforelse
        </x-table>

        <x-pagination :paginator="$pagination" />
    </div>
</div>
@endsection
