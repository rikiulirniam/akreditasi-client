@extends('layouts.app')

@section('title', 'Program Studi')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Top Action Bar: Search Input & Tambah Prodi Button -->
        <div class="flex items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
                <x-input 
                    type="text" 
                    id="searchProdiInput"
                    placeholder="Cari..." 
                    class="pl-10 text-xs"
                />
            </div>
            <div>
                <button 
                    type="button" 
                    id="openCreateProdiModalBtn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Prodi
                </button>
            </div>
        </div>

        <!-- Table: Data Program Studi -->
        <x-table>
            <x-slot:header>
                <x-table.th>No</x-table.th>
                <x-table.th>Nama Prodi</x-table.th>
                <x-table.th>Akreditasi</x-table.th>
                <x-table.th>Nama UPPS</x-table.th>
                <x-table.th>Action</x-table.th>
            </x-slot:header>

            @forelse($prodiList as $index => $item)
                @php
                    $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                    $upps = $item['upps'] ?? null;
                    $hasUpps = ! empty($upps);
                @endphp
                <x-table.row class="prodi-row">
                    <!-- No -->
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $rowNumber }}
                    </x-table.cell>

                    <!-- Nama Prodi -->
                    <x-table.cell class="w-72">
                        <span class="font-extrabold text-sm text-gray-900 prodi-name tracking-tight font-mono">
                            {{ $item['nama_prodi'] ?? '-' }}
                        </span>
                    </x-table.cell>

                    <!-- Akreditasi -->
                    <x-table.cell class="w-36">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 prodi-akreditasi">
                            {{ $item['akreditasi'] ?? '-' }}
                        </span>
                    </x-table.cell>

                    <!-- Nama UPPS -->
                    <x-table.cell>
                        <span class="text-sm font-medium text-gray-700 prodi-upps">
                            {{ $item['nama_upps'] ?? '-' }}
                        </span>
                    </x-table.cell>

                    <!-- Action (Yellow Edit, Red Delete, Navy View) -->
                    <x-table.cell class="w-28">
                        <div class="flex items-center justify-end gap-3">
                            <!-- Edit Prodi Icon (Yellow) -->
                            <button 
                                type="button" 
                                class="open-edit-prodi-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Prodi"
                                data-id="{{ $item['id'] }}"
                                data-nama="{{ $item['nama_prodi'] }}"
                                data-akreditasi="{{ $item['akreditasi'] }}"
                                data-kode="{{ $item['kode_prodi'] ?? '' }}"
                                data-action="{{ route('prodi.update', $item['id']) }}"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>

                            <!-- Delete Prodi Icon (Red) -->
                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Prodi"
                                data-confirm-delete
                                data-action="{{ route('prodi.destroy', $item['id']) }}"
                                data-title="Hapus Program Studi?"
                                data-message="Apakah Anda yakin ingin menghapus Program Studi ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>

                            <!-- View / Detail UPPS Icon (Navy Blue) -->
                            <button 
                                type="button" 
                                class="open-upps-detail-btn text-primary hover:text-additional transition p-1 cursor-pointer"
                                title="Detail UPPS"
                                data-has-upps="{{ $hasUpps ? '1' : '0' }}"
                                data-prodi-id="{{ $item['id'] }}"
                                data-prodi-nama="{{ $item['nama_prodi'] }}"
                                data-upps-id="{{ $upps['id'] ?? '' }}"
                                data-upps-nama="{{ $upps['nama_upps'] ?? '' }}"
                                data-upps-pimpinan="{{ $upps['pimpinan_upps'] ?? '' }}"
                                data-upps-jenis="{{ $upps['jenis'] ?? '' }}"
                                data-upps-kode="{{ $upps['kode_upps'] ?? '' }}"
                                data-destroy-url="{{ $hasUpps ? route('upps.destroy', $upps['id']) : '' }}"
                                data-update-url="{{ $hasUpps ? route('upps.update', $upps['id']) : '' }}"
                            >
                                <x-icon.eye class="w-5 h-5" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="5" message="Belum ada data program studi" submessage="Silakan klik tombol Tambah Prodi untuk membuat program studi baru." />
            @endforelse
        </x-table>

        <!-- Pagination -->
        <x-pagination :paginator="$pagination" />
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Tambahkan Prodi -->
<!-- ========================================== -->
<div 
    id="createProdiModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createProdiModalTitle"
