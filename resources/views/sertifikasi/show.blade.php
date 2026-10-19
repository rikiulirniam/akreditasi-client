@extends('layouts.app')

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
    $year = $certification['tahun_sertifikasi'] ?? null;
    $year = preg_match('/^\d{4}/', (string) $year) ? substr((string) $year, 0, 4) : ($year ?? '-');
    $expirationDate = $certification['tanggal_kedaluwarsa'] ?? null;

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
            <div class="border-b border-[#666666] p-5 sm:border-r sm:p-6">
                <p class="text-sm font-bold">No Registrasi Pendidik</p>
                <p class="text-sm">{{ $certification['nomor_registrasi_pendidik'] ?? '-' }}</p>
            </div>
            <div class="border-b border-[#666666] p-5 sm:p-6">
                <p class="text-sm font-bold">No SK</p>
                <p class="text-sm">{{ $certification['nomor_sk'] ?? '-' }}</p>
            </div>
            <div class="border-b border-[#666666] p-5 sm:border-b-0 sm:border-r sm:p-6">
                <p class="text-sm font-bold">No Sertifikasi</p>
                <p class="text-sm">{{ $certification['nomor_sertifikat'] ?? '-' }}</p>
            </div>
            <div class="p-5 sm:p-6">
                <p class="text-sm font-bold">Lembaga Penerbit</p>
                <p class="text-sm">{{ $certification['lembaga_penerbit'] ?? '-' }}</p>
            </div>
        </section>

        <section class="grid grid-cols-1 divide-y divide-[#666666] overflow-hidden rounded-lg bg-[#D9D9D9] text-gray-950 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
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
            @if(!empty($certification['url_drive']))
                <a href="{{ $certification['url_drive'] }}" target="_blank" rel="noopener noreferrer" class="inline-block break-all text-sm font-medium text-sky-700 underline">
                    {{ $certification['url_drive'] }}
                </a>
            @else
                <p class="text-sm">-</p>
            @endif
        </section>
    </div>
</div>
@endsection
