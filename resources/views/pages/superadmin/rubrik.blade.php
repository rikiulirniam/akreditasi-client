@extends('layouts.app')

@section('title', 'Master Data Rubrik')

@section('content')
<div class="space-y-6">

    <x-toast />

    @if (session('success'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm font-semibold text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Breadcrumbs --}}
    <nav class="flex text-xs sm:text-sm font-medium text-gray-500 py-1" aria-label="Breadcrumb">
        <ol class="inline-flex flex-wrap items-center gap-y-1">
            <li class="inline-flex items-center">
                <a href="{{ route('akreditasi.superadmin.lembaga_akreditasi') }}" class="hover:text-primary transition font-medium">Master Data Akreditasi</a>
            </li>
            <li class="inline-flex items-center">
                <span class="mx-1 text-gray-400">/</span>
                <a href="{{ route('akreditasi.superadmin.versi_instrumen', $lembagaId) }}" class="hover:text-primary transition font-medium">{{ $lembaga['nama_lembaga'] ?? 'Lembaga' }}</a>
            </li>
            <li class="inline-flex items-center">
                <span class="mx-1 text-gray-400">/</span>
                <a href="{{ route('akreditasi.superadmin.kriteria', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId]) }}" class="hover:text-primary transition font-medium">{{ $instrumenTitle }}</a>
            </li>
            <li class="inline-flex items-center">
                <span class="mx-1 text-gray-400">/</span>
                <a href="{{ route('akreditasi.superadmin.subkriteria', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId, 'kriteriaId' => $kriteriaId]) }}" class="hover:text-primary transition font-medium">{{ $kriteria['kode_kriteria'] ?? 'Kriteria' }}</a>
            </li>
            <li class="inline-flex items-center">
                <span class="mx-1 text-gray-400">/</span>
                <a href="{{ route('akreditasi.superadmin.indikator', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId, 'kriteriaId' => $kriteriaId, 'subkriteriaId' => $subkriteriaId]) }}" class="hover:text-primary transition font-medium">{{ $subkriteria['kode_subkriteria'] ?? 'Subkriteria' }}</a>
            </li>
            <li class="inline-flex items-center" aria-current="page">
                <span class="mx-1 text-gray-400">/</span>
                <span class="font-bold text-gray-800">{{ ($indikator['kode_indikator'] ?? 'Indikator') . ' - ' . \Illuminate\Support\Str::limit($indikator['deskripsi_indikator'] ?? '', 35) }}</span>
            </li>
        </ol>
    </nav>

    {{-- Banner Utama --}}
    <section class="flex flex-col justify-between gap-5 rounded-2xl bg-primary p-6 sm:p-7 shadow-xs sm:flex-row sm:items-center text-white">
        <div class="min-w-0">
            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-secondary">
                DETAIL INDIKATOR
            </p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight font-mono">
                {{ $indikator['kode_indikator'] ?? 'Indikator' }}
            </h2>
            @if(!empty($indikator['deskripsi_indikator']))
                <p class="mt-2 text-base sm:text-lg font-semibold text-white/95 leading-relaxed">
                    {{ $indikator['deskripsi_indikator'] }}
                </p>
            @endif
            <p class="mt-2 text-xs sm:text-sm text-white/75">
                Kelola skala skor rubrik penilaian untuk indikator ini.
            </p>
        </div>
    </section>

    {{-- Metadata Info --}}
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-5 rounded-2xl border border-[#EAEAEA] bg-white p-5 sm:p-6 shadow-xs">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">KODE INDIKATOR</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900 font-mono">{{ $indikator['kode_indikator'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">SUBKRITERIA</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900 font-mono">
                {{ ($subkriteria['kode_subkriteria'] ?? '-') . ' - ' . ($subkriteria['nama_subkriteria'] ?? '-') }}
            </p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">TOTAL RUBRIK</p>
            <p class="mt-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    {{ count($rubriks ?? []) }} Skala Terdaftar
                </span>
            </p>
        </div>
        <div class="sm:col-span-3">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">DESKRIPSI INDIKATOR</p>
            <p class="mt-1.5 text-sm font-medium text-gray-800 leading-relaxed">{{ $indikator['deskripsi_indikator'] ?? '-' }}</p>
        </div>
    </section>

    {{-- Tabel Rubrik --}}
    <section class="space-y-4">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Daftar Rubrik Penilaian</h3>
                <p class="text-xs sm:text-sm text-gray-500">{{ count($rubriks ?? []) }} skala penilaian terkait</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#EAEAEA] shadow-xs overflow-hidden">
            <x-table>
                <x-slot:header>
                    <x-table.th class="w-24 text-center">Skor</x-table.th>
                    <x-table.th>Deskripsi Kondisi / Rubrik Penilaian</x-table.th>
                    <x-table.th class="w-44 text-center">Tingkat Capaian</x-table.th>
                </x-slot:header>

                @forelse ($rubriks ?? [] as $item)
                    @php
                        $skor = (int) ($item['skor'] ?? 0);
                        $badgeClass = match(true) {
                            $skor >= 4 => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            $skor == 3 => 'bg-sky-50 text-sky-700 border-sky-200',
                            $skor == 2 => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-rose-50 text-rose-700 border-rose-200',
                        };
                        $capaianLabel = match(true) {
                            $skor >= 4 => 'Sangat Baik (4)',
                            $skor == 3 => 'Baik (3)',
                            $skor == 2 => 'Cukup (2)',
                            default => 'Kurang (' . $skor . ')',
                        };
                    @endphp
                    <x-table.row class="hover:bg-sky-50/40 transition-colors">
                        <x-table.cell class="text-center">
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full font-bold text-sm bg-primary/10 text-primary">
                                {{ $skor }}
                            </span>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="text-sm font-medium text-gray-800 leading-relaxed block py-1">
                                {{ $item['deskripsi_rubrik'] ?? ($item['deskripsi_kondisi'] ?? ($item['deskripsi'] ?? '-')) }}
                            </span>
                        </x-table.cell>
                        <x-table.cell class="text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                {{ $capaianLabel }}
                            </span>
                        </x-table.cell>
                        
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="3" class="py-8 text-center text-sm font-medium text-gray-500">
                            Belum ada data rubrik penilaian untuk indikator ini.
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table>
        </div>
    </section>

</div>
@endsection