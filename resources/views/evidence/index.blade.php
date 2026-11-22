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

<div class="space-y-5 pb-8">
    {{-- Notifikasi Error API jika gagal terhubung --}}
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

    {{-- KONDISI 1: Belum Ada Evidence (Empty State Murni Sesuai Figma) --}}
    @if(count($evidences) === 0 && !$hasActiveFilters && !$apiError)
        <div class="flex flex-col items-center justify-center min-h-[460px] p-8 text-center bg-white rounded-2xl border border-gray-100 shadow-xs">
            {{-- Ikon Ilustrasi Dokumen Kosong --}}
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

    {{-- KONDISI 2: Tabel Evidence (Data Ada atau Sedang Memfilter) --}}
    @else
        {{-- Toolbar Atas: Filter & Tombol Tambah --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            {{-- Form Filter yang Didukung API Backend --}}
            <form method="GET" action="{{ route('evidence.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="relative min-w-[220px]">
                    <label for="evidenceSearch" class="sr-only">Cari nama file</label>
                    <input id="evidenceSearch" name="search" type="search" value="{{ $selectedSearch }}" placeholder="Cari nama file..." class="h-11 w-full rounded-[8px] border-2 border-primary bg-white-form py-2.5 pl-3.5 pr-11 text-sm font-semibold text-gray-900 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="m16 16 4 4" />
                    </svg>
                </div>

                {{-- Filter Status Validasi (Sesuai parameter status_validasi di API) --}}
                <div class="relative min-w-[200px]">
                    <x-input.select name="status_validasi" onchange="this.form.submit()" class="text-xs sm:text-sm">
                        <option value="">Semua Status Validasi</option>
                        <option value="Valid" {{ $selectedStatus === 'Valid' ? 'selected' : '' }}>Valid</option>
                        <option value="Menunggu Validasi" {{ $selectedStatus === 'Menunggu Validasi' ? 'selected' : '' }}>Menunggu Validasi</option>
                        <option value="Ditolak" {{ $selectedStatus === 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </x-input.select>
                </div>

                {{-- Filter Jenis Dokumen (Sesuai parameter jenis_dokumen di API) --}}
                <div class="relative min-w-[200px]">
                    <x-input.select name="jenis_dokumen" onchange="this.form.submit()" class="text-xs sm:text-sm">
                        <option value="">Semua Jenis Dokumen</option>
                        @foreach($jenisDokumenOptions as $jenisDokumenOption)
                            <option value="{{ $jenisDokumenOption }}" {{ $selectedJenis === $jenisDokumenOption ? 'selected' : '' }}>{{ $jenisDokumenOption }}</option>
                        @endforeach
                    </x-input.select>
                </div>

                @if($hasActiveFilters)
                    <a
                        href="{{ route('evidence.index') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-800 transition"
                        title="Reset Filter"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Reset</span>
                    </a>
                @endif
            </form>

            {{-- Tombol Tambah Bukti (Sisi Kanan Atas Sesuai Figma) --}}
            <button
                type="button"
                id="btnTambahBuktiTable"
                data-open-evidence-modal
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition duration-150 cursor-pointer self-start sm:self-auto shrink-0"
            >
                <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Bukti</span>
            </button>
        </div>

        {{-- Tabel Evidence --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-xs overflow-hidden">
            <x-table class="!min-w-0 w-full">
                <x-slot:header>
                    <x-table.th class="w-[5%] !px-2">No</x-table.th>
                    <x-table.th class="w-[27%] !px-2">Nama File</x-table.th>
                    <x-table.th class="w-[17%] !px-2">Jenis Dokumen</x-table.th>
                    <x-table.th class="w-[15%] !px-2">Tanggal Upload</x-table.th>
                    <x-table.th class="w-[20%] !px-2">Status Validasi</x-table.th>
                    <x-table.th class="w-[16%] !px-2">Action</x-table.th>
                </x-slot:header>

                @forelse($evidences as $index => $item)
                    @php
                        $evidenceId = $item['id'] ?? $item['id_evidence'] ?? null;
                        $namaFile = $item['_display_file_name'] ?? 'Nama file tidak tersedia';
                        $editNamaFile = $namaFile === 'Nama file tidak tersedia' ? '' : $namaFile;
                        $driveUrl = $item['_drive_url'] ?? null;
                        $jenisDokumen = $item['jenis_dokumen'] ?? '';
                        $rawDate = $item['tanggal_upload'] ?? null;
                        $tanggalUpload = '-';
                        $editDate = '';
                        if ($rawDate) {
                            try {
                                $parsedDate = \Carbon\Carbon::parse($rawDate);
                                $tanggalUpload = $parsedDate->format('d/m/Y');
                                $editDate = $parsedDate->format('Y-m-d');
                            } catch (\Throwable) {
                                $tanggalUpload = (string) $rawDate;
                            }
                        }

                        $statusRaw = trim((string) ($item['status_validasi'] ?? ''));
                        $statusNormalized = strtolower($statusRaw);
                        $badgeClass = match ($statusNormalized) {
                            'valid' => 'bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0]',
                            'ditolak' => 'bg-[#FEE2E2] text-[#991B1B] border border-[#FECACA]',
                            'menunggu validasi' => 'bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A]',
                            default => 'bg-gray-100 text-gray-700 border border-gray-200',
                        };
                        $statusLabel = $statusRaw !== '' ? $statusRaw : '-';
                    @endphp
                    <x-table.row>
                        <x-table.cell class="!px-2 font-medium text-gray-500">
                            {{ $startNumber + $index }}
                        </x-table.cell>

                        <x-table.cell class="!min-w-0 !px-2">
                            <span class="block truncate font-semibold text-gray-900" title="{{ $namaFile }}">
                                {{ $namaFile }}
                            </span>
                        </x-table.cell>

                        <x-table.cell class="!px-2">
                            <span class="inline-flex max-w-full items-center break-words rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">
                                {{ $jenisDokumen !== '' ? $jenisDokumen : '-' }}
                            </span>
                        </x-table.cell>

                        <x-table.cell class="!px-2 text-gray-700 text-sm whitespace-nowrap">
                            {{ $tanggalUpload }}
                        </x-table.cell>

                        <x-table.cell class="!px-2">
                            <span class="inline-flex max-w-full items-center rounded-full px-2 py-1 text-xs font-bold {{ $badgeClass }}">
                                {{ $statusLabel }}
                            </span>
                        </x-table.cell>

                        <x-table.cell class="!px-2">
                            <div class="inline-flex items-center justify-start gap-2">
                                @if($evidenceId)
                                    <button
                                        type="button"
                                        data-edit-evidence
                                        data-evidence-id="{{ $evidenceId }}"
                                        data-evidence-url="{{ $driveUrl }}"
                                        data-evidence-name="{{ $editNamaFile }}"
                                        data-evidence-type="{{ $jenisDokumen }}"
                                        data-evidence-date="{{ $editDate }}"
                                        data-evidence-status="{{ $statusRaw }}"
                                        data-evidence-action="{{ route('evidence.update-link', $evidenceId) }}"
                                        class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition"
                                        title="Edit Evidence"
                                        aria-label="Edit Evidence"
                                    >
                                        <x-icon.edit class="w-4 h-4" />
                                    </button>

                                    <form method="POST" action="{{ route('evidence.destroy', $evidenceId) }}" onsubmit="return confirm('Hapus evidence ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition" title="Hapus Evidence" aria-label="Hapus Evidence">
                                            <x-icon.delete class="w-4 h-4" />
                                        </button>
                                    </form>

                                    <a href="{{ route('evidence.show', $evidenceId) }}" class="p-1.5 text-primary hover:text-additional hover:bg-sky-50 rounded-lg transition" title="Detail Evidence" aria-label="Detail Evidence">
                                        <x-icon.eye class="w-4 h-4" />
                                    </a>
                                @endif
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.empty colspan="6" message="Tidak ada data evidence ditemukan" submessage="Coba ubah kriteria filter di atas." />
                @endforelse
            </x-table>
        </div>

        {{-- Paginasi Menggunakan Komponen Standar Project --}}
        <x-pagination :paginator="$pagination" />
    @endif
</div>

<div id="evidenceUploadModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="evidenceUploadTitle">
    <div class="flex min-h-[631px] w-full max-w-[443px] flex-col rounded-xl border border-gray-200 bg-white p-0 shadow-xl">
        <div class="flex items-start justify-between gap-4 p-5 pb-0">
            <div>
                <h2 id="evidenceUploadTitle" class="text-xl font-bold text-gray-900">Tambahkan Evidence</h2>
            </div>
            <button type="button" data-close-evidence-modal class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800" aria-label="Tutup form">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('evidence.store') }}" class="flex flex-1 flex-col gap-[11px] p-5 pt-0">
            @csrf
            <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-900">
                Kontrak API saat ini hanya mendukung tautan, jenis dokumen, status, dan ID kegiatan. Nama File tampilan serta Tanggal Upload dari form belum dapat disimpan.
            </p>

            <div>
                <label for="evidenceNamaFile" class="mb-1.5 block text-sm font-semibold text-gray-900">Nama File</label>
                <input id="evidenceNamaFile" name="nama_file_display" type="text" value="{{ old('nama_file_display') }}" placeholder="Isi Nama File" required maxlength="255" class="h-[50px] w-full rounded-[10px] border-0 bg-[#d9d9d9] px-4 text-base text-gray-800 placeholder:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary/30">
                @error('nama_file_display', 'evidenceUpload')
                    <p class="mt-1 text-sm text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="evidenceJenisDokumen" class="mb-1.5 block text-sm font-semibold text-gray-900">Jenis Dokumen Evidence</label>
                <select id="evidenceJenisDokumen" name="jenis_dokumen" required class="h-[50px] w-full rounded-[10px] border-0 bg-[#d9d9d9] px-4 text-base text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <option value="" disabled {{ old('jenis_dokumen') ? '' : 'selected' }}>Pilih Jenis Evidence</option>
                    @foreach($jenisDokumenOptions as $jenisDokumenOption)
                        <option value="{{ $jenisDokumenOption }}" {{ old('jenis_dokumen') === $jenisDokumenOption ? 'selected' : '' }}>{{ $jenisDokumenOption }}</option>
                    @endforeach
                </select>
                @error('jenis_dokumen', 'evidenceUpload')
                    <p class="mt-1 text-sm text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="evidenceTanggalUpload" class="mb-1.5 block text-sm font-semibold text-gray-900">Tanggal Upload</label>
                <input id="evidenceTanggalUpload" name="tanggal_upload" type="date" value="{{ old('tanggal_upload') }}" placeholder="Isi Tanggal Upload" required class="h-[50px] w-full rounded-[10px] border-0 bg-[#d9d9d9] px-4 text-base text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/30">
                @error('tanggal_upload', 'evidenceUpload')
                    <p class="mt-1 text-sm text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="evidenceStatusValidasi" class="mb-1.5 block text-sm font-semibold text-gray-900">Status Validasi</label>
                <select id="evidenceStatusValidasi" name="status_validasi" required class="h-[50px] w-full rounded-[10px] border-0 bg-[#d9d9d9] px-4 text-base text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <option value="" disabled {{ old('status_validasi') ? '' : 'selected' }}>Pilih Status Validasi</option>
                    @foreach(['Valid', 'Menunggu Validasi', 'Ditolak'] as $statusValidasiOption)
                        <option value="{{ $statusValidasiOption }}" {{ old('status_validasi') === $statusValidasiOption ? 'selected' : '' }}>{{ $statusValidasiOption }}</option>
                    @endforeach
                </select>
                @error('status_validasi', 'evidenceUpload')
                    <p class="mt-1 text-sm text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="evidenceLink" class="mb-1.5 block text-sm font-semibold text-gray-700">Tautan Evidence (Unggah Bukti, tautan G.Drive - PDF Maks 10 mb)</label>
                <input id="evidenceLink" name="nama_file" type="text" value="{{ old('nama_file') }}" placeholder="Tempel tautan Google Drive" required class="h-[50px] w-full rounded-[10px] border-0 bg-[#d9d9d9] px-4 text-base text-gray-800 placeholder:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary/30">
                @error('nama_file', 'evidenceUpload')
                    <p class="mt-1 text-sm text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-auto flex justify-end gap-3 pt-2">
                <button type="button" data-close-evidence-modal class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-additional">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="evidenceEditModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="evidenceEditTitle">
    <div class="max-h-[90vh] w-full max-w-[588px] overflow-y-auto rounded-[20px] border border-gray-200 bg-white p-6 shadow-xl sm:p-8">
        <div class="mb-5 flex items-center justify-between gap-4">
            <span class="w-8" aria-hidden="true"></span>
            <h2 id="evidenceEditTitle" class="text-center text-2xl font-bold text-gray-900">Perbarui Evidence</h2>
            <button type="button" data-close-evidence-edit class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800" aria-label="Tutup form edit">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="evidenceEditForm" method="POST" action="#" class="space-y-4">
            @csrf
            <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-900">
                API saat ini hanya mendukung penggantian tautan. Metadata lain ditampilkan sebagai data lama dan belum dapat diubah sampai backend menyediakan endpoint update.
            </p>
            <div>
                <label for="evidenceEditName" class="mb-1.5 block text-sm font-semibold text-gray-900">Nama File</label>
                <input id="evidenceEditName" type="text" disabled placeholder="Tidak tersedia dari API" class="h-[50px] w-full rounded-[10px] border-0 bg-[#d9d9d9] px-4 text-base text-gray-800 disabled:cursor-not-allowed disabled:opacity-80">
            </div>
            <div>
                <label for="evidenceEditType" class="mb-1.5 block text-sm font-semibold text-gray-900">Jenis Dokumen Evidence</label>
                <select id="evidenceEditType" disabled class="h-[50px] w-full rounded-[10px] border-0 bg-[#d9d9d9] px-4 text-base text-gray-800 disabled:cursor-not-allowed disabled:opacity-80">
                    <option value="">Tidak tersedia dari API</option>
                    @foreach($jenisDokumenOptions as $jenisDokumenOption)
                        <option value="{{ $jenisDokumenOption }}">{{ $jenisDokumenOption }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="evidenceEditDate" class="mb-1.5 block text-sm font-semibold text-gray-900">Tanggal Upload</label>
                <input id="evidenceEditDate" type="date" disabled class="h-[50px] w-full rounded-[10px] border-0 bg-[#d9d9d9] px-4 text-base text-gray-800 disabled:cursor-not-allowed disabled:opacity-80">
            </div>
            <div>
                <label for="evidenceEditStatus" class="mb-1.5 block text-sm font-semibold text-gray-900">Status Validasi</label>
                <input id="evidenceEditStatus" type="text" disabled placeholder="Tidak tersedia dari API" class="h-[50px] w-full rounded-[10px] border-0 bg-[#d9d9d9] px-4 text-base text-gray-800 disabled:cursor-not-allowed disabled:opacity-80">
            </div>
            <div>
                <label for="evidenceEditUrl" class="mb-1.5 block text-sm font-semibold text-gray-900">Tautan Evidence</label>
                <input id="evidenceEditUrl" name="url_drive_baru" type="url" value="{{ old('url_drive_baru') }}" placeholder="Tempel tautan Google Drive" required class="h-[50px] w-full rounded-[10px] border-0 bg-[#d9d9d9] px-4 text-base text-gray-800 placeholder:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary/30">
                @error('url_drive_baru', 'evidenceEdit')
                    <p class="mt-1 text-sm text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-4 pt-3">
                <button type="button" data-close-evidence-edit class="h-[52px] flex-1 rounded-lg bg-[#d9d9d9] px-4 text-base font-semibold text-gray-900 hover:bg-gray-300">Batal</button>
                <button type="submit" class="h-[52px] flex-1 rounded-lg bg-[#005A91] px-4 text-base font-semibold text-white hover:bg-[#004674]">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    (() => {
        const modal = document.getElementById('evidenceUploadModal');
        const editModal = document.getElementById('evidenceEditModal');
        if (!modal) {
            return;
        }

        const openModal = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            modal.querySelector('input[name="nama_file"]')?.focus();
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');
        };

        const openEditModal = (button) => {
            const form = document.getElementById('evidenceEditForm');
            form.action = button.dataset.evidenceAction;
            document.getElementById('evidenceEditName').value = button.dataset.evidenceName || '';
            document.getElementById('evidenceEditDate').value = button.dataset.evidenceDate || '';
            document.getElementById('evidenceEditStatus').value = button.dataset.evidenceStatus || '';
            const typeSelect = document.getElementById('evidenceEditType');
            const unknownTypeOption = typeSelect.querySelector('[data-api-value]');
            if (unknownTypeOption) {
                unknownTypeOption.remove();
            }
            const evidenceType = button.dataset.evidenceType || '';
            if (evidenceType && !Array.from(typeSelect.options).some((option) => option.value === evidenceType)) {
                const option = document.createElement('option');
                option.value = evidenceType;
                option.textContent = evidenceType;
                option.dataset.apiValue = 'true';
                typeSelect.append(option);
            }
            typeSelect.value = evidenceType;
            @if(!session('editEvidenceId'))
                document.getElementById('evidenceEditUrl').value = button.dataset.evidenceUrl || '';
            @endif
            editModal.classList.remove('hidden');
            editModal.classList.add('flex');
            editModal.setAttribute('aria-hidden', 'false');
            document.getElementById('evidenceEditUrl').focus();
        };

        const closeEditModal = () => {
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
            editModal.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-open-evidence-modal]').forEach((button) => {
            button.addEventListener('click', openModal);
        });
        modal.querySelectorAll('[data-close-evidence-modal]').forEach((button) => {
            button.addEventListener('click', closeModal);
        });
        document.querySelectorAll('[data-edit-evidence]').forEach((button) => {
            button.addEventListener('click', () => openEditModal(button));
        });
        editModal.querySelectorAll('[data-close-evidence-edit]').forEach((button) => {
            button.addEventListener('click', closeEditModal);
        });
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
        editModal.addEventListener('click', (event) => {
            if (event.target === editModal) {
                closeEditModal();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
            if (event.key === 'Escape' && !editModal.classList.contains('hidden')) {
                closeEditModal();
            }
        });

        const searchInput = document.getElementById('evidenceSearch');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', () => {
                window.clearTimeout(searchTimeout);
                searchTimeout = window.setTimeout(() => searchInput.form.requestSubmit(), 350);
            });
        }

        @if(session('editEvidenceId'))
            const failedEditButton = document.querySelector('[data-edit-evidence][data-evidence-id="{{ session('editEvidenceId') }}"]');
            if (failedEditButton) {
                openEditModal(failedEditButton);
            }
        @endif

        @if(session('openEvidenceModal'))
            openModal();
        @endif
    })();
</script>
@endsection
