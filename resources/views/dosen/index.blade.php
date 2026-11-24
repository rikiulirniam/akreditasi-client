@extends('layouts.app')

@section('title', 'Dosen')

@php
    $hasActiveFilters = !empty($search) || !empty($selectedStatus) || !empty($jabatanFilter) || !empty($serdosFilter);
@endphp

@if(!empty($dosenList) && count($dosenList) > 0)
    @section('subtitle', ($pagination['total'] ?? count($dosenList)) . ' Data ditemukan')
@endif

@section('content')
@php
    $eligiblePendidikanList = collect($pendidikanList)
        ->map(function ($pendidikan) {
            $jenjang = trim((string) ($pendidikan['jenjang'] ?? ($pendidikan['nama_pendidikan'] ?? ($pendidikan['nama'] ?? ''))));
            $pendidikan['id'] = $pendidikan['id_pendidikan'] ?? ($pendidikan['id'] ?? '');
            $pendidikan['jenjang'] = $jenjang;
            $pendidikan['label'] = $jenjang;

            if (! empty($pendidikan['program_studi']) || ! empty($pendidikan['universitas'])) {
                $details = array_filter([
                    $pendidikan['program_studi'] ?? null,
                    $pendidikan['universitas'] ?? null,
                ]);
                $pendidikan['label'] .= ' - '.implode(', ', $details);
            }

            return $pendidikan;
        })
        ->filter(fn ($pendidikan) => !empty($pendidikan['id']) && preg_match('/(?:^|[^A-Z0-9])(?:S[123]|D4)(?:$|[^A-Z0-9])|terapan/i', $pendidikan['jenjang']) === 1)
        ->unique(fn ($pendidikan) => mb_strtoupper(trim((string) $pendidikan['label'])))
        ->sortByDesc(function ($pendidikan) {
            $jenjang = strtoupper($pendidikan['jenjang']);

            return preg_match('/S3/', $jenjang) ? 3 : (preg_match('/S2/', $jenjang) ? 2 : 1);
        })
        ->values();
