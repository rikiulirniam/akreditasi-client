@extends('layouts.app')

@section('title', 'Detail Mahasiswa – ' . ($mahasiswa['nama_mahasiswa'] ?? $mahasiswa['nama'] ?? 'Mahasiswa'))

@section('content')
@php
    $id = $mahasiswa['id'] ?? '';
    $nama = $mahasiswa['nama_mahasiswa'] ?? $mahasiswa['nama'] ?? '-';
    $nim = $mahasiswa['nim_display'] ?? $mahasiswa['nim'] ?? '-';
    $tahunMasuk = $mahasiswa['tahun_masuk'] ?? '-';
    $jenisPendaftaran = $mahasiswa['jenis_pendaftaran'] ?? '-';
    $rawKelulusan = $mahasiswa['tanggal_kelulusan'] ?? '';
    $formattedKelulusan = !empty($rawKelulusan) ? \Carbon\Carbon::parse($rawKelulusan)->format('d M Y') : '-';
    $masaStudi = $mahasiswa['masa_studi_bulan'] ?? 48;
    $masaStudiTahun = round($masaStudi / 12, 1);

    $enrollmentId = $enrollmentDetail['id'] ?? null;
    $kelasNama = $enrollmentDetail['kelas']['nama_kelas'] ?? $enrollmentDetail['nama_kelas'] ?? '-';
    $angkatanNama = $enrollmentDetail['angkatan']['angkatan'] ?? $enrollmentDetail['angkatan'] ?? '-';

    // Latest status
    $latestStatus = collect($statusHistory)->sortByDesc('id')->first();
    $statusTerkini = $latestStatus['status'] ?? '-';
    $statusColor = match($statusTerkini) {
        'Aktif'   => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'Lulus'   => 'bg-blue-100 text-blue-700 border-blue-200',
        'Cuti'    => 'bg-amber-100 text-amber-700 border-amber-200',
        'Non-Aktif', 'Drop Out' => 'bg-red-100 text-red-700 border-red-200',
        default   => 'bg-gray-100 text-gray-500 border-gray-200',
    };
@endphp

