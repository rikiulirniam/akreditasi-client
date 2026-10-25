@extends('layouts.app')

@section('title', 'Mata Kuliah')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb / Back Link -->
    <div class="flex items-center justify-between gap-3">
        <h2 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight">
            Detail Mata Kuliah
        </h2>
        <a 
            href="{{ route('mata-kuliah.index') }}" 
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white-form hover:bg-gray-200 text-gray-800 text-xs sm:text-sm font-bold transition cursor-pointer"
            title="Kembali ke Daftar Mata Kuliah"
        >
            <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    @php
        $id = $mataKuliah['id'] ?? '';
        $nama = $mataKuliah['nama_mata_kuliah'] ?? $mataKuliah['nama'] ?? '-';
        $kode = $mataKuliah['kode_mata_kuliah'] ?? $mataKuliah['kode'] ?? '-';
        $sks = $mataKuliah['sks'] ?? 0;
        $kId = $mataKuliah['id_kurikulum'] ?? $mataKuliah['kurikulum_id'] ?? null;
        $jumlahKelas = count($kelasList);
    @endphp

    <!-- ======================================================== -->
    <!-- TOP DETAIL CARD (Sesuai Template Gambar)                  -->
    <!-- ======================================================== -->
    <div class="bg-white rounded-2xl border border-gray-200/90 p-6 sm:p-8 shadow-xs">
        <!-- Card Header: Title & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-gray-100">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                {{ $nama }}
            </h1>

            <div class="flex items-center gap-3 shrink-0">
                @haspermission('mata_kuliah.delete')
                <!-- Tombol Hapus Data (Merah) -->
                <button 
                    type="button" 
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#C9352B] hover:bg-[#b02c23] text-white font-bold text-xs sm:text-sm transition cursor-pointer shadow-xs"
                    data-confirm-delete
                    data-action="{{ route('mata-kuliah.destroy', $id) }}"
                    data-title="Hapus Data Mata Kuliah?"
                    data-message="Apakah Anda yakin ingin menghapus data Mata Kuliah {{ $nama }} ({{ $kode }})? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                >
                    <x-icon.delete class="w-4 h-4" />
                    <span>Hapus Data</span>
                </button>
                @endhaspermission

                @haspermission('mata_kuliah.update')
                <!-- Tombol Edit Data (Kuning) -->
                <button 
                    type="button" 
                    id="openEditMkShowBtn"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#F59E0B] hover:bg-[#D97706] text-white font-bold text-xs sm:text-sm transition cursor-pointer shadow-xs"
                >
                    <x-icon.edit class="w-4 h-4" />
                    <span>Edit Data</span>
                </button>
                @endhaspermission
            </div>
        </div>

        <!-- 2-Column Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8 pt-6">
            <!-- Left Column -->
            <div class="space-y-4">
                <!-- Kode Mata Kuliah -->
                <div class="flex items-center gap-3 text-xs sm:text-sm">
                    <div class="w-6 h-6 rounded-md bg-blue-50 text-primary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <span class="text-gray-900">
                        <span class="font-bold text-gray-900">Kode Mata Kuliah:</span>
                        <span class="font-mono font-semibold ml-1">{{ $kode }}</span>
                    </span>
                </div>

                <!-- Program Studi -->
                <div class="flex items-center gap-3 text-xs sm:text-sm">
                    <div class="w-6 h-6 rounded-md bg-blue-50 text-primary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
                        </svg>
                    </div>
                    <span class="text-gray-900">
                        <span class="font-bold text-gray-900">Program Studi:</span>
                        <span class="font-medium ml-1">{{ $prodiNama }}</span>
                    </span>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
                <!-- Kurikulum -->
                <div class="flex items-center gap-3 text-xs sm:text-sm">
                    <div class="w-6 h-6 rounded-md bg-blue-50 text-primary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <span class="text-gray-900">
                        <span class="font-bold text-gray-900">Kurikulum :</span>
                        <span class="font-medium ml-1">{{ $kurikulumNama }}</span>
                    </span>
                </div>

                <!-- Jumlah Kelas -->
                <div class="flex items-center gap-3 text-xs sm:text-sm">
                    <div class="w-6 h-6 rounded-md bg-blue-50 text-primary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                    <span class="text-gray-900">
                        <span class="font-bold text-gray-900">Jumlah Kelas:</span>
                        <span class="font-semibold ml-1">{{ $jumlahKelas }} Kelas</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- BOTTOM SECTION: DAFTAR KELAS & BULK IMPORT              -->
    <!-- ======================================================== -->
    <div class="space-y-3 pt-2">
        <!-- Flash Alert for Import Errors/Warnings -->
        @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800">
            <div class="font-bold flex items-center gap-2 text-rose-900 mb-1">
                <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                Catatan Kesalahan Saat Import (Sebagian baris gagal):
            </div>
            <ul class="list-disc list-inside space-y-0.5 mt-2 max-h-36 overflow-y-auto font-mono text-[11px]">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('import_warnings') && count(session('import_warnings')) > 0)
        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-800">
            <div class="font-bold flex items-center gap-2 text-amber-900 mb-1">
                <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Peringatan Import:
            </div>
            <ul class="list-disc list-inside space-y-0.5 mt-2 max-h-36 overflow-y-auto font-mono text-[11px]">
                @foreach(session('import_warnings') as $warn)
                    <li>{{ $warn }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight">
                    Daftar Kelas
                </h3>
            </div>

            <!-- Actions: Format Import & Bulk Import Nilai -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Dropdown Format Import Multi-Kelas -->
                <div class="relative inline-block text-left" id="bulkTemplateDropdownContainer">
                    <button 
                        type="button" 
                        id="bulkTemplateDropdownBtn"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-300 bg-white text-gray-700 text-xs font-bold hover:bg-gray-50 transition shadow-2xs cursor-pointer"
                        title="Unduh format template impor nilai untuk seluruh kelas mata kuliah ini"
                    >
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Format Import (Semua Kelas)</span>
                        <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div 
                        id="bulkTemplateDropdownMenu" 
                        class="hidden absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 z-50 p-2 space-y-1"
                    >
                        <a 
                            href="{{ route('nilai-mahasiswa.template', ['mode' => 'multi', 'kode_mata_kuliah' => $kode, 'format' => 'xlsx']) }}" 
                            class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition"
                        >
                            <span>Format Excel (.xlsx)</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">XLSX</span>
                        </a>
                        <a 
                            href="{{ route('nilai-mahasiswa.template', ['mode' => 'multi', 'kode_mata_kuliah' => $kode, 'format' => 'csv']) }}" 
                            class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition"
                        >
                            <span>Format CSV (.csv)</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-blue-100 text-blue-800">CSV</span>
                        </a>
                    </div>
                </div>

                <!-- Tombol Bulk Import Nilai -->
                <button 
                    type="button" 
                    id="openBulkImportModalBtn"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-2xs cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    <span>Bulk Import Nilai</span>
                </button>
            </div>
        </div>

        @if($kelasDataError)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
                Sebagian informasi kelas tidak dapat dimuat dari Akademik Service. Data yang tampil mungkin belum lengkap.
            </div>
        @endif

        <x-table>
            <x-slot:header>
                <x-table.th class="w-16">No</x-table.th>
                <x-table.th>Kelas</x-table.th>
                <x-table.th>Kurikulum</x-table.th>
                <x-table.th>Jumlah Mahasiswa</x-table.th>
                <x-table.th class="w-20 text-right">Action</x-table.th>
            </x-slot:header>

            @forelse($kelasList as $index => $item)
                @php
                    $namaKelas = $item['nama_kelas'] ?? $item['kelas'] ?? '-';
                    $kurik = $item['kurikulum'] ?? $kurikulumNama;
                    $jmlMhs = isset($item['jumlah_mahasiswa']) ? $item['jumlah_mahasiswa'] : 0;
                @endphp
                <x-table.row>
                    <!-- No -->
                    <x-table.cell class="text-gray-600 font-medium w-16">
                        {{ $index + 1 }}.
                    </x-table.cell>

                    <!-- Kelas -->
                    <x-table.cell>
                        <span class="font-bold text-sm text-gray-900 font-mono tracking-tight">
                            {{ $namaKelas }}
                        </span>
                    </x-table.cell>

                    <!-- Kurikulum -->
                    <x-table.cell>
                        <span class="text-sm font-medium text-gray-800">
                            {{ $kurik }}
                        </span>
                    </x-table.cell>

                    <!-- Jumlah Mahasiswa -->
                    <x-table.cell>
                        <span class="text-sm font-medium text-gray-800">
                            {{ $jmlMhs }} Mahasiswa
                        </span>
                    </x-table.cell>

                    <!-- Action -->
                    <x-table.cell class="w-20">
                        <div class="flex items-center justify-end">
                            <button
                                type="button"
                                class="open-class-detail-modal text-primary hover:text-additional transition p-1 cursor-pointer"
                                title="Lihat Kelas"
                                aria-label="Lihat detail {{ $namaKelas }}"
                                data-kelas="{{ $namaKelas }}"
                                data-angkatan="{{ $item['angkatan'] }}"
                                data-jumlah-mahasiswa="{{ $jmlMhs }}"
                                data-prodi="{{ $item['prodi'] }}"
                                data-tahun-akademik="{{ $item['tahun_akademik'] }}"
                                data-rata-rata-ipk="{{ $item['rata_rata_ipk'] }}"
                                data-jumlah-mata-kuliah="{{ $item['jumlah_mata_kuliah'] }}"
                                data-class-id="{{ $item['id'] }}"
                                data-mata-kuliah-angkatan-kelas-id="{{ $item['mata_kuliah_angkatan_kelas_id'] ?? '' }}"
                                data-link-nilai="{{ $item['link_nilai'] }}"
                                data-link-evidence="{{ $item['link_evidence'] }}"
                                data-multiple-grade-links="{{ $item['has_multiple_grade_links'] ? '1' : '0' }}"
                                data-multiple-evidence-links="{{ $item['has_multiple_evidence_links'] ? '1' : '0' }}"
                            >
                                <x-icon.eye class="w-5 h-5" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="5" message="Belum ada kelas terdaftar untuk mata kuliah ini" submessage="Silakan buat kelas baru pada modul Kelas & Angkatan." />
            @endforelse
        </x-table>
    </div>
</div>

<div
    id="classDetailModal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200"
    role="dialog"
    aria-modal="true"
    aria-labelledby="classDetailModalTitle"
>
    <div class="class-detail-card relative bg-white rounded-2xl p-6 max-w-[500px] w-full shadow-2xl transform scale-95 transition-all duration-200">
        <h3 id="classDetailModalTitle" class="text-lg font-bold text-gray-900 text-center mb-6"></h3>

        <div class="space-y-3 rounded-2xl bg-gray-100 p-5 text-sm text-gray-900">
            <p><span class="font-bold">Angkatan:</span> <span id="classDetailAngkatan"></span></p>
            <p><span class="font-bold">Jumlah Mahasiswa:</span> <span id="classDetailJumlahMahasiswa"></span> Mahasiswa</p>
            <p><span class="font-bold">Program Studi:</span> <span id="classDetailProdi"></span></p>
            <p><span class="font-bold">Tahun Akademik:</span> <span id="classDetailTahunAkademik"></span></p>
            <p><span class="font-bold">Rata-rata IPK Kelas:</span> <span id="classDetailRataRataIpk"></span></p>
            <p><span class="font-bold">Jumlah Mata Kuliah:</span> <span id="classDetailJumlahMataKuliah"></span> Mata Kuliah</p>
            <p>
                <span class="font-bold">Link Input Nilai:</span>
                <a id="classDetailLinkNilai" class="hidden break-all text-primary underline" target="_blank" rel="noopener noreferrer"></a>
                <span id="classDetailLinkNilaiEmpty">-</span>
                <span id="classDetailLinkNilaiMultiple" class="hidden">Terdapat beberapa link berbeda</span>
            </p>
            <p>
                <span class="font-bold">Link Input Evidence:</span>
                <a id="classDetailLinkEvidence" class="hidden break-all text-primary underline" target="_blank" rel="noopener noreferrer"></a>
                <span id="classDetailLinkEvidenceEmpty">-</span>
                <span id="classDetailLinkEvidenceMultiple" class="hidden">Terdapat beberapa link berbeda</span>
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3 pt-5">
            <button
                type="button"
                id="openGradeLinkModalBtn"
                class="w-full rounded-lg bg-primary px-4 py-2.5 text-xs font-bold text-white transition hover:bg-additional cursor-pointer"
            >
                Input Nilai Link
            </button>
            <button
                type="button"
                id="openEvidenceModalBtn"
                class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer"
            >
                Input Evidence Link
            </button>
        </div>
        <div class="grid grid-cols-2 gap-3 pt-2">
            <button
                type="button"
                id="openClassImportBtn"
                class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                Import Nilai Kelas Ini
            </button>
            <button
                type="button"
                id="openClassGradesBtn"
                class="w-full rounded-lg bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-xs font-bold text-white transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Lihat Nilai Kelas Ini
            </button>
        </div>
        <button
            type="button"
            class="close-class-detail-modal mt-3 w-full rounded-lg bg-gray-200 px-4 py-2.5 text-sm font-bold text-gray-900 transition hover:bg-gray-300 cursor-pointer"
        >
            Batal
        </button>
    </div>
</div>

<div
    id="gradeLinkModal"
    class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200"
    role="dialog"
    aria-modal="true"
    aria-labelledby="gradeLinkModalTitle"
>
    <div class="grade-link-modal-card relative bg-white rounded-2xl p-7 max-w-[460px] w-full shadow-2xl transform scale-95 transition-all duration-200">
        <h3 id="gradeLinkModalTitle" class="text-lg font-bold text-gray-900 text-center mb-6">
            Input Nilai Mata Kuliah
        </h3>

        <div class="space-y-4">
            <div>
                <label for="gradeLinkInput" class="mb-2 block text-xs font-bold text-gray-900">
                    Input Link Nilai
                </label>
                <form id="gradeLinkForm" action="" method="POST">
                    @csrf
                    @method('PATCH')
                    <input
                        type="url"
                        name="link_nilai"
                        id="gradeLinkInput"
                        maxlength="2048"
                        placeholder="Masukkan Link"
                        required
                        class="w-full rounded-lg bg-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                    <p id="gradeLinkHint" class="mt-1 hidden text-xs text-amber-700" role="status"></p>
                    <div class="grid grid-cols-2 gap-3 pt-4">
                        <button
                            type="button"
                            class="close-grade-link-modal w-full rounded-lg bg-gray-200 px-4 py-2.5 text-sm font-bold text-gray-900 transition hover:bg-gray-300"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            id="saveGradeLinkBtn"
                            class="w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-additional disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div
    id="evidenceModal"
    class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200"
    role="dialog"
    aria-modal="true"
    aria-labelledby="evidenceModalTitle"
>
    <div class="evidence-modal-card relative bg-white rounded-2xl p-7 max-w-[460px] w-full shadow-2xl transform scale-95 transition-all duration-200">
        <h3 id="evidenceModalTitle" class="text-lg font-bold text-gray-900 text-center mb-6">
            Input Evidence Mata Kuliah
        </h3>

        <form id="evidenceForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="evidenceLinkInput" class="mb-2 block text-xs font-bold text-gray-900">
                    Input Link Evidence
                </label>
                <input
                    type="url"
                    name="link_evidence"
                    id="evidenceLinkInput"
                    maxlength="2048"
                    placeholder="Masukkan Link"
                    required
                    class="w-full rounded-lg bg-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30"
                >
                <p id="evidenceLinkHint" class="mt-1 hidden text-xs text-amber-700" role="status"></p>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-1">
                <button
                    type="button"
                    class="close-evidence-modal w-full rounded-lg bg-gray-200 px-4 py-2.5 text-sm font-bold text-gray-900 transition hover:bg-gray-300"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-additional"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: Perbarui Mata Kuliah                              -->
<!-- ======================================================== -->
<div 
    id="editMkShowModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editMkShowModalTitle"
>
    <div class="mk-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editMkShowModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Perbarui Mata Kuliah
        </h3>

        <form action="{{ route('mata-kuliah.update', $id) }}" method="POST" class="space-y-4">
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
                    value="{{ $nama }}"
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
                    value="{{ $kode }}"
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
                            <option value="{{ $kid }}" {{ (string) $kId === (string) $kid ? 'selected' : '' }}>
                                {{ $knama }}
                            </option>
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
                    value="{{ $sks }}"
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
                    class="close-edit-show-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
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

<!-- ======================================================== -->
<!-- MODAL: Bulk Import Nilai (Semua Kelas Mata Kuliah Ini)   -->
<!-- ======================================================== -->
<div 
    id="bulkImportModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="bulkImportModalTitle"
>
    <div class="bulk-import-card relative bg-white rounded-[28px] p-6 sm:p-8 max-w-lg w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
                <div>
                    <h3 id="bulkImportModalTitle" class="text-base font-black text-gray-900 tracking-tight">
                        Bulk Import Nilai Mahasiswa
                    </h3>
                    <p class="text-xs text-gray-500">Mata Kuliah: {{ $nama }} ({{ $kode }})</p>
                </div>
            </div>
            <button type="button" class="close-bulk-import p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="bulkImportForm" action="{{ route('nilai-mahasiswa.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

            <!-- Template Quick Download Hint -->
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs text-emerald-900">
                <div class="font-bold flex items-center gap-1.5 mb-1 text-emerald-950">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    Unduh Format Berkas Multi-Kelas
                </div>
                <p class="text-[11px] text-emerald-800 leading-relaxed mb-2">
                    Format ini memuat seluruh mahasiswa dari semua kelas yang mengambil mata kuliah {{ $kode }}:
                </p>
                <div class="flex flex-wrap gap-2">
                    <a 
                        href="{{ route('nilai-mahasiswa.template', ['mode' => 'multi', 'kode_mata_kuliah' => $kode, 'format' => 'xlsx']) }}" 
                        class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition shadow-2xs"
                    >
                        Unduh Template Excel (.xlsx)
                    </a>
                    <a 
                        href="{{ route('nilai-mahasiswa.template', ['mode' => 'multi', 'kode_mata_kuliah' => $kode, 'format' => 'csv']) }}" 
                        class="px-2.5 py-1 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold text-[11px] transition"
                    >
                        Unduh CSV (.csv)
                    </a>
                </div>
            </div>

            <!-- Upload File Input Dropzone -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">
                    Pilih File Excel / CSV <span class="text-rose-500">*</span>
                </label>
                <div 
                    class="relative border-2 border-dashed border-gray-300 hover:border-emerald-500 rounded-2xl p-6 text-center cursor-pointer transition bg-gray-50/50 hover:bg-emerald-50/30"
                >
                    <input 
                        type="file" 
                        name="file" 
                        id="bulkImportFileInput"
                        accept=".xlsx,.csv,.tsv,.txt" 
                        required 
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                    />
                    <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                        <svg class="w-10 h-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <div id="bulkDropzoneFilename" class="text-xs font-semibold text-gray-700">
                            Klik atau tarik berkas ke sini (.xlsx, .csv)
                        </div>
                        <div class="text-[10px] text-gray-400">Ukuran maksimal file: 10 MB</div>
                    </div>
                </div>
            </div>

            <!-- Opsi Penanganan Duplikasi Data -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">
                    Opsi Keamanan Duplikasi Data <span class="text-rose-500">*</span>
                </label>
                <div class="space-y-2">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                        <input 
                            type="radio" 
                            name="duplicate_action" 
                            value="update" 
                            checked 
                            class="mt-0.5 text-emerald-600 focus:ring-emerald-500"
                        />
                        <div>
                            <div class="text-xs font-bold text-gray-900">Perbarui Nilai yang Sudah Ada (Update) - Rekomendasi</div>
                            <div class="text-[11px] text-gray-500">Nilai mahasiswa pada mata kuliah ini akan diperbarui dengan data baru dari file.</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                        <input 
                            type="radio" 
                            name="duplicate_action" 
                            value="skip" 
                            class="mt-0.5 text-emerald-600 focus:ring-emerald-500"
                        />
                        <div>
                            <div class="text-xs font-bold text-gray-900">Lewati Baris Duplikat (Skip)</div>
                            <div class="text-[11px] text-gray-500">Hanya memasukkan data mahasiswa yang belum memiliki nilai di sistem.</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                        <input 
                            type="radio" 
                            name="duplicate_action" 
                            value="fail" 
                            class="mt-0.5 text-emerald-600 focus:ring-emerald-500"
                        />
                        <div>
                            <div class="text-xs font-bold text-gray-900">Gagalkan Seluruh Impor (Fail)</div>
                            <div class="text-[11px] text-gray-500">Batalkan seluruh proses jika ditemukan minimal satu data duplikat.</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-100">
                <button 
                    type="button" 
                    class="close-bulk-import w-full py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    id="submitBulkImportBtn"
                    class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm cursor-pointer text-center flex items-center justify-center gap-2"
                >
                    <span id="submitBulkImportText">Mulai Impor</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: Import Nilai Per Kelas Spesifik                   -->
<!-- ======================================================== -->
<div 
    id="classImportModal" 
    class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="classImportModalTitle"
>
    <div class="class-import-card relative bg-white rounded-[28px] p-6 sm:p-8 max-w-lg w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
                <div>
                    <h3 id="classImportModalTitle" class="text-base font-black text-gray-900 tracking-tight">
                        Import Nilai Kelas <span id="classImportKelasName"></span>
                    </h3>
                    <p class="text-xs text-gray-500">Mata Kuliah: {{ $nama }} ({{ $kode }})</p>
                </div>
            </div>
            <button type="button" class="close-class-import p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="classImportForm" action="{{ route('nilai-mahasiswa.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
            <input type="hidden" name="mata_kuliah_angkatan_kelas_id" id="classImportMkAkId" value="">

            <!-- Template Quick Download Hint -->
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs text-emerald-900">
                <div class="font-bold flex items-center gap-1.5 mb-1 text-emerald-950">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    Format Template Khusus Kelas Ini
                </div>
                <p class="text-[11px] text-emerald-800 leading-relaxed mb-2">
                    Template ini otomatis hanya berisi daftar mahasiswa di kelas ini:
                </p>
                <div class="flex flex-wrap gap-2">
                    <a 
                        id="classImportDownloadXlsx"
                        href="#" 
                        class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition shadow-2xs"
                    >
                        Unduh Template (.xlsx)
                    </a>
                    <a 
                        id="classImportDownloadCsv"
                        href="#" 
                        class="px-2.5 py-1 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold text-[11px] transition"
                    >
                        Unduh CSV (.csv)
                    </a>
                </div>
            </div>

            <!-- Upload File Input Dropzone -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">
                    Pilih File Excel / CSV <span class="text-rose-500">*</span>
                </label>
                <div 
                    class="relative border-2 border-dashed border-gray-300 hover:border-emerald-500 rounded-2xl p-6 text-center cursor-pointer transition bg-gray-50/50 hover:bg-emerald-50/30"
                >
                    <input 
                        type="file" 
                        name="file" 
                        id="classImportFileInput"
                        accept=".xlsx,.csv,.tsv,.txt" 
                        required 
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                    />
                    <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                        <svg class="w-10 h-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <div id="classDropzoneFilename" class="text-xs font-semibold text-gray-700">
                            Klik atau tarik berkas ke sini (.xlsx, .csv)
                        </div>
                        <div class="text-[10px] text-gray-400">Ukuran maksimal file: 10 MB</div>
                    </div>
                </div>
            </div>

            <!-- Opsi Penanganan Duplikasi Data -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">
                    Opsi Keamanan Duplikasi Data <span class="text-rose-500">*</span>
                </label>
                <div class="space-y-2">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                        <input 
                            type="radio" 
                            name="duplicate_action" 
                            value="update" 
                            checked 
                            class="mt-0.5 text-emerald-600 focus:ring-emerald-500"
                        />
                        <div>
                            <div class="text-xs font-bold text-gray-900">Perbarui Nilai yang Sudah Ada (Update) - Rekomendasi</div>
                            <div class="text-[11px] text-gray-500">Nilai mahasiswa pada kelas ini akan diperbarui dengan data baru dari file.</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                        <input 
                            type="radio" 
                            name="duplicate_action" 
                            value="skip" 
                            class="mt-0.5 text-emerald-600 focus:ring-emerald-500"
                        />
                        <div>
                            <div class="text-xs font-bold text-gray-900">Lewati Baris Duplikat (Skip)</div>
                            <div class="text-[11px] text-gray-500">Hanya memasukkan data mahasiswa yang nilainya belum ada di sistem.</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                        <input 
                            type="radio" 
                            name="duplicate_action" 
                            value="fail" 
                            class="mt-0.5 text-emerald-600 focus:ring-emerald-500"
                        />
                        <div>
                            <div class="text-xs font-bold text-gray-900">Gagalkan Seluruh Impor (Fail)</div>
                            <div class="text-[11px] text-gray-500">Batalkan seluruh proses jika ditemukan minimal satu data duplikat.</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-100">
                <button 
                    type="button" 
                    class="close-class-import w-full py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    id="submitClassImportBtn"
                    class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm cursor-pointer text-center flex items-center justify-center gap-2"
                >
                    <span id="submitClassImportText">Mulai Impor</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: Daftar Nilai Mahasiswa Kelas Spesifik             -->
<!-- ======================================================== -->
<div 
    id="classGradesModal" 
    class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="classGradesModalTitle"
>
    <div class="class-grades-card relative bg-white rounded-[28px] p-6 sm:p-8 max-w-3xl w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
            <div>
                <h3 id="classGradesModalTitle" class="text-base font-black text-gray-900 tracking-tight">
                    Daftar Nilai Kelas <span id="classGradesTitleKelas"></span>
                </h3>
                <p class="text-xs text-gray-500">Mata Kuliah: {{ $nama }} ({{ $kode }})</p>
            </div>
            <button type="button" class="close-class-grades p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="classGradesLoading" class="py-12 text-center text-xs text-gray-500">
            Memuat daftar nilai kelas...
        </div>

        <div id="classGradesContainer" class="hidden space-y-4">
            <div class="overflow-x-auto rounded-xl border border-gray-200 max-h-80 overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-200 sticky top-0">
                        <tr>
                            <th class="p-2.5 w-12 text-center">No</th>
                            <th class="p-2.5">Mahasiswa</th>
                            <th class="p-2.5 text-center">Nilai Angka</th>
                            <th class="p-2.5 text-center">Nilai Huruf</th>
                            <th class="p-2.5 text-center">Bobot</th>
                            <th class="p-2.5 text-center w-16">KHS</th>
                        </tr>
                    </thead>
                    <tbody id="classGradesTableBody" class="divide-y divide-gray-100">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 mt-4 flex items-center justify-between">
            <button 
                type="button" 
                id="btnBackToDetailModal"
                class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition cursor-pointer"
            >
                ← Kembali ke Detail Kelas
            </button>
            <button 
                type="button" 
                class="close-class-grades px-5 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold text-xs transition cursor-pointer"
            >
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: KHS & Transkrip Akademik Mahasiswa                -->
<!-- ======================================================== -->
<div 
    id="khsTranskripModal" 
    class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/50 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="khsModalTitle"
>
    <div class="khs-modal-card relative bg-white rounded-[28px] p-6 sm:p-8 max-w-2xl w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
            <div>
                <h3 id="khsModalTitle" class="text-base font-black text-gray-900 tracking-tight">
                    Catatan Akademik Mahasiswa
                </h3>
                <p class="text-xs text-gray-500" id="khsStudentSubtitle">-</p>
            </div>
            <button type="button" class="close-khs-modal p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex border-b border-gray-200 mb-4">
            <button 
                type="button" 
                id="tabKhsBtn"
                class="py-2.5 px-4 font-bold text-xs border-b-2 border-primary text-primary transition cursor-pointer"
            >
                Kartu Hasil Studi (KHS)
            </button>
            <button 
                type="button" 
                id="tabTranskripBtn"
                class="py-2.5 px-4 font-bold text-xs border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition cursor-pointer"
            >
                Transkrip Nilai Kumulatif (IPK)
            </button>
        </div>

        <div id="tabKhsContent" class="space-y-4">
            <div id="khsLoading" class="py-8 text-center text-xs text-gray-500">Memuat data KHS...</div>
            <div id="khsDataContainer" class="hidden space-y-4">
                <div class="p-4 rounded-2xl bg-primary/5 border border-primary/20 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-primary">Indeks Prestasi Semester (IP)</div>
                        <div class="text-xs text-gray-500 mt-0.5">Rumus: Σ (SKS × Bobot) ÷ Σ SKS</div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-black text-primary" id="khsIpValue">0.00</div>
                        <div class="text-[11px] text-gray-500" id="khsSksValue">Total 0 SKS • Kredit 0</div>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-200">
                            <tr>
                                <th class="p-2.5">Mata Kuliah</th>
                                <th class="p-2.5 text-center">SKS</th>
                                <th class="p-2.5 text-center">Nilai</th>
                                <th class="p-2.5 text-center">Bobot</th>
                                <th class="p-2.5 text-center">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="khsTableBody" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="tabTranskripContent" class="hidden space-y-4">
            <div id="transkripLoading" class="py-8 text-center text-xs text-gray-500">Memuat transkrip...</div>
            <div id="transkripDataContainer" class="hidden space-y-4">
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Indeks Prestasi Kumulatif (IPK)</div>
                        <div class="text-xs text-emerald-700 mt-0.5">Perhitungan seluruh semester berbobot SKS resmi</div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-black text-emerald-700" id="transkripIpkValue">0.00</div>
                        <div class="text-[11px] text-emerald-800" id="transkripSksValue">Total 0 SKS • Kredit 0</div>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-200 max-h-64 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-200 sticky top-0">
                            <tr>
                                <th class="p-2.5">Mata Kuliah</th>
                                <th class="p-2.5 text-center">SKS</th>
                                <th class="p-2.5 text-center">Nilai</th>
                                <th class="p-2.5 text-center">Bobot</th>
                                <th class="p-2.5 text-center">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="transkripTableBody" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 mt-4 text-right">
            <button 
                type="button" 
                class="close-khs-modal px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition cursor-pointer"
            >
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const gradeLinkUrlTemplate = @json(route('mata-kuliah.update-grade-link', [$id, '__CLASS_ID__']));
        const evidenceLinkUrlTemplate = @json(route('mata-kuliah.update-grade-evidence', [$id, '__CLASS_ID__']));
        
        const classDetailModal = document.getElementById('classDetailModal');
        const evidenceModal = document.getElementById('evidenceModal');
        const gradeLinkModal = document.getElementById('gradeLinkModal');
        const bulkImportModal = document.getElementById('bulkImportModal');
        const classImportModal = document.getElementById('classImportModal');
        const classGradesModal = document.getElementById('classGradesModal');
        const khsModal = document.getElementById('khsTranskripModal');
        const editMkShowModal = document.getElementById('editMkShowModal');

        const evidenceForm = document.getElementById('evidenceForm');
        const evidenceLinkInput = document.getElementById('evidenceLinkInput');
        const openEvidenceModalBtn = document.getElementById('openEvidenceModalBtn');
        const openGradeLinkModalBtn = document.getElementById('openGradeLinkModalBtn');
        const gradeLinkInput = document.getElementById('gradeLinkInput');
        const gradeLinkForm = document.getElementById('gradeLinkForm');
        const gradeLinkHint = document.getElementById('gradeLinkHint');
        const evidenceLinkHint = document.getElementById('evidenceLinkHint');

        let selectedClassButton = null;

        function showDialog(dialog) {
            const card = dialog?.querySelector('.class-detail-card, .evidence-modal-card, .grade-link-modal-card, .bulk-import-card, .class-import-card, .class-grades-card, .khs-modal-card, .mk-modal-card');
            if (!dialog) return;
            dialog.classList.remove('opacity-0', 'pointer-events-none');
            dialog.classList.add('opacity-100');
            card?.classList.remove('scale-95');
            card?.classList.add('scale-100');
        }

        function hideDialog(dialog) {
            const card = dialog?.querySelector('.class-detail-card, .evidence-modal-card, .grade-link-modal-card, .bulk-import-card, .class-import-card, .class-grades-card, .khs-modal-card, .mk-modal-card');
            if (!dialog) return;
            dialog.classList.remove('opacity-100');
            dialog.classList.add('opacity-0', 'pointer-events-none');
            card?.classList.remove('scale-100');
            card?.classList.add('scale-95');
        }

        // Bulk Template Dropdown Toggle
        const bulkTemplateDropdownBtn = document.getElementById('bulkTemplateDropdownBtn');
        const bulkTemplateDropdownMenu = document.getElementById('bulkTemplateDropdownMenu');
        if (bulkTemplateDropdownBtn && bulkTemplateDropdownMenu) {
            bulkTemplateDropdownBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                bulkTemplateDropdownMenu.classList.toggle('hidden');
            });
            document.addEventListener('click', function () {
                bulkTemplateDropdownMenu.classList.add('hidden');
            });
        }

        // Bulk Import Modal Trigger
        const openBulkImportModalBtn = document.getElementById('openBulkImportModalBtn');
        openBulkImportModalBtn?.addEventListener('click', () => showDialog(bulkImportModal));
        document.querySelectorAll('.close-bulk-import').forEach(btn => {
            btn.addEventListener('click', () => hideDialog(bulkImportModal));
        });

        // Dropzone filename previews
        const bulkImportFileInput = document.getElementById('bulkImportFileInput');
        const bulkDropzoneFilename = document.getElementById('bulkDropzoneFilename');
        if (bulkImportFileInput && bulkDropzoneFilename) {
            bulkImportFileInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    bulkDropzoneFilename.textContent = 'Berkas dipilih: ' + this.files[0].name;
                    bulkDropzoneFilename.classList.add('text-emerald-700', 'font-bold');
                } else {
                    bulkDropzoneFilename.textContent = 'Klik atau tarik berkas ke sini (.xlsx, .csv)';
                    bulkDropzoneFilename.classList.remove('text-emerald-700', 'font-bold');
                }
            });
        }

        const classImportFileInput = document.getElementById('classImportFileInput');
        const classDropzoneFilename = document.getElementById('classDropzoneFilename');
        if (classImportFileInput && classDropzoneFilename) {
            classImportFileInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    classDropzoneFilename.textContent = 'Berkas dipilih: ' + this.files[0].name;
                    classDropzoneFilename.classList.add('text-emerald-700', 'font-bold');
                } else {
                    classDropzoneFilename.textContent = 'Klik atau tarik berkas ke sini (.xlsx, .csv)';
                    classDropzoneFilename.classList.remove('text-emerald-700', 'font-bold');
                }
            });
        }

        // Submit loading spinners
        const bulkImportForm = document.getElementById('bulkImportForm');
        const submitBulkImportBtn = document.getElementById('submitBulkImportBtn');
        const submitBulkImportText = document.getElementById('submitBulkImportText');
        bulkImportForm?.addEventListener('submit', function () {
            submitBulkImportBtn.disabled = true;
            submitBulkImportBtn.classList.add('opacity-75');
            submitBulkImportText.textContent = 'Sedang Memproses Impor...';
        });

        const classImportForm = document.getElementById('classImportForm');
        const submitClassImportBtn = document.getElementById('submitClassImportBtn');
        const submitClassImportText = document.getElementById('submitClassImportText');
        classImportForm?.addEventListener('submit', function () {
            submitClassImportBtn.disabled = true;
            submitClassImportBtn.classList.add('opacity-75');
            submitClassImportText.textContent = 'Sedang Memproses Impor...';
        });

        // Detail Kelas Modal Click Handler
        document.querySelectorAll('.open-class-detail-modal').forEach(button => {
            button.addEventListener('click', function () {
                selectedClassButton = this;
                document.getElementById('classDetailModalTitle').textContent = `Kelas ${this.dataset.kelas}`;
                document.getElementById('classDetailAngkatan').textContent = this.dataset.angkatan;
                document.getElementById('classDetailJumlahMahasiswa').textContent = this.dataset.jumlahMahasiswa;
                document.getElementById('classDetailProdi').textContent = this.dataset.prodi;
                document.getElementById('classDetailTahunAkademik').textContent = this.dataset.tahunAkademik;
                document.getElementById('classDetailRataRataIpk').textContent = this.dataset.rataRataIpk;
                document.getElementById('classDetailJumlahMataKuliah').textContent = this.dataset.jumlahMataKuliah;
                const gradeLink = this.dataset.linkNilai ?? '';
                const evidenceLink = this.dataset.linkEvidence ?? '';
                const gradeLinkElement = document.getElementById('classDetailLinkNilai');
                const evidenceLinkElement = document.getElementById('classDetailLinkEvidence');
                gradeLinkElement.href = gradeLink || '#';
                gradeLinkElement.textContent = gradeLink;
                gradeLinkElement.classList.toggle('hidden', !gradeLink);
                const multipleGradeLinks = this.dataset.multipleGradeLinks === '1';
                document.getElementById('classDetailLinkNilaiEmpty').classList.toggle('hidden', Boolean(gradeLink) || multipleGradeLinks);
                document.getElementById('classDetailLinkNilaiMultiple').classList.toggle('hidden', !multipleGradeLinks);
                evidenceLinkElement.href = evidenceLink || '#';
                evidenceLinkElement.textContent = evidenceLink;
                evidenceLinkElement.classList.toggle('hidden', !evidenceLink);
                const multipleEvidenceLinks = this.dataset.multipleEvidenceLinks === '1';
                document.getElementById('classDetailLinkEvidenceEmpty').classList.toggle('hidden', Boolean(evidenceLink) || multipleEvidenceLinks);
                document.getElementById('classDetailLinkEvidenceMultiple').classList.toggle('hidden', !multipleEvidenceLinks);
                gradeLinkForm.action = gradeLinkUrlTemplate.replace('__CLASS_ID__', encodeURIComponent(this.dataset.classId));
                evidenceForm.action = evidenceLinkUrlTemplate.replace('__CLASS_ID__', encodeURIComponent(this.dataset.classId));

                openGradeLinkModalBtn.disabled = false;
                openEvidenceModalBtn.disabled = false;
                showDialog(classDetailModal);
            });
        });

        // Trigger Import Nilai Kelas Ini from Class Detail Modal
        document.getElementById('openClassImportBtn')?.addEventListener('click', function () {
            if (!selectedClassButton) return;
            const kelasNama = selectedClassButton.dataset.kelas;
            const mkAkId = selectedClassButton.dataset.mataKuliahAngkatanKelasId;

            document.getElementById('classImportKelasName').textContent = kelasNama;
            document.getElementById('classImportMkAkId').value = mkAkId;

            const dlXlsx = document.getElementById('classImportDownloadXlsx');
            const dlCsv = document.getElementById('classImportDownloadCsv');
            if (dlXlsx) {
                dlXlsx.href = `{{ route('nilai-mahasiswa.template') }}?mode=single&mata_kuliah_angkatan_kelas_id=${encodeURIComponent(mkAkId)}&format=xlsx`;
            }
            if (dlCsv) {
                dlCsv.href = `{{ route('nilai-mahasiswa.template') }}?mode=single&mata_kuliah_angkatan_kelas_id=${encodeURIComponent(mkAkId)}&format=csv`;
            }

            hideDialog(classDetailModal);
            showDialog(classImportModal);
        });

        document.querySelectorAll('.close-class-import').forEach(btn => {
            btn.addEventListener('click', () => hideDialog(classImportModal));
        });

        // Trigger Lihat Nilai Kelas Ini from Class Detail Modal
        document.getElementById('openClassGradesBtn')?.addEventListener('click', function () {
            if (!selectedClassButton) return;
            const kelasNama = selectedClassButton.dataset.kelas;
            const mkAkId = selectedClassButton.dataset.mataKuliahAngkatanKelasId;

            document.getElementById('classGradesTitleKelas').textContent = kelasNama;
            const loading = document.getElementById('classGradesLoading');
            const container = document.getElementById('classGradesContainer');
            const tbody = document.getElementById('classGradesTableBody');

            loading.classList.remove('hidden');
            container.classList.add('hidden');
            tbody.innerHTML = '';

            hideDialog(classDetailModal);
            showDialog(classGradesModal);

            fetch(`/nilai-mahasiswa?mata_kuliah_angkatan_kelas_id=${encodeURIComponent(mkAkId)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(res => {
                loading.classList.add('hidden');
                container.classList.remove('hidden');

                const data = res.data ?? [];
                if (!Array.isArray(data) || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-gray-500">Belum ada nilai mahasiswa yang tercatat pada kelas ini. Silakan gunakan tombol Import Nilai.</td></tr>';
                    return;
                }

                tbody.innerHTML = data.map((it, idx) => {
                    const mhs = it.mahasiswa ?? it.data_mahasiswa ?? it.enrollment_kelas?.mahasiswa ?? {};
                    const namaMhs = mhs.nama_mahasiswa ?? mhs.nama ?? mhs.name ?? it.nama_mahasiswa ?? it.nama ?? '-';
                    const nimMhs = mhs.nim ?? it.nim ?? '-';
                    const mhsId = it.mahasiswa_id ?? it.id_mahasiswa ?? mhs.id ?? null;

                    const angkaVal = it.nilai_angka ?? it.angka ?? null;
                    const angka = (angkaVal !== null && angkaVal !== undefined && angkaVal !== '') ? parseFloat(angkaVal).toFixed(2) : '-';

                    const huruf = it.nilai_huruf ?? it.huruf ?? '-';

                    const bobotVal = it.bobot ?? it.bobot_nilai ?? null;
                    const bobot = (bobotVal !== null && bobotVal !== undefined && bobotVal !== '') ? parseFloat(bobotVal).toFixed(2) : '-';

                    return `
                        <tr>
                            <td class="p-2.5 text-center font-medium text-gray-500">${idx + 1}</td>
                            <td class="p-2.5">
                                <div class="font-bold text-gray-900">${namaMhs}</div>
                                <div class="text-[11px] font-mono text-gray-500">${nimMhs}</div>
                            </td>
                            <td class="p-2.5 text-center font-mono font-bold">${angka}</td>
                            <td class="p-2.5 text-center font-black text-primary">${huruf}</td>
                            <td class="p-2.5 text-center font-mono">${bobot}</td>
                            <td class="p-2.5 text-center">
                                ${mhsId ? `
                                    <button 
                                        type="button" 
                                        class="btn-view-khs text-primary hover:text-additional transition p-1 cursor-pointer"
                                        data-mhs-id="${mhsId}"
                                        data-mhs-nama="${namaMhs !== '-' ? namaMhs : ''}"
                                        data-mhs-nim="${nimMhs !== '-' ? nimMhs : ''}"
                                        title="Lihat KHS Mahasiswa"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                ` : '-'}
                            </td>
                        </tr>
                    `;
                }).join('');

                // Attach click handler to KHS buttons
                document.querySelectorAll('.btn-view-khs').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const mId = this.dataset.mhsId;
                        const mNama = this.dataset.mhsNama;
                        const mNim = this.dataset.mhsNim;
                        openKhsForStudent(mId, mNama, mNim);
                    });
                });
            })
            .catch(() => {
                loading.innerHTML = '<span class="text-rose-600">Gagal memuat nilai kelas.</span>';
            });
        });

        document.getElementById('btnBackToDetailModal')?.addEventListener('click', function () {
            hideDialog(classGradesModal);
            showDialog(classDetailModal);
        });

        document.querySelectorAll('.close-class-grades').forEach(btn => {
            btn.addEventListener('click', () => hideDialog(classGradesModal));
        });

        // KHS Modal and Tab Functionality
        let activeKhsMhsId = null;
        const tabKhsBtn = document.getElementById('tabKhsBtn');
        const tabTranskripBtn = document.getElementById('tabTranskripBtn');
        const tabKhsContent = document.getElementById('tabKhsContent');
        const tabTranskripContent = document.getElementById('tabTranskripContent');

        tabKhsBtn?.addEventListener('click', function () {
            tabKhsBtn.classList.add('border-primary', 'text-primary');
            tabKhsBtn.classList.remove('border-transparent', 'text-gray-500');
            tabTranskripBtn.classList.remove('border-primary', 'text-primary');
            tabTranskripBtn.classList.add('border-transparent', 'text-gray-500');
            tabKhsContent.classList.remove('hidden');
            tabTranskripContent.classList.add('hidden');
        });

        tabTranskripBtn?.addEventListener('click', function () {
            tabTranskripBtn.classList.add('border-primary', 'text-primary');
            tabTranskripBtn.classList.remove('border-transparent', 'text-gray-500');
            tabKhsBtn.classList.remove('border-primary', 'text-primary');
            tabKhsBtn.classList.add('border-transparent', 'text-gray-500');
            tabTranskripContent.classList.remove('hidden');
            tabKhsContent.classList.add('hidden');

            if (activeKhsMhsId) {
                loadTranskripData(activeKhsMhsId);
            }
        });

        function openKhsForStudent(mhsId, mhsNama, mhsNim) {
            activeKhsMhsId = mhsId;
            document.getElementById('khsStudentSubtitle').textContent = `${mhsNama} (${mhsNim})`;
            tabKhsBtn.click();
            loadKhsData(mhsId);
            showDialog(khsModal);
        }

        function loadKhsData(mhsId) {
            const loading = document.getElementById('khsLoading');
            const container = document.getElementById('khsDataContainer');
            const tbody = document.getElementById('khsTableBody');
            const ipVal = document.getElementById('khsIpValue');
            const sksVal = document.getElementById('khsSksValue');

            loading.classList.remove('hidden');
            container.classList.add('hidden');
            tbody.innerHTML = '';

            fetch(`/nilai-mahasiswa/khs/${mhsId}`)
                .then(r => r.json())
                .then(res => {
                    loading.classList.add('hidden');
                    container.classList.remove('hidden');

                    const d = res.data ?? {};
                    const ringkasan = d.ringkasan ?? {};
                    const items = d.items ?? [];

                    ipVal.textContent = (ringkasan.ip ?? 0).toFixed(2);
                    sksVal.textContent = `Total ${ringkasan.total_sks ?? 0} SKS • Kredit ${ringkasan.total_kredit ?? 0}`;

                    if (items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-500">Belum ada nilai terdaftar untuk KHS mahasiswa ini.</td></tr>';
                        return;
                    }

                    tbody.innerHTML = items.map(it => `
                        <tr>
                            <td class="p-2.5 font-bold text-gray-900">${it.nama_mata_kuliah ?? '-'} <span class="font-normal text-gray-500 font-mono text-[11px]">(${it.kode_mata_kuliah ?? ''})</span></td>
                            <td class="p-2.5 text-center font-mono font-bold">${it.sks ?? 0}</td>
                            <td class="p-2.5 text-center font-black text-primary">${it.nilai_huruf ?? '-'}</td>
                            <td class="p-2.5 text-center font-mono">${(it.bobot ?? 0).toFixed(2)}</td>
                            <td class="p-2.5 text-center font-mono font-bold text-gray-900">${(it.kredit ?? 0).toFixed(2)}</td>
                        </tr>
                    `).join('');
                })
                .catch(() => {
                    loading.innerHTML = '<span class="text-rose-600">Gagal memuat data KHS.</span>';
                });
        }

        function loadTranskripData(mhsId) {
            const loading = document.getElementById('transkripLoading');
            const container = document.getElementById('transkripDataContainer');
            const tbody = document.getElementById('transkripTableBody');
            const ipkVal = document.getElementById('transkripIpkValue');
            const sksVal = document.getElementById('transkripSksValue');

            loading.classList.remove('hidden');
            container.classList.add('hidden');
            tbody.innerHTML = '';

            fetch(`/nilai-mahasiswa/transkrip/${mhsId}`)
                .then(r => r.json())
                .then(res => {
                    loading.classList.add('hidden');
                    container.classList.remove('hidden');

                    const d = res.data ?? {};
                    const ringkasan = d.ringkasan ?? {};
                    const items = d.mata_kuliah ?? [];

                    ipkVal.textContent = (ringkasan.ipk ?? 0).toFixed(2);
                    sksVal.textContent = `Total ${ringkasan.total_sks ?? 0} SKS • Kredit ${ringkasan.total_kredit ?? 0}`;

                    if (items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-500">Belum ada mata kuliah yang tercatat pada transkrip.</td></tr>';
                        return;
                    }

                    tbody.innerHTML = items.map(it => `
                        <tr>
                            <td class="p-2.5 font-bold text-gray-900">${it.nama_mata_kuliah ?? '-'} <span class="font-normal text-gray-500 font-mono text-[11px]">(${it.kode_mata_kuliah ?? ''})</span></td>
                            <td class="p-2.5 text-center font-mono font-bold">${it.sks ?? 0}</td>
                            <td class="p-2.5 text-center font-black text-emerald-700">${it.nilai_huruf ?? '-'}</td>
                            <td class="p-2.5 text-center font-mono">${(it.bobot ?? 0).toFixed(2)}</td>
                            <td class="p-2.5 text-center font-mono font-bold text-gray-900">${(it.kredit ?? 0).toFixed(2)}</td>
                        </tr>
                    `).join('');
                })
                .catch(() => {
                    loading.innerHTML = '<span class="text-rose-600">Gagal memuat transkrip.</span>';
                });
        }

        document.querySelectorAll('.close-khs-modal').forEach(btn => {
            btn.addEventListener('click', () => hideDialog(khsModal));
        });

        // Other Modals (Evidence, Grade Link, Edit Mata Kuliah)
        openEvidenceModalBtn?.addEventListener('click', function () {
            if (this.disabled) return;
            evidenceLinkInput.value = selectedClassButton?.dataset.linkEvidence ?? '';
            evidenceLinkHint.textContent = selectedClassButton?.dataset.multipleEvidenceLinks === '1'
                ? 'Terdapat beberapa link evidence berbeda di kelas ini. Isi link baru untuk menggantinya pada seluruh mahasiswa.'
                : '';
            evidenceLinkHint.classList.toggle('hidden', evidenceLinkHint.textContent === '');
            hideDialog(classDetailModal);
            showDialog(evidenceModal);
            evidenceLinkInput.focus();
        });

        openGradeLinkModalBtn?.addEventListener('click', function () {
            gradeLinkInput.value = selectedClassButton?.dataset.linkNilai ?? '';
            gradeLinkHint.textContent = selectedClassButton?.dataset.multipleGradeLinks === '1'
                ? 'Terdapat beberapa link nilai berbeda di kelas ini. Isi link baru untuk menggantinya pada seluruh mahasiswa.'
                : '';
            gradeLinkHint.classList.toggle('hidden', gradeLinkHint.textContent === '');
            hideDialog(classDetailModal);
            showDialog(gradeLinkModal);
            gradeLinkInput.focus();
        });

        document.querySelectorAll('.close-class-detail-modal').forEach(button => {
            button.addEventListener('click', () => hideDialog(classDetailModal));
        });

        document.querySelectorAll('.close-grade-link-modal').forEach(button => {
            button.addEventListener('click', () => hideDialog(gradeLinkModal));
        });

        document.querySelectorAll('.close-evidence-modal').forEach(button => {
            button.addEventListener('click', () => hideDialog(evidenceModal));
        });

        [classDetailModal, gradeLinkModal, evidenceModal, bulkImportModal, classImportModal, classGradesModal, khsModal].forEach(dialog => {
            dialog?.addEventListener('click', function (event) {
                if (event.target === dialog) hideDialog(dialog);
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                hideDialog(khsModal);
                hideDialog(classGradesModal);
                hideDialog(classImportModal);
                hideDialog(bulkImportModal);
                hideDialog(evidenceModal);
                hideDialog(gradeLinkModal);
                hideDialog(classDetailModal);
            }
        });

        const modal = document.getElementById('editMkShowModal');
        const openBtn = document.getElementById('openEditMkShowBtn');
        const card = modal?.querySelector('.mk-modal-card');

        function openModal() {
            if (!modal) return;
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            card?.classList.remove('scale-95');
            card?.classList.add('scale-100');
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            card?.classList.remove('scale-100');
            card?.classList.add('scale-95');
        }

        if (openBtn) {
            openBtn.addEventListener('click', openModal);
        }

        document.querySelectorAll('.close-edit-show-modal').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        modal?.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal?.classList.contains('pointer-events-none')) {
                closeModal();
            }
        });
    });
</script>
@endsection
