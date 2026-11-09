@extends('layouts.app')

@section('title', 'Evaluasi Ketercapaian CPL')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-extrabold tracking-tight text-gray-900">Evaluasi Ketercapaian CPL</h1>
            <p class="mt-1 text-sm text-gray-500">Skor dan status capaian berdasarkan hasil kalkulasi API akademik.</p>
        </div>
        <a href="{{ route('cpl.index') }}" class="inline-flex items-center gap-2 self-start rounded-xl bg-white-form px-4 py-2 text-xs font-bold text-gray-800 transition hover:bg-gray-200 sm:text-sm" title="Kembali ke Data CPL">
            <svg class="h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    @if($apiError)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="alert">
            {{ $apiError }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Butir CPL', 'value' => $stats['total_cpl']],
            ['label' => 'Tercapai', 'value' => $stats['tercapai_count']],
            ['label' => 'Belum Tercapai', 'value' => $stats['belum_tercapai_count']],
            ['label' => 'Rata-rata Skor', 'value' => $stats['rata_rata_skor'] === null ? '—' : number_format($stats['rata_rata_skor'], 1, ',', '.').'%'],
        ] as $card)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-2 text-2xl font-extrabold text-gray-900">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('cpl.evaluasi') }}" class="grid gap-3 rounded-2xl border border-gray-200 bg-white p-4 sm:grid-cols-2 xl:grid-cols-4">
        <label class="text-xs font-bold text-gray-700">
            Kurikulum
            <select name="id_kurikulum" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-900">
                @forelse($kurikulumList as $kurikulum)
                    @php
                        $kurikulumId = (string) ($kurikulum['id'] ?? '');
                        $kurikulumNama = $kurikulum['nama_kurikulum'] ?? $kurikulum['nama'] ?? 'Kurikulum '.$kurikulumId;
                    @endphp
                    <option value="{{ $kurikulumId }}" {{ $selectedKurikulum === $kurikulumId ? 'selected' : '' }}>{{ $kurikulumNama }}</option>
                @empty
                    <option value="">Tidak ada kurikulum</option>
                @endforelse
            </select>
        </label>

        <label class="text-xs font-bold text-gray-700">
            Angkatan
            <select name="angkatan" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-900">
                @forelse($angkatanList as $angkatan)
                    @php
                        $angkatanValue = (string) ($angkatan['angkatan'] ?? $angkatan['tahun'] ?? $angkatan['id'] ?? '');
                    @endphp
                    <option value="{{ $angkatanValue }}" {{ $selectedAngkatan === $angkatanValue ? 'selected' : '' }}>{{ $angkatan['angkatan'] ?? $angkatan['tahun'] ?? $angkatanValue }}</option>
                @empty
                    <option value="">Tidak ada angkatan</option>
                @endforelse
            </select>
        </label>

        <label class="text-xs font-bold text-gray-700">
            Kategori
            <select name="kategori" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-900">
                <option value="">Semua kategori</option>
                @foreach(['Sikap', 'Pengetahuan', 'Keterampilan Umum', 'Keterampilan Khusus'] as $kategori)
                    <option value="{{ $kategori }}" {{ $selectedKategori === $kategori ? 'selected' : '' }}>{{ $kategori }}</option>
                @endforeach
            </select>
        </label>

        <div class="flex items-end gap-2">
            <label class="min-w-0 flex-1 text-xs font-bold text-gray-700">
                Cari CPL
                <input type="search" name="search" value="{{ $search }}" placeholder="Kode atau deskripsi" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm font-medium text-gray-900">
            </label>
            <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-additional">Terapkan</button>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="font-bold text-gray-900">Skor per Butir CPL</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Kode / Kategori</th>
                        <th class="px-5 py-3">Deskripsi CPL</th>
                        <th class="px-5 py-3">Skor / Target</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Mata Kuliah Pendukung</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($evaluasiList as $item)
                        @php
                            $isAchieved = $item['status'] === 'Tercapai';
                            $scoreWidth = is_numeric($item['skor_aktual']) ? max(0, min(100, (float) $item['skor_aktual'])) : 0;
                            $supportedCourses = $item['mata_kuliah_pendukung'];
                        @endphp
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="font-mono font-bold text-gray-900">{{ $item['kode_cpl'] }}</span>
                                <span class="mt-1 block text-xs text-gray-500">{{ $item['kategori'] }}</span>
                            </td>
                            <td class="max-w-xl px-5 py-4 text-gray-700">{{ $item['deskripsi_cpl'] }}</td>
                            <td class="min-w-40 px-5 py-4">
                                <div class="font-bold text-gray-900">
                                    {{ $item['skor_aktual'] === null ? '—' : number_format($item['skor_aktual'], 1, ',', '.').'%' }}
                                    <span class="font-normal text-gray-500">/ {{ number_format($item['target'], 1, ',', '.') }}%</span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full {{ $isAchieved ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $scoreWidth }}%"></div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $isAchieved ? 'bg-emerald-50 text-emerald-700' : ($item['status'] === 'Belum Tercapai' ? 'bg-rose-50 text-rose-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ $item['status'] }}
                                </span>
                                @if($item['rekomendasi'])
                                    <p class="mt-2 max-w-xs whitespace-normal text-xs text-gray-500">{{ $item['rekomendasi'] }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">
                                @if(is_array($supportedCourses))
                                    @forelse($supportedCourses as $course)
                                        <span class="mb-1 block">{{ is_array($course) ? ($course['nama_mata_kuliah'] ?? $course['nama'] ?? '-') : $course }}</span>
                                    @empty
                                        <span class="text-gray-400">—</span>
                                    @endforelse
                                @else
                                    {{ $supportedCourses ?: '—' }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-500">
                                {{ $apiError ? 'Data evaluasi belum tersedia karena API tidak dapat memberikan hasil.' : 'Tidak ada hasil CPL untuk filter yang dipilih.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