<div class="space-y-6">
    <!-- Top Navigation Bar -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Detail Mahasiswa</h2>
        </div>
        <div>
            <a
                href="{{ route('mahasiswa.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary hover:bg-additional text-white text-xs font-bold shadow-xs transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Mahasiswa
            </a>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="bg-white rounded-2xl border border-white-bone shadow-xs p-6 sm:p-8">
        <!-- Card Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                <!-- Avatar initials -->
                <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <span class="text-primary font-extrabold text-xl">{{ strtoupper(substr($nama, 0, 1)) }}</span>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ $nama }}</h3>
                    <p class="text-sm font-mono text-gray-500 mt-0.5">NIM: {{ $nim }}</p>
                    @if($statusTerkini !== '-')
                        <span class="mt-1.5 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusColor }}">
                            {{ $statusTerkini }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Hapus Data -->
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-bold shadow-xs transition cursor-pointer"
                    data-confirm-delete
                    data-action="{{ route('mahasiswa.destroy', $id) }}"
                    data-title="Hapus Data Mahasiswa?"
                    data-message="Apakah Anda yakin ingin menghapus data mahasiswa {{ $nama }} (NIM: {{ $nim }})? Tindakan ini bersifat permanen."
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Data
                </button>

                <!-- Edit Data -->
                <button
                    type="button"
                    id="openEditMahasiswaFromShowBtn"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#EAB308] hover:bg-[#CA8A04] text-white text-xs font-bold shadow-xs transition cursor-pointer"
                    data-action="{{ route('mahasiswa.update', $id) }}"
                    data-nama="{{ $nama }}"
                    data-nim="{{ $nim }}"
                    data-tahun="{{ $tahunMasuk }}"
                    data-jenis="{{ $jenisPendaftaran }}"
                    data-kelulusan="{{ $rawKelulusan }}"
                    data-masa-studi="{{ $masaStudi }}"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Data
                </button>
            </div>
        </div>

        <hr class="border-gray-100 mb-6">

        <!-- Info Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php
                $fields = [
                    ['label' => 'Tahun Masuk',        'value' => $tahunMasuk],
                    ['label' => 'Jenis Pendaftaran',   'value' => $jenisPendaftaran],
                    ['label' => 'Tanggal Kelulusan',   'value' => $formattedKelulusan],
                    ['label' => 'Masa Studi',          'value' => "{$masaStudi} Bulan (~{$masaStudiTahun} Tahun)"],
                    ['label' => 'Kelas',               'value' => $kelasNama],
                    ['label' => 'Angkatan',            'value' => $angkatanNama],
                ];
            @endphp
            @foreach($fields as $field)
                <div class="bg-gray-50/70 rounded-xl px-4 py-3 border border-gray-100">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ $field['label'] }}</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $field['value'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ===== HISTORY STATUS MAHASISWA ===== -->
    <div class="bg-white rounded-2xl border border-white-bone shadow-xs p-6 sm:p-8">
        <!-- Section Header -->
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h3 class="text-base font-bold text-gray-900 tracking-tight">History Status Mahasiswa</h3>
                <p class="text-xs text-gray-400 mt-0.5">Riwayat perubahan status keaktifan mahasiswa per semester</p>
            </div>

            @if($enrollmentId)
                <button
                    type="button"
                    id="openTambahStatusBtn"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary hover:bg-additional text-white text-xs font-bold shadow-xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    + Tambah Status
                </button>
            @else
                <span class="text-xs text-gray-400 italic">Mahasiswa belum terdaftar di kelas (enrollment)</span>
            @endif
        </div>

        <!-- Status Table -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-14">No</x-table.th>
                <x-table.th>Semester</x-table.th>
                <x-table.th>Tahun Akademik</x-table.th>
                <x-table.th class="w-32">Status</x-table.th>
                <x-table.th>Tanggal Dibuat</x-table.th>
                <x-table.th class="w-24 text-right">Aksi</x-table.th>
            </x-slot:header>

            @forelse($statusHistory as $idx => $status)
                @php
                    $statusId = $status['id'] ?? '';
                    $semester = $status['semester'] ?? ('Semester ' . ($idx + 1));
                    $taId = $status['tahun_akademik_id'] ?? null;
                    $matchedTa = null;
                    if ($taId && !empty($tahunAkademikList)) {
                        foreach ($tahunAkademikList as $taItem) {
                            if ((string)($taItem['id'] ?? '') === (string)$taId) {
                                $matchedTa = $taItem;
                                break;
                            }
                        }
                    }
                    $tahunAkademikLabel = $status['tahun_akademik']['tahun_akademik'] ?? ($status['tahun_akademik']['label'] ?? ($matchedTa['tahun_akademik'] ?? ($matchedTa['label'] ?? ($taId ? "ID #{$taId}" : '-'))));
                    $statusVal = $status['status'] ?? '-';
                    $createdAt = !empty($status['created_at']) ? \Carbon\Carbon::parse($status['created_at'])->format('d M Y') : '-';
                    $rowColor = match($statusVal) {
                        'Aktif'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'Lulus'   => 'bg-blue-50 text-blue-700 border-blue-200',
                        'Cuti'    => 'bg-amber-50 text-amber-700 border-amber-200',
                        'Non-Aktif', 'Drop Out' => 'bg-red-50 text-red-700 border-red-200',
                        default   => 'bg-gray-100 text-gray-500 border-gray-200',
                    };
                    $enrollIdForRow = $status['id_enrollment_mahasiswa_angkatan_kelas'] ?? $enrollmentId;
                    $tahunAkademikIdForRow = $status['tahun_akademik_id'] ?? '';
                @endphp
                <x-table.row>
                    <x-table.cell class="text-gray-500 font-medium w-14">{{ $idx + 1 }}</x-table.cell>
                    <x-table.cell>
                        <span class="text-sm font-medium text-gray-800">{{ $semester }}</span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="text-sm text-gray-600">{{ $tahunAkademikLabel }}</span>
                    </x-table.cell>
                    <x-table.cell class="w-32">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $rowColor }}">
                            {{ $statusVal }}
                        </span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="text-sm text-gray-500">{{ $createdAt }}</span>
                    </x-table.cell>
                    <x-table.cell class="w-24">
                        <div class="flex items-center justify-end gap-2">
                            <!-- Edit status button -->
                            <button
                                type="button"
                                class="open-edit-status-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Status"
                                data-id="{{ $statusId }}"
                                data-action="{{ route('mahasiswa.status.update', [$id, $statusId]) }}"
                                data-enrollment="{{ $enrollIdForRow }}"
                                data-semester="{{ $semester }}"
                                data-tahun-akademik-id="{{ $tahunAkademikIdForRow }}"
                                data-status="{{ $statusVal }}"
                            >
                                <x-icon.edit class="w-4 h-4" />
                            </button>

                            <!-- Delete status button -->
                            <button
                                type="button"
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Status"
                                data-confirm-delete
                                data-action="{{ route('mahasiswa.status.destroy', [$id, $statusId]) }}"
                                data-title="Hapus Status?"
                                data-message="Apakah Anda yakin ingin menghapus status '{{ $statusVal }}' pada {{ $semester }}? Tindakan ini tidak dapat dibatalkan."
                            >
                                <x-icon.delete class="w-4 h-4" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="6" message="Belum ada riwayat status" submessage="Klik tombol '+ Tambah Status' untuk menambahkan status mahasiswa." />
            @endforelse
        </x-table>
    </div>
