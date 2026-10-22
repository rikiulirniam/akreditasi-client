@extends('layouts.app')

@section('meta_title', 'Detail Dosen')

@section('title')
    <a href="{{ route('dosen.index') }}" class="hover:underline text-black">Dosen</a>
    <span class="text-black font-semibold mx-1.5">&gt;</span>
    <span>Detail dosen</span>
@endsection

@section('subtitle', is_string($dosen['nama_dosen'] ?? null) ? $dosen['nama_dosen'] : '-')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    {{-- LEFT COLUMN: Profil + Tabbed Penelitian / Pengabdian (Col 7) --}}
    <div class="lg:col-span-7 flex flex-col gap-6">

        {{-- CARD 1: Profil Dosen --}}
        <div class="bg-[#F2F4F7] rounded-[18px] p-6 sm:p-7 shadow-xs">
            <h2 class="text-2xl sm:text-[28px] font-bold text-gray-900 tracking-tight leading-tight">
                {{ is_string($dosen['nama_dosen'] ?? null) ? $dosen['nama_dosen'] : '-' }}
            </h2>
            <p class="text-sm sm:text-base font-normal text-gray-500 mt-1 mb-5">
                {{ is_string($dosen['jabatan'] ?? null) ? $dosen['jabatan'] : '-' }}
            </p>

            <div class="border-t border-[#CBD5E1]">
                {{-- Bidang Keahlian --}}
                <div class="py-3.5 border-b border-[#CBD5E1] flex items-center gap-3.5 text-xs sm:text-sm font-semibold text-gray-900">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                    </svg>
                    <span>{{ is_string($dosen['jurusan'] ?? null) ? $dosen['jurusan'] : '-' }}</span>
                </div>

                {{-- Program Studi --}}
                <div class="py-3.5 border-b border-[#CBD5E1] flex items-center gap-3.5 text-xs sm:text-sm font-semibold text-gray-900">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" />
                    </svg>
                    <span>{{ is_string($dosen['prodi'] ?? null) ? $dosen['prodi'] : '-' }}</span>
                </div>

                {{-- Email --}}
                <div class="py-3.5 border-b border-[#CBD5E1] flex items-center gap-3.5 text-xs sm:text-sm font-semibold text-gray-900">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <span>{{ is_string($dosen['email'] ?? null) ? $dosen['email'] : '-' }}</span>
                </div>

                {{-- Status --}}
                <div class="py-3.5 border-b border-[#CBD5E1] flex items-center gap-3.5 text-xs sm:text-sm font-semibold text-gray-900">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <span>{{ is_string($dosen['status_dosen'] ?? null) ? $dosen['status_dosen'] : '-' }}</span>
                </div>
            </div>

            <div class="flex justify-end pt-5">
                @if(!empty($dosen['sertifikasi']) && is_string($dosen['sertifikasi']))
                    <a href="{{ $dosen['sertifikasi'] }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-[#01568D] hover:bg-[#012650] text-white text-xs sm:text-sm font-semibold rounded-[10px] shadow-xs transition duration-150">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Lihat sertifikasi</span>
                    </a>
                @else
                    <button type="button" onclick="alert('Sertifikasi belum tersedia untuk dosen ini.')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-[#01568D] hover:bg-[#012650] text-white text-xs sm:text-sm font-semibold rounded-[10px] shadow-xs transition duration-150 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Lihat sertifikasi</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- CARD 2: Tabbed — Penelitian & Pengabdian --}}
        <div class="bg-[#F2F4F7] rounded-[18px] overflow-hidden shadow-xs">
            {{-- Tab Header --}}
            <div class="bg-[#012650] px-4 sm:px-6 pt-3 flex items-center justify-around sm:justify-start sm:gap-12">
                <button type="button" id="tabPenelitianBtn"
                    class="flex items-center gap-2 pb-3 font-bold text-sm sm:text-base text-[#FFCC03] border-b-2 border-[#FFCC03] transition cursor-pointer select-none">
                    <svg class="w-5 h-5 text-[#FFCC03]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span>Penelitian</span>
                </button>

                <button type="button" id="tabPengabdianBtn"
                    class="flex items-center gap-2 pb-3 font-bold text-sm sm:text-base text-white hover:text-[#FFCC03] border-b-2 border-transparent transition cursor-pointer select-none">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <span>Pengabdian Kepada Masyarakat</span>
                </button>
            </div>

            {{-- Tab Content: Penelitian --}}
            <div id="penelitianContent" class="p-5 sm:p-6 divide-y divide-[#CBD5E1]">
                @forelse($penelitianList as $item)
                    <div class="py-3.5 first:pt-0 last:pb-0">
                        <h4 class="text-xs sm:text-sm font-bold text-gray-900 leading-snug mb-2.5">
                            {{ is_scalar($item['judul'] ?? null) ? $item['judul'] : '-' }}
                        </h4>
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                Usulan: {{ is_scalar($item['tahun_usulan'] ?? null) ? $item['tahun_usulan'] : '-' }}
                            </span>
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                Kegiatan: {{ is_scalar($item['tahun_kegiatan'] ?? null) ? $item['tahun_kegiatan'] : '-' }}
                            </span>
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                Pelaksanaan: {{ is_scalar($item['tahun_pelaksanaan'] ?? null) ? $item['tahun_pelaksanaan'] : '-' }}
                            </span>
                            @if(!empty($item['bidang']) && $item['bidang'] !== '-')
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                {{ $item['bidang'] }}
                            </span>
                            @endif
                            @if(!empty($item['instansi']) && $item['instansi'] !== '-')
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                {{ $item['instansi'] }}
                            </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">Belum ada data penelitian.</p>
                @endforelse
            </div>

            {{-- Tab Content: Pengabdian (hidden by default) --}}
            <div id="pengabdianContent" class="hidden p-5 sm:p-6 divide-y divide-[#CBD5E1]">
                @forelse($pengabdianMasyarakat as $pkm)
                    <div class="py-3.5 first:pt-0 last:pb-0">
                        <h4 class="text-xs sm:text-[13px] font-bold text-gray-900 leading-snug uppercase mb-2">
                            {{ is_scalar($pkm['judul'] ?? null) ? $pkm['judul'] : '-' }}
                        </h4>
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                            @if(!empty($pkm['tahun']) && $pkm['tahun'] !== '-')
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                {{ $pkm['tahun'] }}
                            </span>
                            @endif
                            @if(!empty($pkm['lokasi']) && $pkm['lokasi'] !== '-')
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px] inline-flex items-center gap-1 max-w-full">
                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                <span class="truncate">{{ $pkm['lokasi'] }}</span>
                            </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">Belum ada data pengabdian kepada masyarakat.</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- RIGHT COLUMN: Riwayat Pendidikan & Luaran (Col 5) --}}
    <div class="lg:col-span-5 flex flex-col gap-6">

        {{-- CARD 3: Riwayat Pendidikan --}}
        <div class="bg-[#F2F4F7] rounded-[18px] overflow-hidden shadow-xs">
            <div class="bg-[#012650] py-3.5 px-4 text-center">
                <h3 class="text-white font-bold text-sm sm:text-base tracking-wide">Riwayat Pendidikan</h3>
            </div>

            <div class="p-5 sm:p-6 divide-y divide-[#CBD5E1]">
                @forelse($riwayatPendidikan as $item)
                    <div class="py-3.5 first:pt-0 last:pb-0 flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-full bg-[#FFCC03] flex items-center justify-center shrink-0 text-black shadow-xs">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm sm:text-base font-bold text-gray-900">
                                    {{ is_scalar($item['gelar'] ?? null) ? $item['gelar'] : '-' }}
                                </span>
                                @if(!empty($item['jenjang']) && $item['jenjang'] !== '-')
                                <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded-[4px]">
                                    {{ $item['jenjang'] }}
                                </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-600 mt-0.5">
                                {{ is_scalar($item['instansi'] ?? null) ? $item['instansi'] : '-' }}
                            </p>
                            <p class="text-xs font-bold text-gray-900 mt-0.5">
                                {{ is_scalar($item['jurusan'] ?? null) ? $item['jurusan'] : '-' }}
                                @if(!empty($item['tahun_lulus']) && $item['tahun_lulus'] !== '-')
                                    - Lulus {{ $item['tahun_lulus'] }}
                                @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">Belum ada riwayat pendidikan.</p>
                @endforelse
            </div>

            {{-- Tombol Tambah Pendidikan (footer full-width flush) --}}
            <button type="button"
                class="w-full bg-[#012650] hover:bg-[#01568D] py-3 px-4 text-white text-center flex items-center justify-center gap-2 transition duration-150 cursor-pointer">
                <svg class="w-5 h-5 text-white shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm sm:text-base font-normal tracking-wide">Tambah Pendidikan</span>
            </button>
        </div>

        {{-- CARD 4: Luaran (HKI + Luaran + Buku) --}}
        <div class="bg-[#F2F4F7] rounded-[18px] overflow-hidden shadow-xs">
            <div class="bg-[#012650] py-3.5 px-4 text-center">
                <h3 class="text-white font-bold text-sm sm:text-base tracking-wide">Luaran</h3>
                <p class="text-[10px] sm:text-[11px] font-medium text-white/70 mt-0.5">HaKI, Luaran Ilmiah, dan Buku</p>
            </div>

            <div class="p-5 sm:p-6 divide-y divide-[#CBD5E1]">
                @forelse($luaranList as $item)
                    <div class="py-3.5 first:pt-0 last:pb-0">
                        <h4 class="text-xs sm:text-sm font-bold text-gray-900 leading-snug mb-2.5">
                            {{ is_scalar($item['judul'] ?? null) ? $item['judul'] : '-' }}
                        </h4>
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                            <span class="bg-[#FFCC03] text-[#012650] text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded-[4px]">
                                {{ is_scalar($item['jenis'] ?? null) ? $item['jenis'] : '-' }}
                            </span>
                            @if(!empty($item['nomor']) && $item['nomor'] !== '-')
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                {{ $item['nomor'] }}
                            </span>
                            @endif
                            @if(!empty($item['tahun']) && $item['tahun'] !== '-')
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                {{ $item['tahun'] }}
                            </span>
                            @endif
                            @if(!empty($item['status']) && $item['status'] !== '-')
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                {{ $item['status'] }}
                            </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">Belum ada data luaran.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnPenelitian = document.getElementById('tabPenelitianBtn');
        const btnPengabdian = document.getElementById('tabPengabdianBtn');
        const contentPenelitian = document.getElementById('penelitianContent');
        const contentPengabdian = document.getElementById('pengabdianContent');

        function activate(activeBtn, inactiveBtn, showEl, hideEl) {
            activeBtn.classList.add('text-[#FFCC03]', 'border-[#FFCC03]');
            activeBtn.classList.remove('text-white', 'border-transparent');
            activeBtn.querySelector('svg')?.classList.add('text-[#FFCC03]');

            inactiveBtn.classList.remove('text-[#FFCC03]', 'border-[#FFCC03]');
            inactiveBtn.classList.add('text-white', 'border-transparent');
            inactiveBtn.querySelector('svg')?.classList.remove('text-[#FFCC03]');

            showEl.classList.remove('hidden');
            hideEl.classList.add('hidden');
        }

        btnPenelitian?.addEventListener('click', () => activate(btnPenelitian, btnPengabdian, contentPenelitian, contentPengabdian));
        btnPengabdian?.addEventListener('click', () => activate(btnPengabdian, btnPenelitian, contentPengabdian, contentPenelitian));
    });
</script>
@endsection
