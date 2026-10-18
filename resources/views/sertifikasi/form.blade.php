@extends('layouts.app')

@section('title', 'Sertifikasi')

@section('content')
@php
    $pageTitle = $isEditing ? 'Perbarui Sertifikasi' : 'Tambah Sertifikasi';
    $certificationId = $certification['id'] ?? $certification['id_sertifikasi'] ?? null;
    $ownerIdValue = old('owner_id', $ownerId);
    $ownerTypeValue = old('owner_type', $ownerType);
@endphp

<div class="space-y-5 pb-8">
    <nav class="flex items-center gap-2 text-sm text-gray-600" aria-label="Breadcrumb">
        <a href="{{ route('sertifikasi.index') }}" class="hover:text-primary">Sertifikasi</a>
        <span aria-hidden="true">›</span>
        <span class="font-semibold text-gray-900">{{ $pageTitle }}</span>
    </nav>

    <section class="rounded-2xl bg-white p-5 shadow-sm sm:p-8">
        <h2 class="mb-7 text-center text-2xl font-extrabold text-gray-950 sm:text-3xl">{{ $pageTitle }}</h2>

        <form
            method="POST"
            action="{{ $isEditing ? route('sertifikasi.update', $certificationId) : route('sertifikasi.store') }}"
            class="space-y-4"
        >
            @csrf
            @if($isEditing)
                @method('PUT')
            @endif

            @unless($isEditing)
                @if($ownerTypeValue && $ownerIdValue)
                    <input type="hidden" name="owner_type" value="{{ $ownerTypeValue }}">
                    <input type="hidden" name="owner_id" value="{{ $ownerIdValue }}">
                @else
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="owner_type" class="mb-1.5 block text-xs font-bold text-gray-900">Tipe Pemilik</label>
                            <select id="owner_type" name="owner_type" required class="w-full border-0 px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-primary/20">
                                <option value="" disabled @selected(!$ownerTypeValue)>Pilih pemilik</option>
                                <option value="dosen" @selected($ownerTypeValue === 'dosen')>Dosen</option>
                                <option value="tendik" @selected($ownerTypeValue === 'tendik')>Tenaga Kependidikan</option>
                            </select>
                            @error('owner_type')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="owner_id" class="mb-1.5 block text-xs font-bold text-gray-900">ID Pemilik pada API</label>
                            <input id="owner_id" type="number" name="owner_id" min="1" value="{{ $ownerIdValue }}" required placeholder="Masukkan ID dosen/tendik dari API" class="w-full border-0 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20">
                            @error('owner_id')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                @endif
            @endunless

            <div>
                <label for="jenis_sertifikasi" class="mb-1.5 block text-xs font-bold text-gray-900">Jenis Sertifikasi</label>
                <input id="jenis_sertifikasi" type="text" name="jenis_sertifikasi" value="{{ old('jenis_sertifikasi', $certification['jenis_sertifikasi'] ?? '') }}" required placeholder="Masukkan judul sertifikasi" class="w-full border-0 px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-primary/20">
                @error('jenis_sertifikasi')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="bidang_studi" class="mb-1.5 block text-xs font-bold text-gray-900">Bidang Studi</label>
                <input id="bidang_studi" type="text" name="bidang_studi" value="{{ old('bidang_studi', $certification['bidang_studi'] ?? '') }}" required placeholder="Isi bidang studi" class="w-full border-0 px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-primary/20">
                @error('bidang_studi')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                    <label for="nomor_registrasi_pendidik" class="mb-1.5 block text-xs font-bold text-gray-900">Nomor Registrasi Pendidik</label>
                    <input id="nomor_registrasi_pendidik" type="text" name="nomor_registrasi_pendidik" value="{{ old('nomor_registrasi_pendidik', $certification['nomor_registrasi_pendidik'] ?? '') }}" placeholder="Isi nomor registrasi" class="w-full border-0 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20">
                    @error('nomor_registrasi_pendidik')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="nomor_sk" class="mb-1.5 block text-xs font-bold text-gray-900">Nomor SK</label>
                    <input id="nomor_sk" type="text" name="nomor_sk" value="{{ old('nomor_sk', $certification['nomor_sk'] ?? '') }}" placeholder="Isi nomor SK" class="w-full border-0 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20">
                    @error('nomor_sk')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="nomor_sertifikat" class="mb-1.5 block text-xs font-bold text-gray-900">Nomor Sertifikat</label>
                    <input id="nomor_sertifikat" type="text" name="nomor_sertifikat" value="{{ old('nomor_sertifikat', $certification['nomor_sertifikat'] ?? '') }}" placeholder="Isi nomor sertifikat" class="w-full border-0 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20">
                    @error('nomor_sertifikat')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="lembaga_penerbit" class="mb-1.5 block text-xs font-bold text-gray-900">Lembaga Penerbit</label>
                <input id="lembaga_penerbit" type="text" name="lembaga_penerbit" value="{{ old('lembaga_penerbit', $certification['lembaga_penerbit'] ?? '') }}" required placeholder="Isi nama lembaga penerbit sertifikat" class="w-full border-0 px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-primary/20">
                @error('lembaga_penerbit')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                    <label for="status_sertifikasi" class="mb-1.5 block text-xs font-bold text-gray-900">Status Sertifikasi</label>
                    <select id="status_sertifikasi" name="status_sertifikasi" required class="w-full border-0 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20">
                        <option value="" disabled @selected(!old('status_sertifikasi', $certification['status_sertifikasi'] ?? ''))>Pilih status</option>
                        @foreach(['Aktif', 'Kedaluwarsa'] as $status)
                            <option value="{{ $status }}" @selected(old('status_sertifikasi', $certification['status_sertifikasi'] ?? 'Aktif') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('status_sertifikasi')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tahun_sertifikasi" class="mb-1.5 block text-xs font-bold text-gray-900">Tahun Sertifikasi</label>
                    <input id="tahun_sertifikasi" type="number" name="tahun_sertifikasi" min="1900" max="2100" value="{{ old('tahun_sertifikasi', $certification['tahun_sertifikasi'] ?? '') }}" required placeholder="Tahun terbit sertifikasi" class="w-full border-0 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20">
                    @error('tahun_sertifikasi')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tanggal_kedaluwarsa" class="mb-1.5 block text-xs font-bold text-gray-900">Tanggal Kedaluwarsa</label>
                    <input id="tanggal_kedaluwarsa" type="date" name="tanggal_kedaluwarsa" value="{{ old('tanggal_kedaluwarsa', $certification['tanggal_kedaluwarsa'] ?? '') }}" class="w-full border-0 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20">
                    @error('tanggal_kedaluwarsa')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="url_drive" class="mb-1.5 block text-xs font-bold text-gray-900">Tautan Sertifikasi (G. Drive)</label>
                <input id="url_drive" type="url" name="url_drive" value="{{ old('url_drive', $certification['url_drive'] ?? '') }}" placeholder="https://drive.google.com/file/d/..." class="w-full border-0 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20">
                <p class="mt-1 text-[11px] text-gray-500">Masukkan tautan Google Drive dokumen sertifikasi.</p>
                @error('url_drive')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col-reverse justify-end gap-3 pt-4 sm:flex-row">
                <a href="{{ $isEditing && $certificationId ? route('sertifikasi.show', $certificationId) : route('sertifikasi.index', $ownerTypeValue === 'dosen' ? ['tipe_pemilik' => 'dosen', 'id_dosen' => $ownerIdValue] : ($ownerTypeValue === 'tendik' ? ['tipe_pemilik' => 'tendik', 'id_tendik' => $ownerIdValue] : [])) }}" class="inline-flex min-w-36 items-center justify-center rounded-lg bg-white-form px-6 py-3 text-sm font-bold text-gray-900 transition hover:bg-gray-300">
                    Kembali
                </a>
                <button type="submit" class="inline-flex min-w-36 items-center justify-center rounded-lg bg-additional px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-primary">
                    {{ $isEditing ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
