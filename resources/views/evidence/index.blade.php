@extends('layouts.app')

@section('browser-title', 'Evidence')
@section('title', 'Evidence')

@section('subtitle')
    @if(!$apiError && ($pagination['total'] ?? 0) > 0)
        {{ number_format((int) ($pagination['total'] ?? count($evidences)), 0, ',', '.') }} Data ditemukan
    @endif
@endsection

@section('content')
@php
    $startNumber = (int) ($pagination['from'] ?? (((int) ($pagination['current_page'] ?? 1) - 1) * (int) ($pagination['per_page'] ?? 10) + 1));
    $selectedSearch = (string) ($filters['search'] ?? '');
    $selectedStatus = (string) ($filters['status_validasi'] ?? '');
    $selectedJenis = (string) ($filters['jenis_dokumen'] ?? '');
    $hasActiveFilters = $selectedSearch !== '' || $selectedStatus !== '' || $selectedJenis !== '';
@endphp

{{-- Scoped CSS Khusus Halaman Evidence --}}
<style>
    /* Styling Notifikasi Hapus Evidence: Minimalis, Lembut, Elegan */
    #toast-container .toast-delete-item {
        max-width: 340px !important;
        background-color: #FFF1F2 !important; /* Soft rose-50 */
        border: 1px solid #FECDD3 !important; /* Thin rose-200 */
        border-bottom: 1px solid #FECDD3 !important; /* Hilangkan garis tebal 5px */
        border-radius: 12px !important;
        box-shadow: 0 4px 12px -2px rgba(225, 29, 72, 0.08) !important;
    }
    #toast-container .toast-delete-item svg {
        width: 18px !important;
        height: 18px !important;
        color: #E11D48 !important; /* Rose-600 */
    }
    #toast-container .toast-delete-item .toast-message {
        font-size: 13.5px !important;
        font-weight: 500 !important;
        color: #9F1239 !important; /* Rose-800 */
        letter-spacing: normal !important;
        line-height: 1.4 !important;
    }
    #toast-container .toast-delete-item .toast-close-btn {
        color: #FB7185 !important;
        padding: 4px !important;
    }
    #toast-container .toast-delete-item .toast-close-btn:hover {
        color: #E11D48 !important;
        background-color: rgba(254, 205, 211, 0.5) !important;
    }
</style>

