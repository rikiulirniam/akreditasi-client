@extends('layouts.app')

@section('title', 'Anggota UPPS - ' . ($upps['nama_upps'] ?? ($upps['nama'] ?? 'Detail UPPS')))

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Top Navigation & Header Title -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <a 
                    href="{{ route('upps.index') }}" 
                    class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-white border border-gray-200 text-gray-600 hover:text-primary hover:border-primary hover:shadow-sm transition"
                    title="Kembali ke Master UPPS"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">
                        Anggota UPPS — {{ $upps['nama_upps'] ?? ($upps['nama'] ?? 'Detail UPPS') }}
                    </h2>
                    <p class="text-xs text-gray-500 font-medium">
                        Struktur Organisasi & Hirarki Kepengurusan Unit Pengelola Program Studi
                    </p>
                </div>
            </div>
        </div>

        <!-- Info Card: Ringkasan UPPS & Panduan Hirarki Jabatan -->
        <div class="bg-gradient-to-r from-primary/5 via-primary/10 to-transparent p-5 rounded-3xl border border-primary/20 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-primary text-white font-mono">
                            {{ $upps['kode_upps'] ?? 'UPPS-'.$id }}
                        </span>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            {{ $upps['jenis'] ?? 'Jurusan' }}
                        </span>
                    </div>
                    <h3 class="text-lg font-extrabold text-gray-900 tracking-tight">
                        {{ $upps['nama_upps'] ?? ($upps['nama'] ?? 'Unit Pengelola') }}
                    </h3>
                    <p class="text-xs text-gray-700 font-medium">
                        Pimpinan Utama: <span class="font-bold text-gray-900">{{ $upps['pimpinan_upps'] ?? ($upps['pimpinan'] ?? '-') }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-xs font-bold text-gray-800 border border-gray-200 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-primary"></span>
                        Total {{ count($anggotaList) }} Anggota Terdaftar
                    </span>
                </div>
            </div>

            <!-- Baris Indikator Tingkatan Hirarki Jabatan -->
            <div class="pt-2 border-t border-primary/10 flex flex-wrap items-center gap-2 text-xs">
                <span class="text-gray-500 font-semibold text-[11px]">Hirarki Jabatan:</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Level 1: Pimpinan UPPS
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    Level 2: Sekretaris UPPS
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Level 3: Koordinator / Kaprodi
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    Level 4: Kepala Lab / Bengkel
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200">
                    Level 5: Staf / Anggota
                </span>
            </div>
        </div>

        <!-- Top Action Bar: Search Input & Tambah Anggota Button -->
        <div class="flex items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
                <x-input 
                    type="text" 
                    id="searchAnggotaUppsInput"
                    placeholder="Cari nama atau jabatan anggota..." 
                    class="pl-10 text-xs"
                />
            </div>
            <div>
                <button 
                    type="button" 
                    id="openCreateAnggotaModalBtn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Anggota
                </button>
            </div>
        </div>

        <!-- Table: Data Anggota UPPS (Berhirarki) -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-16">No</x-table.th>
                <x-table.th class="w-64">Nama Anggota</x-table.th>
                <x-table.th class="w-48">Hirarki Jabatan</x-table.th>
                <x-table.th>Jabatan Spesifik</x-table.th>
                <x-table.th class="w-28 text-right">Action</x-table.th>
            </x-slot:header>

            @forelse($anggotaList as $index => $item)
                @php
                    $aId = $item['id'] ?? 0;
                    $uppsId = $upps['id'] ?? $id;
                    $nama = $item['nama'] ?? $item['nama_anggota'] ?? '-';
                    $jabatan = $item['jabatan'] ?? '-';
                    $level = $item['level_jabatan'] ?? 5;
                    $levelLabel = $item['level_label'] ?? 'Staf / Anggota';
                    $badgeClass = $item['badge_class'] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                @endphp
                <x-table.row class="anggota-upps-row">
                    <!-- No -->
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $index + 1 }}
                    </x-table.cell>

                    <!-- Nama Anggota -->
                    <x-table.cell class="w-64">
                        <span class="font-extrabold text-sm text-gray-900 tracking-tight nama-text block">
                            {{ $nama }}
                        </span>
                    </x-table.cell>

                    <!-- Hirarki Jabatan (Badge Tingkatan) -->
                    <x-table.cell class="w-48">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }} hirarki-badge">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ $levelLabel }}
                        </span>
                    </x-table.cell>

                    <!-- Jabatan Spesifik -->
                    <x-table.cell>
                        <span class="text-sm font-semibold text-gray-800 jabatan-text block">
                            {{ $jabatan }}
                        </span>
                    </x-table.cell>

                    <!-- Action (Edit, Delete, Detail) -->
                    <x-table.cell class="w-28">
                        <div class="flex items-center justify-end gap-2.5">
                            <!-- Edit Icon (Yellow) -->
                            <button 
                                type="button" 
                                class="open-edit-anggota-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Anggota UPPS"
                                data-id="{{ $aId }}"
                                data-nama="{{ $nama }}"
                                data-jabatan="{{ $jabatan }}"
                                data-level="{{ $level }}"
                                data-action="{{ route('upps.anggota.update', ['id' => $uppsId, 'anggotaId' => $aId]) }}"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>

                            <!-- Delete Icon (Red) -->
                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Anggota UPPS"
                                data-confirm-delete
                                data-action="{{ route('upps.anggota.destroy', ['id' => $uppsId, 'anggotaId' => $aId]) }}"
                                data-title="Hapus Anggota UPPS?"
                                data-message="Apakah Anda yakin ingin menghapus data anggota '{{ $nama }}'? Tindakan ini bersifat permanen."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>

                            <!-- Eye / View Icon (Navy) -> Detail Modal -->
                            <button 
                                type="button" 
                                class="open-detail-anggota-btn text-primary hover:text-additional transition p-1 cursor-pointer"
                                title="Lihat Detail Anggota"
                                data-nama="{{ $nama }}"
                                data-jabatan="{{ $jabatan }}"
                                data-level-label="{{ $levelLabel }}"
                                data-level="{{ $level }}"
                            >
                                <x-icon.eye class="w-5 h-5" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="5" message="Belum ada data anggota UPPS" submessage="Silakan klik tombol Tambah Anggota untuk membuat data struktur organisasi baru." />
            @endforelse
        </x-table>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Tambahkan Anggota UPPS (Berhirarki) -->
