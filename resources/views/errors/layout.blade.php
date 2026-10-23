<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - Portal Akreditasi</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-white text-black min-h-screen flex flex-col justify-between selection:bg-secondary/40 selection:text-primary">
    <!-- Topbar Header (Identik dengan Topbar aplikasi utama & login) -->
    <header class="shrink-0 h-20 bg-white border-b border-[#EAEAEA] px-4 sm:px-6 lg:px-8 flex items-center justify-between shadow-xs sticky top-0 z-30">
        <a href="{{ session()->has('access_token') ? route('dashboard') : url('/') }}" class="flex items-center gap-3.5 group">
            <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center p-2 shadow-xs group-hover:scale-105 transition-transform shrink-0">
                <img src="{{ asset('images/cap-icon.png') }}" alt="Logo Portal" class="w-6 h-auto drop-shadow-xs">
            </div>
            <div>
                <span class="block text-base font-extrabold tracking-tight text-primary leading-tight">Portal Akreditasi</span>
                <span class="block text-[11px] text-gray-form uppercase tracking-wider font-semibold">Sistem Penjaminan Mutu</span>
            </div>
        </a>

        <div>
            @if(session()->has('access_token'))
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-secondary hover:brightness-95 text-primary text-xs sm:text-sm font-bold shadow-xs transition">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Ke Dashboard</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-secondary hover:brightness-95 text-primary text-xs sm:text-sm font-bold shadow-xs transition">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    <span>Masuk ke Akun</span>
                </a>
            @endif
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-4xl mx-auto">
            @yield('content')
        </div>
    </main>

    <!-- Footer Sistem -->
    <footer class="border-t border-[#EAEAEA] py-4 px-6 text-center text-xs text-gray-form">
        <p>&copy; {{ date('Y') }} Sistem Informasi Akreditasi Program Studi. Hak cipta dilindungi.</p>
    </footer>
</body>
</html>
