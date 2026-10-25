@extends('layouts.app')

@section('browser-title', 'Sertifikasi')

@section('title', 'Sertifikasi')

@section('content')
@php
    $pageTitle = $isEditing ? 'Perbarui Sertifikasi' : 'Tambah Sertifikasi';
    $certificationId = $certification['id'] ?? $certification['id_sertifikasi'] ?? null;
    $ownerIdValue = old('owner_id', $ownerId);
    $ownerTypeValue = old('owner_type', $ownerType);
    $yearValue = old('tahun_sertifikasi', $certification['tahun_sertifikasi'] ?? '');

    if (preg_match('/^\d{4}$/', (string) $yearValue)) {
        $yearValue .= '-01-01';
    } elseif ($yearValue !== '') {
        $yearValue = substr((string) $yearValue, 0, 10);
    }

    $inputClass = 'w-full border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2';
    $inputBorderClass = static fn (string $field): string => $errors->has($field)
        ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-200'
        : 'border-gray-300 focus:border-primary focus:ring-primary/20';
@endphp

<div class="space-y-5 pb-8">
    <nav class="flex items-center gap-2 text-sm text-gray-600" aria-label="Breadcrumb">
        @if($ownerTypeValue === 'tendik' && $ownerIdValue)
            <a href="{{ route('tenaga-pendidikan.show', $ownerIdValue) }}" class="hover:text-primary">Tenaga Pendidikan</a>
            <span aria-hidden="true">›</span>
            <a href="{{ route('sertifikasi.index', ['tipe_pemilik' => 'tendik', 'id_tendik' => $ownerIdValue]) }}" class="hover:text-primary">Sertifikasi</a>
        @else
            <a href="{{ route('sertifikasi.index') }}" class="hover:text-primary">Sertifikasi</a>
        @endif
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

            @if($ownerTypeValue === 'tendik' && $ownerIdValue)
                <input type="hidden" name="redirect_to" value="{{ route('tenaga-pendidikan.show', $ownerIdValue) }}">
            @elseif(request()->query('redirect_to'))
                <input type="hidden" name="redirect_to" value="{{ request()->query('redirect_to') }}">
            @endif

            @if(($ownerOptionsError ?? null) || $errors->any())
                <div class="rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                    @if($ownerOptionsError ?? null)
                        <p>{{ $ownerOptionsError }}</p>
                    @endif
                    @if($errors->any())
                        <p class="font-bold">Periksa kembali data yang diisi:</p>
                        <ul class="mt-1 list-inside list-disc">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @unless($isEditing)
                @if($isDosen || ($ownerTypeValue && $ownerIdValue))
                    <input type="hidden" name="owner_type" value="{{ $ownerTypeValue }}">
                    <input type="hidden" name="owner_id" value="{{ $ownerIdValue }}">
                @else
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="owner_type" class="mb-1.5 block text-xs font-bold text-gray-900">Tipe Pemilik</label>
                            <select id="owner_type" name="owner_type" required class="{{ $inputClass }} font-medium {{ $inputBorderClass('owner_type') }}">
                                <option value="" disabled @selected(!$ownerTypeValue)>Pilih pemilik</option>
                                <option value="dosen" @selected($ownerTypeValue === 'dosen')>Dosen</option>
                                <option value="tendik" @selected($ownerTypeValue === 'tendik')>Tenaga Kependidikan</option>
                            </select>
                            @error('owner_type')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="owner_id" class="mb-1.5 block text-xs font-bold text-gray-900">Nama Dosen/Tenaga Kependidikan</label>
                            <select id="owner_id" name="owner_id" required class="{{ $inputClass }} {{ $inputBorderClass('owner_id') }}">
                                <option value="">Pilih nama</option>
                                @foreach(['dosen' => 'Dosen', 'tendik' => 'Tenaga Kependidikan'] as $type => $label)
                                    <optgroup label="{{ $label }}" data-owner-type="{{ $type }}">
                                        @foreach(($ownerOptions[$type] ?? []) as $owner)
                                            <option
                                                value="{{ $owner['id'] }}"
                                                data-owner-type="{{ $type }}"
                                                @selected($ownerTypeValue === $type && (string) $ownerIdValue === (string) $owner['id'])
                                            >
                                                {{ $owner['name'] }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('owner_id')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <script>
                            (function () {
                                const ownerType = document.getElementById('owner_type');
                                const ownerSelect = document.getElementById('owner_id');
                                if (!ownerType || !ownerSelect) return;

                                const updateOwnerOptions = function () {
                                    const selectedType = ownerType.value;
                                    let selectedOptionIsValid = false;

                                    ownerSelect.querySelectorAll('option[data-owner-type]').forEach(function (option) {
                                        const isVisible = option.dataset.ownerType === selectedType;
                                        option.hidden = !isVisible;
                                        option.disabled = !isVisible;
                                        if (isVisible && option.selected) selectedOptionIsValid = true;
                                    });
                                    ownerSelect.querySelectorAll('optgroup[data-owner-type]').forEach(function (group) {
                                        group.hidden = group.dataset.ownerType !== selectedType;
                                    });

                                    if (!selectedOptionIsValid) ownerSelect.value = '';
                                };

                                ownerType.addEventListener('change', updateOwnerOptions);
                                updateOwnerOptions();
                            })();
                        </script>
                    </div>
                @endif
            @endunless

            <div>
                <label for="jenis_sertifikasi" class="mb-1.5 block text-xs font-bold text-gray-900">Jenis Sertifikasi</label>
                <input id="jenis_sertifikasi" type="text" name="jenis_sertifikasi" value="{{ old('jenis_sertifikasi', $certification['jenis_sertifikasi'] ?? '') }}" required placeholder="Masukkan judul sertifikasi" class="{{ $inputClass }} font-medium {{ $inputBorderClass('jenis_sertifikasi') }}">
                @error('jenis_sertifikasi')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="bidang_studi" class="mb-1.5 block text-xs font-bold text-gray-900">Bidang Studi</label>
                <input id="bidang_studi" type="text" name="bidang_studi" value="{{ old('bidang_studi', $certification['bidang_studi'] ?? '') }}" required placeholder="Isi bidang studi" class="{{ $inputClass }} font-medium {{ $inputBorderClass('bidang_studi') }}">
                @error('bidang_studi')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                    <label for="nomor_registrasi_pendidik" class="mb-1.5 block text-xs font-bold text-gray-900">Nomor Registrasi Pendidik</label>
                    <input id="nomor_registrasi_pendidik" type="text" name="nomor_registrasi_pendidik" value="{{ old('nomor_registrasi_pendidik', $certification['nomor_registrasi_pendidik'] ?? '') }}" placeholder="Isi nomor registrasi" class="{{ $inputClass }} {{ $inputBorderClass('nomor_registrasi_pendidik') }}">
                    @error('nomor_registrasi_pendidik')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="nomor_sk" class="mb-1.5 block text-xs font-bold text-gray-900">Nomor SK</label>
                    <input id="nomor_sk" type="text" name="nomor_sk" value="{{ old('nomor_sk', $certification['nomor_sk'] ?? '') }}" placeholder="Isi nomor SK" class="{{ $inputClass }} {{ $inputBorderClass('nomor_sk') }}">
                    @error('nomor_sk')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="nomor_sertifikat" class="mb-1.5 block text-xs font-bold text-gray-900">Nomor Sertifikat</label>
                    <input id="nomor_sertifikat" type="text" name="nomor_sertifikat" value="{{ old('nomor_sertifikat', $certification['nomor_sertifikat'] ?? '') }}" placeholder="Isi nomor sertifikat" class="{{ $inputClass }} {{ $inputBorderClass('nomor_sertifikat') }}">
                    @error('nomor_sertifikat')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="lembaga_penerbit" class="mb-1.5 block text-xs font-bold text-gray-900">Lembaga Penerbit</label>
                <input id="lembaga_penerbit" type="text" name="lembaga_penerbit" value="{{ old('lembaga_penerbit', $certification['lembaga_penerbit'] ?? '') }}" required placeholder="Isi nama lembaga penerbit sertifikat" class="{{ $inputClass }} font-medium {{ $inputBorderClass('lembaga_penerbit') }}">
                @error('lembaga_penerbit')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                    <label for="status_sertifikasi" class="mb-1.5 block text-xs font-bold text-gray-900">Status Sertifikasi</label>
                    <select id="status_sertifikasi" name="status_sertifikasi" required class="{{ $inputClass }} {{ $inputBorderClass('status_sertifikasi') }}">
                        <option value="" disabled @selected(!old('status_sertifikasi', $certification['status_sertifikasi'] ?? ''))>Pilih status</option>
                        @foreach(['Aktif', 'Kedaluwarsa'] as $status)
                            <option value="{{ $status }}" @selected(old('status_sertifikasi', $certification['status_sertifikasi'] ?? 'Aktif') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('status_sertifikasi')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tahun_sertifikasi" class="mb-1.5 block text-xs font-bold text-gray-900">Tahun Sertifikasi</label>
                    <input id="tahun_sertifikasi" type="date" name="tahun_sertifikasi" value="{{ $yearValue }}" required class="{{ $inputClass }} {{ $inputBorderClass('tahun_sertifikasi') }}">
                    @error('tahun_sertifikasi')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tanggal_kedaluwarsa" class="mb-1.5 block text-xs font-bold text-gray-900">Tanggal Kedaluwarsa</label>
                    <input id="tanggal_kedaluwarsa" type="date" name="tanggal_kedaluwarsa" value="{{ old('tanggal_kedaluwarsa', $certification['tanggal_kedaluwarsa'] ?? '') }}" class="{{ $inputClass }} {{ $inputBorderClass('tanggal_kedaluwarsa') }}">
                    @error('tanggal_kedaluwarsa')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="url_drive" class="mb-1.5 block text-xs font-bold text-gray-900">Tautan Sertifikasi (G. Drive)</label>
                <input id="url_drive" type="url" name="url_drive" value="{{ old('url_drive', $certification['url_drive'] ?? '') }}" placeholder="https://drive.google.com/file/d/..." class="{{ $inputClass }} {{ $inputBorderClass('url_drive') }}">
                <p class="mt-1 text-xs text-gray-600">Unggah dokumen sertifikasi dalam format PDF ke Google Drive, aktifkan akses “Siapa saja yang memiliki link”, lalu tempel tautan berbagi file di sini.</p>
                @error('url_drive')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col-reverse justify-end gap-3 pt-4 sm:flex-row">
                <a href="{{ ($ownerTypeValue === 'tendik' && $ownerIdValue) ? route('tenaga-pendidikan.show', $ownerIdValue) : route('sertifikasi.index') }}" class="inline-flex min-w-36 items-center justify-center rounded-lg bg-white-form px-6 py-3 text-sm font-bold text-gray-900 transition hover:bg-gray-300">
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
