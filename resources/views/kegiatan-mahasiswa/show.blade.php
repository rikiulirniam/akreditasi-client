@extends('layouts.app')

@section('title', 'Detail Kegiatan Mahasiswa')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Detail Kegiatan Mahasiswa</h2>
        </div>
        <div>
            <a
                href="{{ route('kegiatan-mahasiswa.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary hover:bg-additional text-white text-xs font-bold shadow-xs transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Kegiatan Mahasiswa
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-white-bone shadow-xs p-6 sm:p-8">
        <!-- Card Header: Title & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-xl font-bold text-gray-900 tracking-tight">
                    {{ $kegiatan['nama_kegiatan'] ?? '-' }}
                </h3>
                @php
                    $statusColor = match($kegiatan['status_validasi'] ?? 'Pending') {
                        'Disetujui' => 'bg-green-100 text-green-700 border-green-200',
                        'Ditolak' => 'bg-red-100 text-red-700 border-red-200',
                        default => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    };
                @endphp
                <span class="mt-1 inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold border {{ $statusColor }}">
                    {{ $kegiatan['status_validasi'] ?? 'Pending' }}
                </span>
            </div>

            <div class="flex items-center gap-2.5">
                <!-- Hapus Data Button -->
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-bold shadow-xs transition cursor-pointer"
                    data-confirm-delete
                    data-action="{{ route('kegiatan-mahasiswa.destroy', $kegiatan['id']) }}"
                    data-title="Hapus Kegiatan Mahasiswa?"
                    data-message="Apakah Anda yakin ingin menghapus kegiatan '{{ $kegiatan['nama_kegiatan'] }}'? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Data
                </button>

                <!-- Edit Data Button -->
                <a
                    href="{{ route('kegiatan-mahasiswa.edit', $kegiatan['id']) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#EAB308] hover:bg-[#CA8A04] text-white text-xs font-bold shadow-xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Data
                </a>
            </div>
        </div>

        <hr class="border-gray-200 my-5">

        <!-- Card Body: 2 Columns Info -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 text-xs">
            <!-- Kolom Kiri -->
            <div class="space-y-4">
                <!-- Mahasiswa -->
                <div class="flex items-start gap-3">
                    <span class="text-gray-500 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Mahasiswa:</span>
                        @if($mahasiswaDetail)
                            {{ $mahasiswaDetail['nama'] ?? $mahasiswaDetail['nama_mahasiswa'] ?? '-' }}
                            <span class="text-gray-500">({{ $mahasiswaDetail['nim'] ?? '-' }})</span>
                        @else
                            {{ $kegiatan['nama_mahasiswa'] ?? '-' }}
                            @if($kegiatan['nim'] && $kegiatan['nim'] !== '-')
                                <span class="text-gray-500">({{ $kegiatan['nim'] }})</span>
                            @endif
                        @endif
                    </p>
                </div>

                <!-- Tahun Akademik -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Tahun Akademik:</span>
                        @php
                            $tYear = $tahunDetail['tahun'] ?? $tahunDetail['tahun_akademik'] ?? $tahunDetail['nama'] ?? '';
                            $tSem = $tahunDetail['semester'] ?? '';
                            $taText = trim($tYear . ($tSem ? ' (' . $tSem . ')' : ''));
                            if (empty($taText)) {
                                $taText = (!empty($kegiatan['nama_tahun_akademik']) && $kegiatan['nama_tahun_akademik'] !== '-') ? $kegiatan['nama_tahun_akademik'] : '-';
                            }
                        @endphp
                        {{ $taText }}
                    </p>
                </div>

                <!-- Jenis Kegiatan -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Jenis Kegiatan:</span> {{ $kegiatan['jenis_kegiatan'] ?? '-' }}
                    </p>
                </div>

                <!-- Peran -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Peran:</span> {{ $kegiatan['peran'] ?? '-' }}
                    </p>
                </div>

                <!-- Tingkat -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Tingkat:</span> {{ $kegiatan['tingkat'] ?? '-' }}
                    </p>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="space-y-4">
                <!-- Penyelenggara -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Penyelenggara:</span> {{ $kegiatan['penyelenggara'] ?? '-' }}
                    </p>
                </div>

                <!-- Tanggal Mulai -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Tanggal Mulai:</span>
                        {{ $kegiatan['tanggal_mulai'] ? \Carbon\Carbon::parse($kegiatan['tanggal_mulai'])->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>

                <!-- Tanggal Selesai -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Tanggal Selesai:</span>
                        {{ $kegiatan['tanggal_selesai'] ? \Carbon\Carbon::parse($kegiatan['tanggal_selesai'])->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>

                <!-- Status Validasi -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Status Validasi:</span>
                        <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusColor }}">
                            {{ $kegiatan['status_validasi'] ?? 'Pending' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