<div id="evidencePageWrapper" class="w-full max-w-full overflow-x-hidden min-w-0 space-y-5 pb-8 transition-all duration-200">
    {{-- Notifikasi Error API jika ada kendala koneksi / backend --}}
    @if($apiError)
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700 shadow-xs" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>{{ $apiError }}</span>
            </div>
        </div>
    @endif

    {{-- KONDISI 1: Belum Ada Evidence (Empty State Sesuai Desain) --}}
    @if(count($evidences) === 0 && !$hasActiveFilters && !$apiError)
        <div class="flex flex-col items-center justify-center min-h-[460px] p-8 text-center bg-white rounded-2xl border border-gray-100 shadow-xs">
            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-[#f4f7fb] flex items-center justify-center mb-6 text-primary/70">
                <svg class="w-12 h-12 sm:w-14 sm:h-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>

            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight mb-2">
                Belum Ada Evidence
            </h2>
            <p class="text-sm sm:text-base text-gray-500 max-w-md mb-7 font-medium">
                Tekan tombol di bawah untuk menambah data
            </p>

            <button
                type="button"
                id="btnTambahBuktiEmpty"
                data-open-evidence-modal
                class="inline-flex items-center gap-2.5 px-6 py-3 rounded-xl bg-primary text-white text-sm sm:text-base font-bold shadow-md hover:bg-additional transition duration-150 cursor-pointer"
            >
                <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Bukti</span>
            </button>
        </div>

    {{-- KONDISI 2: Toolbar & Tabel Evidence --}}
    @else
        {{-- Toolbar Atas: Search Bar Minimalis, Filter Status, Filter Jenis Dokumen, & Tombol Tambah --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3.5">
            {{-- Form Filter & Search --}}
            <form method="GET" action="{{ route('evidence.index') }}" id="evidenceFilterForm" class="flex flex-wrap items-center gap-2.5 flex-1 min-w-0">
                {{-- Search Bar Minimalis & Modern --}}
                <div class="relative flex-1 min-w-[240px] sm:min-w-[280px] max-w-md">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="m16 16 4 4" />
                        </svg>
                    </span>
                    <input
                        id="evidenceSearch"
                        name="search"
                        type="search"
                        value="{{ $selectedSearch }}"
                        placeholder="Cari nama file atau jenis dokumen..."
                        class="h-10 w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 placeholder:text-gray-400 shadow-2xs transition focus:border-primary/60 focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                </div>

                {{-- Filter Status Validasi (Minimalis, tanpa kotak kuning) --}}
                <div class="relative w-full sm:w-auto min-w-[175px]">
                    <select
                        name="status_validasi"
                        onchange="this.form.submit()"
                        class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm font-medium text-gray-700 shadow-2xs transition hover:border-gray-400 focus:border-primary/60 focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer"
                    >
                        <option value="">Semua Status Validasi</option>
                        <option value="Menunggu Validasi" {{ $selectedStatus === 'Menunggu Validasi' ? 'selected' : '' }}>Menunggu Validasi</option>
                        <option value="Valid" {{ $selectedStatus === 'Valid' ? 'selected' : '' }}>Valid</option>
                        <option value="Ditolak" {{ $selectedStatus === 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-500">
                        <svg class="w-4 h-4 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </div>

                {{-- Filter Jenis Dokumen (Minimalis, tanpa kotak kuning) --}}
                <div class="relative w-full sm:w-auto min-w-[185px]">
                    <select
                        name="jenis_dokumen"
                        onchange="this.form.submit()"
                        class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm font-medium text-gray-700 shadow-2xs transition hover:border-gray-400 focus:border-primary/60 focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer"
                    >
                        <option value="">Semua Jenis Dokumen</option>
                        @foreach($jenisDokumenOptions as $jenisDokumenOption)
                            <option value="{{ $jenisDokumenOption }}" {{ $selectedJenis === $jenisDokumenOption ? 'selected' : '' }}>{{ $jenisDokumenOption }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-500">
                        <svg class="w-4 h-4 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </div>

                {{-- Tombol Reset Filter --}}
                @if($hasActiveFilters)
                    <a
                        href="{{ route('evidence.index') }}"
                        class="inline-flex h-10 items-center gap-1.5 px-3 text-xs font-semibold text-rose-600 hover:text-rose-800 transition"
                        title="Reset Filter"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Reset</span>
                    </a>
                @endif
            </form>

            {{-- Tombol Tambah Bukti --}}
            <button
                type="button"
                id="btnTambahBuktiTable"
                data-open-evidence-modal
                class="inline-flex h-10 items-center justify-center gap-2 px-5 rounded-lg bg-primary text-white text-sm font-bold shadow-xs hover:bg-additional transition duration-150 cursor-pointer shrink-0 self-start sm:self-auto"
            >
                <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Bukti</span>
            </button>
        </div>

        {{-- Tabel Evidence Responsif (Scroll Horizontal Mandiri, Anti-Terpotong) --}}
        <div class="w-full max-w-full overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-xs">
            <table class="w-full text-left border-collapse table-fixed min-w-[780px]">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-900 font-semibold text-sm bg-gray-50/50">
                        <th scope="col" class="w-14 min-w-[56px] py-3.5 px-3 text-left">No</th>
                        <th scope="col" class="w-72 min-w-[220px] py-3.5 px-3 text-left">Nama File</th>
                        <th scope="col" class="w-44 min-w-[140px] py-3.5 px-3 text-left">Jenis Dokumen</th>
                        <th scope="col" class="w-36 min-w-[125px] py-3.5 px-3 text-left">Tanggal Upload</th>
                        <th scope="col" class="w-44 min-w-[155px] py-3.5 px-3 text-left">Status Validasi</th>
                        <th scope="col" class="w-36 min-w-[130px] py-3.5 px-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-sm">
                    @forelse($evidences as $index => $item)
                        @php
                            $evidenceId = $item['id_evidence'] ?? $item['id'] ?? null;
                            $namaFile = $item['_display_file_name'] ?? 'Dokumen Tanpa Judul';
                            $driveUrl = $item['_drive_url'] ?? '';
                            $jenisDokumen = $item['jenis_dokumen'] ?? '-';
                            $tanggalUpload = $item['_formatted_date'] ?? '-';
                            $rawDateInput = $item['_raw_date_input'] ?? '';
                            $statusRaw = trim((string) ($item['status_validasi'] ?? 'Menunggu Validasi'));
                            $statusNormalized = strtolower($statusRaw);

                            $badgeClass = match ($statusNormalized) {
                                'valid' => 'bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0]',
                                'ditolak' => 'bg-[#FEE2E2] text-[#991B1B] border border-[#FECACA]',
                                default => 'bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A]',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            {{-- 1. No --}}
                            <td class="w-14 min-w-[56px] py-3.5 px-3 text-left font-medium text-gray-500">
                                {{ $startNumber + $index }}
                            </td>

                            {{-- 2. Nama File --}}
                            <td class="w-72 min-w-[220px] py-3.5 px-3 text-left">
                                <div class="truncate max-w-full" title="{{ $namaFile }}">
                                    @if($driveUrl)
                                        <a
                                            href="{{ $driveUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="font-semibold text-gray-900 hover:text-primary transition"
                                        >
                                            {{ $namaFile }}
                                        </a>
                                    @else
                                        <span class="font-semibold text-gray-900">
                                            {{ $namaFile }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- 3. Jenis Dokumen --}}
                            <td class="w-44 min-w-[140px] py-3.5 px-3 text-left">
                                <span class="inline-flex max-w-full items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-800 truncate">
                                    {{ $jenisDokumen !== '' ? $jenisDokumen : '-' }}
                                </span>
                            </td>

                            {{-- 4. Tanggal Upload --}}
                            <td class="w-36 min-w-[125px] py-3.5 px-3 text-left text-gray-700 whitespace-nowrap">
                                {{ $tanggalUpload }}
                            </td>

                            {{-- 5. Status Validasi --}}
                            <td class="w-44 min-w-[155px] py-3.5 px-3 text-left">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $badgeClass }}">
                                    {{ $statusRaw }}
                                </span>
                            </td>

                            {{-- 6. Action: Judul & Kumpulan Tombol Tepat Rata Tengah --}}
                            <td class="w-36 min-w-[130px] py-3.5 px-3 text-center align-middle">
                                <div class="inline-flex items-center justify-center gap-2 w-full">
                                    @if($evidenceId)
                                        {{-- 1. Lihat Detail --}}
                                        <a
                                            href="{{ route('evidence.show', $evidenceId) }}"
                                            class="p-1.5 text-primary hover:text-additional hover:bg-sky-50 rounded-lg transition"
                                            title="Lihat Detail Evidence"
                                            aria-label="Lihat Detail Evidence"
                                        >
                                            <x-icon.eye class="w-4 h-4" />
                                        </a>

                                        {{-- 2. Satu Tombol Edit Gabungan (Metadata & Tautan) --}}
                                        <button
                                            type="button"
                                            data-open-unified-edit
                                            data-evidence-id="{{ $evidenceId }}"
                                            data-evidence-name="{{ $namaFile }}"
                                            data-evidence-type="{{ $jenisDokumen }}"
                                            data-evidence-date="{{ $rawDateInput }}"
                                            data-evidence-status="{{ $statusRaw }}"
                                            data-evidence-url="{{ $driveUrl }}"
                                            data-evidence-action="{{ route('evidence.update', $evidenceId) }}"
                                            class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition cursor-pointer"
                                            title="Edit Evidence (Metadata & Tautan)"
                                            aria-label="Edit Evidence"
                                        >
                                            <x-icon.edit class="w-4 h-4" />
                                        </button>

                                        {{-- 3. Hapus --}}
                                        <form method="POST" action="{{ route('evidence.destroy', $evidenceId) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data evidence ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                                                title="Hapus Evidence"
                                                aria-label="Hapus Evidence"
                                            >
                                                <x-icon.delete class="w-4 h-4" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">
                                <p class="text-base font-semibold text-gray-900">Tidak ada data evidence ditemukan</p>
                                <p class="mt-1 text-xs text-gray-500">Coba ubah kata kunci atau kriteria filter di atas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginasi --}}
        <x-pagination :paginator="$pagination" />
    @endif
</div>

{{-- ========================================================================= --}}
{{-- MODAL 1: FORM TAMBAH EVIDENCE (STATUS DAPAT DIPILIH)                      --}}
{{-- ========================================================================= --}}
<div id="evidenceUploadModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="evidenceUploadTitle">
    <div class="flex max-h-[90vh] w-full max-w-[500px] flex-col overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <h2 id="evidenceUploadTitle" class="text-xl font-bold text-gray-900">Tambahkan Evidence</h2>
            <button type="button" data-close-evidence-modal class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition" aria-label="Tutup formulir">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="evidenceCreateForm" method="POST" action="{{ route('evidence.store') }}" class="flex flex-col gap-4">
            @csrf

            {{-- 1. Nama File --}}
            <div>
                <label for="evidenceNamaFile" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    Nama File <span class="text-rose-500">*</span>
                </label>
                <input
                    id="evidenceNamaFile"
                    name="nama_file"
                    type="text"
                    value="{{ old('nama_file') }}"
                    placeholder="Contoh: Proposal Penelitian Dosen 2026"
                    required
                    maxlength="255"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 placeholder:text-gray-400 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
                @error('nama_file', 'evidenceUpload')
                    <p class="mt-1 text-xs font-semibold text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- 2. Jenis Dokumen Evidence --}}
            <div>
                <label for="evidenceJenisDokumen" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    Jenis Dokumen Evidence <span class="text-rose-500">*</span>
                </label>
                <select
                    id="evidenceJenisDokumen"
                    name="jenis_dokumen"
                    required
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer"
                >
                    <option value="" disabled {{ old('jenis_dokumen') ? '' : 'selected' }}>Pilih Jenis Dokumen</option>
                    @foreach($jenisDokumenOptions as $jenisDokumenOption)
                        <option value="{{ $jenisDokumenOption }}" {{ old('jenis_dokumen') === $jenisDokumenOption ? 'selected' : '' }}>{{ $jenisDokumenOption }}</option>
                    @endforeach
                </select>
                @error('jenis_dokumen', 'evidenceUpload')
                    <p class="mt-1 text-xs font-semibold text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- 3. Tanggal Upload --}}
            <div>
                <label for="evidenceTanggalUpload" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    Tanggal Upload
                </label>
                <input
                    id="evidenceTanggalUpload"
                    name="tanggal_upload"
                    type="date"
                    value="{{ old('tanggal_upload', date('Y-m-d')) }}"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
                @error('tanggal_upload', 'evidenceUpload')
                    <p class="mt-1 text-xs font-semibold text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- 4. Status Validasi (Dapat Dipilih Langsung) --}}
            <div>
                <label for="evidenceStatusValidasi" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    Status Validasi
                </label>
                <select
                    id="evidenceStatusValidasi"
                    name="status_validasi"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer"
                >
                    <option value="Menunggu Validasi" {{ old('status_validasi', 'Menunggu Validasi') === 'Menunggu Validasi' ? 'selected' : '' }}>Menunggu Validasi (Pemeriksaan Ulang)</option>
                    <option value="Valid" {{ old('status_validasi') === 'Valid' ? 'selected' : '' }}>Valid (Telah Memenuhi Syarat)</option>
                    <option value="Ditolak" {{ old('status_validasi') === 'Ditolak' ? 'selected' : '' }}>Ditolak (Perlu Perbaikan)</option>
                </select>
                @error('status_validasi', 'evidenceUpload')
                    <p class="mt-1 text-xs font-semibold text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- 5. Tautan Evidence (Universal URL) --}}
            <div>
                <label for="evidenceLink" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    Tautan Evidence <span class="text-rose-500">*</span>
                </label>
                <input
                    id="evidenceLink"
                    name="url_drive"
                    type="url"
                    value="{{ old('url_drive') }}"
                    placeholder="https://contoh.com/dokumen"
                    required
                    maxlength="2048"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 placeholder:text-gray-400 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
                <p class="mt-1 text-[11px] text-gray-500">Mendukung tautan Google Drive, OneDrive, Dropbox, GitHub, dokumen website, dll.</p>
                @error('url_drive', 'evidenceUpload')
                    <p class="mt-1 text-xs font-semibold text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- Peringatan Duplikasi URL jika 409 --}}
            @if(session('duplicateUrlWarning'))
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900">
                    <label class="flex items-start gap-2 cursor-pointer font-medium">
                        <input type="checkbox" name="konfirmasi_lanjut" value="1" class="mt-0.5 rounded border-amber-400 text-primary focus:ring-primary">
                        <span>Tautan ini sudah pernah digunakan. Centang kotak ini jika Anda tetap ingin menyimpan salinan baru.</span>
                    </label>
                </div>
            @endif

            {{-- Aksi Batal & Simpan --}}
            <div class="mt-3 flex justify-end gap-2.5 pt-2 border-t border-gray-100">
                <button
                    type="button"
                    data-close-evidence-modal
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    id="btnSubmitCreateEvidence"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2 text-sm font-bold text-white hover:bg-additional transition cursor-pointer disabled:opacity-50"
                >
                    <span id="btnSubmitCreateSpinner" class="hidden">
                        <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </span>
                    <span id="btnSubmitCreateText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 2: SATU FORM EDIT GABUNGAN (METADATA & TAUTAN)                      --}}
{{-- ========================================================================= --}}
<div id="evidenceUnifiedEditModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="unifiedEditTitle">
    <div class="flex max-h-[90vh] w-full max-w-[520px] flex-col overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <div>
                <h2 id="unifiedEditTitle" class="text-xl font-bold text-gray-900">Edit Data Evidence</h2>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui metadata dokumen dan tautan bukti dalam satu formulir.</p>
            </div>
            <button type="button" data-close-unified-edit class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition" aria-label="Tutup form edit">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="evidenceUnifiedEditForm" method="POST" action="#" class="flex flex-col gap-4">
            @csrf
            @method('PUT')

            {{-- Hidden input untuk mendeteksi perubahan tautan --}}
            <input type="hidden" id="editUrlLama" name="url_drive_lama" value="">

            {{-- 1. Nama File / Judul Dokumen --}}
            <div>
                <label for="unifiedEditNamaFile" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    Nama File <span class="text-rose-500">*</span>
                </label>
                <input
                    id="unifiedEditNamaFile"
                    name="nama_file"
                    type="text"
                    required
                    maxlength="255"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
            </div>

            {{-- 2. Jenis Dokumen Evidence --}}
            <div>
                <label for="unifiedEditJenisDokumen" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    Jenis Dokumen Evidence <span class="text-rose-500">*</span>
                </label>
                <select
                    id="unifiedEditJenisDokumen"
                    name="jenis_dokumen"
                    required
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer"
                >
                    @foreach($jenisDokumenOptions as $jenisDokumenOption)
                        <option value="{{ $jenisDokumenOption }}">{{ $jenisDokumenOption }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 3. Tanggal Upload --}}
            <div>
                <label for="unifiedEditTanggalUpload" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    Tanggal Upload
                </label>
                <input
                    id="unifiedEditTanggalUpload"
                    name="tanggal_upload"
                    type="date"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
            </div>

            {{-- 4. Status Validasi --}}
            <div>
                <label for="unifiedEditStatusValidasi" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    Status Validasi
                </label>
                <select
                    id="unifiedEditStatusValidasi"
                    name="status_validasi"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer"
                >
                    <option value="Menunggu Validasi">Menunggu Validasi</option>
                    <option value="Valid">Valid</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
            </div>

            {{-- 5. Tautan Evidence --}}
            <div>
                <label for="unifiedEditUrlDrive" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    Tautan Evidence <span class="text-rose-500">*</span>
                </label>
                <input
                    id="unifiedEditUrlDrive"
                    name="url_drive"
                    type="url"
                    required
                    maxlength="2048"
                    placeholder="https://contoh.com/dokumen"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 placeholder:text-gray-400 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
            </div>

            {{-- 6. Alasan Perubahan Tautan (Tampil jika tautan diedit) --}}
            <div id="unifiedEditAlasanContainer" class="hidden transition-all duration-200">
                <label for="unifiedEditAlasan" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    Alasan Perubahan Tautan <span class="text-rose-500">* (wajib jika tautan diubah)</span>
                </label>
                <textarea
                    id="unifiedEditAlasan"
                    name="alasan_perubahan"
                    rows="3"
                    placeholder="Jelaskan alasan pembaruan tautan dokumen ini..."
                    class="w-full rounded-lg border border-amber-300 bg-amber-50/50 p-3 text-sm text-gray-800 placeholder:text-gray-400 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                ></textarea>
                <p class="mt-1 text-[11px] text-gray-500">Tautan lama akan diarsipkan otomatis ke dalam riwayat perubahan dokumen.</p>
            </div>

            {{-- Tombol Batal & Simpan --}}
            <div class="mt-3 flex justify-end gap-2.5 pt-2 border-t border-gray-100">
                <button
                    type="button"
                    data-close-unified-edit
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    id="btnSubmitUnifiedEdit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2 text-sm font-bold text-white hover:bg-additional transition cursor-pointer disabled:opacity-50"
                >
                    <span id="btnSubmitUnifiedSpinner" class="hidden">
                        <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </span>
                    <span id="btnSubmitUnifiedText">Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (() => {
        // Layout Responsif: Pastikan halaman Evidence tidak tertutup sidebar fixed
        const adjustEvidenceLayout = () => {
            const sidebar = document.getElementById('sidebar');
            const wrapper = document.getElementById('evidencePageWrapper');
            if (!sidebar || !wrapper) return;

            const sidebarStyle = window.getComputedStyle(sidebar);
            if (sidebarStyle.position === 'fixed' && !sidebar.classList.contains('-translate-x-full')) {
                const rect = sidebar.getBoundingClientRect();
                if (rect.right > 0) {
                    wrapper.style.paddingLeft = (rect.right + 16) + 'px';
                    return;
                }
            }
            wrapper.style.paddingLeft = '';
        };

        window.addEventListener('resize', adjustEvidenceLayout);
        window.addEventListener('load', adjustEvidenceLayout);
        setTimeout(adjustEvidenceLayout, 150);

        // Modal Upload Elements
        const uploadModal = document.getElementById('evidenceUploadModal');
        const uploadForm = document.getElementById('evidenceCreateForm');
        const submitBtn = document.getElementById('btnSubmitCreateEvidence');
        const submitSpinner = document.getElementById('btnSubmitCreateSpinner');
        const submitText = document.getElementById('btnSubmitCreateText');

        // Modal Unified Edit Elements
        const unifiedEditModal = document.getElementById('evidenceUnifiedEditModal');
        const unifiedEditForm = document.getElementById('evidenceUnifiedEditForm');
        const editUrlInput = document.getElementById('unifiedEditUrlDrive');
        const editUrlLamaInput = document.getElementById('editUrlLama');
        const alasanContainer = document.getElementById('unifiedEditAlasanContainer');
        const alasanTextarea = document.getElementById('unifiedEditAlasan');
        const submitUnifiedBtn = document.getElementById('btnSubmitUnifiedEdit');
        const submitUnifiedSpinner = document.getElementById('btnSubmitUnifiedSpinner');
        const submitUnifiedText = document.getElementById('btnSubmitUnifiedText');

        // Handlers: Modal Upload
        const openUploadModal = () => {
            if (!uploadModal) return;
            uploadModal.classList.remove('hidden');
            uploadModal.classList.add('flex');
            uploadModal.setAttribute('aria-hidden', 'false');
            uploadModal.querySelector('input[name="nama_file"]')?.focus();
        };

        const closeUploadModal = () => {
            if (!uploadModal) return;
            uploadModal.classList.add('hidden');
            uploadModal.classList.remove('flex');
            uploadModal.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-open-evidence-modal]').forEach(btn => btn.addEventListener('click', openUploadModal));
        document.querySelectorAll('[data-close-evidence-modal]').forEach(btn => btn.addEventListener('click', closeUploadModal));

        if (uploadForm) {
            uploadForm.addEventListener('submit', () => {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitSpinner?.classList.remove('hidden');
                    if (submitText) submitText.textContent = 'Menyimpan...';
                }
            });
        }

        // Handlers: Satu Modal Edit Gabungan
        const checkUrlChanged = () => {
            const currentUrl = (editUrlInput?.value || '').trim();
            const originalUrl = (editUrlLamaInput?.value || '').trim();

            if (currentUrl !== '' && originalUrl !== '' && currentUrl !== originalUrl) {
                alasanContainer?.classList.remove('hidden');
                alasanTextarea?.setAttribute('required', 'required');
            } else {
                alasanContainer?.classList.add('hidden');
                alasanTextarea?.removeAttribute('required');
            }
        };

        if (editUrlInput) {
            editUrlInput.addEventListener('input', checkUrlChanged);
        }

        const openUnifiedEditModal = (btn) => {
            if (!unifiedEditModal || !unifiedEditForm) return;

            unifiedEditForm.action = btn.dataset.evidenceAction;
            document.getElementById('unifiedEditNamaFile').value = btn.dataset.evidenceName || '';
            document.getElementById('unifiedEditJenisDokumen').value = btn.dataset.evidenceType || 'Proposal';
            document.getElementById('unifiedEditTanggalUpload').value = btn.dataset.evidenceDate || '';
            document.getElementById('unifiedEditStatusValidasi').value = btn.dataset.evidenceStatus || 'Menunggu Validasi';

            const activeUrl = btn.dataset.evidenceUrl || '';
            if (editUrlInput) editUrlInput.value = activeUrl;
            if (editUrlLamaInput) editUrlLamaInput.value = activeUrl;
            if (alasanTextarea) alasanTextarea.value = '';

            checkUrlChanged();

            unifiedEditModal.classList.remove('hidden');
            unifiedEditModal.classList.add('flex');
            unifiedEditModal.setAttribute('aria-hidden', 'false');
        };

        const closeUnifiedEditModal = () => {
            if (!unifiedEditModal) return;
            unifiedEditModal.classList.add('hidden');
            unifiedEditModal.classList.remove('flex');
            unifiedEditModal.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-open-unified-edit]').forEach(btn => {
            btn.addEventListener('click', () => openUnifiedEditModal(btn));
        });
        document.querySelectorAll('[data-close-unified-edit]').forEach(btn => {
            btn.addEventListener('click', closeUnifiedEditModal);
        });

        if (unifiedEditForm) {
            unifiedEditForm.addEventListener('submit', () => {
                if (submitUnifiedBtn) {
                    submitUnifiedBtn.disabled = true;
                    submitUnifiedSpinner?.classList.remove('hidden');
                    if (submitUnifiedText) submitUnifiedText.textContent = 'Menyimpan...';
                }
            });
        }

        // Close on backdrop click & ESC
        [uploadModal, unifiedEditModal].forEach(m => {
            if (!m) return;
            m.addEventListener('click', (e) => {
                if (e.target === m) {
                    m.classList.add('hidden');
                    m.classList.remove('flex');
                    m.setAttribute('aria-hidden', 'true');
                }
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeUploadModal();
                closeUnifiedEditModal();
            }
        });

        // Search debounce
        const searchInput = document.getElementById('evidenceSearch');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', () => {
                window.clearTimeout(searchTimeout);
                searchTimeout = window.setTimeout(() => searchInput.form.requestSubmit(), 400);
            });
        }

        // Auto-reopen modal jika validasi error kembali
        @if(session('openEvidenceModal'))
            openUploadModal();
        @endif
    })();
</script>
@endsection
