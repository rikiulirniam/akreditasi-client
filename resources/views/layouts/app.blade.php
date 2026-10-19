<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('browser-title', $title ?? 'Dashboard') - Portal Akreditasi</title>

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
    @endphp

    <div class="flex h-screen w-full overflow-hidden bg-white">
        
        <!-- Sidebar Component -->
        <x-sidebar />

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
            
            <!-- Topbar -->
            <header class="shrink-0 min-h-24 bg-white px-4 sm:px-6 lg:px-[54px] py-3.5 flex items-center justify-between z-30">
                
                <!-- Left: Sidebar Toggle & Page Title -->
                <div class="flex items-center gap-3 sm:gap-4">
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
                        class="hidden lg:flex p-1.5 -ml-1 text-primary hover:text-additional transition cursor-pointer" 
                        aria-label="Toggle Sidebar"
                    >
                        <svg class="w-8 h-8 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <!-- Page Title & Subtitle -->
                    <div>
                        <h1 class="text-2xl sm:text-3xl lg:text-[44px] font-extrabold text-black tracking-tight leading-none">
                            @yield('title', $title ?? 'Dashboard')
                        </h1>
                        @hasSection('subtitle')
                            <p class="text-sm sm:text-base lg:text-[20px] font-semibold text-black mt-1">
                                @yield('subtitle')
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Right: Profile Pill -->
                <div class="relative" id="profileDropdownContainer">
                    <button 
                        type="button" 
                        id="profileDropdownBtn"
                        class="relative z-[51] flex items-center gap-2 rounded-md border border-primary bg-[#e5e5e5] px-2.5 py-1.5 text-primary shadow-sm transition duration-150 hover:bg-[#dcdcdc] active:scale-[0.99] sm:gap-2.5 sm:px-3"
                        style="background-color: #e5e5e5;"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <svg 
                            id="profileChevron" 
                            class="h-4 w-4 shrink-0 text-primary stroke-[3] transition-transform duration-200 sm:h-[18px] sm:w-[18px]"
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>

                        <div class="text-left leading-tight">
                            <span class="block whitespace-nowrap text-xs font-bold tracking-tight text-[#102b46] sm:text-[13px]">
                                Halo, {{ explode(' ', $currentUser['nama'] ?? 'Kuwat')[0] }}
                            </span>
                            <span class="block text-[10px] font-medium leading-tight text-gray-600">
                                {{ ucfirst(strtolower($currentUser['role'] ?? 'Kaprodi')) }}
                            </span>
                        </div>

                        <div class="shrink-0 text-additional">
                            <svg class="h-8 w-8 sm:h-[34px] sm:w-[34px]" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="18" cy="18" r="15.5" stroke="currentColor" stroke-width="2.6"/>
                                <circle cx="18" cy="14" r="4.5" stroke="currentColor" stroke-width="2.6"/>
                                <path d="M9.5 28C10.8 23.5 14.1 21.5 18 21.5C21.9 21.5 25.2 23.5 26.5 28" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <div 
                        id="profileDropdownMenu" 
                        class="pointer-events-none absolute right-0 top-full z-50 w-full origin-top-right scale-95 transform rounded-b-md border border-t-0 border-primary bg-[#e5e5e5] py-1.5 opacity-0 shadow-lg transition-all duration-150"
                        style="background-color: #e5e5e5;"
                        role="menu"
                    >
                        <div class="border-b border-primary/20 px-3 py-1.5">
                            <p class="truncate text-xs font-bold leading-tight text-[#102b46]">{{ $currentUser['nama'] ?? 'Kuwat' }}</p>
                            <p class="truncate text-[10px] font-medium leading-tight text-gray-600">{{ ucfirst(strtolower($currentUser['role'] ?? 'Kaprodi')) }}</p>
                        </div>

                        <div class="space-y-0.5 p-1">
                            <div class="flex items-center gap-2 rounded px-2 py-1.5 text-xs font-medium text-gray-800">
                                <svg class="h-3.5 w-3.5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h3m-1.5-1.5v3M6.75 9.75l-1.06-1.06m12.62 0-1.06 1.06M4.5 13.5h1.5m12 0h1.5M7.5 19.5l1.06-1.06m6.88 0 1.06 1.06M12 21v-1.5m0-12a6 6 0 1 0 0 12 6 6 0 0 0 0-12Z" />
                                </svg>
                                Pengaturan
                            </div>
                            <div class="flex items-center gap-2 rounded px-2 py-1.5 text-xs font-medium text-gray-800">
                                <svg class="h-3.5 w-3.5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h3l2-6 4 12 2-6h3l2 3h2" />
                                </svg>
                                Aktivitas
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="flex w-full cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-left text-xs font-medium transition hover:bg-white/60"
                                    style="color: #dc2626;"
                                    role="menuitem"
                                >
                                    <svg class="h-3.5 w-3.5" style="color: #dc2626;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 4H5.5A1.5 1.5 0 0 0 4 5.5v13A1.5 1.5 0 0 0 5.5 20H10M14 8l4 4-4 4m4-4H9" />
                                    </svg>
                                    Keluar
                                </button>
                            </form>
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

            let isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';

            function applySidebarState() {
                if (isCollapsed) {
                    sidebar.classList.remove('w-[350px]', 'w-64');
                    sidebar.classList.add('w-20', 'is-collapsed');
                    sidebarTexts.forEach(el => el.classList.add('hidden'));
                    sidebarChevrons.forEach(el => el.classList.add('hidden'));
                    submenuContainers.forEach(el => el.classList.add('hidden'));
                } else {
                    sidebar.classList.remove('w-20', 'is-collapsed');
                    sidebar.classList.add('w-[350px]');
                    sidebarTexts.forEach(el => el.classList.remove('hidden'));
                    sidebarChevrons.forEach(el => el.classList.remove('hidden'));
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

            // Submenu Accordion Logic
            const submenuButtons = document.querySelectorAll('.submenu-toggle-btn');
            submenuButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    // If collapsed, expand first so submenu is visible
                    if (isCollapsed) {
                        toggleDesktopSidebar();
                    }

                    const targetId = this.getAttribute('data-target');
                    const targetSubmenu = document.getElementById(targetId);
                    const chevron = this.querySelector('.submenu-chevron');

                    if (targetSubmenu) {
                        const isHidden = targetSubmenu.classList.contains('hidden');
                        if (isHidden) {
                            targetSubmenu.classList.remove('hidden');
                            if (chevron) chevron.classList.add('rotate-180');
                        } else {
                            targetSubmenu.classList.add('hidden');
                            if (chevron) chevron.classList.remove('rotate-180');
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
                    profileBtn.classList.add('rounded-b-none');
                    if (profileChevron) profileChevron.classList.add('rotate-180');
                    profileBtn.setAttribute('aria-expanded', 'true');
                } else {
                    profileMenu.classList.remove('opacity-100', 'scale-100');
                    profileMenu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                    profileBtn.classList.remove('rounded-b-none');
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
        });
    </script>
</body>
</html>
