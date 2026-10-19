@extends('layouts.app')

@section('title', 'Sertifikasi')

@section('subtitle')
    @if(!$apiError)
        {{ number_format((int) ($pagination['total'] ?? 0), 0, ',', '.') }} Data ditemukan
    @endif
@endsection

@section('content')
@php
    $ownerQuery = array_filter([
        'tipe_pemilik' => $filters['tipe_pemilik'] ?? null,
        'id_dosen' => $filters['id_dosen'] ?? null,
        'id_tendik' => $filters['id_tendik'] ?? null,
    ], static fn ($value) => $value !== null && $value !== '');
    $createUrl = route('sertifikasi.create', $ownerQuery);
    $startNumber = (int) ($pagination['from'] ?? (((int) ($pagination['current_page'] ?? 1) - 1) * (int) ($pagination['per_page'] ?? 10) + 1));
    $hasActiveFilters = array_filter([
        $filters['search'] ?? null,
        $filters['tahun_sertifikasi'] ?? null,
        $filters['status_sertifikasi'] ?? null,
    ], static fn ($value) => $value !== null && $value !== '') !== [];
@endphp

<div class="space-y-5 pb-8">
    @if($apiError)
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700" role="alert">
            {{ $apiError }}
        </div>
    @else
        @if(count($certifications) > 0 || $hasActiveFilters)
        <form method="GET" action="{{ route('sertifikasi.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            @foreach($ownerQuery as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="flex flex-1 flex-wrap items-center gap-3">
                <label class="relative min-w-[220px] flex-1 sm:max-w-sm">
                    <span class="sr-only">Cari sertifikasi</span>
                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Cari nama sertifikasi..."
                        class="w-full border border-gray-300 bg-white px-4 py-2.5 pr-10 text-xs text-gray-900 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                    <svg class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </label>

                <label>
                    <span class="sr-only">Tahun sertifikasi</span>
                    <select name="tahun_sertifikasi" class="min-w-40 border border-gray-300 px-3.5 py-2.5 text-xs font-medium text-gray-800 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">Semua Tahun</option>
                        @for($year = (int) date('Y') + 1; $year >= 2000; $year--)
                            <option value="{{ $year }}" @selected((string) ($filters['tahun_sertifikasi'] ?? '') === (string) $year)>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </label>

                <label>
                    <span class="sr-only">Status sertifikasi</span>
                    <select name="status_sertifikasi" class="min-w-40 border border-gray-300 px-3.5 py-2.5 text-xs font-medium text-gray-800 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">Semua Status</option>
                        @foreach(['Aktif', 'Kedaluwarsa'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status_sertifikasi'] ?? '') === $status)>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-xs font-bold text-white transition hover:bg-additional">
                    Terapkan
                </button>
            </div>

            <a href="{{ $createUrl }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-additional px-4 py-2.5 text-xs font-bold text-white transition hover:bg-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Sertifikasi
            </a>
        </form>

            <x-table>
                <x-slot:header>
                    <x-table.th>No</x-table.th>
                    <x-table.th>Jenis Sertifikasi</x-table.th>
                    <x-table.th>Nomor Sertifikat</x-table.th>
                    <x-table.th>Status Sertifikasi</x-table.th>
                    <x-table.th>Tahun Sertifikasi</x-table.th>
                    <x-table.th class="text-right">Action</x-table.th>
                </x-slot:header>

                @foreach($certifications as $index => $certification)
                    @php
                        $certificationId = $certification['id'] ?? $certification['id_sertifikasi'] ?? null;
                        $status = $certification['status_sertifikasi'] ?? '-';
                        $year = $certification['tahun_sertifikasi'] ?? '-';
                        $year = preg_match('/^\d{4}/', (string) $year) ? substr((string) $year, 0, 4) : $year;
                    @endphp
                    <x-table.row>
                        <x-table.cell class="w-14 text-gray-500">{{ $startNumber + $index }}.</x-table.cell>
                        <x-table.cell class="min-w-52 font-semibold text-gray-900">
                            {{ $certification['jenis_sertifikasi'] ?? '-' }}
                        </x-table.cell>
                        <x-table.cell class="min-w-40">{{ $certification['nomor_sertifikat'] ?? '-' }}</x-table.cell>
                        <x-table.cell>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ strtolower($status) === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $status }}
                            </span>
                        </x-table.cell>
                        <x-table.cell>{{ $year }}</x-table.cell>
                        <x-table.cell class="min-w-28">
                            @if($certificationId)
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('sertifikasi.edit', $certificationId) }}" class="text-amber-500 transition hover:text-amber-700" title="Edit sertifikasi" aria-label="Edit sertifikasi">
                                        <x-icon.edit class="h-5 w-5" />
                                    </a>
                                    <form method="POST" action="{{ route('sertifikasi.destroy', ['sertifikasi' => $certificationId] + request()->only(['search', 'tipe_pemilik', 'id_dosen', 'id_tendik', 'tahun_sertifikasi', 'status_sertifikasi'])) }}" data-confirm-delete data-title="Hapus Sertifikasi?" data-message="Apakah Anda yakin ingin menghapus sertifikasi ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.">
                                        @csrf
                                        @method('DELETE')
                                        @foreach(request()->only(['search', 'tipe_pemilik', 'id_dosen', 'id_tendik', 'tahun_sertifikasi', 'status_sertifikasi']) as $key => $value)
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endforeach
                                        <button type="submit" class="text-red-600 transition hover:text-red-800" title="Hapus sertifikasi" aria-label="Hapus sertifikasi">
                                            <x-icon.delete class="h-5 w-5" />
                                        </button>
                                    </form>
                                    <a href="{{ route('sertifikasi.show', $certificationId) }}" class="text-primary transition hover:text-additional" title="Lihat detail sertifikasi" aria-label="Lihat detail sertifikasi">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.5 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            <x-pagination :paginator="$pagination" />
        @else
            <div class="flex min-h-[420px] flex-col items-center justify-center rounded-2xl border border-gray-100 bg-white px-5 py-12 text-center">
                <h2 class="text-2xl font-extrabold tracking-tight text-black sm:text-3xl">Belum Ada Data Sertifikasi</h2>
                <p class="mt-1 text-sm text-gray-600">Tekan tombol di bawah untuk menambah data</p>
                <a href="{{ $createUrl }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-additional px-6 py-2.5 text-xs font-semibold text-white transition hover:bg-primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Sertifikasi
                </a>
            </div>
        @endif
    @endif
</div>
@endsection
