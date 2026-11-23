@extends('layouts.app')

@section('browser-title', 'Detail '.$kategoriLabel)

@section('title')
    <span class="inline-flex items-center gap-2.5 sm:gap-3.5 flex-wrap">
        <a href="{{ route('luaran.tridharma.index', ['jenis' => $jenis, 'kategori' => $kategori]) }}" class="transition hover:text-additional">Luaran</a>
        <svg class="h-5 w-5 shrink-0 stroke-[3] text-black sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <span>Detail {{ $kategoriLabel }}</span>
    </span>
@endsection

@section('content')
@php
    $luaran = is_array($luaran) ? $luaran : [];
    $fields = match ($kategori) {
        'artikel' => [
            'Nama Jurnal / Artikel' => 'nama_jurnal',
            'ISSN' => 'issn',
            'Volume' => 'volume',
            'Nomor Edisi' => 'nomor_edisi',
            'Halaman' => 'halaman_jurnal',
            'Tanggal Terbit' => 'tanggal_terbit',
            'DOI' => 'doi',
            'Indeksasi' => 'indeksasi',
        ],
        'buku' => [
            'Judul Buku' => 'judul_buku',
            'ISBN' => 'isbn',
            'Penerbit' => 'penerbit_buku',
            'Jenis Buku' => 'jenis_buku',
            'Bidang Ilmu' => 'bidang_ilmu',
            'Tahun Terbit' => 'tahun_terbit',
            'Halaman' => 'halaman_buku',
            'Tanggal Terbit' => 'tanggal_terbit',
            'Tautan Buku' => 'tautan_buku',
        ],
        'hki' => [
            'Judul Hak Cipta' => 'judul_hak_cipta',
            'Jenis HKI' => 'jenis_hki',
            'Nomor Pendaftaran' => 'nomor_pendaftaran_hki',
            'Nomor Sertifikat' => 'nomor_sertifikat_hki',
            'Tanggal Pendaftaran' => 'tanggal_pendaftaran_hki',
            'Tanggal Terbit Sertifikat' => 'tanggal_terbit_sertifikat_hki',
            'Pemegang HKI' => 'pemegang_hki',
            'Status' => 'status_hki',
        ],
        default => [
            'Nama Seminar' => 'nama_seminar',
            'Nama Prosiding' => 'nama_prosiding',
            'ISBN Prosiding' => 'isbn_prosiding',
            'Penyelenggara' => 'penyelenggara',
            'Tanggal Seminar' => 'tanggal_seminar',
            'Lokasi' => 'lokasi_seminar',
            'Halaman' => 'halaman_prosiding',
            'DOI' => 'doi_prosiding',
            'Tautan Seminar' => 'tautan_seminar',
        ],
    };
    $previewUrl = match ($kategori) {
        'artikel' => $luaran['tautan_jurnal'] ?? null,
        'buku' => $luaran['tautan_buku'] ?? null,
        'hki' => $luaran['tautan_hki'] ?? null,
        default => $luaran['tautan_seminar'] ?? null,
    };
    $previewUrl = is_string($previewUrl) && filter_var($previewUrl, FILTER_VALIDATE_URL) ? $previewUrl : null;
@endphp

<div class="space-y-5 pb-8">
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <section class="overflow-hidden rounded-lg border border-gray-300 bg-[#D9D9D9] text-gray-950">
            <h2 class="bg-primary px-4 py-4 text-base font-bold text-white">Informasi {{ $kategoriLabel }}</h2>
            <div class="divide-y divide-[#666666]">
                <div class="grid gap-2 p-4 sm:grid-cols-[132px_1fr] sm:gap-5">
                    <p class="text-sm font-semibold">Penulis</p>
                    <p class="break-words text-sm">{{ $luaran['nama_penulis'] ?? ($luaran['dosen']['nama_dosen'] ?? '-') }}</p>
                </div>
                @foreach($fields as $label => $key)
                    @php($value = $luaran[$key] ?? null)
                    @if($value !== null && trim((string) $value) !== '')
                        <div class="grid gap-2 p-4 sm:grid-cols-[132px_1fr] sm:gap-5">
                            <p class="text-sm font-semibold">{{ $label }}</p>
                            @if(str_starts_with($key, 'tautan_') || $key === 'doi' || $key === 'doi_prosiding')
                                <a href="{{ $value }}" target="_blank" rel="noopener noreferrer" class="break-all text-sm text-sky-700 underline">{{ $value }}</a>
                            @else
                                <p class="break-words text-sm">{{ $value }}</p>
                            @endif
                        </div>
                    @endif
                @endforeach
                @if(!empty($luaran['kegiatan']['judul_kegiatan']))
                    <div class="grid gap-2 p-4 sm:grid-cols-[132px_1fr] sm:gap-5">
                        <p class="text-sm font-semibold">Kegiatan</p>
                        <p class="break-words text-sm">{{ $luaran['kegiatan']['judul_kegiatan'] }}</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-gray-300 bg-[#D9D9D9]">
            <h2 class="bg-primary px-4 py-4 text-base font-bold text-white">Preview {{ $kategoriLabel }}</h2>
            @if($previewUrl)
                <div class="p-4">
                    <iframe src="{{ $previewUrl }}" title="Preview {{ $kategoriLabel }}" class="h-[520px] w-full rounded border border-gray-300 bg-white" loading="lazy"></iframe>
                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener noreferrer" class="mt-3 block text-center text-sm font-semibold text-sky-700 underline">Buka {{ $kategoriLabel }}</a>
                </div>
            @else
                <div class="flex min-h-[220px] items-center justify-center p-6 text-center text-sm text-gray-700">
                    Preview {{ strtolower($kategoriLabel) }} belum tersedia.
                </div>
            @endif
        </section>
    </div>

    <div class="flex flex-wrap justify-end gap-3">
        <a href="{{ route('luaran.tridharma.index', ['jenis' => $jenis, 'kategori' => $kategori]) }}" class="inline-flex min-w-32 items-center justify-center rounded-lg bg-white-form px-5 py-3 text-sm font-bold text-gray-900 transition hover:bg-gray-300">Kembali</a>
    </div>
</div>
@endsection
