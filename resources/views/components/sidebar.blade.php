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