@extends('layouts.app')

@section('meta_title', 'Tenaga Kependidikan')
@section('title', 'Tenaga Kependidikan')
@if($totalData > 0 || $search !== '' || $selectedJabatan !== '')
    @section('subtitle', $totalData . ' Data ditemukan')
@endif

@section('content')
<div class="w-full flex-1 flex flex-col {{ $totalData === 0 && empty($search) && empty($selectedJabatan) ? 'min-h-[70vh] justify-center items-center' : 'pt-12 sm:pt-16 lg:pt-20' }}">
    @if($apiError)
        <div class="w-full rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-900 mb-4" role="alert">
            {{ $apiError }}
        </div>
    @endif

    {{-- KONDISI 1: Belum Ada Data Tendik (Empty State - Screenshot 1 / Foto 3) --}}
    @if($totalData === 0 && empty($search) && empty($selectedJabatan))
        <div class="flex flex-col items-center justify-center text-center px-4 select-none my-auto">
            <h2 class="text-3xl sm:text-4xl lg:text-[48px] font-black text-black tracking-tight mb-2 sm:mb-3">
                Belum Ada Data Tendik
            </h2>
            <p class="text-base sm:text-lg lg:text-[20px] font-normal text-black mb-7 sm:mb-8">
                Tekan tombol di bawah untuk menambah data
            </p>
            <button 
                type="button" 
                class="open-create-modal w-full max-w-[410px] h-[58px] sm:h-[61px] rounded-[10px] bg-[#075B91] hover:bg-[#004e7e] text-white flex items-center justify-center gap-3 text-base sm:text-[18px] font-bold shadow-sm transition active:scale-[0.98] cursor-pointer"
                title="Tambah Tendik"
            >
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white text-[#075B91] shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </span>
                <span>Tambah Tendik</span>
            </button>
        </div>
    @else
        {{-- KONDISI 2: Data Tersedia / Sedang Pencarian (Screenshot 2 & 3) --}}
        
        <!-- Toolbar: Search, Filter, & Tambah Tendik -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 sm:mb-5">
            
            <!-- Left: Search & Filter Form -->
            <form method="GET" action="{{ route('tenaga-kependidikan.index') }}" id="searchFilterForm" class="flex flex-wrap items-center gap-3 sm:gap-3.5 flex-1">
                <!-- Search Input: Grey box with magnifying glass on right -->
                <div class="relative w-full sm:w-[275px]">
                    <input 
                        type="text" 
                        id="searchInput"
                        name="cari" 
                        value="{{ $search }}" 
                        placeholder="Cari nama"
                        class="box-border w-full h-[36px] bg-[#D6DADE] border border-[#8093A7] rounded-[6px] pl-3.5 pr-10 text-xs font-medium text-black placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 transition"
                        autocomplete="off"
                    >
                    <button 
                        type="submit" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-800 hover:text-black transition cursor-pointer" 
                        aria-label="Cari"
                    >
                        <svg class="w-4 h-4 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </button>
                </div>

                <!-- Jabatan Filter Dropdown: Grey box with yellow square chevron on right -->
                <div class="relative shrink-0" id="filterDropdownContainer" style="width: 128px; flex: 0 0 128px;">
                    <input type="hidden" name="jabatan" id="filterJabatanValue" value="{{ $selectedJabatan }}">
                    <button
                        type="button"
                        id="filterDropdownToggle"
                        aria-expanded="false"
                        aria-haspopup="true"
                        class="box-border flex h-[36px] w-full items-center justify-between rounded-[6px] border border-[#8093A7] bg-[#D6DADE] pl-3 pr-1 text-xs font-semibold text-gray-900 transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 cursor-pointer"
                    >
                        <span class="truncate">{{ $selectedJabatan ?: 'Jabatan' }}</span>
                        <span class="flex h-[26px] w-[26px] items-center justify-center rounded-[4px] bg-[#FFCC00] text-black shrink-0 border border-[#8093A7]">
                            <svg id="filterChevronIcon" class="h-4 w-4 stroke-[3] transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </button>
                    <div
                        id="filterPopover"
                        class="absolute left-0 top-full z-40 mt-1 hidden w-full overflow-hidden rounded-[8px] border border-[#8093A7] bg-[#EAEAEA] py-1 shadow-lg"
                        role="menu"
                        aria-label="Filter berdasarkan jabatan"
                    >
                        <button type="button" class="filter-jabatan-option w-full px-3 py-1.5 text-left text-xs font-medium text-gray-800 hover:bg-black/10 transition cursor-pointer {{ $selectedJabatan === '' ? 'font-bold text-primary bg-black/5' : '' }}" data-value="" role="menuitem">
                            All Data
                        </button>
                        <button type="button" class="filter-jabatan-option w-full px-3 py-1.5 text-left text-xs font-medium text-gray-800 hover:bg-black/10 transition cursor-pointer {{ Str::lower($selectedJabatan) === 'kepala' ? 'font-bold text-primary bg-black/5' : '' }}" data-value="Kepala" role="menuitem">
                            Kepala
                        </button>
                        <button type="button" class="filter-jabatan-option w-full px-3 py-1.5 text-left text-xs font-medium text-gray-800 hover:bg-black/10 transition cursor-pointer {{ Str::lower($selectedJabatan) === 'admin' ? 'font-bold text-primary bg-black/5' : '' }}" data-value="Admin" role="menuitem">
                            Admin
                        </button>
                        <button type="button" class="filter-jabatan-option w-full px-3 py-1.5 text-left text-xs font-medium text-gray-800 hover:bg-black/10 transition cursor-pointer {{ in_array(Str::lower($selectedJabatan), ['staff', 'staf']) ? 'font-bold text-primary bg-black/5' : '' }}" data-value="Staff" role="menuitem">
                            Staff
                        </button>
                        <button type="button" class="filter-jabatan-option w-full px-3 py-1.5 text-left text-xs font-medium text-gray-800 hover:bg-black/10 transition cursor-pointer {{ Str::lower($selectedJabatan) === 'teknisi' ? 'font-bold text-primary bg-black/5' : '' }}" data-value="Teknisi" role="menuitem">
                            Teknisi
                        </button>
                        @foreach($jabatans as $jabatan)
                            @if(!in_array(Str::lower($jabatan), ['kepala', 'admin', 'staff', 'staf', 'teknisi', 'all data', 'all']))
                                <button type="button" class="filter-jabatan-option w-full px-3 py-1.5 text-left text-xs font-medium text-gray-800 hover:bg-black/10 transition cursor-pointer {{ $selectedJabatan === $jabatan ? 'font-bold text-primary bg-black/5' : '' }}" data-value="{{ $jabatan }}" role="menuitem">
                                    {{ $jabatan }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Active Filter Reset Button -->
                @if($selectedJabatan || $search)
                    <a 
                        href="{{ route('tenaga-kependidikan.index') }}" 
                        class="inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-800 font-semibold px-2.5 py-1.5 bg-rose-50 rounded-lg border border-rose-200 transition"
                        title="Hapus pencarian dan filter"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Reset</span>
                    </a>
                @endif
            </form>

            <!-- Right: Tambah Tendik Button (Figma: rounded rect with white circle + icon) -->
            <div class="shrink-0">
                <button 
                    type="button" 
                    id="openCreateModalBtn"
                    class="open-create-modal inline-flex items-center gap-2 rounded-[6px] bg-[#005B94] hover:bg-[#004e7e] pl-2.5 pr-3.5 h-[36px] text-xs font-semibold text-white shadow-xs transition cursor-pointer active:scale-[0.98]"
                    title="Tambah Tendik"
                >
                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-white text-[#005B94] shrink-0">
                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </span>
                    <span>Tambah Tendik</span>
                </button>
            </div>
        </div>

        {{-- Table Area --}}
        @if($tenagaKependidikan->isEmpty())
            <div class="py-16 text-center text-gray-600 bg-white rounded-2xl border border-dashed border-gray-300 mt-2">
                <p class="text-base font-bold text-gray-800">Tidak ada data tenaga kependidikan yang sesuai.</p>
                <p class="text-xs text-gray-500 mt-1">Coba ubah kata kunci pencarian atau reset filter.</p>
                <a href="{{ route('tenaga-kependidikan.index') }}" class="inline-flex items-center gap-1.5 mt-4 text-xs font-bold text-[#075B91] hover:underline">
                    Reset Pencarian & Filter
                </a>
            </div>
        @else
            <div class="tendik-flow-table">
                <x-table>
                    <x-slot:header>
                        <x-table.th class="w-[5%] text-center">No</x-table.th>
                        <x-table.th class="w-[23%] text-left">Nama</x-table.th>
                        <x-table.th class="w-[21%] text-left">NIP</x-table.th>
                        <x-table.th class="w-[19%] text-left">Jabatan</x-table.th>
                        <x-table.th class="w-[18%] text-left">Kualifikasi</x-table.th>
                        <x-table.th class="w-[14%] text-center">Action</x-table.th>
                    </x-slot:header>

                    @foreach($tenagaKependidikan as $index => $item)
                        @php
                            $rowNumber = $index + 1 + (($tenagaKependidikan->currentPage() - 1) * $tenagaKependidikan->perPage());
                        @endphp
                        <x-table.row class="tendik-row">
                            <x-table.cell class="text-black font-normal w-[5%] text-center">
                                <span class="row-number-text">{{ $rowNumber }}.</span>
                            </x-table.cell>
                            <x-table.cell class="w-[23%]">
                                <span class="text-[14px] font-medium text-black">
                                    {{ $item['nama'] }}
                                </span>
                            </x-table.cell>
                            <x-table.cell class="w-[21%] whitespace-nowrap">
                                <span class="text-[14px] font-normal text-black">{{ $item['nip'] }}</span>
                            </x-table.cell>
                            <x-table.cell class="w-[19%] text-[14px] font-normal text-black">
                                {{ $item['jabatan'] }}
                            </x-table.cell>
                            <x-table.cell class="w-[18%] text-[14px] font-normal text-black">
                                {{ $item['kualifikasi'] }}
                            </x-table.cell>
                            <x-table.cell class="w-[14%] text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <!-- Edit Icon (Yellow #FFC400) -->
                                    <button
                                        type="button" 
                                        class="edit-tendik-btn text-[#FFC400] hover:text-[#e0ac00] transition p-1 cursor-pointer focus:outline-none"
                                        data-id="{{ $item['id'] }}"
                                        data-nama="{{ $item['nama'] }}"
                                        data-nip="{{ $item['nip'] }}"
                                        data-jabatan="{{ $item['jabatan'] }}"
                                        data-id-pendidikan="{{ $item['id_pendidikan'] }}"
                                        data-kualifikasi="{{ $item['kualifikasi'] }}"
                                        data-action="{{ route('tenaga-kependidikan.update', $item['id']) }}"
                                        title="Edit {{ $item['nama'] }}"
                                        aria-label="Edit {{ $item['nama'] }}"
                                    >
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>

                                    <!-- Delete Icon (Solid Red #C72C22) -->
                                    <button 
                                        type="button" 
                                        class="delete-tendik-btn text-[#C72C22] hover:text-[#a8251c] transition p-1 cursor-pointer focus:outline-none"
                                        data-id="{{ $item['id'] }}"
                                        data-nama="{{ $item['nama'] }}"
                                        data-action="{{ route('tenaga-kependidikan.destroy', $item['id']) }}"
                                        title="Hapus {{ $item['nama'] }}"
                                        aria-label="Hapus {{ $item['nama'] }}"
                                    >
                                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.353 6.83a.75.75 0 0 1 .75.75v7.5a.75.75 0 0 1-1.5 0v-7.5a.75.75 0 0 1 .75-.75Zm4.5 0a.75.75 0 0 1 .75.75v7.5a.75.75 0 0 1-1.5 0v-7.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <!-- Detail Icon (Navy / Blue #003B6D) -->
                                    <a
                                        href="{{ route('tenaga-pendidikan.show', $item['id']) }}"
                                        class="text-[#003B6D] hover:text-[#075B91] transition p-1 cursor-pointer flex items-center justify-center focus:outline-none"
                                        title="Detail {{ $item['nama'] }}"
                                        aria-label="Detail {{ $item['nama'] }}"
                                    >
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </a>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                    @endforeach
                </x-table>
            </div>

            <!-- Bottom Pagination (Adaptive: shows "dari X" if multi-page) -->
            <div class="mt-6 sm:mt-8 mb-6">
                <x-pagination :paginator="$tenagaKependidikan" :always-show="true" :show-total="$tenagaKependidikan->total() > $tenagaKependidikan->perPage()" />
            </div>
        @endif
    @endif
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
        class="w-full max-w-[540px] bg-white rounded-[20px] shadow-2xl p-7 sm:px-11 sm:py-8 transform scale-95 transition-all duration-200 relative select-none"
    >
        <!-- Modal Title: Centered, Bold, Black -->
        <h2 id="createModalTitle" class="text-center font-bold text-black text-2xl sm:text-[26px] tracking-tight mb-6 sm:mb-7">
            Tambahkan Tenaga Kependidikan
        </h2>

        <!-- Form: Nama, NIP, Jabatan, Kualifikasi -->
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
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black placeholder-[#737373] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition @error('nama') border-rose-500 ring-1 ring-rose-500 @enderror"
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
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black placeholder-[#737373] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition @error('nip') border-rose-500 ring-1 ring-rose-500 @enderror"
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
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black placeholder-[#737373] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition @error('jabatan') border-rose-500 ring-1 ring-rose-500 @enderror"
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
                <select
                    id="create_kualifikasi" 
                    name="id_pendidikan"
                    required
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition cursor-pointer @error('id_pendidikan') border-rose-500 ring-1 ring-rose-500 @enderror"
                >
                    <option value="" disabled {{ old('id_pendidikan') ? '' : 'selected' }}>Isi Pendidikan Terakhir</option>
                    @foreach($pendidikan as $level)
                        <option value="{{ $level['id'] ?? '' }}" {{ (string) old('id_pendidikan') === (string) ($level['id'] ?? '') ? 'selected' : '' }}>
                            {{ $level['jenjang'] ?? '-' }}
                        </option>
                    @endforeach
                </select>
                @error('id_pendidikan')
                    <p class="text-rose-600 text-xs font-semibold mt-1.5 text-left">{{ $message }}</p>
                @enderror
            </div>

            <!-- Modal Action Buttons: Batal & Simpan -->
            <div class="flex items-center justify-between gap-4 pt-4 sm:pt-6">
                <button 
                    type="button" 
                    id="cancelCreateBtn"
                    class="close-create-modal flex-1 h-[50px] bg-[#DEDEDE] hover:bg-[#d0d0d0] text-black font-bold text-[17px] rounded-[10px] transition cursor-pointer active:scale-[0.99] flex items-center justify-center select-none"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    id="submitCreateBtn"
                    class="flex-1 h-[50px] bg-[#075B91] hover:bg-[#064e7c] text-white font-bold text-[17px] rounded-[10px] shadow-xs transition cursor-pointer active:scale-[0.99] flex items-center justify-center select-none"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: PERBARUI TENAGA KEPENDIDIKAN        -->
<!-- ========================================== -->
<div 
    id="editTendikModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editModalTitle"
>
    <div 
        id="editModalCard" 
        class="w-full max-w-[540px] bg-white rounded-[20px] shadow-2xl p-7 sm:px-11 sm:py-8 transform scale-95 transition-all duration-200 relative select-none"
    >
        <!-- Modal Title: Centered, Bold, Black -->
        <h2 id="editModalTitle" class="text-center font-bold text-black text-2xl sm:text-[26px] tracking-tight mb-6 sm:mb-7">
            Perbarui Tenaga Kependidikan
        </h2>

        <!-- Form: Nama, NIP, Jabatan, Kualifikasi -->
        <form method="POST" action="" id="editTendikForm" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field 1: Nama -->
            <div>
                <label for="edit_nama" class="block text-left text-[16px] font-bold text-black mb-2">
                    Nama
                </label>
                <input 
                    type="text" 
                    id="edit_nama" 
                    name="nama" 
                    placeholder="Nama Tenaga Kependidikan"
                    required
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black placeholder-[#737373] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition"
                >
            </div>

            <!-- Field 2: NIP -->
            <div>
                <label for="edit_nip" class="block text-left text-[16px] font-bold text-black mb-2">
                    NIP
                </label>
                <input 
                    type="text" 
                    id="edit_nip" 
                    name="nip" 
                    placeholder="Nomor Induk Pegawai"
                    required
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black placeholder-[#737373] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition"
                >
            </div>

            <!-- Field 3: Jabatan -->
            <div>
                <label for="edit_jabatan" class="block text-left text-[16px] font-bold text-black mb-2">
                    Jabatan
                </label>
                <input 
                    type="text" 
                    id="edit_jabatan" 
                    name="jabatan" 
                    placeholder="Jabatan Tenaga Kependidikan"
                    required
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black placeholder-[#737373] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition"
                >
            </div>

            <!-- Field 4: Kualifikasi -->
            <div>
                <label for="edit_kualifikasi" class="block text-left text-[16px] font-bold text-black mb-2">
                    Kualifikasi
                </label>
                <select
                    id="edit_kualifikasi" 
                    name="id_pendidikan"
                    required
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition cursor-pointer"
                >
                    <option value="" disabled>Isi Pendidikan Terakhir</option>
                    @foreach($pendidikan as $level)
                        <option value="{{ $level['id'] ?? '' }}">{{ $level['jenjang'] ?? '-' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Modal Action Buttons: Batal & Simpan -->
            <div class="flex items-center justify-between gap-4 pt-4 sm:pt-6">
                <button 
                    type="button" 
                    class="close-edit-modal flex-1 h-[50px] bg-[#DEDEDE] hover:bg-[#CACACA] text-black font-bold text-[17px] rounded-[10px] shadow-xs transition cursor-pointer active:scale-[0.99] flex items-center justify-center select-none"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    id="submitEditBtn"
                    class="flex-1 h-[50px] bg-[#075B91] hover:bg-[#064e7c] text-white font-bold text-[17px] rounded-[10px] shadow-xs transition cursor-pointer active:scale-[0.99] flex items-center justify-center select-none"
                >
                    Simpan
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
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200"
    role="dialog"
    aria-modal="true"
    aria-labelledby="deleteModalTitle"
>
    <div
        id="deleteModalCard"
        class="w-full max-w-[490px] bg-white rounded-[16px] shadow-2xl p-8 sm:p-10 transform scale-95 transition-all duration-200 text-center select-none"
    >
        <!-- Warning Triangle Icon (red, centered matching reference) -->
        <div class="flex items-center justify-center mb-5">
            <svg width="58" height="52" viewBox="0 0 58 52" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M26.3923 2.5C27.547 0.5 30.453 0.5 31.6077 2.5L56.7224 46C57.8771 48 56.4241 50.5 54.1147 50.5H3.88527C1.57587 50.5 0.122869 48 1.27757 46L26.3923 2.5Z" fill="#C52B21"/>
                <rect x="26.5" y="18" width="5" height="15" rx="2.5" fill="white"/>
                <circle cx="29" cy="40" r="3" fill="white"/>
            </svg>
        </div>

        <!-- Title -->
        <h3 id="deleteModalTitle" class="font-extrabold text-black text-2xl sm:text-[26px] leading-tight mb-3">
            Hapus Data Tendik?
        </h3>

        <!-- Body text -->
        <p class="text-gray-800 text-[14.5px] leading-relaxed mx-auto max-w-[380px] mb-8">
            Apakah Anda yakin ingin menghapus data Tendik ini?<br>
            Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.
        </p>

        <!-- Action Buttons -->
        <form method="POST" action="" id="deleteTendikForm" class="w-full">
            @csrf
            @method('DELETE')
            <div class="flex items-center justify-center gap-4">
                <button
                    type="button"
                    id="cancelDeleteBtn"
                    class="close-delete-modal w-[155px] h-[48px] bg-[#D9D9D9] hover:bg-[#CACACA] text-black font-bold text-[15px] rounded-[10px] transition cursor-pointer flex items-center justify-center"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    id="submitDeleteBtn"
                    class="w-[155px] h-[48px] bg-[#C52B21] hover:bg-[#B02720] text-white font-bold text-[15px] rounded-[10px] transition cursor-pointer flex items-center justify-center shadow-xs"
                >
                    Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- JAVASCRIPT: FILTER, SEARCH, & MODALS       -->
<!-- ========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterContainer = document.getElementById('filterDropdownContainer');
    const filterToggle = document.getElementById('filterDropdownToggle');
    const filterMenu = document.getElementById('filterPopover');
    const filterChevron = document.getElementById('filterChevronIcon');
    const selectedJabatanInput = document.getElementById('filterJabatanValue');
    const searchFilterForm = document.getElementById('searchFilterForm');
    const searchInput = document.getElementById('searchInput');

    function setFilterMenuOpen(isOpen) {
        if (!filterMenu || !filterToggle) return;
        filterMenu.classList.toggle('hidden', !isOpen);
        filterToggle.setAttribute('aria-expanded', String(isOpen));
        filterChevron?.classList.toggle('rotate-180', isOpen);
    }

    filterToggle?.addEventListener('click', function () {
        setFilterMenuOpen(filterToggle.getAttribute('aria-expanded') !== 'true');
    });

    document.querySelectorAll('.filter-jabatan-option').forEach(option => {
        option.addEventListener('click', function () {
            if (selectedJabatanInput) selectedJabatanInput.value = this.dataset.value || '';
            setFilterMenuOpen(false);
            searchFilterForm?.requestSubmit();
        });
    });

    document.addEventListener('click', function (event) {
        if (filterContainer && !filterContainer.contains(event.target)) {
            setFilterMenuOpen(false);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') setFilterMenuOpen(false);
    });

    // Debounced search on input change
    let searchDebounceTimeout = null;
    if (searchInput && searchFilterForm) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchDebounceTimeout);
            searchDebounceTimeout = setTimeout(() => {
                searchFilterForm.requestSubmit();
            }, 600);
        });
    }

    // ----------------------------------------
    // 1. CREATE MODAL (TAMBAHKAN TENAGA KEPENDIDIKAN)
    // ----------------------------------------
    const openCreateBtns = document.querySelectorAll('.open-create-modal');
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

    openCreateBtns.forEach(btn => {
        btn.addEventListener('click', () => toggleCreateModal(true));
    });

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

    // Auto-open modal if requested via URL query (?tambah=1 or ?create=1) or if there are validation errors from previous submit
    @if(request()->boolean('tambah') || request()->boolean('create') || $errors->has('nama') || $errors->has('nip') || $errors->has('jabatan') || $errors->has('id_pendidikan'))
        toggleCreateModal(true);
    @endif

    // ----------------------------------------
    // 2. EDIT MODAL
    // ----------------------------------------
    const editModal = document.getElementById('editTendikModal');
    const editModalCard = document.getElementById('editModalCard');
    const closeEditBtns = document.querySelectorAll('.close-edit-modal');
    const editForm = document.getElementById('editTendikForm');
    const editNama = document.getElementById('edit_nama');
    const editNip = document.getElementById('edit_nip');
    const editJabatan = document.getElementById('edit_jabatan');
    const editPendidikan = document.getElementById('edit_kualifikasi');
    const editButtons = document.querySelectorAll('.edit-tendik-btn');

    function toggleEditModal(show) {
        if (!editModal || !editModalCard) return;
        if (show) {
            editModal.classList.remove('opacity-0', 'pointer-events-none');
            editModal.classList.add('opacity-100');
            editModalCard.classList.remove('scale-95');
            editModalCard.classList.add('scale-100');
            if (editNama) setTimeout(() => editNama.focus(), 100);
        } else {
            editModal.classList.add('opacity-0', 'pointer-events-none');
            editModal.classList.remove('opacity-100');
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
            const idPendidikan = this.dataset.idPendidikan || '';
            const action = this.dataset.action || '';

            if (editForm) editForm.action = action;
            if (editNama) editNama.value = nama;
            if (editNip) editNip.value = nip;
            if (editJabatan) editJabatan.value = jabatan;
            if (editPendidikan) editPendidikan.value = idPendidikan;

            toggleEditModal(true);
        });
    });

    @if(session()->has('editing_tendik_id'))
        const failedEditId = @json((string) session('editing_tendik_id'));
        const failedEditButton = Array.from(editButtons).find(button => button.dataset.id === failedEditId);
        if (failedEditButton) {
            failedEditButton.click();
            if (editNama) editNama.value = @json(old('nama', ''));
            if (editNip) editNip.value = @json(old('nip', ''));
            if (editJabatan) editJabatan.value = @json(old('jabatan', ''));
            if (editPendidikan) editPendidikan.value = @json(old('id_pendidikan', ''));
        }
    @endif

    const submitEditBtn = document.getElementById('submitEditBtn');
    if (editForm) {
        editForm.addEventListener('submit', function () {
            if (submitEditBtn) {
                submitEditBtn.disabled = true;
                submitEditBtn.classList.add('opacity-80', 'cursor-not-allowed');
                submitEditBtn.textContent = 'Menyimpan...';
            }
        });
    }

    closeEditBtns.forEach(btn => {
        btn.addEventListener('click', () => toggleEditModal(false));
    });
    if (editModal) {
        editModal.addEventListener('click', function (e) {
            if (e.target === editModal) toggleEditModal(false);
        });
    }

    // ----------------------------------------
    // 3. DELETE CONFIRMATION MODAL
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

    let activeDeleteRow = null;

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            activeDeleteRow = this.closest('tr');
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

    // Animated deletion with smooth upward slide
    if (deleteForm) {
        deleteForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (isSubmittingDelete) return;
            isSubmittingDelete = true;

            const targetRow = activeDeleteRow;
            const actionUrl = deleteForm.action;
            const csrfToken = deleteForm.querySelector('input[name="_token"]')?.value 
                || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                || '';

            toggleDeleteModal(false);

            if (targetRow) {
                targetRow.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                targetRow.style.opacity = '0.35';
                targetRow.style.pointerEvents = 'none';

                fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                })
                .then(async response => {
                    let data = {};
                    try { data = await response.json(); } catch (err) {}

                    if (!response.ok || (data.success !== undefined && data.success === false)) {
                        throw new Error(data.message || 'Gagal menghapus data.');
                    }

                    targetRow.style.opacity = '0';
                    targetRow.style.transform = 'translateY(-6px)';

                    const cells = targetRow.querySelectorAll('td');
                    cells.forEach(td => {
                        td.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                        td.style.paddingTop = '0px';
                        td.style.paddingBottom = '0px';
                        td.style.borderTopWidth = '0px';
                        td.style.borderBottomWidth = '0px';
                        td.style.height = '0px';
                        td.style.lineHeight = '0px';
                        td.style.fontSize = '0px';
                        td.style.boxShadow = 'none';
                        Array.from(td.children).forEach(child => {
                            child.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                            child.style.maxHeight = '0px';
                            child.style.opacity = '0';
                            child.style.overflow = 'hidden';
                            child.style.margin = '0px';
                            child.style.padding = '0px';
                        });
                    });

                    setTimeout(() => {
                        targetRow.remove();

                        // Re-number remaining rows in the table
                        document.querySelectorAll('.tendik-row').forEach((r, idx) => {
                            const noCell = r.querySelector('.row-number-text') || r.querySelector('td:first-child');
                            if (noCell) noCell.textContent = (idx + 1) + '.';
                        });

                        // Show empty state if all rows are removed
                        const tbody = document.querySelector('.tendik-flow-table tbody');
                        if (tbody && tbody.querySelectorAll('.tendik-row').length === 0) {
                            window.location.reload();
                        }

                        if (window.showToast) {
                            window.showToast({
                                type: 'success',
                                title: 'Berhasil Dihapus',
                                message: data.message || 'Data Tenaga Kependidikan berhasil dihapus.'
                            });
                        }
                        isSubmittingDelete = false;
                        activeDeleteRow = null;
                    }, 360);
                })
                .catch(error => {
                    isSubmittingDelete = false;
                    targetRow.style.opacity = '1';
                    targetRow.style.transform = 'none';
                    targetRow.style.pointerEvents = 'auto';

                    if (window.showToast) {
                        window.showToast({
                            type: 'error',
                            title: 'Gagal Menghapus',
                            message: error.message || 'Tidak dapat terhubung ke server.'
                        });
                    } else {
                        alert(error.message || 'Gagal menghapus data.');
                    }
                    activeDeleteRow = null;
                });
            } else {
                deleteForm.submit();
            }
        });
    }

    // ESC key closes all modals
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            toggleCreateModal(false);
            toggleEditModal(false);
            toggleDeleteModal(false);
            setFilterMenuOpen(false);
        }
    });
});
</script>
@endsection
