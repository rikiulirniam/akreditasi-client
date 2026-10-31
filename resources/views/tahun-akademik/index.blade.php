@extends('layouts.app')

@section('title', 'Tahun Akademik')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Top Action Bar: Search Input & Tambah Tahun Akademik Button -->
        <div class="flex items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
                <x-input 
                    type="text" 
                    id="searchTahunAkademikInput"
                    placeholder="Cari..." 
                    class="pl-10 text-xs"
                />
            </div>
            @haspermission('tahun_akademik.create')
            <div>
                <button 
                    type="button" 
                    id="openCreateTahunAkademikModalBtn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Tahun Akademik
                </button>
            </div>
            @endhaspermission
        </div>

        <!-- Table: Data Tahun Akademik -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-16">No</x-table.th>
                <x-table.th>Tahun Akademik</x-table.th>
                <x-table.th class="w-32">Jumlah Kelas</x-table.th>
                <x-table.th class="w-32">Status</x-table.th>
                <x-table.th>Prodi</x-table.th>
                <x-table.th class="w-28 text-right">Action</x-table.th>
            </x-slot:header>

            @forelse($tahunAkademikList as $index => $item)
                @php
                    $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                    $tahun = $item['tahun'] ?? '-';
                    $semester = $item['semester'] ?? 'Ganjil';
                    $isActive = ! empty($item['is_active']);
                    $jumlahKelas = $item['jumlah_kelas'] ?? 12;
                    $prodi = $item['prodi']['nama'] ?? 'Teknologi Rekayasa Komputer';
                @endphp
                <x-table.row class="tahun-akademik-row">
                    <!-- No -->
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $rowNumber }}
                    </x-table.cell>

                    <!-- Tahun Akademik -->
                    <x-table.cell>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-sm text-gray-900 tracking-tight font-mono tahun-text">
                                {{ $tahun }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-primary/10 text-primary">
                                {{ $semester }}
                            </span>
                        </div>
                    </x-table.cell>

                    <!-- Jumlah Kelas -->
                    <x-table.cell class="w-32 text-gray-700 font-medium">
                        {{ $jumlahKelas }} Kelas
                    </x-table.cell>

                    <!-- Status -->
                    <x-table.cell class="w-32">
                        @if($isActive)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 status-text">
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600 border border-gray-200 status-text">
                                Tidak Aktif
                            </span>
                        @endif
                    </x-table.cell>

                    <!-- Prodi -->
                     <x-table.cell class="text-gray-700 font-medium">
                        {{ $prodi }}
                    </x-table.cell>

                    <!-- Action (Yellow Edit, Red Delete) -->
                    <x-table.cell class="w-28">
                        <div class="flex items-center justify-end gap-2.5">
                            @haspermission('tahun_akademik.update')
                            <!-- Edit Icon (Yellow) -->
                            <button 
                                type="button" 
                                class="open-edit-tahun-akademik-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Tahun Akademik"
                                data-id="{{ $item['id'] }}"
                                data-tahun="{{ $tahun }}"
                                data-semester="{{ $semester }}"
                                data-active="{{ $isActive ? '1' : '0' }}"
                                data-action="{{ route('tahun-akademik.update', $item['id']) }}"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>
                            @endhaspermission

                            @haspermission('tahun_akademik.delete')
                            <!-- Delete Icon (Red) -->
                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Tahun Akademik"
                                data-confirm-delete
                                data-action="{{ route('tahun-akademik.destroy', $item['id']) }}"
                                data-title="Hapus Tahun Akademik?"
                                data-message="Apakah Anda yakin ingin menghapus data tahun akademik '{{ $tahun }}'? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>
                            @endhaspermission

                            <!-- Eye / View Icon (Navy) -> ke Halaman 2 (Daftar Kelas) -->
                            <a 
                                href="{{ route('tahun-akademik.kelas', $item['id']) }}" 
                                class="text-primary hover:text-additional transition p-1 cursor-pointer"
                                title="Lihat Daftar Kelas"
                            >
                                <x-icon.eye class="w-5 h-5" />
                            </a>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="5" message="Belum ada data tahun akademik" submessage="Silakan klik tombol Tambah Tahun Akademik untuk membuat data baru." />
            @endforelse
        </x-table>

        <!-- Pagination -->
        <x-pagination :paginator="$pagination" />
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Tambahkan Tahun Akademik -->
<!-- ========================================== -->
<div 
    id="createTahunAkademikModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createTahunAkademikModalTitle"
>
    <div class="tahun-akademik-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createTahunAkademikModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan Tahun Akademik
        </h3>

        <form action="{{ route('tahun-akademik.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Field 1: Tahun Akademik -->
            <div>
                <label for="createTahunInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tahun Akademik
                </label>
                <input 
                    type="text" 
                    name="tahun" 
                    id="createTahunInput" 
                    placeholder="Contoh: 2024/2025" 
                    maxlength="20" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Semester -->
            <div>
                <label for="createSemesterSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Semester
                </label>
                <div class="relative">
                    <select 
                        name="semester" 
                        id="createSemesterSelect" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none cursor-pointer"
                    >
                        <option value="Ganjil" selected>Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Field 3: Status Aktif -->
            <div class="pt-1">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        id="createIsActiveInput" 
                        class="w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300"
                        checked
                    >
                    <span class="text-sm font-semibold text-gray-800">
                        Status Aktif
                    </span>
                </label>
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-tahun-akademik-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
<!-- MODAL: Perbarui Tahun Akademik -->
<!-- ========================================== -->
<div 
    id="editTahunAkademikModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editTahunAkademikModalTitle"
>
    <div class="tahun-akademik-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editTahunAkademikModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Perbarui Tahun Akademik
        </h3>

        <form id="editTahunAkademikForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field 1: Tahun Akademik -->
            <div>
                <label for="editTahunInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tahun Akademik
                </label>
                <input 
                    type="text" 
                    name="tahun" 
                    id="editTahunInput" 
                    placeholder="Contoh: 2024/2025" 
                    maxlength="20" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Semester -->
            <div>
                <label for="editSemesterSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Semester
                </label>
                <div class="relative">
                    <select 
                        name="semester" 
                        id="editSemesterSelect" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none cursor-pointer"
                    >
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Field 3: Status Aktif -->
            <div class="pt-1">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        id="editIsActiveInput" 
                        class="w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300"
                    >
                    <span class="text-sm font-semibold text-gray-800">
                        Status Aktif
                    </span>
                </label>
            </div>

            <!-- Modal Action Buttons (Batal & Perbarui) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-tahun-akademik-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
        const searchInput = document.getElementById('searchTahunAkademikInput');
        const rows = document.querySelectorAll('.tahun-akademik-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const tahun = row.querySelector('.tahun-text')?.textContent.toLowerCase() || '';
                    const semester = row.querySelector('.semester-text')?.textContent.toLowerCase() || '';
                    const status = row.querySelector('.status-text')?.textContent.toLowerCase() || '';

                    const match = !keyword || tahun.includes(keyword) || semester.includes(keyword) || status.includes(keyword);
                    row.style.display = match ? '' : 'none';
                });
            });
        }

        // Modal Helpers
        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.tahun-akademik-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.tahun-akademik-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        // Create Modal
        const createModal = document.getElementById('createTahunAkademikModal');
        const openCreateBtn = document.getElementById('openCreateTahunAkademikModalBtn');
        const createTahunInput = document.getElementById('createTahunInput');
        const createSemesterSelect = document.getElementById('createSemesterSelect');
        const createIsActiveInput = document.getElementById('createIsActiveInput');

        if (openCreateBtn) {
            openCreateBtn.addEventListener('click', function () {
                if (createTahunInput) createTahunInput.value = '';
                if (createSemesterSelect) createSemesterSelect.value = 'Ganjil';
                if (createIsActiveInput) createIsActiveInput.checked = true;
                showModal(createModal);
                setTimeout(() => createTahunInput?.focus(), 100);
            });
        }

        // Edit Modal
        const editModal = document.getElementById('editTahunAkademikModal');
        const editForm = document.getElementById('editTahunAkademikForm');
        const editTahunInput = document.getElementById('editTahunInput');
        const editSemesterSelect = document.getElementById('editSemesterSelect');
        const editIsActiveInput = document.getElementById('editIsActiveInput');

        document.querySelectorAll('.open-edit-tahun-akademik-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const action = this.getAttribute('data-action');
                const tahun = this.getAttribute('data-tahun');
                const semester = this.getAttribute('data-semester');
                const active = this.getAttribute('data-active') === '1';

                if (editForm) editForm.action = action;
                if (editTahunInput) editTahunInput.value = tahun || '';
                if (editSemesterSelect) editSemesterSelect.value = semester || 'Ganjil';
                if (editIsActiveInput) editIsActiveInput.checked = active;

                showModal(editModal);
                setTimeout(() => editTahunInput?.focus(), 100);
            });
        });

        // Close Buttons
        document.querySelectorAll('.close-tahun-akademik-modal').forEach(btn => {
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
