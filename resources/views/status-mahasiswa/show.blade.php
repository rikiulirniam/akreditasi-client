@extends('layouts.app')

@section('title', 'Detail Status Mahasiswa')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Detail Status Mahasiswa</h2>
            <p class="text-xs text-gray-500 mt-0.5">Informasi status keaktifan mahasiswa</p>
        </div>
        <div>
            <a
                href="{{ route('status-mahasiswa.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary hover:bg-additional text-white text-xs font-bold shadow-xs transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-white-bone shadow-xs p-6 sm:p-8">
        <!-- Card Header: Title & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                @php
                    $namaMhs = $statusData['nama_mahasiswa'] !== '-' ? $statusData['nama_mahasiswa'] : ($enrollmentDetail['nama_mahasiswa'] ?? 'Mahasiswa');
                    $nimMhs = $statusData['nim'] !== '-' ? $statusData['nim'] : ($enrollmentDetail['nim'] ?? '-');
                    $status = $statusData['status'] ?? 'Aktif';
                    $statusColor = match($status) {
                        'Aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'Lulus' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'Cuti' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'Drop Out' => 'bg-red-50 text-red-700 border-red-200',
                        default => 'bg-gray-100 text-gray-700 border-gray-200',
                    };
                @endphp
                <h3 class="text-xl font-bold text-gray-900 tracking-tight">
                    {{ $namaMhs }}
                </h3>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-mono text-gray-500">{{ $nimMhs }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusColor }}">
                        {{ $status }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <!-- Hapus Data Button -->
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-bold shadow-xs transition cursor-pointer"
                    data-confirm-delete
                    data-action="{{ route('status-mahasiswa.destroy', $statusData['id']) }}"
                    data-title="Hapus Status Mahasiswa?"
                    data-message="Apakah Anda yakin ingin menghapus data status mahasiswa ini? Tindakan ini bersifat permanen."
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Data
                </button>

                <!-- Edit Data Button -->
                <a
                    href="{{ route('status-mahasiswa.edit', $statusData['id']) }}"
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
                <!-- Nama Mahasiswa -->
                <div class="flex items-start gap-3">
                    <span class="text-gray-500 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Nama Mahasiswa:</span>
                        {{ $namaMhs }}
                    </p>
                </div>

                <!-- NIM -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">NIM:</span>
                        <span class="font-mono">{{ $nimMhs }}</span>
                    </p>
                </div>

                <!-- ID Enrollment -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">ID Enrollment:</span>
                        #{{ $statusData['id_enrollment_mahasiswa_angkatan_kelas'] ?? '-' }}
                    </p>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="space-y-4">
                <!-- Kelas & Angkatan -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Kelas / Angkatan:</span>
                        @php
                            $kls = $statusData['nama_kelas'] !== '-' ? $statusData['nama_kelas'] : ($enrollmentDetail['nama_kelas'] ?? '-');
                            $ang = $statusData['nama_angkatan'] !== '-' ? $statusData['nama_angkatan'] : ($enrollmentDetail['nama_angkatan'] ?? '-');
                        @endphp
                        @if($kls !== '-' && $ang !== '-')
                            <span class="font-semibold text-gray-900">{{ $kls }}</span>
                            <span class="text-gray-500">(Angkatan {{ $ang }})</span>
                        @elseif($kls !== '-')
                            <span class="font-semibold text-gray-900">{{ $kls }}</span>
                        @elseif($ang !== '-')
                            <span class="text-gray-700">Angkatan {{ $ang }}</span>
                        @else
                            -
                        @endif
                    </p>
                </div>

                <!-- Program Studi -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Program Studi:</span>
                        {{ $statusData['nama_prodi'] !== '-' ? $statusData['nama_prodi'] : ($enrollmentDetail['nama_prodi'] ?? 'Teknologi Rekayasa Komputer') }}
                    </p>
                </div>

                <!-- Status Keaktifan -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Status Keaktifan:</span>
                        <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusColor }}">
                            {{ $status }}
                        </span>
                    </p>
                </div>

                <!-- Waktu Dibuat / Diperbarui -->
                @if(!empty($statusData['created_at']))
                    <div class="flex items-center gap-3">
                        <span class="text-gray-500 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <p class="text-gray-700">
                            <span class="font-bold text-gray-900">Tanggal Dicatat:</span>
                            {{ \Carbon\Carbon::parse($statusData['created_at'])->translatedFormat('d F Y H:i') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
