@extends('layouts.app')

@section('meta_title', 'Tambah Kegiatan')
@section('title', 'Penelitian & PKM')

@section('subtitle')
    <a href="{{ route('penelitian-pkm.index') }}" class="hover:underline">Penelitian</a> &rsaquo; Tambah Kegiatan
@endsection

@section('content')
@php
    $input = 'h-[46px] w-full rounded-lg bg-gray-200 px-4 text-sm text-gray-900 placeholder:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary/30';
    $label = 'mb-1.5 block text-sm font-bold text-black';
@endphp

<div class="mx-auto max-w-5xl pb-8">
    <h2 class="mb-6 text-center text-3xl font-extrabold text-black">Tambah Kegiatan</h2>

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700" role="alert">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('penelitian-pkm.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="judul_kegiatan" class="{{ $label }}">Judul Kegiatan</label>
            <input id="judul_kegiatan" type="text" name="judul_kegiatan" value="{{ old('judul_kegiatan') }}" placeholder="Masukkan judul kegiatan" class="{{ $input }}" required>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="id_jenis_kegiatan" class="{{ $label }}">Jenis Kegiatan</label>
                <select id="id_jenis_kegiatan" name="id_jenis_kegiatan" class="{{ $input }}" required>
                    <option value="">Pilih jenis kegiatan</option>
                    @foreach($jenisKegiatan as $jenis)
                        <option value="{{ $jenis['id_jenis_kegiatan'] }}" @selected(old('id_jenis_kegiatan') == $jenis['id_jenis_kegiatan'])>{{ $jenis['nama_jenis_kegiatan'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status_kegiatan" class="{{ $label }}">Status Kegiatan</label>
                <select id="status_kegiatan" name="status_kegiatan" class="{{ $input }}" required>
                    <option value="">Pilih status kegiatan</option>
                    @foreach(['Pengajuan', 'Berjalan', 'Selesai', 'Ditolak'] as $status)
                        <option value="{{ $status }}" @selected(old('status_kegiatan') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-3">
            <div>
                <label for="tahun_usulan" class="{{ $label }}">Tahun Usulan</label>
                <input id="tahun_usulan" type="number" name="tahun_usulan" value="{{ old('tahun_usulan') }}" placeholder="Isi tahun usulan" class="{{ $input }}" required>
            </div>
            <div>
                <label for="tahun_kegiatan" class="{{ $label }}">Tahun Kegiatan</label>
                <input id="tahun_kegiatan" type="number" name="tahun_kegiatan" value="{{ old('tahun_kegiatan') }}" placeholder="Isi tahun kegiatan" class="{{ $input }}" required>
            </div>
            <div>
                <label for="tahun_pelaksanaan" class="{{ $label }}">Tahun Pelaksanaan</label>
                <input id="tahun_pelaksanaan" type="number" name="tahun_pelaksanaan" value="{{ old('tahun_pelaksanaan') }}" placeholder="Isi tahun pelaksanaan" class="{{ $input }}" required>
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="lokasi_kegiatan" class="{{ $label }}">Lokasi Kegiatan</label>
                <input id="lokasi_kegiatan" type="text" name="lokasi_kegiatan" value="{{ old('lokasi_kegiatan') }}" placeholder="Contoh: Semarang, Jawa Tengah" class="{{ $input }}">
            </div>
            <div>
                <label for="lama_kegiatan" class="{{ $label }}">Lama Kegiatan</label>
                <input id="lama_kegiatan" type="text" name="lama_kegiatan" value="{{ old('lama_kegiatan') }}" placeholder="Contoh: 12 Bulan" class="{{ $input }}">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('penelitian-pkm.index') }}" class="inline-flex h-11 w-40 items-center justify-center rounded-lg bg-gray-200 text-sm font-bold text-black transition hover:bg-gray-300">Kembali</a>
            <button type="submit" class="inline-flex h-11 w-40 items-center justify-center rounded-lg bg-additional text-sm font-bold text-white transition hover:bg-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection