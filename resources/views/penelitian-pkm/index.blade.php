@extends('layouts.app')

@section('meta_title', 'Penelitian & PKM')
@section('title', 'Penelitian & PKM')

@section('subtitle')
    @if(!$apiError){{ number_format($pagination['total'], 0, ',', '.') }} Data ditemukan @endif
@endsection

@section('content')
@php
    $startNumber = $pagination['from'] ?? 1;
    $selectedStatus = $filters['status_kegiatan'] ?? '';
    $hasFilter = filled($filters['search'] ?? null) || $selectedStatus !== '';
@endphp

<div class="space-y-5 pb-8">
    @if($apiError)
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700" role="alert">{{ $apiError }}</div>
    @elseif(count($kegiatan) > 0 || $hasFilter)
        <form method="GET" action="{{ route('penelitian-pkm.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama"
                class="h-[34px] w-[248px] rounded-md border border-primary bg-white-form px-3 text-xs text-gray-900 placeholder:text-gray-600 focus:outline-none">
            <select name="status_kegiatan" onchange="this.form.submit()"
                class="h-[34px] rounded-md border border-primary bg-white-form px-2 text-xs text-gray-900 focus:outline-none">
                <option value="">Status</option>
                @foreach(['Pengajuan', 'Berjalan', 'Selesai', 'Ditolak'] as $status)
                    <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </form>

        @if(count($kegiatan) > 0)
            <x-table :headers="['No', 'Judul Kegiatan', 'Dosen', 'Jenis', 'Status', 'Tahun Kegiatan']">
                @foreach($kegiatan as $index => $item)
                    <x-table.row>
                        <x-table.cell>{{ $startNumber + $index }}.</x-table.cell>
                        <x-table.cell class="font-medium">{{ $item['judul_kegiatan'] ?? '-' }}</x-table.cell>
                        <x-table.cell>{{ $item['ketua'] }}</x-table.cell>
                        <x-table.cell>{{ $item['jenis_kegiatan']['nama_jenis_kegiatan'] ?? '-' }}</x-table.cell>
                        <x-table.cell>{{ $item['status_kegiatan'] ?? '-' }}</x-table.cell>
                        <x-table.cell>{{ $item['tahun_kegiatan'] ?? '-' }}</x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            <x-pagination :paginator="$pagination" />
        @else
            <p class="py-10 text-center text-sm text-gray-600">Data tidak ditemukan.</p>
        @endif
    @else
        <div class="flex min-h-[420px] flex-col items-center justify-center rounded-2xl border border-gray-100 bg-white px-5 py-12 text-center">
            <h2 class="text-2xl font-extrabold tracking-tight text-black sm:text-3xl">Belum Ada Penelitian</h2>
            <p class="mt-1 text-sm text-gray-600">Belum ada data kegiatan.</p>
        </div>
    @endif
</div>
@endsection