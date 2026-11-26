@extends('layouts.app')

@section('browser-title', 'Detail Evidence')
@section('title', 'Evidence')
@section('subtitle', 'Detail Informasi & Riwayat Dokumen Bukti')

@section('content')
@php
    $evidenceId = $evidence['id_evidence'] ?? $evidence['id'] ?? null;
    $namaFile = $evidence['_display_file_name'] ?? 'Dokumen Tanpa Judul';
    $driveUrl = $evidence['_drive_url'] ?? null;
    $jenisDokumen = $evidence['jenis_dokumen'] ?? '-';
    $tanggalUpload = $evidence['_formatted_date'] ?? '-';
    $status = trim((string) ($evidence['status_validasi'] ?? 'Menunggu Validasi'));
    $catatanValidasi = $evidence['catatan_validasi'] ?? null;
    $history = $evidence['history'] ?? [];

    $statusNormalized = strtolower($status);
    $statusBadgeClass = match ($statusNormalized) {
        'valid' => 'bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0]',
        'ditolak' => 'bg-[#FEE2E2] text-[#991B1B] border border-[#FECACA]',
        default => 'bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A]',
    };

    // Google Drive Preview URL helper
    $previewUrl = null;
    if (is_string($driveUrl) && preg_match('~drive\.google\.com/file/d/([^/?]+)~', $driveUrl, $matches)) {
        $previewUrl = 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
    }
@endphp

<div class="w-full max-w-full overflow-x-hidden min-w-0 space-y-6 pb-12">
    {{-- Header Action Bar: Tombol Kembali & Buka Tautan Asli --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a
            href="{{ route('evidence.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-2xs transition hover:bg-gray-50 cursor-pointer"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Daftar</span>
        </a>

        @if($driveUrl)
            <div class="flex items-center gap-2">
                <a
                    href="{{ $driveUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white shadow-xs hover:bg-additional transition cursor-pointer"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                    <span>Buka Tautan Asli</span>
                </a>
            </div>
        @endif
    </div>

    {{-- Grid 2 Kolom: Kiri (Pratinjau) & Kanan (Informasi Evidence) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Kolom Kiri: Pratinjau Dokumen Universal --}}
        <div class="lg:col-span-7 space-y-6">
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs">
                <div class="border-b border-gray-100 bg-[#062D53] px-5 py-3.5 flex items-center justify-between text-white">
                    <h2 class="text-sm font-bold tracking-tight">Pratinjau Dokumen</h2>
                    <span class="text-xs text-gray-300 font-medium">{{ $jenisDokumen }}</span>
                </div>

                <div class="min-h-[460px] flex items-center justify-center bg-gray-50 p-4">
                    @if($previewUrl)
                        <iframe
                            src="{{ $previewUrl }}"
                            title="Pratinjau {{ $namaFile }}"
                            class="h-[540px] w-full rounded-xl border border-gray-200 bg-white shadow-inner"
                            allow="autoplay"
                        ></iframe>
                    @elseif($driveUrl)
                        <div class="text-center p-8 max-w-md">
                            <div class="mx-auto w-16 h-16 rounded-2xl bg-sky-100 text-primary flex items-center justify-center mb-4">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-gray-900 mb-1.5">{{ $namaFile }}</h3>
                            <p class="text-xs text-gray-500 mb-5 break-all">{{ $driveUrl }}</p>
                            <a
                                href="{{ $driveUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-xs hover:bg-additional transition cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                                <span>Buka Dokumen di Tab Baru</span>
                            </a>
                        </div>
                    @else
                        <div class="text-center p-8 text-gray-500">
                            <p class="text-sm">Tautan dokumen tidak tersedia.</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Tabel Riwayat Perubahan Tautan jika Ada --}}
            @if(!empty($history) && is_array($history) && count($history) > 0)
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs">
                    <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Riwayat Pergantian Tautan</span>
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 text-gray-500">
                                    <th class="py-2 px-2.5 font-semibold">Tanggal</th>
                                    <th class="py-2 px-2.5 font-semibold">Tautan Lama</th>
                                    <th class="py-2 px-2.5 font-semibold">Tautan Baru</th>
                                    <th class="py-2 px-2.5 font-semibold">Alasan Perubahan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($history as $item)
                                    <tr>
                                        <td class="py-2 px-2.5 whitespace-nowrap text-gray-600">
                                            {{ isset($item['created_at']) ? \Carbon\Carbon::parse($item['created_at'])->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td class="py-2 px-2.5 max-w-[140px] truncate text-gray-500" title="{{ $item['url_lama'] ?? '' }}">
                                            {{ $item['url_lama'] ?? '-' }}
                                        </td>
                                        <td class="py-2 px-2.5 max-w-[140px] truncate text-primary font-medium" title="{{ $item['url_baru'] ?? '' }}">
                                            {{ $item['url_baru'] ?? '-' }}
                                        </td>
                                        <td class="py-2 px-2.5 text-gray-800">
                                            {{ $item['alasan_perubahan'] ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>

        {{-- Kolom Kanan: Informasi Evidence --}}
        <div class="lg:col-span-5 space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs space-y-5">
                <div class="flex items-center justify-between pb-3.5 border-b border-gray-100">
                    <h2 class="text-base font-bold text-gray-900 tracking-tight">Informasi Evidence</h2>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $statusBadgeClass }}">
                        {{ $status }}
                    </span>
                </div>

                <div class="space-y-4 text-sm">
                    <div>
                        <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama File / Judul</span>
                        <p class="mt-1 font-bold text-gray-900 break-words text-base leading-snug">{{ $namaFile }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                        <div>
                            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Dokumen</span>
                            <span class="mt-1.5 inline-flex rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-800">
                                {{ $jenisDokumen }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Upload</span>
                            <p class="mt-1.5 font-medium text-gray-800">{{ $tanggalUpload }}</p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-100">
                        <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Validasi</span>
                        <div class="mt-1.5 flex items-center gap-2">
                            <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClass }}">
                                {{ $status }}
                            </span>
                            <span class="text-xs text-gray-500">
                                @if($statusNormalized === 'valid')
                                    Dokumen telah disetujui.
                                @elseif($statusNormalized === 'ditolak')
                                    Dokumen memerlukan perbaikan.
                                @else
                                    Dokumen sedang menunggu pemeriksaan.
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-100">
                        <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Tautan Dokumen</span>
                        @if($driveUrl)
                            <a
                                href="{{ $driveUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-1.5 inline-flex items-center gap-1.5 break-all text-xs font-medium text-primary hover:text-additional hover:underline"
                            >
                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                                <span>{{ $driveUrl }}</span>
                            </a>
                        @else
                            <p class="mt-1.5 text-xs text-gray-400 italic">Tautan tidak tersedia</p>
                        @endif
                    </div>

                    @if(!empty($catatanValidasi))
                        <div class="pt-2 border-t border-gray-100">
                            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Catatan Validasi</span>
                            <div class="mt-2 rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 text-xs text-amber-900 leading-relaxed">
                                {{ $catatanValidasi }}
                            </div>
                        </div>
                    @endif

                    {{-- Relasi Kegiatan jika Tersedia --}}
                    @if(!empty($evidence['kegiatan']) && is_array($evidence['kegiatan']))
                        <div class="pt-2 border-t border-gray-100">
                            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Kegiatan Terkait</span>
                            <p class="mt-1.5 font-semibold text-gray-900 text-xs">
                                {{ $evidence['kegiatan']['nama_kegiatan'] ?? $evidence['kegiatan']['judul'] ?? 'ID: ' . ($evidence['id_kegiatan'] ?? '-') }}
                            </p>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
