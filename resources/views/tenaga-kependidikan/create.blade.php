@extends('layouts.app')

@section('meta_title', 'Tambah Tenaga Kependidikan')
@section('title', 'Tambah Tenaga Kependidikan')

@section('content')
<div class="max-w-[540px] mx-auto py-6">
    <div class="bg-white rounded-[20px] shadow-2xl p-7 sm:px-11 sm:py-8 select-none">
        <!-- Title: Centered, Bold, Black -->
        <h2 class="text-center font-bold text-black text-2xl sm:text-[26px] tracking-tight mb-6 sm:mb-7">
            Tambahkan Tenaga Kependidikan
        </h2>

        <form method="POST" action="{{ route('tenaga-kependidikan.store') }}" class="space-y-4">
            @csrf

            <!-- Field 1: Nama -->
            <div>
                <label for="create_nama" class="block text-left text-[16px] font-bold text-black mb-2">
                    Nama
                </label>
                <input 
                    type="text" 
                    id="create_nama" 
                    name="nama" 
                    value="{{ old('nama') }}" 
                    placeholder="Nama Tenaga Kependidikan"
                    required
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black placeholder-[#737373] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition @error('nama') border-rose-500 ring-1 ring-rose-500 @enderror"
                >
                @error('nama')
                    <p class="text-rose-600 text-xs font-semibold mt-1.5 text-left">{{ $message }}</p>
                @enderror
            </div>

            <!-- Field 2: NIP -->
            <div>
                <label for="create_nip" class="block text-left text-[16px] font-bold text-black mb-2">
                    NIP
                </label>
                <input 
                    type="text" 
                    id="create_nip" 
                    name="nip" 
                    value="{{ old('nip') }}" 
                    placeholder="Nomor Induk Pegawai"
                    required
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black placeholder-[#737373] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition @error('nip') border-rose-500 ring-1 ring-rose-500 @enderror"
                >
                @error('nip')
                    <p class="text-rose-600 text-xs font-semibold mt-1.5 text-left">{{ $message }}</p>
                @enderror
            </div>

            <!-- Field 3: Jabatan -->
            <div>
                <label for="create_jabatan" class="block text-left text-[16px] font-bold text-black mb-2">
                    Jabatan
                </label>
                <input 
                    type="text" 
                    id="create_jabatan" 
                    name="jabatan" 
                    value="{{ old('jabatan') }}" 
                    placeholder="Jabatan Tenaga Kependidikan"
                    required
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black placeholder-[#737373] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition @error('jabatan') border-rose-500 ring-1 ring-rose-500 @enderror"
                >
                @error('jabatan')
                    <p class="text-rose-600 text-xs font-semibold mt-1.5 text-left">{{ $message }}</p>
                @enderror
            </div>

            <!-- Field 4: Kualifikasi -->
            <div>
                <label for="create_kualifikasi" class="block text-left text-[16px] font-bold text-black mb-2">
                    Kualifikasi
                </label>
                <select
                    id="create_kualifikasi" 
                    name="id_pendidikan"
                    required
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition cursor-pointer @error('id_pendidikan') border-rose-500 ring-1 ring-rose-500 @enderror"
                >
                    <option value="" disabled {{ old('id_pendidikan') ? '' : 'selected' }}>Isi Pendidikan Terakhir</option>
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

            <!-- Action Buttons: Batal & Simpan -->
            <div class="flex items-center justify-between gap-4 pt-4 sm:pt-6">
                <a 
                    href="{{ route('tenaga-kependidikan.index') }}" 
                    class="flex-1 h-[50px] bg-[#DEDEDE] hover:bg-[#d0d0d0] text-black font-bold text-[17px] rounded-[10px] transition cursor-pointer active:scale-[0.99] flex items-center justify-center select-none"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="flex-1 h-[50px] bg-[#075B91] hover:bg-[#064e7c] text-white font-bold text-[17px] rounded-[10px] shadow-xs transition cursor-pointer active:scale-[0.99] flex items-center justify-center select-none"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
