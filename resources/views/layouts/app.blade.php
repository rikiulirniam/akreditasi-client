<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('meta_title')@yield('meta_title')@else{{ trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($__env->yieldContent('title', $title ?? 'Dashboard'))))) }}@endif - Portal Akreditasi</title>

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
<body class="h-full font-sans antialiased text-black overflow-hidden">
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
            <header class="shrink-0 h-20 bg-white border-b border-[#EAEAEA] px-4 sm:px-6 lg:px-8 flex items-center justify-between shadow-xs z-30">
                
                <!-- Left: Sidebar Toggle & Page Title -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- Mobile Hamburger Button -->
                    <button 
                        type="button" 
                        id="mobileSidebarToggleBtn" 
                        class="lg:hidden p-2 rounded-xl text-primary hover:bg-gray-100 transition cursor-pointer" 
                        aria-label="Buka Menu"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <!-- Desktop Collapse Button in Topbar -->
                    <button 
                        type="button" 
                        id="topbarSidebarToggleBtn" 
                        class="hidden lg:flex p-2 rounded-xl text-primary hover:bg-gray-100 transition cursor-pointer" 
                        aria-label="Toggle Sidebar"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <!-- Page Title & Subtitle -->
                    <div class="leading-tight">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-black tracking-tight flex items-center gap-1.5 flex-wrap">
                            @yield('title', $title ?? 'Dashboard')
                        </h1>
                        @hasSection('subtitle')
                            <p class="text-xs sm:text-sm font-normal text-gray-700 mt-1">
                                @yield('subtitle')
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Right: Profile Pill (Matches User Attached Image) -->
                <div class="relative" id="profileDropdownContainer">
                    <button 
                        type="button" 
                        id="profileDropdownBtn"
                        class="bg-white-form border-2 border-primary rounded-2xl px-3.5 py-1.5 sm:px-4 sm:py-2 flex items-center gap-3 sm:gap-4 cursor-pointer shadow-xs hover:brightness-95 active:scale-[0.99] transition duration-150 select-none"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <!-- Left: Chevron Down -->
                        <svg 
                            id="profileChevron" 
                            class="w-4 h-4 sm:w-5 sm:h-5 text-primary stroke-[3] transition-transform duration-200" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>

                        <!-- Middle: Greeting & Role -->
                        <div class="text-left leading-tight">
                            <span class="block font-bold text-xs sm:text-sm text-black tracking-tight">
                                Halo, {{ explode(' ', $currentUser['nama'] ?? 'Kuwat')[0] }}
                            </span>
                            <span class="block text-[11px] sm:text-xs text-black/80 font-medium capitalize">
                                {{ strtolower($currentUser['role'] ?? 'Kaprodi') }}
                            </span>
                        </div>

                        <!-- Right: Circle User Avatar Icon (Exact line art matching attached image) -->
                        <div class="shrink-0 text-primary">
                            <svg class="w-8 h-8 sm:w-9 sm:h-9" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="18" cy="18" r="15.5" stroke="currentColor" stroke-width="2.6"/>
                                <circle cx="18" cy="14" r="4.5" stroke="currentColor" stroke-width="2.6"/>
                                <path d="M9.5 28C10.8 23.5 14.1 21.5 18 21.5C21.9 21.5 25.2 23.5 26.5 28" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <div 
                        id="profileDropdownMenu" 
                        class="absolute right-0 mt-2 w-60 rounded-2xl bg-white border border-[#EAEAEA] shadow-xl py-2 z-50 transform origin-top-right transition-all duration-150 opacity-0 scale-95 pointer-events-none"
                        role="menu"
                    >
                        <!-- User Info Header -->
                        <div class="px-4 py-2.5 border-b border-gray-100">
                            <p class="text-[11px] text-gray-form uppercase tracking-wider font-semibold">Akun Aktif</p>
                            <p class="text-sm font-bold text-primary truncate mt-0.5">{{ $currentUser['nama'] ?? 'Kuwat' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $currentUser['email'] ?? '-' }}</p>
                        </div>

                        <!-- Logout Action -->
                        <div class="p-1.5">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-semibold text-rose-600 hover:bg-rose-50 transition cursor-pointer text-left"
                                    role="menuitem"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </header>

            <!-- Main Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 pb-12">
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

            let isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';

            // Track open submenus so they remain open when expanded
            const openSubmenus = new Set();
            submenuContainers.forEach(container => {
                if (container.getAttribute('data-is-active') === 'true' || !container.classList.contains('hidden')) {
                    openSubmenus.add(container.id);
                }
            });

            function applySidebarState() {
                if (!sidebar) return;
                if (isCollapsed) {
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-20', 'is-collapsed');
                    sidebarTexts.forEach(el => el.classList.add('hidden'));
                    sidebarChevrons.forEach(el => el.classList.add('hidden'));
                    submenuContainers.forEach(el => el.classList.add('hidden'));
                } else {
                    sidebar.classList.remove('w-20', 'is-collapsed');
                    sidebar.classList.add('w-64');
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
                        sidebar.classList.add('w-64');
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
            const profileChevron = document.getElementById('profileChevron');
            let isDropdownOpen = false;

            function toggleProfileDropdown() {
                isDropdownOpen = !isDropdownOpen;
                if (isDropdownOpen) {
                    profileMenu.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
                    profileMenu.classList.add('opacity-100', 'scale-100');
                    if (profileChevron) profileChevron.classList.add('rotate-180');
                    profileBtn.setAttribute('aria-expanded', 'true');
                } else {
                    profileMenu.classList.remove('opacity-100', 'scale-100');
                    profileMenu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                    if (profileChevron) profileChevron.classList.remove('rotate-180');
                    profileBtn.setAttribute('aria-expanded', 'false');
                }
            }

            if (profileBtn) {
                profileBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    toggleProfileDropdown();
                });
            }

            // Close on click outside
            document.addEventListener('click', function (e) {
                if (isDropdownOpen && profileMenu && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
                    toggleProfileDropdown();
                }
            });

            // Close on Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    if (isDropdownOpen) toggleProfileDropdown();
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
