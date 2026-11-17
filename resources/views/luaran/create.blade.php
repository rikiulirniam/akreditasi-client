@extends('layouts.app')

@section('browser-title', 'Tambah Luaran')
@section('title', 'Tambah Luaran')

@section('content')
@php
    $fieldGroups = [
        'judul_buku' => 'buku',
        'nama_jurnal' => 'artikel',
        'nama_seminar' => 'seminar',
        'nama_prosiding' => 'seminar',
        'judul_hak_cipta' => 'hki',
        'nama_produk' => 'produk',
        'tahun_terbit' => 'artikel buku',
        'doi' => 'artikel',
        'isbn' => 'buku',
        'tautan_jurnal' => 'artikel',
        'tautan_buku' => 'buku',
        'tautan_seminar' => 'seminar',
        'tautan_hki' => 'hki',
        'tautan_produk' => 'produk',
    ];
    $defaultGroup = in_array($kategori, ['artikel', 'buku', 'hki', 'seminar'], true) ? $kategori : 'artikel';
@endphp
<div class="w-full max-w-3xl space-y-5 pb-8">
    <form method="POST" action="{{ route('luaran.store', ['jenis' => $jenis]) }}" class="w-full space-y-5 rounded-xl bg-white p-4 shadow-sm sm:p-6">
        @csrf
        <input type="hidden" name="kategori" value="{{ $kategori }}">

        <div class="grid min-w-0 gap-4 sm:grid-cols-2">
            <div class="min-w-0">
                <label for="id_kegiatan" class="mb-1 block text-sm font-bold">Kegiatan {{ $jenisLabel }}</label>
                <select id="id_kegiatan" name="id_kegiatan" required class="block w-full min-w-0 rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Pilih kegiatan</option>
                    @foreach($kegiatan as $item)
                        <option value="{{ $item['id_kegiatan'] ?? '' }}" @selected(old('id_kegiatan', $idKegiatan) == ($item['id_kegiatan'] ?? null))>{{ $item['judul_kegiatan'] ?? '-' }}</option>
                    @endforeach
                </select>
                @error('id_kegiatan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="min-w-0">
                <label for="id_dosen" class="mb-1 block text-sm font-bold">Dosen</label>
                <select id="id_dosen" name="id_dosen" required class="block w-full min-w-0 rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Pilih dosen</option>
                    @foreach($dosen as $item)
                        <option value="{{ $item['id_dosen'] ?? '' }}" @selected(old('id_dosen') == ($item['id_dosen'] ?? null))>{{ $item['nama_dosen'] ?? '-' }}</option>
                    @endforeach
                </select>
                @error('id_dosen')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="id_jenis_luaran" class="mb-1 block text-sm font-bold">Jenis Luaran</label>
            <select id="id_jenis_luaran" name="id_jenis_luaran" required class="block w-full min-w-0 rounded-md border border-gray-300 px-3 py-2 text-sm">
                <option value="">Pilih jenis luaran</option>
                @foreach($jenisLuaran as $item)
                    <option value="{{ $item['id_jenis_luaran'] ?? '' }}" @selected(old('id_jenis_luaran') == ($item['id_jenis_luaran'] ?? null))>{{ $item['nama_jenis_luaran'] ?? '-' }}</option>
                @endforeach
            </select>
        </div>

        <div id="luaran-fields" class="grid min-w-0 gap-4 sm:grid-cols-2">
            @foreach([
                'judul_buku' => 'Judul Buku',
                'nama_jurnal' => 'Nama Jurnal / Artikel',
                'nama_seminar' => 'Nama Seminar',
                'nama_prosiding' => 'Nama Prosiding',
                'judul_hak_cipta' => 'Judul HKI',
                'nama_produk' => 'Nama Produk',
                'tahun_terbit' => 'Tahun Terbit',
                'doi' => 'DOI',
                'isbn' => 'ISBN',
                'tautan_jurnal' => 'Tautan Jurnal',
                'tautan_buku' => 'Tautan Buku',
                'tautan_seminar' => 'Tautan Seminar',
                'tautan_hki' => 'Tautan HKI',
                'tautan_produk' => 'Tautan Produk',
            ] as $field => $label)
                <div class="luaran-field min-w-0" data-groups="{{ $fieldGroups[$field] }}">
                    <label for="{{ $field }}" class="mb-1 block text-sm font-bold">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" type="{{ $field === 'tahun_terbit' ? 'number' : 'text' }}" class="block w-full min-w-0 rounded-md border border-gray-300 px-3 py-2 text-sm">
                </div>
            @endforeach
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('luaran.tridharma.index', ['jenis' => $jenis, 'kategori' => $kategori]) }}" class="w-full rounded-md bg-gray-200 px-4 py-2 text-center text-sm font-bold sm:w-auto">Batal</a>
            <button type="submit" class="w-full rounded-md bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-additional sm:w-auto">Simpan</button>
        </div>
    </form>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('id_jenis_luaran');
        const fields = document.querySelectorAll('.luaran-field');
        const initialGroup = @json($defaultGroup);

        function normalize(value) {
            return value.toLowerCase()
                .replace(/hki|hak cipta/g, 'hki')
                .replace(/prosiding|seminar/g, 'seminar')
                .replace(/jurnal|artikel/g, 'artikel')
                .replace(/buku/g, 'buku')
                .replace(/produk/g, 'produk');
        }

        function updateFields() {
            const selected = typeSelect.options[typeSelect.selectedIndex];
            const group = selected && selected.value ? normalize(selected.text) : initialGroup;

            fields.forEach(function (field) {
                const visible = field.dataset.groups.split(' ').includes(group);
                field.classList.toggle('hidden', !visible);
                field.querySelector('input').disabled = !visible;
            });
        }

        typeSelect.addEventListener('change', updateFields);
        updateFields();
    });
</script>
@endpush
@endsection
