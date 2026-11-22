@extends('layouts.app')

@section('title', 'Master UPPS')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Top Action Bar: Search Input & Tambah UPPS Button -->
        <div class="flex items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
                <x-input 
                    type="text" 
                    id="searchUppsInput"
                    placeholder="Cari UPPS..." 
                    class="pl-10 text-xs"
                />
            </div>
            <div>
                <button 
                    type="button" 
                    id="openCreateUppsModalBtn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah UPPS
                </button>
            </div>
        </div>

        <!-- Table: Data Master UPPS (6 Kolom) -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-16">No</x-table.th>
                <x-table.th class="w-32">Kode</x-table.th>
                <x-table.th>Nama UPPS</x-table.th>
                <x-table.th class="w-36">Jenis</x-table.th>
                <x-table.th class="w-72">Pimpinan UPPS</x-table.th>
                <x-table.th class="w-36 text-right">Action</x-table.th>
            </x-slot:header>

            @forelse($uppsList as $index => $item)
                @php
                    $uId = $item['id'] ?? 0;
                    $prodis = $item['prodis'] ?? [];
                @endphp
                <x-table.row class="upps-row">
                    <!-- No -->
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $index + 1 }}
                    </x-table.cell>

                    <!-- Kode UPPS -->
                    <x-table.cell class="w-32">
                        <span class="font-semibold text-xs text-gray-700 font-mono upps-kode">
                            {{ $item['kode_upps'] ?? '-' }}
                        </span>
                    </x-table.cell>

                    <!-- Nama UPPS -->
                    <x-table.cell>
                        <span class="font-extrabold text-sm text-gray-900 upps-nama tracking-tight block">
                            {{ $item['nama_upps'] ?? '-' }}
                        </span>
                    </x-table.cell>

                    <!-- Jenis UPPS -->
                    <x-table.cell class="w-36">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-primary/10 text-primary border border-primary/20 upps-jenis">
                            {{ $item['jenis'] ?? 'Jurusan' }}
                        </span>
                    </x-table.cell>

                    <!-- Pimpinan UPPS -->
                    <x-table.cell class="w-72">
                        <span class="text-sm font-medium text-gray-800 upps-pimpinan block">
                            {{ $item['pimpinan_upps'] ?? '-' }}
                        </span>
                    </x-table.cell>

                    <!-- Aksi (Anggota, Edit, Delete, Detail) -->
                    <x-table.cell class="w-36">
                        <div class="flex items-center justify-end gap-2.5">
                            <!-- Kelola Anggota UPPS (Icon UserGroup/Anggota) -->
                            <a 
                                href="{{ route('upps.anggota', $uId) }}"
                                class="text-secondary hover:text-primary transition p-1 cursor-pointer"
                                title="Kelola Anggota UPPS & Hirarki Jabatan"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6 0 3.375 3.375 0 016 0zm6 2.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                </svg>
                            </a>

                            <!-- Edit Icon (Yellow) -->
                            <button 
                                type="button" 
                                class="open-edit-upps-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Data UPPS"
                                data-id="{{ $uId }}"
                                data-nama="{{ $item['nama_upps'] }}"
                                data-kode="{{ $item['kode_upps'] }}"
                                data-pimpinan="{{ $item['pimpinan_upps'] }}"
                                data-jenis="{{ $item['jenis'] }}"
                                data-prodi-ids='@json($item['prodi_ids'])'
                                data-action="{{ route('upps.update', $uId) }}"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>

                            <!-- Delete Icon (Red) -->
                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus UPPS"
                                data-confirm-delete
                                data-action="{{ route('upps.destroy', $uId) }}"
                                data-title="Hapus Data UPPS?"
                                data-message="Apakah Anda yakin ingin menghapus UPPS '{{ $item['nama_upps'] }}'? Tindakan ini bersifat permanen."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>

                            <!-- Detail / Eye Icon (Navy) -->
                            <button 
                                type="button" 
                                class="open-detail-upps-btn text-primary hover:text-additional transition p-1 cursor-pointer"
                                title="Lihat Detail UPPS & Program Studi"
                                data-id="{{ $uId }}"
                                data-nama="{{ $item['nama_upps'] }}"
                                data-kode="{{ $item['kode_upps'] }}"
                                data-pimpinan="{{ $item['pimpinan_upps'] }}"
                                data-jenis="{{ $item['jenis'] }}"
                                data-prodis='@json($prodis)'
                                data-prodi-ids='@json($item['prodi_ids'])'
                                data-anggota-url="{{ route('upps.anggota', $uId) }}"
                                data-update-url="{{ route('upps.update', $uId) }}"
                            >
                                <x-icon.eye class="w-5 h-5" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="6" message="Belum ada data UPPS" submessage="Silakan klik tombol Tambah UPPS untuk membuat data baru." />
            @endforelse
        </x-table>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Tambah UPPS                         -->
