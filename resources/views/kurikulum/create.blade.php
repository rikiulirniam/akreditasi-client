@extends('layouts.app')

@section('title', 'Tambah Kurikulum')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-lg font-bold text-gray-900 tracking-tight">Tambahkan Kurikulum</h2>
    </div>

    <div class="bg-white rounded-2xl border border-white-bone shadow-xs p-6 sm:p-8">
        <form action="{{ route('kurikulum.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                <!-- Row 1: Nama Kurikulum (Kiri) & SK Kurikulum (Kanan) -->
                <div>
                    <label for="namaKurikulumInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                        Nama Kurikulum
                    </label>
                    <input
                        type="text"
                        name="nama_kurikulum"
                        id="namaKurikulumInput"
                        placeholder="Isi nama"
                        value="{{ old('nama_kurikulum') }}"
                        required
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-400"
                    >
                    @error('nama_kurikulum')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="skKurikulumInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                        SK Kurikulum
                    </label>
                    <input
                        type="text"
                        name="sk_kurikulum"
                        id="skKurikulumInput"
                        placeholder="Isikan SK Kurikulum"
                        value="{{ old('sk_kurikulum') }}"
                        required
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-400"
                    >
                    @error('sk_kurikulum')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Row 2: Berlaku Hingga (Kiri) & Prodi (Kanan) -->
                <div>
                    <label for="berlakuSampaiInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                        Berlaku Hingga
                    </label>
                    <x-year
                        name="berlaku_sampai"
                        id="berlakuSampaiInput"
                        placeholder="yyyy"
                        min="1900"
                        max="2100"
                        step="1"
                        value="{{ old('berlaku_sampai') }}"
                        required
                    />
                    @error('berlaku_sampai')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="prodiSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                        Prodi
                    </label>
                    <div class="relative">
                        <select
                            name="id_prodi"
                            id="prodiSelect"
                            required
                            class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                        >
                            <option value="" disabled {{ old('id_prodi') ? '' : 'selected' }}>Pilih Prodi</option>
                            @foreach($prodiList as $prodi)
                                <option value="{{ $prodi['id'] }}" {{ old('id_prodi') == $prodi['id'] ? 'selected' : '' }}>
                                    {{ $prodi['nama_prodi'] ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                            <svg class="w-5 h-5 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>
                    @error('id_prodi')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Row 3: Deskripsi CPL (Kiri) & Kategori CPL + Tombol (Kanan) -->
                <div>
                    <label for="deskripsiCplTextarea" class="block text-xs font-bold text-gray-900 mb-1.5">
                        Deskripsi CPL
                    </label>
                    <textarea
                        name="deskripsi_cpl"
                        id="deskripsiCplTextarea"
                        rows="6"
                        placeholder="Isikan Deskripsi CPL"
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-400 resize-none"
                    >{{ old('deskripsi_cpl') }}</textarea>
                    @error('deskripsi_cpl')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col justify-between">
                    <div>
                        <label for="kategoriCplSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                            Kategori CPL
                        </label>
                        <div class="relative">
                            <select
                                name="kategori_cpl"
                                id="kategoriCplSelect"
                                class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                            >
                                <option value="" disabled {{ old('kategori_cpl') ? '' : 'selected' }}>Pilih Kategori CPL</option>
                                @foreach($kategoriCplList as $kat)
                                    <option value="{{ $kat }}" {{ old('kategori_cpl') == $kat ? 'selected' : '' }}>
                                        {{ $kat }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                                <svg class="w-5 h-5 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </span>
                        </div>
                        @error('kategori_cpl')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tombol Aksi di Kanan Bawah -->
                    <div class="flex items-center justify-end gap-3 pt-6">
                        <a
                            href="{{ route('kurikulum.index') }}"
                            class="px-8 py-2.5 rounded-[10px] bg-[#E5E7EB] hover:bg-gray-300 text-gray-900 font-bold text-sm transition text-center"
                        >
                            Batal
                        </a>
                        <button
                            type="submit"
                            class="px-8 py-2.5 rounded-[10px] bg-primary hover:bg-additional text-white font-bold text-sm shadow-xs transition cursor-pointer"
                        >
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