@endphp
<div class="space-y-5">
        <!-- Toolbar: Search, filters, and actions -->
            <div class="mb-2 flex flex-col items-stretch justify-between gap-3 lg:flex-row lg:flex-nowrap lg:items-center lg:gap-2">
                <!-- Left: Search "Cari nama" & Status Dropdown Form -->
                <form method="GET" action="{{ route('dosen.index') }}" id="searchFilterForm" class="flex min-w-0 items-center gap-2 lg:flex-1 lg:flex-nowrap">
                    <!-- Search Input -->
                    <div class="relative w-40 shrink-0">
                        <button type="submit" class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 transition hover:text-primary cursor-pointer" aria-label="Cari">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </button>
                        <input
                            type="text"
                            name="search"
                            id="searchDosenInput"
                            value="{{ $search }}"
                            placeholder="Cari nama"
                            class="h-10 w-full rounded-lg border border-transparent bg-gray-100 pl-10 pr-3 text-xs font-medium text-gray-800 placeholder:text-gray-800 focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/10 transition"
                        />
                    </div>

                    <!-- Status Filter Dropdown (Custom Mockup Design) -->
                    <div class="relative w-28 shrink-0" id="filterStatusDropdown">
                        <!-- Trigger Button -->
                        <button
                            type="button"
                            id="filterStatusBtn"
                            class="flex h-10 w-full cursor-pointer select-none items-center justify-between rounded-lg border border-transparent bg-gray-100 px-3 text-xs font-semibold text-gray-800 transition focus:outline-none focus:ring-2 focus:ring-primary/10"
                            aria-expanded="false"
                            aria-haspopup="true"
                        >
                            <span id="filterStatusLabel" class="truncate pr-2">
                                {{ $selectedStatusLabel ?? 'Status' }}
                            </span>
                            <svg id="filterStatusChevron" class="h-4 w-4 shrink-0 text-gray-500 transition-transform duration-200 rotate-0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" viewBox="0 0 24 24">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <!-- Hidden Input for Form/Query state & Event Dispatching -->
                        <input type="hidden" name="status" id="filterStatusSelect" value="{{ $selectedStatus ?? '' }}">

                        <!-- Dropdown Menu Popover -->
                        <div
                            id="filterStatusMenu"
                            class="hidden absolute left-0 top-full mt-1.5 w-full bg-white border border-gray-200 rounded-lg shadow-lg py-1.5 z-40 overflow-hidden transition-all duration-150"
                            role="menu"
                        >
                            <button
                                type="button"
                                class="status-option-btn w-full text-left px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 hover:text-primary transition cursor-pointer select-none"
                                data-value=""
                                data-label="All Data"
                            >
                                All Data
                            </button>
                            <button
                                type="button"
                                class="status-option-btn w-full text-left px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 hover:text-primary transition cursor-pointer select-none"
                                data-value="DTPS"
                                data-label="DTPS"
                            >
                                DTPS
                            </button>
                            <button
                                type="button"
                                class="status-option-btn w-full text-left px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 hover:text-primary transition cursor-pointer select-none"
                                data-value="DTT"
                                data-label="Tidak tetap"
                            >
                                Tidak tetap
                            </button>
                        </div>
                    </div>

                    <select
                        name="jabatan"
                        id="filterJabatanSelect"
                        aria-label="Filter jabatan akademik"
                        class="h-10 w-36 shrink-0 rounded-lg border border-transparent bg-gray-100 px-2.5 text-xs font-medium text-gray-800 focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/10"
                        onchange="this.form.submit()"
                    >
                        <option value="">Semua Jabatan</option>
                        @foreach($jabatanList as $jabatan)
                            @php
                                $filterJabatanId = $jabatan['id_jabatan'] ?? ($jabatan['id'] ?? '');
                                $filterJabatanName = $jabatan['nama_jabatan'] ?? ($jabatan['nama'] ?? '');
                            @endphp
                            @if($filterJabatanId !== '' && $filterJabatanName !== '')
                                <option value="{{ $filterJabatanId }}" {{ (string) $jabatanFilter === (string) $filterJabatanId ? 'selected' : '' }}>
                                    {{ $filterJabatanName }}
                                </option>
                            @endif
                        @endforeach
                    </select>

                    <select
                        name="serdos"
                        id="filterSerdosSelect"
                        aria-label="Filter sertifikasi dosen"
                        class="h-10 w-36 shrink-0 rounded-lg border border-transparent bg-gray-100 px-2.5 text-xs font-medium text-gray-800 focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/10"
                        onchange="this.form.submit()"
                    >
                        <option value="">Serdos</option>
                        <option value="tersertifikasi" {{ $serdosFilter === 'tersertifikasi' ? 'selected' : '' }}>Tersertifikasi</option>
                        <option value="belum" {{ $serdosFilter === 'belum' ? 'selected' : '' }}>Belum Tersertifikasi</option>
                    </select>

                </form>

                <!-- Right Action Buttons: Export, import, and add lecturer -->
                <div class="flex shrink-0 items-center gap-2">
                    <button
                        type="button"
                        id="exportDosenBtn"
                        data-export-url="{{ route('dosen.export') }}"
                        class="inline-flex h-10 shrink-0 cursor-pointer items-center gap-1.5 whitespace-nowrap rounded-lg border-0 bg-blue-600 px-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4m-4 7v1a2 2 0 002 2h12a2 2 0 002-2v-1" />
                        </svg>
                        <span class="export-dosen-label">Download Excel</span>
                    </button>
                    <button
                        type="button"
                        id="openImportModalBtnTable"
                        class="open-import-modal-btn inline-flex h-10 shrink-0 cursor-pointer items-center gap-1.5 whitespace-nowrap rounded-lg border-0 bg-green-700 px-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-green-800"
                    >
                        <svg class="w-4 h-4 stroke-[2.2] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span>Import Excel</span>
                    </button>

                    <button
                        type="button"
                        id="openCreateModalBtnTable"
                        class="open-create-modal-btn inline-flex h-10 shrink-0 cursor-pointer items-center gap-1.5 whitespace-nowrap rounded-lg border-0 bg-primary px-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-additional"
                        style="background-color: #0F294A; color: #FFFFFF;"
                    >
                        <svg class="w-4 h-4 stroke-[2.5] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.5v7m3.5-3.5h-7" />
                        </svg>
                        <span>Tambah Dosen</span>
                    </button>
                </div>
            </div>

            <div class="mt-7 min-w-0 overflow-x-auto">
                <table id="dosen-table" class="w-full min-w-262.5 table-fixed text-left border-collapse">
                    <thead>
                        <tr class="border-y border-gray-200 text-gray-900 font-semibold text-sm">
                            <th class="w-16 text-center py-3.5 px-2 sm:px-3">No</th>
                            <th class="py-3.5 px-2 sm:px-3">Identitas Dosen</th>
                            <th class="py-3.5 px-2 sm:px-3">Status DTPS/DTT</th>
                            <th class="py-3.5 px-2 sm:px-3">Pendidikan Terakhir</th>
                            <th class="py-3.5 px-2 sm:px-3">Jabatan Akademik</th>
                            <th class="py-3.5 px-2 sm:px-3">Sertifikasi Dosen</th>
                            <th class="w-28 text-center py-3.5 px-2 sm:px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-800">
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
                                $rawNuptk = $d['nuptk'] ?? '-';
                                $nuptk = is_scalar($rawNuptk) ? (string) $rawNuptk : '-';

                                $rawStatus = $d['status_dosen'] ?? ($d['status'] ?? 'DTPS');
                                $statusDosen = is_string($rawStatus) ? $rawStatus : (is_array($rawStatus) ? ($rawStatus['status'] ?? ($rawStatus['nama'] ?? 'DTPS')) : 'DTPS');

                                $sertifikasi = $d['_serdos_status'] ?? 'Belum Tersertifikasi';
                                $isSertifikasi = $sertifikasi === 'Tersertifikasi';
                                $rawJabatan = $d['jabatan_akademik'] ?? ($d['jabatan'] ?? []);
                                $jabatanNama = is_array($rawJabatan)
                                    ? ($rawJabatan['nama_jabatan'] ?? ($rawJabatan['nama'] ?? ''))
                                    : (is_string($rawJabatan) ? $rawJabatan : '');
                                $rawPendidikan = $d['pendidikan_terakhir'] ?? ($d['pendidikan'] ?? []);
                                $pendidikanJenjang = is_array($rawPendidikan)
                                    ? ($rawPendidikan['jenjang'] ?? ($rawPendidikan['nama_pendidikan'] ?? ''))
                                    : (is_string($rawPendidikan) ? $rawPendidikan : '');
                                $pendidikanJenjang = preg_match('/(?:^|[^A-Z0-9])(?:S[123]|D4)(?:$|[^A-Z0-9])|terapan/i', $pendidikanJenjang) === 1
                                    ? $pendidikanJenjang
                                    : '-';
                            @endphp
                            <tr class="dosen-row border-b border-gray-200 hover:bg-gray-50/60 transition-colors duration-150 group">
                                <td class="w-16 text-center text-gray-900 py-4 px-2 sm:px-3">{{ $rowNumber }}.</td>
                                <td class="text-sm text-gray-900 py-4 px-2 sm:px-3 wrap-break-word">
                                    <span class="block font-semibold">{{ $nama }}</span>
                                    <span class="mt-1 block text-xs text-gray-600">NIDN: {{ $nidn }} · NUPTK: {{ $nuptk }}</span>
                                </td>
                                <td class="text-sm text-gray-900 py-4 px-2 sm:px-3 wrap-break-word">{{ $statusDosen }}</td>
                                <td class="text-sm text-gray-900 py-4 px-2 sm:px-3 wrap-break-word">{{ $pendidikanJenjang }}</td>
                                <td class="text-sm text-gray-900 py-4 px-2 sm:px-3 wrap-break-word">{{ $jabatanNama !== '' ? $jabatanNama : '-' }}</td>
                                <td class="text-sm py-4 px-2 sm:px-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $isSertifikasi ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $isSertifikasi ? 'Tersertifikasi' : 'Belum Tersertifikasi' }}
                                    </span>
                                </td>
                                <td class="w-28 py-4 px-2 sm:px-3">
                                    <div class="flex items-center justify-center gap-2 sm:gap-4">
                                        <button
                                            type="button"
                                            class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                            title="Hapus Dosen"
                                            data-confirm-delete
                                            data-action="{{ route('dosen.destroy', $dosenId) }}"
                                            data-title="Hapus Data Dosen?"
                                            data-message="Apakah Anda yakin ingin menghapus data dosen ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                                        >
                                            <x-icon.delete class="w-5 h-5" />
                                        </button>
                                        <a
                                            href="{{ route('dosen.show', $dosenId) }}"
                                            class="text-primary hover:text-additional transition p-1 cursor-pointer"
                                            title="Lihat Detail Dosen"
                                            data-test="dosen-detail-link"
                                        >
                                            <x-icon.eye class="w-5 h-5" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @if($hasActiveFilters)
                                <tr><td colspan="7" class="py-12 px-6 border-b border-gray-200 text-center text-gray-400"><p class="text-sm font-semibold text-gray-600">Tidak ada data dosen yang sesuai</p><p class="text-xs text-gray-400 mt-1">Coba ubah kata kunci pencarian atau filter Anda.</p></td></tr>
                            @else
                                <tr><td colspan="7" class="py-12 px-6 border-b border-gray-200 text-center text-gray-400"><p class="text-sm font-semibold text-gray-600">Belum ada data dosen</p><p class="text-xs text-gray-400 mt-1">Silakan klik tombol Tambah Dosen atau Import Excel untuk menambahkan data.</p></td></tr>
                            @endif
                        @endforelse
                    </tbody>
                </table>
            </div>

    </div>

    <x-pagination :paginator="$pagination" />
