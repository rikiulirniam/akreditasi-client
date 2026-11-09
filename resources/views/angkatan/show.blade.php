@extends('layouts.app')

@php
    $tahun = $angkatan['angkatan'] ?? $angkatan['tahun'] ?? '-';
@endphp

@section('title', "Daftar Kelas Angkatan {$tahun}")

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Top Action Bar: Search Input & Action Buttons -->
        <div class="flex items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
                <x-input 
                    type="text" 
                    id="searchAngkatanKelasInput"
                    placeholder="Cari..." 
                    class="pl-10 text-xs"
                />
            </div>
            <div class="flex items-center gap-3">
                <a 
                    href="{{ route('angkatan.index') }}" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white-form hover:bg-gray-300 text-gray-800 text-sm font-bold transition cursor-pointer"
                    title="Kembali ke Daftar Angkatan"
                >
                    <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali
                </a>
                <button 
                    type="button" 
                    id="openCreateAngkatanKelasModalBtn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Kelas
                </button>
            </div>
        </div>

        <x-table>
            <x-slot:header>
                <x-table.th class="w-16">No</x-table.th>
                <x-table.th class="w-48">Nama Kelas</x-table.th>
                <x-table.th>Program Studi</x-table.th>
                <x-table.th class="w-56">Tahun Akademik</x-table.th>
                <x-table.th class="w-24 text-right">Action</x-table.th>
            </x-slot:header>

            @forelse($angkatanKelasList as $index => $ak)
                @php
                    $namaKelas = $ak['kelas']['kelas'] ?? $ak['kelas']['nama_kelas'] ?? '-';
                    $namaProdi = $ak['prodi']['nama_prodi'] ?? '-';
                    $namaTa = $ak['tahun_akademik']['tahun'] ?? $ak['tahunAkademik']['tahun'] ?? '-';
                    $semesterTa = $ak['tahun_akademik']['semester'] ?? $ak['tahunAkademik']['semester'] ?? '';
                @endphp
                <x-table.row class="ak-row">
                    <!-- No -->
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $index + 1 }}
                    </x-table.cell>

                    <!-- Nama Kelas -->
                    <x-table.cell class="w-48">
                        <span class="font-extrabold text-sm text-gray-900 tracking-tight font-mono kelas-name">
                            {{ $namaKelas }}
                        </span>
                    </x-table.cell>

                    <!-- Program Studi -->
                    <x-table.cell>
                        <span class="text-sm font-semibold text-gray-800 prodi-name">
                            {{ $namaProdi }}
                        </span>
                    </x-table.cell>

                    <!-- Tahun Akademik -->
                    <x-table.cell class="w-56">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-additional border border-blue-200 ta-name">
                                {{ $namaTa }}
                            </span>
                            @if($semesterTa)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ strtolower($semesterTa) === 'ganjil' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                    {{ $semesterTa }}
                                </span>
                            @endif
                        </div>
                    </x-table.cell>

                    <!-- Action (Edit, Delete) -->
                    <x-table.cell class="w-24">
                        <div class="flex items-center justify-end gap-3">
                            <!-- Edit Icon (Yellow) -->
                            <button 
                                type="button" 
                                class="open-edit-ak-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Penugasan Kelas"
                                data-id="{{ $ak['id'] }}"
                                data-kelas-id="{{ $ak['kelas_id'] ?? ($ak['kelas']['id'] ?? '') }}"
                                data-prodi-id="{{ $ak['prodi_id'] ?? ($ak['prodi']['id'] ?? '') }}"
                                data-ta-id="{{ $ak['tahun_akademik_id'] ?? ($ak['tahun_akademik']['id'] ?? ($ak['tahunAkademik']['id'] ?? '')) }}"
                                data-action="{{ route('angkatan-kelas.update', $ak['id']) }}"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>

                            <!-- Delete Icon (Red) -->
                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Kelas dari Angkatan"
                                data-confirm-delete
                                data-action="{{ route('angkatan-kelas.destroy', $ak['id']) }}"
                                data-title="Hapus Kelas dari Angkatan?"
                                data-message="Apakah Anda yakin ingin menghapus kelas '{{ $namaKelas }}' dari Angkatan {{ $tahun }}? Tindakan ini bersifat permanen."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty 
                    colspan="5" 
                    message="Belum ada kelas terdaftar di Angkatan {{ $tahun }}" 
                    submessage="Silakan klik tombol Tambah Kelas ke Angkatan untuk menambahkan rombel kelas baru." 
                />
            @endforelse
        </x-table>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Tambahkan Kelas ke Angkatan -->
<!-- ========================================== -->
<div 
    id="createAngkatanKelasModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createAngkatanKelasModalTitle"
