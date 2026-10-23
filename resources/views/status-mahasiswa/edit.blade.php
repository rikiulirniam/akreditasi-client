@extends('layouts.app')

@section('title', 'Edit Status Mahasiswa')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Edit Status Mahasiswa</h2>
            <p class="text-xs text-gray-500 mt-0.5">Perbarui status keaktifan mahasiswa</p>
        </div>
        <div>
            <a
                href="{{ route('status-mahasiswa.index') }}"
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
        <form method="POST" action="{{ route('status-mahasiswa.update', $statusData['id']) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                <!-- Mahasiswa / Enrollment -->
                <div class="space-y-1.5 md:col-span-2">
                    <label for="id_enrollment_mahasiswa_angkatan_kelas" class="block text-xs font-semibold text-gray-700">
                        Mahasiswa (Enrollment Kelas) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select
                            name="id_enrollment_mahasiswa_angkatan_kelas"
                            id="id_enrollment_mahasiswa_angkatan_kelas"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[10px] bg-white-form border-none text-gray-900 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer transition @error('id_enrollment_mahasiswa_angkatan_kelas') ring-2 ring-red-400 @enderror"
                        >
                            <option value="">-- Pilih Mahasiswa / Enrollment --</option>
                            @foreach($enrollmentList as $enr)
                                <option value="{{ $enr['id'] }}" {{ (string) old('id_enrollment_mahasiswa_angkatan_kelas', $statusData['id_enrollment_mahasiswa_angkatan_kelas']) === (string) $enr['id'] ? 'selected' : '' }}>
                                    {{ $enr['label'] ?? ($enr['nama_mahasiswa'] ?? 'Enrollment #'.$enr['id']) }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>
                    @error('id_enrollment_mahasiswa_angkatan_kelas')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Keaktifan -->
                <div class="space-y-1.5 md:col-span-2">
                    <label for="status" class="block text-xs font-semibold text-gray-700">
                        Status Mahasiswa <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select
                            name="status"
                            id="status"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[10px] bg-white-form border-none text-gray-900 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer transition @error('status') ring-2 ring-red-400 @enderror"
                        >
                            <option value="">-- Pilih Status --</option>
                            @foreach($statusOptions as $opt)
                                <option value="{{ $opt }}" {{ old('status', $statusData['status']) === $opt ? 'selected' : '' }}>
                                    {{ $opt }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>
                    @error('status')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a
                    href="{{ route('status-mahasiswa.index') }}"
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
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
