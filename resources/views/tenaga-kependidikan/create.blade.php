@extends('layouts.app')

@section('title', 'Tambah Tenaga Kependidikan')
@section('subtitle', 'Formulir penambahan data tenaga kependidikan baru')

@section('content')
<div class="max-w-2xl mx-auto py-4">
    <!-- Back Button -->
    <div class="mb-6">
        <a 
            href="{{ route('tenaga-kependidikan.index') }}" 
            class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:text-additional transition"
        >
            <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-[#CACACA] shadow-sm p-6 sm:p-8">
        <h2 class="text-xl font-extrabold text-primary mb-6 pb-3 border-b border-gray-100">
            Data Tenaga Kependidikan
        </h2>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                <p class="font-bold mb-1">Terdapat kesalahan pengisian:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('tenaga-kependidikan.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="nama" class="block text-xs font-bold text-gray-700 mb-1.5">
                    Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nama" 
                    name="nama" 
                    value="{{ old('nama') }}" 
                    placeholder="Contoh: Bambang Triyono, S.E."
                    required
                    class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-xl px-4 py-2.5 text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
            </div>

            <div>
                <label for="nip" class="block text-xs font-bold text-gray-700 mb-1.5">
                    NIP <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nip" 
                    name="nip" 
                    value="{{ old('nip') }}" 
                    placeholder="Contoh: 197805122003121001"
                    required
                    class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-xl px-4 py-2.5 text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20 font-mono"
                >
            </div>

            <div>
                <label for="jabatan" class="block text-xs font-bold text-gray-700 mb-1.5">
                    Jabatan <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="jabatan" 
                    name="jabatan" 
                    value="{{ old('jabatan') }}" 
                    placeholder="Contoh: Kepala Tata Usaha"
                    required
                    class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-xl px-4 py-2.5 text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
            </div>

            <div>
                <label for="id_pendidikan" class="block text-xs font-bold text-gray-700 mb-1.5">
                    Kualifikasi Pendidikan <span class="text-rose-500">*</span>
                </label>
                <select
                    id="id_pendidikan" 
                    name="id_pendidikan" 
                    required
                    class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-xl px-4 py-2.5 text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
                    <option value="">Pilih jenjang pendidikan</option>
                    @foreach($pendidikan ?? [
                        ['id' => 4, 'jenjang' => 'D4/S1'],
                        ['id' => 1, 'jenjang' => 'S2'],
                        ['id' => 3, 'jenjang' => 'S3'],
                    ] as $level)
                        <option value="{{ $level['id'] ?? $level['id_pendidikan'] ?? '' }}" {{ (string) old('id_pendidikan') === (string) ($level['id'] ?? $level['id_pendidikan'] ?? '') ? 'selected' : '' }}>
                            {{ $level['jenjang'] ?? '-' }}
                        </option>
                    @endforeach
                </select>
                @error('id_pendidikan')
                    <p class="text-rose-600 text-xs font-semibold mt-1.5 text-left">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a 
                    href="{{ route('tenaga-kependidikan.index') }}" 
                    class="px-5 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-2.5 bg-primary hover:bg-additional text-white text-sm font-bold rounded-xl shadow-xs transition active:scale-[0.98] cursor-pointer"
                >
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
