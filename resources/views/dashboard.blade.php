<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard - Portal Akreditasi</title>

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-[#F8FAFC] text-black flex flex-col min-h-screen">
    
    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-30 w-full bg-white/95 backdrop-blur-md border-b border-[#EAEAEA] shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
            
            <!-- Brand Logo -->
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center p-2 shadow-xs shrink-0">
                    <img src="{{ asset('images/cap-icon.png') }}" alt="Logo Portal" class="w-6 h-auto">
                </div>
                <div>
                    <span class="block text-base font-bold tracking-tight text-primary leading-tight">Portal Akreditasi</span>
                    <span class="block text-[11px] text-gray-form uppercase tracking-wider font-medium">Sistem Penjaminan Mutu</span>
                </div>
            </div>

            <!-- User Menu & Logout -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- User Profile Pill -->
                <div class="flex items-center gap-3 pl-3 pr-2 py-1.5 rounded-full bg-gray-50 border border-gray-200/80">
                    <div class="hidden sm:block text-right">
                        <span class="block text-xs font-bold text-primary leading-tight">
                            {{ $user['nama'] ?? 'Pengguna' }}
                        </span>
                        <span class="block text-[10px] font-semibold text-additional">
                            {{ $user['role'] ?? 'DOSEN' }}
                        </span>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                        {{ strtoupper(substr($user['nama'] ?? 'U', 0, 1)) }}
                    </div>
                </div>

                <!-- Logout Form -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button 
                        type="submit" 
                        class="p-2 sm:px-3 sm:py-2 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition cursor-pointer flex items-center gap-1.5"
                        title="Keluar dari akun"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        
        <!-- Welcome Hero Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-primary p-6 sm:p-10 text-white shadow-xl mb-8">
            <div class="relative z-10 max-w-2xl">
                <span class="inline-block py-1 px-3 rounded-full bg-secondary/20 text-secondary text-xs font-bold tracking-wide uppercase mb-3">
                    Portal Akreditasi Aktif
                </span>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight mb-2">
                    Selamat Datang, {{ $user['nama'] ?? 'Pengguna' }}!
                </h1>
                <p class="text-white/80 text-sm sm:text-base leading-relaxed">
                    Anda telah berhasil masuk ke Sistem Penjaminan Mutu & Portal Akreditasi.
                </p>
            </div>

            <!-- Subtle Decorative Circles in Background -->
            <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-additional/20 blur-2xl pointer-events-none"></div>
            <div class="absolute right-20 -bottom-20 w-80 h-80 rounded-full bg-secondary/10 blur-3xl pointer-events-none"></div>
        </div>

        <!-- User Information Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Card 1: Profil Akun -->
            <div class="p-6 rounded-2xl bg-white border border-[#EAEAEA] shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <h3 class="text-xs font-bold text-gray-form uppercase tracking-wider mb-1">Nama Lengkap</h3>
                <p class="text-base font-extrabold text-primary">{{ $user['nama'] ?? '-' }}</p>
                <p class="text-xs text-[#5A6E85] mt-1">{{ $user['email'] ?? '-' }}</p>
            </div>

            <!-- Card 2: Role Pengguna -->
            <div class="p-6 rounded-2xl bg-white border border-[#EAEAEA] shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-additional/10 text-additional flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <h3 class="text-xs font-bold text-gray-form uppercase tracking-wider mb-1">Peran Akses</h3>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-additional/10 text-additional">
                        {{ $user['role'] ?? 'DOSEN' }}
                    </span>
                    <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Aktif
                    </span>
                </div>
            </div>

            <!-- Card 3: Status Sesi -->
            <div class="p-6 rounded-2xl bg-white border border-[#EAEAEA] shadow-xs">
                <div class="w-10 h-10 rounded-xl bg-secondary/20 text-[#B38F00] flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h3 class="text-xs font-bold text-gray-form uppercase tracking-wider mb-1">Autentikasi</h3>
                <p class="text-base font-extrabold text-primary">Token JWT Terhubung</p>
                <p class="text-xs text-gray-form mt-1">Terhubung ke API Akademik</p>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="border-t border-[#EAEAEA] bg-white py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-form gap-2">
            <p>&copy; {{ date('Y') }} Portal Akreditasi. Sistem Penjaminan Mutu.</p>
            <p>Terhubung ke Layanan Akademik</p>
        </div>
    </footer>

    <!-- Reusable Toast Notification Component -->
    <x-toast />
</body>
</html>