<!-- ========================================== -->
<div 
    id="createUppsModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createUppsModalTitle"
>
    <div class="upps-modal-card relative bg-white rounded-[28px] p-8 max-w-[520px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createUppsModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambah Master UPPS
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
                    placeholder="Contoh: Jurusan Teknik Elektro" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Kode UPPS -->
            <div>
                <label for="createKodeUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kode UPPS <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <input 
                    type="text" 
                    name="kode_upps" 
                    id="createKodeUpps" 
                    placeholder="Contoh: JTE-01" 
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
                        <option value="" disabled selected>Pilih Jenis UPPS</option>
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

            <!-- Field 4: Pimpinan UPPS -->
            <div>
                <label for="createPimpinanUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Pimpinan UPPS (Ketua Jurusan / Dekan)
                </label>
                <input 
                    type="text" 
                    name="pimpinan_upps" 
                    id="createPimpinanUpps" 
                    placeholder="Contoh: Dr. Garup Lambang Goro, S.T., M.T." 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 5: Program Studi yang Dinaungi (Relasi 1 UPPS : Banyak Prodi) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-gray-900">
                        Program Studi yang Dinaungi (1 UPPS menaungi banyak prodi)
                    </label>
                    <span class="text-[11px] text-gray-500 font-medium">Bisa memilih lebih dari 1</span>
                </div>
                <div class="max-h-40 overflow-y-auto p-3 rounded-[10px] bg-white-form space-y-2 border border-gray-100">
                    @forelse($allProdis as $p)
                        <label class="flex items-center gap-2.5 text-xs font-medium text-gray-800 hover:text-black cursor-pointer select-none">
                            <input 
                                type="checkbox" 
                                name="prodi_ids[]" 
                                value="{{ $p['id'] }}" 
                                class="rounded text-primary focus:ring-primary w-4 h-4 cursor-pointer"
                            >
                            <span>{{ $p['nama_prodi'] }} <span class="text-gray-400">({{ $p['jenjang'] ?? 'D3/D4' }})</span></span>
                        </label>
                    @empty
                        <span class="text-xs text-gray-400">Belum ada data program studi tersedia.</span>
                    @endforelse
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-upps-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
<!-- MODAL: Edit UPPS                           -->
<!-- ========================================== -->
<div 
    id="editUppsModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editUppsModalTitle"
>
    <div class="upps-modal-card relative bg-white rounded-[28px] p-8 max-w-[520px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editUppsModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Perbarui Master UPPS
        </h3>

        <form id="editUppsForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field 1: Nama UPPS -->
            <div>
                <label for="editNamaUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama UPPS
                </label>
                <input 
                    type="text" 
                    name="nama_upps" 
                    id="editNamaUpps" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Kode UPPS -->
            <div>
                <label for="editKodeUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kode UPPS
                </label>
                <input 
                    type="text" 
                    name="kode_upps" 
                    id="editKodeUpps" 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 3: Jenis UPPS -->
            <div>
                <label for="editJenisUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jenis UPPS
                </label>
                <div class="relative">
                    <select 
                        name="jenis" 
                        id="editJenisUpps" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
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

            <!-- Field 4: Pimpinan UPPS -->
            <div>
                <label for="editPimpinanUpps" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Pimpinan UPPS (Ketua Jurusan / Dekan)
                </label>
                <input 
                    type="text" 
                    name="pimpinan_upps" 
                    id="editPimpinanUpps" 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 5: Program Studi yang Dinaungi -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-gray-900">
                        Program Studi yang Dinaungi (1 UPPS menaungi banyak prodi)
                    </label>
                    <span class="text-[11px] text-gray-500 font-medium">Bisa memilih lebih dari 1</span>
                </div>
                <div class="max-h-40 overflow-y-auto p-3 rounded-[10px] bg-white-form space-y-2 border border-gray-100">
                    @forelse($allProdis as $p)
                        <label class="flex items-center gap-2.5 text-xs font-medium text-gray-800 hover:text-black cursor-pointer select-none">
                            <input 
                                type="checkbox" 
                                name="prodi_ids[]" 
                                value="{{ $p['id'] }}" 
                                class="edit-prodi-checkbox rounded text-primary focus:ring-primary w-4 h-4 cursor-pointer"
                            >
                            <span>{{ $p['nama_prodi'] }} <span class="text-gray-400">({{ $p['jenjang'] ?? 'D3/D4' }})</span></span>
                        </label>
                    @empty
                        <span class="text-xs text-gray-400">Belum ada data program studi tersedia.</span>
                    @endforelse
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-upps-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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

