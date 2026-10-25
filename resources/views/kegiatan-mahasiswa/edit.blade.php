@extends('layouts.app')

@section('title', 'Edit Kegiatan Mahasiswa')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Edit Kegiatan Mahasiswa</h2>
        </div>
        <div>
            <a
                href="{{ route('kegiatan-mahasiswa.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary hover:bg-additional text-white text-xs font-bold shadow-xs transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-white-bone shadow-xs p-6 sm:p-8">
        <form method="POST" action="{{ route('kegiatan-mahasiswa.update', $kegiatan['id']) }}" class="space-y-6" novalidate>
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-5">

                <!-- Mahasiswa -->
                <div class="space-y-1.5">
                    <label for="mahasiswa_id" class="block text-xs font-semibold text-gray-700">
                        Mahasiswa <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select
                            name="mahasiswa_id"
                            id="mahasiswa_id"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[10px] bg-white-form border-none text-gray-900 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer transition @error('mahasiswa_id') ring-2 ring-red-400 @enderror"
                        >
                            <option value="">-- Pilih Mahasiswa --</option>
                            @foreach($mahasiswaList as $mhs)
                                <option value="{{ $mhs['id'] }}" {{ old('mahasiswa_id', $kegiatan['mahasiswa_id']) == $mhs['id'] ? 'selected' : '' }}>
                                    {{ $mhs['nim'] ?? '' }} - {{ $mhs['nama'] ?? $mhs['nama_mahasiswa'] ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>
                    @error('mahasiswa_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tahun Akademik -->
                <div class="space-y-1.5">
                    <label for="tahun_akademik_id" class="block text-xs font-semibold text-gray-700">
                        Tahun Akademik <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select
                            name="tahun_akademik_id"
                            id="tahun_akademik_id"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[10px] bg-white-form border-none text-gray-900 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer transition @error('tahun_akademik_id') ring-2 ring-red-400 @enderror"
                        >
                            <option value="">-- Pilih Tahun Akademik --</option>
                            @foreach($tahunAkademikList as $ta)
                                @php
                                    $taLabel = trim(($ta['tahun'] ?? $ta['tahun_akademik'] ?? $ta['nama'] ?? '') . (!empty($ta['semester']) ? ' (' . $ta['semester'] . ')' : ''));
                                    if (empty($taLabel)) {
                                        $taLabel = '-';
                                    }
                                @endphp
                                <option value="{{ $ta['id'] }}" {{ old('tahun_akademik_id', $kegiatan['tahun_akademik_id']) == $ta['id'] ? 'selected' : '' }}>
                                    {{ $taLabel }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>
                    @error('tahun_akademik_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Kegiatan -->
                <div class="space-y-1.5 lg:col-span-2">
                    <label for="nama_kegiatan" class="block text-xs font-semibold text-gray-700">
                        Nama Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <x-input
                        type="text"
                        name="nama_kegiatan"
                        id="nama_kegiatan"
                        placeholder="Contoh: Turnamen Mobile Legends"
                        value="{{ old('nama_kegiatan', $kegiatan['nama_kegiatan']) }}"
                        class="{{ $errors->has('nama_kegiatan') ? 'ring-2 ring-red-400' : '' }}"
                    />
                    @error('nama_kegiatan')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Kegiatan -->
                <div class="space-y-1.5">
                    <label for="jenis_kegiatan" class="block text-xs font-semibold text-gray-700">
                        Jenis Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select
                            name="jenis_kegiatan"
                            id="jenis_kegiatan"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[10px] bg-white-form border-none text-gray-900 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer transition @error('jenis_kegiatan') ring-2 ring-red-400 @enderror"
                        >
                            <option value="">-- Pilih Jenis Kegiatan --</option>
                            @foreach(['Organisasi', 'Lomba', 'Seminar', 'Magang', 'UKM'] as $jenis)
                                <option value="{{ $jenis }}" {{ old('jenis_kegiatan', $kegiatan['jenis_kegiatan']) == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>
                    @error('jenis_kegiatan')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Peran -->
                <div class="space-y-1.5">
                    <label for="peran" class="block text-xs font-semibold text-gray-700">
                        Peran <span class="text-red-500">*</span>
                    </label>
                    <x-input
                        type="text"
                        name="peran"
                        id="peran"
                        placeholder="Contoh: Ketua Tim, Anggota, Peserta"
                        value="{{ old('peran', $kegiatan['peran']) }}"
                        class="{{ $errors->has('peran') ? 'ring-2 ring-red-400' : '' }}"
                    />
                    @error('peran')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tingkat -->
                <div class="space-y-1.5">
                    <label for="tingkat" class="block text-xs font-semibold text-gray-700">
                        Tingkat <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select
                            name="tingkat"
                            id="tingkat"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[10px] bg-white-form border-none text-gray-900 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer transition @error('tingkat') ring-2 ring-red-400 @enderror"
                        >
                            <option value="">-- Pilih Tingkat --</option>
                            @foreach(['Jurusan', 'Polines', 'Regional', 'Nasional', 'Internasional'] as $tk)
                                <option value="{{ $tk }}" {{ old('tingkat', $kegiatan['tingkat']) == $tk ? 'selected' : '' }}>{{ $tk }}</option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>
                    @error('tingkat')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Penyelenggara -->
                <div class="space-y-1.5">
                    <label for="penyelenggara" class="block text-xs font-semibold text-gray-700">
                        Penyelenggara <span class="text-red-500">*</span>
                    </label>
                    <x-input
                        type="text"
                        name="penyelenggara"
                        id="penyelenggara"
                        placeholder="Contoh: BEM Polines"
                        value="{{ old('penyelenggara', $kegiatan['penyelenggara']) }}"
                        class="{{ $errors->has('penyelenggara') ? 'ring-2 ring-red-400' : '' }}"
                    />
                    @error('penyelenggara')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Mulai -->
                <div class="space-y-1.5">
                    <label for="tanggal_mulai" class="block text-xs font-semibold text-gray-700">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <x-input
                        type="date"
                        name="tanggal_mulai"
                        id="tanggal_mulai"
                        value="{{ old('tanggal_mulai', $kegiatan['tanggal_mulai']) }}"
                        class="{{ $errors->has('tanggal_mulai') ? 'ring-2 ring-red-400' : '' }}"
                    />
                    @error('tanggal_mulai')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Selesai -->
                <div class="space-y-1.5">
                    <label for="tanggal_selesai" class="block text-xs font-semibold text-gray-700">
                        Tanggal Selesai <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <x-input
                        type="date"
                        name="tanggal_selesai"
                        id="tanggal_selesai"
                        value="{{ old('tanggal_selesai', $kegiatan['tanggal_selesai']) }}"
                        class="{{ $errors->has('tanggal_selesai') ? 'ring-2 ring-red-400' : '' }}"
                    />
                    @error('tanggal_selesai')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Validasi -->
                <div class="space-y-1.5">
                    <label for="status_validasi" class="block text-xs font-semibold text-gray-700">
                        Status Validasi <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select
                            name="status_validasi"
                            id="status_validasi"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[10px] bg-white-form border-none text-gray-900 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer transition @error('status_validasi') ring-2 ring-red-400 @enderror"
                        >
                            @foreach(['Pending', 'Disetujui', 'Ditolak'] as $sv)
                                <option value="{{ $sv }}" {{ old('status_validasi', $kegiatan['status_validasi']) == $sv ? 'selected' : '' }}>{{ $sv }}</option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>
                    @error('status_validasi')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a
                    href="{{ route('kegiatan-mahasiswa.index') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition cursor-pointer"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
