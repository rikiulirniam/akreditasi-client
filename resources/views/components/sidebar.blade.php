<div 
    id="sidebarBackdrop" 
    class="fixed inset-0 bg-black/40 backdrop-blur-[2px] z-40 lg:hidden opacity-0 pointer-events-none transition-opacity duration-300"
    aria-hidden="true"
></div>

<aside 
    id="sidebar" 
    class="fixed lg:static inset-y-0 left-0 z-40 h-full bg-primary flex flex-col transition-all duration-300 w-64 -translate-x-full lg:translate-x-0 shrink-0 shadow-xl overflow-hidden select-none"
>
    <div 
        class="relative h-36 sm:h-40 p-4 sm:p-5 flex items-center gap-3.5 bg-cover bg-center overflow-hidden shrink-0" 
        style="background-image: url('{{ asset('images/background.jpeg') }}');"
    >
        <div class="absolute inset-0 bg-gradient-to-r from-primary/30 to-black/20 pointer-events-none"></div>

        <div class="relative z-10 shrink-0">
            <img src="{{ asset('images/cap-icon.png') }}" alt="Logo" class="w-12 h-auto drop-shadow-md">
        </div>
        <div class="relative z-10 sidebar-text min-w-0 text-white font-black leading-[1.12] select-none">
            <span class="block text-sm sm:text-base font-extrabold tracking-wide">SISTEM</span>
            <span class="block text-sm sm:text-base font-extrabold tracking-wide">AKREDITASI</span>
            <span class="block text-xs sm:text-sm font-extrabold tracking-wide">PROGRAM STUDI</span>
        </div>
    </div>

    <div class="relative -mt-5 flex-1 flex flex-col bg-secondary rounded-t-[32px] pt-[5px] overflow-hidden">
        <div class="flex-1 bg-primary rounded-t-[27px] px-3.5 py-5 overflow-y-auto overflow-x-hidden">
            
            <nav class="space-y-3" aria-label="Menu Utama">
                
                <!-- Dashboard -->
                <div>
                    <a 
                        href="{{ route('dashboard') }}" 
                        class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 group {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                        title="Dashboard"
                    >
                        <svg class="w-6 h-6 shrink-0 fill-current text-white" viewBox="0 0 24 24">
                            <path d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"/>
                        </svg>
                        <span class="sidebar-text truncate">Dashboard</span>
                    </a>
                </div>

                <!-- Pengguna -->
                @php
                    $isUserMgmtActive = request()->routeIs('roles.*') || request()->routeIs('permissions.*');
                @endphp
                <div>
                    <button 
                        type="button" 
                        class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-medium {{ $isUserMgmtActive ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }} transition-all duration-150 group cursor-pointer"
                        data-target="submenu-akun"
                        title="Pengguna"
                    >
                        <div class="flex items-center gap-3.5 min-w-0">
                            <svg class="w-6 h-6 shrink-0 fill-current text-white/90" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                            <span class="sidebar-text truncate">Pengguna</span>
                        </div>
                        <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200 {{ $isUserMgmtActive ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div id="submenu-akun" class="submenu-container {{ $isUserMgmtActive ? '' : 'hidden' }} pl-11 pr-2 py-1 space-y-1">
                        <a href="{{ route('roles.index') }}" class="block py-1.5 px-3 rounded-lg text-xs {{ request()->routeIs('roles.*') ? 'bg-white/20 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }} transition">
                            Peran
                        </a>
                        <a href="{{ route('permissions.index') }}" class="block py-1.5 px-3 rounded-lg text-xs {{ request()->routeIs('permissions.*') ? 'bg-white/20 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }} transition">
                            Hak Akses
                        </a>
                    </div>
                </div>

                <!-- Menu 3 -->
                <div>
                    <button 
                        type="button" 
                        class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-medium text-white/80 hover:bg-white/10 hover:text-white transition-all duration-150 group cursor-pointer"
                        data-target="submenu-3"
                        title="Menu 3"
                    >
                        <div class="flex items-center gap-3.5 min-w-0">
                            <svg class="w-6 h-6 shrink-0 fill-current text-white/90" viewBox="0 0 24 24">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                            </svg>
                            <span class="sidebar-text truncate">Menu 3</span>
                        </div>
                        <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div id="submenu-3" class="submenu-container hidden pl-11 pr-2 py-1 space-y-1">
                        <a href="#" class="block py-1.5 px-3 rounded-lg text-xs font-normal text-white/70 hover:text-white hover:bg-white/10 transition">
                            Submenu 3.1
                        </a>
                        <a href="#" class="block py-1.5 px-3 rounded-lg text-xs font-normal text-white/70 hover:text-white hover:bg-white/10 transition">
                            Submenu 3.2
                        </a>
                        <a href="#" class="block py-1.5 px-3 rounded-lg text-xs font-normal text-white/70 hover:text-white hover:bg-white/10 transition">
                            Submenu 3.3
                        </a>
                    </div>
                </div>

                <!-- Menu 4 -->
                <div>
                    <button 
                        type="button" 
                        class="sidebar-link submenu-toggle-btn w-full flex items-center justify-between gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-medium text-white/80 hover:bg-white/10 hover:text-white transition-all duration-150 group cursor-pointer"
                        data-target="submenu-4"
                        title="Menu 4"
                    >
                        <div class="flex items-center gap-3.5 min-w-0">
                            <svg class="w-6 h-6 shrink-0 fill-current text-white/90" viewBox="0 0 24 24">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                            </svg>
                            <span class="sidebar-text truncate">Menu 4</span>
                        </div>
                        <svg class="submenu-chevron sidebar-chevron w-4 h-4 text-white/60 group-hover:text-white transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div id="submenu-4" class="submenu-container hidden pl-11 pr-2 py-1 space-y-1">
                        <a href="#" class="block py-1.5 px-3 rounded-lg text-xs font-normal text-white/70 hover:text-white hover:bg-white/10 transition">
                            Submenu 4.1
                        </a>
                        <a href="#" class="block py-1.5 px-3 rounded-lg text-xs font-normal text-white/70 hover:text-white hover:bg-white/10 transition">
                            Submenu 4.2
                        </a>
                    </div>
                </div>

                <!-- Menu 5 -->
                <div>
                    <a 
                        href="#" 
                        class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-medium text-white/80 hover:bg-white/10 hover:text-white transition-all duration-150 group"
                        title="Menu 5"
                    >
                        <svg class="w-6 h-6 shrink-0 fill-current text-white/90" viewBox="0 0 24 24">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                        <span class="sidebar-text truncate">Menu 5</span>
                    </a>
                </div>

                <!-- Menu 6 -->
                <div>
                    <a 
                        href="#" 
                        class="sidebar-link w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-sm font-medium text-white/80 hover:bg-white/10 hover:text-white transition-all duration-150 group"
                        title="Menu 6"
                    >
                        <svg class="w-6 h-6 shrink-0 fill-current text-white/90" viewBox="0 0 24 24">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                        <span class="sidebar-text truncate">Menu 6</span>
                    </a>
                </div>

            </nav>
        </div>
    </div>
</aside>
