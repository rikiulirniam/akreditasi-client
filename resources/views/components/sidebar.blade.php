{{-- resources/views/components/sidebar.blade.php --}}

@php
    $menuItems = [
        [
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'url' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
        ],
    ];
@endphp

{{-- Backdrop untuk sidebar mobile --}}
<div
    id="sidebarBackdrop"
    class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none
           transition-opacity duration-300 lg:hidden"
></div>

<aside
    id="sidebar"
    class="fixed inset-y-0 left-0 z-50 flex h-screen w-64
           -translate-x-full flex-col overflow-y-auto
           bg-[#06284F] text-white transition-all duration-300
           lg:relative lg:inset-auto lg:translate-x-0
           shrink-0"
>
    {{-- Logo dan identitas sistem --}}
    <div class="relative shrink-0 px-4 pt-4 pb-5">
        <div class="flex items-center gap-3 px-1 py-3">
            <svg
                class="h-10 w-10 shrink-0 text-white"
                viewBox="0 0 48 48"
                fill="none"
                aria-hidden="true"
            >
                <path d="M4 19L24 9L44 19L24 29L4 19Z"
                    fill="currentColor"/>
                <path d="M10 23V36M38 23V36"
                    stroke="currentColor"
                    stroke-width="3"
                    stroke-linecap="round"/>
                <path d="M18 30V39H30V30"
                    stroke="currentColor"
                    stroke-width="3"
                    stroke-linejoin="round"/>
            </svg>

            <div class="sidebar-text min-w-0">
                <p class="text-sm font-extrabold uppercase leading-tight">
                    Sistem Akreditasi
                </p>
                <p class="text-sm font-extrabold uppercase leading-tight">
                    Program Studi
                </p>
            </div>
        </div>

        {{-- Garis aksen kuning --}}
        <div class="absolute bottom-0 left-0 h-2 w-full
                    rounded-t-xl bg-[#FFD400]"></div>
    </div>

    <div class="relative -mt-5 flex-1 flex flex-col bg-secondary rounded-t-[32px] pt-[5px] overflow-hidden">
        <div class="flex-1 bg-primary rounded-t-[27px] px-3.5 py-5 overflow-y-auto overflow-x-hidden">

            <nav class="space-y-3" aria-label="Menu Utama">

                <!-- Dashboard -->
                <div>
                    <a href="{{ route('dashboard') }}"
                        class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 group {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                        title="Dashboard">
                        <svg class="w-6 h-6 shrink-0 fill-current text-white" viewBox="0 0 24 24">
                            <path d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z" />
                        </svg>
                        <span class="sidebar-text truncate">Dashboard</span>
                    </a>
                </div>

                <!-- Pengguna -->
                @php
                    $isUserMgmtActive = request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*');
                @endphp
                <div>
                    <button type="button"
                        class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-medium {{ $isUserMgmtActive ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                        data-target="submenu-akun" title="Pengguna">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <svg class="w-6 h-6 shrink-0 fill-current text-white/90" viewBox="0 0 24 24">
                                <path
                                    d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                            </svg>
                            <span class="sidebar-text truncate">Pengguna</span>
                        </div>
                        <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200 {{ $isUserMgmtActive ? 'rotate-180' : '' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div id="submenu-akun"
                        data-is-active="{{ $isUserMgmtActive ? "true" : "false" }}"
                        class="submenu-container {{ $isUserMgmtActive ? '' : 'hidden' }} pl-10 pr-2 pt-1 pb-1 space-y-1.5">
                        <a href="{{ route('users.index') }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('users.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('users.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Manajemen Pengguna</span>
                        </a>
                        <a href="{{ route('roles.index') }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('roles.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('roles.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Peran</span>
                        </a>
                        <a href="{{ route('permissions.index') }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('permissions.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('permissions.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Hak Akses</span>
                        </a>
                    </div>
                </div>
                <!-- Data Perguruan Tinggi -->
                @php
                    $isPtActive = request()->routeIs('perguruan-tinggi.*') || request()->routeIs('upps.*') || request()->routeIs('prodi.*') || request()->routeIs('program-studi.*') || request()->routeIs('tahun-akademik.*') || request()->routeIs('kurikulum.*');
                @endphp
                <div>
                    <button type="button"
                        class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-medium {{ $isPtActive ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                        data-target="submenu-pt" title="Data Perguruan Tinggi">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-6 h-6 shrink-0 text-white/90">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                            </svg>
                            <span class="sidebar-text truncate">Perguruan Tinggi</span>
                        </div>
                        <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200 {{ $isPtActive ? 'rotate-180' : '' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div id="submenu-pt"
                        data-is-active="{{ $isPtActive ? "true" : "false" }}"
                        class="submenu-container {{ $isPtActive ? '' : 'hidden' }} pl-10 pr-2 pt-1 pb-1 space-y-1.5">
                        <a href="{{ Route::has('perguruan-tinggi.index') ? route('perguruan-tinggi.index') : '#' }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('perguruan-tinggi.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('perguruan-tinggi.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Perguruan Tinggi</span>
                        </a>
                        <a href="{{ Route::has('prodi.index') ? route('prodi.index') : (Route::has('program-studi.index') ? route('program-studi.index') : '#') }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ (request()->routeIs('prodi.*') || request()->routeIs('program-studi.*')) ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('prodi.*') || request()->routeIs('program-studi.*')) ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Program Studi</span>
                        </a>
                        <a href="{{ Route::has('tahun-akademik.index') ? route('tahun-akademik.index') : '#' }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('tahun-akademik.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('tahun-akademik.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Tahun Akademik</span>
                        </a>
                        <a href="{{ Route::has('kurikulum.index') ? route('kurikulum.index') : '#' }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('kurikulum.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('kurikulum.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Kurikulum</span>
                        </a>
                    </div>
                </div>

                <!-- Kemahasiswaan -->
                @php
                    $isKemahasiswaanActive = request()->routeIs('angkatan.*') || request()->routeIs('kelas.*') || request()->routeIs('mahasiswa.*') || request()->routeIs('kegiatan-mahasiswa.*') || request()->routeIs('prestasi-mahasiswa.*') || request()->routeIs('kemahasiswaan.jenis-prestasi.*');
                @endphp
                <div>
                    <button type="button"
                        class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-medium {{ $isKemahasiswaanActive ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                        data-target="submenu-kemahasiswaan" title="Kemahasiswaan">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-6 h-6 shrink-0 text-white/90">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-5.25 6.557q0-.36.03-.715" />
                            </svg>
                            <span class="sidebar-text truncate">Kemahasiswaan</span>
                        </div>
                        <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200 {{ $isKemahasiswaanActive ? 'rotate-180' : '' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div id="submenu-kemahasiswaan"
                        data-is-active="{{ $isKemahasiswaanActive ? "true" : "false" }}"
                        class="submenu-container {{ $isKemahasiswaanActive ? '' : 'hidden' }} pl-10 pr-2 pt-1 pb-1 space-y-1.5">
                        <a href="{{ route('angkatan.index') }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('angkatan.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('angkatan.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Angkatan</span>
                        </a>
                        <a href="{{ route('kelas.index') }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('kelas.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('kelas.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Kelas</span>
                        </a>
                        <a href="{{ route('mahasiswa.index') }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('mahasiswa.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('mahasiswa.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Data Mahasiswa</span>
                        </a>
                        <a href="{{ Route::has('kegiatan-mahasiswa.index') ? route('kegiatan-mahasiswa.index') : '#' }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('kegiatan-mahasiswa.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('kegiatan-mahasiswa.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Kegiatan Mahasiswa</span>
                        </a>
                        <a href="{{ Route::has('prestasi-mahasiswa.index') ? route('prestasi-mahasiswa.index') : '#' }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('prestasi-mahasiswa.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span 
                                class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('prestasi-mahasiswa.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Prestasi Mahasiswa</span>
                        </a>
                    </div>
                </div>

                <!-- Kurikulum & Mata Kuliah -->
                @php
                    $isKurikulumMkActive = request()->routeIs('mata-kuliah.*') || request()->routeIs('kurikulum.*');
                @endphp
                <div>
                    <button type="button"
                        class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-medium {{ $isKurikulumMkActive ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                        data-target="submenu-kurikulum-mk" title="Kurikulum & Mata Kuliah">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <svg class="w-6 h-6 shrink-0 fill-none stroke-current text-white/90" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                            <span class="sidebar-text truncate">Kurikulum & Mata Kuliah</span>
                        </div>
                        <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200 {{ $isKurikulumMkActive ? 'rotate-180' : '' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div id="submenu-kurikulum-mk"
                        data-is-active="{{ $isKurikulumMkActive ? "true" : "false" }}"
                        class="submenu-container {{ $isKurikulumMkActive ? '' : 'hidden' }} pl-10 pr-2 pt-1 pb-1 space-y-1.5">
                        <a href="{{ route('mata-kuliah.index') }}"
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('mata-kuliah.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('mata-kuliah.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Mata Kuliah</span>
                        </a>
                    </div>
                </div>

                <!-- Validasi & Monitoring -->
                <div>
                    <a href="{{ Route::has('validasi-monitoring.index') ? route('validasi-monitoring.index') : '#' }}"
                        class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-medium {{ request()->routeIs('validasi-monitoring.*') ? 'bg-white/20 text-white shadow-xs font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group"
                        title="Validasi & Monitoring">
                        <svg class="w-6 h-6 shrink-0 text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 011.65 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                        </svg>
                        <span class="sidebar-text truncate">Validasi & Monitoring</span>
                    </a>
                </div>

                <!-- Tindak Lanjut SPMI -->
                <div>
                    <a href="{{ Route::has('spmi.tindak-lanjut') ? route('spmi.tindak-lanjut') : url('/spmi/tindak-lanjut') }}"
                        class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-medium {{ request()->routeIs('spmi.*') ? 'bg-white/20 text-white shadow-xs font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group"
                        title="Tindak Lanjut SPMI">
                        <svg class="w-6 h-6 shrink-0 text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="sidebar-text truncate">Tindak Lanjut SPMI</span>
                    </a>
                </div>

                <!-- Laporan Akhir -->
                <div>
                    <a href="{{ Route::has('laporan-akhir.index') ? route('laporan-akhir.index') : url('/laporan-akhir') }}"
                        class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-medium {{ request()->routeIs('laporan-akhir.*') ? 'bg-white/20 text-white shadow-xs font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group"
                        title="Laporan Akhir">
                        <svg class="w-6 h-6 shrink-0 text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span class="sidebar-text truncate">Laporan Akhir</span>
                    </a>
                </div>

            </nav>
    {{-- Navigasi --}}
    <nav class="flex-1 space-y-1 px-3 py-6">

        {{-- Dashboard --}}
        <a
            href="{{ route('akreditasi.superadmin.dashboard') }}"
            class="sidebar-link flex items-center gap-3 rounded-xl
                   px-3 py-2.5 text-sm transition
                   {{ request()->routeIs('akreditasi.superadmin.dashboard')
                       ? 'bg-white/10 text-white font-bold'
                       : 'text-white/90 hover:bg-white/10' }}"
        >
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24"
                 fill="currentColor" aria-hidden="true">
                <path d="M3 3h8v8H3V3Zm10 0h8v5h-8V3ZM13
                10h8v11h-8V10ZM3 13h8v8H3v-8Z"/>
            </svg>
            <span class="sidebar-text flex-1">Dashboard</span>
        </a>

        {{-- Akademik --}}
        <div>
            <button
                type="button"
                class="sidebar-link submenu-toggle-btn flex w-full
                       items-center gap-3 rounded-xl px-3 py-2.5
                       text-sm text-white/90 hover:bg-white/10 transition"
                data-target="submenu-akademik"
                aria-expanded="false"
            >
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24"
                     fill="currentColor" aria-hidden="true">
                    <path d="M12 3 1 8.5 12 14l9-4.5V16h2V8.5L12
                    3ZM5 12.2V17c3.8 3 10.2 3 14 0v-4.8l-7
                    3.5-7-3.5Z"/>
                </svg>
                <span class="sidebar-text flex-1 text-left">Akademik</span>
                <svg class="sidebar-chevron h-4 w-4 shrink-0 transition-transform"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" aria-hidden="true">
                    <path d="m7 10 5 5 5-5"/>
                </svg>
            </button>

            <div id="submenu-akademik"
                 class="submenu-container hidden space-y-1 pl-8 pt-1">
                <a href="#"
                   class="sidebar-link flex rounded-lg px-3 py-2 text-xs
                          text-white/75 hover:bg-white/10 hover:text-white">
                    <span class="sidebar-text">Data Akademik</span>
                </a>
                <a href="#"
                   class="sidebar-link flex rounded-lg px-3 py-2 text-xs
                          text-white/75 hover:bg-white/10 hover:text-white">
                    <span class="sidebar-text">Program Studi</span>
                </a>
            </div>
        </div>

        {{-- Data Master --}}
        <div>
            <button
                type="button"
                class="sidebar-link submenu-toggle-btn flex w-full
                       items-center gap-3 rounded-xl px-3 py-2.5
                       text-sm text-white/90 hover:bg-white/10 transition"
                data-target="submenu-master"
                aria-expanded="false"
            >
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24"
                     fill="currentColor" aria-hidden="true">
                    <path d="M19.4 13a7.7 7.7 0 0 0 0-2l2-1.6-2-3.4-2.4
                    1a7.8 7.8 0 0 0-1.7-1L15 3h-4l-.4 2.9a7.8 7.8
                    0 0 0-1.7 1l-2.4-1-2 3.4 2 1.6a7.7 7.7 0 0
                    0 0 2l-2 1.6 2 3.4 2.4-1a7.8 7.8 0 0 0 1.7
                    1L11 21h4l.4-2.9a7.8 7.8 0 0 0 1.7-1l2.4
                    1 2-3.4-2.1-1.7ZM13 15a3 3 0 1 1 0-6 3 3
                    0 0 1 0 6Z"/>
                </svg>
                <span class="sidebar-text flex-1 text-left">Data Master</span>
                <svg class="sidebar-chevron h-4 w-4 shrink-0 transition-transform"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" aria-hidden="true">
                    <path d="m7 10 5 5 5-5"/>
                </svg>
            </button>

            <div id="submenu-master"
                 class="submenu-container hidden space-y-1 pl-8 pt-1">
                <a href="{{ route('akreditasi.superadmin.lembaga_akreditasi') }}"
                   class="sidebar-link flex rounded-lg px-3 py-2 text-xs transition
                          {{ request()->routeIs('akreditasi.superadmin.lembaga_akreditasi', 'akreditasi.superadmin.instrumen', 'akreditasi.superadmin.kriteria', 'akreditasi.superadmin.subkriteria', 'akreditasi.superadmin.indikator', 'akreditasi.superadmin.rubrik', 'akreditasi.superadmin.bobot', 'akreditasi.superadmin.detail_bobot') 
                              ? 'bg-white/10 text-white font-bold' 
                              : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                    <span class="sidebar-text">Instrumen Akreditasi</span>
                </a>

                {{-- Menu Standar SPMI --}}
                <a href="{{ route('akreditasi.superadmin.standar_spmi') }}"
                    class="sidebar-link flex rounded-lg px-3 py-2 text-xs transition
                        {{ request()->routeIs('akreditasi.superadmin.standar_spmi') 
                            ? 'bg-white/10 text-white font-bold' 
                            : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                    <span class="sidebar-text">Standar SPMI</span>
                </a>
                {{-- AKHIR REVISI --}}

                <a href="#"
                   class="sidebar-link flex rounded-lg px-3 py-2 text-xs
                          text-white/75 hover:bg-white/10 hover:text-white">
                    <span class="sidebar-text">Indikator Penilaian</span>
                </a>
                <a href="#"
                   class="sidebar-link flex rounded-lg px-3 py-2 text-xs
                          text-white/75 hover:bg-white/10 hover:text-white">
                    <span class="sidebar-text">Data Referensi</span>
                </a>
            </div>

        {{-- SDM --}}
        <div>
            <button
                type="button"
                class="sidebar-link submenu-toggle-btn flex w-full
                       items-center gap-3 rounded-xl px-3 py-2.5
                       text-sm text-white/90 hover:bg-white/10 transition"
                data-target="submenu-sdm"
                aria-expanded="false"
            >
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24"
                     fill="currentColor" aria-hidden="true">
                    <path d="M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4
                    4ZM8 11a3.5 3.5 0 1 0-3.5-3.5A3.5 3.5
                    0 0 0 8 11Zm8 2c-3 0-8 1.5-8 4.5V21h16v-3.5
                    c0-3-5-4.5-8-4.5ZM8 13c-.5 0-1 0-1.5.1
                    C4 13.5 1 15 1 17.5V21h4v-3.5c0-1.8 1.1-3.2
                    3-4.5Z"/>
                </svg>
                <span class="sidebar-text flex-1 text-left">SDM</span>
                <svg class="sidebar-chevron h-4 w-4 shrink-0 transition-transform"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" aria-hidden="true">
                    <path d="m7 10 5 5 5-5"/>
                </svg>
            </button>

            <div id="submenu-sdm"
                 class="submenu-container hidden space-y-1 pl-8 pt-1">
                <a href="#" class="sidebar-link flex rounded-lg px-3 py-2
                    text-xs text-white/75 hover:bg-white/10">
                    <span class="sidebar-text">Data Dosen</span>
                </a>
                <a href="#" class="sidebar-link flex rounded-lg px-3 py-2
                    text-xs text-white/75 hover:bg-white/10">
                    <span class="sidebar-text">Data Tenaga Kependidikan</span>
                </a>
            </div>
        </div>

        {{-- Tri Dharma --}}
        <div>
            <button
                type="button"
                class="sidebar-link submenu-toggle-btn flex w-full
                       items-center gap-3 rounded-xl px-3 py-2.5
                       text-sm text-white/90 hover:bg-white/10 transition"
                data-target="submenu-tridharma"
                aria-expanded="false"
            >
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24"
                     fill="currentColor" aria-hidden="true">
                    <path d="M6 2h9l5 5v15H6a2 2 0 0 1-2-2V4a2
                    2 0 0 1 2-2Zm8 1v5h5M8 12h8v2H8v-2Zm0 4h8v2H8v-2Z"/>
                    <path d="M2 6v15a2 2 0 0 0 2 2h12v-2H4V6H2Z"/>
                </svg>
                <span class="sidebar-text flex-1 text-left">Tri Dharma</span>
                <svg class="sidebar-chevron h-4 w-4 shrink-0 transition-transform"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" aria-hidden="true">
                    <path d="m7 10 5 5 5-5"/>
                </svg>
            </button>

            <div id="submenu-tridharma"
                 class="submenu-container hidden space-y-1 pl-8 pt-1">
                <a href="#" class="sidebar-link flex rounded-lg px-3 py-2
                    text-xs text-white/75 hover:bg-white/10">
                    <span class="sidebar-text">Pendidikan</span>
                </a>
                <a href="#" class="sidebar-link flex rounded-lg px-3 py-2
                    text-xs text-white/75 hover:bg-white/10">
                    <span class="sidebar-text">Penelitian</span>
                </a>
                <a href="#" class="sidebar-link flex rounded-lg px-3 py-2
                    text-xs text-white/75 hover:bg-white/10">
                    <span class="sidebar-text">Pengabdian</span>
                </a>
            </div>
        </div>

        {{-- Data Akreditasi --}}
        <a href="#"
           class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5
                  text-sm text-white/90 hover:bg-white/10 transition">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24"
                 fill="currentColor" aria-hidden="true">
                <path d="M6 2h12a2 2 0 0 1 2 2v18l-8-4-8
                4V4a2 2 0 0 1 2-2Z"/>
            </svg>
            <span class="sidebar-text">Data Akreditasi</span>
        </a>

        {{-- Lembar Kerja Prodi --}}
        <a href="{{ route('akreditasi.superadmin.lembar_kerja') }}"
            class="sidebar-link flex items-center gap-3 rounded-lg px-3 py-2 text-xs transition
                {{ request()->routeIs('akreditasi.superadmin.lembar_kerja') 
                  ? 'bg-white/10 text-white font-bold' 
                  : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
            <span class="sidebar-text">Lembar Kerja Prodi</span>
        </a>

        {{-- Manajemen Akun --}}
        <a href="#"
           class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5
                  text-sm text-white/90 hover:bg-white/10 transition">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24"
                 fill="currentColor" aria-hidden="true">
                <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0
                0 0 10Zm0 2c-5 0-9 2.5-9 6v2h18v-2
                c0-3.5-4-6-9-6Z"/>
            </svg>
            <span class="sidebar-text">Manajemen Akun</span>
        </a>

        {{-- Manajemen Bukti Dokumen --}}
        <a href="{{ route('akreditasi.superadmin.bukti_dokumen') }}"
            class="sidebar-link flex items-center gap-3 rounded-lg px-3 py-2 text-xs transition
              {{ request()->routeIs('akreditasi.superadmin.bukti_dokumen') 
                  ? 'bg-white/10 text-white font-bold' 
                  : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
            <span class="sidebar-text">Manajemen Bukti Dokumen</span>
        </a>

    </nav>
</aside>