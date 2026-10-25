@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    $stats = [
        [
            'label' => 'Total Pengguna',
            'value' => 5,
            'note' => '4 akun aktif',
            'iconBg' => 'bg-sky-200',
            'iconColor' => 'text-sky-800',
            'icon' => 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21v-2a8 8 0 0 1 16 0v2',
            'dot' => 'bg-sky-700',
        ],
        [
            'label' => 'Aktivitas Hari Ini',
            'value' => 28,
            'note' => '',
            'iconBg' => 'bg-green-300',
            'iconColor' => 'text-green-800',
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.6L19 9v10a2 2 0 0 1-2 2Z',
            'dot' => 'bg-sky-700',
        ],
        [
            'label' => 'Project Aktif',
            'value' => 0,
            'note' => '0 Total Project',
            'iconBg' => 'bg-red-400',
            'iconColor' => 'text-red-900',
            'icon' => 'M12 9v4m0 4h.01M10 3h4l8 15H2L10 3Z',
            'dot' => 'bg-yellow-400',
        ],
        [
            'label' => 'Instrumen Aktif',
            'value' => 5,
            'note' => '720 indikator tersedia',
            'iconBg' => 'bg-yellow-300',
            'iconColor' => 'text-yellow-900',
            'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'dot' => 'bg-sky-700',
        ],
    ];

    $prioritas = [
        [
            'kode' => 'C1.1.1',
            'judul' => 'Ketersediaan dokumen formal — kejelasan & kerealistisan',
            'deadline' => '19 Okt 2026',
            'status' => 'Belum diisi',
        ],
        [
            'kode' => 'C1.1.2',
            'judul' => 'Tingkat ketercapaian target — kejelasan & kerealistisan',
            'deadline' => '22 Okt 2026',
            'status' => 'Belum diisi',
        ],
        [
            'kode' => 'C1.2.1',
            'judul' => 'Rasio dan kecukupan — mekanisme penyusunan &',
            'deadline' => '25 Okt 2026',
            'status' => 'Belum diisi',
        ],
        [
            'kode' => 'C1.2.2',
            'judul' => 'Monitoring dan evaluasi berkala — mekanisme penyusunan &',
            'deadline' => '28 Okt 2026',
            'status' => 'Belum diisi',
        ],
        [
            'kode' => 'C2.1.1',
            'judul' => 'Tingkat ketercapaian target — sistem tata',
            'deadline' => '31 Okt 2026',
            'status' => 'Belum diisi',
        ],
    ];

    $totalIndikator = 12;
    $indikatorTerisi = 0;
    $persentase = $totalIndikator > 0
        ? round(($indikatorTerisi / $totalIndikator) * 100)
        : 0;
@endphp

