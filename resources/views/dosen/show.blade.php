@extends('layouts.app')

@section('meta_title', 'Detail Dosen')

@section('title')
    <a href="{{ route('dosen.index') }}" class="hover:underline text-black">Dosen</a>
    <span class="text-black font-semibold mx-1.5">&gt;</span>
    <span>Detail dosen</span>
@endsection

@section('subtitle', is_string($dosen['nama_dosen'] ?? null) ? $dosen['nama_dosen'] : 'Dr. Budi Santoso, M. Kom.')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    
    <!-- LEFT COLUMN: Profil Dosen & Tabbed Penelitian / HaKI (Col 7) -->
    <div class="lg:col-span-7 flex flex-col gap-6">
        
        <!-- CARD 1: Profil Dosen -->
        <div class="bg-[#F2F4F7] rounded-[18px] p-6 sm:p-7 shadow-xs">
            <!-- Nama Dosen -->
            <h2 class="text-2xl sm:text-[28px] font-bold text-gray-900 tracking-tight leading-tight">
                {{ is_string($dosen['nama_dosen'] ?? null) ? $dosen['nama_dosen'] : 'Dr. Budi Santoso, M. Kom.' }}
            </h2>
            
            <!-- Jabatan Fungsional -->
            <p class="text-sm sm:text-base font-normal text-gray-500 mt-1 mb-5">
                {{ is_string($dosen['jabatan'] ?? null) ? $dosen['jabatan'] : 'Lektor Kepala' }}
            </p>

            <!-- 4 Rows with Dividing Lines -->
            <div class="border-t border-[#CBD5E1]">
                <!-- Row 1: Jurusan / Bidang Keahlian -->
                <div class="py-3.5 border-b border-[#CBD5E1] flex items-center gap-3.5 text-xs sm:text-sm font-semibold text-gray-900">
                    <!-- Lightning Bolt Icon -->
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-900 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                    </svg>
                    <span>{{ is_string($dosen['jurusan'] ?? null) ? $dosen['jurusan'] : 'Teknik Elektro' }}</span>
                </div>

                <!-- Row 2: Program Studi -->
                <div class="py-3.5 border-b border-[#CBD5E1] flex items-center gap-3.5 text-xs sm:text-sm font-semibold text-gray-900">
                    <!-- Computer Monitor Icon -->
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-900 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" />
                    </svg>
                    <span>{{ is_string($dosen['prodi'] ?? null) ? $dosen['prodi'] : 'D4 - Teknologi Rekayasa Komputer' }}</span>
                </div>

                <!-- Row 3: Email -->
                <div class="py-3.5 border-b border-[#CBD5E1] flex items-center gap-3.5 text-xs sm:text-sm font-semibold text-gray-900">
                    <!-- Envelope Icon -->
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-900 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <span>{{ is_string($dosen['email'] ?? null) ? $dosen['email'] : 'Budisantoso@gmail.com' }}</span>
                </div>

                <!-- Row 4: Status Dosen -->
                <div class="py-3.5 border-b border-[#CBD5E1] flex items-center gap-3.5 text-xs sm:text-sm font-semibold text-gray-900">
                    <!-- Document / Notepad Icon -->
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-900 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <span>{{ is_string($dosen['status_dosen'] ?? null) ? $dosen['status_dosen'] : 'Aktif' }}</span>
                </div>
            </div>

            <!-- Bottom Right Action: Lihat sertifikasi -->
            <div class="flex justify-end pt-5">
                @if(!empty($dosen['sertifikasi']) && is_string($dosen['sertifikasi']))
                    <a 
                        href="{{ $dosen['sertifikasi'] }}" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="inline-flex items-center gap-2 px-4 py-2 bg-[#01568D] hover:bg-[#012650] text-white text-xs sm:text-sm font-semibold rounded-[10px] shadow-xs transition duration-150 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Lihat sertifikasi</span>
                    </a>
                @else
                    <button 
                        type="button" 
                        onclick="alert('Tautan sertifikasi belum tersedia untuk dosen ini.')" 
                        class="inline-flex items-center gap-2 px-4 py-2 bg-[#01568D] hover:bg-[#012650] text-white text-xs sm:text-sm font-semibold rounded-[10px] shadow-xs transition duration-150 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Lihat sertifikasi</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- CARD 2: Tabbed Box - Penelitian & HaKI -->
        <div class="bg-[#F2F4F7] rounded-[18px] overflow-hidden shadow-xs">
            <!-- Navy Tab Bar Header -->
            <div class="bg-[#012650] px-4 sm:px-6 pt-3 flex items-center justify-around sm:justify-start sm:gap-12">
                <!-- Tab 1: Penelitian -->
                <button 
                    type="button" 
                    id="tabPenelitianBtn"
                    class="group flex items-center gap-2 pb-3 font-bold text-sm sm:text-base text-[#FFCC03] border-b-2 border-[#FFCC03] transition cursor-pointer select-none"
                >
                    <!-- Open Book SVG -->
                    <svg class="w-5 h-5 text-[#FFCC03]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span>Penelitian</span>
                </button>

                <!-- Tab 2: HaKI -->
                <button 
                    type="button" 
                    id="tabHakiBtn"
                    class="group flex items-center gap-2 pb-3 font-bold text-sm sm:text-base text-white hover:text-[#FFCC03] border-b-2 border-transparent transition cursor-pointer select-none"
                >
                    <!-- Scales of Justice SVG -->
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
                    </svg>
                    <span>HaKI</span>
                </button>
            </div>

            <!-- Tab Content 1: Penelitian List -->
            <div id="penelitianContent" class="p-5 sm:p-6 divide-y divide-[#CBD5E1]">
                @forelse($penelitianList as $item)
                    <div class="py-3.5 first:pt-0 last:pb-0">
                        <h4 class="text-xs sm:text-sm font-bold text-gray-900 leading-snug mb-2.5">
                            {{ is_scalar($item['judul'] ?? null) ? $item['judul'] : 'Judul Penelitian' }}
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
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                {{ is_scalar($item['bidang'] ?? null) ? $item['bidang'] : '-' }}
                            </span>
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                {{ is_scalar($item['instansi'] ?? null) ? $item['instansi'] : '-' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">Belum ada data penelitian.</p>
                @endforelse
            </div>

            <!-- Tab Content 2: HaKI List (Hidden by default) -->
            <div id="hakiContent" class="hidden p-5 sm:p-6 divide-y divide-[#CBD5E1]">
                @forelse($hakiList as $haki)
                    <div class="py-3.5 first:pt-0 last:pb-0">
                        <h4 class="text-xs sm:text-sm font-bold text-gray-900 leading-snug mb-2.5">
                            {{ is_scalar($haki['judul'] ?? null) ? $haki['judul'] : 'Judul HaKI' }}
                        </h4>
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                No: {{ is_scalar($haki['nomor'] ?? null) ? $haki['nomor'] : '-' }}
                            </span>
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                Tahun: {{ is_scalar($haki['tahun'] ?? null) ? $haki['tahun'] : '-' }}
                            </span>
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                Jenis: {{ is_scalar($haki['jenis'] ?? null) ? $haki['jenis'] : '-' }}
                            </span>
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                Status: {{ is_scalar($haki['status'] ?? null) ? $haki['status'] : '-' }}
                            </span>
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                {{ is_scalar($haki['instansi'] ?? null) ? $haki['instansi'] : '-' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">Belum ada data HaKI.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- RIGHT COLUMN: Riwayat Pendidikan & Pengabdian Kepada Masyarakat (Col 5) -->
    <div class="lg:col-span-5 flex flex-col gap-6">
        
        <!-- CARD 3: Riwayat Pendidikan -->
        <div class="bg-[#F2F4F7] rounded-[18px] overflow-hidden shadow-xs">
            <!-- Dark Navy Header -->
            <div class="bg-[#012650] py-3.5 px-4 text-center">
                <h3 class="text-white font-bold text-sm sm:text-base tracking-wide">
                    Riwayat Pendidikan
                </h3>
            </div>

            <!-- Content Items -->
            <div class="p-5 sm:p-6 divide-y divide-[#CBD5E1]">
                @forelse($riwayatPendidikan as $item)
                    <div class="py-3.5 first:pt-0 last:pb-0 flex items-start gap-3.5">
                        <!-- Yellow Circular Icon with Graduation Cap -->
                        <div class="w-10 h-10 rounded-full bg-[#FFCC03] flex items-center justify-center shrink-0 text-black shadow-xs">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm sm:text-base font-bold text-gray-900">{{ is_scalar($item['gelar'] ?? null) ? $item['gelar'] : '-' }}</span>
                                <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded-[4px]">
                                    {{ is_scalar($item['jenjang'] ?? null) ? $item['jenjang'] : '-' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 mt-0.5">{{ is_scalar($item['instansi'] ?? null) ? $item['instansi'] : '-' }}</p>
                            <p class="text-xs font-bold text-gray-900 mt-0.5">{{ is_scalar($item['jurusan'] ?? null) ? $item['jurusan'] : '-' }} - Lulus {{ is_scalar($item['tahun_lulus'] ?? null) ? $item['tahun_lulus'] : '-' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">Belum ada riwayat pendidikan.</p>
                @endforelse
            </div>
        </div>

        <!-- CARD 4: Pengabdian Kepada Masyarakat -->
        <div class="bg-[#F2F4F7] rounded-[18px] overflow-hidden shadow-xs">
            <!-- Dark Navy Header -->
            <div class="bg-[#012650] py-3.5 px-4 text-center">
                <h3 class="text-white font-bold text-sm sm:text-base tracking-wide">
                    Pengabdian Kepada Masyarakat
                </h3>
            </div>

            <!-- Content Items -->
            <div class="p-5 sm:p-6 divide-y divide-[#CBD5E1]">
                @forelse($pengabdianMasyarakat as $pkm)
                    <div class="py-3.5 first:pt-0 last:pb-0">
                        <h4 class="text-xs sm:text-[13px] font-bold text-gray-900 leading-snug uppercase mb-2">
                            {{ is_scalar($pkm['judul'] ?? null) ? $pkm['judul'] : 'Kegiatan Pengabdian Masyarakat' }}
                        </h4>
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px]">
                                {{ is_scalar($pkm['tahun'] ?? null) ? $pkm['tahun'] : '-' }}
                            </span>
                            <span class="bg-[#012650] text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-[4px] inline-flex items-center gap-1 max-w-full">
                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                <span class="truncate">{{ is_scalar($pkm['lokasi'] ?? null) ? $pkm['lokasi'] : '-' }}</span>
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">Belum ada data pengabdian kepada masyarakat.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>

<!-- Tab Switching Script (Penelitian vs HaKI) -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabPenelitianBtn = document.getElementById('tabPenelitianBtn');
        const tabHakiBtn = document.getElementById('tabHakiBtn');
        const penelitianContent = document.getElementById('penelitianContent');
        const hakiContent = document.getElementById('hakiContent');

        if (tabPenelitianBtn && tabHakiBtn && penelitianContent && hakiContent) {
            tabPenelitianBtn.addEventListener('click', function () {
                // Active: Penelitian
                tabPenelitianBtn.classList.add('text-[#FFCC03]', 'border-[#FFCC03]');
                tabPenelitianBtn.classList.remove('text-white', 'border-transparent');
                tabPenelitianBtn.querySelector('svg')?.classList.add('text-[#FFCC03]');

                // Inactive: HaKI
                tabHakiBtn.classList.remove('text-[#FFCC03]', 'border-[#FFCC03]');
                tabHakiBtn.classList.add('text-white', 'border-transparent');
                tabHakiBtn.querySelector('svg')?.classList.remove('text-[#FFCC03]');

                penelitianContent.classList.remove('hidden');
                hakiContent.classList.add('hidden');
            });

            tabHakiBtn.addEventListener('click', function () {
                // Active: HaKI
                tabHakiBtn.classList.add('text-[#FFCC03]', 'border-[#FFCC03]');
                tabHakiBtn.classList.remove('text-white', 'border-transparent');
                tabHakiBtn.querySelector('svg')?.classList.add('text-[#FFCC03]');

                // Inactive: Penelitian
                tabPenelitianBtn.classList.remove('text-[#FFCC03]', 'border-[#FFCC03]');
                tabPenelitianBtn.classList.add('text-white', 'border-transparent');
                tabPenelitianBtn.querySelector('svg')?.classList.remove('text-[#FFCC03]');

                hakiContent.classList.remove('hidden');
                penelitianContent.classList.add('hidden');
            });
        }
    });
</script>
@endsection
