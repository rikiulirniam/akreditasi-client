@extends('layouts.app')

@section('title', 'Data Mahasiswa')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <!-- Top Action Bar: Search Input, Filters, & Tambah Mahasiswa Button -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[200px] max-w-xs">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                    <x-input 
                        type="text" 
                        id="searchMahasiswaInput"
                        placeholder="Cari nama, NIM, tahun..." 
                        value="{{ $search ?? '' }}"
                        class="pl-10 text-xs"
                    />
                </div>

                <!-- Filter: Status -->
                <div class="relative w-36 sm:w-40">
                    <select 
                        id="filterStatusSelect"
                        class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                    >
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $st)
                            <option value="{{ $st }}" {{ ($selectedStatus ?? '') === $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                        <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>

                <!-- Filter: Tahun Masuk -->
                <div class="relative w-40 sm:w-44">
                    <select 
                        id="filterTahunSelect"
                        class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                    >
                        <option value="">Semua Tahun Masuk</option>
                        @foreach($tahunOptions as $thn)
                            <option value="{{ $thn }}" {{ (string)($selectedTahun ?? '') === (string)$thn ? 'selected' : '' }}>
                                {{ $thn }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                        <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>

                <!-- Filter: Jalur Masuk (Jenis Pendaftaran) -->
                @if(!empty($jenisPendaftaranOptions))
                <div class="relative w-36 sm:w-40">
                    <select 
                        id="filterJenisSelect"
                        class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                    >
                        <option value="">Semua Jalur</option>
                        @foreach($jenisPendaftaranOptions as $jp)
                            <option value="{{ $jp }}" {{ ($selectedJenis ?? '') === $jp ? 'selected' : '' }}>
                                {{ $jp }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                        <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
                @endif

                <!-- Reset Filter Button -->
                @php
                    $hasActiveFilter = !empty($search) || !empty($selectedStatus) || !empty($selectedTahun) || !empty($selectedJenis);
                @endphp
                <button 
                    type="button" 
                    id="resetFilterBtn"
                    class="{{ $hasActiveFilter ? 'inline-flex' : 'hidden' }} items-center gap-1.5 px-3 py-2.5 rounded-[8px] bg-white-form hover:bg-gray-200 text-gray-600 hover:text-gray-900 text-xs font-semibold border border-white-bone transition cursor-pointer"
                    title="Reset Semua Filter"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset
                </button>
            </div>

            <!-- Tambah Mahasiswa Button -->
            <div class="shrink-0">
                <button 
                    type="button" 
                    id="openCreateMahasiswaModalBtn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer whitespace-nowrap"
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
                    $statusTerkini = $item['status_terkini'] ?? '-';
                @endphp
                <x-table.row 
                    class="mahasiswa-row"
                    data-nama="{{ strtolower($nama) }}"
                    data-nim="{{ strtolower($nim) }}"
                    data-tahun="{{ $tahunMasuk }}"
                    data-status="{{ strtolower($statusTerkini) }}"
                    data-jenis="{{ strtolower($jenisPendaftaran) }}"
                >
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
            <tr id="noFilterMatchRow" class="hidden">
                <td colspan="7" class="text-center py-10 text-gray-400 text-xs font-medium">
                    Tidak ada data mahasiswa yang sesuai dengan filter yang dipilih.
                </td>
            </tr>
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
        // Table Search & Filter Handling
        const searchInput = document.getElementById('searchMahasiswaInput');
        const statusFilter = document.getElementById('filterStatusSelect');
        const tahunFilter = document.getElementById('filterTahunSelect');
        const jenisFilter = document.getElementById('filterJenisSelect');
        const resetBtn = document.getElementById('resetFilterBtn');
        const noFilterMatchRow = document.getElementById('noFilterMatchRow');
        const rows = document.querySelectorAll('.mahasiswa-row');

        let debounceTimer = null;

        function applyClientFilter() {
            const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const selectedStatus = statusFilter ? statusFilter.value.toLowerCase().trim() : '';
            const selectedTahun = tahunFilter ? tahunFilter.value.toString().trim() : '';
            const selectedJenis = jenisFilter ? jenisFilter.value.toLowerCase().trim() : '';

            let visibleCount = 0;

            rows.forEach(row => {
                const rowNama = row.getAttribute('data-nama') || '';
                const rowNim = row.getAttribute('data-nim') || '';
                const rowTahun = (row.getAttribute('data-tahun') || '').toString().trim();
                const rowStatus = row.getAttribute('data-status') || '';
                const rowJenis = row.getAttribute('data-jenis') || '';

                const matchesKeyword = !keyword || (rowNama.includes(keyword) || rowNim.includes(keyword) || rowTahun.includes(keyword));
                const matchesStatus = !selectedStatus || rowStatus === selectedStatus;
                const matchesTahun = !selectedTahun || rowTahun === selectedTahun;
                const matchesJenis = !selectedJenis || rowJenis === selectedJenis;

                if (matchesKeyword && matchesStatus && matchesTahun && matchesJenis) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noFilterMatchRow) {
                noFilterMatchRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
            }

            const hasActiveFilter = !!(keyword || selectedStatus || selectedTahun || selectedJenis);
            if (resetBtn) {
                if (hasActiveFilter) {
                    resetBtn.classList.remove('hidden');
                    resetBtn.classList.add('inline-flex');
                } else {
                    resetBtn.classList.remove('inline-flex');
                    resetBtn.classList.add('hidden');
                }
            }
        }

        function syncUrlParams() {
            const params = new URLSearchParams(window.location.search);
            const searchVal = searchInput ? searchInput.value.trim() : '';
            const statusVal = statusFilter ? statusFilter.value : '';
            const tahunVal = tahunFilter ? tahunFilter.value : '';
            const jenisVal = jenisFilter ? jenisFilter.value : '';

            if (searchVal) {
                params.set('search', searchVal);
            } else {
                params.delete('search');
            }

            if (statusVal) {
                params.set('status', statusVal);
            } else {
                params.delete('status');
            }

            if (tahunVal) {
                params.set('tahun', tahunVal);
            } else {
                params.delete('tahun');
            }

            if (jenisVal) {
                params.set('jenis', jenisVal);
            } else {
                params.delete('jenis');
            }

            params.set('page', '1');

            window.location.search = params.toString();
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                applyClientFilter();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    syncUrlParams();
                }, 600);
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(debounceTimer);
                    syncUrlParams();
                }
            });
        }

        [statusFilter, tahunFilter, jenisFilter].forEach(selectEl => {
            if (selectEl) {
                selectEl.addEventListener('change', function () {
                    applyClientFilter();
                    syncUrlParams();
                });
            }
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (window.location.search) {
                    window.location.href = "{{ route('mahasiswa.index') }}";
                } else {
                    if (searchInput) searchInput.value = '';
                    if (statusFilter) statusFilter.value = '';
                    if (tahunFilter) tahunFilter.value = '';
                    if (jenisFilter) jenisFilter.value = '';
                    applyClientFilter();
                }
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