>
    <div class="prodi-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createProdiModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan Prodi
        </h3>

        <form action="{{ route('prodi.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Field 1: Nama Prodi -->
            <div>
                <label for="createNamaProdi" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama Prodi
                </label>
                <input 
                    type="text" 
                    name="nama_prodi" 
                    id="createNamaProdi" 
                    placeholder="Masukkan nama" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Akreditasi -->
            <div>
                <label for="createAkreditasi" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Akrditasi
                </label>
                <div class="relative">
                    <select 
                        name="akreditasi" 
                        id="createAkreditasi" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih akreditasi</option>
                        <option value="Unggul">Unggul</option>
                        <option value="Baik Sekali">Baik Sekali</option>
                        <option value="Baik">Baik</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="Tidak Terakreditasi">Tidak Terakreditasi</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-800">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-prodi-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Perbarui Prodi -->
<!-- ========================================== -->
<div 
    id="editProdiModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editProdiModalTitle"
>
    <div class="prodi-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editProdiModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Perbarui Prodi
        </h3>

        <form id="editProdiForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" name="kode_prodi" id="editKodeProdi">

            <!-- Field 1: Nama Prodi -->
            <div>
                <label for="editNamaProdi" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama Prodi
                </label>
                <input 
                    type="text" 
                    name="nama_prodi" 
                    id="editNamaProdi" 
                    placeholder="Masukkan nama" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Akreditasi -->
            <div>
                <label for="editAkreditasi" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Akrditasi
                </label>
                <div class="relative">
                    <select 
                        name="akreditasi" 
                        id="editAkreditasi" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih akreditasi</option>
                        <option value="Unggul">Unggul</option>
                        <option value="Baik Sekali">Baik Sekali</option>
                        <option value="Baik">Baik</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="Tidak Terakreditasi">Tidak Terakreditasi</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-800">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Modal Action Buttons (Batal & Perbarui) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-prodi-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: Detail UPPS -->
<!-- ======================================================== -->
<div 
    id="uppsEmptyDetailModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="uppsEmptyDetailTitle"
>
    <div class="prodi-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-center transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="uppsEmptyDetailTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Detail UPPS
        </h3>

        <!-- Card Container with Light Gray Background -->
        <div class="bg-[#F8F9FA] rounded-2xl p-8 sm:p-10 border border-gray-200/70 text-center my-4">
            <p class="text-sm sm:text-base font-semibold text-gray-800 leading-relaxed max-w-xs mx-auto mb-6">
                Kamu belum ada data UPPS, silahkan tekan tombol tambah untuk Menambahkan UPPS
            </p>

            <button 
                type="button" 
                id="btnTriggerTambahUpps"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer"
            >
                <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tambah UPPS
            </button>
        </div>

        <div class="pt-2">
            <button 
                type="button" 
                class="close-prodi-modal w-full py-2.5 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-gray-800 font-bold text-sm transition cursor-pointer text-center"
            >
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: Detail UPPS -->
<!-- ======================================================== -->
<div 
    id="uppsFilledDetailModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="uppsFilledDetailTitle"
