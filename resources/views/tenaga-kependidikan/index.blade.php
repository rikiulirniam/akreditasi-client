@extends('layouts.app')

@section('title', 'Tenaga Kependidikan')
@section('subtitle', $totalData . ' Data ditemukan')

@section('content')
<div class="space-y-6">
    <!-- Toolbar: Search, Filter, & Tambah Tendik -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        
        <!-- Left: Search & Filter Form -->
        <form method="GET" action="{{ route('tenaga-kependidikan.index') }}" id="searchFilterForm" class="flex flex-wrap items-center gap-3 flex-1">
            <!-- Retain current filter query params if searching -->
            @if(!empty($selectedJabatan))
                <input type="hidden" name="jabatan" value="{{ $selectedJabatan }}" id="hiddenJabatanInput">
            @endif
            @if(!empty($selectedKualifikasi))
                <input type="hidden" name="kualifikasi" value="{{ $selectedKualifikasi }}" id="hiddenKualifikasiInput">
            @endif

            <!-- Search Input (Matches Attached Screenshot) -->
            <div class="relative w-full sm:w-[292px]">
                <input 
                    type="text" 
                    name="cari" 
                    value="{{ $search }}" 
                    placeholder="Cari nama"
                    class="w-full h-[36px] bg-[#D9D9D9] border-2 border-primary rounded-[10px] pl-4 pr-10 text-sm font-medium text-black placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-primary/20 transition"
                    autocomplete="off"
                >
                <button 
                    type="submit" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-primary cursor-pointer hover:scale-110 transition-transform" 
                    aria-label="Cari"
                >
                    <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </div>

            <!-- Filter Dropdown Button (Matches Screenshot: text + yellow chevron container) -->
            <div class="relative" id="filterDropdownContainer">
                <button 
                    type="button" 
                    id="filterDropdownToggle"
                    class="flex items-center justify-between h-[36px] bg-[#D9D9D9] border-2 border-primary rounded-[10px] pl-3.5 pr-1 text-sm font-bold text-black cursor-pointer hover:brightness-95 active:scale-[0.99] transition shadow-xs select-none"
                    aria-expanded="false"
                    aria-haspopup="true"
                >
                    <span class="mr-3 font-semibold text-sm">Filter</span>
                    <span class="w-7 h-7 rounded-[6px] bg-secondary flex items-center justify-center text-primary shrink-0 shadow-xs">
                        <svg id="filterChevronIcon" class="w-4 h-4 stroke-[3] transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </button>

                <!-- Filter Popover Panel -->
                <div 
                    id="filterPopover"
                    class="hidden absolute left-0 mt-2 w-72 sm:w-80 bg-white border border-[#CACACA] rounded-2xl shadow-xl p-4 z-40 transition-all duration-150"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <span class="font-bold text-sm text-primary">Filter Data</span>
                        @if($selectedJabatan || $selectedKualifikasi)
                            <a href="{{ route('tenaga-kependidikan.index', array_filter(['cari' => $search])) }}" class="text-xs text-rose-600 hover:underline font-semibold">
                                Reset Filter
                            </a>
                        @endif
                    </div>

                    <div class="space-y-3.5 pt-3">
                        <!-- Filter Jabatan -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jabatan</label>
                            <select 
                                id="popoverJabatanSelect"
                                class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-lg px-3 py-2 text-xs font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer"
                            >
                                <option value="">Semua Jabatan</option>
                                @foreach($jabatans as $jabatan)
                                    <option value="{{ $jabatan }}" {{ $selectedJabatan === $jabatan ? 'selected' : '' }}>
                                        {{ $jabatan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Kualifikasi -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kualifikasi</label>
                            <select 
                                id="popoverKualifikasiSelect"
                                class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-lg px-3 py-2 text-xs font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer"
                            >
                                <option value="">Semua Kualifikasi</option>
                                @foreach($kualifikasis as $kualifikasi)
                                    <option value="{{ $kualifikasi }}" {{ $selectedKualifikasi === $kualifikasi ? 'selected' : '' }}>
                                        {{ $kualifikasi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Apply & Reset Buttons -->
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                            <button 
                                type="button" 
                                id="cancelFilterBtn"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 transition cursor-pointer"
                            >
                                Batal
                            </button>
                            <button 
                                type="button" 
                                id="applyFilterBtn"
                                class="px-4 py-1.5 bg-primary hover:bg-additional text-white text-xs font-bold rounded-lg transition cursor-pointer shadow-xs"
                            >
                                Terapkan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Filter Badge / Reset All -->
            @if($selectedJabatan || $selectedKualifikasi || $search)
                <a 
                    href="{{ route('tenaga-kependidikan.index') }}" 
                    class="inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-800 font-semibold px-2.5 py-1.5 bg-rose-50 rounded-xl border border-rose-200 transition"
                    title="Hapus semua pencarian dan filter"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Reset</span>
                </a>
            @endif
        </form>

        <!-- Right: Tambah Tendik Button (Matches Screenshot: blue, rounded ~9px, h ~35px) -->
        <div class="shrink-0">
            <button 
                type="button" 
                id="openCreateModalBtn"
                class="inline-flex items-center gap-2 h-[35px] px-3.5 bg-[#075B91] hover:bg-primary text-white text-[13px] font-bold rounded-[9px] shadow-xs transition active:scale-[0.98] cursor-pointer"
            >
                <span class="w-4 h-4 rounded-full border-[1.5px] border-white flex items-center justify-center text-white shrink-0">
                    <svg class="w-3 h-3 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </span>
                <span>Tambah Tendik</span>
            </button>
        </div>
    </div>

    <!-- Table Section (Matches Screenshot Visual Design) -->
    <!-- Table Section (Matches Screenshot Visual Design) -->
    <div class="w-full overflow-x-auto">
        <table class="w-full border-separate border-spacing-y-2.5 text-left">
            <thead>
                <tr class="text-black font-semibold text-sm">
                    <th class="py-3 px-5 bg-[#D9D9D9] first:rounded-l-[13px] last:rounded-r-[13px] font-bold text-black text-[15px] sm:text-[16px] w-16">
                        No
                    </th>
                    <th class="py-3 px-5 bg-[#D9D9D9] first:rounded-l-[13px] last:rounded-r-[13px] font-bold text-black text-[15px] sm:text-[16px] min-w-[200px]">
                        Nama
                    </th>
                    <th class="py-3 px-5 bg-[#D9D9D9] first:rounded-l-[13px] last:rounded-r-[13px] font-bold text-black text-[15px] sm:text-[16px] min-w-[180px]">
                        NIP
                    </th>
                    <th class="py-3 px-5 bg-[#D9D9D9] first:rounded-l-[13px] last:rounded-r-[13px] font-bold text-black text-[15px] sm:text-[16px] min-w-[180px]">
                        Jabatan
                    </th>
                    <th class="py-3 px-5 bg-[#D9D9D9] first:rounded-l-[13px] last:rounded-r-[13px] font-bold text-black text-[15px] sm:text-[16px] min-w-[180px]">
                        Kualifikasi
                    </th>
                    <th class="py-3 px-5 bg-[#D9D9D9] first:rounded-l-[13px] last:rounded-r-[13px] font-bold text-black text-[15px] sm:text-[16px] w-32 text-right">
                        Action
                    </th>
                </tr>
            </thead>
            <tbody class="text-black">
                @forelse($tenagaKependidikan as $index => $item)
                    @php
                        $rowNumber = $index + 1 + (($tenagaKependidikan->currentPage() - 1) * $tenagaKependidikan->perPage());
                    @endphp
                    <tr class="bg-[#CCCCCC] hover:bg-[#C2C2C2] transition-colors duration-150 group">
                        <!-- No -->
                        <td class="py-3.5 px-5 border-y border-[#CACACA] first:border-l first:rounded-l-[13px] last:border-r last:rounded-r-[13px] font-medium text-[15px] sm:text-[16px] text-black">
                            {{ $rowNumber }}.
                        </td>

                        <!-- Nama -->
                        <td class="py-3.5 px-5 border-y border-[#CACACA] first:border-l first:rounded-l-[13px] last:border-r last:rounded-r-[13px] font-medium text-[15px] sm:text-[16px] text-black">
                            {{ $item->nama }}
                        </td>

                        <!-- NIP -->
                        <td class="py-3.5 px-5 border-y border-[#CACACA] first:border-l first:rounded-l-[13px] last:border-r last:rounded-r-[13px] font-medium text-[15px] sm:text-[16px] text-black font-mono">
                            {{ $item->nip }}
                        </td>

                        <!-- Jabatan -->
                        <td class="py-3.5 px-5 border-y border-[#CACACA] first:border-l first:rounded-l-[13px] last:border-r last:rounded-r-[13px] font-medium text-[15px] sm:text-[16px] text-black">
                            {{ $item->jabatan }}
                        </td>

                        <!-- Kualifikasi -->
                        <td class="py-3.5 px-5 border-y border-[#CACACA] first:border-l first:rounded-l-[13px] last:border-r last:rounded-r-[13px] font-medium text-[15px] sm:text-[16px] text-black">
                            {{ $item->kualifikasi }}
                        </td>

                        <!-- Action Icons (3 Icons: Edit, Hapus, Detail) -->
                        <td class="py-3.5 px-5 border-y border-[#CACACA] first:border-l first:rounded-l-[13px] last:border-r last:rounded-r-[13px] text-right">
                            <div class="flex items-center justify-end gap-3.5">
                                <!-- Edit Icon (Yellow #FFC400) -->
                                <button 
                                    type="button" 
                                    class="edit-tendik-btn text-[#FFC400] hover:text-amber-500 transition-transform hover:scale-115 cursor-pointer"
                                    data-id="{{ $item->id }}"
                                    data-nama="{{ $item->nama }}"
                                    data-nip="{{ $item->nip }}"
                                    data-jabatan="{{ $item->jabatan }}"
                                    data-kualifikasi="{{ $item->kualifikasi }}"
                                    data-action="{{ route('tenaga-kependidikan.update', $item) }}"
                                    title="Edit Tenaga Kependidikan"
                                    aria-label="Edit {{ $item->nama }}"
                                >
                                    <x-icon.edit class="w-5 h-5 stroke-[2]" />
                                </button>

                                <!-- Delete Icon (Red #C92D23) -->
                                <button 
                                    type="button" 
                                    class="delete-tendik-btn text-[#C92D23] hover:text-rose-700 transition-transform hover:scale-115 cursor-pointer"
                                    data-id="{{ $item->id }}"
                                    data-nama="{{ $item->nama }}"
                                    data-action="{{ route('tenaga-kependidikan.destroy', $item) }}"
                                    title="Hapus Tenaga Kependidikan"
                                    aria-label="Hapus {{ $item->nama }}"
                                >
                                    <x-icon.delete class="w-5 h-5 stroke-[2]" />
                                </button>

                                <!-- Detail Icon (Navy / Blue #012650) -->
                                <a 
                                    href="{{ route('tenaga-pendidikan.show', $item) }}"
                                    class="text-[#012650] hover:text-[#01568D] transition-transform hover:scale-115 cursor-pointer flex items-center justify-center"
                                    title="Detail Tenaga Pendidikan"
                                    aria-label="Detail {{ $item->nama }}"
                                >
                                    <x-icon.detail class="w-5 h-5 stroke-[2]" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 px-6 text-center bg-[#D9D9D9] rounded-[13px] border border-[#CACACA] text-gray-600">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-10 h-10 text-gray-400 stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                <span class="font-bold text-sm text-gray-800">Tidak ada data Tenaga Kependidikan ditemukan</span>
                                <span class="text-xs text-gray-500">Coba ubah kata kunci pencarian atau reset filter.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer: Counter & Pagination (Matches Screenshot Layout) -->
    @php
        $from = $tenagaKependidikan->firstItem() ?? 0;
        $to = $tenagaKependidikan->lastItem() ?? 0;
        $total = $tenagaKependidikan->total();
        $fromFormatted = sprintf('%02d', $from);
        $toFormatted = sprintf('%02d', $to);
        $lastPage = $tenagaKependidikan->lastPage();
        $currentPage = $tenagaKependidikan->currentPage();
    @endphp
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-3 text-xs sm:text-sm font-semibold text-black select-none">
        <!-- Left: Counter Text (e.g. "Menampilkan 01–08 Data dari 39") -->
        <div>
            @if($total > 0)
                <span>Menampilkan {{ $fromFormatted }}–{{ $toFormatted }} Data dari {{ $total }}</span>
            @else
                <span>Menampilkan 00 Data dari 0</span>
            @endif
        </div>

        <!-- Right: Pagination -->
        <div class="flex items-center gap-1.5 text-primary font-bold">
            {{-- Previous Page Chevron --}}
            @if($currentPage > 1)
                <a 
                    href="{{ $tenagaKependidikan->previousPageUrl() }}" 
                    class="p-1 text-primary hover:text-additional transition flex items-center justify-center"
                    aria-label="Halaman Sebelumnya"
                >
                    <svg class="w-4 h-4 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </a>
            @else
                <span class="p-1 text-gray-300 cursor-not-allowed flex items-center justify-center">
                    <svg class="w-4 h-4 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </span>
            @endif

            {{-- Page Numbers (Active in blue circle, others links) --}}
            @for($p = 1; $p <= $lastPage; $p++)
                @if($p === $currentPage)
                    <span class="w-6 h-6 rounded-full bg-[#01568D] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                        {{ $p }}
                    </span>
                @else
                    <a 
                        href="{{ $tenagaKependidikan->url($p) }}" 
                        class="w-6 h-6 rounded-full hover:bg-gray-200 text-black flex items-center justify-center font-semibold text-xs transition"
                    >
                        {{ $p }}
                    </a>
                @endif
            @endfor

            {{-- Next Page Chevron --}}
            @if($currentPage < $lastPage)
                <a 
                    href="{{ $tenagaKependidikan->nextPageUrl() }}" 
                    class="p-1 text-primary hover:text-additional transition flex items-center justify-center"
                    aria-label="Halaman Selanjutnya"
                >
                    <svg class="w-4 h-4 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            @else
                <span class="p-1 text-gray-300 cursor-not-allowed flex items-center justify-center">
                    <svg class="w-4 h-4 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
            @endif
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: TAMBAHKAN TENAGA KEPENDIDIKAN       -->
<!-- ========================================== -->
<div 
    id="createTendikModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createModalTitle"
>
    <div 
        id="createModalCard" 
        class="w-full max-w-[570px] bg-white rounded-[20px] shadow-2xl p-7 sm:px-12 sm:py-9 transform scale-95 transition-all duration-200 relative select-none"
    >
        <!-- Modal Title: Centered, Bold, Black, No Close X Icon -->
        <h2 id="createModalTitle" class="text-center font-bold text-black text-2xl sm:text-[26px] tracking-tight mb-7 sm:mb-8">
            Tambahkan Tenaga Kependidikan
        </h2>

        <!-- Form: Nama, NIP, Jabatan -->
        <form method="POST" action="{{ route('tenaga-kependidikan.store') }}" id="createTendikForm" class="space-y-4">
            @csrf

            <!-- Field 1: Nama -->
            <div>
                <label for="create_nama" class="block text-left text-[16px] font-bold text-black mb-2">
                    Nama
                </label>
                <input 
                    type="text" 
                    id="create_nama" 
                    name="nama" 
                    value="{{ old('nama') }}" 
                    placeholder="Nama Tenaga Kependidikan"
                    required
                    class="w-full h-[52px] bg-[#E0E0E0] border border-transparent rounded-[8px] pl-[28px] pr-4 text-[17px] text-black placeholder-[#8E8E8E] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition @error('nama') border-rose-500 ring-1 ring-rose-500 @enderror"
                >
                @error('nama')
                    <p class="text-rose-600 text-xs font-semibold mt-1.5 text-left">{{ $message }}</p>
                @enderror
            </div>

            <!-- Field 2: NIP -->
            <div>
                <label for="create_nip" class="block text-left text-[16px] font-bold text-black mb-2">
                    NIP
                </label>
                <input 
                    type="text" 
                    id="create_nip" 
                    name="nip" 
                    value="{{ old('nip') }}" 
                    placeholder="Nomor Induk Pegawai"
                    required
                    class="w-full h-[52px] bg-[#E0E0E0] border border-transparent rounded-[8px] pl-[28px] pr-4 text-[17px] text-black placeholder-[#8E8E8E] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition font-mono @error('nip') border-rose-500 ring-1 ring-rose-500 @enderror"
                >
                @error('nip')
                    <p class="text-rose-600 text-xs font-semibold mt-1.5 text-left">{{ $message }}</p>
                @enderror
            </div>

            <!-- Field 3: Jabatan -->
            <div>
                <label for="create_jabatan" class="block text-left text-[16px] font-bold text-black mb-2">
                    Jabatan
                </label>
                <input 
                    type="text" 
                    id="create_jabatan" 
                    name="jabatan" 
                    value="{{ old('jabatan') }}" 
                    placeholder="Jabatan Tenaga Kependidikan"
                    required
                    class="w-full h-[52px] bg-[#E0E0E0] border border-transparent rounded-[8px] pl-[28px] pr-4 text-[17px] text-black placeholder-[#8E8E8E] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition @error('jabatan') border-rose-500 ring-1 ring-rose-500 @enderror"
                >
                @error('jabatan')
                    <p class="text-rose-600 text-xs font-semibold mt-1.5 text-left">{{ $message }}</p>
                @enderror
            </div>

            <!-- Field 4: Kualifikasi -->
            <div>
                <label for="create_kualifikasi" class="block text-left text-[16px] font-bold text-black mb-2">
                    Kualifikasi
                </label>
                <input 
                    type="text" 
                    id="create_kualifikasi" 
                    name="kualifikasi" 
                    value="{{ old('kualifikasi') }}" 
                    placeholder="Contoh: S1 Teknik Informatika"
                    class="w-full h-[52px] bg-[#E0E0E0] border border-transparent rounded-[8px] pl-[28px] pr-4 text-[17px] text-black placeholder-[#8E8E8E] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition @error('kualifikasi') border-rose-500 ring-1 ring-rose-500 @enderror"
                >
                @error('kualifikasi')
                    <p class="text-rose-600 text-xs font-semibold mt-1.5 text-left">{{ $message }}</p>
                @enderror
            </div>

            <!-- Modal Action Buttons: Batal & Simpan -->
            <div class="flex items-center justify-between gap-4 sm:gap-6 pt-4 sm:pt-6">
                <button 
                    type="button" 
                    id="cancelCreateBtn"
                    class="close-create-modal w-full sm:w-[215px] h-[51px] bg-[#D9D9D9] hover:bg-[#cfcfcf] text-black font-semibold text-[17px] rounded-[6px] transition cursor-pointer active:scale-[0.99] flex items-center justify-center select-none"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    id="submitCreateBtn"
                    class="w-full sm:w-[215px] h-[51px] bg-[#075B91] hover:bg-[#064e7c] text-white font-semibold text-[17px] rounded-[6px] shadow-xs transition cursor-pointer active:scale-[0.99] flex items-center justify-center select-none"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: EDIT TENAGA KEPENDIDIKAN            -->
<!-- ========================================== -->
<div 
    id="editTendikModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs opacity-0 pointer-events-none transition-all duration-200"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editModalTitle"
>
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden transform scale-95 transition-all duration-200" id="editModalCard">
        <!-- Header -->
        <div class="px-6 py-5 bg-white border-b border-gray-100 flex items-center justify-between">
            <h2 id="editModalTitle" class="text-xl font-extrabold text-primary">Edit Tenaga Kependidikan</h2>
            <button type="button" class="close-edit-modal p-1.5 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition cursor-pointer" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" action="" id="editTendikForm" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="edit_nama" class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="edit_nama" 
                    name="nama" 
                    required
                    class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-xl px-4 py-2.5 text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
            </div>

            <div>
                <label for="edit_nip" class="block text-xs font-bold text-gray-700 mb-1">NIP <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="edit_nip" 
                    name="nip" 
                    required
                    class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-xl px-4 py-2.5 text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20 font-mono"
                >
            </div>

            <div>
                <label for="edit_jabatan" class="block text-xs font-bold text-gray-700 mb-1">Jabatan <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="edit_jabatan" 
                    name="jabatan" 
                    required
                    class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-xl px-4 py-2.5 text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
            </div>

            <div>
                <label for="edit_kualifikasi" class="block text-xs font-bold text-gray-700 mb-1">Kualifikasi Pendidikan <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    id="edit_kualifikasi" 
                    name="kualifikasi" 
                    required
                    class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-xl px-4 py-2.5 text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
            </div>

            <!-- Modal Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button 
                    type="button" 
                    class="close-edit-modal px-5 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-6 py-2.5 bg-primary hover:bg-additional text-white text-sm font-bold rounded-xl shadow-xs transition active:scale-[0.98] cursor-pointer"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: KONFIRMASI HAPUS                    -->
<!-- ========================================== -->
<div
    id="deleteTendikModal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-200"
    style="background-color: rgba(0, 0, 0, 0.45); backdrop-filter: blur(1px);"
    role="dialog"
    aria-modal="true"
    aria-labelledby="deleteModalTitle"
>
    <div
        id="deleteModalCard"
        class="w-full transform scale-95 transition-all duration-200 relative text-center select-none"
        style="max-width: 490px; width: 100%; background-color: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); padding: 40px 32px;"
    >
        <!-- Warning Triangle Icon (red, centered matching reference) -->
        <div class="flex items-center justify-center mb-5" style="display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
            <svg width="58" height="52" viewBox="0 0 58 52" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M26.3923 2.5C27.547 0.5 30.453 0.5 31.6077 2.5L56.7224 46C57.8771 48 56.4241 50.5 54.1147 50.5H3.88527C1.57587 50.5 0.122869 48 1.27757 46L26.3923 2.5Z" fill="#C92D23"/>
                <rect x="26.5" y="18" width="5" height="15" rx="2.5" fill="white"/>
                <circle cx="29" cy="40" r="3" fill="white"/>
            </svg>
        </div>

        <!-- Title -->
        <h3 id="deleteModalTitle" class="font-extrabold text-black leading-tight mb-3" style="color: #000000; font-weight: 800; font-size: 26px; margin-bottom: 12px;">
            Hapus Data Tendik?
        </h3>

        <!-- Body text -->
        <p class="text-black text-sm leading-relaxed mx-auto" style="color: #1f2937; font-size: 14.5px; line-height: 1.5; max-width: 380px; margin-bottom: 30px;">
            Apakah Anda yakin ingin menghapus data Tendik ini?<br>
            Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.
        </p>

        <!-- Action Buttons -->
        <form method="POST" action="" id="deleteTendikForm" style="width: 100%;">
            @csrf
            @method('DELETE')
            <div class="flex items-center justify-center gap-4" style="display: flex; align-items: center; justify-content: center; gap: 16px;">
                <button
                    type="button"
                    id="cancelDeleteBtn"
                    class="close-delete-modal font-bold transition duration-150 cursor-pointer flex items-center justify-center"
                    style="width: 155px; height: 48px; background-color: #D9D9D9; color: #000000; border-radius: 10px; font-weight: 700; font-size: 15px; border: none; cursor: pointer;"
                    onmouseover="this.style.backgroundColor='#CACACA'"
                    onmouseout="this.style.backgroundColor='#D9D9D9'"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    id="submitDeleteBtn"
                    class="font-bold text-white transition duration-150 cursor-pointer flex items-center justify-center"
                    style="width: 155px; height: 48px; background-color: #C92D23; color: #FFFFFF; border-radius: 10px; font-weight: 700; font-size: 15px; border: none; cursor: pointer;"
                    onmouseover="this.style.backgroundColor='#B02720'"
                    onmouseout="this.style.backgroundColor='#C92D23'"
                >
                    Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: DETAIL TENAGA KEPENDIDIKAN          -->
<!-- ========================================== -->
<div 
    id="detailTendikModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-200"
    style="background-color: rgba(0, 0, 0, 0.45); backdrop-filter: blur(1px);"
    role="dialog"
    aria-modal="true"
    aria-labelledby="detailModalTitle"
>
    <div 
        id="detailModalCard" 
        class="w-full transform scale-95 transition-all duration-200 relative select-none"
        style="max-width: 520px; width: 100%; background-color: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); padding: 36px 32px;"
    >
        <!-- Modal Header with Blue Eye Icon & Title -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-[#01568D]/10 text-[#01568D] flex items-center justify-center">
                    <x-icon.detail class="w-5 h-5 stroke-[2]" />
                </span>
                <div>
                    <h2 id="detailModalTitle" class="font-extrabold text-black text-xl leading-tight">
                        Detail Tenaga Kependidikan
                    </h2>
                    <p class="text-xs text-gray-500 font-medium">Informasi lengkap data tenaga kependidikan</p>
                </div>
            </div>
            <button 
                type="button" 
                class="close-detail-modal p-1.5 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition cursor-pointer"
                aria-label="Tutup"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Detail Information Rows -->
        <div class="py-6 space-y-3.5">
            <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-3.5">
                <span class="block text-xs font-semibold text-gray-500 mb-1">Nama Lengkap</span>
                <span id="detail_nama" class="block text-base font-bold text-black">-</span>
            </div>
            <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-3.5">
                <span class="block text-xs font-semibold text-gray-500 mb-1">Nomor Induk Pegawai (NIP)</span>
                <span id="detail_nip" class="block text-base font-bold text-black font-mono">-</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-3.5">
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Jabatan</span>
                    <span id="detail_jabatan" class="block text-sm font-bold text-black">-</span>
                </div>
                <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-3.5">
                    <span class="block text-xs font-semibold text-gray-500 mb-1">Kualifikasi</span>
                    <span id="detail_kualifikasi" class="block text-sm font-bold text-black">-</span>
                </div>
            </div>
        </div>

        <!-- Footer: Tutup Button -->
        <div class="pt-2 flex justify-end">
            <button 
                type="button" 
                class="close-detail-modal px-6 py-2.5 bg-[#012650] hover:bg-[#01568D] text-white text-sm font-bold rounded-xl transition cursor-pointer shadow-xs active:scale-[0.98]"
            >
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- JAVASCRIPT: FILTER, SEARCH, & MODALS       -->
<!-- ========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ----------------------------------------
    // 1. FILTER POPOVER TOGGLE & INTERACTION
    // ----------------------------------------
    const filterToggleBtn = document.getElementById('filterDropdownToggle');
    const filterPopover = document.getElementById('filterPopover');
    const filterChevron = document.getElementById('filterChevronIcon');
    const cancelFilterBtn = document.getElementById('cancelFilterBtn');
    const applyFilterBtn = document.getElementById('applyFilterBtn');
    const popoverJabatan = document.getElementById('popoverJabatanSelect');
    const popoverKualifikasi = document.getElementById('popoverKualifikasiSelect');
    const searchFilterForm = document.getElementById('searchFilterForm');

    function toggleFilterPopover(show) {
        if (!filterPopover) return;
        const willShow = show !== undefined ? show : filterPopover.classList.contains('hidden');
        if (willShow) {
            filterPopover.classList.remove('hidden');
            if (filterChevron) filterChevron.classList.add('rotate-180');
        } else {
            filterPopover.classList.add('hidden');
            if (filterChevron) filterChevron.classList.remove('rotate-180');
        }
    }

    if (filterToggleBtn) {
        filterToggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleFilterPopover();
        });
    }

    if (cancelFilterBtn) {
        cancelFilterBtn.addEventListener('click', function () {
            toggleFilterPopover(false);
        });
    }

    if (applyFilterBtn && searchFilterForm) {
        applyFilterBtn.addEventListener('click', function () {
            // Update hidden inputs in the form
            let hiddenJabatan = document.getElementById('hiddenJabatanInput');
            if (!hiddenJabatan) {
                hiddenJabatan = document.createElement('input');
                hiddenJabatan.type = 'hidden';
                hiddenJabatan.name = 'jabatan';
                hiddenJabatan.id = 'hiddenJabatanInput';
                searchFilterForm.appendChild(hiddenJabatan);
            }
            hiddenJabatan.value = popoverJabatan ? popoverJabatan.value : '';

            let hiddenKualifikasi = document.getElementById('hiddenKualifikasiInput');
            if (!hiddenKualifikasi) {
                hiddenKualifikasi = document.createElement('input');
                hiddenKualifikasi.type = 'hidden';
                hiddenKualifikasi.name = 'kualifikasi';
                hiddenKualifikasi.id = 'hiddenKualifikasiInput';
                searchFilterForm.appendChild(hiddenKualifikasi);
            }
            hiddenKualifikasi.value = popoverKualifikasi ? popoverKualifikasi.value : '';

            searchFilterForm.submit();
        });
    }

    // Close filter when clicked outside
    document.addEventListener('click', function (e) {
        if (filterPopover && !filterPopover.contains(e.target) && filterToggleBtn && !filterToggleBtn.contains(e.target)) {
            toggleFilterPopover(false);
        }
    });

    // ----------------------------------------
    // 2. CREATE MODAL (TAMBAHKAN TENAGA KEPENDIDIKAN)
    // ----------------------------------------
    const openCreateBtn = document.getElementById('openCreateModalBtn');
    const createModal = document.getElementById('createTendikModal');
    const createModalCard = document.getElementById('createModalCard');
    const closeCreateBtns = document.querySelectorAll('.close-create-modal');
    const createForm = document.getElementById('createTendikForm');
    const submitCreateBtn = document.getElementById('submitCreateBtn');
    let isSubmittingCreate = false;

    function toggleCreateModal(show) {
        if (!createModal || !createModalCard) return;
        if (show) {
            createModal.classList.remove('opacity-0', 'pointer-events-none');
            createModal.classList.add('opacity-100');
            createModalCard.classList.remove('scale-95');
            createModalCard.classList.add('scale-100');
            document.body.classList.add('overflow-hidden');
            const firstInput = document.getElementById('create_nama');
            if (firstInput) setTimeout(() => firstInput.focus(), 80);
        } else {
            createModal.classList.add('opacity-0', 'pointer-events-none');
            createModal.classList.remove('opacity-100');
            createModalCard.classList.remove('scale-100');
            createModalCard.classList.add('scale-95');
            document.body.classList.remove('overflow-hidden');
        }
    }

    if (openCreateBtn) {
        openCreateBtn.addEventListener('click', () => toggleCreateModal(true));
    }
    closeCreateBtns.forEach(btn => {
        btn.addEventListener('click', () => toggleCreateModal(false));
    });

    if (createModalCard) {
        createModalCard.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    if (createModal) {
        createModal.addEventListener('click', function (e) {
            if (e.target === createModal && !isSubmittingCreate) {
                toggleCreateModal(false);
            }
        });
    }

    if (createForm) {
        createForm.addEventListener('submit', function () {
            isSubmittingCreate = true;
            if (submitCreateBtn) {
                submitCreateBtn.disabled = true;
                submitCreateBtn.classList.add('opacity-80', 'cursor-not-allowed');
                submitCreateBtn.textContent = 'Menyimpan...';
            }
        });
    }

    // Auto-open modal if there are validation errors from previous submit
    @if($errors->has('nama') || $errors->has('nip') || $errors->has('jabatan'))
        toggleCreateModal(true);
    @endif

    // ----------------------------------------
    // 3. EDIT MODAL
    // ----------------------------------------
    const editModal = document.getElementById('editTendikModal');
    const editModalCard = document.getElementById('editModalCard');
    const closeEditBtns = document.querySelectorAll('.close-edit-modal');
    const editForm = document.getElementById('editTendikForm');
    const editNama = document.getElementById('edit_nama');
    const editNip = document.getElementById('edit_nip');
    const editJabatan = document.getElementById('edit_jabatan');
    const editKualifikasi = document.getElementById('edit_kualifikasi');
    const editButtons = document.querySelectorAll('.edit-tendik-btn');

    function toggleEditModal(show) {
        if (!editModal || !editModalCard) return;
        if (show) {
            editModal.classList.remove('opacity-0', 'pointer-events-none');
            editModalCard.classList.remove('scale-95');
            editModalCard.classList.add('scale-100');
            if (editNama) setTimeout(() => editNama.focus(), 100);
        } else {
            editModal.classList.add('opacity-0', 'pointer-events-none');
            editModalCard.classList.remove('scale-100');
            editModalCard.classList.add('scale-95');
        }
    }

    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const nama = this.dataset.nama || '';
            const nip = this.dataset.nip || '';
            const jabatan = this.dataset.jabatan || '';
            const kualifikasi = this.dataset.kualifikasi || '';
            const action = this.dataset.action || '';

            if (editForm) editForm.action = action;
            if (editNama) editNama.value = nama;
            if (editNip) editNip.value = nip;
            if (editJabatan) editJabatan.value = jabatan;
            if (editKualifikasi) editKualifikasi.value = kualifikasi;

            toggleEditModal(true);
        });
    });

    closeEditBtns.forEach(btn => {
        btn.addEventListener('click', () => toggleEditModal(false));
    });
    if (editModal) {
        editModal.addEventListener('click', function (e) {
            if (e.target === editModal) toggleEditModal(false);
        });
    }

    // ----------------------------------------
    // 4. DELETE CONFIRMATION MODAL
    // ----------------------------------------
    const deleteModal = document.getElementById('deleteTendikModal');
    const deleteModalCard = document.getElementById('deleteModalCard');
    const closeDeleteBtns = document.querySelectorAll('.close-delete-modal');
    const deleteForm = document.getElementById('deleteTendikForm');
    const submitDeleteBtn = document.getElementById('submitDeleteBtn');
    const deleteButtons = document.querySelectorAll('.delete-tendik-btn');
    let isSubmittingDelete = false;

    function toggleDeleteModal(show) {
        if (!deleteModal || !deleteModalCard) return;
        if (show) {
            deleteModal.classList.remove('opacity-0', 'pointer-events-none');
            deleteModal.classList.add('opacity-100');
            deleteModalCard.classList.remove('scale-95');
            deleteModalCard.classList.add('scale-100');
            document.body.classList.add('overflow-hidden');
        } else {
            deleteModal.classList.remove('opacity-100');
            deleteModal.classList.add('opacity-0', 'pointer-events-none');
            deleteModalCard.classList.remove('scale-100');
            deleteModalCard.classList.add('scale-95');
            document.body.classList.remove('overflow-hidden');
        }
    }

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const action = this.dataset.action || '';
            if (deleteForm) deleteForm.action = action;
            isSubmittingDelete = false;
            if (submitDeleteBtn) {
                submitDeleteBtn.disabled = false;
                submitDeleteBtn.style.opacity = '1';
                submitDeleteBtn.style.cursor = 'pointer';
                submitDeleteBtn.textContent = 'Hapus';
            }
            toggleDeleteModal(true);
        });
    });

    // Stop propagation so clicking inside modal card won't close it
    if (deleteModalCard) {
        deleteModalCard.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    closeDeleteBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (!isSubmittingDelete) toggleDeleteModal(false);
        });
    });

    if (deleteModal) {
        deleteModal.addEventListener('click', function (e) {
            if (e.target === deleteModal && !isSubmittingDelete) toggleDeleteModal(false);
        });
    }

    // Prevent double-submit on delete
    if (deleteForm) {
        deleteForm.addEventListener('submit', function () {
            isSubmittingDelete = true;
            if (submitDeleteBtn) {
                submitDeleteBtn.disabled = true;
                submitDeleteBtn.classList.add('opacity-70', 'cursor-not-allowed');
                submitDeleteBtn.textContent = 'Menghapus...';
            }
        });
    }

    // ----------------------------------------
    // 5. DETAIL MODAL INTERACTION
    // ----------------------------------------
    const detailModal = document.getElementById('detailTendikModal');
    const detailModalCard = document.getElementById('detailModalCard');
    const closeDetailBtns = document.querySelectorAll('.close-detail-modal');
    const detailButtons = document.querySelectorAll('.detail-tendik-btn');
    const detailNama = document.getElementById('detail_nama');
    const detailNip = document.getElementById('detail_nip');
    const detailJabatan = document.getElementById('detail_jabatan');
    const detailKualifikasi = document.getElementById('detail_kualifikasi');

    function toggleDetailModal(show) {
        if (!detailModal || !detailModalCard) return;
        if (show) {
            detailModal.classList.remove('opacity-0', 'pointer-events-none');
            detailModal.classList.add('opacity-100');
            detailModalCard.classList.remove('scale-95');
            detailModalCard.classList.add('scale-100');
            document.body.classList.add('overflow-hidden');
        } else {
            detailModal.classList.remove('opacity-100');
            detailModal.classList.add('opacity-0', 'pointer-events-none');
            detailModalCard.classList.remove('scale-100');
            detailModalCard.classList.add('scale-95');
            document.body.classList.remove('overflow-hidden');
        }
    }

    detailButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            if (detailNama) detailNama.textContent = this.dataset.nama || '-';
            if (detailNip) detailNip.textContent = this.dataset.nip || '-';
            if (detailJabatan) detailJabatan.textContent = this.dataset.jabatan || '-';
            if (detailKualifikasi) detailKualifikasi.textContent = this.dataset.kualifikasi || '-';
            toggleDetailModal(true);
        });
    });

    closeDetailBtns.forEach(btn => {
        btn.addEventListener('click', () => toggleDetailModal(false));
    });

    if (detailModal) {
        detailModal.addEventListener('click', function (e) {
            if (e.target === detailModal) toggleDetailModal(false);
        });
    }

    if (detailModalCard) {
        detailModalCard.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // ESC key closes all modals
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            toggleCreateModal(false);
            toggleEditModal(false);
            toggleDeleteModal(false);
            toggleDetailModal(false);
            toggleFilterPopover(false);
        }
    });
});
</script>
@endsection
