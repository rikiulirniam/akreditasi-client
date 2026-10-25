@extends('layouts.app')

@section('title', 'Kelas')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Top Action Bar: Search Input & Tambah Kelas Button -->
        <div class="flex items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
                <x-input 
                    type="text" 
                    id="searchKelasInput"
                    placeholder="Cari..." 
                    class="pl-10 text-xs"
                />
            </div>
            @haspermission('kelas.create')
            <div>
                <button 
                    type="button" 
                    id="openCreateKelasModalBtn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Kelas
                </button>
            </div>
            @endhaspermission
        </div>

        <!-- Table: Data Kelas -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-16">No</x-table.th>
                <x-table.th>Nama Kelas</x-table.th>
                <x-table.th class="w-36">Angkatan</x-table.th>
                <x-table.th class="w-48">Jumlah Mahasiswa</x-table.th>
                <x-table.th class="w-28 text-right">Action</x-table.th>
            </x-slot:header>

            @forelse($kelasList as $index => $item)
                @php
                    $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                    $namaKelas = $item['kelas'] ?? $item['nama_kelas'] ?? '-';
                    $angkatan = $item['angkatan']['angkatan'] ?? $item['angkatan'] ?? '-';
                    $jumlahMahasiswa = $item['jumlah_mahasiswa'] ?? 0;
                @endphp
                <x-table.row class="kelas-row">
                    <!-- No -->
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $rowNumber }}
                    </x-table.cell>

                    <!-- Nama Kelas -->
                    <x-table.cell>
                        <a 
                            href="{{ route('kelas.show', $item['id']) }}" 
                            class="font-extrabold text-sm text-primary hover:text-additional tracking-tight font-mono kelas-text transition inline-flex items-center gap-1.5 group"
                            title="Lihat Detail Kelas {{ $namaKelas }}"
                        >
                            <span>{{ $namaKelas }}</span>
                            <svg class="w-3.5 h-3.5 opacity-0 group-hover:opacity-100 transition-opacity text-additional shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    </x-table.cell>

                    <!-- Angkatan -->
                    <x-table.cell class="w-36 text-gray-700 font-medium text-sm kelas-angkatan">
                        {{ $angkatan }}
                    </x-table.cell>

                    <!-- Jumlah Mahasiswa -->
                    <x-table.cell class="w-48 text-gray-700 font-medium text-sm">
                        {{ $jumlahMahasiswa }} Mahasiswa
                    </x-table.cell>

                    <!-- Action (Detail Eye, Yellow Edit, Red Delete) -->
                    <x-table.cell class="w-28">
                        <div class="flex items-center justify-end gap-2.5">
                            <!-- Detail Kelas Icon (Eye) -->
                            <a 
                                href="{{ route('kelas.show', $item['id']) }}" 
                                class="text-primary hover:text-additional transition p-1 cursor-pointer"
                                title="Lihat Detail Kelas"
                            >
                                <x-icon.eye class="w-5 h-5" />
                            </a>

                            @haspermission('kelas.update')
                            <!-- Edit Kelas Icon (Yellow) -->
                            <button 
                                type="button" 
                                class="open-edit-kelas-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Kelas"
                                data-id="{{ $item['id'] }}"
                                data-kelas="{{ $namaKelas }}"
                                data-action="{{ route('kelas.update', $item['id']) }}"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>
                            @endhaspermission

                            @haspermission('kelas.delete')
                            <!-- Delete Kelas Icon (Red) -->
                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Kelas"
                                data-confirm-delete
                                data-action="{{ route('kelas.destroy', $item['id']) }}"
                                data-title="Hapus Kelas?"
                                data-message="Apakah Anda yakin ingin menghapus kelas '{{ $namaKelas }}'? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>
                            @endhaspermission
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="5" message="Belum ada data kelas" submessage="Silakan klik tombol Tambah Kelas untuk membuat kelas baru." />
            @endforelse
        </x-table>

        <!-- Pagination -->
        <x-pagination :paginator="$pagination" />
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Tambahkan Kelas -->
<!-- ========================================== -->
<div 
    id="createKelasModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createKelasModalTitle"
>
    <div class="kelas-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createKelasModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan Kelas
        </h3>

        <form action="{{ route('kelas.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Field: Nama Kelas -->
            <div>
                <label for="createKelasInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama Kelas
                </label>
                <input 
                    type="text" 
                    name="kelas" 
                    id="createKelasInput" 
                    placeholder="Contoh: TI-1A" 
                    maxlength="50" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-kelas-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
<!-- MODAL: Perbarui Kelas -->
<!-- ========================================== -->
<div 
    id="editKelasModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editKelasModalTitle"
>
    <div class="kelas-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editKelasModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Perbarui Kelas
        </h3>

        <form id="editKelasForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field: Nama Kelas -->
            <div>
                <label for="editKelasInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama Kelas
                </label>
                <input 
                    type="text" 
                    name="kelas" 
                    id="editKelasInput" 
                    placeholder="Contoh: TI-1A" 
                    maxlength="50" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Modal Action Buttons (Batal & Perbarui) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-kelas-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
        const searchInput = document.getElementById('searchKelasInput');
        const rows = document.querySelectorAll('.kelas-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const text = (row.querySelector('.kelas-text')?.textContent || '') + ' ' + (row.querySelector('.kelas-angkatan')?.textContent || '');
                    row.style.display = (!keyword || text.toLowerCase().includes(keyword)) ? '' : 'none';
                });
            });
        }

        // Modal Helpers
        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.kelas-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.kelas-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        // Create Modal
        const createModal = document.getElementById('createKelasModal');
        const openCreateBtn = document.getElementById('openCreateKelasModalBtn');
        const createInput = document.getElementById('createKelasInput');

        if (openCreateBtn) {
            openCreateBtn.addEventListener('click', function () {
                if (createInput) createInput.value = '';
                showModal(createModal);
                setTimeout(() => createInput?.focus(), 100);
            });
        }

        // Edit Modal
        const editModal = document.getElementById('editKelasModal');
        const editForm = document.getElementById('editKelasForm');
        const editInput = document.getElementById('editKelasInput');

        document.querySelectorAll('.open-edit-kelas-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const action = this.getAttribute('data-action');
                const kelas = this.getAttribute('data-kelas');

                if (editForm) editForm.action = action;
                if (editInput) editInput.value = kelas || '';

                showModal(editModal);
                setTimeout(() => editInput?.focus(), 100);
            });
        });

        // Close Buttons
        document.querySelectorAll('.close-kelas-modal').forEach(btn => {
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
