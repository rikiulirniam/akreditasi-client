@extends('layouts.app')

@section('title', 'Jenis Prestasi')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Top Action Bar: Search Input & Tambah Jenis Prestasi Button -->
        <div class="flex items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
                <x-input 
                    type="text" 
                    id="searchJenisPrestasiInput"
                    placeholder="Cari jenis prestasi..." 
                    class="pl-10 text-xs"
                />
            </div>
            <div>
                <button 
                    type="button" 
                    id="openCreateJenisPrestasiModalBtn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Jenis Prestasi
                </button>
            </div>
        </div>

        <!-- Table: Data Jenis Prestasi (Arsitektur Baru) -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-16">No</x-table.th>
                <x-table.th class="w-28">Kode</x-table.th>
                <x-table.th>Nama Jenis Prestasi</x-table.th>
                <x-table.th class="w-36">Kategori</x-table.th>
                <x-table.th class="w-36">Tingkat</x-table.th>
                <x-table.th class="w-28 text-right">Action</x-table.th>
            </x-slot:header>

            @forelse($jenisPrestasiList as $index => $item)
                @php
                    $id = $item['id'] ?? 0;
                    $kode = $item['kode'] ?? '-';
                    $nama = $item['nama'] ?? $item['jenis'] ?? '-';
                    $kategori = $item['kategori'] ?? '-';
                    $tingkat = $item['tingkat'] ?? '-';
                @endphp
                <x-table.row class="jenis-prestasi-row">
                    <!-- No -->
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $index + 1 }}
                    </x-table.cell>

                    <!-- Kode -->
                    <x-table.cell class="font-bold text-gray-700 w-28 jenis-kode">
                        {{ $kode }}
                    </x-table.cell>

                    <!-- Nama -->
                    <x-table.cell>
                        <span class="font-extrabold text-sm text-gray-900 tracking-tight jenis-text">
                            {{ $nama }}
                        </span>
                    </x-table.cell>

                    <!-- Kategori -->
                    <x-table.cell class="w-36">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                            {{ $kategori }}
                        </span>
                    </x-table.cell>

                    <!-- Tingkat -->
                    <x-table.cell class="w-36">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700">
                            {{ $tingkat }}
                        </span>
                    </x-table.cell>

                    <!-- Action (Edit & Delete) -->
                    <x-table.cell class="w-28">
                        <div class="flex items-center justify-end gap-2.5">
                            <!-- Edit Button -->
                            <button 
                                type="button" 
                                class="open-edit-jenis-prestasi-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Jenis Prestasi"
                                data-id="{{ $id }}"
                                data-kode="{{ $kode }}"
                                data-nama="{{ $nama }}"
                                data-kategori="{{ $kategori }}"
                                data-tingkat="{{ $tingkat }}"
                                data-action="{{ route('kemahasiswaan.jenis-prestasi.update', $id) }}"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>

                            <!-- Delete Button -->
                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Jenis Prestasi"
                                data-confirm-delete
                                data-action="{{ route('kemahasiswaan.jenis-prestasi.destroy', $id) }}"
                                data-title="Hapus Jenis Prestasi?"
                                data-message="Apakah Anda yakin ingin menghapus data jenis prestasi '{{ $nama }}'? Tindakan ini bersifat permanen."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="6" message="Belum ada data jenis prestasi" submessage="Silakan klik tombol Tambah Jenis Prestasi untuk membuat data baru." />
            @endforelse
        </x-table>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Tambahkan Jenis Prestasi           -->
<!-- ========================================== -->
<div 
    id="createJenisPrestasiModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createJenisPrestasiModalTitle"
>
    <div class="jenis-prestasi-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createJenisPrestasiModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan Jenis Prestasi
        </h3>

        <form action="{{ route('kemahasiswaan.jenis-prestasi.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Field: Kode -->
            <div>
                <label for="createKodeInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kode Jenis Prestasi
                </label>
                <input 
                    type="text" 
                    name="kode" 
                    id="createKodeInput" 
                    placeholder="Contoh: JP-001" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field: Nama Jenis Prestasi -->
            <div>
                <label for="createNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama Jenis Prestasi
                </label>
                <input 
                    type="text" 
                    name="nama" 
                    id="createNamaInput" 
                    placeholder="Contoh: Olimpiade Pemrograman" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field: Kategori (Akademik / Non-Akademik) -->
            <div>
                <label for="createKategoriInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kategori
                </label>
                <select 
                    name="kategori" 
                    id="createKategoriInput" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none"
                >
                    <option value="" disabled selected>Pilih Kategori</option>
                    <option value="Akademik">Akademik</option>
                    <option value="Non-Akademik">Non-Akademik</option>
                </select>
            </div>

            <!-- Field: Tingkat (Internasional / Nasional / Regional / Lokal) -->
            <div>
                <label for="createTingkatInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tingkat
                </label>
                <select 
                    name="tingkat" 
                    id="createTingkatInput" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none"
                >
                    <option value="" disabled selected>Pilih Tingkat</option>
                    <option value="Internasional">Internasional</option>
                    <option value="Nasional">Nasional</option>
                    <option value="Regional">Regional</option>
                    <option value="Lokal">Lokal</option>
                </select>
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-jenis-prestasi-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
<!-- MODAL: Perbarui Jenis Prestasi             -->
<!-- ========================================== -->
<div 
    id="editJenisPrestasiModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editJenisPrestasiModalTitle"
