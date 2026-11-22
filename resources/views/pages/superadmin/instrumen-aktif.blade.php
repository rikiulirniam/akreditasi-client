@extends('layouts.app')

@section('title', 'Hierarki Instrumen Aktif (LAM-INFOKOM 2.1 TRK)')

@section('content')
<div class="space-y-6">

    <x-toast />

    {{-- Breadcrumbs --}}
    <nav class="flex text-xs sm:text-sm font-medium text-gray-500 py-1" aria-label="Breadcrumb">
        <ol class="inline-flex flex-wrap items-center gap-y-1">
            <li class="inline-flex items-center">
                <a href="{{ route('akreditasi.superadmin.lembaga_akreditasi') }}" class="hover:text-primary transition font-medium">Master Data Akreditasi</a>
            </li>
            <li class="inline-flex items-center" aria-current="page">
                <span class="mx-1 text-gray-400">/</span>
                <span class="font-bold text-gray-800">Hierarki Instrumen Aktif (LAM-INFOKOM 2.1 TRK)</span>
            </li>
        </ol>
    </nav>

    {{-- Banner Utama --}}
    <section class="flex flex-col justify-between gap-5 rounded-2xl bg-primary p-6 sm:p-7 shadow-xs sm:flex-row sm:items-center text-white">
        <div class="min-w-0">
            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-secondary">
                MASTER DATA STATIS
            </p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">
                Hierarki Instrumen LAM-INFOKOM 2.1 TRK
            </h2>
            <p class="mt-2 text-xs sm:text-sm text-white/80 leading-relaxed max-w-3xl">
                Struktur hierarki resmi instrumen akreditasi aktif: Kriteria 1–9 &rarr; Subkriteria &rarr; Butir Indikator Penilaian. Seluruh data dipetakan langsung dari endpoint statis backend tanpa pengisian lembar kerja manual.
            </p>
        </div>
        <div class="shrink-0 flex items-center gap-2">
            <a href="{{ route('akreditasi.superadmin.lembaga_akreditasi') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Lembaga
            </a>
        </div>
    </section>

    @if ($error)
        {{-- Error State --}}
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-rose-800 shadow-xs">
            <div class="flex items-start gap-3">
                <div class="shrink-0 p-1 text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-rose-900">Gagal Mengambil Data Instrumen Aktif</h3>
                    <p class="text-sm text-rose-700 leading-relaxed">{{ $error }}</p>
                    <div class="pt-3">
                        <a href="{{ url()->current() }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Muat Ulang Halaman
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @elseif ($instrumen)
        @php
            $namaLembaga = is_array($instrumen['lembaga'] ?? null)
                ? ($instrumen['lembaga']['nama'] ?? '-')
                : ($instrumen['lembaga'] ?? '-');

            $nomorVersi = is_array($instrumen['versi_instrumen'] ?? null)
                ? ($instrumen['versi_instrumen']['nomor_versi'] ?? '-')
                : ($instrumen['versi_instrumen'] ?? '-');

            $namaJenjang = is_array($instrumen['jenjang'] ?? null)
                ? ($instrumen['jenjang']['nama'] ?? '-')
                : ($instrumen['jenjang'] ?? '-');

            $namaJenis = is_array($instrumen['jenis_instrumen'] ?? null)
                ? ($instrumen['jenis_instrumen']['nama'] ?? '-')
                : ($instrumen['jenis_instrumen'] ?? '-');

            $kriterias = $instrumen['kriteria'] ?? [];
            $totalKriteria = count($kriterias);
            $totalSubkriteria = 0;
            $totalIndikator = 0;

            foreach ($kriterias as $k) {
                $subs = $k['subkriteria'] ?? [];
                $totalSubkriteria += count($subs);
                foreach ($subs as $s) {
                    $totalIndikator += count($s['indikator'] ?? []);
                }
            }
        @endphp

        {{-- Metadata Info Instrumen --}}
        <section class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4 rounded-2xl border border-[#EAEAEA] bg-white p-5 sm:p-6 shadow-xs">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Lembaga</p>
                <p class="mt-1 text-sm sm:text-base font-extrabold text-gray-900">{{ $namaLembaga }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Versi Instrumen</p>
                <p class="mt-1 text-sm sm:text-base font-extrabold text-primary font-mono">{{ $nomorVersi }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Jenjang</p>
                <p class="mt-1 text-sm sm:text-base font-extrabold text-gray-900">{{ $namaJenjang }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Jenis Instrumen</p>
                <p class="mt-1 text-xs sm:text-sm font-semibold text-gray-800 line-clamp-1" title="{{ $namaJenis }}">{{ $namaJenis }}</p>
            </div>
            <div class="col-span-2 sm:col-span-4 lg:col-span-1">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Statistik</p>
                <p class="mt-1 text-xs font-semibold text-emerald-700">
                    {{ $totalKriteria }} Kriteria &bull; {{ $totalSubkriteria }} Sub &bull; {{ $totalIndikator }} Indikator
                </p>
            </div>
        </section>

        {{-- Hierarki Kriteria -> Subkriteria -> Indikator --}}
        <div class="space-y-5">
            @forelse ($kriterias as $kriteria)
                @php
                    $subkriterias = $kriteria['subkriteria'] ?? [];
                    $subCount = count($subkriterias);
                @endphp
                <div class="overflow-hidden rounded-2xl border border-[#EAEAEA] bg-white shadow-xs transition hover:border-primary/40">

                    {{-- Level 1: Kriteria --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 bg-slate-50/80 px-5 sm:px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-xs font-extrabold bg-primary text-white font-mono shadow-xs">
                                {{ $kriteria['kode'] ?? 'K' }}
                            </span>
                            <h3 class="text-base sm:text-lg font-extrabold text-gray-900 tracking-tight">
                                {{ $kriteria['nama'] ?? '-' }}
                            </h3>
                        </div>
                        <div class="text-xs font-semibold text-gray-500">
                            {{ $subCount }} Subkriteria
                        </div>
                    </div>

                    <div class="p-5 sm:p-6 space-y-5">
                        @forelse ($subkriterias as $subkriteria)
                            @php
                                $indikators = $subkriteria['indikator'] ?? [];
                                $indCount = count($indikators);
                            @endphp
                            {{-- Level 2: Subkriteria --}}
                            <div class="rounded-xl border border-gray-200/80 bg-white p-4 sm:p-5 shadow-xs">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100">
                                    <div class="flex items-center gap-2.5">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-sky-100 text-sky-800 font-mono">
                                            {{ $subkriteria['kode'] ?? '-' }}
                                        </span>
                                        <h4 class="text-sm sm:text-base font-bold text-gray-800">
                                            {{ $subkriteria['nama'] ?? '-' }}
                                        </h4>
                                    </div>
                                    <span class="text-xs font-medium text-gray-500">
                                        {{ $indCount }} Indikator Penilaian
                                    </span>
                                </div>

                                {{-- Level 3: Butir Indikator --}}
                                <div class="mt-3.5 space-y-2.5">
                                    @forelse ($indikators as $indikator)
                                        <div class="rounded-xl bg-slate-50/70 p-3.5 border border-slate-200/60 hover:bg-sky-50/40 transition">
                                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                                                <div class="min-w-0 space-y-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-mono text-xs font-extrabold text-primary">
                                                            {{ $indikator['kode'] ?? '-' }}
                                                        </span>
                                                        <span class="text-xs px-2 py-0.5 rounded-md font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                                            Bobot: {{ $indikator['bobot'] ?? '1.0' }}
                                                        </span>
                                                    </div>
                                                    <p class="text-xs sm:text-sm font-medium text-gray-800 leading-relaxed">
                                                        {{ $indikator['nama'] ?? '-' }}
                                                    </p>
                                                </div>

                                                @if (!empty($indikator['target_sumber_data']))
                                                    <div class="shrink-0 text-right">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-200/70 text-slate-700">
                                                            Sumber: {{ $indikator['target_sumber_data'] }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-gray-400 py-2 italic">
                                            Belum ada butir indikator pada subkriteria ini.
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 py-3 italic">
                                Belum ada subkriteria pada kriteria ini.
                            </p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center text-gray-500 shadow-xs">
                    <p class="text-sm font-medium">Belum ada data kriteria pada instrumen aktif LAM-INFOKOM 2.1 TRK.</p>
                </div>
            @endforelse
        </div>
    @endif

</div>
@endsection
