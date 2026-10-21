@extends('layouts.app')

@section('title', 'Dosen')

@php
    $hasActiveFilters = !empty($search) || !empty($selectedStatus);
@endphp

@if(!empty($dosenList) && count($dosenList) > 0)
    @section('subtitle', ($pagination['total'] ?? count($dosenList)) . ' Data ditemukan')
@endif

@section('content')
<div class="h-full flex flex-col">
    @if((empty($dosenList) || count($dosenList) === 0) && !$hasActiveFilters)
        <!-- EMPTY STATE -->
        <div class="flex-1 min-h-[70vh] flex flex-col items-center justify-center -mt-6 sm:-mt-10 px-4 text-center select-none">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-black tracking-tight mb-2">
                Belum Ada Data Dosen
            </h2>
            <p class="text-sm sm:text-base text-gray-700 font-medium mb-8">
                Tekan tombol di bawah untuk menambah data dosen
            </p>

            <div class="w-full max-w-[340px] sm:max-w-[380px] space-y-3.5">
                <!-- 1. Download Template Excel (Yellow Outline) -->
                <a
                    href="{{ route('dosen.template') }}"
                    class="w-full flex items-center justify-center gap-2.5 px-6 py-3 rounded-[9px] border border-secondary text-secondary hover:bg-secondary/10 font-bold text-sm sm:text-base transition cursor-pointer shadow-2xs active:scale-[0.99]"
                >
                    <svg class="w-5 h-5 shrink-0 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12l4.5 4.5m0 0l4.5-4.5M12 3v13.5" />
                    </svg>
                    Download Template Excel
                </a>

                <!-- 2. Import Excel (Green Outline) -->
                <button
                    type="button"
                    id="openImportModalBtnEmpty"
                    class="open-import-modal-btn w-full flex items-center justify-center gap-2.5 px-6 py-3 rounded-[9px] border border-[#16A34A] text-[#16A34A] hover:bg-emerald-50 font-bold text-sm sm:text-base transition cursor-pointer shadow-2xs active:scale-[0.99]"
                >
                    <svg class="w-5 h-5 shrink-0 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    Import Excel
                </button>

                <!-- 3. Tambah Dosen (Blue Solid) -->
                <button
                    type="button"
                    id="openCreateModalBtnEmpty"
                    class="open-create-modal-btn w-full flex items-center justify-center gap-2.5 px-6 py-3 rounded-[9px] bg-[#01568D] hover:bg-primary text-white font-bold text-sm sm:text-base transition cursor-pointer shadow-xs active:scale-[0.99]"
                >
                    <svg class="w-5 h-5 shrink-0 stroke-[2.5]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.5v7m3.5-3.5h-7" />
                    </svg>
                    Tambah Dosen
                </button>
            </div>
        </div>

    @else
        <!-- TABLE STATE -->
        <div class="space-y-5">
            <!-- Toolbar: Left (Search & Status Filter Form), Right (Import Excel & Tambah Dosen) -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                <!-- Left: Search "Cari nama" & Status Dropdown Form -->
                <form method="GET" action="{{ route('dosen.index') }}" id="searchFilterForm" class="flex items-center gap-3">
                    <!-- Search Input -->
                    <div class="relative w-64 sm:w-80 shrink-0">
                        <input
                            type="text"
                            name="search"
                            id="searchDosenInput"
                            value="{{ $search }}"
                            placeholder="Cari nama"
                            class="w-full h-[36px] pl-4 pr-10 rounded-[10px] bg-[#D9D9D9] border-2 border-primary text-black text-sm font-medium placeholder:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary/20 transition"
                        />
                        <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-primary hover:scale-110 transition cursor-pointer" aria-label="Cari">
                            <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Status Filter Dropdown (Custom Mockup Design) -->
                    <div class="relative w-36 sm:w-40 shrink-0" id="filterStatusDropdown">
                        <!-- Trigger Button -->
                        <button
                            type="button"
                            id="filterStatusBtn"
                            class="w-full h-[36px] pl-3.5 pr-1 bg-[#D9D9D9] border-2 border-primary rounded-[10px] flex items-center justify-between cursor-pointer select-none transition focus:outline-none focus:ring-2 focus:ring-primary/20"
                            aria-expanded="false"
                            aria-haspopup="true"
                        >
                            <span id="filterStatusLabel" class="text-sm font-semibold text-gray-900 truncate pr-2">
                                {{ !empty($selectedStatus) && $selectedStatus !== 'all' ? $selectedStatus : 'Status' }}
                            </span>
                            <div class="w-7 h-7 rounded-[6px] bg-secondary border border-primary flex items-center justify-center shrink-0 shadow-2xs">
                                <svg id="filterStatusChevron" class="w-4 h-4 text-primary stroke-primary transition-transform duration-200 rotate-0" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" viewBox="0 0 24 24">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </div>
                        </button>

                        <!-- Hidden Input for Form/Query state & Event Dispatching -->
                        <input type="hidden" name="status" id="filterStatusSelect" value="{{ $selectedStatus ?? '' }}">

                        <!-- Dropdown Menu Popover -->
                        <div
                            id="filterStatusMenu"
                            class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-primary rounded-[10px] shadow-lg py-1.5 z-40 overflow-hidden transition-all duration-150"
                            role="menu"
                        >
                            <button
                                type="button"
                                class="status-option-btn w-full text-left px-4 py-1.5 text-xs sm:text-sm font-medium text-gray-900 hover:bg-black/5 hover:text-primary transition cursor-pointer select-none"
                                data-value=""
                                data-label="All Data"
                            >
                                All Data
                            </button>
                            <button
                                type="button"
                                class="status-option-btn w-full text-left px-4 py-1.5 text-xs sm:text-sm font-medium text-gray-900 hover:bg-black/5 hover:text-primary transition cursor-pointer select-none"
                                data-value="DTPS"
                                data-label="DTPS"
                            >
                                DTPS
                            </button>
                            <button
                                type="button"
                                class="status-option-btn w-full text-left px-4 py-1.5 text-xs sm:text-sm font-medium text-gray-900 hover:bg-black/5 hover:text-primary transition cursor-pointer select-none"
                                data-value="Tidak Tetap"
                                data-label="Tidak tetap"
                            >
                                Tidak tetap
                            </button>
                            <button
                                type="button"
                                class="status-option-btn w-full text-left px-4 py-1.5 text-xs sm:text-sm font-medium text-gray-900 hover:bg-black/5 hover:text-primary transition cursor-pointer select-none"
                                data-value="Non-aktif"
                                data-label="Non-aktif"
                            >
                                Non-aktif
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Right Action Buttons: Import Excel & Tambah Dosen -->
                <div class="flex items-center gap-3 shrink-0">
                    <!-- Import Excel (Green Border, Single Line) -->
                    <button
                        type="button"
                        id="openImportModalBtnTable"
                        class="open-import-modal-btn h-[35px] inline-flex items-center gap-2 px-3.5 rounded-[8px] border border-[#16A34A] text-[#16A34A] bg-white hover:bg-emerald-50 text-xs sm:text-[13px] font-bold transition cursor-pointer shadow-2xs whitespace-nowrap shrink-0"
                    >
                        <svg class="w-4 h-4 stroke-[2.2] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span>Import Excel</span>
                    </button>

                    <!-- Tambah Dosen (Dark Blue Solid, Single Line) -->
                    <button
                        type="button"
                        id="openCreateModalBtnTable"
                        class="open-create-modal-btn h-[35px] inline-flex items-center gap-2 px-3.5 rounded-[8px] bg-[#01568D] hover:bg-[#012650] text-white text-xs sm:text-[13px] font-bold shadow-xs transition cursor-pointer whitespace-nowrap shrink-0"
                    >
                        <svg class="w-4 h-4 stroke-[2.5] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.5v7m3.5-3.5h-7" />
                        </svg>
                        <span>Tambah Dosen</span>
                    </button>
                </div>
            </div>

            <!-- Table (Exact 6 Columns from Mockup: No, Nama Dosen, NIDN, Status, Sertifikasi, Action) -->
            <div class="mx-1 mt-7 w-full overflow-x-auto">
                <table class="w-full text-left border-collapse table-fixed min-w-[760px]">
                    <thead>
                        <tr class="h-[43px] border-t border-b border-black text-black font-bold text-sm sm:text-base">
                            <th scope="col" class="w-[8%] px-3 text-center">No</th>
                            <th scope="col" class="w-[22.5%] px-4 text-left">Nama Dosen</th>
                            <th scope="col" class="w-[16.8%] px-4 text-left">NIDN</th>
                            <th scope="col" class="w-[16.8%] px-4 text-left">Status</th>
                            <th scope="col" class="w-[19%] px-4 text-left">Sertifikasi</th>
                            <th scope="col" class="w-[16.9%] px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-black">
                        @forelse($dosenList as $index => $d)
                            @php
                                $pageCurrent = (int) ($pagination['current_page'] ?? 1);
                                $pageLimit = (int) ($pagination['per_page'] ?? 10);
                                $rowNumber = $loop->iteration + (($pageCurrent - 1) * $pageLimit);
                                $rawId = $d['id_dosen'] ?? ($d['id'] ?? $loop->index);
                                $dosenId = is_scalar($rawId) ? $rawId : (is_array($rawId) ? ($rawId['id'] ?? $loop->index) : $loop->index);

                                $rawNama = $d['nama_dosen'] ?? ($d['nama'] ?? ($d['name'] ?? '-'));
                                $nama = is_string($rawNama) ? $rawNama : (is_array($rawNama) ? ($rawNama['nama'] ?? ($rawNama['name'] ?? '-')) : '-');

                                $rawNidn = $d['nidn'] ?? '-';
                                $nidn = is_scalar($rawNidn) ? (string) $rawNidn : '-';

                                $rawStatus = $d['status_dosen'] ?? ($d['status'] ?? 'DTPS');
                                $statusDosen = is_string($rawStatus) ? $rawStatus : (is_array($rawStatus) ? ($rawStatus['status'] ?? ($rawStatus['nama'] ?? 'DTPS')) : 'DTPS');

                                $rawEmail = $d['email'] ?? '-';
                                $email = is_string($rawEmail) ? $rawEmail : '-';

                                $rawSertifikasi = $d['sertifikasi'] ?? '';
                                $sertifikasi = is_string($rawSertifikasi) ? $rawSertifikasi : (is_array($rawSertifikasi) ? ($rawSertifikasi['url'] ?? ($rawSertifikasi['link'] ?? '')) : '');
                                $isSertifikasi = ! empty($sertifikasi);
                            @endphp
                            <tr class="dosen-row h-[69.5px] border-b border-[#C7C7C7] hover:bg-gray-50/40 transition-colors">
                                <td class="px-3 text-center text-black font-normal text-sm sm:text-base align-middle">{{ $rowNumber }}</td>
                                <td class="px-4 text-left align-middle">
                                    <span class="font-normal text-sm sm:text-base text-black dosen-nama tracking-tight block max-w-xs leading-snug">{{ $nama }}</span>
                                </td>
                                <td class="px-4 text-left text-black font-normal text-sm sm:text-base align-middle"><span class="dosen-nidn">{{ $nidn }}</span></td>
                                <td class="px-4 text-left text-black font-normal text-sm sm:text-base align-middle"><span class="dosen-status">{{ $statusDosen }}</span></td>
                                <td class="px-4 text-left text-black font-normal text-sm sm:text-base align-middle"><span>{{ $isSertifikasi ? 'Tersertifikasi' : 'Belum' }}</span></td>
                                <td class="px-4 text-center align-middle">
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- Edit (Yellow Pencil) -->
                                        <button
                                            type="button"
                                            class="edit-dosen-btn text-[#FFCC03] hover:text-amber-500 transition cursor-pointer"
                                            title="Edit Data Dosen"
                                            data-id="{{ $dosenId }}"
                                            data-nama="{{ $nama }}"
                                            data-nidn="{{ $nidn }}"
                                            data-status="{{ $statusDosen }}"
                                            data-email="{{ $email }}"
                                            data-sertifikasi="{{ $sertifikasi }}"
                                            data-jabatan-id="{{ $d['id_jabatan'] ?? ($d['jabatan']['id_jabatan'] ?? ($d['jabatan']['id'] ?? '')) }}"
                                            data-pendidikan-id="{{ $d['id_pendidikan'] ?? ($d['pendidikan']['id_pendidikan'] ?? ($d['pendidikan']['id'] ?? '')) }}"
                                            data-action="{{ route('dosen.update', $dosenId) }}"
                                        >
                                            <svg class="w-5 h-5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>

                                        <!-- Delete (Solid Red Trash Can) -->
                                        <button
                                            type="button"
                                            class="text-[#DC2626] hover:text-[#B91C1C] transition cursor-pointer"
                                            title="Hapus Dosen"
                                            data-confirm-delete
                                            data-action="{{ route('dosen.destroy', $dosenId) }}"
                                            data-title="Hapus Data Dosen?"
                                            data-message="Apakah Anda yakin ingin menghapus data dosen ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                                        >
                                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Z" clip-rule="evenodd" />
                                            </svg>
                                        </button>

                                        <!-- Detail / View (Navy Blue Eye) -->
                                        <a
                                            href="{{ route('dosen.show', $dosenId) }}"
                                            class="text-[#012650] hover:text-[#01568D] transition cursor-pointer"
                                            title="Lihat Detail Dosen"
                                            data-test="dosen-detail-link"
                                        >
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-6 border-b border-gray-200 text-center text-gray-500">
                                    @if($hasActiveFilters)
                                        <p class="text-base font-semibold text-gray-900 mb-1">Tidak ada data dosen yang sesuai</p>
                                        <p class="text-sm text-gray-500">Coba ubah kata kunci pencarian atau filter status Anda.</p>
                                    @else
                                        <p class="text-base font-semibold text-gray-900 mb-1">Belum ada data dosen</p>
                                        <p class="text-sm text-gray-500">Silakan klik tombol Tambah Dosen atau Import Excel untuk menambahkan data.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$pagination" />
        </div>
    @endif
