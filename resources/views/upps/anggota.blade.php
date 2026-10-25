@extends('layouts.app')

@section('title', 'Anggota UPPS')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Header Title / Sub-title -->
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900 tracking-tight">
                Anggota UPPS - {{ $upps['nama'] ?? 'Detail UPPS' }}
            </h2>
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
                    placeholder="Cari..." 
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

        <!-- Table: Data Anggota UPPS -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-16">No</x-table.th>
                <x-table.th>Nama Anggota</x-table.th>
                <x-table.th>Jabatan</x-table.th>
                <x-table.th class="w-28 text-right">Action</x-table.th>
            </x-slot:header>

            @forelse($anggotaList as $index => $item)
                @php
                    $id = $item['id'] ?? 0;
                    $uppsId = $upps['id'] ?? $id_upps ?? 3;
                    $nama = $item['nama'] ?? $item['nama_anggota'] ?? '-';
                    $jabatan = $item['jabatan'] ?? '-';
                @endphp
                <x-table.row class="anggota-upps-row">
                    <!-- No -->
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $index + 1 }}
                    </x-table.cell>

                    <!-- Nama Anggota -->
                    <x-table.cell>
                        <span class="font-extrabold text-sm text-gray-900 tracking-tight nama-text">
                            {{ $nama }}
                        </span>
                    </x-table.cell>

                    <!-- Jabatan -->
                    <x-table.cell class="text-gray-700 font-medium jabatan-text">
                        {{ $jabatan }}
                    </x-table.cell>

                    <!-- Action (Edit, Delete, Detail) -->
                    <x-table.cell class="w-28">
                        <div class="flex items-center justify-end gap-2.5">
                            <!-- Edit Icon (Yellow) -->
                            <button 
                                type="button" 
                                class="open-edit-anggota-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Anggota UPPS"
                                data-id="{{ $id }}"
                                data-nama="{{ $nama }}"
                                data-jabatan="{{ $jabatan }}"
                                data-action="{{ route('upps.anggota.update', ['id' => $uppsId, 'anggotaId' => $id]) }}"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>

                            <!-- Delete Icon (Red) -->
                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Anggota UPPS"
                                data-confirm-delete
                                data-action="{{ route('upps.anggota.destroy', ['id' => $uppsId, 'anggotaId' => $id]) }}"
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
                            >
                                <x-icon.eye class="w-5 h-5" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="4" message="Belum ada data anggota UPPS" submessage="Silakan klik tombol Tambah Anggota untuk membuat data baru." />
            @endforelse
        </x-table>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Tambahkan Anggota UPPS              -->
<!-- ========================================== -->
<div 
    id="createAnggotaModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createAnggotaModalTitle"
>
    <div class="anggota-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createAnggotaModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan Anggota UPPS
        </h3>

        <form action="{{ route('upps.anggota.store', ['id' => $upps['id'] ?? $id_upps ?? 3]) }}" method="POST" class="space-y-4">
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

            <!-- Field 2: Jabatan -->
            <div>
                <label for="createJabatanInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jabatan
                </label>
                <input 
                    type="text" 
                    name="jabatan" 
                    id="createJabatanInput" 
                    placeholder="Contoh: Ketua Jurusan Teknik Elektro" 
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
<!-- MODAL: Perbarui Anggota UPPS               -->
<!-- ========================================== -->
<div 
    id="editAnggotaModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editAnggotaModalTitle"
>
    <div class="anggota-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
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
                    placeholder="Contoh: Dr. Amin Suharjono, S.T., M.T." 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Jabatan -->
            <div>
                <label for="editJabatanInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jabatan
                </label>
                <input 
                    type="text" 
                    name="jabatan" 
                    id="editJabatanInput" 
                    placeholder="Contoh: Ketua Jurusan Teknik Elektro" 
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
<!-- MODAL: Detail Anggota UPPS                 -->
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

            <!-- Jabatan -->
            <div class="bg-white-form p-3.5 rounded-[12px]">
                <span class="block text-xs font-bold text-gray-500 mb-1">Jabatan</span>
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

                    const match = !keyword || nama.includes(keyword) || jabatan.includes(keyword);
                    row.style.display = match ? '' : 'none';
                });
            });
        }

        // Modal Helpers
        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.anggota-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.anggota-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        // Create Modal
        const createModal = document.getElementById('createAnggotaModal');
        const openCreateBtn = document.getElementById('openCreateAnggotaModalBtn');
        const createNamaInput = document.getElementById('createNamaInput');
        const createJabatanInput = document.getElementById('createJabatanInput');

        if (openCreateBtn) {
            openCreateBtn.addEventListener('click', function () {
                if (createNamaInput) createNamaInput.value = '';
                if (createJabatanInput) createJabatanInput.value = '';
                showModal(createModal);
                setTimeout(() => createNamaInput?.focus(), 100);
            });
        }

        // Edit Modal (Event Delegation)
        const editModal = document.getElementById('editAnggotaModal');
        const editForm = document.getElementById('editAnggotaForm');
        const editNamaInput = document.getElementById('editNamaInput');
        const editJabatanInput = document.getElementById('editJabatanInput');

        document.addEventListener('click', function (e) {
            const editBtn = e.target.closest('.open-edit-anggota-btn');
            if (!editBtn) return;

            e.preventDefault();
            e.stopPropagation();

            const actionUrl = editBtn.getAttribute('data-action') || editBtn.dataset.action;
            const nama = editBtn.getAttribute('data-nama') || editBtn.dataset.nama;
            const jabatan = editBtn.getAttribute('data-jabatan') || editBtn.dataset.jabatan;

            if (editForm && actionUrl) {
                editForm.action = actionUrl;
                editForm.setAttribute('action', actionUrl);
            }

            if (editNamaInput) editNamaInput.value = nama || '';
            if (editJabatanInput) editJabatanInput.value = jabatan || '';

            showModal(editModal);
            setTimeout(() => editNamaInput?.focus(), 100);
        });

        // Detail Modal (Event Delegation)
        const detailModal = document.getElementById('detailAnggotaModal');
        const detailNamaText = document.getElementById('detailNamaText');
        const detailJabatanText = document.getElementById('detailJabatanText');

        document.addEventListener('click', function (e) {
            const detailBtn = e.target.closest('.open-detail-anggota-btn');
            if (!detailBtn) return;

            e.preventDefault();

            const nama = detailBtn.getAttribute('data-nama') || detailBtn.dataset.nama;
            const jabatan = detailBtn.getAttribute('data-jabatan') || detailBtn.dataset.jabatan;

            if (detailNamaText) detailNamaText.textContent = nama || '-';
            if (detailJabatanText) detailJabatanText.textContent = jabatan || '-';

            showModal(detailModal);
        });

        // Close Buttons
        document.querySelectorAll('.close-anggota-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                hideModal(this.closest('[role="dialog"]'));
            });
        });

        // Close on Backdrop Click
        [createModal, editModal, detailModal].forEach(m => {
            m?.addEventListener('click', function (e) {
                if (e.target === m) hideModal(m);
            });
        });

        // Close on Escape Key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideModal(createModal);
                hideModal(editModal);
                hideModal(detailModal);
            }
        });
    });
</script>
@endsection