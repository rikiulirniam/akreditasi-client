@extends('layouts.app')

@section('title')
    <span class="inline-flex items-center gap-2.5 sm:gap-3.5 flex-wrap">
        <a href="{{ route('tenaga-pendidikan.index') }}" class="hover:text-additional transition text-black">
            Tenaga Kependidikan
        </a>
        <svg class="w-5 h-5 sm:w-7 sm:h-7 stroke-[3] text-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <span>Detail</span>
    </span>
@endsection

@section('subtitle', $tenagaKependidikan->nama)

@section('content')
<div class="space-y-7 pb-10">
    
    <!-- Top Row: Left Panel (Identitas) & Right Panel (Kualifikasi) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">
        
        <!-- ========================================== -->
        <!-- PANEL KIRI: IDENTITAS TENAGA PENDIDIKAN   -->
        <!-- ========================================== -->
        <div class="lg:col-span-7 bg-[#EDF3F7] rounded-[14px] overflow-hidden shadow-xs border border-[#E0EBF2] flex flex-col justify-between min-h-[408px]">
            
            <!-- Identitas Utama: Nama & Jabatan -->
            <div class="px-8 pt-8 pb-4">
                <h2 class="text-2xl sm:text-3xl lg:text-[34px] font-extrabold text-black leading-tight tracking-tight">
                    {{ $tenagaKependidikan->nama }}
                </h2>
                <p class="text-lg sm:text-xl lg:text-[21px] font-semibold text-gray-700 mt-1">
                    {{ $tenagaKependidikan->jabatan ?? 'Tenaga Pendidikan' }}
                </p>
            </div>

            <!-- Baris Informasi dengan Ikon -->
            <div class="divide-y divide-[#DCE5EB] mt-auto">
                <!-- Baris 1: Bidang Keahlian (Ikon Petir) -->
                <div class="h-[46px] px-8 flex items-center gap-4 text-black text-sm sm:text-[15.5px] font-medium">
                    <span class="w-6 flex items-center justify-center shrink-0 text-black">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.272-.71l1.992-7.302H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" />
                        </svg>
                    </span>
                    <span class="truncate">
                        {{ $tenagaKependidikan->bidang_keahlian ?: 'Belum diisi' }}
                    </span>
                </div>

                <!-- Baris 2: Pendidikan / Program Studi (Ikon Monitor) -->
                <div class="h-[46px] px-8 flex items-center gap-4 text-black text-sm sm:text-[15.5px] font-medium">
                    <span class="w-6 flex items-center justify-center shrink-0 text-black">
                        <svg class="w-4 h-4 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3" />
                        </svg>
                    </span>
                    <span class="truncate">
                        {{ $tenagaKependidikan->program_studi ?: ($tenagaKependidikan->kualifikasi ?: 'Belum diisi') }}
                    </span>
                </div>

                <!-- Baris 3: Email (Ikon Amplop) -->
                <div class="h-[46px] px-8 flex items-center gap-4 text-black text-sm sm:text-[15.5px] font-medium">
                    <span class="w-6 flex items-center justify-center shrink-0 text-black">
                        <svg class="w-4 h-4 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </span>
                    <span class="truncate">
                        {{ $tenagaKependidikan->email ?: 'Belum diisi' }}
                    </span>
                </div>

                <!-- Baris 4: Status (Ikon Dokumen) -->
                <div class="h-[46px] px-8 flex items-center gap-4 text-black text-sm sm:text-[15.5px] font-medium">
                    <span class="w-6 flex items-center justify-center shrink-0 text-black">
                        <svg class="w-4 h-4 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                    </span>
                    <span class="truncate">
                        {{ $tenagaKependidikan->status ?: 'Aktif' }}
                    </span>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- PANEL KANAN: KUALIFIKASI                   -->
        <!-- ========================================== -->
        <div class="lg:col-span-5 bg-[#EDF3F7] rounded-[14px] overflow-hidden shadow-xs border border-[#E0EBF2]">
            
            <!-- Header Panel: Navy Gelap #062B53 -->
            <div class="bg-[#062B53] h-16 flex items-center justify-center px-6">
                <h3 class="text-white font-bold text-xl sm:text-[21px] tracking-wide text-center">
                    Kualifikasi
                </h3>
            </div>

            <!-- Body Daftar Kualifikasi -->
            <div class="divide-y divide-[#DCE5EB] min-h-[344px] flex flex-col justify-start">
                @forelse($tenagaKependidikan->kualifikasis as $kualifikasi)
                    <div class="p-4 sm:px-6 sm:py-4 flex items-start gap-4">
                        <!-- Yellow Circle Icon with Graduation Cap -->
                        <div class="w-9 h-9 rounded-full bg-secondary flex items-center justify-center shrink-0 text-[#062B53] shadow-2xs mt-0.5">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72l5 2.73 5-2.73v3.72z"/>
                            </svg>
                        </div>

                        <!-- Kualifikasi Detail -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-black text-lg sm:text-[21px] leading-tight">
                                    {{ $kualifikasi->gelar ?: 'Gelar' }}
                                </span>
                                @if($kualifikasi->jenjang)
                                    <span class="px-2 py-0.5 rounded-[4px] bg-[#062B53] text-secondary text-[11px] font-extrabold uppercase tracking-wider">
                                        {{ $kualifikasi->jenjang }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs sm:text-[13.5px] font-semibold text-black mt-0.5 truncate">
                                {{ $kualifikasi->institusi ?: '-' }}
                            </p>
                            <p class="text-xs sm:text-[12.5px] font-medium text-gray-700 truncate">
                                {{ $kualifikasi->program_studi ?: '-' }} {{ $kualifikasi->tahun_lulus ? '- Lulus ' . $kualifikasi->tahun_lulus : '' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <!-- Empty State Kualifikasi -->
                    <div class="p-10 flex-1 flex flex-col items-center justify-center text-center text-gray-500">
                        <svg class="w-12 h-12 text-gray-300 stroke-1 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                        <p class="font-bold text-sm text-gray-700">Belum ada data kualifikasi</p>
                        <p class="text-xs text-gray-400 mt-0.5">Riwayat kualifikasi akademik belum ditambahkan.</p>
                    </div>
                @endforelse
            </div>

        </div>

    </div>

    <!-- ========================================== -->
    <!-- PANEL BAWAH: SERTIFIKASI                   -->
    <!-- ========================================== -->
    <div class="bg-[#EDF3F7] rounded-[14px] overflow-hidden shadow-xs border border-[#E0EBF2]">
        
        <!-- Header Panel: Navy Gelap #062B53 dengan Tombol Tambah Sertifikasi -->
        <div class="bg-[#062B53] h-16 flex items-center justify-between px-6 sm:px-8">
            <h3 class="text-white font-bold text-xl sm:text-[21px] tracking-wide">
                Sertifikasi
            </h3>

            <!-- Tombol Tambah Sertifikasi -->
            <button 
                type="button" 
                id="openSertifikasiModalBtn"
                class="inline-flex items-center gap-2 h-[35px] px-3.5 bg-[#00649B] hover:bg-[#00517d] text-white text-[13px] font-bold rounded-[9px] shadow-xs transition active:scale-[0.98] cursor-pointer"
            >
                <span class="w-4 h-4 rounded-full border-[1.5px] border-white flex items-center justify-center text-white shrink-0">
                    <svg class="w-3 h-3 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </span>
                <span>Tambah Sertifikasi</span>
            </button>
        </div>

        <!-- Body Sertifikasi (Min-height ~330px sesuai referensi desktop) -->
        <div class="p-6 sm:p-8 min-h-[330px] flex flex-col justify-start">
            @if($tenagaKependidikan->sertifikasis && $tenagaKependidikan->sertifikasis->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($tenagaKependidikan->sertifikasis as $sertifikasi)
                        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-2xs flex flex-col justify-between hover:shadow-md transition">
                            <div>
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.004 0H9.496m5.004 0a3 3 0 0 0 3-3V6.75a3 3 0 0 0-3-3H9.496a3 3 0 0 0-3 3v5.625a3 3 0 0 0 3 3Z" />
                                        </svg>
                                    </span>
                                    <form method="POST" action="{{ route('tenaga-kependidikan.sertifikasi.destroy', [$tenagaKependidikan, $sertifikasi]) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sertifikasi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-rose-600 transition p-1 cursor-pointer" title="Hapus Sertifikasi">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                <h4 class="font-bold text-black text-base line-clamp-2 leading-snug">
                                    {{ $sertifikasi->nama_sertifikasi }}
                                </h4>
                                <p class="text-xs font-semibold text-primary mt-1">
                                    {{ $sertifikasi->penerbit }}
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500 font-medium">
                                <span>No: {{ $sertifikasi->nomor_sertifikasi ?: '-' }}</span>
                                <span>Tahun: {{ $sertifikasi->tahun ?: '-' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State Sertifikasi (Sesuai referensi visual screenshot) -->
                <div class="flex-1 flex flex-col items-center justify-center text-center text-gray-400 py-16">
                    <svg class="w-16 h-16 text-gray-300 stroke-1 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <p class="font-bold text-base text-gray-600">Belum ada data sertifikasi</p>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm">
                        Klik tombol "Tambah Sertifikasi" di atas untuk menambahkan riwayat sertifikasi keahlian.
                    </p>
                </div>
            @endif
        </div>

    </div>

</div>

<!-- ========================================== -->
<!-- MODAL: TAMBAH SERTIFIKASI                  -->
<!-- ========================================== -->
<div 
    id="sertifikasiModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-200"
    style="background-color: rgba(0, 0, 0, 0.45); backdrop-filter: blur(1px);"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalSertifikasiTitle"
>
    <div 
        id="sertifikasiModalCard" 
        class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6 sm:p-8 transform scale-95 transition-all duration-200 relative select-none"
    >
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <h3 id="modalSertifikasiTitle" class="text-xl font-bold text-primary">
                Tambah Sertifikasi
            </h3>
            <button 
                type="button" 
                class="close-sertifikasi-modal p-1.5 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition cursor-pointer"
                aria-label="Tutup"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form Tambah Sertifikasi -->
        <form method="POST" action="{{ route('tenaga-kependidikan.sertifikasi.store', $tenagaKependidikan) }}" class="mt-5 space-y-4">
            @csrf

            <!-- Field: Nama Sertifikasi -->
            <div>
                <label for="nama_sertifikasi" class="block text-xs font-bold text-gray-700 mb-1">
                    Nama Sertifikasi <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nama_sertifikasi" 
                    name="nama_sertifikasi" 
                    value="{{ old('nama_sertifikasi') }}"
                    placeholder="Contoh: Certified Associate in Project Management"
                    required
                    class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-xl px-4 py-2.5 text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20 @error('nama_sertifikasi') border-rose-500 @enderror"
                >
                @error('nama_sertifikasi')
                    <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Field: Lembaga Penerbit -->
            <div>
                <label for="penerbit" class="block text-xs font-bold text-gray-700 mb-1">
                    Lembaga Penerbit <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="penerbit" 
                    name="penerbit" 
                    value="{{ old('penerbit') }}"
                    placeholder="Contoh: BNSP / PMI / Cisco"
                    required
                    class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-xl px-4 py-2.5 text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20 @error('penerbit') border-rose-500 @enderror"
                >
                @error('penerbit')
                    <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Field Row: Nomor & Tahun -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nomor_sertifikasi" class="block text-xs font-bold text-gray-700 mb-1">
                        Nomor Sertifikasi
                    </label>
                    <input 
                        type="text" 
                        id="nomor_sertifikasi" 
                        name="nomor_sertifikasi" 
                        value="{{ old('nomor_sertifikasi') }}"
                        placeholder="Contoh: SERT-2024-001"
                        class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-xl px-4 py-2.5 text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20 font-mono"
                    >
                </div>
                <div>
                    <label for="tahun" class="block text-xs font-bold text-gray-700 mb-1">
                        Tahun Terbit / Perolehan
                    </label>
                    <input 
                        type="text" 
                        id="tahun" 
                        name="tahun" 
                        value="{{ old('tahun') }}"
                        placeholder="Contoh: 2024"
                        class="w-full bg-[#D9D9D9] border border-[#CACACA] rounded-xl px-4 py-2.5 text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button 
                    type="button" 
                    class="close-sertifikasi-modal px-5 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-6 py-2.5 bg-primary hover:bg-additional text-white text-sm font-bold rounded-xl shadow-xs transition active:scale-[0.98] cursor-pointer"
                >
                    Simpan Sertifikasi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- SCRIPT: MODAL SERTIFIKASI INTERACTION      -->
<!-- ========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sertifikasiModal = document.getElementById('sertifikasiModal');
    const sertifikasiCard = document.getElementById('sertifikasiModalCard');
    const openBtn = document.getElementById('openSertifikasiModalBtn');
    const closeBtns = document.querySelectorAll('.close-sertifikasi-modal');

    function toggleSertifikasiModal(show) {
        if (!sertifikasiModal || !sertifikasiCard) return;
        if (show) {
            sertifikasiModal.classList.remove('opacity-0', 'pointer-events-none');
            sertifikasiModal.classList.add('opacity-100');
            sertifikasiCard.classList.remove('scale-95');
            sertifikasiCard.classList.add('scale-100');
            document.body.classList.add('overflow-hidden');
            const firstInput = document.getElementById('nama_sertifikasi');
            if (firstInput) setTimeout(() => firstInput.focus(), 80);
        } else {
            sertifikasiModal.classList.remove('opacity-100');
            sertifikasiModal.classList.add('opacity-0', 'pointer-events-none');
            sertifikasiCard.classList.remove('scale-100');
            sertifikasiCard.classList.add('scale-95');
            document.body.classList.remove('overflow-hidden');
        }
    }

    if (openBtn) {
        openBtn.addEventListener('click', () => toggleSertifikasiModal(true));
    }

    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => toggleSertifikasiModal(false));
    });

    if (sertifikasiModal) {
        sertifikasiModal.addEventListener('click', function (e) {
            if (e.target === sertifikasiModal) toggleSertifikasiModal(false);
        });
    }

    if (sertifikasiCard) {
        sertifikasiCard.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') toggleSertifikasiModal(false);
    });

    @if($errors->has('nama_sertifikasi') || $errors->has('penerbit'))
        toggleSertifikasiModal(true);
    @endif
});
</script>
@endsection
