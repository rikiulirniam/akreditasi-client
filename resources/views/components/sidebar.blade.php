{{-- resources/views/components/sidebar.blade.php --}}

@php
    $sidebarUser = session('user', []);
    $userRole = strtolower((string) (($sidebarUser['role'] ?? null) ?: ($sidebarUser['role_name'] ?? '')));
    $isReviewerRole = str_contains($userRole, 'reviewer');
@endphp

{{-- Backdrop untuk sidebar mobile --}}
<div
    id="sidebarBackdrop"
    class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none transition-opacity duration-300 lg:hidden"
></div>

<aside
    id="sidebar"
    class="fixed inset-y-0 left-0 z-50 flex h-screen w-64 -translate-x-full flex-col bg-[#06284F] text-white transition-all duration-300 lg:relative lg:inset-auto lg:translate-x-0 shrink-0 select-none"
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
                <path d="M4 19L24 9L44 19L24 29L4 19Z" fill="currentColor"/>
                <path d="M10 23V36M38 23V36" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                <path d="M18 30V39H30V30" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
            </svg>

            <div class="sidebar-text min-w-0">
                <p class="text-sm font-extrabold uppercase leading-tight tracking-tight">
                    Sistem Akreditasi
                </p>
                <p class="text-xs font-semibold uppercase leading-tight tracking-wider text-white/80">
                    Program Studi
                </p>
            </div>
        </div>
    </div>

    {{-- Curved card envelope --}}
    <div class="relative -mt-5 flex-1 flex flex-col bg-secondary rounded-t-[32px] pt-[5px] overflow-hidden">
        <div class="flex-1 bg-primary rounded-t-[27px] px-3.5 py-4 overflow-y-auto overflow-x-hidden">

            <nav class="space-y-2" aria-label="Menu Utama">

                @if ($isReviewerRole)
                    {{-- ============================================================== --}}
                    {{-- MENU REVIEWER --}}
                    {{-- ============================================================== --}}

                    <div class="sidebar-section-header pt-1 pb-1 px-3.5">
                        <p class="sidebar-text text-[11px] font-bold uppercase tracking-wider text-white/50">Portal Reviewer</p>
                    </div>

                    {{-- Dashboard Reviewer --}}
                    <div>
                        <a href="{{ route('reviewer.dashboard') }}"
                            class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 group {{ request()->routeIs('reviewer.dashboard') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                            title="Dashboard">
                            <svg class="w-6 h-6 shrink-0 fill-current text-white/90 group-hover:text-white" viewBox="0 0 24 24">
                                <path d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z" />
                            </svg>
                            <span class="sidebar-text truncate">Dashboard</span>
                        </a>
                    </div>

                    {{-- Penilaian Reviewer --}}
                    <div>
                        <a href="{{ route('reviewer.assessments') }}"
                            class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 group {{ request()->routeIs('reviewer.assessments*') || request()->routeIs('reviewer.assessment-*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                            title="Penilaian">
                            <svg class="w-6 h-6 shrink-0 text-white/90 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12m-12 5.25h12m-12 5.25h12M3.75 6.75h.008v.008H3.75V6.75zm0 5.25h.008v.008H3.75V12zm0 5.25h.008v.008H3.75v-.008z" />
                            </svg>
                            <span class="sidebar-text truncate">Penilaian</span>
                        </a>
                    </div>

                @else
                    {{-- ============================================================== --}}
                    {{-- MENU UTAMA (KAPRODI / SUPERADMIN / UMUM) --}}
                    {{-- ============================================================== --}}

                    <div class="sidebar-section-header pt-1 pb-1 px-3.5">
                        <p class="sidebar-text text-[11px] font-bold uppercase tracking-wider text-white/50">Menu Utama</p>
                    </div>

                    {{-- Dashboard --}}
                    @php
                        $isSuperadminContext = request()->routeIs('akreditasi.superadmin.*');
                        $isKaprodiContext = request()->routeIs('kaprodi.*');
                        $dashboardRoute = $isSuperadminContext && Route::has('akreditasi.superadmin.dashboard')
                            ? route('akreditasi.superadmin.dashboard')
                            : ($isKaprodiContext && Route::has('kaprodi.dashboard') ? route('kaprodi.dashboard') : route('dashboard'));
                        $isDashboardActive = request()->routeIs('dashboard') || request()->routeIs('akreditasi.superadmin.dashboard') || request()->routeIs('kaprodi.dashboard');
                    @endphp
                    <div>
                        <a href="{{ $dashboardRoute }}"
                            class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 group {{ $isDashboardActive ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                            title="Dashboard">
                            <svg class="w-6 h-6 shrink-0 fill-current text-white/90 group-hover:text-white" viewBox="0 0 24 24">
                                <path d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z" />
                            </svg>
                            <span class="sidebar-text truncate">Dashboard</span>
                        </a>
                    </div>

                    {{-- Project Akreditasi (Kaprodi) --}}
                    @if (Route::has('kaprodi.project'))
                    <div>
                        <a href="{{ route('kaprodi.project') }}"
                            class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 group {{ request()->routeIs('kaprodi.project*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                            title="Project Akreditasi">
                            <svg class="w-6 h-6 shrink-0 text-white/90 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                            </svg>
                            <span class="sidebar-text truncate">Project Akreditasi</span>
                        </a>
                    </div>
                    @endif

                    {{-- ============================================================== --}}
                    {{-- SEKSI: MANAJEMEN & AKADEMIK --}}
                    {{-- ============================================================== --}}
                    <div class="sidebar-section-header pt-3 pb-1 px-3.5 border-t border-white/10 mt-3 mb-1">
                        <p class="sidebar-text text-[11px] font-bold uppercase tracking-wider text-white/50">Data & Akademik</p>
                    </div>

                    {{-- Pengguna --}}
                    @haspermission('user.read')
                    @php
                        $isUserMgmtActive = request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*');
                    @endphp
                    <div>
                        <button type="button"
                            class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold {{ $isUserMgmtActive ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                            data-target="submenu-akun" title="Pengguna">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <svg class="w-6 h-6 shrink-0 fill-current text-white/90 group-hover:text-white" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                </svg>
                                <span class="sidebar-text truncate">Pengguna</span>
                            </div>
                            <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200 {{ $isUserMgmtActive ? 'rotate-180' : '' }}"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div id="submenu-akun"
                            data-is-active="{{ $isUserMgmtActive ? 'true' : 'false' }}"
                            class="submenu-container {{ $isUserMgmtActive ? '' : 'hidden' }} pl-10 pr-2 pt-1 pb-1 space-y-1.5">
                            <a href="{{ route('users.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('users.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('users.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Manajemen Pengguna</span>
                            </a>
                            <a href="{{ route('roles.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('roles.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('roles.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Peran</span>
                            </a>
                            <a href="{{ route('permissions.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('permissions.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('permissions.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Hak Akses</span>
                            </a>
                        </div>
                    </div>
                    @endhaspermission

                    {{-- Data Perguruan Tinggi --}}
                    @php
                        $isPtActive = request()->routeIs('perguruan-tinggi.*') || request()->routeIs('upps.*') || request()->routeIs('prodi.*') || request()->routeIs('program-studi.*') || request()->routeIs('tahun-akademik.*');
                    @endphp
                    <div>
                        <button type="button"
                            class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold {{ $isPtActive ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                            data-target="submenu-pt" title="Perguruan Tinggi">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <svg class="w-6 h-6 shrink-0 text-white/90 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                                </svg>
                                <span class="sidebar-text truncate">Perguruan Tinggi</span>
                            </div>
                            <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200 {{ $isPtActive ? 'rotate-180' : '' }}"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div id="submenu-pt"
                            data-is-active="{{ $isPtActive ? 'true' : 'false' }}"
                            class="submenu-container {{ $isPtActive ? '' : 'hidden' }} pl-10 pr-2 pt-1 pb-1 space-y-1.5">
                            <a href="{{ route('perguruan-tinggi.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('perguruan-tinggi.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('perguruan-tinggi.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Perguruan Tinggi</span>
                            </a>
                            <a href="{{ route('prodi.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ (request()->routeIs('prodi.*') || request()->routeIs('program-studi.*')) ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('prodi.*') || request()->routeIs('program-studi.*')) ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Program Studi</span>
                            </a>
                            @if (Route::has('upps.index'))
                            <a href="{{ route('upps.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('upps.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('upps.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">UPPS</span>
                            </a>
                            @endif
                            <a href="{{ route('tahun-akademik.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('tahun-akademik.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('tahun-akademik.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Tahun Akademik</span>
                            </a>
                        </div>
                    </div>

                    {{-- Kemahasiswaan --}}
                    @php
                        $isKemahasiswaanActive = request()->routeIs('angkatan.*') || request()->routeIs('kelas.*') || request()->routeIs('mahasiswa.*') || request()->routeIs('kegiatan-mahasiswa.*') || request()->routeIs('prestasi-mahasiswa.*') || request()->routeIs('kemahasiswaan.jenis-prestasi.*');
                    @endphp
                    <div>
                        <button type="button"
                            class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold {{ $isKemahasiswaanActive ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                            data-target="submenu-kemahasiswaan" title="Kemahasiswaan">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <svg class="w-6 h-6 shrink-0 text-white/90 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-5.25 6.557q0-.36.03-.715" />
                                </svg>
                                <span class="sidebar-text truncate">Kemahasiswaan</span>
                            </div>
                            <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200 {{ $isKemahasiswaanActive ? 'rotate-180' : '' }}"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div id="submenu-kemahasiswaan"
                            data-is-active="{{ $isKemahasiswaanActive ? 'true' : 'false' }}"
                            class="submenu-container {{ $isKemahasiswaanActive ? '' : 'hidden' }} pl-10 pr-2 pt-1 pb-1 space-y-1.5">
                            <a href="{{ route('angkatan.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('angkatan.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('angkatan.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Angkatan</span>
                            </a>
                            <a href="{{ route('kelas.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('kelas.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('kelas.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Kelas</span>
                            </a>
                            <a href="{{ route('mahasiswa.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('mahasiswa.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('mahasiswa.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Data Mahasiswa</span>
                            </a>
                            @if (Route::has('kegiatan-mahasiswa.index'))
                            <a href="{{ route('kegiatan-mahasiswa.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('kegiatan-mahasiswa.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('kegiatan-mahasiswa.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Kegiatan Mahasiswa</span>
                            </a>
                            @endif
                            @if (Route::has('prestasi-mahasiswa.index'))
                            <a href="{{ route('prestasi-mahasiswa.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('prestasi-mahasiswa.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('prestasi-mahasiswa.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Prestasi Mahasiswa</span>
                            </a>
                            @endif
                        </div>
                    </div>

                    {{-- Kurikulum & Mata Kuliah --}}
                    @php
                        $isKurikulumMkActive = request()->routeIs('mata-kuliah.*') || request()->routeIs('kurikulum.*');
                    @endphp
                    <div>
                        <button type="button"
                            class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold {{ $isKurikulumMkActive ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                            data-target="submenu-kurikulum-mk" title="Kurikulum & Mata Kuliah">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <svg class="w-6 h-6 shrink-0 fill-none stroke-current text-white/90 group-hover:text-white" stroke-width="1.8" viewBox="0 0 24 24">
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
                            data-is-active="{{ $isKurikulumMkActive ? 'true' : 'false' }}"
                            class="submenu-container {{ $isKurikulumMkActive ? '' : 'hidden' }} pl-10 pr-2 pt-1 pb-1 space-y-1.5">
                            <a href="{{ route('kurikulum.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('kurikulum.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('kurikulum.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Kurikulum</span>
                            </a>
                            <a href="{{ route('mata-kuliah.index') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('mata-kuliah.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('mata-kuliah.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Daftar Mata Kuliah</span>
                            </a>
                        </div>
                    </div>

                    {{-- SDM --}}
                    @php
                        $isTendikPage = request()->routeIs('tenaga-kependidikan.*') || request()->routeIs('tenaga-pendidikan.*') || (request()->routeIs('pendidikan.*') && (request()->filled('id_tendik') || !empty($selectedTendik)));
                        $isDosenPage = request()->routeIs('dosen.*') || (request()->routeIs('pendidikan.*') && !empty($isDosen)) || (request()->routeIs('pendidikan.*') && (request()->filled('id_dosen') || !empty($selectedDosen)));
                        $isSdmActive = $isDosenPage || $isTendikPage || request()->routeIs('sertifikasi.*');
                    @endphp
                    <div>
                        <button
                            type="button"
                            class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold {{ $isSdmActive ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                            data-target="submenu-sdm"
                            title="SDM"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <svg class="w-6 h-6 shrink-0 fill-current text-white/90 group-hover:text-white" viewBox="0 0 24 24">
                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                </svg>
                                <span class="sidebar-text truncate">SDM</span>
                            </div>
                            <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200 {{ $isSdmActive ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div id="submenu-sdm"
                            data-is-active="{{ $isSdmActive ? 'true' : 'false' }}"
                            class="submenu-container {{ $isSdmActive ? '' : 'hidden' }} pl-10 pr-2 pt-1 pb-1 space-y-1.5">
                            <a href="{{ route('dosen.index') }}" class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ $isDosenPage ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isDosenPage ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Dosen</span>
                            </a>
                            <a href="{{ route('tenaga-kependidikan.index') }}" class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ $isTendikPage ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isTendikPage ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Tenaga Kependidikan</span>
                            </a>
                            <a href="{{ route('sertifikasi.index') }}" class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('sertifikasi.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('sertifikasi.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Sertifikasi</span>
                            </a>
                        </div>
                    </div>

                    {{-- Tri Dharma --}}
                    <div>
                        <button
                            type="button"
                            class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold text-white/80 hover:bg-white/10 hover:text-white transition-all duration-150 group cursor-pointer"
                            data-target="submenu-tridharma"
                            title="Tri Dharma"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <svg class="w-6 h-6 shrink-0 fill-current text-white/90 group-hover:text-white" viewBox="0 0 24 24">
                                    <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/>
                                </svg>
                                <span class="sidebar-text truncate">Tri Dharma</span>
                            </div>
                            <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div id="submenu-tridharma" class="submenu-container hidden pl-10 pr-2 pt-1 pb-1 space-y-1.5">
                            <a href="#" class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold text-white/80 hover:text-white hover:bg-white/10 transition">
                                <span class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></span>
                                <span class="truncate">Pendidikan &amp; Pengajaran</span>
                            </a>
                            <a href="#" class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold text-white/80 hover:text-white hover:bg-white/10 transition">
                                <span class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></span>
                                <span class="truncate">Penelitian</span>
                            </a>
                            <a href="#" class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold text-white/80 hover:text-white hover:bg-white/10 transition">
                                <span class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></span>
                                <span class="truncate">Pengabdian</span>
                            </a>
                            <a href="#" class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold text-white/80 hover:text-white hover:bg-white/10 transition">
                                <span class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></span>
                                <span class="truncate">Kerja Sama</span>
                            </a>
                        </div>
                    </div>

                    {{-- ============================================================== --}}
                    {{-- SEKSI: PENJAMINAN MUTU & AKREDITASI --}}
                    {{-- ============================================================== --}}
                    <div class="sidebar-section-header pt-3 pb-1 px-3.5 border-t border-white/10 mt-3 mb-1">
                        <p class="sidebar-text text-[11px] font-bold uppercase tracking-wider text-white/50">Akreditasi & SPMI</p>
                    </div>

                    {{-- Validasi & Monitoring --}}
                    <div>
                        <a href="{{ route('validasi-monitoring.index') }}"
                            class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 group {{ request()->routeIs('validasi-monitoring.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                            title="Validasi & Monitoring">
                            <svg class="w-6 h-6 shrink-0 text-white/90 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 011.65 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                            </svg>
                            <span class="sidebar-text truncate">Validasi & Monitoring</span>
                        </a>
                    </div>

                    {{-- Tindak Lanjut SPMI --}}
                    <div>
                        <a href="{{ route('spmi.tindak-lanjut') }}"
                            class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 group {{ request()->routeIs('spmi.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                            title="Tindak Lanjut SPMI">
                            <svg class="w-6 h-6 shrink-0 text-white/90 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="sidebar-text truncate">Tindak Lanjut SPMI</span>
                        </a>
                    </div>

                    {{-- Laporan Akhir --}}
                    <div>
                        <a href="{{ route('laporan-akhir.index') }}"
                            class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 group {{ request()->routeIs('laporan-akhir.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                            title="Laporan Akhir">
                            <svg class="w-6 h-6 shrink-0 text-white/90 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span class="sidebar-text truncate">Laporan Akhir</span>
                        </a>
                    </div>

                    {{-- Data Master Akreditasi --}}
                    @php
                        $isMasterActive = request()->routeIs('akreditasi.superadmin.lembaga_akreditasi', 'akreditasi.superadmin.instrumen', 'akreditasi.superadmin.kriteria', 'akreditasi.superadmin.subkriteria', 'akreditasi.superadmin.indikator', 'akreditasi.superadmin.rubrik', 'akreditasi.superadmin.standar_spmi');
                    @endphp
                    <div>
                        <button type="button"
                            class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold {{ $isMasterActive ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                            data-target="submenu-master" title="Data Master Akreditasi">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <svg class="w-6 h-6 shrink-0 fill-current text-white/90 group-hover:text-white" viewBox="0 0 24 24">
                                    <path d="M19.4 13a7.7 7.7 0 0 0 0-2l2-1.6-2-3.4-2.4 1a7.8 7.8 0 0 0-1.7-1L15 3h-4l-.4 2.9a7.8 7.8 0 0 0-1.7 1l-2.4-1-2 3.4 2 1.6a7.7 7.7 0 0 0 0 2l-2 1.6 2 3.4 2.4-1a7.8 7.8 0 0 0 1.7 1L11 21h4l.4-2.9a7.8 7.8 0 0 0 1.7-1l2.4 1 2-3.4-2.1-1.7ZM13 15a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"/>
                                </svg>
                                <span class="sidebar-text truncate">Data Master Akreditasi</span>
                            </div>
                            <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200 {{ $isMasterActive ? 'rotate-180' : '' }}"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div id="submenu-master"
                            data-is-active="{{ $isMasterActive ? 'true' : 'false' }}"
                            class="submenu-container {{ $isMasterActive ? '' : 'hidden' }} pl-10 pr-2 pt-1 pb-1 space-y-1.5">
                            @if (Route::has('akreditasi.superadmin.lembaga_akreditasi'))
                            <a href="{{ route('akreditasi.superadmin.lembaga_akreditasi') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('akreditasi.superadmin.lembaga_akreditasi', 'akreditasi.superadmin.instrumen', 'akreditasi.superadmin.kriteria', 'akreditasi.superadmin.subkriteria', 'akreditasi.superadmin.indikator', 'akreditasi.superadmin.rubrik') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('akreditasi.superadmin.lembaga_akreditasi', 'akreditasi.superadmin.instrumen', 'akreditasi.superadmin.kriteria', 'akreditasi.superadmin.subkriteria', 'akreditasi.superadmin.indikator', 'akreditasi.superadmin.rubrik') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Instrumen Akreditasi</span>
                            </a>
                            @endif

                            @if (Route::has('akreditasi.superadmin.standar_spmi'))
                            <a href="{{ route('akreditasi.superadmin.standar_spmi') }}"
                                class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('akreditasi.superadmin.standar_spmi') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('akreditasi.superadmin.standar_spmi') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                                <span class="truncate">Standar SPMI</span>
                            </a>
                            @endif
                        </div>
                    </div>

                    {{-- Lembar Kerja Prodi --}}
                    @if (Route::has('akreditasi.superadmin.lembar_kerja'))
                    <div>
                        <a href="{{ route('akreditasi.superadmin.lembar_kerja') }}"
                            class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 group {{ request()->routeIs('akreditasi.superadmin.lembar_kerja') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                            title="Lembar Kerja Prodi">
                            <svg class="w-6 h-6 shrink-0 text-white/90 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                            </svg>
                            <span class="sidebar-text truncate">Lembar Kerja Prodi</span>
                        </a>
                    </div>
                    @endif

                    {{-- Manajemen Bukti Dokumen --}}
                    @if (Route::has('akreditasi.superadmin.bukti_dokumen'))
                    <div>
                        <a href="{{ route('akreditasi.superadmin.bukti_dokumen') }}"
                            class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 group {{ request()->routeIs('akreditasi.superadmin.bukti_dokumen') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                            title="Manajemen Bukti Dokumen">
                            <svg class="w-6 h-6 shrink-0 text-white/90 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                            </svg>
                            <span class="sidebar-text truncate">Bukti Dokumen</span>
                        </a>
                    </div>
                    @endif

                @endif

            </nav>
        </div>
    </div>
</aside>
