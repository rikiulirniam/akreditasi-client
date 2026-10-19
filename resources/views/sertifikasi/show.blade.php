@extends('layouts.app')

@section('browser-title', 'Sertifikasi')

@section('title')
    <span class="inline-flex items-center gap-2.5 sm:gap-3.5 flex-wrap">
        <a href="{{ route('sertifikasi.index') }}" class="text-black transition hover:text-additional">Sertifikasi</a>
        <svg class="h-5 w-5 shrink-0 stroke-[3] text-black sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <span>Detail</span>
    </span>
@endsection

@section('content')
@php
    $certificationId = $certification['id'] ?? $certification['id_sertifikasi'] ?? null;
    $ownerQuery = array_filter([
        'tipe_pemilik' => $certification['tipe_pemilik'] ?? null,
        'id_dosen' => $certification['id_dosen'] ?? null,
        'id_tendik' => $certification['id_tendik'] ?? null,
    ], static fn ($value) => $value !== null && $value !== '');
    $driveUrl = $certification['url_sertifikasi'] ?? $certification['url_drive'] ?? null;
    $drivePreviewUrl = null;
    $year = $certification['tahun_sertifikasi'] ?? null;
    $year = preg_match('/^\d{4}/', (string) $year) ? substr((string) $year, 0, 4) : ($year ?? '-');
    $expirationDate = $certification['tanggal_kedaluwarsa'] ?? null;

    if (is_string($driveUrl) && filter_var($driveUrl, FILTER_VALIDATE_URL)) {
        $driveUrlParts = parse_url($driveUrl);
        $driveHost = is_array($driveUrlParts) ? strtolower($driveUrlParts['host'] ?? '') : '';
        $driveFileId = null;

        if ($driveHost === 'drive.google.com') {
            $drivePath = $driveUrlParts['path'] ?? '';

            if (preg_match('~^/file/d/([A-Za-z0-9_-]+)~', $drivePath, $matches)) {
                $driveFileId = $matches[1];
            } else {
                parse_str($driveUrlParts['query'] ?? '', $driveQuery);
                $candidateId = $driveQuery['id'] ?? null;
                $driveFileId = is_string($candidateId) && preg_match('/^[A-Za-z0-9_-]+$/', $candidateId)
                    ? $candidateId
                    : null;
            }
        }

        if ($driveFileId !== null) {
            $drivePreviewUrl = 'https://drive.google.com/file/d/'.rawurlencode($driveFileId).'/preview';
        }
    }

    if (is_string($expirationDate) && preg_match('/^\d{4}-\d{2}-\d{2}/', $expirationDate)) {
        $expirationDate = \Illuminate\Support\Carbon::parse($expirationDate)->locale('id')->translatedFormat('d M Y');
    }
@endphp

