<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('meta_title')@yield('meta_title')@elseif($__env->hasSection('browser-title'))@yield('browser-title')@else{{ trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($__env->yieldContent('title', $title ?? 'Dashboard'))))) }}@endif - Portal Akreditasi</title>

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Collapsed Sidebar Item Styles */
        #sidebar.is-collapsed .sidebar-link {
            width: 3.25rem !important;
            height: 3.25rem !important;
            padding: 0 !important;
            margin-left: auto !important;
            margin-right: auto !important;
            justify-content: center !important;
            border-radius: 1.125rem !important;
        }
        #sidebar.is-collapsed .sidebar-link svg {
            margin: 0 !important;
        }
        #sidebar.is-collapsed .sidebar-link > div {
            justify-content: center !important;
            width: 100% !important;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-black overflow-hidden {{ request()->routeIs('tenaga-kependidikan.*', 'tenaga-pendidikan.*') ? 'flow2-tendik' : '' }}">
    @php
        $currentUser = $user ?? session('user') ?? [
            'nama' => 'Kuwat',
            'email' => 'kuwat@example.com',
            'role' => 'Kaprodi',
        ];
        $tokenExpiresAt = session('token_expires_at');
        if (! $tokenExpiresAt && session()->has('access_token')) {
            $parts = explode('.', (string) session('access_token'));
            if (isset($parts[1])) {
                $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                if (isset($payload['exp'])) {
                    $tokenExpiresAt = (int) $payload['exp'];
                }
            }
        }
    @endphp

    <div class="flex h-screen w-full overflow-hidden bg-white">
        
        <!-- Sidebar Component -->
        <x-sidebar />

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
            
            <!-- Topbar -->
            <header class="shrink-0 min-h-20 bg-white px-4 sm:px-6 lg:px-[54px] pt-5 sm:pt-7 pb-3 flex items-center justify-between z-30">
                
                <!-- Left: Sidebar Toggle & Page Title -->
                <div class="flex items-center gap-3 sm:gap-5">
                    <!-- Mobile Hamburger Button -->
                    <button 
                        type="button" 
                        id="mobileSidebarToggleBtn" 
                        class="lg:hidden p-2 rounded-xl text-primary hover:bg-gray-100 transition cursor-pointer" 
                        aria-label="Buka Menu"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <!-- Desktop Collapse Button in Topbar (styled as navy hamburger matching screenshot) -->
                    <button 
                        type="button" 
                        id="topbarSidebarToggleBtn" 
                        class="hidden lg:flex p-1.5 -ml-1 text-primary hover:text-additional transition cursor-pointer self-start mt-1.5" 
                        aria-label="Toggle Sidebar"
                    >
                        <svg class="w-8 h-8 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <!-- Page Title & Subtitle -->
                    <div class="leading-tight">
                        <h1 class="text-3xl sm:text-4xl lg:text-[44px] font-black text-black tracking-tight leading-tight">
                            @yield('title', $title ?? 'Dashboard')
                        </h1>
                        @hasSection('subtitle')
                            <p class="text-sm sm:text-base lg:text-[16px] font-medium text-[#4B5563] mt-2 sm:mt-2.5">
                                @yield('subtitle')
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Right: Profile Pill & Dropdown (Aligned & Matching Prototype) -->
                <div class="relative inline-block w-[220px] sm:w-[235px]" id="profileDropdownContainer">
                    <!-- Closed State Button (Header Pill) -->
                    <button 
                        type="button" 
                        id="profileDropdownBtn"
                        class="w-full bg-[#EBF1F6] border-2 border-[#075B91] rounded-[18px] px-3.5 py-1.5 sm:px-4 sm:py-2 flex items-center gap-3 sm:gap-4 cursor-pointer shadow-xs hover:bg-[#e2eaf1] active:scale-[0.99] transition duration-150 select-none justify-between"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <svg 
                            id="profileChevron" 
                            class="w-4 h-4 sm:w-5 sm:h-5 text-[#075B91] stroke-[3] transition-transform duration-200 shrink-0" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>

                        <!-- Middle: Greeting & Role -->
                        <div class="text-left leading-tight flex-1 min-w-0 pr-1">
                            <span class="block font-extrabold text-[13px] sm:text-[14px] text-black tracking-tight truncate">
                                Halo, {{ explode(' ', $currentUser['nama'] ?? 'Kuwat')[0] }}
                            </span>
                            <span class="block text-[11px] sm:text-[11.5px] text-gray-600 font-medium truncate">
                                {{ ucfirst(strtolower($currentUser['role'] ?? 'Kaprodi')) }}
                            </span>
                        </div>

                        <!-- Right: Circle User Avatar Icon (Exact match to screenshot) -->
                        <div class="shrink-0 text-[#075B91]">
                            <svg class="w-8 h-8 sm:w-9 sm:h-9" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="18" cy="18" r="15.5" stroke="currentColor" stroke-width="2.6"/>
                                <circle cx="18" cy="14" r="4.5" stroke="currentColor" stroke-width="2.6"/>
                                <path d="M9.5 28C10.8 23.5 14.1 21.5 18 21.5C21.9 21.5 25.2 23.5 26.5 28" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </button>

                    <!-- Expanded Profile Card (compact and exactly aligned) -->
                    <div 
                        id="profileDropdownMenu" 
                        class="absolute inset-x-0 top-0 w-full bg-[#EBF1F6] border-2 border-[#075B91] rounded-[18px] shadow-2xl z-50 overflow-hidden transform origin-top transition-all duration-150 opacity-0 scale-95 pointer-events-none select-none"
                        role="menu"
                    >
                        <!-- Header Row (identical dimensions, click to close) -->
                        <button 
                            type="button" 
                            id="profileDropdownCloseBtn"
                            class="w-full bg-[#EBF1F6] px-3.5 py-1.5 sm:px-4 sm:py-2 flex items-center gap-3 sm:gap-4 cursor-pointer hover:bg-[#e2eaf1] transition select-none justify-between border-b border-[#075B91]/25"
                            aria-label="Tutup Menu Profil"
                        >
                            <!-- Chevron UP -->
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#075B91] stroke-[3] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                            </svg>
                            <!-- Greeting & Role -->
                            <div class="text-left leading-tight flex-1 min-w-0 pr-1">
                                <span class="block font-extrabold text-[13px] sm:text-[14px] text-black tracking-tight truncate">
                                    Halo, {{ explode(' ', $currentUser['nama'] ?? 'Kuwat')[0] }}
                                </span>
                                <span class="block text-[11px] sm:text-[11.5px] text-gray-600 font-medium truncate">
                                    {{ ucfirst(strtolower($currentUser['role'] ?? 'Kaprodi')) }}
                                </span>
                            </div>
                            <!-- Avatar -->
                            <div class="shrink-0 text-[#075B91]">
                                <svg class="w-8 h-8 sm:w-9 sm:h-9" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="18" cy="18" r="15.5" stroke="currentColor" stroke-width="2.6"/>
                                    <circle cx="18" cy="14" r="4.5" stroke="currentColor" stroke-width="2.6"/>
                                    <path d="M9.5 28C10.8 23.5 14.1 21.5 18 21.5C21.9 21.5 25.2 23.5 26.5 28" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </button>

                        <!-- Body: Name block + Menu Items -->
                        <div class="px-3.5 pt-2.5 pb-3">
                            <!-- Full Name & Role (compact) -->
                            <div class="mb-2.5">
                                <p class="font-extrabold text-[13.5px] text-black tracking-tight leading-snug truncate">
                                    {{ $currentUser['nama'] ?? 'Administrator Sistem' }}
                                </p>
                                <p class="text-[11px] text-black/60 font-medium">
                                    {{ ucfirst(strtolower($currentUser['role'] ?? 'Administrator')) }}
                                </p>
                            </div>

                            <!-- Divider -->
                            <div class="border-t border-[#075B91]/15 mb-2.5"></div>

                            <!-- Menu Items -->
                            <div class="space-y-2">
                                <!-- Pengaturan -->
                                <a href="#" class="flex items-center gap-2.5 text-black hover:text-[#075B91] transition group cursor-pointer" role="menuitem">
                                    <svg class="w-4 h-4 text-gray-600 group-hover:text-[#075B91] stroke-[2.2] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="text-[13px] font-semibold">Pengaturan</span>
                                </a>

                                <!-- Aktivitas -->
                                <a href="#" class="flex items-center gap-2.5 text-black hover:text-[#075B91] transition group cursor-pointer" role="menuitem">
                                    <svg class="w-4 h-4 text-gray-600 group-hover:text-[#075B91] stroke-[2.3] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12h3.75l2.25-6 4.5 12 3-7.5 1.5 3h4.5" />
                                    </svg>
                                    <span class="text-[13px] font-semibold">Aktivitas</span>
                                </a>

                                <!-- Keluar -->
                                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 text-[#8B1D1D] hover:text-[#B91C1C] transition group cursor-pointer text-left" role="menuitem">
                                        <svg class="w-4 h-4 shrink-0 fill-current text-[#8B1D1D] group-hover:text-[#B91C1C]" viewBox="0 0 24 24">
                                            <path d="M5 3h10a2 2 0 0 1 2 2v3a1 1 0 1 1-2 0V5H5v14h10v-3a1 1 0 1 1 2 0v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
                                            <path d="M14.293 8.293a1 1 0 0 1 1.414 0l3 3a1 1 0 0 1 0 1.414l-3 3a1 1 0 0 1-1.414-1.414L15.586 13H9a1 1 0 1 1 0-2h6.586l-1.293-1.293a1 1 0 0 1 0-1.414z"/>
                                        </svg>
                                        <span class="text-[13px] font-semibold">Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </header>

            <!-- Main Page Content -->
            <main class="flex-1 overflow-y-auto px-4 sm:px-6 lg:px-[54px] py-4 pb-12">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Reusable Toast Notification Component -->
    <x-toast />

    <!-- Reusable Confirmation Delete Modal Component -->
    <x-confirm-delete />

    <!-- Sidebar & Dropdown Interactive Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sidebar Elements
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const mobileToggleBtn = document.getElementById('mobileSidebarToggleBtn');
            const topbarToggleBtn = document.getElementById('topbarSidebarToggleBtn');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const sidebarChevrons = document.querySelectorAll('.sidebar-chevron');
            const submenuContainers = document.querySelectorAll('.submenu-container');
            const sidebarExpandedWidth = document.body.classList.contains('flow2-tendik') ? 'w-[314px]' : 'w-[350px]';

            let isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';

            // Track open submenus so they remain open when expanded
            const openSubmenus = new Set();
            submenuContainers.forEach(container => {
                if (container.getAttribute('data-is-active') === 'true' || !container.classList.contains('hidden')) {
                    openSubmenus.add(container.id);
                }
            });

            function applySidebarState() {
                if (isCollapsed) {
                    sidebar.classList.remove('w-[350px]', 'w-[314px]', 'w-64');
                    sidebar.classList.add('w-20', 'is-collapsed');
                    sidebarTexts.forEach(el => el.classList.add('hidden'));
                    sidebarChevrons.forEach(el => el.classList.add('hidden'));
                    submenuContainers.forEach(el => el.classList.add('hidden'));
                } else {
                    sidebar.classList.remove('w-20', 'is-collapsed');
                    sidebar.classList.add(sidebarExpandedWidth);
                    sidebarTexts.forEach(el => el.classList.remove('hidden'));
                    sidebarChevrons.forEach(el => el.classList.remove('hidden'));

                    // Restore open submenu state when expanded
                    submenuContainers.forEach(container => {
                        const targetId = container.id;
                        const btn = document.querySelector(`.submenu-toggle-btn[data-target="${targetId}"]`);
                        const chevron = btn ? btn.querySelector('.submenu-chevron') : null;
                        const shouldBeOpen = openSubmenus.has(targetId) || container.getAttribute('data-is-active') === 'true';

                        if (shouldBeOpen) {
                            container.classList.remove('hidden');
                            if (chevron) chevron.classList.add('rotate-180');
                            openSubmenus.add(targetId);
                        } else {
                            container.classList.add('hidden');
                            if (chevron) chevron.classList.remove('rotate-180');
                        }
                    });
                }
            }

            // Apply initially on desktop
            if (window.innerWidth >= 1024) {
                applySidebarState();
            }

            function toggleDesktopSidebar() {
                isCollapsed = !isCollapsed;
                localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
                applySidebarState();
            }

            function openMobileSidebar() {
                if (!sidebar || !backdrop) return;
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100');
            }

            function closeMobileSidebar() {
                if (!sidebar || !backdrop) return;
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.remove('opacity-100');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
            }

            if (topbarToggleBtn) topbarToggleBtn.addEventListener('click', toggleDesktopSidebar);
            if (mobileToggleBtn) mobileToggleBtn.addEventListener('click', openMobileSidebar);
            if (backdrop) backdrop.addEventListener('click', closeMobileSidebar);

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) {
                    applySidebarState();
                } else {
                    if (sidebar) {
                        sidebar.classList.remove('w-20', 'is-collapsed');
                        sidebar.classList.add('w-[350px]');
                    }
                    sidebarTexts.forEach(el => el.classList.remove('hidden'));
                    sidebarChevrons.forEach(el => el.classList.remove('hidden'));
                }
            });

            // Submenu Accordion Logic
            const submenuButtons = document.querySelectorAll('.submenu-toggle-btn');
            submenuButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-target');
                    const targetSubmenu = document.getElementById(targetId);
                    const chevron = this.querySelector('.submenu-chevron');

                    // If collapsed, expand first and ensure this submenu is opened
                    if (isCollapsed) {
                        openSubmenus.add(targetId);
                        toggleDesktopSidebar();
                        return;
                    }

                    if (targetSubmenu) {
                        const isHidden = targetSubmenu.classList.contains('hidden');
                        if (isHidden) {
                            targetSubmenu.classList.remove('hidden');
                            if (chevron) chevron.classList.add('rotate-180');
                            openSubmenus.add(targetId);
                        } else {
                            targetSubmenu.classList.add('hidden');
                            if (chevron) chevron.classList.remove('rotate-180');
                            openSubmenus.delete(targetId);
                        }
                    }
                });
            });

            // Profile Dropdown Elements
            const profileBtn = document.getElementById('profileDropdownBtn');
            const profileMenu = document.getElementById('profileDropdownMenu');
            const profileCloseBtn = document.getElementById('profileDropdownCloseBtn');
            let isDropdownOpen = false;

            function openProfileDropdown() {
                isDropdownOpen = true;
                if (profileMenu) {
                    profileMenu.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
                    profileMenu.classList.add('opacity-100', 'scale-100');
                }
                if (profileBtn) {
                    profileBtn.setAttribute('aria-expanded', 'true');
                    profileBtn.classList.add('invisible');
                }
            }

            function closeProfileDropdown() {
                isDropdownOpen = false;
                if (profileMenu) {
                    profileMenu.classList.remove('opacity-100', 'scale-100');
                    profileMenu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                }
                if (profileBtn) {
                    profileBtn.setAttribute('aria-expanded', 'false');
                    profileBtn.classList.remove('invisible');
                }
            }

            if (profileBtn) {
                profileBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (isDropdownOpen) {
                        closeProfileDropdown();
                    } else {
                        openProfileDropdown();
                    }
                });
            }

            if (profileCloseBtn) {
                profileCloseBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    closeProfileDropdown();
                });
            }

            // Close on click outside
            document.addEventListener('click', function (e) {
                if (isDropdownOpen && profileMenu && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
                    closeProfileDropdown();
                }
            });

            // Close on Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    if (isDropdownOpen) closeProfileDropdown();
                    closeMobileSidebar();
                }
            });

            // Auto-refresh JWT token proactively before expiration
            let tokenExpiresAt = {{ (int) ($tokenExpiresAt ?? 0) }};
            const refreshTokenUrl = "{{ route('auth.refresh-token') }}";
            const csrfMetaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (tokenExpiresAt > 0 && csrfMetaToken) {
                let tokenRefreshTimer = null;

                function scheduleTokenRefresh() {
                    if (tokenRefreshTimer) {
                        clearTimeout(tokenRefreshTimer);
                    }

                    const nowSeconds = Math.floor(Date.now() / 1000);
                    const leadSeconds = 300;
                    const secondsLeft = tokenExpiresAt - nowSeconds - leadSeconds;

                    const delayMs = Math.max(3000, secondsLeft * 1000);

                    tokenRefreshTimer = setTimeout(async function () {
                        try {
                            const res = await fetch(refreshTokenUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfMetaToken,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            if (res.ok) {
                                const data = await res.json();
                                if (data.success && data.expires_at) {
                                    tokenExpiresAt = data.expires_at;
                                    scheduleTokenRefresh();
                                }
                            } else if (res.status === 401) {
                                window.location.href = "{{ route('login') }}";
                            }
                        } catch (e) {
                            // Coba kembali dalam 30 detik jika ada gangguan jaringan
                            tokenRefreshTimer = setTimeout(scheduleTokenRefresh, 30000);
                        }
                    }, delayMs);
                }

                scheduleTokenRefresh();
            }
        });
    </script>
</body>
</html>
