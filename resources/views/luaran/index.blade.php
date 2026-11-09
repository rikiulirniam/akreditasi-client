@extends('layouts.app')

@section('title', 'Luaran')

@php
    $totalData = $pagination['total'] ?? count($luaranList);
@endphp

@if($totalData > 0)
    @section('subtitle', $totalData . ' Data ditemukan')
@endif

@section('content')

{{-- Container Utama Halaman Luaran (Bisa di-refresh via AJAX tanpa reload utuh) --}}
<div id="luaran-main-wrapper">

    @if(empty($luaranList) && empty($search) && empty($selectedJenis))

        {{-- ══════════════════════════════════════════════════════════════════════
             TAMPILAN KOSONG (Empty State — Sesuai Figma Screen 2)
        ══════════════════════════════════════════════════════════════════════ --}}
        <div class="flex flex-col items-center justify-center py-28 px-6 text-center select-none">
            <h2 class="text-3xl sm:text-4xl font-black text-black tracking-tight mb-2">
                Belum Ada Luaran
            </h2>
            <p class="text-sm sm:text-base text-gray-700 font-medium mb-8">
                Tekan tombol di bawah untuk menambah data
            </p>

            {{-- Tombol Buka Modal Step 1 --}}
            <button
                type="button"
                class="open-step1-btn inline-flex items-center gap-2.5 px-7 py-3 rounded-[10px] bg-[#01568D] hover:bg-primary text-white font-bold text-sm shadow-md transition cursor-pointer"
            >
                <svg class="w-5 h-5 stroke-[2.5] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="9" stroke-width="2"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.5v7m3.5-3.5h-7"/>
                </svg>
                <span>Tambah Luaran</span>
            </button>
        </div>

    @else

        {{-- ══════════════════════════════════════════════════════════════════════
             TAMPILAN DAFTAR (List State — Sesuai Figma Screen 1)
        ══════════════════════════════════════════════════════════════════════ --}}
        <div class="space-y-5">

            {{-- Toolbar: Search + Filter Jenis Luaran (Kiri) & Tombol Tambah Luaran (Kanan) --}}
            <div class="flex flex-wrap items-center justify-between gap-3 mb-2">

                {{-- Form Filter Kiri --}}
                <form method="GET" action="{{ route('luaran.index') }}" id="luaranFilterForm" class="flex items-center gap-3">

                    {{-- 1. Search Input --}}
                    <div class="relative w-64 sm:w-80 shrink-0">
                        <input
                            type="text"
                            name="search"
                            id="searchLuaranInput"
                            value="{{ $search }}"
                            placeholder="Cari nama"
                            class="w-full h-[36px] pl-4 pr-10 rounded-[10px] bg-[#D9D9D9] border-2 border-primary text-black text-sm font-medium placeholder:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary/20 transition"
                        />
                        <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-primary hover:scale-110 transition cursor-pointer" aria-label="Cari">
                            <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- 2. Dropdown Filter 5 Jenis Luaran --}}
                    <div class="relative w-40 sm:w-44 shrink-0" id="filterJenisDropdown">
                        <button
                            type="button"
                            id="filterJenisBtn"
                            class="w-full h-[36px] pl-3.5 pr-1 bg-[#D9D9D9] border-2 border-primary rounded-[10px] flex items-center justify-between cursor-pointer select-none transition focus:outline-none focus:ring-2 focus:ring-primary/20"
                            aria-expanded="false"
                            aria-haspopup="true"
                        >
                            <span id="filterJenisLabel" class="text-sm font-semibold text-gray-900 truncate pr-2">
                                {{ $selectedJenis !== '' ? $selectedJenis : 'Jenis Luaran' }}
                            </span>
                            <div class="w-7 h-7 rounded-[6px] bg-secondary border border-primary flex items-center justify-center shrink-0 shadow-2xs">
                                <svg id="filterJenisChevron" class="w-4 h-4 text-primary transition-transform duration-200" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" viewBox="0 0 24 24">
                                    <path d="m6 9 6 6 6-6"/>
                                </svg>
                            </div>
                        </button>

                        <input type="hidden" name="jenis" id="filterJenisSelect" value="{{ $selectedJenis }}">

                        <div
                            id="filterJenisMenu"
                            class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-primary rounded-[10px] shadow-lg py-1.5 z-40 overflow-hidden"
                            role="menu"
                        >
                            @foreach($jenisOptions as $opt)
                                <button
                                    type="button"
                                    class="jenis-option-btn w-full text-left px-4 py-1.5 text-xs sm:text-sm font-medium text-gray-900 hover:bg-black/5 hover:text-primary transition cursor-pointer select-none {{ $selectedJenis === $opt['value'] ? 'text-primary font-bold' : '' }}"
                                    data-value="{{ $opt['value'] }}"
                                    data-label="{{ $opt['label'] }}"
                                >
                                    {{ $opt['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                </form>

                {{-- Kanan: Tombol Tambah Luaran --}}
                <div class="flex items-center gap-3 shrink-0">
                    <button
                        type="button"
                        class="open-step1-btn h-[35px] inline-flex items-center gap-2 px-3.5 rounded-[8px] bg-[#01568D] hover:bg-primary text-white text-xs sm:text-[13px] font-bold shadow-xs transition cursor-pointer whitespace-nowrap shrink-0"
                    >
                        <svg class="w-4 h-4 stroke-[2.5] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.5v7m3.5-3.5h-7"/>
                        </svg>
                        <span>Tambah Luaran</span>
                    </button>
                </div>

            </div>

            {{-- Tabel Data Luaran --}}
            <div id="luaran-table-container" class="mt-7 min-w-0 overflow-x-auto">
                <table id="luaran-table" class="w-full text-left border-collapse" style="table-layout: fixed;">
                    <colgroup>
                        <col style="width: 3.5rem;">
                        <col>
                        <col style="width: 12rem;">
                        <col style="width: 14rem;">
                        <col style="width: 9rem;">
                        <col style="width: 7rem;">
                    </colgroup>
                    <thead>
                        <tr class="border-y border-gray-200 text-gray-900 font-semibold text-sm">
                            <th class="text-center py-3.5 px-2 sm:px-3">No</th>
                            <th class="py-3.5 px-2 sm:px-3">Judul Luaran</th>
                            <th class="py-3.5 px-2 sm:px-3">Jenis Luaran</th>
                            <th class="py-3.5 px-2 sm:px-3">Nama Penerbit</th>
                            <th class="py-3.5 px-2 sm:px-3">Tanggal terbit</th>
                            <th class="text-center py-3.5 px-2 sm:px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-800">
                        @forelse($luaranList as $item)
                            @php
                                $pageCurrent = (int) ($pagination['current_page'] ?? 1);
                                $pageLimit   = (int) ($pagination['per_page'] ?? 10);
                                $rowNumber   = $loop->iteration + (($pageCurrent - 1) * $pageLimit);
                                $itemId      = $item['id'] ?? $loop->index;
                                $kategori    = $item['kategori'] ?? 'jurnal';
                                $judul       = $item['judul'] ?? '-';
                                $jenis       = $item['jenis'] ?? '-';
                                $penerbit    = $item['nama_penerbit'] ?? '-';
                                $url         = $item['url'] ?? null;
                                $extra       = $item['extra'] ?? [];

                                // Format tanggal DD/MM/YYYY
                                $rawDate = $item['tanggal_terbit'] ?? null;
                                $tanggal = '-';
                                if ($rawDate) {
                                    try {
                                        $ts = strtotime((string) $rawDate);
                                        $tanggal = $ts ? date('d/m/Y', $ts) : (string) $rawDate;
                                    } catch (\Throwable) {
                                        $tanggal = (string) $rawDate;
                                    }
                                }

                                $extraDataJson = json_encode($extra, JSON_HEX_APOS | JSON_HEX_QUOT);
                            @endphp
                            <tr class="luaran-row border-b border-gray-200 hover:bg-gray-50/60 transition-colors duration-150 group">
                                <td class="text-center text-gray-900 py-4 px-2 sm:px-3 text-sm">{{ $rowNumber }}.</td>
                                <td class="text-sm font-medium text-gray-900 py-4 px-2 sm:px-3 break-words leading-snug">
                                    {{ $judul }}
                                </td>
                                <td class="py-4 px-2 sm:px-3 text-sm text-gray-800">
                                    {{ $jenis }}
                                </td>
                                <td class="text-sm text-gray-800 py-4 px-2 sm:px-3 break-words">{{ $penerbit }}</td>
                                <td class="text-sm text-gray-800 py-4 px-2 sm:px-3 whitespace-nowrap">{{ $tanggal }}</td>
                                <td class="py-4 px-2 sm:px-3">
                                    <div class="flex items-center justify-center gap-2 sm:gap-3">
                                        {{-- Edit --}}
                                        <button
                                            type="button"
                                            class="edit-luaran-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                            title="Edit Luaran"
                                            data-id="{{ $itemId }}"
                                            data-kategori="{{ strtolower($jenis) }}"
                                            data-extra="{{ $extraDataJson }}"
                                        >
                                            <x-icon.edit class="w-5 h-5"/>
                                        </button>

                                        {{-- Hapus --}}
                                        <button
                                            type="button"
                                            class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                            title="Hapus Luaran"
                                            data-confirm-delete
                                            data-action="{{ route('luaran.destroy', $itemId) }}"
                                            data-method="DELETE"
                                            data-fields='{"jenis":"{{ strtolower($kategori) }}"}'
                                            data-title="Hapus Data Luaran?"
                                            data-message="Apakah Anda yakin ingin menghapus &quot;{{ addslashes($judul) }}&quot;? Data tidak dapat dikembalikan."
                                        >
                                            <x-icon.delete class="w-5 h-5"/>
                                        </button>

                                        {{-- Lihat Dokumen --}}
                                        @if($url)
                                            <a
                                                href="{{ $url }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-primary hover:text-additional transition p-1 cursor-pointer"
                                                title="Buka Dokumen"
                                            >
                                                <x-icon.eye class="w-5 h-5"/>
                                            </a>
                                        @else
                                            <span class="text-gray-300 p-1 cursor-not-allowed" title="Tidak ada tautan">
                                                <x-icon.eye class="w-5 h-5"/>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-14 px-6 border-b border-gray-200 text-center">
                                    <p class="text-sm font-semibold text-gray-600">Tidak ada data luaran yang sesuai</p>
                                    <p class="text-xs text-gray-400 mt-1">Coba ubah kata kunci pencarian atau filter jenis luaran.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Container --}}
            <div id="luaran-pagination-container">
                <x-pagination :paginator="$pagination"/>
            </div>

        </div>

    @endif

</div>

{{-- ══════════════════════════════════════════════════════════════════════
     STEP 1: MODAL POP-UP PILIH JENIS LUARAN (CARD / RADIO SELECTOR)
     Opsi HANYA 5: Buku, Jurnal, Seminar, HKI, Produk
══════════════════════════════════════════════════════════════════════ --}}
<div
    id="modalStep1PilihJenis"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalStep1Title"
>
    <div class="luaran-modal-card relative bg-white rounded-[20px] p-6 sm:p-7 max-w-[460px] w-full shadow-2xl transform scale-95 transition-all duration-200 overflow-hidden">
        
        {{-- Header Modal Step 1 --}}
        <div class="text-center mb-5">
            <h3 id="modalStep1Title" class="text-xl font-bold text-gray-900 tracking-tight">
                Jenis Luaran
            </h3>
            <p class="text-xs text-gray-500 mt-1">
                Langkah 1 dari 2: Pilih jenis data luaran yang ingin ditambahkan
            </p>
        </div>

        {{-- Radio / Card Selector (Hanya 5 Jenis Pas) --}}
        <div class="space-y-2.5 my-5" id="cardSelectorJenisLuaran">

            {{-- 1. BUKU --}}
            <label class="jenis-radio-card group relative flex items-center justify-between p-3.5 rounded-xl border-2 border-gray-200 hover:border-[#01568D] hover:bg-blue-50/30 transition-all cursor-pointer select-none">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-gray-900 group-hover:text-primary transition-colors">Buku</span>
                        <span class="block text-xs text-gray-500">Buku Ajar, Referensi, Monograf, Jurnal</span>
                    </div>
                </div>
                <input type="radio" name="radio_jenis_luaran" value="Buku" class="sr-only">
                <div class="radio-indicator w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0 transition-all">
                    <div class="radio-dot w-2.5 h-2.5 rounded-full bg-primary opacity-0 transition-opacity"></div>
                </div>
            </label>

            {{-- 2. JURNAL --}}
            <label class="jenis-radio-card group relative flex items-center justify-between p-3.5 rounded-xl border-2 border-gray-200 hover:border-[#01568D] hover:bg-blue-50/30 transition-all cursor-pointer select-none">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-200 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-gray-900 group-hover:text-primary transition-colors">Jurnal</span>
                        <span class="block text-xs text-gray-500">Artikel Terindeks Scopus, Sinta, dll.</span>
                    </div>
                </div>
                <input type="radio" name="radio_jenis_luaran" value="Jurnal" class="sr-only">
                <div class="radio-indicator w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0 transition-all">
                    <div class="radio-dot w-2.5 h-2.5 rounded-full bg-primary opacity-0 transition-opacity"></div>
                </div>
            </label>

            {{-- 3. SEMINAR --}}
            <label class="jenis-radio-card group relative flex items-center justify-between p-3.5 rounded-xl border-2 border-gray-200 hover:border-[#01568D] hover:bg-blue-50/30 transition-all cursor-pointer select-none">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-gray-900 group-hover:text-primary transition-colors">Seminar</span>
                        <span class="block text-xs text-gray-500">Prosiding Seminar & Konferensi Ilmiah</span>
                    </div>
                </div>
                <input type="radio" name="radio_jenis_luaran" value="Seminar" class="sr-only">
                <div class="radio-indicator w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0 transition-all">
                    <div class="radio-dot w-2.5 h-2.5 rounded-full bg-primary opacity-0 transition-opacity"></div>
                </div>
            </label>

            {{-- 4. HKI --}}
            <label class="jenis-radio-card group relative flex items-center justify-between p-3.5 rounded-xl border-2 border-gray-200 hover:border-[#01568D] hover:bg-blue-50/30 transition-all cursor-pointer select-none">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-200 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-gray-900 group-hover:text-primary transition-colors">HKI</span>
                        <span class="block text-xs text-gray-500">Hak Cipta, Paten, Merek, Desain Industri</span>
                    </div>
                </div>
                <input type="radio" name="radio_jenis_luaran" value="HKI" class="sr-only">
                <div class="radio-indicator w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0 transition-all">
                    <div class="radio-dot w-2.5 h-2.5 rounded-full bg-primary opacity-0 transition-opacity"></div>
                </div>
            </label>

            {{-- 5. PRODUK --}}
            <label class="jenis-radio-card group relative flex items-center justify-between p-3.5 rounded-xl border-2 border-gray-200 hover:border-[#01568D] hover:bg-blue-50/30 transition-all cursor-pointer select-none">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-200 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-gray-900 group-hover:text-primary transition-colors">Produk</span>
                        <span class="block text-xs text-gray-500">Produk Terapan, Prototipe, Perangkat Lunak</span>
                    </div>
                </div>
                <input type="radio" name="radio_jenis_luaran" value="Produk" class="sr-only">
                <div class="radio-indicator w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0 transition-all">
                    <div class="radio-dot w-2.5 h-2.5 rounded-full bg-primary opacity-0 transition-opacity"></div>
                </div>
            </label>

        </div>

        {{-- Action Buttons: Batal & Lanjut (Simpan Pilihan) --}}
        <div class="grid grid-cols-2 gap-4 pt-2">
            <button
                type="button"
                id="btnBatalStep1"
                class="w-full py-2.5 px-6 rounded-[8px] bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
            >
                Batal
            </button>
            <button
                type="button"
                id="btnLanjutStep2"
                class="w-full py-2.5 px-6 rounded-[8px] bg-[#01568D] hover:bg-primary text-white font-bold text-sm shadow-xs transition cursor-pointer text-center flex items-center justify-center gap-2"
            >
                <span>Lanjut</span>
                <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                </svg>
            </button>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     STEP 2: MODAL POP-UP FORM INPUT SPESIFIK (AJAX SUBMIT KE POST /api/luaran)
══════════════════════════════════════════════════════════════════════ --}}
<div
    id="modalStep2Form"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalStep2Title"