<!-- ========================================== -->
<div 
    id="createAnggotaModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createAnggotaModalTitle"
>
    <div class="anggota-modal-card relative bg-white rounded-[28px] p-8 max-w-[480px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createAnggotaModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan Anggota UPPS
        </h3>

        <form action="{{ route('upps.anggota.store', ['id' => $upps['id'] ?? $id]) }}" method="POST" class="space-y-4">
            @csrf

            <!-- Field 1: Nama Anggota -->
            <div>
                <label for="createNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama Anggota
                </label>
                <input 
                    type="text" 
                    name="nama" 
                    id="createNamaInput" 
                    placeholder="Contoh: Dr. Amin Suharjono, S.T., M.T." 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Tingkatan Hirarki Jabatan -->
            <div>
                <label for="createLevelJabatan" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tingkatan Hirarki Jabatan
                </label>
                <div class="relative">
                    <select 
                        name="level_jabatan" 
                        id="createLevelJabatan" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="1">Level 1: Pimpinan UPPS (Ketua Jurusan / Dekan / Direktur)</option>
                        <option value="2">Level 2: Sekretaris UPPS (Sekretaris Jurusan / Wakil Dekan)</option>
                        <option value="3" selected>Level 3: Koordinator Unit / Kaprodi</option>
                        <option value="4">Level 4: Kepala Laboratorium / Bengkel / Studio</option>
                        <option value="5">Level 5: Staf Administrasi / Anggota Pengelola</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-800">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Field 3: Jabatan Spesifik -->
            <div>
                <label for="createJabatanInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jabatan Spesifik
                </label>
                <input 
                    type="text" 
                    name="jabatan" 
                    id="createJabatanInput" 
                    placeholder="Contoh: Koordinator Program Studi D3 Teknik Listrik" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Modal Action Buttons -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-anggota-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
<!-- MODAL: Perbarui Anggota UPPS (Berhirarki)  -->
<!-- ========================================== -->
<div 
    id="editAnggotaModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editAnggotaModalTitle"
>
    <div class="anggota-modal-card relative bg-white rounded-[28px] p-8 max-w-[480px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editAnggotaModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Perbarui Anggota UPPS
        </h3>

        <form id="editAnggotaForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field 1: Nama Anggota -->
            <div>
                <label for="editNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama Anggota
                </label>
                <input 
                    type="text" 
                    name="nama" 
                    id="editNamaInput" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Tingkatan Hirarki Jabatan -->
            <div>
                <label for="editLevelJabatan" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tingkatan Hirarki Jabatan
                </label>
                <div class="relative">
                    <select 
                        name="level_jabatan" 
                        id="editLevelJabatan" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="1">Level 1: Pimpinan UPPS (Ketua Jurusan / Dekan / Direktur)</option>
                        <option value="2">Level 2: Sekretaris UPPS (Sekretaris Jurusan / Wakil Dekan)</option>
                        <option value="3">Level 3: Koordinator Unit / Kaprodi</option>
                        <option value="4">Level 4: Kepala Laboratorium / Bengkel / Studio</option>
                        <option value="5">Level 5: Staf Administrasi / Anggota Pengelola</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-800">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Field 3: Jabatan Spesifik -->
            <div>
                <label for="editJabatanInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jabatan Spesifik
                </label>
                <input 
                    type="text" 
                    name="jabatan" 
                    id="editJabatanInput" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Modal Action Buttons -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-anggota-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
<!-- MODAL: Detail Anggota UPPS (Berhirarki)    -->
<!-- ========================================== -->
<div 
    id="detailAnggotaModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="detailAnggotaModalTitle"
