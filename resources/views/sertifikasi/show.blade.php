@extends('layouts.app')

@section('title', 'Sertifikasi')

@section('content')
@php
    $certificationId = $certification['id'] ?? $certification['id_sertifikasi'] ?? null;
    $ownerQuery = array_filter([
        'tipe_pemilik' => $certification['tipe_pemilik'] ?? null,
        'id_dosen' => $certification['id_dosen'] ?? null,
        'id_tendik' => $certification['id_tendik'] ?? null,
    ], static fn ($value) => $value !== null && $value !== '');
@endphp

<div class="space-y-5 pb-8">
    @if(session('toast'))
        <x-toast />
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <h2 class="mr-auto text-2xl font-extrabold text-gray-950 sm:text-3xl">
            Sertifikasi <span class="text-primary" aria-hidden="true">›</span> Detail
        </h2>
        <a href="{{ route('sertifikasi.index', $ownerQuery) }}" class="rounded-lg bg-white-form px-5 py-3 text-sm font-bold text-gray-900 transition hover:bg-gray-300">Kembali</a>
        @if($certificationId)
            <form method="POST" action="{{ route('sertifikasi.destroy', $certificationId) }}" data-confirm-delete data-title="Hapus Sertifikasi?" data-message="Apakah Anda yakin ingin menghapus sertifikasi ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.">
                @csrf
                @method('DELETE')
                @foreach($ownerQuery as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <button type="submit" class="rounded-lg bg-red-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-800">Hapus</button>
            </form>
            <a href="{{ route('sertifikasi.edit', $certificationId) }}" class="rounded-lg bg-secondary px-5 py-3 text-sm font-bold text-gray-950 transition hover:brightness-95">Edit</a>
        @endif
    </div>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="grid grid-cols-1 divide-y divide-gray-300 bg-white-form sm:grid-cols-2 sm:divide-x sm:divide-y-0">
            <div class="p-5 sm:p-6">
                <p class="text-sm font-bold text-gray-950">Jenis Sertifikasi</p>
                <p class="mt-1 text-sm text-gray-800">{{ $certification['jenis_sertifikasi'] ?? '-' }}</p>
            </div>
            <div class="p-5 sm:p-6">
                <p class="text-sm font-bold text-gray-950">Bidang Studi</p>
                <p class="mt-1 text-sm text-gray-800">{{ $certification['bidang_studi'] ?? '-' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 divide-y divide-gray-300 bg-white-form sm:grid-cols-2 sm:divide-x sm:divide-y-0">
            <div class="p-5 sm:p-6">
                <p class="text-sm font-bold text-gray-950">Nomor Registrasi Pendidik</p>
                <p class="mt-1 text-sm text-gray-800">{{ $certification['nomor_registrasi_pendidik'] ?? '-' }}</p>
            </div>
            <div class="p-5 sm:p-6">
                <p class="text-sm font-bold text-gray-950">Nomor SK</p>
                <p class="mt-1 text-sm text-gray-800">{{ $certification['nomor_sk'] ?? '-' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 divide-y divide-gray-300 bg-white-form sm:grid-cols-2 sm:divide-x sm:divide-y-0">
            <div class="p-5 sm:p-6">
                <p class="text-sm font-bold text-gray-950">Nomor Sertifikat</p>
                <p class="mt-1 text-sm text-gray-800">{{ $certification['nomor_sertifikat'] ?? '-' }}</p>
            </div>
            <div class="p-5 sm:p-6">
                <p class="text-sm font-bold text-gray-950">Lembaga Penerbit</p>
                <p class="mt-1 text-sm text-gray-800">{{ $certification['lembaga_penerbit'] ?? '-' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 divide-y divide-gray-300 bg-white-form sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            <div class="p-5 sm:p-6">
                <p class="text-sm font-bold text-gray-950">Status Sertifikasi</p>
                @php($status = $certification['status_sertifikasi'] ?? '-')
                <span class="mt-2 inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ strtolower($status) === 'aktif' ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-800' }}">{{ $status }}</span>
            </div>
            <div class="p-5 sm:p-6">
                <p class="text-sm font-bold text-gray-950">Tahun Sertifikasi</p>
                <p class="mt-1 text-sm text-gray-800">{{ $certification['tahun_sertifikasi'] ?? '-' }}</p>
            </div>
            <div class="p-5 sm:p-6">
                <p class="text-sm font-bold text-gray-950">Tanggal Kedaluwarsa</p>
                <p class="mt-1 text-sm text-gray-800">{{ $certification['tanggal_kedaluwarsa'] ?? '-' }}</p>
            </div>
        </div>

        <div class="bg-white-form p-5 sm:p-6">
            <p class="text-sm font-bold text-gray-950">Tautan Sertifikasi (G. Drive)</p>
            @if(!empty($certification['url_drive']))
                <a href="{{ $certification['url_drive'] }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block break-all text-sm font-medium text-sky-700 underline">
                    {{ $certification['url_drive'] }}
                </a>
            @else
                <p class="mt-1 text-sm text-gray-800">-</p>
            @endif
        </div>
    </section>
    <x-confirm-delete id="deleteSertifikasiModal" />
</div>
@endsection