>
    <div class="prodi-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="uppsFilledDetailTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Detail UPPS
        </h3>

        <div class="space-y-4">
            <!-- Field 1: Nama UPPS -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Nama UPPS</label>
                <input 
                    type="text" 
                    id="viewUppsNama" 
                    readonly 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-800 cursor-default select-none focus:outline-none"
                >
            </div>

            <!-- Field 2: Pimpinan UPPS -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Pimpinan UPPS</label>
                <input 
                    type="text" 
                    id="viewUppsPimpinan" 
                    readonly 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-800 cursor-default select-none focus:outline-none"
                >
            </div>

            <!-- Field 3: Jenis UPPS -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Jenis UPPS</label>
                <input 
                    type="text" 
                    id="viewUppsJenis" 
                    readonly 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-800 cursor-default select-none focus:outline-none"
                >
            </div>

            <!-- Field 4: Anggota UPPS -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Anggota UPPS</label>
                <a 
                    id="linkUppsAnggota" 
                    href="#" 
                    class="w-full flex items-center justify-between px-4 py-2.5 rounded-[10px] bg-primary/10 hover:bg-primary hover:text-white text-primary transition font-semibold text-sm cursor-pointer group"
                    title="Kelola Anggota UPPS"
                >
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6 0 3.375 3.375 0 016 0zm6 2.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                        <span id="viewUppsAnggotaText">Lihat Data Anggota</span>
                    </div>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>

            <!-- Action Buttons: Hapus Data (Red), Edit Data (Yellow), OK (Navy) - Gambar 3 -->
            <div class="grid grid-cols-3 gap-2.5 pt-4">
                <button 
                    type="button" 
                    id="btnDetailHapusUpps"
                    class="w-full flex items-center justify-center gap-1.5 py-3 px-2 rounded-xl bg-[#C9352B] hover:bg-[#b02c23] text-white font-bold text-xs sm:text-sm transition cursor-pointer text-center"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Hapus Data
                </button>
                <button 
                    type="button" 
                    id="btnDetailEditUpps"
                    class="w-full flex items-center justify-center gap-1.5 py-3 px-2 rounded-xl bg-[#EAB308] hover:bg-[#ca8a04] text-white font-bold text-xs sm:text-sm transition cursor-pointer text-center"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    Edit Data
                </button>
                <button 
                    type="button" 
                    class="close-prodi-modal w-full py-3 px-2 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-xs sm:text-sm transition cursor-pointer text-center"
                >
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Tambah UPPS -->
<!-- ========================================== -->
<div 
    id="createUppsModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createUppsModalTitle"
>
    <div class="prodi-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createUppsModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambah UPPS
        </h3>

        <form action="{{ route('upps.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Field 1: Nama UPPS -->
            <div>
                <label for="createNamaUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama UPPS
                </label>
                <input 
                    type="text" 
                    name="nama_upps" 
                    id="createNamaUpps" 
                    placeholder="Masukkan nama" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Pimpinan UPPS -->
            <div>
                <label for="createPimpinanUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Pimpinan UPPS
                </label>
                <input 
                    type="text" 
                    name="pimpinan_upps" 
                    id="createPimpinanUpps" 
                    placeholder="Masukkan nama pimpinan" 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 3: Jenis UPPS -->
            <div>
                <label for="createJenisUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jenis UPPS
                </label>
                <div class="relative">
                    <select 
                        name="jenis" 
                        id="createJenisUpps" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled selected>Masukkan Jenis</option>
                        <option value="Jurusan">Jurusan</option>
                        <option value="Fakultas">Fakultas</option>
                        <option value="Direktorat">Direktorat</option>
                        <option value="Departemen">Departemen</option>
                        <option value="Program Pascasarjana">Program Pascasarjana</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-800">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Field 4: Anggota UPPS (Prodi) -->
            <div>
                <label for="createAnggotaUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Anggota UPPS
                </label>
                <div class="relative">
                    <select 
                        name="prodi_id" 
                        id="createAnggotaUpps" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih anggota UPPS</option>
                        @foreach($allProdis as $p)
                            <option value="{{ $p['id'] }}">{{ $p['nama_prodi'] }}</option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-800">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-prodi-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Update UPPS  -->
<!-- ========================================== -->
<div 
    id="updateUppsModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="updateUppsModalTitle"