>
    <div class="anggota-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="detailAnggotaModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Detail Anggota UPPS
        </h3>

        <div class="space-y-4">
            <!-- Nama Anggota -->
            <div class="bg-white-form p-3.5 rounded-[12px]">
                <span class="block text-xs font-bold text-gray-500 mb-1">Nama Anggota</span>
                <span id="detailNamaText" class="text-sm font-extrabold text-gray-900 block">-</span>
            </div>

            <!-- Hirarki Jabatan -->
            <div class="bg-white-form p-3.5 rounded-[12px]">
                <span class="block text-xs font-bold text-gray-500 mb-1">Tingkatan Hirarki</span>
                <span id="detailLevelBadge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary">
                    -
                </span>
            </div>

            <!-- Jabatan Spesifik -->
            <div class="bg-white-form p-3.5 rounded-[12px]">
                <span class="block text-xs font-bold text-gray-500 mb-1">Jabatan Spesifik</span>
                <span id="detailJabatanText" class="text-sm font-medium text-gray-800 block">-</span>
            </div>

            <!-- Close Button -->
            <div class="pt-4">
                <button 
                    type="button" 
                    class="close-anggota-modal w-full py-3 px-6 rounded-xl bg-primary hover:bg-additional text-white font-bold text-sm transition cursor-pointer text-center"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Table Search Filtering
        const searchInput = document.getElementById('searchAnggotaUppsInput');
        const rows = document.querySelectorAll('.anggota-upps-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const nama = row.querySelector('.nama-text')?.textContent.toLowerCase() || '';
                    const jabatan = row.querySelector('.jabatan-text')?.textContent.toLowerCase() || '';
                    const hirarki = row.querySelector('.hirarki-badge')?.textContent.toLowerCase() || '';

                    const match = !keyword || nama.includes(keyword) || jabatan.includes(keyword) || hirarki.includes(keyword);
                    row.style.display = match ? '' : 'none';
                });
            });
        }

        // Modal Helpers
        function showModal(modal) {
            if (!modal) return;
            modal.classList.remove('opacity-0', 'pointer-events-none');
            const card = modal.querySelector('.anggota-modal-card');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modal) {
            if (!modal) return;
            modal.classList.add('opacity-0', 'pointer-events-none');
            const card = modal.querySelector('.anggota-modal-card');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        const createModal = document.getElementById('createAnggotaModal');
        const editModal = document.getElementById('editAnggotaModal');
        const detailModal = document.getElementById('detailAnggotaModal');

        // Open Create Modal
        const openCreateBtn = document.getElementById('openCreateAnggotaModalBtn');
        const createNamaInput = document.getElementById('createNamaInput');
        const createJabatanInput = document.getElementById('createJabatanInput');

        if (openCreateBtn) {
            openCreateBtn.addEventListener('click', function () {
                if (createNamaInput) createNamaInput.value = '';
                if (createJabatanInput) createJabatanInput.value = '';
                showModal(createModal);
            });
        }

        // Open Edit Modal
        const editForm = document.getElementById('editAnggotaForm');
        const editNamaInput = document.getElementById('editNamaInput');
        const editJabatanInput = document.getElementById('editJabatanInput');
        const editLevelSelect = document.getElementById('editLevelJabatan');

        document.querySelectorAll('.open-edit-anggota-btn').forEach(editBtn => {
            editBtn.addEventListener('click', function () {
                const actionUrl = this.getAttribute('data-action');
                const nama = this.getAttribute('data-nama') || '';
                const jabatan = this.getAttribute('data-jabatan') || '';
                const level = this.getAttribute('data-level') || '3';

                if (editForm) editForm.action = actionUrl;
                if (editNamaInput) editNamaInput.value = nama;
                if (editJabatanInput) editJabatanInput.value = jabatan;
                if (editLevelSelect) editLevelSelect.value = String(level);

                showModal(editModal);
            });
        });

        // Open Detail Modal
        const detailNamaText = document.getElementById('detailNamaText');
        const detailJabatanText = document.getElementById('detailJabatanText');
        const detailLevelBadge = document.getElementById('detailLevelBadge');

        document.querySelectorAll('.open-detail-anggota-btn').forEach(detailBtn => {
            detailBtn.addEventListener('click', function () {
                const nama = this.getAttribute('data-nama') || '-';
                const jabatan = this.getAttribute('data-jabatan') || '-';
                const levelLabel = this.getAttribute('data-level-label') || '-';

                if (detailNamaText) detailNamaText.textContent = nama;
                if (detailJabatanText) detailJabatanText.textContent = jabatan;
                if (detailLevelBadge) detailLevelBadge.textContent = levelLabel;

                showModal(detailModal);
            });
        });

        // Close Modal Handlers
        document.querySelectorAll('.close-anggota-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                hideModal(this.closest('[role="dialog"]'));
            });
        });

        // Click outside backdrop
        [createModal, editModal, detailModal].forEach(modal => {
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) hideModal(modal);
                });
            }
        });

        // Escape key to close
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                [createModal, editModal, detailModal].forEach(modal => {
                    if (modal && !modal.classList.contains('pointer-events-none')) {
                        hideModal(modal);
                    }
                });
            }
        });
    });
</script>
@endsection
