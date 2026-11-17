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
        ?? $cooperation['jenis_kerja_sama']['nama_jenis_kerjasama']
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
    $documentPreviewUrl = null;

    if ($isDocumentUrl) {
        $documentUrlParts = parse_url($documentUrl);
        $documentHost = is_array($documentUrlParts) ? strtolower($documentUrlParts['host'] ?? '') : '';
        $documentPath = is_array($documentUrlParts) ? ($documentUrlParts['path'] ?? '') : '';

        if ($documentHost === 'drive.google.com') {
            $driveFileId = null;

            if (preg_match('~^/file/d/([A-Za-z0-9_-]+)~', $documentPath, $matches)) {
                $driveFileId = $matches[1];
            } else {
                parse_str(is_array($documentUrlParts) ? ($documentUrlParts['query'] ?? '') : '', $driveQuery);
                $candidateId = $driveQuery['id'] ?? null;
                $driveFileId = is_string($candidateId) && preg_match('/^[A-Za-z0-9_-]+$/', $candidateId)
                    ? $candidateId
                    : null;
            }

            if ($driveFileId !== null) {
                $documentPreviewUrl = 'https://drive.google.com/file/d/'.rawurlencode($driveFileId).'/preview';
            }
        } elseif (preg_match('/\.pdf$/i', $documentPath)) {
            $documentPreviewUrl = $documentUrl;
        }
    }
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
        <header class="flex flex-col gap-4 pb-6 sm:flex-row sm:items-center">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[#edf4f8] text-additional">
                <span class="material-symbols-outlined inline-flex items-center justify-center leading-none" style="font-size: 40px; line-height: 1;">handshake</span>
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
                <div class="relative grid grid-cols-1 gap-x-8 gap-y-5 sm:col-span-2 sm:grid-cols-2">
                    <span class="pointer-events-none absolute inset-y-0 left-1/2 hidden w-px -translate-x-1/2 bg-gray-200 sm:block" aria-hidden="true"></span>
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
                </div>
                <div class="grid grid-cols-1 gap-x-8 border-t border-gray-200 pt-5 sm:col-span-2 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-gray-600">Nomor Dokumen</dt>
                        <dd class="mt-1 font-medium text-gray-950">{{ $cooperation['nomor_dokumen'] ?? '-' }}</dd>
                    </div>
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
                    Bukti Dokumen Kerja Sama (Tautan Google Drive)
                </h3>
                @if($isDocumentUrl)
                    <a href="{{ $documentUrl }}" target="_blank" rel="noopener noreferrer" class="block break-all text-sm font-medium text-sky-700 underline hover:text-sky-900">
                        {{ $documentUrl }}
                    </a>
                @else
                    <p class="text-sm text-gray-500">Belum ada tautan Google Drive yang dilampirkan.</p>
                @endif
            </div>

            <section class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-[#D9D9D9]">
                <h3 class="bg-primary px-4 py-3 text-center text-sm font-semibold text-white sm:text-base">Pratinjau Dokumen Kerja Sama</h3>
                <div class="p-4 sm:p-6">
                    @if($documentPreviewUrl)
                        <iframe
                            src="{{ $documentPreviewUrl }}"
                            title="Pratinjau dokumen kerja sama"
                            class="h-[70vh] min-h-[480px] w-full rounded bg-white"
                            loading="lazy"
                            allow="autoplay"
                        ></iframe>
                        <p class="mt-3 text-center text-xs text-gray-700">
                            Jika pratinjau tidak muncul, pastikan file dapat diakses melalui tautan.
                            <a href="{{ $documentUrl }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-sky-700 underline">Buka file di Google Drive</a>
                        </p>
                    @elseif($isDocumentUrl)
                        <p class="text-center text-sm text-gray-700">
                            Pratinjau otomatis tersedia untuk file PDF atau tautan file Google Drive yang valid.
                            <a href="{{ $documentUrl }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-sky-700 underline">Buka file di Google Drive</a>
                        </p>
                    @else
                        <p class="text-center text-sm text-gray-500">Belum ada dokumen untuk dipratinjau.</p>
                    @endif
                </div>
            </section>
        </div>
    </section>
</div>
@endsection
