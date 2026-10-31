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
    $formattedKelulusan = '-';
    if (!empty($rawKelulusan)) {
        try {
            $formattedKelulusan = \Carbon\Carbon::parse($rawKelulusan)->locale('id')->isoFormat('D MMMM Y');
        } catch (\Throwable) {
            $formattedKelulusan = \Carbon\Carbon::parse($rawKelulusan)->format('d-m-Y');
        }
    }
    $masaStudi = (int) ($mahasiswa['masa_studi_bulan'] ?? 48);
    if ($masaStudi > 0) {
        if ($masaStudi % 12 === 0) {
            $tahunCount = (int) ($masaStudi / 12);
            $masaStudiDisplay = "{$tahunCount} Tahun";
        } else {
            $tahunCount = round($masaStudi / 12, 1);
            $masaStudiDisplay = "{$tahunCount} Tahun";
        }
    } else {
        $masaStudiDisplay = '-';
    }

    $enrollmentId = $enrollmentDetail['id'] ?? null;
    $kelasNama = $enrollmentDetail['kelas']['nama_kelas'] ?? $enrollmentDetail['kelas']['kelas'] ?? $enrollmentDetail['nama_kelas'] ?? '-';
    $angkatanNama = $enrollmentDetail['angkatan']['angkatan'] ?? $enrollmentDetail['angkatan']['tahun'] ?? $enrollmentDetail['nama_angkatan'] ?? (is_scalar($enrollmentDetail['angkatan'] ?? null) ? $enrollmentDetail['angkatan'] : '-');

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

    // Helper to format Tahun Akademik: e.g. 2026/2027 Gasal, 2026/2027 Genap
    $formatTa = function ($ta, $fallbackSemester = null) {
        if (! $ta) {
            return '-';
        }
        $rawYear = (string) (is_array($ta) ? ($ta['tahun'] ?? $ta['tahun_akademik'] ?? $ta['nama'] ?? '') : $ta);
        $rawSem = (string) (is_array($ta) ? ($ta['semester'] ?? $fallbackSemester ?? '') : ($fallbackSemester ?? ''));

        if (empty($rawSem) && preg_match('/(ganjil|gasal|genap)/i', $rawYear, $m)) {
            $rawSem = $m[1];
            $rawYear = trim(preg_replace('/(ganjil|gasal|genap)/i', '', $rawYear));
        }

        if (empty($rawSem) && ! empty($fallbackSemester)) {
            if (preg_match('/[13579]/', (string) $fallbackSemester) || preg_match('/(ganjil|gasal)/i', (string) $fallbackSemester)) {
                $rawSem = 'Gasal';
            } elseif (preg_match('/[24680]/', (string) $fallbackSemester) || preg_match('/genap/i', (string) $fallbackSemester)) {
                $rawSem = 'Genap';
            }
        }

        $semName = '';
        if (! empty($rawSem)) {
            if (strcasecmp($rawSem, 'ganjil') === 0 || strcasecmp($rawSem, 'gasal') === 0) {
                $semName = 'Gasal';
            } elseif (strcasecmp($rawSem, 'genap') === 0) {
                $semName = 'Genap';
            } else {
                $semName = ucfirst(trim($rawSem));
            }
        }

        $cleanedYear = trim(preg_replace('/[^\d\/]/', '', $rawYear));
        if (preg_match('/^\d{4}$/', $cleanedYear)) {
            $y = (int) $cleanedYear;
            $formattedYear = $y . '/' . ($y + 1);
        } elseif (preg_match('/^\d{4}\/\d{2,4}$/', $cleanedYear)) {
            if (strlen($cleanedYear) === 7) {
                $parts = explode('/', $cleanedYear);
                $formattedYear = $parts[0] . '/' . substr($parts[0], 0, 2) . $parts[1];
            } else {
                $formattedYear = $cleanedYear;
            }
        } else {
            $formattedYear = $cleanedYear ?: $rawYear;
        }

        if (! empty($formattedYear) && ! empty($semName)) {
            return "{$formattedYear} {$semName}";
        }
        if (! empty($formattedYear)) {
            return $formattedYear;
        }
        if (! empty($semName)) {
            return $semName;
        }

        return '-';
    };