<div class="space-y-5 pb-8">
    <div class="flex flex-wrap justify-end gap-3">
        <a href="{{ route('sertifikasi.index', $ownerQuery) }}" class="inline-flex min-w-36 items-center justify-center rounded-lg bg-white-form px-5 py-3 text-sm font-bold text-gray-900 transition hover:bg-gray-300">Kembali</a>
        @if($certificationId)
            <form method="POST" action="{{ route('sertifikasi.destroy', ['sertifikasi' => $certificationId] + $ownerQuery) }}" data-confirm-delete data-title="Hapus Sertifikasi?" data-message="Apakah Anda yakin ingin menghapus sertifikasi ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex min-w-36 items-center justify-center rounded-lg bg-red-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-800">Hapus</button>
            </form>
            <a href="{{ route('sertifikasi.edit', ['sertifikasi' => $certificationId] + $ownerQuery) }}" class="inline-flex min-w-36 items-center justify-center rounded-lg bg-secondary px-5 py-3 text-sm font-bold text-gray-950 transition hover:brightness-95">Edit</a>
        @endif
    </div>

    <div class="space-y-3">
        <section class="overflow-hidden rounded-lg bg-[#D9D9D9] text-gray-950">
            <div class="border-b border-[#666666] p-5 sm:p-6">
                <p class="text-sm font-bold">Jenis Sertifikasi</p>
                <p class="text-sm">{{ $certification['jenis_sertifikasi'] ?? '-' }}</p>
            </div>
            <div class="p-5 sm:p-6">
                <p class="text-sm font-bold">Bidang Studi</p>
                <p class="text-sm">{{ $certification['bidang_studi'] ?? '-' }}</p>
            </div>
        </section>

        <section class="grid grid-cols-1 overflow-hidden rounded-lg bg-[#D9D9D9] text-gray-950 sm:grid-cols-2">
            <div class="border-b border-[#666666] p-5 sm:p-6">
                <p class="text-sm font-bold">No Registrasi Pendidik</p>
                <p class="text-sm">{{ $certification['nomor_registrasi_pendidik'] ?? '-' }}</p>
            </div>
            <div class="border-b border-[#666666] p-5 sm:p-6">
                <p class="text-sm font-bold">No SK</p>
                <p class="text-sm">{{ $certification['nomor_sk'] ?? '-' }}</p>
            </div>
            <div class="border-b border-[#666666] p-5 sm:border-b-0 sm:p-6">
                <p class="text-sm font-bold">No Sertifikasi</p>
                <p class="text-sm">{{ $certification['nomor_sertifikat'] ?? '-' }}</p>
            </div>
            <div class="p-5 sm:p-6">
                <p class="text-sm font-bold">Lembaga Penerbit</p>
                <p class="text-sm">{{ $certification['lembaga_penerbit'] ?? '-' }}</p>
            </div>
        </section>

        <section class="grid grid-cols-1 divide-y divide-[#666666] overflow-hidden rounded-lg bg-[#D9D9D9] text-gray-950 sm:grid-cols-3 sm:divide-y-0">
            <div class="p-5 sm:px-7 sm:py-6">
                <p class="text-sm font-bold">Status Sertifikasi</p>
                @php($status = $certification['status_sertifikasi'] ?? '-')
                <span class="mt-1 inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ strtolower((string) $status) === 'aktif' ? 'bg-emerald-600 text-white' : 'bg-gray-500 text-white' }}">{{ $status }}</span>
            </div>
            <div class="p-5 sm:px-7 sm:py-6">
                <p class="text-sm font-bold">Tahun Sertifikasi</p>
                <p class="text-sm">{{ $year }}</p>
            </div>
            <div class="p-5 sm:px-7 sm:py-6">
                <p class="text-sm font-bold">Tanggal Kedaluwarsa</p>
                <p class="text-sm">{{ $expirationDate ?: '-' }}</p>
            </div>
        </section>

        <section class="rounded-lg bg-[#D9D9D9] p-5 text-gray-950 sm:p-6">
            <p class="text-sm font-bold">Tautan Sertifikasi (G. Drive)</p>
            @if($driveUrl)
                <a href="{{ $driveUrl }}" target="_blank" rel="noopener noreferrer" class="inline-block break-all text-sm font-medium text-sky-700 underline">
                    {{ $driveUrl }}
                </a>
            @else
                <p class="text-sm">-</p>
            @endif
        </section>

        @if($drivePreviewUrl)
            <section class="overflow-hidden rounded-lg border border-gray-200 bg-[#D9D9D9]">
                <h2 class="bg-primary px-4 py-3 text-center text-sm font-semibold text-white sm:text-base">Pratinjau File Sertifikasi</h2>
                <div class="p-4 sm:p-6">
                    <iframe
                        src="{{ $drivePreviewUrl }}"
                        title="Pratinjau PDF sertifikasi"
                        class="h-[70vh] min-h-[480px] w-full rounded bg-white"
                        loading="lazy"
                        allow="autoplay"
                    ></iframe>
                    <p class="mt-3 text-center text-xs text-gray-700">
                        Jika pratinjau tidak muncul, pastikan file PDF dapat diakses oleh siapa saja yang memiliki tautan.
                        <a href="{{ $driveUrl }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-sky-700 underline">Buka file di Google Drive</a>
                    </p>
                </div>
            </section>
        @elseif($driveUrl)
            <section class="rounded-lg border border-gray-200 bg-[#D9D9D9] p-5 text-sm text-gray-800 sm:p-6">
                Pratinjau otomatis tersedia untuk tautan berbagi Google Drive. Gunakan tautan Drive dengan format file atau folder yang valid.
            </section>
        @endif
    </div>
</div>
@endsection
