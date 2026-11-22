@extends('layouts.app')

@section('title', 'Edit Data Lulusan')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Edit Data Lulusan</h2>
        </div>
        <div>
            <a
                href="{{ route('lulusan.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary hover:bg-additional text-white text-xs font-bold shadow-xs transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Lulusan
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-white-bone shadow-xs p-6 sm:p-8">
        <!-- Info Header -->
        <div class="mb-6 pb-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-gray-900">
                    {{ $mahasiswa['nama'] ?? $mahasiswa['nama_mahasiswa'] ?? '-' }}
                </h3>
                <p class="text-xs text-gray-500 font-mono mt-0.5">
                    NIM: {{ $mahasiswa['nim_display'] ?? $mahasiswa['nim'] ?? '-' }}
                    @if(!empty($mahasiswa['tahun_masuk']))
                        &bull; Angkatan {{ $mahasiswa['tahun_masuk'] }}
                    @endif
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 w-fit">
                Pembaruan Data Kelulusan & Karir
            </span>
        </div>

        <form method="POST" action="{{ route('lulusan.update', $mahasiswa['id']) }}" class="space-y-6" novalidate>
            @csrf
            @method('PUT')

            <input type="hidden" name="nim" value="{{ $mahasiswa['nim'] ?? '' }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tanggal Kelulusan -->
                <div class="space-y-1.5">
                    <label for="tanggal_kelulusan" class="block text-xs font-bold text-gray-900">
                        Tanggal Kelulusan
                    </label>
                    <input
                        type="date"
                        name="tanggal_kelulusan"
                        id="tanggal_kelulusan"
                        value="{{ old('tanggal_kelulusan', !empty($mahasiswa['tanggal_kelulusan']) ? \Carbon\Carbon::parse($mahasiswa['tanggal_kelulusan'])->format('Y-m-d') : '') }}"
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer @error('tanggal_kelulusan') ring-2 ring-red-400 @enderror"
                    >
                    <p class="text-[11px] text-gray-400">Tanggal resmi yudisium atau kelulusan mahasiswa.</p>
                    @error('tanggal_kelulusan')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Masa Tunggu (Bulan) -->
                <div class="space-y-1.5">
                    <label for="masa_tunggu_bulan" class="block text-xs font-bold text-gray-900">
                        Masa Tunggu Kerja (Bulan)
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            name="masa_tunggu_bulan"
                            id="masa_tunggu_bulan"
                            min="0"
                            max="120"
                            placeholder="Contoh: 3"
                            value="{{ old('masa_tunggu_bulan', $mahasiswa['masa_tunggu_bulan'] ?? '') }}"
                            class="w-full px-4 py-2.5 pr-12 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none @error('masa_tunggu_bulan') ring-2 ring-red-400 @enderror"
                        >
                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-xs font-semibold text-gray-400">
                            Bulan
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-400">Waktu yang dibutuhkan lulusan untuk memperoleh pekerjaan pertama (standar: &le; 6 bulan).</p>
                    @error('masa_tunggu_bulan')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tempat Kerja -->
                <div class="space-y-1.5 md:col-span-2">
                    <label for="tempat_kerja" class="block text-xs font-bold text-gray-900">
                        Tempat Bekerja / Nama Instansi / Perusahaan
                    </label>
                    <input
                        type="text"
                        name="tempat_kerja"
                        id="tempat_kerja"
                        placeholder="Contoh: PT Bank Mandiri (Persero) Tbk"
                        value="{{ old('tempat_kerja', $mahasiswa['tempat_kerja'] ?? '') }}"
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-400 @error('tempat_kerja') ring-2 ring-red-400 @enderror"
                    >
                    <p class="text-[11px] text-gray-400">Nama perusahaan, instansi, atau organisasi tempat lulusan saat ini bekerja.</p>
                    @error('tempat_kerja')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                <a
                    href="{{ route('lulusan.show', $mahasiswa['nim'] ?? $mahasiswa['id']) }}"
                    class="px-6 py-2.5 rounded-[10px] bg-[#E5E7EB] hover:bg-gray-300 text-gray-900 font-bold text-sm transition text-center"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="px-6 py-2.5 rounded-[10px] bg-primary hover:bg-additional text-white font-bold text-sm shadow-xs transition cursor-pointer"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