@endphp

<div class="space-y-6">
    <!-- Top Navigation Bar -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 tracking-tight">Detail Mahasiswa</h2>
        </div>
        <div>
            <a
                href="{{ route('mahasiswa.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#00476E] hover:bg-additional text-white text-xs font-bold shadow-xs transition cursor-pointer"
            >
                <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Mahasiswa
            </a>
        </div>
    </div>

    <!-- Profile Card (Sesuai Gambar) -->
    <div class="bg-[#F8F9FA] rounded-[22px] border border-gray-200/90 shadow-xs p-6 sm:p-8">
        <!-- Top Row: Student Name & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                    {{ $nama }}
                </h3>
            </div>

            <!-- Action Buttons: Hapus Data & Edit Data -->
            <div class="flex items-center gap-3 shrink-0">
                <!-- Hapus Data Button (Red) -->
                <button
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#BA2626] hover:bg-[#991B1B] text-white text-sm font-bold shadow-xs transition cursor-pointer"
                    data-confirm-delete
                    data-action="{{ route('mahasiswa.destroy', $id) }}"
                    data-title="Hapus Data Mahasiswa?"
                    data-message="Apakah Anda yakin ingin menghapus data mahasiswa {{ $nama }} (NIM: {{ $nim }})? Tindakan ini bersifat permanen."
                >
                    <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                    </svg>
                    Hapus Data
                </button>

                <!-- Edit Data Button (Yellow) -->
                <button
                    type="button"
                    id="openEditMahasiswaFromShowBtn"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#F59E0B] hover:bg-[#D97706] text-white text-sm font-bold shadow-xs transition cursor-pointer"
                    data-action="{{ route('mahasiswa.update', $id) }}"
                    data-nama="{{ $nama }}"
                    data-nim="{{ $nim }}"
                    data-tahun="{{ $tahunMasuk }}"
                    data-jenis="{{ $jenisPendaftaran }}"
                    data-kelulusan="{{ $rawKelulusan }}"
                    data-masa-studi="{{ $masaStudi }}"
                >
                    <svg class="w-4 h-4 stroke-[2.2] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Data
                </button>
            </div>
        </div>

        <!-- Divider Line -->
        <hr class="border-t border-gray-200/90 my-6">

        <!-- Two Columns Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-12">
            <!-- Left Column -->
            <div class="space-y-4">
                <!-- NIM -->
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 flex items-center justify-center shrink-0 text-slate-500">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2L1 7l11 5 9-4.09V14h2V7L12 2zm0 8c1.66 0 3-1.34 3-3V6.26l-3-1.36-3 1.36V7c0 1.66 1.34 3 3 3zm6 7.5v-1c0-2-4-3-6-3s-6 1-6 3v1h12z"/>
                        </svg>
                    </span>
                    <div class="text-sm">
                        <span class="font-bold text-gray-900">NIM:</span>
                        <span class="text-gray-800 ml-1.5 font-normal">{{ $nim }}</span>
                    </div>
                </div>

                <!-- Kelas -->
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 flex items-center justify-center shrink-0 text-slate-500">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/>
                        </svg>
                    </span>
                    <div class="text-sm">
                        <span class="font-bold text-gray-900">Kelas:</span>
                        <span class="text-gray-800 ml-1.5 font-normal">{{ $kelasNama }}</span>
                    </div>
                </div>

                <!-- Angkatan -->
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 flex items-center justify-center shrink-0 text-slate-500">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-6 4h-2v2H9v2h2v2h2v-2h2v-2h-2v-2z"/>
                        </svg>
                    </span>
                    <div class="text-sm">
                        <span class="font-bold text-gray-900">Angkatan:</span>
                        <span class="text-gray-800 ml-1.5 font-normal">{{ $angkatanNama }}</span>
                    </div>
                </div>

                <!-- Jenis Pendaftaran -->
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 flex items-center justify-center shrink-0 text-slate-500">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                        </svg>
                    </span>
                    <div class="text-sm">
                        <span class="font-bold text-gray-900">Jenis Pendaftaran:</span>
                        <span class="text-gray-800 ml-1.5 font-normal">{{ $jenisPendaftaran }}</span>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
                <!-- Tahun Masuk -->
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 flex items-center justify-center shrink-0 text-slate-500">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                    </span>
                    <div class="text-sm">
                        <span class="font-bold text-gray-900">Tahun Masuk:</span>
                        <span class="text-gray-800 ml-1.5 font-normal">{{ $tahunMasuk }}</span>
                    </div>
                </div>

                <!-- Tanggal Kelulusan -->
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 flex items-center justify-center shrink-0 text-slate-500">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2a6 6 0 0 0-6 6c0 2.22 1.21 4.16 3 5.2V22l3-2 3 2v-8.8c1.79-1.04 3-2.98 3-5.2a6 6 0 0 0-6-6zm0 9a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                        </svg>
                    </span>
                    <div class="text-sm">
                        <span class="font-bold text-gray-900">Tanggal Kelulusan:</span>
                        <span class="text-gray-800 ml-1.5 font-normal">{{ $formattedKelulusan }}</span>
                    </div>
                </div>

                <!-- Masa Studi -->
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 flex items-center justify-center shrink-0 text-slate-500">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M15 1H9v2h6V1zm-4 13h2V8h-2v6zm8.03-6.61l1.42-1.42c-.43-.51-.9-.99-1.41-1.41l-1.42 1.42A8.962 8.962 0 0 0 12 4c-4.97 0-9 4.03-9 9s4.02 9 9 9a8.994 8.994 0 0 0 7.03-14.61zM12 20c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
                        </svg>
                    </span>
                    <div class="text-sm">
                        <span class="font-bold text-gray-900">Masa Studi:</span>
                        <span class="text-gray-800 ml-1.5 font-normal">{{ $masaStudiDisplay }}</span>
                    </div>
                </div>
            </div>
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
                    Tambah Status
                </button>
            @else
                <span class="text-xs text-gray-400 italic">Mahasiswa belum terdaftar di kelas (enrollment)</span>
            @endif
        </div>

        <!-- Status Table -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-14">No</x-table.th>
                <x-table.th>Tahun Akademik</x-table.th>
                <x-table.th class="w-32">Status</x-table.th>
                <x-table.th>Tanggal Dibuat</x-table.th>
                <x-table.th class="w-24 text-right">Aksi</x-table.th>
            </x-slot:header>

            @forelse($statusHistory as $idx => $status)
                @php
                    $statusId = $status['id'] ?? '';
                    $semester = $status['semester'] ?? ($idx % 2 === 0 ? 'Gasal' : 'Genap');
                    $taId = $status['tahun_akademik_id'] ?? null;
                    $matchedTa = null;
                    if ($taId && !empty($tahunAkademikList)) {
                        foreach ($tahunAkademikList as $taItem) {
                            if ((string)($taItem['id'] ?? '') === (string)$taId) {
                                if (empty($semester) || empty($taItem['semester']) || strcasecmp($taItem['semester'], $semester) === 0) {
                                    $matchedTa = $taItem;
                                    break;
                                }
                            }
                        }
                        if (! $matchedTa) {
                            foreach ($tahunAkademikList as $taItem) {
                                if ((string)($taItem['id'] ?? '') === (string)$taId) {
                                    $matchedTa = $taItem;
                                    break;
                                }
                            }
                        }
                    }
                    $taSource = $status['tahun_akademik'] ?? $matchedTa ?? null;
                    $tahunAkademikLabel = $formatTa($taSource, $semester);
                    if ($tahunAkademikLabel === '-' && $taId) {
                        $tahunAkademikLabel = "ID #{$taId}";
                    }
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
                        <span class="text-sm font-semibold text-gray-800">{{ $tahunAkademikLabel }}</span>
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
                                data-tahun-akademik-id="{{ $tahunAkademikIdForRow }}"
                                data-semester="{{ $status['semester'] ?? $semester }}"
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
                                data-message="Apakah Anda yakin ingin menghapus status '{{ $statusVal }}' pada {{ $tahunAkademikLabel }}? Tindakan ini tidak dapat dibatalkan."
                            >
                                <x-icon.delete class="w-4 h-4" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="5" message="Belum ada riwayat status" submessage="Klik tombol '+ Tambah Status' untuk menambahkan status mahasiswa." />
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
                <label for="tambahStatusTahunAkademikInput" class="block text-xs font-bold text-gray-900 mb-1.5">Tahun Akademik</label>
                <div class="relative">
                    <select name="tahun_akademik_id" id="tambahStatusTahunAkademikInput" required
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer">
                        <option value="" disabled selected>Pilih Tahun Akademik</option>
                        @foreach($tahunAkademikList as $ta)
                            @php
                                $optVal = $ta['option_key'] ?? ($ta['id'] . (!empty($ta['semester']) ? '_' . $ta['semester'] : ''));
                                $taLabel = $formatTa($ta);
                                if ($taLabel === '-' || empty($taLabel)) {
                                    $taLabel = "ID #{$ta['id']}";
                                }
                            @endphp
                            <option value="{{ $optVal }}">{{ $taLabel }}</option>
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
                <label for="editStatusTahunAkademikInput" class="block text-xs font-bold text-gray-900 mb-1.5">Tahun Akademik</label>
                <div class="relative">
                    <select name="tahun_akademik_id" id="editStatusTahunAkademikInput" required
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer">
                        <option value="" disabled>Pilih Tahun Akademik</option>
                        @foreach($tahunAkademikList as $ta)
                            @php
                                $optValEdit = $ta['option_key'] ?? ($ta['id'] . (!empty($ta['semester']) ? '_' . $ta['semester'] : ''));
                                $taLabelEdit = $formatTa($ta);
                                if ($taLabelEdit === '-' || empty($taLabelEdit)) {
                                    $taLabelEdit = "ID #{$ta['id']}";
                                }
                            @endphp
                            <option value="{{ $optValEdit }}">{{ $taLabelEdit }}</option>
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
                const taInput = form.querySelector('#tambahStatusTahunAkademikInput');
                if (taInput) taInput.value = '';
                const stInput = form.querySelector('#tambahStatusStatusInput');
                if (stInput) stInput.value = '';
            }
            showModal(tambahStatusModal);
        });
    }

    // ===== Edit Status =====
    document.querySelectorAll('.open-edit-status-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = document.getElementById('editStatusForm');
            if (form) form.action = this.getAttribute('data-action') || '';
            const enrollInput = document.getElementById('editStatusEnrollmentInput');
            if (enrollInput) enrollInput.value = this.getAttribute('data-enrollment') || '';
            
            const taId = this.getAttribute('data-tahun-akademik-id') || '';
            const sem = this.getAttribute('data-semester') || '';
            const taInput = document.getElementById('editStatusTahunAkademikInput');
            if (taInput) {
                const comboVal = sem ? `${taId}_${sem}` : taId;
                let opt = taInput.querySelector(`option[value="${comboVal}"]`);
                if (!opt && sem) {
                    opt = taInput.querySelector(`option[value^="${taId}_"]`);
                }
                if (!opt) {
                    opt = taInput.querySelector(`option[value="${taId}"]`);
                }
                taInput.value = opt ? opt.value : '';
            }

            const stInput = document.getElementById('editStatusStatusInput');
            if (stInput) stInput.value = this.getAttribute('data-status') || '';
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
