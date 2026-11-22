<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Read Data Dokumen Evidence') - Portal Akreditasi</title>

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 overflow-y-auto">
    {{-- Header Khusus Asesor / Tamu Eksternal --}}
    <header class="sticky top-0 z-30 w-full bg-white/95 backdrop-blur-md border-b border-zinc-200 px-4 sm:px-8 py-3.5 shadow-2xs">
        <div class="max-w-[1200px] mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center p-2 shadow-xs shrink-0">
                    <img src="{{ asset('images/cap-icon.png') }}" alt="Logo" class="w-6 h-auto">
                </div>
                <div>
                    <span class="block text-xs sm:text-sm font-extrabold text-slate-900 tracking-tight leading-tight">
                        SISTEM AKREDITASI PROGRAM STUDI
                    </span>
                    <span class="block text-[11px] font-semibold text-sky-800">
                        Portal Verifikasi Asesor Eksternal & SPMI
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                    <span class="size-2 rounded-full bg-purple-600 animate-pulse"></span>
                    <span>Read-Only Asesor</span>
                </span>
                <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-xl border border-zinc-300 text-xs font-bold text-slate-700 hover:bg-slate-100 transition-colors">
                    Login Sistem
                </a>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="py-8 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>
</body>
</html>

