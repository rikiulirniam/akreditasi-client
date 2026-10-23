@extends('layouts.app')

@section('title', 'Status Mahasiswa')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3 flex-1 max-w-2xl">
                <!-- Search & Filters Form -->
                <form id="filterFormStatus" method="GET" action="{{ route('status-mahasiswa.index') }}" class="flex flex-wrap items-center gap-2.5 w-full">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[200px] max-w-sm">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </span>
                        <x-input
                            type="text"
                            name="search"
                            id="searchStatusInput"
                            placeholder="Cari mahasiswa, NIM, kelas..."
                            value="{{ $search }}"
                            class="pl-10 text-xs"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div class="relative w-40">
                        <select
                            name="status"
                            id="filterStatusSelect"
                            onchange="document.getElementById('filterFormStatus').submit()"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                        >
                            <option value="">Semua Status</option>
                            @foreach($statusOptions as $opt)
                                <option value="{{ $opt }}" {{ $selectedStatus === $opt ? 'selected' : '' }}>
                                    {{ $opt }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>

                    @if($search !== '' || $selectedStatus !== '')
                        <a
                            href="{{ route('status-mahasiswa.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition"
                            title="Reset Filter"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Tambah Status Mahasiswa Button -->
            <div>
                <a
                    href="{{ route('status-mahasiswa.create') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Status Mahasiswa
                </a>
            </div>
        </div>

        <!-- Table -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-12">No</x-table.th>
                <x-table.th>Mahasiswa</x-table.th>
                <x-table.th>Kelas / Angkatan</x-table.th>
                <x-table.th>Program Studi</x-table.th>
                <x-table.th class="w-32">Status</x-table.th>
                <x-table.th class="w-28 text-right">Action</x-table.th>
            </x-slot:header>

            @forelse($statusMahasiswaList as $index => $item)
                @php
                    $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                    $statusColor = match($item['status'] ?? 'Aktif') {
                        'Aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'Lulus' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'Cuti' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'Drop Out' => 'bg-red-50 text-red-700 border-red-200',
                        default => 'bg-gray-100 text-gray-700 border-gray-200',
                    };
                @endphp
                <x-table.row>
                    <x-table.cell class="text-gray-500 font-medium w-12">
                        {{ $rowNumber }}
                    </x-table.cell>
                    <x-table.cell>
                        <div>
                            <span class="font-bold text-sm text-gray-900 block">{{ $item['nama_mahasiswa'] ?? '-' }}</span>
                            <span class="text-xs text-gray-500 font-mono">{{ $item['nim'] ?? '-' }}</span>
                        </div>
                    </x-table.cell>
                    <x-table.cell>
                        @php
                            $kls = $item['nama_kelas'] ?? '-';
                            $ang = $item['nama_angkatan'] ?? '-';
                        @endphp
                        @if($kls !== '-' || $ang !== '-')
                            <div>
                                @if($kls !== '-')
                                    <span class="font-semibold text-sm text-gray-800 block">{{ $kls }}</span>
                                @endif
                                @if($ang !== '-')
                                    <span class="text-xs text-gray-500">Angkatan {{ $ang }}</span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </x-table.cell>
                    <x-table.cell>
                        <span class="text-xs font-medium text-gray-700">{{ $item['nama_prodi'] ?? '-' }}</span>
                    </x-table.cell>
                    <x-table.cell class="w-32">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusColor }}">
                            {{ $item['status'] ?? 'Aktif' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="w-28">
                        <div class="flex items-center justify-end gap-3">
                            <!-- Edit -->
                            <a
                                href="{{ route('status-mahasiswa.edit', $item['id']) }}"
                                class="text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Status"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </a>

                            <!-- Delete -->
                            <button
                                type="button"
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Status"
                                data-confirm-delete
                                data-action="{{ route('status-mahasiswa.destroy', $item['id']) }}"
                                data-title="Hapus Status Mahasiswa?"
                                data-message="Apakah Anda yakin ingin menghapus status mahasiswa '{{ $item['nama_mahasiswa'] }}'? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>

                            <!-- Detail / View -->
                            <a
                                href="{{ route('status-mahasiswa.show', $item['id']) }}"
                                class="text-primary hover:text-additional transition p-1 cursor-pointer"
                                title="Lihat Detail"
                            >
                                <x-icon.eye class="w-5 h-5" />
                            </a>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="6" message="Belum ada data Status Mahasiswa" submessage="Silakan klik tombol Tambah Status Mahasiswa untuk membuat data baru." />
            @endforelse
        </x-table>

        <x-pagination :paginator="$pagination" />
    </div>
</div>
@endsection
