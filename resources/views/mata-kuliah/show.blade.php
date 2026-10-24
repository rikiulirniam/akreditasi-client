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
    <!-- BOTTOM SECTION: DAFTAR KELAS (Sesuai Template Gambar)    -->
    <!-- ======================================================== -->
    <div class="space-y-3 pt-2">
        <h3 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight">
            Daftar Kelas
        </h3>

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
        </div>

        <div class="grid grid-cols-2 gap-3 pt-5">
            <button
                type="button"
                id="openGradeLinkModalBtn"
                class="w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-additional"
            >
                Input Nilai
            </button>
            <button
                type="button"
                id="openEvidenceModalBtn"
                class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Input Evidence
            </button>
        </div>
        <button
            type="button"
            class="close-class-detail-modal mt-3 w-full rounded-lg bg-gray-200 px-4 py-2.5 text-sm font-bold text-gray-900 transition hover:bg-gray-300"
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const gradeLinkUrlTemplate = @json(route('mata-kuliah.update-grade-link', [$id, '__CLASS_ID__']));
        const evidenceLinkUrlTemplate = @json(route('mata-kuliah.update-grade-evidence', [$id, '__CLASS_ID__']));
        const classDetailModal = document.getElementById('classDetailModal');
        const evidenceModal = document.getElementById('evidenceModal');
        const gradeLinkModal = document.getElementById('gradeLinkModal');
        const evidenceForm = document.getElementById('evidenceForm');
        const evidenceLinkInput = document.getElementById('evidenceLinkInput');
        const openEvidenceModalBtn = document.getElementById('openEvidenceModalBtn');
        const openGradeLinkModalBtn = document.getElementById('openGradeLinkModalBtn');
        const gradeLinkInput = document.getElementById('gradeLinkInput');
        const gradeLinkForm = document.getElementById('gradeLinkForm');

        function showDialog(dialog) {
            const card = dialog?.querySelector('.class-detail-card, .evidence-modal-card, .grade-link-modal-card');
            if (!dialog) return;
            dialog.classList.remove('opacity-0', 'pointer-events-none');
            dialog.classList.add('opacity-100');
            card?.classList.remove('scale-95');
            card?.classList.add('scale-100');
        }

        function hideDialog(dialog) {
            const card = dialog?.querySelector('.class-detail-card, .evidence-modal-card, .grade-link-modal-card');
            if (!dialog) return;
            dialog.classList.remove('opacity-100');
            dialog.classList.add('opacity-0', 'pointer-events-none');
            card?.classList.remove('scale-100');
            card?.classList.add('scale-95');
        }

        document.querySelectorAll('.open-class-detail-modal').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('classDetailModalTitle').textContent = `Kelas ${this.dataset.kelas}`;
                document.getElementById('classDetailAngkatan').textContent = this.dataset.angkatan;
                document.getElementById('classDetailJumlahMahasiswa').textContent = this.dataset.jumlahMahasiswa;
                document.getElementById('classDetailProdi').textContent = this.dataset.prodi;
                document.getElementById('classDetailTahunAkademik').textContent = this.dataset.tahunAkademik;
                document.getElementById('classDetailRataRataIpk').textContent = this.dataset.rataRataIpk;
                document.getElementById('classDetailJumlahMataKuliah').textContent = this.dataset.jumlahMataKuliah;
                gradeLinkForm.action = gradeLinkUrlTemplate.replace('__CLASS_ID__', encodeURIComponent(this.dataset.classId));
                evidenceForm.action = evidenceLinkUrlTemplate.replace('__CLASS_ID__', encodeURIComponent(this.dataset.classId));
                openGradeLinkModalBtn.disabled = false;
                openEvidenceModalBtn.disabled = false;
                showDialog(classDetailModal);
            });
        });

        openEvidenceModalBtn?.addEventListener('click', function () {
            if (this.disabled) return;
            evidenceLinkInput.value = '';
            hideDialog(classDetailModal);
            showDialog(evidenceModal);
            evidenceLinkInput.focus();
        });

        document.getElementById('openGradeLinkModalBtn')?.addEventListener('click', function () {
            gradeLinkInput.value = '';
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

        [classDetailModal, gradeLinkModal, evidenceModal].forEach(dialog => {
            dialog?.addEventListener('click', function (event) {
                if (event.target === dialog) hideDialog(dialog);
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
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
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
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
