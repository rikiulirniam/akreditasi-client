@extends('layouts.app')

@section('title', 'Mata Kuliah')

@section('content')
<div class="space-y-6">
    @if($rawTotalCount === 0 && empty($search) && empty($selectedKurikulum))
        <!-- ======================================================== -->
        <!-- EMPTY STATE: Belum Ada Data Mata Kuliah (Sesuai Gambar 1) -->
        <!-- ======================================================== -->
        <div class="bg-[#F0F2F5] rounded-[24px] p-12 sm:p-20 border border-gray-200/50 flex flex-col items-center justify-center text-center min-h-[460px] shadow-xs">
            <p class="text-sm sm:text-base font-semibold text-gray-800 leading-relaxed max-w-md mx-auto mb-6">
                Kamu belum ada data Mata Kuliah,<br>
                silahkan tekan tombol tambah untuk Tambah Mata Kuliah.
            </p>

            <button 
                type="button" 
                class="open-create-mk-btn inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer"
            >
                <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tambah Mata Kuliah
            </button>
        </div>
    @else
        <!-- ======================================================== -->
        <!-- DATA TABLE STATE: Daftar Mata Kuliah (Sesuai Gambar 2)   -->
        <!-- ======================================================== -->
        <div class="space-y-4">
            <!-- Top Action Bar: Search Input, Filter Dropdown, & Tambah Button -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3 flex-1 max-w-xl">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-sm">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </span>
                        <x-input 
                            type="text" 
                            id="searchMkInput"
                            value="{{ $search }}"
                            placeholder="Cari..." 
                            class="pl-10 text-xs"
                        />
                    </div>

                    <!-- Filter Dropdown: Kurikulum -->
                    <div class="relative w-48">
                        <select 
                            id="filterKurikulumSelect" 
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                        >
                            <option value="">Semua Kurikulum</option>
                            @foreach($kurikulumList as $k)
                                @php
                                    $kid = $k['id'] ?? '';
                                    $knama = $k['nama_kurikulum'] ?? $k['nama'] ?? 'Kurikulum #'.$kid;
                                @endphp
                                <option value="{{ $kid }}" {{ (string) $selectedKurikulum === (string) $kid ? 'selected' : '' }}>
                                    {{ $knama }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>
                </div>

                <!-- Tambah Mata Kuliah Button -->
                <div>
                    <button 
                        type="button" 
                        class="open-create-mk-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Mata Kuliah
                    </button>
                </div>
            </div>

            <!-- Table: Data Mata Kuliah -->
            <x-table>
                <x-slot:header>
                    <x-table.th class="w-16">No</x-table.th>
                    <x-table.th>Nama</x-table.th>
                    <x-table.th class="w-20 text-center">SKS</x-table.th>
                    <x-table.th>Kode Mata Kuliah</x-table.th>
                    <x-table.th>Kurikulum</x-table.th>
                    <x-table.th class="w-28 text-right">Action</x-table.th>
                </x-slot:header>

                @forelse($mataKuliahList as $index => $item)
                    @php
                        $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                        $id = $item['id'] ?? '';
                        $nama = $item['nama_mata_kuliah'] ?? $item['nama'] ?? '-';
                        $kode = $item['kode_mata_kuliah'] ?? $item['kode'] ?? '-';
                        $sks = $item['sks'] ?? '-';
                        $kId = $item['id_kurikulum'] ?? $item['kurikulum_id'] ?? null;
                        $kurikulumNama = $item['kurikulum']['nama_kurikulum'] 
                            ?? $item['kurikulum']['nama'] 
                            ?? ($kId && isset($kurikulumMap[$kId]) ? ($kurikulumMap[$kId]['nama_kurikulum'] ?? $kurikulumMap[$kId]['nama'] ?? 'Kurikulum #'.$kId) : '-');
                    @endphp
                    <x-table.row class="mk-row">
                        <!-- No (Formatted as "1.", "2.", dst seperti pada mockup) -->
                        <x-table.cell class="text-gray-600 font-medium w-16">
                            {{ $rowNumber }}.
                        </x-table.cell>

                        <!-- Nama Mata Kuliah -->
                        <x-table.cell>
                            <span class="font-bold text-sm text-gray-900 mk-nama tracking-tight">
                                {{ $nama }}
                            </span>
                        </x-table.cell>

                        <!-- SKS -->
                        <x-table.cell class="w-20 text-center">
                            <span class="text-sm font-semibold text-gray-800 mk-sks">
                                {{ $sks }}
                            </span>
                        </x-table.cell>

                        <!-- Kode Mata Kuliah -->
                        <x-table.cell>
                            <span class="font-mono text-sm font-semibold text-gray-800 mk-kode">
                                {{ $kode }}
                            </span>
                        </x-table.cell>

                        <!-- Kurikulum -->
                        <x-table.cell>
                            <span class="text-sm font-medium text-gray-800 mk-kurikulum" data-kurikulum-id="{{ $kId }}">
                                {{ $kurikulumNama }}
                            </span>
                        </x-table.cell>

                        <!-- Action: Edit (Yellow), Delete (Red), Detail (Navy Blue) -->
                        <x-table.cell class="w-28">
                            <div class="flex items-center justify-end gap-3">
                                <!-- Edit Icon (Yellow) -->
                                <button 
                                    type="button" 
                                    class="open-edit-mk-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                    title="Edit Mata Kuliah"
                                    data-id="{{ $id }}"
                                    data-action="{{ route('mata-kuliah.update', $id) }}"
                                    data-nama="{{ $nama }}"
                                    data-kode="{{ $kode }}"
                                    data-sks="{{ $sks }}"
                                    data-kurikulum-id="{{ $kId }}"
                                >
                                    <x-icon.edit class="w-5 h-5" />
                                </button>

                                <!-- Delete Icon (Red) -->
                                <button 
                                    type="button" 
                                    class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                    title="Hapus Mata Kuliah"
                                    data-confirm-delete
                                    data-action="{{ route('mata-kuliah.destroy', $id) }}"
                                    data-title="Hapus Data Mata Kuliah?"
                                    data-message="Apakah Anda yakin ingin menghapus data Mata Kuliah {{ $nama }} ({{ $kode }})? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                                >
                                    <x-icon.delete class="w-5 h-5" />
                                </button>

                                <!-- View / Detail Link (Navy Blue / Eye) -->
                                <a 
                                    href="{{ route('mata-kuliah.show', $id) }}" 
                                    class="text-primary hover:text-additional transition p-1 cursor-pointer"
                                    title="Detail Mata Kuliah"
                                >
                                    <x-icon.eye class="w-5 h-5" />
                                </a>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.empty colspan="6" message="Belum ada data mata kuliah" submessage="Silakan sesuaikan kata kunci pencarian atau klik Tambah Mata Kuliah." />
                @endforelse
            </x-table>

            <!-- Template Pagination Component: 10 Data Per Halaman -->
            <x-pagination :paginator="$pagination" />
        </div>
    @endif
</div>

<!-- ======================================================== -->
<!-- MODAL: Tambahkan Mata Kuliah (Sesuai Gambar 3)            -->
<!-- ======================================================== -->
<div 
    id="createMkModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createMkModalTitle"
>
    <div class="mk-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createMkModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan Mata Kuliah
        </h3>

        <form action="{{ route('mata-kuliah.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Field 1: Nama Mata Kuliah -->
            <div>
                <label for="createNamaMkInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Mata Kuliah
                </label>
                <input 
                    type="text" 
                    name="nama_mata_kuliah" 
                    id="createNamaMkInput" 
                    placeholder="Pilih / Masukkan Mata Kuliah" 
                    maxlength="255" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Kode Mata Kuliah -->
            <div>
                <label for="createKodeMkInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kode Mata Kuliah
                </label>
                <input 
                    type="text" 
                    name="kode_mata_kuliah" 
                    id="createKodeMkInput" 
                    placeholder="Pilih / Masukkan Kode Mata Kuliah" 
                    maxlength="50" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 uppercase focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 3: Kurikulum -->
            <div>
                <label for="createKurikulumMkSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kurikulum
                </label>
                <div class="relative">
                    <select 
                        name="id_kurikulum" 
                        id="createKurikulumMkSelect" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Kurikulum</option>
                        @foreach($kurikulumList as $k)
                            @php
                                $kid = $k['id'] ?? '';
                                $knama = $k['nama_kurikulum'] ?? $k['nama'] ?? 'Kurikulum #'.$kid;
                            @endphp
                            <option value="{{ $kid }}">{{ $knama }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field 4: SKS -->
            <div>
                <label for="createSksMkInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    SKS
                </label>
                <input 
                    type="number" 
                    name="sks" 
                    id="createSksMkInput" 
                    placeholder="Masukkan SKS" 
                    min="1" 
                    max="20" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-mk-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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

<!-- ======================================================== -->
<!-- MODAL: Perbarui Mata Kuliah (Sesuai Gambar 4)             -->
<!-- ======================================================== -->
<div 
    id="editMkModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editMkModalTitle"
>
    <div class="mk-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editMkModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Perbarui Mata Kuliah
        </h3>

        <form id="editMkForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field 1: Mata Kuliah -->
            <div>
                <label for="editNamaMkInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Mata Kuliah
                </label>
                <input 
                    type="text" 
                    name="nama_mata_kuliah" 
                    id="editNamaMkInput" 
                    placeholder="Pilih / Masukkan Mata Kuliah" 
                    maxlength="255" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Kode Mata Kuliah -->
            <div>
                <label for="editKodeMkInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kode Mata Kuliah
                </label>
                <input 
                    type="text" 
                    name="kode_mata_kuliah" 
                    id="editKodeMkInput" 
                    placeholder="Pilih / Masukkan Kode Mata Kuliah" 
                    maxlength="50" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 uppercase focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 3: Kurikulum -->
            <div>
                <label for="editKurikulumMkSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kurikulum
                </label>
                <div class="relative">
                    <select 
                        name="id_kurikulum" 
                        id="editKurikulumMkSelect" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih Kurikulum</option>
                        @foreach($kurikulumList as $k)
                            @php
                                $kid = $k['id'] ?? '';
                                $knama = $k['nama_kurikulum'] ?? $k['nama'] ?? 'Kurikulum #'.$kid;
                            @endphp
                            <option value="{{ $kid }}">{{ $knama }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field 4: SKS -->
            <div>
                <label for="editSksMkInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    SKS
                </label>
                <input 
                    type="number" 
                    name="sks" 
                    id="editSksMkInput" 
                    placeholder="Masukkan SKS" 
                    min="1" 
                    max="20" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Modal Action Buttons (Batal & Perbarui) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-mk-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
        // Modal Helper Functions
        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.mk-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.mk-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        const createModal = document.getElementById('createMkModal');
        const editModal = document.getElementById('editMkModal');

        // Open Create Modal
        document.querySelectorAll('.open-create-mk-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const form = createModal?.querySelector('form');
                if (form) form.reset();
                showModal(createModal);
                setTimeout(() => document.getElementById('createNamaMkInput')?.focus(), 100);
            });
        });

        // Open Edit Modal
        const editForm = document.getElementById('editMkForm');
        const editNama = document.getElementById('editNamaMkInput');
        const editKode = document.getElementById('editKodeMkInput');
        const editKurikulum = document.getElementById('editKurikulumMkSelect');
        const editSks = document.getElementById('editSksMkInput');

        function openEditModalFromData({ id, action, nama, kode, sks, kurikulumId }) {
            if (editForm) editForm.action = action;
            if (editNama) editNama.value = nama || '';
            if (editKode) editKode.value = kode || '';
            if (editKurikulum) editKurikulum.value = kurikulumId || '';
            if (editSks) editSks.value = sks || '';

            showModal(editModal);
            setTimeout(() => editNama?.focus(), 100);
        }

        document.querySelectorAll('.open-edit-mk-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                openEditModalFromData({
                    id: this.getAttribute('data-id'),
                    action: this.getAttribute('data-action'),
                    nama: this.getAttribute('data-nama'),
                    kode: this.getAttribute('data-kode'),
                    sks: this.getAttribute('data-sks'),
                    kurikulumId: this.getAttribute('data-kurikulum-id')
                });
            });
        });

        // Close Buttons
        document.querySelectorAll('.close-mk-modal').forEach(btn => {
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

        // Search and Filter Handling with URL query parameters
        const searchInput = document.getElementById('searchMkInput');
        const filterSelect = document.getElementById('filterKurikulumSelect');

        function applyFilter() {
            const params = new URLSearchParams(window.location.search);
            const searchVal = searchInput?.value.trim() || '';
            const filterVal = filterSelect?.value || '';

            if (searchVal) {
                params.set('search', searchVal);
            } else {
                params.delete('search');
            }

            if (filterVal) {
                params.set('kurikulum_id', filterVal);
            } else {
                params.delete('kurikulum_id');
            }

            // Reset page to 1 when search/filter changes
            params.set('page', '1');

            window.location.search = params.toString();
        }

        if (filterSelect) {
            filterSelect.addEventListener('change', applyFilter);
        }

        if (searchInput) {
            let debounceTimer = null;
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(debounceTimer);
                    applyFilter();
                }
            });

            // Live filter on visible table rows for instantaneous response
            const rows = document.querySelectorAll('.mk-row');
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                clearTimeout(debounceTimer);
                
                rows.forEach(row => {
                    const nama = row.querySelector('.mk-nama')?.textContent.toLowerCase() || '';
                    const kode = row.querySelector('.mk-kode')?.textContent.toLowerCase() || '';
                    row.style.display = (!keyword || nama.includes(keyword) || kode.includes(keyword)) ? '' : 'none';
                });

                // Debounce URL update if typing stops
                debounceTimer = setTimeout(() => {
                    applyFilter();
                }, 800);
            });
        }
    });
</script>
@endsection

