@extends('layouts.app')

@section('browser-title', 'Detail Kerja Sama')

@section('title', 'Kerja Sama')

@section('content')
@php
    $partnerName = $cooperation['nama_mitra']
        ?? $cooperation['nama_instansi']
        ?? $cooperation['mitra']['nama_mitra']
        ?? $cooperation['mitra']['nama_instansi']
        ?? '-';
    $lecturerName = $cooperation['nama_dosen']
        ?? $cooperation['dosen']['nama_dosen']
        ?? '-';
    $documentType = $cooperation['jenis_dokumen']
        ?? $cooperation['nama_jenis_kerjasama']
        ?? $cooperation['jenis_kerjasama']['nama_jenis_kerjasama']
        ?? '-';
    $status = $cooperation['status'] ?? '-';
    $statusClass = match (strtolower((string) $status)) {
        'aktif' => 'bg-emerald-600',
        'sedang proses' => 'bg-amber-500',
        'non-aktif' => 'bg-red-600',
        default => 'bg-gray-500',
    };
    $startDate = $cooperation['tanggal_mulai'] ?? null;
    $endDate = $cooperation['tanggal_selesai'] ?? null;
    $startYear = $startDate ? substr((string) $startDate, 0, 4) : '-';
    $endYear = $endDate ? substr((string) $endDate, 0, 4) : '-';
    $documentUrl = trim((string) ($cooperation['bukti_dokumen'] ?? ''));
    $isDocumentUrl = preg_match('/^https?:\/\//i', $documentUrl) === 1;
    $documentName = $isDocumentUrl
        ? basename((string) (parse_url($documentUrl, PHP_URL_PATH) ?: 'Bukti dokumen'))
        : 'Bukti dokumen kerja sama';
@endphp

<div class="space-y-5 pb-8">
    <nav class="flex items-center gap-2 text-sm text-gray-600" aria-label="Breadcrumb">
        <a href="{{ route('kerja-sama.index') }}" class="font-semibold hover:text-primary">Kerja Sama</a>
        <span aria-hidden="true" class="text-xl text-gray-400">›</span>
        <span class="font-semibold text-gray-900">Detail</span>
    </nav>

    <a href="{{ route('kerja-sama.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700 transition hover:text-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Kembali ke daftar
    </a>

    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-8">
        <header class="flex flex-col gap-4 border-b border-gray-200 pb-6 sm:flex-row sm:items-center">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[#edf4f8] text-additional">
                <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 12.75 5.5 15.5a2.121 2.121 0 0 1-3-3L7.25 7.75a4.243 4.243 0 0 1 6 0l.75.75m-3 3 5.75 5.75a2.121 2.121 0 0 0 3-3l-5-5m-2.5 5.5 1.75 1.75a2.121 2.121 0 0 0 3-3m-7 0 2.25 2.25a2.121 2.121 0 0 0 3-3M3.5 8.5l-1 1m18-1-1-1" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-xl font-extrabold tracking-tight text-gray-950 sm:text-2xl">{{ $cooperation['judul_kerja_sama'] ?? 'Detail Kerja Sama' }}</h2>
                <p class="mt-1 text-sm font-semibold text-gray-600">{{ $partnerName }}</p>
            </div>
            <span class="inline-flex min-w-20 items-center justify-center rounded-md px-4 py-1.5 text-sm font-semibold text-white {{ $statusClass }}">{{ $status }}</span>
        </header>

        <div class="mt-6 rounded-xl border border-gray-200 p-5 sm:p-7">
            <h3 class="mb-6 flex items-center gap-2 text-base font-extrabold text-gray-950">
                <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v5m0-8h.01" />
                </svg>
                Informasi Utama
            </h3>

            <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-gray-600">Judul Kerja Sama</dt>
                    <dd class="mt-1 font-medium text-gray-950">{{ $cooperation['judul_kerja_sama'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Nama Mitra</dt>
                    <dd class="mt-1 font-medium text-gray-950">{{ $partnerName }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Jenis Dokumen</dt>
                    <dd class="mt-1 font-medium text-gray-950">{{ $documentType }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Nama Dosen</dt>
                    <dd class="mt-1 font-medium text-gray-950">{{ $lecturerName }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Tingkat Kerja Sama</dt>
                    <dd class="mt-1 font-medium text-gray-950">{{ $cooperation['tingkat'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Periode</dt>
                    <dd class="mt-1 font-medium text-gray-950">{{ $startYear }}–{{ $endYear }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Tanggal Mulai</dt>
                    <dd class="mt-1 font-medium text-gray-950">{{ $startDate ? \Illuminate\Support\Carbon::parse($startDate)->format('d/m/Y') : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Tanggal Selesai</dt>
                    <dd class="mt-1 font-medium text-gray-950">{{ $endDate ? \Illuminate\Support\Carbon::parse($endDate)->format('d/m/Y') : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Nomor Dokumen</dt>
                    <dd class="mt-1 font-medium text-gray-950">{{ $cooperation['nomor_dokumen'] ?? '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm text-gray-600">Bentuk Kegiatan</dt>
                    <dd class="mt-1 whitespace-pre-line font-medium text-gray-950">{{ $cooperation['bentuk_kegiatan'] ?? '-' }}</dd>
                </div>
            </dl>

            <div class="mt-6 border-t border-gray-200 pt-5">
                <h3 class="mb-3 flex items-center gap-2 text-base font-extrabold text-gray-950">
                    <svg class="h-5 w-5 text-additional" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Zm0 0v6h6M8 13h8m-8 4h5" />
                    </svg>
                    Bukti Dokumen
                </h3>
                @if($isDocumentUrl)
                    <a href="{{ $documentUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between gap-4 rounded-lg bg-[#edf4f8] px-4 py-3 text-sm text-gray-700 transition hover:bg-blue-100">
                        <span class="flex min-w-0 items-center gap-3">
                            <span class="flex h-9 w-8 shrink-0 items-center justify-center rounded bg-red-600 text-[10px] font-bold text-white">PDF</span>
                            <span class="min-w-0">
                                <span class="block truncate font-medium">{{ $documentName }}</span>
                                <span class="block text-xs text-gray-500">Buka dokumen kerja sama</span>
                            </span>
                        </span>
                        <svg class="h-5 w-5 shrink-0 text-additional" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3" />
                        </svg>
                    </a>
                @else
                    <p class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-500">Belum ada bukti dokumen yang dilampirkan.</p>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
