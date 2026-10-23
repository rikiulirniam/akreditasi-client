@extends('layouts.app')

@section('title', 'Data Mahasiswa')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Top Action Bar: Search Input & Tambah Mahasiswa Button -->
        <div class="flex items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
                <x-input 
                    type="text" 
                    id="searchMahasiswaInput"
                    placeholder="Cari..." 
                    class="pl-10 text-xs"
                />
            </div>
            <div>
                <button 
                    type="button" 
                    id="openCreateMahasiswaModalBtn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Mahasiswa
                </button>
            </div>
        </div>

        <!-- Table: Data Mahasiswa -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-16">No</x-table.th>
                <x-table.th>Nama Mahasiswa</x-table.th>
                <x-table.th>NIM</x-table.th>
                <x-table.th>Tahun Masuk</x-table.th>
                <x-table.th>Tanggal Kelulusan</x-table.th>
                <x-table.th class="w-32">Status</x-table.th>
                <x-table.th class="w-28 text-right">Action</x-table.th>
            </x-slot:header>

            @forelse($mahasiswaList as $index => $item)
                @php
                    $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                    $id = $item['id'] ?? '';
                    $nama = $item['nama_mahasiswa'] ?? $item['nama'] ?? '-';
                    $nim = $item['nim_display'] ?? $item['nim'] ?? '-';
                    $tahunMasuk = $item['tahun_masuk'] ?? '-';
                    $jenisPendaftaran = $item['jenis_pendaftaran'] ?? 'Reguler';
                    $rawKelulusan = $item['tanggal_kelulusan'] ?? '';
                    $formattedKelulusan = !empty($rawKelulusan) ? \Carbon\Carbon::parse($rawKelulusan)->format('d-m-Y') : '-';
                    $masaStudi = $item['masa_studi_bulan'] ?? 48;
                @endphp
                <x-table.row class="mahasiswa-row">
                    <!-- No -->
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $rowNumber }}
                    </x-table.cell>

                    <!-- Nama Mahasiswa -->
                    <x-table.cell>
                        <span class="font-extrabold text-sm text-gray-900 mahasiswa-nama tracking-tight font-mono">
                            {{ $nama }}
                        </span>
                    </x-table.cell>

                    <!-- NIM -->
                    <x-table.cell>
                        <span class="font-mono text-sm text-gray-700 mahasiswa-nim">
                            {{ $nim }}
                        </span>
                    </x-table.cell>

                    <!-- Tahun Masuk -->
                    <x-table.cell>
                        <span class="text-sm font-medium text-gray-700 mahasiswa-tahun">
                            {{ $tahunMasuk }}
                        </span>
                    </x-table.cell>

                    <!-- Tanggal Kelulusan -->
                    <x-table.cell>
                        <span class="text-sm font-medium text-gray-700 mahasiswa-kelulusan">
                            {{ $formattedKelulusan }}
                        </span>
                    </x-table.cell>

                    <!-- Status Terkini -->
                    <x-table.cell class="w-32">
                        @php
                            $statusTerkini = $item['status_terkini'] ?? '-';
                            $statusColor = match($statusTerkini) {
                                'Aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'Lulus' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'Cuti' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'Non-Aktif', 'Drop Out' => 'bg-red-50 text-red-700 border-red-200',
                                default => 'bg-gray-100 text-gray-500 border-gray-200',
                            };
                        @endphp
                        @if($statusTerkini !== '-')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusColor }}">
                                {{ $statusTerkini }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </x-table.cell>

                    <!-- Action: Edit (Yellow), Delete (Red), View / Detail (Navy) -->
                    <x-table.cell class="w-28">
                        <div class="flex items-center justify-end gap-3">
                            <!-- Edit Mahasiswa Icon (Yellow) -->
                            <button 
                                type="button" 
                                class="open-edit-mahasiswa-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Mahasiswa"
                                data-id="{{ $id }}"
                                data-action="{{ route('mahasiswa.update', $id) }}"
                                data-nama="{{ $nama }}"
                                data-nim="{{ $nim }}"
                                data-tahun="{{ $tahunMasuk }}"
                                data-jenis="{{ $jenisPendaftaran }}"
                                data-kelulusan="{{ $rawKelulusan }}"
                                data-masa-studi="{{ $masaStudi }}"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>

                            <!-- Delete Mahasiswa Icon (Red) -->
                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Mahasiswa"
                                data-confirm-delete
                                data-action="{{ route('mahasiswa.destroy', $id) }}"
                                data-title="Hapus Data Mahasiswa?"
                                data-message="Apakah Anda yakin ingin menghapus data mahasiswa {{ $nama }} (NIM: {{ $nim }})? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>

                            <!-- View / Detail Mahasiswa — redirect ke halaman detail -->
                            <a
                                href="{{ route('mahasiswa.show', $id) }}"
                                class="text-primary hover:text-additional transition p-1 cursor-pointer inline-flex"
                                title="Detail Mahasiswa"
                            >
                                <x-icon.eye class="w-5 h-5" />
                            </a>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="7" message="Belum ada data mahasiswa" submessage="Silakan klik tombol Tambah Mahasiswa untuk menambahkan data baru." />
            @endforelse
        </x-table>

        <!-- Pagination -->
        <x-pagination :paginator="$pagination" />
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: Tambahkan Mahasiswa -->
<!-- ========================================== -->
<div 
    id="createMahasiswaModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createMahasiswaModalTitle"
>
    <div class="mahasiswa-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createMahasiswaModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan Mahasiswa
        </h3>

        <form action="{{ route('mahasiswa.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Field: Nama Mahasiswa -->
            <div>
                <label for="createNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama Mahasiswa
                </label>
                <input 
                    type="text" 
                    name="nama_mahasiswa" 
                    id="createNamaInput" 
                    placeholder="Isi nama" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field: NIM -->
            <div>
                <label for="createNimInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    NIM
                </label>
                <input 
                    type="text" 
                    name="nim" 
                    id="createNimInput" 
                    placeholder="Masukkan NIM" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field: Tahun Masuk -->
            <div>
                <label for="createTahunInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tahun Masuk
                </label>
                <div class="relative">
                    <select 
                        name="tahun_masuk" 
                        id="createTahunInput" 
                        required 
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Tahun Masuk</option>
                        @foreach($tahunOptions as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field: Jenis Pendaftaran -->
            <div>
                <label for="createJenisInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jenis Pendaftaran
                </label>
                <div class="relative">
                    <select 
                        name="jenis_pendaftaran" 
                        id="createJenisInput" 
                        required 
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Jenis Pendaftaran</option>
                        @foreach($jenisPendaftaranOptions as $jenis)
                            <option value="{{ $jenis }}">{{ $jenis }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field: Tanggal Kelulusan -->
            <div>
                <label for="createKelulusanInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tanggal Kelulusan
                </label>
                <input 
                    type="date" 
                    name="tanggal_kelulusan" 
                    id="createKelulusanInput" 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500 cursor-pointer"
                >
            </div>

            <!-- Field: Masa Studi -->
            <div>
                <label for="createMasaStudiInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Masa Studi (Bulan)
                </label>
                <input 
                    type="number" 
                    name="masa_studi_bulan" 
                    id="createMasaStudiInput" 
                    placeholder="Isi Masa Studi (contoh: 48)" 
                    value="48"
                    min="1"
                    max="120"
                    required
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-mahasiswa-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
<!-- MODAL: Update Mahasiswa -->

<!-- ========================================== -->
<div 
    id="editMahasiswaModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editMahasiswaModalTitle"
>
    <div class="mahasiswa-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editMahasiswaModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Update Mahasiswa
        </h3>

        <form id="editMahasiswaForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field: Nama Mahasiswa -->
            <div>
                <label for="editNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama Mahasiswa
                </label>
                <input 
                    type="text" 
                    name="nama_mahasiswa" 
                    id="editNamaInput" 
                    placeholder="Isi nama" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field: NIM -->
            <div>
                <label for="editNimInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    NIM
                </label>
                <input 
                    type="text" 
                    name="nim" 
                    id="editNimInput" 
                    placeholder="Masukkan NIM" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field: Tahun Masuk -->
            <div>
                <label for="editTahunInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tahun Masuk
                </label>
                <div class="relative">
                    <select 
                        name="tahun_masuk" 
                        id="editTahunInput" 
                        required 
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih Tahun Masuk</option>
                        @foreach($tahunOptions as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field: Jenis Pendaftaran -->
            <div>
                <label for="editJenisInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jenis Pendaftaran
                </label>
                <div class="relative">
                    <select 
                        name="jenis_pendaftaran" 
                        id="editJenisInput" 
                        required 
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih Jenis Pendaftaran</option>
                        @foreach($jenisPendaftaranOptions as $jenis)
                            <option value="{{ $jenis }}">{{ $jenis }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field: Tanggal Kelulusan -->
            <div>
                <label for="editKelulusanInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tanggal Kelulusan
                </label>
                <input 
                    type="date" 
                    name="tanggal_kelulusan" 
                    id="editKelulusanInput" 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500 cursor-pointer"
                >
            </div>

            <!-- Field: Masa Studi -->
            <div>
                <label for="editMasaStudiInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Masa Studi (Bulan)
                </label>
                <input 
                    type="number" 
                    name="masa_studi_bulan" 
                    id="editMasaStudiInput" 
                    placeholder="Isi Masa Studi" 
                    min="1"
                    max="120"
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-mahasiswa-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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
        // Table Search Filtering
        const searchInput = document.getElementById('searchMahasiswaInput');
        const rows = document.querySelectorAll('.mahasiswa-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const nama = row.querySelector('.mahasiswa-nama')?.textContent.toLowerCase() || '';
                    const nim = row.querySelector('.mahasiswa-nim')?.textContent.toLowerCase() || '';
                    const tahun = row.querySelector('.mahasiswa-tahun')?.textContent.toLowerCase() || '';
                    row.style.display = (!keyword || nama.includes(keyword) || nim.includes(keyword) || tahun.includes(keyword)) ? '' : 'none';
                });
            });
        }

        // Modal Helpers
        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.mahasiswa-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.mahasiswa-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        // Create Modal
        const createModal = document.getElementById('createMahasiswaModal');
        const openCreateBtn = document.getElementById('openCreateMahasiswaModalBtn');
        const createNamaInput = document.getElementById('createNamaInput');

        if (openCreateBtn) {
            openCreateBtn.addEventListener('click', function () {
                const form = createModal?.querySelector('form');
                if (form) form.reset();
                const defaultMasaStudi = document.getElementById('createMasaStudiInput');
                if (defaultMasaStudi) defaultMasaStudi.value = '48';

                showModal(createModal);
                setTimeout(() => createNamaInput?.focus(), 100);
            });
        }

        // Edit Modal
        const editModal = document.getElementById('editMahasiswaModal');
        const editForm = document.getElementById('editMahasiswaForm');
        const editNama = document.getElementById('editNamaInput');
        const editNim = document.getElementById('editNimInput');
        const editTahun = document.getElementById('editTahunInput');
        const editJenis = document.getElementById('editJenisInput');
        const editKelulusan = document.getElementById('editKelulusanInput');
        const editMasaStudi = document.getElementById('editMasaStudiInput');

        function populateAndShowEdit(data) {
            if (editForm) editForm.action = data.action || '';
            if (editNama) editNama.value = data.nama || '';
            if (editNim) editNim.value = data.nim || '';
            if (editTahun) editTahun.value = data.tahun || '';
            if (editJenis) editJenis.value = data.jenis || '';
            if (editKelulusan) editKelulusan.value = data.kelulusan || '';
            if (editMasaStudi) editMasaStudi.value = data.masaStudi || '48';
            showModal(editModal);
            setTimeout(() => editNama?.focus(), 100);
        }

        document.querySelectorAll('.open-edit-mahasiswa-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                populateAndShowEdit({
                    action: this.getAttribute('data-action'),
                    nama: this.getAttribute('data-nama'),
                    nim: this.getAttribute('data-nim'),
                    tahun: this.getAttribute('data-tahun'),
                    jenis: this.getAttribute('data-jenis'),
                    kelulusan: this.getAttribute('data-kelulusan'),
                    masaStudi: this.getAttribute('data-masa-studi')
                });
            });
        });

        // Close Buttons on all modals
        document.querySelectorAll('.close-mahasiswa-modal').forEach(btn => {
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