</div>

<!-- MODAL: TAMBAHKAN DOSEN -->
<div 
    id="createDosenModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createDosenModalTitle"
>
    <div class="dosen-modal-card relative bg-white rounded-[20px] p-8 max-w-110 w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[90vh] overflow-y-auto">
        <!-- Title: Tambahkan Dosen -->
        <h3 id="createDosenModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan Dosen
        </h3>

        <form id="createDosenForm" action="{{ route('dosen.store') }}" method="POST" class="space-y-4">
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

            <div>
                <label for="createNuptkInput" class="block text-xs font-bold text-gray-900 mb-1.5">NUPTK</label>
                <input
                    type="text"
                    name="nuptk"
                    id="createNuptkInput"
                    placeholder="Isi NUPTK (opsional)"
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
                        <option value="DTT">Tidak Tetap</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
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

            <div>
                <label for="createJurusanInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jurusan
                </label>
                <input
                    type="text"
                    name="jurusan"
                    id="createJurusanInput"
                    list="createJurusanOptions"
                    maxlength="255"
                    placeholder="Isi jurusan homebase"
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
                <datalist id="createJurusanOptions">
                    <option value="Teknik Elektro"></option>
                    <option value="Teknik Mesin"></option>
                    <option value="Akuntansi"></option>
                    <option value="Administrasi Bisnis"></option>
                    <option value="Teknik Sipil"></option>
                </datalist>
            </div>

            <div>
                <label for="createProdiInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Program Studi
                </label>
                <input
                    type="text"
                    name="prodi"
                    id="createProdiInput"
                    list="createProdiOptions"
                    maxlength="255"
                    placeholder="Isi program studi homebase"
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
                <datalist id="createProdiOptions">
                    <option value="D4 - Teknologi Rekayasa Komputer"></option>
                    <option value="D3 - Teknik Informatika"></option>
                    <option value="Mata Kuliah Umum"></option>
                </datalist>
            </div>

            <!-- 5. Sertifikasi Dosen -->
            <div>
                <label for="createSertifikasiInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Sertifikasi Dosen (Serdos)
                </label>
                <select
                    name="sertifikasi_dosen"
                    id="createSertifikasiInput"
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none"
                >
                    <option value="">Pilih Status Serdos</option>
                    <option value="Tersertifikasi">Tersertifikasi</option>
                    <option value="Belum Tersertifikasi">Belum Tersertifikasi</option>
                </select>
            </div>

            @if($canManageJabatan)
                <div>
                    <label for="createJabatanSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                        Jabatan Akademik
                    </label>
                    <div class="relative">
                        <select name="id_jabatan" id="createJabatanSelect" class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer">
                            <option value="">Pilih Jabatan Akademik</option>
                            @foreach($jabatanList as $jabatan)
                                @php
                                    $jabatanId = $jabatan['id_jabatan'] ?? ($jabatan['id'] ?? '');
                                    $jabatanNama = $jabatan['nama_jabatan'] ?? ($jabatan['nama'] ?? '');
                                @endphp
                                @if($jabatanId !== '' && $jabatanNama !== '')
                                    <option value="{{ $jabatanId }}">{{ $jabatanNama }}</option>
                                @endif
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                            <svg class="w-5 h-5 stroke-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
                        </span>
                    </div>
                </div>
            @endif

            <div>
                <label for="createPendidikanSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Pendidikan Terakhir
                </label>
                <div class="relative">
                    <select
                        name="id_pendidikan"
                        id="createPendidikanSelect"
                        required
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                    >
                        <option value="">Pilih Pendidikan Terakhir</option>
                        @foreach($eligiblePendidikanList as $pendidikanOption)
                            <option value="{{ $pendidikanOption['id'] }}">{{ $pendidikanOption['label'] }}</option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
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
                    class="w-full py-2.5 px-6 rounded-lg bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="dosen-submit-btn w-full py-2.5 px-6 rounded-lg bg-additional hover:bg-primary text-white font-bold text-sm shadow-xs transition cursor-pointer text-center disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span class="dosen-submit-label">Simpan</span>
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
    <div class="dosen-modal-card relative bg-white rounded-[20px] p-8 max-w-110 w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[90vh] overflow-y-auto">
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

            <div>
                <label for="editNuptkInput" class="block text-xs font-bold text-gray-900 mb-1.5">NUPTK</label>
                <input
                    type="text"
                    name="nuptk"
                    id="editNuptkInput"
                    placeholder="Isi NUPTK (opsional)"
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
                        <option value="DTT">Tidak Tetap</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
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

            <!-- 5. Sertifikasi Dosen -->
            <div>
                <label for="editSertifikasiInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Sertifikasi Dosen (Serdos)
                </label>
                <select
                    name="sertifikasi_dosen"
                    id="editSertifikasiInput"
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none"
                >
                    <option value="">Pilih Status Serdos</option>
                    <option value="Tersertifikasi">Tersertifikasi</option>
                    <option value="Belum Tersertifikasi">Belum Tersertifikasi</option>
                </select>
            </div>

            @if($canManageJabatan)
                <div>
                    <label for="editJabatanSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                        Jabatan Akademik
                    </label>
                    <div class="relative">
                        <select name="id_jabatan" id="editJabatanSelect" class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer">
                            <option value="">Pilih Jabatan Akademik</option>
                            @foreach($jabatanList as $jabatan)
                                @php
                                    $jabatanId = $jabatan['id_jabatan'] ?? ($jabatan['id'] ?? '');
                                    $jabatanNama = $jabatan['nama_jabatan'] ?? ($jabatan['nama'] ?? '');
                                @endphp
                                @if($jabatanId !== '' && $jabatanNama !== '')
                                    <option value="{{ $jabatanId }}">{{ $jabatanNama }}</option>
                                @endif
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                            <svg class="w-5 h-5 stroke-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
                        </span>
                    </div>
                </div>
            @endif

            <div>
                <label for="editPendidikanSelect" class="block text-xs font-bold text-gray-900 mb-1.5">Pendidikan Terakhir</label>
                <div class="relative">
                    <select name="id_pendidikan" id="editPendidikanSelect" required class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer">
                        <option value="">Pilih Pendidikan Terakhir</option>
                        @foreach($eligiblePendidikanList as $pendidikanOption)
                            <option value="{{ $pendidikanOption['id'] }}">{{ $pendidikanOption['label'] }}</option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
                    </span>
                </div>
            </div>

            <!-- Action Buttons: Batal & Simpan -->
            <div class="grid grid-cols-2 gap-4 pt-3">
                <button
                    type="button"
                    id="closeEditModalBtn"
                    class="w-full py-2.5 px-6 rounded-lg bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="dosen-submit-btn w-full py-2.5 px-6 rounded-lg bg-additional hover:bg-primary text-white font-bold text-sm shadow-xs transition cursor-pointer text-center disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span class="dosen-submit-label">Simpan</span>
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
    <div class="dosen-modal-card relative bg-white rounded-[20px] p-8 max-w-110 w-full shadow-2xl text-left transform scale-95 transition-all duration-200 overflow-hidden">
        <!-- Title: Import Data Dosen -->
        <h3 id="importExcelModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Import Data Dosen
        </h3>

        <a
            href="{{ route('dosen.template') }}"
            class="mb-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border-0 bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-amber-500"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4m-4 7v1a2 2 0 002 2h12a2 2 0 002-2v-1" />
            </svg>
            Download Template Excel
        </a>

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
                class="w-full h-36 rounded-xl border-2 border-dashed border-gray-form hover:border-primary/60 bg-transparent flex flex-col items-center justify-center cursor-pointer transition select-none group"
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
                    class="w-full py-2.5 px-6 rounded-lg bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
            </div>

            <!-- 2. State Dengan File (Mockup State 2): Grid "Batal" & "Import" -->
            <div id="withFileButtons" class="hidden grid-cols-2 gap-4 pt-2">
                <button
                    type="button"
                    id="closeImportModalBtnDual"
                    class="w-full py-2.5 px-6 rounded-lg bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="w-full py-2.5 px-6 rounded-lg bg-additional hover:bg-primary text-white font-bold text-sm shadow-xs transition cursor-pointer text-center"
                >
                    Import
                </button>
            </div>
        </form>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function showDosenToast(message, type, title) {
            if (typeof window.showToast === 'function') {
                window.showToast(message, type, title);
            } else {
                alert(message);
            }
        }

        function clearDosenFormErrors(form) {
            form.querySelectorAll('[data-validation-error]').forEach(error => error.remove());
            form.querySelectorAll('[aria-invalid="true"]').forEach(field => field.removeAttribute('aria-invalid'));
        }

        function showDosenFormErrors(form, errors) {
            if (!errors || typeof errors !== 'object') return;

            Object.entries(errors).forEach(([name, messages]) => {
                const field = Array.from(form.elements).find(element => element.name === name);
                const message = Array.isArray(messages) ? messages[0] : messages;
                if (!field || typeof message !== 'string') return;

                const container = field.closest('.relative')?.parentElement || field.parentElement;
                if (!container) return;

                field.setAttribute('aria-invalid', 'true');
                const errorElement = document.createElement('p');
                errorElement.dataset.validationError = '';
                errorElement.className = 'mt-1 text-xs text-red-600';
                errorElement.textContent = message;
                container.append(errorElement);
            });
        }

        const savedToast = sessionStorage.getItem('dosen-success-toast');
        if (savedToast) {
            sessionStorage.removeItem('dosen-success-toast');
            try {
                const toast = JSON.parse(savedToast);
                showDosenToast(toast.message, 'success', 'Berhasil');
            } catch (error) {
                console.error('Notifikasi dosen tidak dapat dipulihkan.', error);
            }
        }

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

        const createForm = document.getElementById('createDosenForm');
        const editForm = document.getElementById('editDosenForm');

        async function submitDosenForm(form, modal, defaultLabel) {
            const submitButton = form.querySelector('.dosen-submit-btn');
            const submitLabel = submitButton?.querySelector('.dosen-submit-label');
            if (submitButton?.disabled) return;

            clearDosenFormErrors(form);
            if (submitButton) submitButton.disabled = true;
            if (submitLabel) submitLabel.textContent = 'Menyimpan...';

            try {
                const formData = new FormData(form);
                const payload = {};
                formData.forEach((value, key) => {
                    if (key !== '_token' && key !== '_method') payload[key] = value;
                });
                ['id_jabatan', 'id_pendidikan'].forEach(key => {
                    if (payload[key]) payload[key] = Number(payload[key]);
                });

                const isUpdate = form.id === 'editDosenForm';
                const response = await fetch(form.action, {
                    method: isUpdate ? 'PUT' : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                const result = await response.json().catch(() => ({}));

                if (!response.ok || result.success === false) {
                    const error = new Error(result.message || 'Data dosen gagal disimpan.');
                    error.validationErrors = result.errors || {};
                    throw error;
                }

                form.reset();
                clearDosenFormErrors(form);
                hideModal(modal);
                sessionStorage.setItem('dosen-success-toast', JSON.stringify({
                    message: result.message || (isUpdate ? 'Data dosen berhasil diperbarui.' : 'Data dosen berhasil ditambahkan.')
                }));
                window.location.reload();
            } catch (error) {
                showDosenFormErrors(form, error.validationErrors);
                showDosenToast(error.message || 'Data dosen gagal disimpan.', 'error', 'Gagal');
            } finally {
                if (submitButton) submitButton.disabled = false;
                if (submitLabel) submitLabel.textContent = defaultLabel;
            }
        }

        [createForm, editForm].forEach(form => {
            form?.addEventListener('submit', function (event) {
                event.preventDefault();
                const modal = form === createForm ? createModal : document.getElementById('editDosenModal');
                submitDosenForm(form, modal, 'Simpan');
            });
        });

        const exportButton = document.getElementById('exportDosenBtn');
        exportButton?.addEventListener('click', async function () {
            const button = this;
            const label = button.querySelector('.export-dosen-label');
            if (button.disabled) return;

            button.disabled = true;
            if (label) label.textContent = 'Mengunduh...';

            try {
                const response = await fetch(button.dataset.exportUrl, {
                    headers: { 'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }
                });
                const contentType = response.headers.get('content-type') || '';

                if (!response.ok || !contentType.includes('spreadsheetml')) {
                    const body = await response.text();
                    let message = 'Gagal mengunduh file Excel dosen.';
                    try {
                        message = JSON.parse(body).message || message;
                    } catch (error) {
                        if (response.redirected) message = 'Sesi Anda berakhir. Silakan login kembali.';
                    }
                    throw new Error(message);
                }

                const responseBlob = await response.blob();
                const objectUrl = window.URL.createObjectURL(new Blob([responseBlob], { type: contentType }));
                const downloadLink = document.createElement('a');
                downloadLink.href = objectUrl;
                downloadLink.download = 'Data_Dosen.xlsx';
                document.body.append(downloadLink);
                downloadLink.click();
                downloadLink.remove();
                window.setTimeout(() => window.URL.revokeObjectURL(objectUrl), 1000);
            } catch (error) {
                showDosenToast(error.message || 'Gagal mengunduh file Excel dosen.', 'error', 'Gagal');
            } finally {
                button.disabled = false;
                if (label) label.textContent = 'Download Excel';
            }
        });

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
        const closeEditBtn = document.getElementById('closeEditModalBtn');

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