>
    <div class="ak-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createAngkatanKelasModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambah Kelas ke Angkatan {{ $tahun }}
        </h3>

        <form action="{{ route('angkatan-kelas.store') }}" method="POST" class="space-y-4">
            @csrf

            <input type="hidden" name="angkatan_id" value="{{ $angkatan['id'] }}">

            <!-- Field 1: Program Studi -->
            <div>
                <label for="createProdiSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Program Studi
                </label>
                <div class="relative">
                    <select 
                        name="prodi_id" 
                        id="createProdiSelect" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Program Studi</option>
                        @foreach($prodiList as $p)
                            <option value="{{ $p['id'] }}">
                                {{ $p['nama_prodi'] ?? $p['nama'] ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Field 2: Nama Kelas -->
            <div>
                <label for="createKelasSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kelas
                </label>
                <div class="relative">
                    <select 
                        name="kelas_id" 
                        id="createKelasSelect" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k['id'] }}">
                                {{ $k['kelas'] ?? $k['nama_kelas'] ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Field 3: Tahun Akademik -->
            <div>
                <label for="createTaSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tahun Akademik
                </label>
                <div class="relative">
                    <select 
                        name="tahun_akademik_id" 
                        id="createTaSelect" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Tahun Akademik</option>
                        @foreach($tahunAkademikList as $ta)
                            @php
                                $taLabel = ($ta['tahun'] ?? '-') . (!empty($ta['semester']) ? ' (' . $ta['semester'] . ')' : '');
                            @endphp
                            <option value="{{ $ta['id'] }}">
                                {{ $taLabel }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-ak-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
<!-- MODAL: Perbarui Kelas di Angkatan -->
<!-- ========================================== -->
<div 
    id="editAngkatanKelasModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editAngkatanKelasModalTitle"
>
    <div class="ak-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editAngkatanKelasModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Perbarui Kelas di Angkatan {{ $tahun }}
        </h3>

        <form id="editAngkatanKelasForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" name="angkatan_id" value="{{ $angkatan['id'] }}">

            <!-- Field 1: Program Studi -->
            <div>
                <label for="editProdiSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Program Studi
                </label>
                <div class="relative">
                    <select 
                        name="prodi_id" 
                        id="editProdiSelect" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih Program Studi</option>
                        @foreach($prodiList as $p)
                            <option value="{{ $p['id'] }}">
                                {{ $p['nama_prodi'] ?? $p['nama'] ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Field 2: Nama Kelas -->
            <div>
                <label for="editKelasSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kelas
                </label>
                <div class="relative">
                    <select 
                        name="kelas_id" 
                        id="editKelasSelect" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k['id'] }}">
                                {{ $k['kelas'] ?? $k['nama_kelas'] ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Field 3: Tahun Akademik -->
            <div>
                <label for="editTaSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tahun Akademik
                </label>
                <div class="relative">
                    <select 
                        name="tahun_akademik_id" 
                        id="editTaSelect" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih Tahun Akademik</option>
                        @foreach($tahunAkademikList as $ta)
                            @php
                                $taLabel = ($ta['tahun'] ?? '-') . (!empty($ta['semester']) ? ' (' . $ta['semester'] . ')' : '');
                            @endphp
                            <option value="{{ $ta['id'] }}">
                                {{ $taLabel }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Modal Action Buttons (Batal & Perbarui) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-ak-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
        const searchInput = document.getElementById('searchAngkatanKelasInput');
        const rows = document.querySelectorAll('.ak-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const kelas = row.querySelector('.kelas-name')?.textContent.toLowerCase() || '';
                    const prodi = row.querySelector('.prodi-name')?.textContent.toLowerCase() || '';
                    const ta = row.querySelector('.ta-name')?.textContent.toLowerCase() || '';

                    const match = !keyword || kelas.includes(keyword) || prodi.includes(keyword) || ta.includes(keyword);
                    row.style.display = match ? '' : 'none';
                });
            });
        }

        // Modal Helpers
        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.ak-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.ak-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        // Create Modal
        const createModal = document.getElementById('createAngkatanKelasModal');
        const openCreateBtn = document.getElementById('openCreateAngkatanKelasModalBtn');
        const createProdi = document.getElementById('createProdiSelect');
        const createKelas = document.getElementById('createKelasSelect');
        const createTa = document.getElementById('createTaSelect');

        if (openCreateBtn) {
            openCreateBtn.addEventListener('click', function () {
                if (createProdi) createProdi.selectedIndex = 0;
                if (createKelas) createKelas.selectedIndex = 0;
                if (createTa) createTa.selectedIndex = 0;
                showModal(createModal);
            });
        }

        // Edit Modal
        const editModal = document.getElementById('editAngkatanKelasModal');
        const editForm = document.getElementById('editAngkatanKelasForm');
        const editProdi = document.getElementById('editProdiSelect');
        const editKelas = document.getElementById('editKelasSelect');
        const editTa = document.getElementById('editTaSelect');

        document.querySelectorAll('.open-edit-ak-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const action = this.getAttribute('data-action');
                const prodiId = this.getAttribute('data-prodi-id');
                const kelasId = this.getAttribute('data-kelas-id');
                const taId = this.getAttribute('data-ta-id');

                if (editForm) editForm.action = action;
                if (editProdi && prodiId) editProdi.value = prodiId;
                if (editKelas && kelasId) editKelas.value = kelasId;
                if (editTa && taId) editTa.value = taId;

                showModal(editModal);
            });
        });

        // Close Buttons
        document.querySelectorAll('.close-ak-modal').forEach(btn => {
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
