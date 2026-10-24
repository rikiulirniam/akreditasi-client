@extends('layouts.app')

@section('title', 'Tenaga Kependidikan')
@section('subtitle', $totalData . ' Data ditemukan')

@section('content')
<div class="pt-10 sm:pt-16 lg:pt-20">
    @if($apiError)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-medium text-amber-900 mb-6" role="alert">
            {{ $apiError }}
        </div>
    @endif

    <!-- Toolbar: Search, Filter, & Tambah Tendik (Matches Image 2 Spacing & Style) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 sm:mb-10 lg:mb-12">
        
        <!-- Left: Search & Filter Form -->
        <form method="GET" action="{{ route('tenaga-kependidikan.index') }}" id="searchFilterForm" class="flex flex-wrap items-center gap-3.5 flex-1">
            <!-- Search Input (Matches Image 2: uniform grey background with outline search icon on right) -->
            <div class="relative w-full sm:w-[310px]">
                <input 
                    type="text" 
                    name="cari" 
                    value="{{ $search }}" 
                    placeholder="Cari nama"
                    class="box-border w-full h-[36px] bg-white-form border border-[#8093A7] rounded-[6px] pl-3.5 pr-10 text-xs font-medium text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/20 transition"
                    autocomplete="off"
                >
                <button 
                    type="submit" 
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-800 hover:text-black transition cursor-pointer" 
                    aria-label="Cari"
                >
                    <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </div>

            <!-- Jabatan Filter Dropdown (Matches Image 2: grey box with yellow square button on right) -->
            <div class="relative shrink-0" id="filterDropdownContainer" style="width: 140px; flex: 0 0 140px;">
                <input type="hidden" name="jabatan" id="filterJabatanValue" value="{{ $selectedJabatan }}">
                <button
                    type="button"
                    id="filterDropdownToggle"
                    aria-expanded="false"
                    aria-haspopup="true"
                    class="box-border flex h-[36px] w-full items-center justify-between rounded-[6px] border border-[#8093A7] bg-white-form pl-3.5 pr-1 text-xs font-medium text-gray-900 transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer"
                >
                    <span class="truncate">{{ $selectedJabatan ?: 'Jabatan' }}</span>
                    <span class="flex h-[28px] w-[28px] items-center justify-center rounded-[4px] bg-secondary text-primary shrink-0 border border-[#8093A7]">
                        <svg id="filterChevronIcon" class="h-4 w-4 stroke-[3] transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </button>
                <div
                    id="filterPopover"
                    class="absolute left-0 top-full z-40 mt-1 hidden w-full overflow-hidden rounded-[6px] border border-[#8093A7] bg-[#EAEAEA] py-1 shadow-lg"
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

            <!-- Active Filter Badge / Reset All -->
            @if($selectedJabatan || $search)
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

        <!-- Right: Tambah Tendik Button (navy pill matching Image 2) -->
        <div class="shrink-0">
            <button 
                type="button" 
                id="openCreateModalBtn"
                class="inline-flex items-center gap-2 rounded-full bg-[#012650] hover:bg-[#01568D] px-5 h-[36px] text-xs font-bold text-white shadow-sm transition cursor-pointer active:scale-[0.98]"
                title="Tambah Tendik"
            >
                <svg class="w-4 h-4 stroke-[2.5] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Tendik</span>
            </button>
        </div>
    </div>

    <div class="tendik-flow-table">
    <x-table>
        <x-slot:header>
            <x-table.th class="w-[7%]">No</x-table.th>
            <x-table.th class="w-[20%]">Nama</x-table.th>
            <x-table.th class="w-[22%]">NIP</x-table.th>
            <x-table.th class="w-[17%]">Jabatan</x-table.th>
            <x-table.th class="w-[17%]">Kualifikasi</x-table.th>
            <x-table.th class="w-[17%] text-center">Action</x-table.th>
        </x-slot:header>

        @forelse($tenagaKependidikan as $index => $item)
            @php
                $rowNumber = $index + 1 + (($tenagaKependidikan->currentPage() - 1) * $tenagaKependidikan->perPage());
            @endphp
            <x-table.row class="tendik-row">
                <x-table.cell class="text-gray-500 font-medium w-[7%]">
                    <span class="row-number-text">{{ $rowNumber }}.</span>
                </x-table.cell>
                <x-table.cell class="w-[20%]">
                    <span class="text-sm font-medium text-gray-700">
                        {{ $item['nama'] }}
                    </span>
                </x-table.cell>
                <x-table.cell class="w-[22%] whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-700">{{ $item['nip'] }}</span>
                </x-table.cell>
                <x-table.cell class="w-[17%] text-sm font-medium text-gray-700">
                    {{ $item['jabatan'] }}
                </x-table.cell>
                <x-table.cell class="w-[17%] text-sm font-medium text-gray-700">
                    {{ $item['kualifikasi'] }}
                </x-table.cell>
                <x-table.cell class="w-[17%]">
                    <div class="flex items-center justify-center gap-3">
                                <!-- Edit Icon (Yellow #FFC400) -->
                                <button
                                    type="button" 
                                    class="edit-tendik-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                    data-id="{{ $item['id'] }}"
                                    data-nama="{{ $item['nama'] }}"
                                    data-nip="{{ $item['nip'] }}"
                                    data-jabatan="{{ $item['jabatan'] }}"
                                    data-id-pendidikan="{{ $item['id_pendidikan'] }}"
                                    data-kualifikasi="{{ $item['kualifikasi'] }}"
                                    data-action="{{ route('tenaga-kependidikan.update', $item['id']) }}"
                                    title="Edit Tenaga Kependidikan"
                                    aria-label="Edit {{ $item['nama'] }}"
                                >
                                    <x-icon.edit class="w-5 h-5" />
                                </button>

                                <!-- Delete Icon (Red #C92D23) -->
                                <button 
                                    type="button" 
                                    class="delete-tendik-btn text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                    data-id="{{ $item['id'] }}"
                                    data-nama="{{ $item['nama'] }}"
                                    data-action="{{ route('tenaga-kependidikan.destroy', $item['id']) }}"
                                    title="Hapus Tenaga Kependidikan"
                                    aria-label="Hapus {{ $item['nama'] }}"
                                >
                                    <x-icon.delete class="w-5 h-5" />
                                </button>

                                <!-- Detail Icon (Navy / Blue #012650) -->
                                <a
                                    href="{{ route('tenaga-pendidikan.show', $item['id']) }}"
                                    class="text-primary hover:text-additional transition p-1 cursor-pointer flex items-center justify-center"
                                    title="Detail Tenaga Kependidikan"
                                    aria-label="Detail {{ $item['nama'] }}"
                                >
                                    <x-icon.detail class="w-5 h-5" />
                                </a>
                    </div>
                </x-table.cell>
            </x-table.row>
        @empty
            <x-table.empty colspan="6" message="Belum ada data tenaga kependidikan" submessage="Coba ubah kata kunci pencarian atau reset filter." />
        @endforelse
    </x-table>
    </div>

    <!-- Bottom Pagination (Matches Image 2 distance) -->
    <div class="mt-8 sm:mt-10 mb-8">
        <x-pagination :paginator="$tenagaKependidikan" :always-show="true" :show-total="false" />
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
                <select
                    id="create_kualifikasi" 
                    name="id_pendidikan"
                    required
                    class="w-full h-[52px] bg-[#E0E0E0] border border-transparent rounded-[8px] pl-[28px] pr-4 text-[17px] text-black focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition @error('id_pendidikan') border-rose-500 ring-1 ring-rose-500 @enderror"
                >
                    <option value="">Pilih jenjang pendidikan</option>
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
        class="w-full max-w-[570px] bg-white rounded-[20px] shadow-2xl p-7 sm:px-12 sm:py-9 transform scale-95 transition-all duration-200 relative select-none"
    >
        <!-- Modal Title: Centered, Bold, Black, Matching Frame 10 -->
        <h2 id="editModalTitle" class="text-center font-bold text-black text-2xl sm:text-[26px] tracking-tight mb-7 sm:mb-8">
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
                    class="w-full h-[52px] bg-[#E0E0E0] border border-transparent rounded-[8px] pl-[28px] pr-4 text-[17px] text-black placeholder-[#8E8E8E] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition"
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
                    class="w-full h-[52px] bg-[#E0E0E0] border border-transparent rounded-[8px] pl-[28px] pr-4 text-[17px] text-black placeholder-[#8E8E8E] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition font-mono"
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
                    class="w-full h-[52px] bg-[#E0E0E0] border border-transparent rounded-[8px] pl-[28px] pr-4 text-[17px] text-black placeholder-[#8E8E8E] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition"
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
                    class="w-full h-[52px] bg-[#E0E0E0] border border-transparent rounded-[8px] pl-[28px] pr-4 text-[17px] text-black focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition"
                >
                    <option value="" disabled>Isi Pendidikan Terakhir</option>
                    @foreach($pendidikan as $level)
                        <option value="{{ $level['id'] ?? '' }}">{{ $level['jenjang'] ?? '-' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Modal Action Buttons (Frame 10: Batal & Simpan) -->
            <div class="flex items-center justify-between gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-edit-modal w-full sm:w-[215px] h-[51px] bg-[#D9D9D9] hover:bg-[#CACACA] text-black font-semibold text-[17px] rounded-[6px] shadow-xs transition cursor-pointer active:scale-[0.99] flex items-center justify-center select-none"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    id="submitEditBtn"
                    class="w-full sm:w-[215px] h-[51px] bg-[#075B91] hover:bg-[#064e7c] text-white font-semibold text-[17px] rounded-[6px] shadow-xs transition cursor-pointer active:scale-[0.99] flex items-center justify-center select-none"
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
            <p id="detailStatus" class="hidden rounded-lg bg-blue-50 px-3 py-2 text-xs font-medium text-blue-800" role="status"></p>
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
    const filterContainer = document.getElementById('filterDropdownContainer');
    const filterToggle = document.getElementById('filterDropdownToggle');
    const filterMenu = document.getElementById('filterPopover');
    const filterChevron = document.getElementById('filterChevronIcon');
    const selectedJabatanInput = document.getElementById('filterJabatanValue');
    const searchFilterForm = document.getElementById('searchFilterForm');

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

    // ----------------------------------------
    // 1. CREATE MODAL (TAMBAHKAN TENAGA KEPENDIDIKAN)
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

    // Auto-open modal if requested via URL query (?tambah=1 or ?create=1) or if there are validation errors from previous submit
    @if(request()->boolean('tambah') || request()->boolean('create') || $errors->has('nama') || $errors->has('nip') || $errors->has('jabatan') || $errors->has('id_pendidikan'))
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
    const editPendidikan = document.getElementById('edit_kualifikasi');
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

    // Animated deletion with smooth upward slide of rows below
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
                // 1. Initial fade-out state
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

                    // 2. Animate row height collapsing so rows below it smoothly slide upwards!
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

                        // 3. Re-number remaining rows in the table
                        document.querySelectorAll('.tendik-row').forEach((r, idx) => {
                            const noCell = r.querySelector('.row-number-text') || r.querySelector('td:first-child');
                            if (noCell) noCell.textContent = (idx + 1) + '.';
                        });

                        // Show empty state if all rows are removed
                        const tbody = document.querySelector('.tendik-flow-table tbody');
                        if (tbody && tbody.querySelectorAll('.tendik-row').length === 0) {
                            const emptyRow = document.createElement('tr');
                            emptyRow.className = 'tendik-row';
                            emptyRow.innerHTML = '<td colspan="6" class="text-center py-10 text-gray-500 font-medium">Belum ada data Tenaga Kependidikan</td>';
                            tbody.appendChild(emptyRow);
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
    const detailStatus = document.getElementById('detailStatus');

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
        btn.addEventListener('click', async function () {
            if (detailNama) detailNama.textContent = '-';
            if (detailNip) detailNip.textContent = '-';
            if (detailJabatan) detailJabatan.textContent = '-';
            if (detailKualifikasi) detailKualifikasi.textContent = '-';
            if (detailStatus) {
                detailStatus.textContent = 'Memuat detail...';
                detailStatus.classList.remove('hidden', 'bg-rose-50', 'text-rose-800');
                detailStatus.classList.add('bg-blue-50', 'text-blue-800');
            }
            toggleDetailModal(true);

            try {
                const response = await fetch(this.dataset.endpoint, {
                    headers: { Accept: 'application/json' },
                });
                const contentType = response.headers.get('content-type') || '';
                if (response.redirected || !contentType.includes('application/json')) {
                    throw new Error('Sesi API berakhir atau detail tidak tersedia. Silakan login kembali.');
                }
                const result = await response.json();

                if (!response.ok || result.success !== true || !result.data) {
                    throw new Error(result.message || 'Detail Tendik tidak dapat dimuat.');
                }

                if (detailNama) detailNama.textContent = result.data.nama || '-';
                if (detailNip) detailNip.textContent = result.data.nip || '-';
                if (detailJabatan) detailJabatan.textContent = result.data.jabatan || '-';
                const selectedEducation = document.querySelector(`#create_kualifikasi option[value="${CSS.escape(String(result.data.id_pendidikan || ''))}"]`);
                if (detailKualifikasi) {
                    detailKualifikasi.textContent = result.data.kualifikasi && result.data.kualifikasi !== '-'
                        ? result.data.kualifikasi
                        : (selectedEducation?.textContent.trim() || '-');
                }
                detailStatus?.classList.add('hidden');
            } catch (error) {
                if (detailStatus) {
                    detailStatus.textContent = error.message || 'Tidak dapat terhubung ke layanan Tendik.';
                    detailStatus.classList.remove('hidden', 'bg-blue-50', 'text-blue-800');
                    detailStatus.classList.add('bg-rose-50', 'text-rose-800');
                }
            }
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
            setFilterMenuOpen(false);
        }
    });
});
</script>
@endsection