<!-- ========================================== -->
<!-- MODAL: Detail Master UPPS (Relasi Multi-Prodi) -->
<!-- ========================================== -->
<div 
    id="detailUppsModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="detailUppsModalTitle"
>
    <div class="upps-modal-card relative bg-white rounded-[28px] p-8 max-w-[560px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="detailUppsModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Detail Master UPPS
        </h3>

        <div class="space-y-4">
            <!-- Informasi Pokok UPPS -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white-form p-3.5 rounded-[12px]">
                    <span class="block text-xs font-bold text-gray-500 mb-1">Kode UPPS</span>
                    <span id="detailUppsKodeText" class="text-sm font-extrabold text-gray-900 block font-mono">-</span>
                </div>
                <div class="bg-white-form p-3.5 rounded-[12px]">
                    <span class="block text-xs font-bold text-gray-500 mb-1">Jenis UPPS</span>
                    <span id="detailUppsJenisText" class="text-sm font-extrabold text-gray-900 block">-</span>
                </div>
            </div>

            <div class="bg-white-form p-3.5 rounded-[12px]">
                <span class="block text-xs font-bold text-gray-500 mb-1">Nama UPPS</span>
                <span id="detailUppsNamaText" class="text-sm font-extrabold text-gray-900 block">-</span>
            </div>

            <div class="bg-white-form p-3.5 rounded-[12px]">
                <span class="block text-xs font-bold text-gray-500 mb-1">Pimpinan UPPS</span>
                <span id="detailUppsPimpinanText" class="text-sm font-medium text-gray-800 block">-</span>
            </div>

            <!-- Relasi 1 UPPS Menaungi Banyak Program Studi -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="block text-xs font-bold text-gray-900">Program Studi yang Dinaungi</span>
                    <span id="detailProdiCountBadge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary">
                        0 Prodi
                    </span>
                </div>
                <div id="detailProdiListContainer" class="p-3.5 rounded-[12px] bg-white-form space-y-2 max-h-48 overflow-y-auto border border-gray-100">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>

            <!-- Akses Cepat Kelola Anggota UPPS -->
            <div>
                <a 
                    id="detailLinkAnggotaUpps" 
                    href="#"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-[12px] bg-primary text-white hover:bg-additional transition font-semibold text-sm cursor-pointer group shadow-sm"
                >
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6 0 3.375 3.375 0 016 0zm6 2.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                        <span>Kelola Anggota UPPS & Hirarki Jabatan</span>
                    </div>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>

            <!-- Tombol Tutup -->
            <div class="pt-2">
                <button 
                    type="button" 
                    class="close-upps-modal w-full py-3 px-6 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold text-sm transition cursor-pointer text-center"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Search functionality
        const searchInput = document.getElementById('searchUppsInput');
        const rows = document.querySelectorAll('.upps-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const nama = row.querySelector('.upps-nama')?.textContent.toLowerCase() || '';
                    const kode = row.querySelector('.upps-kode')?.textContent.toLowerCase() || '';
                    const jenis = row.querySelector('.upps-jenis')?.textContent.toLowerCase() || '';
                    const pimpinan = row.querySelector('.upps-pimpinan')?.textContent.toLowerCase() || '';

                    const isMatch = !keyword || 
                        nama.includes(keyword) || 
                        kode.includes(keyword) || 
                        jenis.includes(keyword) || 
                        pimpinan.includes(keyword);

                    row.style.display = isMatch ? '' : 'none';
                });
            });
        }

        // Modal Helpers
        function showModal(modal) {
            if (!modal) return;
            modal.classList.remove('opacity-0', 'pointer-events-none');
            const card = modal.querySelector('.upps-modal-card');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modal) {
            if (!modal) return;
            modal.classList.add('opacity-0', 'pointer-events-none');
            const card = modal.querySelector('.upps-modal-card');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        const createUppsModal = document.getElementById('createUppsModal');
        const editUppsModal = document.getElementById('editUppsModal');
        const detailUppsModal = document.getElementById('detailUppsModal');

        // Open Create Modal
        const openCreateBtn = document.getElementById('openCreateUppsModalBtn');
        if (openCreateBtn) {
            openCreateBtn.addEventListener('click', function () {
                showModal(createUppsModal);
            });
        }

        // Open Edit Modal
        const editUppsForm = document.getElementById('editUppsForm');
        const editNamaUpps = document.getElementById('editNamaUpps');
        const editKodeUpps = document.getElementById('editKodeUpps');
        const editPimpinanUpps = document.getElementById('editPimpinanUpps');
        const editJenisUpps = document.getElementById('editJenisUpps');
        const editCheckboxes = document.querySelectorAll('.edit-prodi-checkbox');

        document.querySelectorAll('.open-edit-upps-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const actionUrl = this.getAttribute('data-action');
                const nama = this.getAttribute('data-nama') || '';
                const kode = this.getAttribute('data-kode') || '';
                const pimpinan = this.getAttribute('data-pimpinan') || '';
                const jenis = this.getAttribute('data-jenis') || 'Jurusan';
                const prodiIdsRaw = this.getAttribute('data-prodi-ids') || '[]';

                let prodiIds = [];
                try {
                    prodiIds = JSON.parse(prodiIdsRaw);
                } catch (e) {
                    prodiIds = [];
                }

                if (editUppsForm) editUppsForm.action = actionUrl;
                if (editNamaUpps) editNamaUpps.value = nama;
                if (editKodeUpps) editKodeUpps.value = kode;
                if (editPimpinanUpps) editPimpinanUpps.value = pimpinan;
                if (editJenisUpps) editJenisUpps.value = jenis;

                // Set prodi checkboxes
                editCheckboxes.forEach(cb => {
                    cb.checked = prodiIds.includes(parseInt(cb.value)) || prodiIds.includes(String(cb.value));
                });

                showModal(editUppsModal);
            });
        });

        // Open Detail Modal
        const detailKodeText = document.getElementById('detailUppsKodeText');
        const detailJenisText = document.getElementById('detailUppsJenisText');
        const detailNamaText = document.getElementById('detailUppsNamaText');
        const detailPimpinanText = document.getElementById('detailUppsPimpinanText');
        const detailProdiCountBadge = document.getElementById('detailProdiCountBadge');
        const detailProdiListContainer = document.getElementById('detailProdiListContainer');
        const detailLinkAnggota = document.getElementById('detailLinkAnggotaUpps');

        document.querySelectorAll('.open-detail-upps-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const nama = this.getAttribute('data-nama') || '-';
                const kode = this.getAttribute('data-kode') || '-';
                const jenis = this.getAttribute('data-jenis') || '-';
                const pimpinan = this.getAttribute('data-pimpinan') || '-';
                const anggotaUrl = this.getAttribute('data-anggota-url') || '#';
                const prodisRaw = this.getAttribute('data-prodis') || '[]';

                let prodis = [];
                try {
                    prodis = JSON.parse(prodisRaw);
                } catch (e) {
                    prodis = [];
                }

                if (detailKodeText) detailKodeText.textContent = kode;
                if (detailJenisText) detailJenisText.textContent = jenis;
                if (detailNamaText) detailNamaText.textContent = nama;
                if (detailPimpinanText) detailPimpinanText.textContent = pimpinan;
                if (detailLinkAnggota) detailLinkAnggota.href = anggotaUrl;

                if (detailProdiCountBadge) {
                    detailProdiCountBadge.textContent = prodis.length + ' Prodi';
                }

                if (detailProdiListContainer) {
                    if (prodis.length === 0) {
                        detailProdiListContainer.innerHTML = '<span class="text-xs text-gray-400 italic">Belum ada Program Studi yang dinaungi oleh UPPS ini.</span>';
                    } else {
                        detailProdiListContainer.innerHTML = prodis.map((p, idx) => `
                            <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center">
                                        ${idx + 1}
                                    </span>
                                    <div>
                                        <span class="text-xs font-bold text-gray-900 block">${p.nama_prodi || '-'}</span>
                                        <span class="text-[10px] text-gray-500 font-mono">${p.kode_prodi || ''} • Jenjang ${p.jenjang || 'D3/D4'}</span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    ${p.akreditasi || 'Terakreditasi'}
                                </span>
                            </div>
                        `).join('');
                    }
                }

                showModal(detailUppsModal);
            });
        });

        // Close buttons
        document.querySelectorAll('.close-upps-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                hideModal(this.closest('[role="dialog"]'));
            });
        });

        // Click outside backdrop to close
        [createUppsModal, editUppsModal, detailUppsModal].forEach(modal => {
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) hideModal(modal);
                });
            }
        });

        // Escape key to close
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                [createUppsModal, editUppsModal, detailUppsModal].forEach(modal => {
                    if (modal && !modal.classList.contains('pointer-events-none')) {
                        hideModal(modal);
                    }
                });
            }
        });
    });
</script>
@endsection