>
    <div class="luaran-modal-card relative bg-white rounded-[20px] p-7 sm:p-8 max-w-[500px] w-full shadow-2xl transform scale-95 transition-all duration-200 overflow-hidden max-h-[90vh] overflow-y-auto">
        
        {{-- Header Form Step 2 dengan Badge Kategori --}}
        <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-primary/70">Langkah 2 dari 2</span>
                <h3 id="modalStep2Title" class="text-xl font-bold text-gray-900 tracking-tight">
                    Tambah Luaran
                </h3>
            </div>
            <span id="badgeJenisLuaran" class="px-3 py-1 rounded-full text-xs font-bold bg-[#01568D]/10 text-[#01568D] border border-[#01568D]/20">
                Buku
            </span>
        </div>

        {{-- Form Container --}}
        <form id="formCreateLuaranAjax" action="{{ route('luaran.store') }}" method="POST" class="space-y-4">
            @csrf
            {{-- Hidden input yang selalu mengirim jenis_luaran --}}
            <input type="hidden" name="jenis_luaran" id="hiddenInputJenisLuaran" value="Buku">

            {{-- 1. SUBFORM BUKU (Gambar 2) --}}
            <div id="subform_Buku" class="step2-subform space-y-4 hidden">
                @include('luaran._form_buku', ['prefix' => 'create_buku', 'data' => []])
            </div>

            {{-- 2. SUBFORM JURNAL (Gambar 3) --}}
            <div id="subform_Jurnal" class="step2-subform space-y-4 hidden">
                @include('luaran._form_jurnal', ['prefix' => 'create_jurnal', 'data' => []])
            </div>

            {{-- 3. SUBFORM SEMINAR (Gambar 4) --}}
            <div id="subform_Seminar" class="step2-subform space-y-4 hidden">
                @include('luaran._form_seminar', ['prefix' => 'create_seminar', 'data' => []])
            </div>

            {{-- 4. SUBFORM HKI (Starter / Skeleton) --}}
            <div id="subform_HKI" class="step2-subform space-y-4 hidden">
                @include('luaran._form_hki', ['prefix' => 'create_hki', 'data' => []])
            </div>

            {{-- 5. SUBFORM PRODUK (Starter / Skeleton) --}}
            <div id="subform_Produk" class="step2-subform space-y-4 hidden">
                @include('luaran._form_produk', ['prefix' => 'create_produk', 'data' => []])
            </div>

            {{-- Action Buttons: Kembali ke Step 1 & Simpan Data --}}
            @include('luaran._form_actions', ['backId' => 'btnKembaliKeStep1', 'submitText' => 'Simpan'])
        </form>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     MODAL EDIT (Untuk memperbarui data yang sudah ada)