>
    <div class="prodi-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="updateUppsModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Update UPPS
        </h3>

        <form id="updateUppsForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" name="kode_upps" id="updateKodeUpps">

            <!-- Field 1: Nama UPPS -->
            <div>
                <label for="updateNamaUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama UPPS
                </label>
                <input 
                    type="text" 
                    name="nama_upps" 
                    id="updateNamaUpps" 
                    placeholder="Masukkan nama" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Pimpinan UPPS -->
            <div>
                <label for="updatePimpinanUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Pimpinan UPPS
                </label>
                <input 
                    type="text" 
                    name="pimpinan_upps" 
                    id="updatePimpinanUpps" 
                    placeholder="Masukkan nama pimpinan" 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 3: Jenis UPPS -->
            <div>
                <label for="updateJenisUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jenis UPPS
                </label>
                <div class="relative">
                    <select 
                        name="jenis" 
                        id="updateJenisUpps" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled>Masukkan Jenis</option>
                        <option value="Jurusan">Jurusan</option>
                        <option value="Fakultas">Fakultas</option>
                        <option value="Direktorat">Direktorat</option>
                        <option value="Departemen">Departemen</option>
                        <option value="Program Pascasarjana">Program Pascasarjana</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-800">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Field 4: Anggota UPPS (Prodi) -->
            <div>
                <label for="updateAnggotaUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Anggota UPPS
                </label>
                <div class="relative">
                    <select 
                        name="prodi_id" 
                        id="updateAnggotaUpps" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih anggota UPPS</option>
                        @foreach($allProdis as $p)
                            <option value="{{ $p['id'] }}">{{ $p['nama_prodi'] }}</option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-800">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-prodi-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Search Filter
        const searchInput = document.getElementById('searchProdiInput');
        const rows = document.querySelectorAll('.prodi-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const name = row.querySelector('.prodi-name')?.textContent.toLowerCase() || '';
                    const akreditasi = row.querySelector('.prodi-akreditasi')?.textContent.toLowerCase() || '';
                    const upps = row.querySelector('.prodi-upps')?.textContent.toLowerCase() || '';
                    const match = name.includes(keyword) || akreditasi.includes(keyword) || upps.includes(keyword);
                    row.style.display = match ? '' : 'none';
                });
            });
        }

        // Generic Modal Helpers
        function showModal(modal) {
            if (!modal) return;
            const card = modal.querySelector('.prodi-modal-card');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modal) {
            if (!modal) return;
            const card = modal.querySelector('.prodi-modal-card');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        const createProdiModal = document.getElementById('createProdiModal');
        const openCreateProdiBtn = document.getElementById('openCreateProdiModalBtn');
        if (openCreateProdiBtn) {
            openCreateProdiBtn.addEventListener('click', () => showModal(createProdiModal));
        }

        const editProdiModal = document.getElementById('editProdiModal');
        const editProdiForm = document.getElementById('editProdiForm');
        const editNamaProdi = document.getElementById('editNamaProdi');
        const editAkreditasi = document.getElementById('editAkreditasi');
        const editKodeProdi = document.getElementById('editKodeProdi');
        const editButtons = document.querySelectorAll('.open-edit-prodi-btn');

        editButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const action = this.getAttribute('data-action');
                const nama = this.getAttribute('data-nama');
                const akreditasi = this.getAttribute('data-akreditasi');
                const kode = this.getAttribute('data-kode');

                if (editProdiForm) editProdiForm.action = action;
                if (editNamaProdi) editNamaProdi.value = nama || '';
                if (editAkreditasi) editAkreditasi.value = akreditasi || '';
                if (editKodeProdi) editKodeProdi.value = kode || '';

                showModal(editProdiModal);
            });
        });

        const uppsEmptyModal = document.getElementById('uppsEmptyDetailModal');
        const uppsFilledModal = document.getElementById('uppsFilledDetailModal');
        const createUppsModal = document.getElementById('createUppsModal');
        const updateUppsModal = document.getElementById('updateUppsModal');

        const createAnggotaUppsSelect = document.getElementById('createAnggotaUpps');
        const btnTriggerTambahUpps = document.getElementById('btnTriggerTambahUpps');

        const viewUppsNama = document.getElementById('viewUppsNama');
        const viewUppsPimpinan = document.getElementById('viewUppsPimpinan');
        const viewUppsJenis = document.getElementById('viewUppsJenis');
        const viewUppsAnggota = document.getElementById('viewUppsAnggota');

        const btnDetailEditUpps = document.getElementById('btnDetailEditUpps');
        const btnDetailHapusUpps = document.getElementById('btnDetailHapusUpps');

        const updateUppsForm = document.getElementById('updateUppsForm');
        const updateNamaUpps = document.getElementById('updateNamaUpps');
        const updatePimpinanUpps = document.getElementById('updatePimpinanUpps');
        const updateJenisUpps = document.getElementById('updateJenisUpps');
        const updateAnggotaUpps = document.getElementById('updateAnggotaUpps');
        const updateKodeUpps = document.getElementById('updateKodeUpps');

        const viewUppsAnggotaText = document.getElementById('viewUppsAnggotaText');
        const linkUppsAnggota = document.getElementById('linkUppsAnggota');

        let activeProdiContext = null;

        // When Eye icon is clicked
        const eyeButtons = document.querySelectorAll('.open-upps-detail-btn');
        eyeButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const hasUpps = this.getAttribute('data-has-upps') === '1';
                const prodiId = this.getAttribute('data-prodi-id');
                const prodiNama = this.getAttribute('data-prodi-nama');
                const uppsId = this.getAttribute('data-upps-id');
                const uppsNama = this.getAttribute('data-upps-nama');
                const uppsPimpinan = this.getAttribute('data-upps-pimpinan');
                const uppsJenis = this.getAttribute('data-upps-jenis');
                const uppsKode = this.getAttribute('data-upps-kode');
                const updateUrl = this.getAttribute('data-update-url');
                const destroyUrl = this.getAttribute('data-destroy-url');

                activeProdiContext = {
                    hasUpps, prodiId, prodiNama, uppsId, uppsNama, uppsPimpinan, uppsJenis, uppsKode, updateUrl, destroyUrl
                };

                if (hasUpps) {
                    // Populate & show Filled UPPS Detail Modal (Image 3)
                    if (viewUppsNama) viewUppsNama.value = uppsNama || '-';
                    if (viewUppsPimpinan) viewUppsPimpinan.value = uppsPimpinan || '-';
                    if (viewUppsJenis) viewUppsJenis.value = uppsJenis || '-';
                    if (viewUppsAnggotaText) viewUppsAnggotaText.innerText = 'Kelola Anggota (' + (prodiNama || 'Prodi') + ')';
                    if (linkUppsAnggota) linkUppsAnggota.href = "{{ url('/upps') }}/" + (uppsId || 1) + "/anggota";

                    showModal(uppsFilledModal);
                } else {
                    // Show Empty UPPS Detail Modal (Image 1)
                    showModal(uppsEmptyModal);
                }
            });
        });

        // Trigger "Tambah UPPS" from Empty Modal button (Image 1 -> Image 2)
        if (btnTriggerTambahUpps) {
            btnTriggerTambahUpps.addEventListener('click', function () {
                hideModal(uppsEmptyModal);
                if (activeProdiContext && createAnggotaUppsSelect) {
                    createAnggotaUppsSelect.value = activeProdiContext.prodiId;
                }
                showModal(createUppsModal);
            });
        }

        // Trigger "Edit Data" from Filled Detail Modal (Image 3 -> Image 4)
        if (btnDetailEditUpps) {
            btnDetailEditUpps.addEventListener('click', function () {
                hideModal(uppsFilledModal);
                if (activeProdiContext) {
                    if (updateUppsForm) updateUppsForm.action = activeProdiContext.updateUrl;
                    if (updateNamaUpps) updateNamaUpps.value = activeProdiContext.uppsNama || '';
                    if (updatePimpinanUpps) updatePimpinanUpps.value = activeProdiContext.uppsPimpinan || '';
                    if (updateJenisUpps) updateJenisUpps.value = activeProdiContext.uppsJenis || '';
                    if (updateAnggotaUpps) updateAnggotaUpps.value = activeProdiContext.prodiId || '';
                    if (updateKodeUpps) updateKodeUpps.value = activeProdiContext.uppsKode || '';
                }
                showModal(updateUppsModal);
            });
        }

        // Trigger "Hapus Data" from Filled Detail Modal (Image 3 -> Image 5 Confirm Modal)
        if (btnDetailHapusUpps) {
            btnDetailHapusUpps.addEventListener('click', function () {
                hideModal(uppsFilledModal);
                if (activeProdiContext && window.openConfirmDeleteModal) {
                    window.openConfirmDeleteModal({
                        action: activeProdiContext.destroyUrl,
                        title: 'Hapus Data UPPS?',
                        message: 'Apakah Anda yakin ingin menghapus UPPS ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.'
                    });
                }
            });
        }

        // Close buttons on all modals
        document.querySelectorAll('.close-prodi-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                hideModal(this.closest('[role="dialog"]'));
            });
        });

        // Backdrop click to close
        const allModals = [createProdiModal, editProdiModal, uppsEmptyModal, uppsFilledModal, createUppsModal, updateUppsModal];
        allModals.forEach(modal => {
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) hideModal(modal);
                });
            }
        });

        // Escape key to close
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                allModals.forEach(modal => {
                    if (modal && !modal.classList.contains('pointer-events-none')) {
                        hideModal(modal);
                    }
                });
            }
        });
    });
</script>
@endsection
