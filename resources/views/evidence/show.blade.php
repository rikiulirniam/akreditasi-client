@extends('layouts.app')

@section('browser-title', 'Detail Evidence')
@section('title', 'Evidence')
@section('subtitle', 'Detail Evidence')

@section('content')
@php
    $rawDate = $evidence['tanggal_upload'] ?? null;
    $tanggalUpload = '-';
    if ($rawDate) {
        try {
            $tanggalUpload = \Carbon\Carbon::parse($rawDate)->format('d/m/Y');
        } catch (\Throwable) {
            $tanggalUpload = (string) $rawDate;
        }
    }

    $driveUrl = $evidence['_drive_url'] ?? null;
    $previewUrl = null;
    if (is_string($driveUrl) && preg_match('~drive\.google\.com/file/d/([^/?]+)~', $driveUrl, $matches)) {
        $previewUrl = 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
    }

    $status = (string) ($evidence['status_validasi'] ?? '-');
    $statusClass = match (strtolower($status)) {
        'valid' => 'bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0]',
        'ditolak' => 'bg-[#FEE2E2] text-[#991B1B] border border-[#FECACA]',
        default => 'bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A]',
    };
@endphp

<div class="space-y-5 pb-8">
    <section class="overflow-hidden rounded-lg border border-gray-300 bg-white">
        <div class="bg-[#062D53] px-5 py-3 text-sm font-bold text-white">Pratinjau Dokumen</div>
        <div class="flex min-h-[360px] items-center justify-center bg-[#e5e5e5] p-5">
            @if($previewUrl)
                <iframe src="{{ $previewUrl }}" title="Pratinjau {{ $evidence['_display_file_name'] }}" class="h-[min(70vh,640px)] w-full border-0 bg-white" allow="autoplay"></iframe>
            @elseif($driveUrl)
                <a href="{{ $driveUrl }}" target="_blank" rel="noopener noreferrer" class="rounded-md bg-white px-5 py-3 text-sm font-semibold text-primary underline">Buka dokumen di Google Drive</a>
            @else
                <p class="text-sm text-gray-600">Tautan dokumen tidak tersedia dari API.</p>
            @endif
        </div>
    </section>

    <section class="grid gap-4 rounded-lg border border-gray-300 bg-[#d9d9d9] p-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <h2 class="text-sm font-bold text-gray-900">Nama File</h2>
            <p class="mt-1 break-words text-sm text-gray-800">{{ $evidence['_display_file_name'] }}</p>
        </div>
        <div>
            <h2 class="text-sm font-bold text-gray-900">Jenis Dokumen</h2>
            <p class="mt-1 break-words text-sm text-gray-800">{{ $evidence['jenis_dokumen'] ?? '-' }}</p>
        </div>
        <div>
            <h2 class="text-sm font-bold text-gray-900">Tanggal Upload</h2>
            <p class="mt-1 text-sm text-gray-800">{{ $tanggalUpload }}</p>
        </div>
        <div>
            <h2 class="text-sm font-bold text-gray-900">Status</h2>
            <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">{{ $status }}</span>
        </div>
        @if($driveUrl)
            <div class="sm:col-span-2 lg:col-span-4">
                <h2 class="text-sm font-bold text-gray-900">Tautan Evidence</h2>
                <a href="{{ $driveUrl }}" target="_blank" rel="noopener noreferrer" class="mt-1 block break-all text-sm text-primary underline">{{ $driveUrl }}</a>
            </div>
        @endif
    </section>

    <div class="flex justify-end gap-3">
        @if($driveUrl)
            <a href="{{ $driveUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md bg-[#005A91] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#004674]">Buka File</a>
        @endif
        <a href="{{ route('evidence.index', request()->query()) }}" class="inline-flex items-center justify-center rounded-md bg-[#d9d9d9] px-6 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-300">Kembali</a>
    </div>
</div>
@endsection
