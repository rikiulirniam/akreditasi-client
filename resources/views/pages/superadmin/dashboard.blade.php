@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('content')

@php
    $stats = $stats ?? [];
    $prioritas = $prioritas ?? [];
    $totalIndikator = $totalIndikator ?? 0;
    $indikatorTerisi = $indikatorTerisi ?? 0;
    $persentase = $persentase ?? 0;
@endphp

<div class="space-y-6">

    {{-- Banner Selamat Datang --}}
    <section class="flex flex-col gap-5 rounded-2xl bg-primary p-6 sm:p-8 text-white shadow-xs md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-secondary">
                Pusat Kendali Sistem
            </p>

            <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                Selamat datang, Superadmin
            </h2>

            <p class="mt-2 max-w-xl text-sm leading-relaxed text-white/80">
                Pantau pengguna, aktivitas sistem, serta kesiapan data master akreditasi dari satu tempat.
            </p>
        </div>

        <div class="shrink-0 rounded-2xl border border-white/15 bg-white/10 px-5 py-3.5 md:min-w-[190px]">
            <p class="text-xs font-bold uppercase tracking-wider text-white/60">
                Status Sistem
            </p>

            <p class="mt-1.5 flex items-center gap-2 text-sm font-bold text-white">
                <span class="h-2.5 w-2.5 rounded-full bg-secondary"></span>
                Semua layanan normal
            </p>
        </div>
    </section>

    {{-- Kartu Statistik --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <article class="relative flex flex-col justify-between rounded-2xl border border-[#EAEAEA] bg-white p-5 shadow-xs transition hover:shadow-md">
                <span class="absolute right-4 top-4 h-2 w-2 rounded-full {{ $stat['dot'] }}"></span>

                <div class="flex items-center gap-3.5">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $stat['iconBg'] }}">
                        <svg class="h-6 w-6 {{ $stat['iconColor'] }}"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="{{ $stat['icon'] }}"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
                            {{ $stat['label'] }}
                        </p>

                        <p class="mt-1 text-2xl sm:text-3xl font-extrabold leading-none text-gray-900 tracking-tight">
                            {{ $stat['value'] }}
                        </p>
                    </div>
                </div>

                <p class="mt-4 text-right text-xs font-medium text-gray-400">
                    {{ $stat['note'] }}
                </p>
            </article>
        @endforeach
    </section>

    {{-- Progres Keseluruhan --}}
    <section class="rounded-2xl border border-[#EAEAEA] bg-white p-6 shadow-xs">
        <div class="mb-3 flex items-end justify-between gap-3">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight">
                    Progres Keseluruhan
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $indikatorTerisi }} dari {{ $totalIndikator }} indikator sudah diisi
                </p>
            </div>

            <span class="text-xl sm:text-2xl font-extrabold text-primary">
                {{ $persentase }}%
            </span>
        </div>

        <div class="h-3 overflow-hidden rounded-full bg-gray-100">
            <div
                class="h-full rounded-full bg-primary transition-all duration-500"
                style="width: {{ $persentase }}%"
            ></div>
        </div>
    </section>

    {{-- Prioritas Pengisian --}}
    <section class="overflow-hidden rounded-2xl border border-[#EAEAEA] bg-white shadow-xs">
        {{-- Header --}}
        <div class="flex flex-col gap-3 border-b border-gray-200 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight">
                    Prioritas Pengisian
                </h3>

                <p class="mt-0.5 text-xs sm:text-sm text-gray-500">
                    Indikator terdekat dengan tenggat pengumpulan.
                </p>
            </div>

            <a
                href="#daftar-prioritas"
                class="inline-flex items-center justify-center rounded-xl bg-gray-100 px-4 py-2 text-xs sm:text-sm font-semibold text-gray-700 transition hover:bg-gray-200"
            >
                Lihat semua
            </a>
        </div>

        {{-- Daftar Indikator --}}
        <div id="daftar-prioritas" class="divide-y divide-gray-100">
            @forelse ($prioritas as $item)
                <a
                    href="{{ route('akreditasi.superadmin.kriteria') }}"
                    class="group flex flex-col gap-3 px-5 py-4 transition hover:bg-gray-50 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <div class="flex min-w-0 items-start gap-4">
                        <span class="w-14 shrink-0 font-mono text-sm font-bold text-primary">
                            {{ $item['kode'] }}
                        </span>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 transition group-hover:text-primary">
                                {{ $item['judul'] }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                Deadline: {{ $item['deadline'] }}
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                            {{ $item['status'] }}
                        </span>

                        <svg class="h-5 w-5 text-gray-400 group-hover:text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-sm text-gray-500 font-medium">
                    Belum ada indikator atau tugas pengisian yang terdaftar.
                </div>
            @endforelse
        </div>
    </section>

</div>

@endsection