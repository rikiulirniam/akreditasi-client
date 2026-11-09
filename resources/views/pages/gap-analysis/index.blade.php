@extends('layouts.app')

@section('title', 'Gap Analysis Pendidikan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-extrabold tracking-tight text-gray-900">Gap Analysis Pendidikan</h1>
            <p class="mt-1 text-sm text-gray-500">Bandingkan skor aktual dengan target indikator pendidikan dari API assessment.</p>
        </div>
        <a href="{{ route('laporan-akhir.index') }}" class="inline-flex items-center gap-2 self-start rounded-xl bg-white-form px-4 py-2 text-xs font-bold text-gray-800 transition hover:bg-gray-200 sm:text-sm" title="Kembali ke Laporan Akhir">
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
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Indikator Pendidikan</p>
            <p class="mt-2 text-2xl font-extrabold text-gray-900">{{ $stats['total_indikator'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Memenuhi Target</p>
            <p class="mt-2 text-2xl font-extrabold text-emerald-700">{{ $stats['tercapai_count'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Di Bawah Target</p>
            <p class="mt-2 text-2xl font-extrabold text-rose-700">{{ $stats['gap_count'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Kesiapan Terhadap Target</p>
            <p class="mt-2 text-2xl font-extrabold text-gray-900">
                {{ $stats['persentase_kesiapan'] === null ? '—' : number_format($stats['persentase_kesiapan'], 1, ',', '.').'%' }}
            </p>
        </div>
    </div>

    <form method="GET" action="{{ route('gap-analysis.index') }}" class="grid gap-3 rounded-2xl border border-gray-200 bg-white p-4 sm:grid-cols-2 xl:grid-cols-4">
        <label class="text-xs font-bold text-gray-700">
            Tahun Penilaian
            <input type="number" name="tahun" min="2000" max="2100" value="{{ $selectedYear }}" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm font-medium text-gray-900">
        </label>
        <label class="text-xs font-bold text-gray-700">
            Status Indikator
            <select name="status" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-900">
                <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua status</option>
                <option value="gap_only" {{ $statusFilter === 'gap_only' ? 'selected' : '' }}>Di bawah target</option>
                <option value="target_met" {{ $statusFilter === 'target_met' ? 'selected' : '' }}>Memenuhi target</option>
            </select>
        </label>
        <label class="text-xs font-bold text-gray-700 sm:col-span-2">
            Cari Indikator atau Rekomendasi
            <input type="search" name="search" value="{{ $search }}" placeholder="Kode, nama indikator, rekomendasi" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm font-medium text-gray-900">
        </label>
        <div class="flex items-end gap-2 sm:col-span-2 xl:col-span-4">
            <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-additional">Terapkan Filter</button>
            <a href="{{ route('gap-analysis.index') }}" class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs">
        <div class="flex flex-col gap-1 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-gray-900">Rincian Skor dan Rekomendasi</h2>
                <p class="mt-1 text-xs text-gray-500">Tahun penilaian {{ $selectedYear }} · nilai bersumber dari API, tanpa skor contoh.</p>
            </div>
            <div class="text-xs text-gray-500">
                Rata-rata aktual:
                <span class="font-bold text-gray-900">{{ $stats['skor_aktual_avg'] === null ? '—' : number_format($stats['skor_aktual_avg'], 2, ',', '.') }}</span>
                <span class="mx-1">/</span>
                target:
                <span class="font-bold text-gray-900">{{ $stats['skor_target_avg'] === null ? '—' : number_format($stats['skor_target_avg'], 2, ',', '.') }}</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Indikator</th>
                        <th class="px-5 py-3">Standar</th>
                        <th class="px-5 py-3">Aktual / Target</th>
                        <th class="px-5 py-3">Gap</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Rekomendasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($indicators as $indicator)
                        @php
                            $actual = $indicator['aktual_value'];
                            $target = $indicator['target_value'];
                            $progress = is_numeric($actual) && is_numeric($target) && $target > 0
                                ? max(0, min(100, ((float) $actual / (float) $target) * 100))
                                : null;
                            $metTarget = $indicator['status'] === 'Memenuhi Target';
                        @endphp
                        <tr class="align-top">
                            <td class="min-w-52 px-5 py-4">
                                <span class="font-mono font-bold text-gray-900">{{ $indicator['kode'] }}</span>
                                <span class="mt-1 block font-semibold text-gray-800">{{ $indicator['nama'] }}</span>
                                <span class="mt-1 block text-xs text-gray-500">{{ $indicator['kriteria'] }}</span>
                            </td>
                            <td class="min-w-48 px-5 py-4 text-xs leading-relaxed text-gray-600">{{ $indicator['standar_deskripsi'] ?: '—' }}</td>
                            <td class="min-w-40 px-5 py-4">
                                <div class="font-bold text-gray-900">{{ $indicator['aktual_label'] }} <span class="font-normal text-gray-500">/ {{ $indicator['target_label'] }}</span></div>
                                @if($progress !== null)
                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full {{ $metTarget ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $progress }}%"></div>
                                    </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 font-bold {{ $indicator['gap_numeric'] !== null && $indicator['gap_numeric'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                {{ $indicator['gap_label'] }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $metTarget ? 'bg-emerald-50 text-emerald-700' : ($indicator['status'] === 'Di Bawah Target' ? 'bg-rose-50 text-rose-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ $indicator['status'] }}
                                </span>
                                @if($indicator['kategori_gap'])
                                    <span class="mt-1 block text-xs text-gray-500">{{ $indicator['kategori_gap'] }}</span>
                                @endif
                            </td>
                            <td class="min-w-64 px-5 py-4 text-xs leading-relaxed text-gray-700">
                                {{ $indicator['rekomendasi'] ?: 'Belum ada rekomendasi dari API.' }}
                                @if($indicator['pic'])
                                    <span class="mt-2 block text-gray-500">PIC: {{ $indicator['pic'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">
                                {{ $apiError ? 'Data analisis belum tersedia karena API tidak dapat memberikan hasil.' : 'Tidak ada indikator untuk filter yang dipilih.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