</div>

{{-- =========================================================== --}}
{{-- MODAL: Edit Data Mahasiswa (dari halaman detail) --}}
{{-- =========================================================== --}}
<div
    id="editMahasiswaFromShowModal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editMahasiswaFromShowModalTitle"
>
    <div class="show-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editMahasiswaFromShowModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Edit Data Mahasiswa
        </h3>

        <form id="editMahasiswaFromShowForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_to" value="show">

            <div>
                <label for="showEditNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">Nama Mahasiswa</label>
                <input type="text" name="nama_mahasiswa" id="showEditNamaInput" required
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500">
            </div>

            <div>
                <label for="showEditNimInput" class="block text-xs font-bold text-gray-900 mb-1.5">NIM</label>
                <input type="text" name="nim" id="showEditNimInput" required
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500">
            </div>

            <div>
                <label for="showEditTahunInput" class="block text-xs font-bold text-gray-900 mb-1.5">Tahun Masuk</label>
                <div class="relative">
                    <select name="tahun_masuk" id="showEditTahunInput" required
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer">
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

            <div>
                <label for="showEditJenisInput" class="block text-xs font-bold text-gray-900 mb-1.5">Jenis Pendaftaran</label>
                <div class="relative">
                    <select name="jenis_pendaftaran" id="showEditJenisInput" required
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer">
                        <option value="" disabled>Pilih Jenis Pendaftaran</option>
                        @foreach(['SNBP','SNBT','Mandiri','Reguler','Transfer','Kerjasama'] as $j)
                            <option value="{{ $j }}">{{ $j }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="showEditKelulusanInput" class="block text-xs font-bold text-gray-900 mb-1.5">Tanggal Kelulusan</label>
                <input type="date" name="tanggal_kelulusan" id="showEditKelulusanInput"
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none cursor-pointer">
            </div>

            <div>
                <label for="showEditMasaStudiInput" class="block text-xs font-bold text-gray-900 mb-1.5">Masa Studi (Bulan)</label>
                <input type="number" name="masa_studi_bulan" id="showEditMasaStudiInput" min="1" max="120" required
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500">
            </div>

            <div class="grid grid-cols-2 gap-4 pt-4">
                <button type="button" class="close-show-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center">
                    Batal
                </button>
                <button type="submit" class="w-full py-3 px-6 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =========================================================== --}}
{{-- MODAL: Tambah Status Mahasiswa --}}
{{-- =========================================================== --}}
<div
    id="tambahStatusModal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="tambahStatusModalTitle"
>
    <div class="show-modal-card relative bg-white rounded-[28px] p-8 max-w-[440px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="tambahStatusModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambah Status Mahasiswa
        </h3>

        <form action="{{ route('mahasiswa.status.store', $id) }}" method="POST" class="space-y-4">
            @csrf

            <input type="hidden" name="id_enrollment_mahasiswa_angkatan_kelas" value="{{ $enrollmentId }}">

            <div>
                <label for="tambahStatusSemesterInput" class="block text-xs font-bold text-gray-900 mb-1.5">Semester</label>
                <div class="relative">
                    <select name="semester" id="tambahStatusSemesterInput" required
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer">
                        <option value="" disabled selected>Pilih Semester</option>
                        @foreach($semesterOptions as $sem)
                            <option value="{{ $sem }}">{{ $sem }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="tambahStatusTahunAkademikInput" class="block text-xs font-bold text-gray-900 mb-1.5">Tahun Akademik <span class="text-gray-400 font-normal">(opsional)</span></label>
                <div class="relative">
                    <select name="tahun_akademik_id" id="tambahStatusTahunAkademikInput"
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer">
                        <option value="">-- Tidak Dipilih --</option>
                        @foreach($tahunAkademikList as $ta)
                            <option value="{{ $ta['id'] }}">{{ $ta['tahun_akademik'] ?? $ta['label'] ?? "ID {$ta['id']}" }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="tambahStatusStatusInput" class="block text-xs font-bold text-gray-900 mb-1.5">Status</label>
                <div class="relative">
                    <select name="status" id="tambahStatusStatusInput" required
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer">
                        <option value="" disabled selected>Pilih Status</option>
                        @foreach($statusOptions as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-4">
                <button type="button" class="close-show-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center">
                    Batal
                </button>
                <button type="submit" class="w-full py-3 px-6 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =========================================================== --}}
{{-- MODAL: Edit Status Mahasiswa --}}
{{-- =========================================================== --}}
<div
    id="editStatusModal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editStatusModalTitle"
>
    <div class="show-modal-card relative bg-white rounded-[28px] p-8 max-w-[440px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editStatusModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Edit Status Mahasiswa
        </h3>

        <form id="editStatusForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" id="editStatusEnrollmentInput" name="id_enrollment_mahasiswa_angkatan_kelas" value="">

            <div>
                <label for="editStatusSemesterInput" class="block text-xs font-bold text-gray-900 mb-1.5">Semester</label>
                <div class="relative">
                    <select name="semester" id="editStatusSemesterInput" required
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer">
                        <option value="" disabled>Pilih Semester</option>
                        @foreach($semesterOptions as $sem)
                            <option value="{{ $sem }}">{{ $sem }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="editStatusTahunAkademikInput" class="block text-xs font-bold text-gray-900 mb-1.5">Tahun Akademik <span class="text-gray-400 font-normal">(opsional)</span></label>
                <div class="relative">
                    <select name="tahun_akademik_id" id="editStatusTahunAkademikInput"
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer">
                        <option value="">-- Tidak Dipilih --</option>
                        @foreach($tahunAkademikList as $ta)
                            <option value="{{ $ta['id'] }}">{{ $ta['tahun_akademik'] ?? $ta['label'] ?? "ID {$ta['id']}" }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="editStatusStatusInput" class="block text-xs font-bold text-gray-900 mb-1.5">Status</label>
                <div class="relative">
                    <select name="status" id="editStatusStatusInput" required
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer">
                        <option value="" disabled>Pilih Status</option>
                        @foreach($statusOptions as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-4">
                <button type="button" class="close-show-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center">
                    Batal
                </button>
                <button type="submit" class="w-full py-3 px-6 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center">
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ===== Modal Helpers =====
    function showModal(modalEl) {
        if (!modalEl) return;
        const card = modalEl.querySelector('.show-modal-card');
        modalEl.classList.remove('opacity-0', 'pointer-events-none');
        modalEl.classList.add('opacity-100');
        if (card) { card.classList.remove('scale-95'); card.classList.add('scale-100'); }
    }

    function hideModal(modalEl) {
        if (!modalEl) return;
        const card = modalEl.querySelector('.show-modal-card');
        modalEl.classList.remove('opacity-100');
        modalEl.classList.add('opacity-0', 'pointer-events-none');
        if (card) { card.classList.remove('scale-100'); card.classList.add('scale-95'); }
    }

    const editMahasiswaModal  = document.getElementById('editMahasiswaFromShowModal');
    const tambahStatusModal   = document.getElementById('tambahStatusModal');
    const editStatusModal     = document.getElementById('editStatusModal');
    const allModals = [editMahasiswaModal, tambahStatusModal, editStatusModal];

    // ===== Edit Mahasiswa (from show page) =====
    const editBtn = document.getElementById('openEditMahasiswaFromShowBtn');
    if (editBtn) {
        editBtn.addEventListener('click', function () {
            const form = document.getElementById('editMahasiswaFromShowForm');
            if (form) form.action = this.getAttribute('data-action') || '';
            document.getElementById('showEditNamaInput').value    = this.getAttribute('data-nama') || '';
            document.getElementById('showEditNimInput').value     = this.getAttribute('data-nim') || '';
            document.getElementById('showEditTahunInput').value   = this.getAttribute('data-tahun') || '';
            document.getElementById('showEditJenisInput').value   = this.getAttribute('data-jenis') || '';
            document.getElementById('showEditKelulusanInput').value = this.getAttribute('data-kelulusan') || '';
            document.getElementById('showEditMasaStudiInput').value = this.getAttribute('data-masa-studi') || '48';
            showModal(editMahasiswaModal);
            setTimeout(() => document.getElementById('showEditNamaInput')?.focus(), 100);
        });
    }

    // ===== Tambah Status =====
    const tambahBtn = document.getElementById('openTambahStatusBtn');
    if (tambahBtn) {
        tambahBtn.addEventListener('click', function () {
            // Reset the form
            const form = tambahStatusModal?.querySelector('form');
            if (form) {
                form.querySelector('#tambahStatusSemesterInput').value = '';
                form.querySelector('#tambahStatusTahunAkademikInput').value = '';
                form.querySelector('#tambahStatusStatusInput').value = '';
            }
            showModal(tambahStatusModal);
        });
    }

    // ===== Edit Status =====
    document.querySelectorAll('.open-edit-status-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = document.getElementById('editStatusForm');
            if (form) form.action = this.getAttribute('data-action') || '';
            document.getElementById('editStatusEnrollmentInput').value = this.getAttribute('data-enrollment') || '';
            document.getElementById('editStatusSemesterInput').value = this.getAttribute('data-semester') || '';
            document.getElementById('editStatusTahunAkademikInput').value = this.getAttribute('data-tahun-akademik-id') || '';
            document.getElementById('editStatusStatusInput').value = this.getAttribute('data-status') || '';
            showModal(editStatusModal);
        });
    });

    // ===== Close Buttons =====
    document.querySelectorAll('.close-show-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            hideModal(this.closest('[role="dialog"]'));
        });
    });

    // ===== Close on backdrop click =====
    allModals.forEach(m => {
        m?.addEventListener('click', function (e) {
            if (e.target === m) hideModal(m);
        });
    });

    // ===== Close on Escape =====
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') allModals.forEach(m => hideModal(m));
    });
});
</script>
@endsection