</div>

<!-- MODAL: TAMBAHKAN DOSEN -->
<div 
    id="createDosenModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createDosenModalTitle"
>
    <div class="dosen-modal-card relative bg-white rounded-[20px] p-8 max-w-[440px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 overflow-hidden">
        <!-- Title: Tambahkan Dosen -->
        <h3 id="createDosenModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan Dosen
        </h3>

        <form action="{{ route('dosen.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- 1. Nama -->
            <div>
                <label for="createNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama
                </label>
                <input 
                    type="text" 
                    name="nama_dosen" 
                    id="createNamaInput" 
                    placeholder="Isi Nama Dosen" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- 2. NIDN -->
            <div>
                <label for="createNidnInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    NIDN
                </label>
                <input 
                    type="text" 
                    name="nidn" 
                    id="createNidnInput" 
                    placeholder="Isi NIDN Dosen" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- 3. Status -->
            <div>
                <label for="createStatusSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Status
                </label>
                <div class="relative">
                    <select 
                        name="status_dosen" 
                        id="createStatusSelect" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Status Dosen</option>
                        <option value="DTPS">DTPS</option>
                        <option value="Tidak Tetap">Tidak Tetap</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- 4. Email -->
            <div>
                <label for="createEmailInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Email
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="createEmailInput" 
                    placeholder="Isi Email" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- 5. Sertifikasi (G. Drive) -->
            <div>
                <label for="createSertifikasiInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Sertifikasi (G. Drive)
                </label>
                <input
                    type="text"
                    name="sertifikasi"
                    id="createSertifikasiInput"
                    placeholder="Masukkan Tautan Sertifikasi"
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- 6. Jabatan Akademik -->
            <div>
                <label for="createJabatanSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jabatan Akademik
                </label>
                <div class="relative">
                    <select
                        name="id_jabatan"
                        id="createJabatanSelect"
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                    >
                        <option value="">Pilih Jabatan Akademik (Opsional)</option>
                        @foreach($jabatanList as $j)
                            @php
                                $jId = $j['id_jabatan'] ?? ($j['id'] ?? '');
                                $jNama = $j['nama_jabatan'] ?? ($j['nama'] ?? ($j['nama_jabatan_akademik'] ?? $jId));
                            @endphp
                            @if(!empty($jId))
                                <option value="{{ $jId }}">{{ $jNama }}</option>
                            @endif
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- 7. Pendidikan Terakhir -->
            <div>
                <label for="createPendidikanSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Pendidikan Terakhir
                </label>
                <div class="relative">
                    <select
                        name="id_pendidikan"
                        id="createPendidikanSelect"
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                    >
                        <option value="">Pilih Pendidikan Terakhir (Opsional)</option>
                        @foreach($pendidikanList as $p)
                            @php
                                $pId = $p['id_pendidikan'] ?? ($p['id'] ?? '');
                                $pNama = $p['nama_pendidikan'] ?? ($p['jenjang'] ?? ($p['nama'] ?? $pId));
                            @endphp
                            @if(!empty($pId))
                                <option value="{{ $pId }}">{{ $pNama }}</option>
                            @endif
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Action Buttons: Batal & Simpan -->
            <div class="grid grid-cols-2 gap-4 pt-3">
                <button
                    type="button"
                    id="closeCreateModalBtn"
                    class="w-full py-2.5 px-6 rounded-[8px] bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="w-full py-2.5 px-6 rounded-[8px] bg-[#01568D] hover:bg-primary text-white font-bold text-sm shadow-xs transition cursor-pointer text-center"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: PERBARUI DATA DOSEN -->
<div 
    id="editDosenModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editDosenModalTitle"
>
    <div class="dosen-modal-card relative bg-white rounded-[20px] p-8 max-w-[440px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 overflow-hidden">
        <!-- Title: Perbarui Data Dosen -->
        <h3 id="editDosenModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Perbarui Data Dosen
        </h3>

        <form id="editDosenForm" action="#" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- 1. Nama -->
            <div>
                <label for="editNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama
                </label>
                <input 
                    type="text" 
                    name="nama_dosen" 
                    id="editNamaInput" 
                    placeholder="Isi Nama Dosen" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- 2. NIDN -->
            <div>
                <label for="editNidnInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    NIDN
                </label>
                <input 
                    type="text" 
                    name="nidn" 
                    id="editNidnInput" 
                    placeholder="Isi NIDN Dosen" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- 3. Status -->
            <div>
                <label for="editStatusSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Status
                </label>
                <div class="relative">
                    <select 
                        name="status_dosen" 
                        id="editStatusSelect" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Status Dosen</option>
                        <option value="DTPS">DTPS</option>
                        <option value="Tidak Tetap">Tidak Tetap</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- 4. Email -->
            <div>
                <label for="editEmailInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Email
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="editEmailInput" 
                    placeholder="Isi Email" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- 5. Sertifikasi (G. Drive) -->
            <div>
                <label for="editSertifikasiInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Sertifikasi (G. Drive)
                </label>
                <input
                    type="text"
                    name="sertifikasi"
                    id="editSertifikasiInput"
                    placeholder="Masukkan Tautan Sertifikasi"
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- 6. Jabatan Akademik -->
            <div>
                <label for="editJabatanSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jabatan Akademik
                </label>
                <div class="relative">
                    <select
                        name="id_jabatan"
                        id="editJabatanSelect"
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                    >
                        <option value="">Pilih Jabatan Akademik (Opsional)</option>
                        @foreach($jabatanList as $j)
                            @php
                                $jId = $j['id_jabatan'] ?? ($j['id'] ?? '');
                                $jNama = $j['nama_jabatan'] ?? ($j['nama'] ?? ($j['nama_jabatan_akademik'] ?? $jId));
                            @endphp
                            @if(!empty($jId))
                                <option value="{{ $jId }}">{{ $jNama }}</option>
                            @endif
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- 7. Pendidikan Terakhir -->
            <div>
                <label for="editPendidikanSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Pendidikan Terakhir
                </label>
                <div class="relative">
                    <select
                        name="id_pendidikan"
                        id="editPendidikanSelect"
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                    >
                        <option value="">Pilih Pendidikan Terakhir (Opsional)</option>
                        @foreach($pendidikanList as $p)
                            @php
                                $pId = $p['id_pendidikan'] ?? ($p['id'] ?? '');
                                $pNama = $p['nama_pendidikan'] ?? ($p['jenjang'] ?? ($p['nama'] ?? $pId));
                            @endphp
                            @if(!empty($pId))
                                <option value="{{ $pId }}">{{ $pNama }}</option>
                            @endif
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Action Buttons: Batal & Simpan -->
            <div class="grid grid-cols-2 gap-4 pt-3">
                <button
                    type="button"
                    id="closeEditModalBtn"
                    class="w-full py-2.5 px-6 rounded-[8px] bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="w-full py-2.5 px-6 rounded-[8px] bg-[#01568D] hover:bg-primary text-white font-bold text-sm shadow-xs transition cursor-pointer text-center"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: IMPORT EXCEL -->
<div 
    id="importExcelModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="importExcelModalTitle"
>
    <div class="dosen-modal-card relative bg-white rounded-[20px] p-8 max-w-[440px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 overflow-hidden">
        <!-- Title: Import Data Dosen -->
        <h3 id="importExcelModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Import Data Dosen
        </h3>

        <form id="importExcelForm" action="{{ route('dosen.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Hidden Actual File Input -->
            <input 
                type="file" 
                name="file" 
                id="importFileInput" 
                accept=".xlsx,.xls,.csv" 
                class="hidden" 
                required 
            />

            <!-- Selected File Preview Pill (Shown when file is selected - Mockup State 2) -->
            <div id="filePreviewContainer" class="hidden w-full px-4 py-3 rounded-[10px] bg-white-form items-center justify-between gap-3 transition">
                <div class="flex items-center gap-3 min-w-0">
                    <svg class="w-5 h-5 text-black shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                    </svg>
                    <span id="selectedFileName" class="text-sm font-bold text-gray-900 truncate">
                        Document 1
                    </span>
                </div>
                <button 
                    type="button" 
                    id="removeFileBtn"
                    class="text-gray-900 hover:text-rose-600 transition p-1 cursor-pointer shrink-0"
                    title="Hapus berkas"
                >
                    <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Dashed Dropzone / Upload Area -->
            <div 
                id="dropzoneArea" 
                class="w-full h-36 rounded-xl border-2 border-dashed border-[#8093A7] hover:border-primary/60 bg-transparent flex flex-col items-center justify-center cursor-pointer transition select-none group"
            >
                <div class="flex flex-col items-center justify-center pointer-events-none">
                    <svg class="w-10 h-10 text-gray-800 stroke-[2.2] mb-1.5 transition-transform group-hover:-translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0-12l-4 4m4-4l4 4" />
                    </svg>
                    <span class="text-xs sm:text-sm font-medium text-gray-800">
                        Pilih File
                    </span>
                </div>
            </div>

            <!-- Action Buttons: Dynamic based on file selected state -->
            <!-- 1. State Tanpa File (Mockup State 1): Hanya tombol "Batal" full-width -->
            <div id="noFileButtons" class="pt-2">
                <button
                    type="button"
                    id="closeImportModalBtnSingle"
                    class="w-full py-2.5 px-6 rounded-[8px] bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
            </div>

            <!-- 2. State Dengan File (Mockup State 2): Grid "Batal" & "Import" -->
            <div id="withFileButtons" class="hidden grid-cols-2 gap-4 pt-2">
                <button
                    type="button"
                    id="closeImportModalBtnDual"
                    class="w-full py-2.5 px-6 rounded-[8px] bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="w-full py-2.5 px-6 rounded-[8px] bg-[#01568D] hover:bg-primary text-white font-bold text-sm shadow-xs transition cursor-pointer text-center"
                >
                    Import
                </button>
            </div>
        </form>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.dosen-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
            document.body.classList.add('overflow-hidden');
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.dosen-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
            document.body.classList.remove('overflow-hidden');
        }

        // 1. Create Modal
        const createModal = document.getElementById('createDosenModal');
        const openCreateButtons = document.querySelectorAll('.open-create-modal-btn');
        const closeCreateBtn = document.getElementById('closeCreateModalBtn');

        openCreateButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                showModal(createModal);
                setTimeout(() => document.getElementById('createNamaInput')?.focus(), 100);
            });
        });

        if (closeCreateBtn) {
            closeCreateBtn.addEventListener('click', () => hideModal(createModal));
        }

        // 2. Import Modal & File Upload Logic
        const importModal = document.getElementById('importExcelModal');
        const openImportButtons = document.querySelectorAll('.open-import-modal-btn');
        const closeImportSingle = document.getElementById('closeImportModalBtnSingle');
        const closeImportDual = document.getElementById('closeImportModalBtnDual');
        const dropzoneArea = document.getElementById('dropzoneArea');
        const importFileInput = document.getElementById('importFileInput');
        const filePreviewContainer = document.getElementById('filePreviewContainer');
        const selectedFileName = document.getElementById('selectedFileName');
        const removeFileBtn = document.getElementById('removeFileBtn');
        const noFileButtons = document.getElementById('noFileButtons');
        const withFileButtons = document.getElementById('withFileButtons');

        function resetImportModal() {
            if (importFileInput) importFileInput.value = '';
            if (filePreviewContainer) {
                filePreviewContainer.classList.add('hidden');
                filePreviewContainer.classList.remove('flex');
            }
            if (noFileButtons) noFileButtons.classList.remove('hidden');
            if (withFileButtons) {
                withFileButtons.classList.add('hidden');
                withFileButtons.classList.remove('grid');
            }
        }

        function setFileSelected(fileName) {
            if (selectedFileName) selectedFileName.textContent = fileName || 'Document 1';
            if (filePreviewContainer) {
                filePreviewContainer.classList.remove('hidden');
                filePreviewContainer.classList.add('flex');
            }
            if (noFileButtons) noFileButtons.classList.add('hidden');
            if (withFileButtons) {
                withFileButtons.classList.remove('hidden');
                withFileButtons.classList.add('grid');
            }
        }

        openImportButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                resetImportModal();
                showModal(importModal);
            });
        });

        [closeImportSingle, closeImportDual].forEach(btn => {
            btn?.addEventListener('click', () => {
                hideModal(importModal);
                setTimeout(resetImportModal, 200);
            });
        });

        // Click dropzone to open file picker
        if (dropzoneArea && importFileInput) {
            dropzoneArea.addEventListener('click', () => {
                importFileInput.click();
            });

            // Drag and drop handlers
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzoneArea.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzoneArea.classList.add('border-primary', 'bg-blue-50/20');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzoneArea.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzoneArea.classList.remove('border-primary', 'bg-blue-50/20');
                });
            });

            dropzoneArea.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length > 0) {
                    importFileInput.files = dt.files;
                    setFileSelected(dt.files[0].name);
                }
            });
        }

        // On input change
        if (importFileInput) {
            importFileInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    setFileSelected(this.files[0].name);
                } else {
                    resetImportModal();
                }
            });
        }

        // Remove selected file
        if (removeFileBtn) {
            removeFileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                resetImportModal();
            });
        }

        // 3. Edit Modal
        const editModal = document.getElementById('editDosenModal');
        const editForm = document.getElementById('editDosenForm');
        const closeEditBtn = document.getElementById('closeEditModalBtn');
        const editNama = document.getElementById('editNamaInput');
        const editNidn = document.getElementById('editNidnInput');
        const editStatus = document.getElementById('editStatusSelect');
        const editEmail = document.getElementById('editEmailInput');
        const editSertifikasi = document.getElementById('editSertifikasiInput');
        const editJabatan = document.getElementById('editJabatanSelect');
        const editPendidikan = document.getElementById('editPendidikanSelect');

        document.querySelectorAll('.edit-dosen-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const action = this.getAttribute('data-action');
                const nama = this.getAttribute('data-nama');
                const nidn = this.getAttribute('data-nidn');
                const status = this.getAttribute('data-status');
                const email = this.getAttribute('data-email');
                const sertifikasi = this.getAttribute('data-sertifikasi');
                const jabatanId = this.getAttribute('data-jabatan-id');
                const pendidikanId = this.getAttribute('data-pendidikan-id');

                if (editForm) editForm.action = action;
                if (editNama) editNama.value = nama || '';
                if (editNidn) editNidn.value = (nidn && nidn !== '-') ? nidn : '';
                if (editStatus) {
                    if (status === 'Tidak Tetap' || status === 'DTT' || (status && status.toLowerCase().includes('tidak tetap'))) {
                        editStatus.value = 'Tidak Tetap';
                    } else if (status === 'DTPS') {
                        editStatus.value = 'DTPS';
                    } else {
                        editStatus.value = status || '';
                    }
                }
                if (editEmail) editEmail.value = (email && email !== '-') ? email : '';
                if (editSertifikasi) editSertifikasi.value = sertifikasi || '';
                if (editJabatan) editJabatan.value = jabatanId || '';
                if (editPendidikan) editPendidikan.value = pendidikanId || '';

                showModal(editModal);
                setTimeout(() => editNama?.focus(), 100);
            });
        });

        if (closeEditBtn) {
            closeEditBtn.addEventListener('click', () => hideModal(editModal));
        }

        // 4. Close on Backdrop Click
        [createModal, editModal, importModal].forEach(m => {
            m?.addEventListener('click', function (e) {
                if (e.target === m) hideModal(m);
            });
        });

        // 5. Close on Escape Key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideModal(createModal);
                hideModal(editModal);
                hideModal(importModal);
                closeStatusFilterMenu();
            }
        });

        // 6. Custom Status Filter Dropdown Logic
        const statusFilterDropdown = document.getElementById('filterStatusDropdown');
        const statusFilterBtn = document.getElementById('filterStatusBtn');
        const statusFilterMenu = document.getElementById('filterStatusMenu');
        const statusFilterChevron = document.getElementById('filterStatusChevron');
        const statusFilterLabel = document.getElementById('filterStatusLabel');
        const statusSelect = document.getElementById('filterStatusSelect');
        const statusOptions = document.querySelectorAll('.status-option-btn');
        const searchFilterForm = document.getElementById('searchFilterForm');

        function toggleStatusFilterMenu() {
            if (!statusFilterMenu) return;
            const isClosed = statusFilterMenu.classList.contains('hidden');
            if (isClosed) {
                statusFilterMenu.classList.remove('hidden');
                statusFilterBtn?.setAttribute('aria-expanded', 'true');
                statusFilterChevron?.classList.remove('rotate-0');
                statusFilterChevron?.classList.add('rotate-180');
            } else {
                closeStatusFilterMenu();
            }
        }

        function closeStatusFilterMenu() {
            if (!statusFilterMenu) return;
            statusFilterMenu.classList.add('hidden');
            statusFilterBtn?.setAttribute('aria-expanded', 'false');
            statusFilterChevron?.classList.remove('rotate-180');
            statusFilterChevron?.classList.add('rotate-0');
        }

        if (statusFilterBtn) {
            statusFilterBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleStatusFilterMenu();
            });
        }

        statusOptions.forEach(opt => {
            opt.addEventListener('click', function (e) {
                e.stopPropagation();
                const val = this.getAttribute('data-value') || '';
                const lbl = this.getAttribute('data-label') || '';

                if (statusSelect) {
                    statusSelect.value = val;
                }

                if (statusFilterLabel) {
                    statusFilterLabel.textContent = val === '' ? 'Status' : lbl;
                }

                closeStatusFilterMenu();

                // Submit searchFilterForm for server-side search & filtering
                if (searchFilterForm) {
                    searchFilterForm.submit();
                }
            });
        });

        document.addEventListener('click', function (e) {
            if (statusFilterDropdown && !statusFilterDropdown.contains(e.target)) {
                closeStatusFilterMenu();
            }
        });
    });
</script>
@endsection
