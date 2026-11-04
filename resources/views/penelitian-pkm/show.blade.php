@extends('layouts.app')

@section('meta_title', 'Detail Kegiatan')
@section('title', 'Penelitian & PKM')

@section('subtitle')
    <a href="{{ route('penelitian-pkm.index') }}" class="hover:underline">Penelitian & PKM</a> &rsaquo; Detail
@endsection

@section('content')
@php
    $nilai = fn ($key) => filled($kegiatan[$key] ?? null) ? $kegiatan[$key] : '-';
    $jenis = $kegiatan['jenis_kegiatan']['nama_jenis_kegiatan'] ?? '-';
    $informasi = [
        'Jenis' => $jenis,
        'Skim' => $nilai('jenis_skim'),
        'Status' => $nilai('status_kegiatan'),
        'Afiliasi' => $nilai('afiliasi'),
        'Lokasi Kegiatan' => $nilai('lokasi_kegiatan'),
    ];
    $waktu = [
        'Tahun Usulan' => $nilai('tahun_usulan'),
        'Lama Kegiatan' => $nilai('lama_kegiatan'),
        'Tahun Kegiatan' => $nilai('tahun_kegiatan'),
        'Tahun Pelaksanaan' => $nilai('tahun_pelaksanaan'),
    ];
    $anggota = $kegiatan['anggota'] ?? [];
    $kartu = 'overflow-hidden rounded-[8px] bg-[#EEF3F7]';
    $judulKartu = 'flex h-[47px] items-center bg-[#002B49] px-5 text-base font-semibold text-[#FFC400] underline';
@endphp

<div class="space-y-5 pb-8">
    <a href="{{ route('penelitian-pkm.index') }}" class="inline-flex items-center gap-1 rounded-md border border-primary px-3 py-1 text-xs font-medium text-black transition hover:bg-gray-100">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" /></svg>
        Kembali ke daftar
    </a>

    <div class="grid gap-5 lg:grid-cols-12">
        <div class="space-y-5 lg:col-span-8">
            <section class="{{ $kartu }} px-5 py-4">
                <div class="mb-2 flex gap-2 text-[11px] font-semibold">
                    <span class="rounded bg-[#002B49] px-2.5 py-0.5 text-[#FFC400]">{{ $jenis }}</span>
                    <span class="rounded bg-green-600 px-2.5 py-0.5 text-white">{{ $informasi['Status'] }}</span>
                </div>
                <h2 class="text-lg font-bold text-black">{{ $nilai('judul_kegiatan') }}</h2>
            </section>

            <section class="{{ $kartu }}">
                <h3 class="{{ $judulKartu }}">Informasi Umum</h3>
                <dl class="grid gap-x-8 gap-y-5 p-6 sm:grid-cols-2">
                    @foreach($informasi as $label => $isi)
                        <div class="border-b border-[#BAC7D5] pb-2">
                            <dt class="text-[11px] uppercase text-gray-600">{{ $label }}</dt>
                            <dd class="text-sm font-medium text-black">{{ $isi }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="{{ $kartu }}">
                <h3 class="{{ $judulKartu }}">Waktu Pelaksanaan</h3>
                <div class="grid gap-4 p-5 sm:grid-cols-2">
                    @foreach($waktu as $label => $isi)
                        <div class="rounded-lg bg-white px-4 py-3">
                            <p class="text-xs uppercase text-[#002B49]">{{ $label }}</p>
                            <p class="mt-1 text-right text-2xl font-bold text-[#002B49]">{{ $isi }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="{{ $kartu }} self-start lg:col-span-4">
            <h3 class="{{ $judulKartu }}">Dosen</h3>
            <ul class="divide-y divide-[#BAC7D5] px-5">
                @forelse($anggota as $item)
                    <li class="flex items-center gap-3 py-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-[#002B49] text-[#002B49]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" /></svg>
                        </span>
                        <div class="min-w-0 text-sm leading-snug text-black">
                            <p class="font-medium">{{ $item['dosen']['nama_dosen'] ?? $item['mahasiswa']['nama_mahasiswa'] ?? $item['nama'] ?? '-' }}</p>
                            <p class="text-xs text-gray-600">{{ $item['peran'] ?? '-' }}</p>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-sm text-gray-600">Belum ada anggota.</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
@endsection