>
    <div class="jenis-prestasi-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editJenisPrestasiModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Perbarui Jenis Prestasi
        </h3>

        <form id="editJenisPrestasiForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field: Kode -->
            <div>
                <label for="editKodeInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kode Jenis Prestasi
                </label>
                <input 
                    type="text" 
                    name="kode" 
                    id="editKodeInput" 
                    placeholder="Contoh: JP-001" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field: Nama Jenis Prestasi -->
            <div>
                <label for="editNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama Jenis Prestasi
                </label>
                <input 
                    type="text" 
                    name="nama" 
                    id="editNamaInput" 
                    placeholder="Contoh: Olimpiade Pemrograman" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field: Kategori -->
            <div>
                <label for="editKategoriInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kategori
                </label>
                <select 
                    name="kategori" 
                    id="editKategoriInput" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none"
                >
                    <option value="" disabled>Pilih Kategori</option>
                    <option value="Akademik">Akademik</option>
                    <option value="Non-Akademik">Non-Akademik</option>
                </select>
            </div>

            <!-- Field: Tingkat -->
            <div>
                <label for="editTingkatInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tingkat
                </label>
                <select 
                    name="tingkat" 
                    id="editTingkatInput" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none"
                >
                    <option value="" disabled>Pilih Tingkat</option>
                    <option value="Internasional">Internasional</option>
                    <option value="Nasional">Nasional</option>
                    <option value="Regional">Regional</option>
                    <option value="Lokal">Lokal</option>
                </select>
            </div>

            <!-- Modal Action Buttons (Batal & Perbarui) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-jenis-prestasi-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Table Search Filtering
        const searchInput = document.getElementById('searchJenisPrestasiInput');
        const rows = document.querySelectorAll('.jenis-prestasi-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const jenis = row.querySelector('.jenis-text')?.textContent.toLowerCase() || '';
                    const kode = row.querySelector('.jenis-kode')?.textContent.toLowerCase() || '';
                    const match = !keyword || jenis.includes(keyword) || kode.includes(keyword);
                    row.style.display = match ? '' : 'none';
                });
            });
        }

        // Modal Helpers
        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.jenis-prestasi-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.jenis-prestasi-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        // Create Modal
        const createModal = document.getElementById('createJenisPrestasiModal');
        const openCreateBtn = document.getElementById('openCreateJenisPrestasiModalBtn');
        const createKodeInput = document.getElementById('createKodeInput');

        if (openCreateBtn) {
            openCreateBtn.addEventListener('click', function () {
                if (createKodeInput) createKodeInput.value = '';
                const createNamaInput = document.getElementById('createNamaInput');
                if (createNamaInput) createNamaInput.value = '';
                showModal(createModal);
                setTimeout(() => createKodeInput?.focus(), 100);
            });
        }

        // Edit Modal
        const editModal = document.getElementById('editJenisPrestasiModal');
        const editForm = document.getElementById('editJenisPrestasiForm');
        const editKodeInput = document.getElementById('editKodeInput');
        const editNamaInput = document.getElementById('editNamaInput');
        const editKategoriInput = document.getElementById('editKategoriInput');
        const editTingkatInput = document.getElementById('editTingkatInput');

        document.querySelectorAll('.open-edit-jenis-prestasi-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const action = this.getAttribute('data-action');
                const kode = this.getAttribute('data-kode');
                const nama = this.getAttribute('data-nama');
                const kategori = this.getAttribute('data-kategori');
                const tingkat = this.getAttribute('data-tingkat');

                if (editForm) editForm.action = action;
                if (editKodeInput) editKodeInput.value = kode || '';
                if (editNamaInput) editNamaInput.value = nama || '';
                if (editKategoriInput) editKategoriInput.value = kategori || '';
                if (editTingkatInput) editTingkatInput.value = tingkat || '';

                showModal(editModal);
                setTimeout(() => editKodeInput?.focus(), 100);
            });
        });

        // Close Buttons
        document.querySelectorAll('.close-jenis-prestasi-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                hideModal(this.closest('[role="dialog"]'));
            });
        });

        // Close on Backdrop Click
        [createModal, editModal].forEach(m => {
            m?.addEventListener('click', function (e) {
                if (e.target === m) hideModal(m);
            });
        });

        // Close on Escape Key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideModal(createModal);
                hideModal(editModal);
            }
        });
    });
</script>
@endsection