══════════════════════════════════════════════════════════════════════ --}}
<div
    id="modalEditLuaran"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalEditTitle"
>
    <div class="luaran-modal-card relative bg-white rounded-[20px] p-7 sm:p-8 max-w-[500px] w-full shadow-2xl transform scale-95 transition-all duration-200 overflow-hidden max-h-[90vh] overflow-y-auto">
        <h3 id="modalEditTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Edit Luaran
        </h3>

        <form id="formEditLuaranAjax" action="#" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="jenis_luaran" id="editHiddenJenisLuaran" value="Jurnal">

            <div id="editSubform_Buku" class="edit-subform space-y-4 hidden">
                @include('luaran._form_buku', ['prefix' => 'edit_buku', 'data' => []])
            </div>
            <div id="editSubform_Jurnal" class="edit-subform space-y-4 hidden">
                @include('luaran._form_jurnal', ['prefix' => 'edit_jurnal', 'data' => []])
            </div>
            <div id="editSubform_Seminar" class="edit-subform space-y-4 hidden">
                @include('luaran._form_seminar', ['prefix' => 'edit_seminar', 'data' => []])
            </div>
            <div id="editSubform_HKI" class="edit-subform space-y-4 hidden">
                @include('luaran._form_hki', ['prefix' => 'edit_hki', 'data' => []])
            </div>
            <div id="editSubform_Produk" class="edit-subform space-y-4 hidden">
                @include('luaran._form_produk', ['prefix' => 'edit_produk', 'data' => []])
            </div>

            @include('luaran._form_actions', ['closeId' => 'closeEditModalBtn', 'cancelText' => 'Batal', 'submitText' => 'Perbarui'])
        </form>
    </div>
