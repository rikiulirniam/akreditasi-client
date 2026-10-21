<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $rawMeta = trim((string) $__env->yieldContent('meta_title'));
        if ($rawMeta === '') {
            $rawMeta = trim((string) $__env->yieldContent('browser-title'));
        }
        if ($rawMeta === '') {
            $rawMeta = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $__env->yieldContent('title', $title ?? 'Dashboard')))));
        }
        $pageTitle = $rawMeta !== '' ? $rawMeta : 'Portal Akreditasi';
    @endphp
    <title>{{ $pageTitle }} - Portal Akreditasi</title>

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
            'nama' => 'Kuwat Santoso',
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
            <header class="shrink-0 min-h-20 bg-white px-4 sm:px-6 lg:px-[54px] pt-5 sm:pt-7 pb-4 sm:pb-5 flex items-center justify-between z-30">
                
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
                    <div>
                        <h1 class="text-3xl sm:text-4xl lg:text-[44px] font-black text-black tracking-tight leading-tight flex items-center gap-1.5 flex-wrap">
                            @yield('title', $title ?? 'Dashboard')
                        </h1>
                        @hasSection('subtitle')
                            <p class="text-sm sm:text-base lg:text-[16px] font-medium text-[#4B5563] mt-2 sm:mt-2.5">
                                @yield('subtitle')
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Right: Profile Pill (Matches User Reference Image) -->
                <div class="relative w-[215px]" id="profileDropdownContainer">
                    <button 
                        type="button" 
                        id="profileDropdownBtn"
                        class="w-full bg-[#E5E5E5] border-2 border-[#3A5675] rounded-[16px] px-3.5 py-2 flex items-center justify-between cursor-pointer select-none transition-all duration-200"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <svg 
                            id="profileChevron" 
                            class="w-4 h-4 text-[#012650] stroke-[3] transition-transform duration-300 shrink-0" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>

                        <!-- Middle: Greeting & Role -->
                        <div class="text-left leading-tight min-w-0 flex-1 px-2.5">
                            <span class="block font-bold text-[13.5px] text-black tracking-tight truncate">
                                Halo, {{ $currentUser['nama'] ?? 'Kuwat' }}
                            </span>
                            <span class="block text-[11px] text-[#656565] font-semibold truncate">
                                {{ ucfirst(strtolower($currentUser['role'] ?? 'Kaprodi')) }}
                            </span>
                        </div>

                        <!-- Right: Circle User Avatar Icon (Blue on white circular background matching photo 1) -->
                        <div class="shrink-0 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white flex items-center justify-center text-[#01568D]">
                            <svg class="w-full h-full" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="18" cy="18" r="16.5" stroke="currentColor" stroke-width="2.6"/>
                                <circle cx="18" cy="14" r="4.5" stroke="currentColor" stroke-width="2.6"/>
                                <path d="M9.5 28C10.8 23.5 14.1 21.5 18 21.5C21.9 21.5 25.2 23.5 26.5 28" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </button>

                    <!-- Dropdown Menu (Drops down below the button, not overlaying/nimpa) -->
                    <div 
                        id="profileDropdownMenu" 
                        role="menu"
                        aria-label="Menu Profil"
                    >
                        <div class="px-4 pt-3.5 pb-4 space-y-3.5 select-none">
                            <!-- User Full Name & Role -->
                            <div class="text-left leading-tight">
                                <p class="text-[15px] font-bold text-black tracking-tight">{{ $currentUser['nama'] ?? 'Kuwat Santoso' }}</p>
                                <p class="text-[12px] font-normal text-gray-700 mt-0.5">{{ ucfirst(strtolower($currentUser['role'] ?? 'Kaprodi')) }}</p>
                            </div>

                            <!-- Menu Items -->
                            <div class="space-y-3 pt-1">
                                <!-- Pengaturan -->
                                <a href="#" class="flex items-center gap-3 text-[14px] font-medium text-black hover:text-[#01568D] transition group" role="menuitem">
                                    <svg class="w-4 h-4 text-gray-800 group-hover:text-[#01568D] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                    </svg>
                                    <span>Pengaturan</span>
                                </a>

                                <!-- Aktivitas -->
                                <a href="#" class="flex items-center gap-3 text-[14px] font-medium text-black hover:text-[#01568D] transition group" role="menuitem">
                                    <svg class="w-4 h-4 text-gray-800 group-hover:text-[#01568D] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                    </svg>
                                    <span>Aktivitas</span>
                                </a>

                                <!-- Keluar -->
                                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 text-[14px] font-medium text-[#8B1E1E] hover:text-red-800 transition cursor-pointer text-left" role="menuitem">
                                        <svg class="w-4 h-4 text-[#8B1E1E] fill-current shrink-0" viewBox="0 0 24 24">
                                            <path d="M5 3h10a2 2 0 0 1 2 2v3a1 1 0 0 1-2 0V5H5v14h10v-3a1 1 0 0 1 2 0v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
                                            <path d="M14.293 8.293a1 1 0 0 1 1.414 0l3 3a1 1 0 0 1 0 1.414l-3 3a1 1 0 0 1-1.414-1.414L15.586 13H9a1 1 0 0 1 0-2h6.586l-1.293-1.293a1 1 0 0 1 0-1.414z"/>
                                        </svg>
                                        <span>Keluar</span>
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
            const sidebarExpandedWidth = 'w-[350px]';

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
                    sidebar.classList.remove('w-[350px]', 'w-64');
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
            const profileContainer = document.getElementById('profileDropdownContainer');
            const profileBtn = document.getElementById('profileDropdownBtn');
            const profileChevron = document.getElementById('profileChevron');
            let isDropdownOpen = false;

            function openProfileDropdown() {
                isDropdownOpen = true;
                if (profileContainer) {
                    profileContainer.classList.add('is-open');
                }
                if (profileBtn) {
                    profileBtn.setAttribute('aria-expanded', 'true');
                }
                if (profileChevron) {
                    profileChevron.classList.add('rotate-180');
                }
            }

            function closeProfileDropdown() {
                isDropdownOpen = false;
                if (profileContainer) {
                    profileContainer.classList.remove('is-open');
                }
                if (profileBtn) {
                    profileBtn.setAttribute('aria-expanded', 'false');
                }
                if (profileChevron) {
                    profileChevron.classList.remove('rotate-180');
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

            // Close on click outside
            document.addEventListener('click', function (e) {
                if (isDropdownOpen && profileContainer && !profileContainer.contains(e.target)) {
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
