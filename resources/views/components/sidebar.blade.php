<div 
    id="sidebarBackdrop" 
    class="fixed inset-0 bg-black/40 backdrop-blur-[2px] z-40 lg:hidden opacity-0 pointer-events-none transition-opacity duration-300"
    aria-hidden="true"
></div>

<aside 
    id="sidebar" 
    class="fixed lg:static inset-y-0 left-0 z-40 h-full bg-primary flex flex-col transition-all duration-300 {{ request()->routeIs('tenaga-kependidikan.*', 'tenaga-pendidikan.*') ? 'w-[314px]' : 'w-[350px]' }} -translate-x-full lg:translate-x-0 shrink-0 shadow-xl overflow-hidden select-none"
>
    <!-- Top: Logo Container with Graduate Cap & Multi-line Brand Text -->
    <div 
        class="relative h-40 sm:h-44 p-5 sm:p-6 flex items-center gap-4 bg-cover bg-center overflow-hidden shrink-0" 
        style="background-image: url('{{ asset('images/background.jpeg') }}');"
    >
        <div class="absolute inset-0 bg-gradient-to-r from-primary/40 to-black/30 pointer-events-none"></div>

        <div class="relative z-10 shrink-0">
            <img src="{{ asset('images/cap-icon.png') }}" alt="Logo" class="w-13 h-auto drop-shadow-md">
        </div>
        <div class="relative z-10 sidebar-text min-w-0 text-white font-black leading-[1.12] select-none">
            <span class="block text-base sm:text-lg font-black tracking-wider">SISTEM</span>
            <span class="block text-base sm:text-lg font-black tracking-wider">AKREDITASI</span>
            <span class="block text-xs sm:text-sm font-black tracking-wider">PROGRAM STUDI</span>
        </div>
    </div>

    <!-- Accent Border & Nav Items Container -->
    <div class="relative -mt-6 flex-1 flex flex-col bg-secondary rounded-t-[36px] pt-1.5 overflow-hidden">
        <div class="flex-1 bg-primary rounded-t-[30px] px-5 py-6 overflow-y-auto overflow-x-hidden">
            
            <nav class="space-y-4" aria-label="Menu Utama">
                
                <!-- SDM Menu (Active for Tenaga Kependidikan) -->
                @php
                    $isFlow2Tendik = request()->routeIs('tenaga-kependidikan.*', 'tenaga-pendidikan.*');
                @endphp
                <div>
                    <button 
                        type="button" 
                        class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-4 py-3.5 rounded-2xl text-base font-medium {{ $isFlow2Tendik ? 'text-secondary font-bold' : 'text-gray-300 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                        data-target="submenu-sdm"
                        title="SDM"
                    >
                        <div class="flex items-center gap-4 min-w-0">
                            <!-- Group/Users Icon -->
                            <svg class="w-6 h-6 shrink-0 fill-current {{ $isFlow2Tendik ? 'text-secondary' : 'text-white/90' }}" viewBox="0 0 24 24">
                                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                            </svg>
                            <span class="sidebar-text truncate {{ $isFlow2Tendik ? 'text-secondary font-bold' : 'font-semibold' }}">SDM</span>
                        </div>
                        <svg class="submenu-chevron sidebar-chevron w-4 h-4 {{ $isFlow2Tendik ? 'text-secondary' : 'text-white/70 group-hover:text-white' }} transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div id="submenu-sdm" class="submenu-container hidden pl-10 pr-2 pt-2 pb-1 space-y-1.5">
                        <a 
                            href="{{ route('tenaga-kependidikan.index') }}" 
                            class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('tenaga-kependidikan.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-gray-300 hover:text-white hover:bg-white/10' }}"
                        >
                            <span class="w-2 h-2 rounded-full {{ request()->routeIs('tenaga-kependidikan.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Tenaga Kependidikan</span>
                        </a>
                    </div>
                </div>

                <!-- Tri Dharma Menu -->
                <div>
                    <button 
                        type="button" 
                        class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-4 py-3.5 rounded-2xl text-base font-medium text-white hover:bg-white/10 transition-all duration-150 group cursor-pointer"
                        data-target="submenu-tridharma"
                        title="Tri Dharma"
                    >
                        <div class="flex items-center gap-4 min-w-0">
                            <!-- Academic Book Icon -->
                            <svg class="w-6 h-6 shrink-0 fill-current text-white" viewBox="0 0 24 24">
                                <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72l5 2.73 5-2.73v3.72z"/>
                            </svg>
                            <span class="sidebar-text truncate font-semibold text-white">Tri Dharma</span>
                        </div>
                        <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white group-hover:text-white transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div id="submenu-tridharma" class="submenu-container hidden pl-10 pr-2 pt-2 pb-1 space-y-1.5">
                        <a href="#" class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-medium text-gray-300 hover:text-white hover:bg-white/10 transition">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></span>
                            <span>Pendidikan & Pengajaran</span>
                        </a>
                        <a href="#" class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-medium text-gray-300 hover:text-white hover:bg-white/10 transition">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></span>
                            <span>Penelitian</span>
                        </a>
                        <a href="#" class="flex items-center gap-2.5 py-2.5 px-3.5 rounded-xl text-sm font-medium text-gray-300 hover:text-white hover:bg-white/10 transition">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></span>
                            <span>Pengabdian Masyarakat</span>
                        </a>
                    </div>
                </div>

                @unless($isFlow2Tendik)
                <!-- Dashboard (Preserved) -->
                <div>
                    <a 
                        href="{{ route('dashboard') }}" 
                        class="sidebar-link w-full flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 group {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white shadow-xs' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}"
                        title="Dashboard"
                    >
                        <svg class="w-5 h-5 shrink-0 fill-current text-white/90" viewBox="0 0 24 24">
                            <path d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"/>
                        </svg>
                        <span class="sidebar-text truncate">Dashboard</span>
                    </a>
                </div>

                <!-- Pengguna / Akun (Preserved) -->
                @php
                    $isUserMgmtActive = request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*');
                @endphp
                <div>
                    <button 
                        type="button" 
                        class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-4 py-3 rounded-2xl text-sm font-medium {{ $isUserMgmtActive ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                        data-target="submenu-akun"
                        title="Pengguna"
                    >
                        <div class="flex items-center gap-4 min-w-0">
                            <svg class="w-5 h-5 shrink-0 fill-current text-white/90" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                            <span class="sidebar-text truncate">Pengguna</span>
                        </div>
                        <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200 {{ $isUserMgmtActive ? '-rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>

                    <div id="submenu-akun" class="submenu-container {{ $isUserMgmtActive ? '' : 'hidden' }} pl-10 pr-2 pt-1 pb-1 space-y-1.5">
                        <a href="{{ route('users.index') }}" class="flex items-center gap-2 py-2 px-3 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('users.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('users.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Manajemen Pengguna</span>
                        </a>
                        <a href="{{ route('roles.index') }}" class="flex items-center gap-2 py-2 px-3 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('roles.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('roles.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Peran</span>
                        </a>
                        <a href="{{ route('permissions.index') }}" class="flex items-center gap-2 py-2 px-3 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('permissions.*') ? 'bg-white/20 text-white shadow-xs font-bold' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('permissions.*') ? 'bg-secondary' : 'bg-white/40' }} shrink-0"></span>
                            <span>Hak Akses</span>
                        </a>
                    </div>
                </div>
                @endunless

            </nav>
        </div>
    </div>
</aside>