<div class="space-y-4 lg:space-y-5">

    {{-- Banner Selamat Datang --}}
    <section class="flex flex-col gap-5 rounded-2xl bg-[#06284F]
                    px-5 py-6 shadow-sm sm:px-7 sm:py-7
                    md:flex-row md:items-center md:justify-between">

        <div class="min-w-0">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em]
                      text-[#FFD400]">
                Pusat Kendali Sistem
            </p>

            <h2 class="mt-3 text-xl font-bold tracking-tight text-white
                       sm:text-2xl">
                Selamat datang, Superadmin
            </h2>

            <p class="mt-2 max-w-xl text-xs leading-relaxed text-white/60">
                Pantau pengguna, aktivitas sistem, serta kesiapan data
                master akreditasi dari satu tempat.
            </p>
        </div>

        <div class="shrink-0 rounded-xl border border-white/10
                    bg-white/10 px-4 py-3 md:min-w-[175px]">

            <p class="text-[9px] font-semibold uppercase tracking-wider
                      text-white/50">
                Status Sistem
            </p>

            <p class="mt-1.5 flex items-center gap-2 text-xs
                      font-semibold text-white">
                <span class="h-2 w-2 rounded-full bg-yellow-400"></span>
                Semua layanan normal
            </p>
        </div>
    </section>


    {{-- Kartu Statistik --}}
    <section class="grid grid-cols-1 gap-3 sm:grid-cols-2
                    xl:grid-cols-4">

        @foreach ($stats as $stat)
            <article class="relative flex min-h-[112px] flex-col
                            justify-between rounded-xl border
                            border-[#E5E7EB] bg-[#F0F4F7] p-4
                            shadow-sm transition hover:shadow-md">

                <span class="absolute right-3 top-3 h-1.5 w-1.5
                             rounded-full {{ $stat['dot'] }}"></span>

                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center
                                justify-center rounded-lg
                                {{ $stat['iconBg'] }}">

                        <svg class="h-5 w-5 {{ $stat['iconColor'] }}"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="{{ $stat['icon'] }}"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[9px] font-semibold uppercase
                                  tracking-wide text-gray-500">
                            {{ $stat['label'] }}
                        </p>

                        <p class="mt-0.5 text-2xl font-bold leading-none
                                  text-[#06284F]">
                            {{ $stat['value'] }}
                        </p>
                    </div>
                </div>

                <p class="mt-3 min-h-3 text-right text-[9px]
                          text-gray-400">
                    {{ $stat['note'] }}
                </p>
            </article>
        @endforeach

    </section>


    {{-- Progres Keseluruhan --}}
    <section class="rounded-xl border border-[#E5E7EB]
                    bg-[#F0F4F7] px-4 py-4 shadow-sm sm:px-5">

        <div class="mb-3 flex items-end justify-between gap-3">
            <div>
                <h3 class="text-xs font-bold text-[#06284F]">
                    Progres keseluruhan
                </h3>

                <p class="mt-1 text-[10px] text-gray-500">
                    {{ $indikatorTerisi }} dari {{ $totalIndikator }}
                    indikator sudah diisi
                </p>
            </div>

            <span class="text-base font-extrabold text-[#06284F]">
                {{ $persentase }}%
            </span>
        </div>

        <div class="h-2 overflow-hidden rounded-full bg-gray-300">
            <div
                class="h-full rounded-full bg-[#06284F]
                       transition-all duration-500"
                style="width: {{ $persentase }}%"
            ></div>
        </div>
    </section>


    {{-- Prioritas Pengisian --}}
    <section class="overflow-hidden rounded-xl border
                    border-[#E5E7EB] bg-[#F0F4F7] shadow-sm">

        {{-- Header --}}
        <div class="flex flex-col gap-3 border-b border-[#E0E5E9]
                    p-4 sm:flex-row sm:items-center
                    sm:justify-between sm:px-5">

            <div>
                <h3 class="text-sm font-bold text-[#06284F]">
                    Prioritas Pengisian
                </h3>

                <p class="mt-1 text-[10px] text-gray-500">
                    Indikator terdekat dengan tenggat pengumpulan.
                </p>
            </div>

            <a
                href="#daftar-prioritas"
                class="inline-flex items-center justify-center
                       rounded-lg bg-gray-200 px-4 py-2
                       text-[10px] font-semibold text-gray-700
                       transition hover:bg-gray-300"
            >
                Lihat semua
            </a>
        </div>


        {{-- Daftar Indikator --}}
        <div id="daftar-prioritas" class="divide-y divide-[#E0E5E9]">

            @foreach ($prioritas as $item)
                <a
                    href="{{ route('akreditasi.superadmin.kriteria') }}"
                    class="group flex flex-col gap-3 px-4 py-3
                           transition hover:bg-white/70
                           sm:flex-row sm:items-center
                           sm:justify-between sm:px-5"
                >

                    <div class="flex min-w-0 items-start gap-3">

                        <span class="w-10 shrink-0 pt-0.5 text-[9px]
                                     font-bold text-[#005A9C]">
                            {{ $item['kode'] }}
                        </span>

                        <div class="min-w-0">
                            <p class="text-[10px] font-bold leading-relaxed
                                      text-gray-800 transition
                                      group-hover:text-[#005A9C]">
                                {{ $item['judul'] }}
                            </p>

                            <p class="mt-0.5 text-[9px] text-gray-500">
                                Deadline {{ $item['deadline'] }}
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center
                                justify-end gap-3 pl-12 sm:pl-0">

                        <span class="rounded-full bg-gray-200
                                     px-2.5 py-1 text-[9px]
                                     font-medium text-gray-600">
                            {{ $item['status'] }}
                        </span>

                        <svg class="h-3.5 w-3.5 text-[#005A9C]
                                    transition group-hover:translate-x-1"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             aria-hidden="true">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </div>

                </a>
            @endforeach

        </div>
    </section>

</div>

@endsection