@extends('layouts.app')

@section('title', 'Kegiatan Mahasiswa')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <!-- Search & Filter Group -->
            <div class="flex items-center gap-3 flex-1 max-w-2xl">
                <form method="GET" action="{{ route('kegiatan-mahasiswa.index') }}" id="filterFormKegiatan" class="flex items-center gap-3 flex-1">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-sm">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </span>
                        <x-input
                            type="text"
                            name="search"
                            id="searchKegiatanInput"
                            placeholder="Cari nama kegiatan / mahasiswa..."
                            value="{{ $search }}"
                            class="pl-10 text-xs"
                        />
                    </div>

                    <!-- Status Filter Dropdown -->
                    <div class="relative w-40">
                        <select
                            name="status_validasi"
                            id="filterStatusSelect"
                            onchange="document.getElementById('filterFormKegiatan').submit()"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                        >
                            <option value="">Semua Status</option>
                            @foreach(['Pending', 'Disetujui', 'Ditolak'] as $status)
                                <option value="{{ $status }}" {{ $selectedStatus == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>

                    <!-- Tahun Akademik Filter -->
                    @if(!empty($tahunAkademikList))
                    <div class="relative w-44">
                        <select
                            name="tahun_akademik_id"
                            id="filterTahunSelect"
                            onchange="document.getElementById('filterFormKegiatan').submit()"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                        >
                            <option value="">Semua Tahun</option>
                            @foreach($tahunAkademikList as $ta)
                                @php
                                    $taLabel = trim(($ta['tahun'] ?? $ta['tahun_akademik'] ?? $ta['nama'] ?? '') . (!empty($ta['semester']) ? ' (' . $ta['semester'] . ')' : ''));
                                    if (empty($taLabel)) {
                                        $taLabel = '-';
                                    }
                                @endphp
                                <option value="{{ $ta['id'] }}" {{ $selectedTahun == $ta['id'] ? 'selected' : '' }}>
                                    {{ $taLabel }}
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

            <!-- Tambah Kegiatan Button -->
            <div>
                <a
                    href="{{ route('kegiatan-mahasiswa.create') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Kegiatan
                </a>
            </div>
        </div>

        <!-- Table -->
        <x-table>
            <x-slot:header>
                <x-table.th>No</x-table.th>
                <x-table.th>Mahasiswa</x-table.th>
                <x-table.th>Nama Kegiatan</x-table.th>
                <x-table.th>Jenis</x-table.th>
                <x-table.th>Tingkat</x-table.th>
                <x-table.th>Status</x-table.th>
                <x-table.th>Action</x-table.th>
            </x-slot:header>

            @forelse($kegiatanList as $index => $item)
                @php
                    $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                    $statusColor = match($item['status_validasi'] ?? 'Pending') {
                        'Disetujui' => 'bg-green-50 text-green-700 border-green-200',
                        'Ditolak' => 'bg-red-50 text-red-700 border-red-200',
                        default => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                    };
                @endphp
                <x-table.row>
                    <x-table.cell class="text-gray-500 font-medium w-12">
                        {{ $rowNumber }}
                    </x-table.cell>
                    <x-table.cell class="w-48">
                        <div>
                            <span class="font-extrabold text-sm text-gray-900 block">{{ $item['nama_mahasiswa'] ?? '-' }}</span>
                            <span class="text-xs text-gray-500">{{ $item['nim'] ?? '-' }}</span>
                        </div>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="font-semibold text-sm text-gray-800">{{ $item['nama_kegiatan'] ?? '-' }}</span>
                    </x-table.cell>
                    <x-table.cell class="w-28">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $item['jenis_kegiatan'] ?? '-' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="w-28">
                        <span class="text-sm font-medium text-gray-700">{{ $item['tingkat'] ?? '-' }}</span>
                    </x-table.cell>
                    <x-table.cell class="w-28">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusColor }}">
                            {{ $item['status_validasi'] ?? 'Pending' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="w-28">
                        <div class="flex items-center justify-end gap-3">
                            <!-- Edit -->
                            <a
                                href="{{ route('kegiatan-mahasiswa.edit', $item['id']) }}"
                                class="text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Kegiatan"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </a>

                            <!-- Delete -->
                            <button
                                type="button"
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Kegiatan"
                                data-confirm-delete
                                data-action="{{ route('kegiatan-mahasiswa.destroy', $item['id']) }}"
                                data-title="Hapus Kegiatan?"
                                data-message="Apakah Anda yakin ingin menghapus kegiatan '{{ $item['nama_kegiatan'] }}'? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>

                            <!-- Detail / View -->
                            <a
                                href="{{ route('kegiatan-mahasiswa.show', $item['id']) }}"
                                class="text-primary hover:text-additional transition p-1 cursor-pointer"
                                title="Lihat Detail"
                            >
                                <x-icon.eye class="w-5 h-5" />
                            </a>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="7" message="Belum ada data Kegiatan Mahasiswa" submessage="Silakan klik tombol Tambah Kegiatan untuk membuat data baru." />
            @endforelse
        </x-table>

        <x-pagination :paginator="$pagination" />
    </div>
</div>
@endsection
