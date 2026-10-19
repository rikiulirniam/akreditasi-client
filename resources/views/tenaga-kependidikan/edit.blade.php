@extends('layouts.app')

@section('meta_title', 'Perbarui Tenaga Kependidikan')
@section('title', 'Perbarui Tenaga Kependidikan')

@php
    $tendikData = is_array($item ?? null) ? $item : (is_object($item ?? null) ? (array) $item : []);
    $tendikId = $tendikData['id'] ?? ($tendikData['id_tendik'] ?? request()->route('tenaga_kependidikan'));
    $tendikNama = $tendikData['nama'] ?? ($tendikData['nama_tendik'] ?? '');
    $tendikNip = $tendikData['nip'] ?? '';
    $tendikJabatan = $tendikData['jabatan'] ?? ($tendikData['jabatan_tendik'] ?? '');
    $tendikPendidikanId = $tendikData['id_pendidikan'] ?? '';
@endphp

@section('content')
<div class="max-w-[540px] mx-auto py-6">
    <div class="bg-white rounded-[20px] shadow-2xl p-7 sm:px-11 sm:py-8 select-none">
        <!-- Title: Centered, Bold, Black -->
        <h2 class="text-center font-bold text-black text-2xl sm:text-[26px] tracking-tight mb-6 sm:mb-7">
            Perbarui Tenaga Kependidikan
        </h2>

        <form method="POST" action="{{ route('tenaga-kependidikan.update', $tendikId) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field 1: Nama -->
            <div>
                <label for="edit_nama" class="block text-left text-[16px] font-bold text-black mb-2">
                    Nama
                </label>
                <input 
                    type="text" 
                    id="edit_nama" 
                    name="nama" 
                    value="{{ old('nama', $tendikNama) }}" 
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
                <label for="edit_nip" class="block text-left text-[16px] font-bold text-black mb-2">
                    NIP
                </label>
                <input 
                    type="text" 
                    id="edit_nip" 
                    name="nip" 
                    value="{{ old('nip', $tendikNip) }}" 
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
                <label for="edit_jabatan" class="block text-left text-[16px] font-bold text-black mb-2">
                    Jabatan
                </label>
                <input 
                    type="text" 
                    id="edit_jabatan" 
                    name="jabatan" 
                    value="{{ old('jabatan', $tendikJabatan) }}" 
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
                <label for="edit_kualifikasi" class="block text-left text-[16px] font-bold text-black mb-2">
                    Kualifikasi
                </label>
                <select
                    id="edit_kualifikasi" 
                    name="id_pendidikan"
                    required
                    class="w-full h-[50px] bg-[#DEDEDE] border border-transparent rounded-[10px] px-5 text-[16px] text-black focus:outline-none focus:ring-2 focus:ring-[#075B91]/30 focus:border-[#075B91] transition cursor-pointer @error('id_pendidikan') border-rose-500 ring-1 ring-rose-500 @enderror"
                >
                    <option value="" disabled>Isi Pendidikan Terakhir</option>
                    @foreach($pendidikan ?? [
                        ['id' => 1, 'jenjang' => 'S2'],
                        ['id' => 3, 'jenjang' => 'S3'],
                        ['id' => 4, 'jenjang' => 'D4/S1'],
                    ] as $level)
                        <option value="{{ $level['id'] ?? $level['id_pendidikan'] ?? '' }}" {{ (string) old('id_pendidikan', $tendikPendidikanId) === (string) ($level['id'] ?? $level['id_pendidikan'] ?? '') ? 'selected' : '' }}>
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
                    class="flex-1 h-[50px] bg-[#DEDEDE] hover:bg-[#CACACA] text-black font-bold text-[17px] rounded-[10px] transition cursor-pointer active:scale-[0.99] flex items-center justify-center select-none"
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