</div>

{{-- Global Confirm Delete Component --}}
<x-confirm-delete/>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // ── Helper Animasi Modal ───────────────────────────────────────────────
    function showModal(modalEl) {
        if (!modalEl) return;
        const card = modalEl.querySelector('.luaran-modal-card');
        modalEl.classList.remove('opacity-0', 'pointer-events-none');
        modalEl.classList.add('opacity-100');
        if (card) {
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        }
        document.body.classList.add('overflow-hidden');
    }

    function hideModal(modalEl) {
        if (!modalEl) return;
        const card = modalEl.querySelector('.luaran-modal-card');
        modalEl.classList.remove('opacity-100');
        modalEl.classList.add('opacity-0', 'pointer-events-none');
        if (card) {
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
        }
        document.body.classList.remove('overflow-hidden');
    }

    // ── STEP 1: Modal Pilih Jenis Luaran ────────────────────────────────────
    const modalStep1 = document.getElementById('modalStep1PilihJenis');
    const modalStep2 = document.getElementById('modalStep2Form');
    const btnBatalStep1 = document.getElementById('btnBatalStep1');
    const btnLanjutStep2 = document.getElementById('btnLanjutStep2');
    const btnKembaliKeStep1 = document.getElementById('btnKembaliKeStep1');

    const radioCards = document.querySelectorAll('.jenis-radio-card');
    let selectedJenisLuaran = '';

    // Reset dan aktifkan state radio card saat diklik
    radioCards.forEach(card => {
        card.addEventListener('click', function () {
            radioCards.forEach(c => {
                c.classList.remove('border-primary', 'bg-primary/5', 'shadow-xs');
                c.classList.add('border-gray-200');
                const dot = c.querySelector('.radio-dot');
                const ind = c.querySelector('.radio-indicator');
                if (dot) dot.classList.add('opacity-0');
                if (ind) ind.classList.remove('border-primary');
            });

            this.classList.remove('border-gray-200');
            this.classList.add('border-primary', 'bg-primary/5', 'shadow-xs');

            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                selectedJenisLuaran = radio.value;
            }

            const activeDot = this.querySelector('.radio-dot');
            const activeInd = this.querySelector('.radio-indicator');
            if (activeDot) activeDot.classList.remove('opacity-0');
            if (activeInd) activeInd.classList.add('border-primary');
        });
    });

    // Buka Modal Step 1 saat tombol Tambah Luaran diklik
    document.querySelectorAll('.open-step1-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            // Default pilih Buku jika belum ada pilihan
            if (!selectedJenisLuaran && radioCards[0]) {
                radioCards[0].click();
            }
            showModal(modalStep1);
        });
    });

    // Tutup Step 1
    btnBatalStep1?.addEventListener('click', () => hideModal(modalStep1));

    // Lanjut ke Step 2 (Transisi antar modal)
    btnLanjutStep2?.addEventListener('click', function () {
        if (!selectedJenisLuaran) {
            if (typeof window.showToast === 'function') {
                window.showToast('Silakan pilih salah satu jenis luaran terlebih dahulu.', 'warning', 'Peringatan');
            } else {
                alert('Silakan pilih salah satu jenis luaran terlebih dahulu.');
            }
            return;
        }

        hideModal(modalStep1);

        setTimeout(() => {
            bukaModalStep2(selectedJenisLuaran);
        }, 150);
    });

    // Kembali dari Step 2 ke Step 1
    btnKembaliKeStep1?.addEventListener('click', function () {
        hideModal(modalStep2);
        setTimeout(() => {
            showModal(modalStep1);
        }, 150);
    });

    // ── STEP 2: Render Form Sesuai Jenis Luaran ─────────────────────────────
    const titleStep2 = document.getElementById('modalStep2Title');
    const badgeJenis = document.getElementById('badgeJenisLuaran');
    const hiddenJenis = document.getElementById('hiddenInputJenisLuaran');
    const step2Subforms = document.querySelectorAll('.step2-subform');

    function bukaModalStep2(jenis) {
        if (titleStep2) titleStep2.textContent = `Tambah ${jenis}`;
        if (badgeJenis) badgeJenis.textContent = jenis;
        if (hiddenJenis) hiddenJenis.value = jenis;

        // Tampilkan hanya subform yang sesuai
        step2Subforms.forEach(sub => sub.classList.add('hidden'));
        const targetSubform = document.getElementById(`subform_${jenis}`);
        if (targetSubform) {
            targetSubform.classList.remove('hidden');
        }

        showModal(modalStep2);
    }

    // ── AJAX SUBMIT KE POST /api/luaran (Via Controller Proxy) ──────────────
    const formCreateAjax = document.getElementById('formCreateLuaranAjax');

    formCreateAjax?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = this.querySelector('.btn-submit-luaran');
        const btnText = submitBtn?.querySelector('.btn-text');
        const btnSpinner = submitBtn?.querySelector('.btn-spinner');

        // State loading
        if (submitBtn) submitBtn.disabled = true;
        if (btnText) btnText.textContent = 'Menyimpan...';
        if (btnSpinner) btnSpinner.classList.remove('hidden');

        try {
            const formData = new FormData(this);
            const payload = {};
            formData.forEach((val, key) => {
                if (key !== '_token' && val !== '') {
                    payload[key] = val;
                }
            });

            // Pastikan atribut jenis_luaran selalu terkirim
            payload['jenis_luaran'] = selectedJenisLuaran || hiddenJenis?.value || 'Jurnal';

            const response = await fetch("{{ route('luaran.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok || result.success === false) {
                const errMsg = result.message || 'Terjadi kesalahan saat menyimpan data luaran.';
                throw new Error(errMsg);
            }

            // Notifikasi sukses via komponen Toast
            if (typeof window.showToast === 'function') {
                window.showToast(result.message || `Data ${payload['jenis_luaran']} berhasil ditambahkan.`, 'success', 'Berhasil');
            }

            // Tutup modal Step 2 & Reset form
            hideModal(modalStep2);
            formCreateAjax.reset();

            // Refresh tabel secara dinamis tanpa reload halaman
            await refreshTableData();

        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error.message, 'error', 'Gagal');
            } else {
                alert(error.message);
            }
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            if (btnText) btnText.textContent = 'Simpan';
            if (btnSpinner) btnSpinner.classList.add('hidden');
        }
    });

    // ── FUNGSI REFRESH DATA TABEL OTOMATIS (SPA-Like Update) ────────────────
    async function refreshTableData() {
        try {
            const res = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;

            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newContent = doc.getElementById('luaran-main-wrapper');
            const currentContent = document.getElementById('luaran-main-wrapper');

            if (newContent && currentContent) {
                currentContent.innerHTML = newContent.innerHTML;
                attachTableRowListeners();
            }
        } catch (e) {
            console.error('Gagal memperbarui tabel:', e);
        }
    }

    // ── EDIT FLOW ──────────────────────────────────────────────────────────
    const modalEdit = document.getElementById('modalEditLuaran');
    const formEditAjax = document.getElementById('formEditLuaranAjax');
    const editSubforms = document.querySelectorAll('.edit-subform');
    const editHiddenJenis = document.getElementById('editHiddenJenisLuaran');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');

    closeEditModalBtn?.addEventListener('click', () => hideModal(modalEdit));

    function attachTableRowListeners() {
        document.querySelectorAll('.edit-luaran-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                let rawKat = (this.dataset.kategori || 'jurnal').toLowerCase();
                let kategori = 'Jurnal';

                if (rawKat.includes('buku')) kategori = 'Buku';
                else if (rawKat.includes('seminar') || rawKat.includes('prosiding')) kategori = 'Seminar';
                else if (rawKat.includes('hki') || rawKat.includes('hak cipta') || rawKat.includes('paten')) kategori = 'HKI';
                else if (rawKat.includes('produk')) kategori = 'Produk';

                let extra = {};
                try { extra = JSON.parse(this.dataset.extra || '{}'); } catch(e) {}

                if (formEditAjax) {
                    formEditAjax.action = `/luaran/${id}`;
                }
                if (editHiddenJenis) {
                    editHiddenJenis.value = kategori;
                }

                editSubforms.forEach(s => s.classList.add('hidden'));
                const activeEditSub = document.getElementById(`editSubform_${kategori}`);
                if (activeEditSub) {
                    activeEditSub.classList.remove('hidden');
                    populateEditFields(activeEditSub, kategori, extra);
                }

                showModal(modalEdit);
            });
        });
    }

    function populateEditFields(container, kategori, data) {
        function val(name, value) {
            const el = container.querySelector(`[name="${name}"]`);
            if (el && value !== undefined && value !== null) el.value = value;
        }

        if (kategori === 'Buku') {
            val('judul_buku', data.judul_buku ?? data.judul ?? '');
            val('isbn', data.isbn ?? '');
            val('nama_penerbit', data.penerbit_buku ?? data.nama_penerbit ?? data.penerbit ?? '');
            val('tahun_terbit', data.tahun_terbit ?? data.tahun ?? '');
            val('jenis_buku', data.jenis_buku ?? '');
            val('bidang_ilmu', data.bidang_ilmu ?? '');
            val('total_halaman', data.total_halaman ?? '');
            val('url_buku', data.url_buku ?? data.url ?? '');
        } else if (kategori === 'Seminar') {
            val('nama_seminar', data.nama_seminar ?? data.judul_publikasi ?? data.judul ?? '');
            val('nama_prosiding', data.nama_prosiding ?? data.nama_penerbit ?? '');
            val('isbn', data.isbn ?? '');
            val('penyelenggara', data.penyelenggara ?? '');
            val('tanggal_seminar', data.tanggal_seminar ?? data.tanggal_terbit ?? '');
            val('lokasi_seminar', data.lokasi_seminar ?? '');
            val('total_halaman', data.total_halaman ?? '');
            val('doi', data.doi ?? '');
            val('url_publikasi', data.url_publikasi ?? data.url ?? '');
        } else if (kategori === 'HKI') {
            val('judul_hak_cipta', data.judul_hak_cipta ?? data.judul ?? '');
            val('nama_penerbit', data.nama_penerbit ?? '');
            val('jenis_hki', data.jenis_hki ?? '');
            val('nomor_pendaftaran_hki', data.nomor_pendaftaran_hki ?? '');
            val('status_hki', data.status_hki ?? '');
            val('tanggal_terbit', data.tanggal_terbit ?? '');
            val('url_hki', data.url_hki ?? data.url ?? '');
        } else if (kategori === 'Produk') {
            val('nama_produk', data.nama_produk ?? data.judul ?? '');
            val('penerbit_produk', data.penerbit_produk ?? data.nama_penerbit ?? '');
            val('deskripsi', data.deskripsi ?? '');
            val('tanggal_terbit', data.tanggal_terbit ?? '');
            val('url_produk', data.url_produk ?? data.url ?? '');
        } else {
            val('nama_jurnal', data.nama_jurnal ?? data.judul_publikasi ?? data.judul ?? '');
            val('issn', data.issn ?? '');
            val('volume', data.volume ?? '');
            val('tanggal_terbit', data.tanggal_terbit ?? '');
            val('doi', data.doi ?? '');
            val('nomor_edisi', data.nomor_edisi ?? '');
            val('total_halaman', data.total_halaman ?? '');
            val('indeksasi', data.indeksasi ?? '');
            val('url_publikasi', data.url_publikasi ?? data.url ?? '');
        }
    }

    formEditAjax?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = this.querySelector('.btn-submit-luaran');
        const btnText = submitBtn?.querySelector('.btn-text');
        const btnSpinner = submitBtn?.querySelector('.btn-spinner');

        if (submitBtn) submitBtn.disabled = true;
        if (btnText) btnText.textContent = 'Menyimpan...';
        if (btnSpinner) btnSpinner.classList.remove('hidden');

        try {
            const formData = new FormData(this);
            const payload = {};
            formData.forEach((val, key) => {
                if (key !== '_token' && key !== '_method' && val !== '') {
                    payload[key] = val;
                }
            });

            const actionUrl = this.action;
            const response = await fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json().catch(() => ({}));
            if (!response.ok || result.success === false) {
                throw new Error(result.message || 'Gagal memperbarui data.');
            }

            if (typeof window.showToast === 'function') {
                window.showToast(result.message || 'Data berhasil diperbarui.', 'success', 'Berhasil');
            }

            hideModal(modalEdit);
            await refreshTableData();

        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error.message, 'error', 'Gagal');
            } else {
                alert(error.message);
            }
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            if (btnText) btnText.textContent = 'Perbarui';
            if (btnSpinner) btnSpinner.classList.add('hidden');
        }
    });

    // Pasang listeners awal
    attachTableRowListeners();

    // ── FILTER TOOLBAR DROPDOWN ────────────────────────────────────────────
    const filterJenisBtn = document.getElementById('filterJenisBtn');
    const filterJenisMenu = document.getElementById('filterJenisMenu');
    const filterJenisChev = document.getElementById('filterJenisChevron');
    const filterJenisHidden = document.getElementById('filterJenisSelect');
    const filterJenisLabel = document.getElementById('filterJenisLabel');

    filterJenisBtn?.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = filterJenisMenu.classList.toggle('hidden');
        filterJenisChev?.classList.toggle('rotate-180', !isOpen);
        filterJenisBtn.setAttribute('aria-expanded', String(!isOpen));
    });

    document.querySelectorAll('.jenis-option-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const val = this.dataset.value;
            const label = this.dataset.label;
            if (filterJenisHidden) filterJenisHidden.value = val;
            if (filterJenisLabel) filterJenisLabel.textContent = label;
            filterJenisMenu?.classList.add('hidden');
            filterJenisChev?.classList.remove('rotate-180');
            document.getElementById('luaranFilterForm')?.submit();
        });
    });

    document.addEventListener('click', function (e) {
        if (!document.getElementById('filterJenisDropdown')?.contains(e.target)) {
            filterJenisMenu?.classList.add('hidden');
            filterJenisChev?.classList.remove('rotate-180');
        }
    });

    // Close on backdrop / escape
    [modalStep1, modalStep2, modalEdit].forEach(modal => {
        modal?.addEventListener('click', function (e) {
            if (e.target === modal) hideModal(modal);
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            hideModal(modalStep1);
            hideModal(modalStep2);
            hideModal(modalEdit);
        }
    });

});
</script>
@endpush
