@extends(session()->has('access_token') ? 'layouts.app' : 'errors.layout')

@section('title', '403 - Akses Dibatasi')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto my-auto py-6">
    <!-- Hero Banner (Seragam dengan Hero Banner Dashboard) -->
    <div class="relative overflow-hidden rounded-3xl bg-primary p-8 sm:p-12 text-white shadow-lg text-center">
        <!-- Decorative Glows (Sesuai Dashboard) -->
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-additional/35 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-72 h-72 rounded-full bg-secondary/20 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col items-center">
            <!-- Cap Logo in Glass Container -->
            <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center p-3 shadow-inner mb-5">
                <img src="{{ asset('images/cap-icon.png') }}" alt="Logo" class="w-10 h-auto drop-shadow-md">
            </div>

            <!-- Error Code Pill -->
            <span class="inline-flex items-center gap-2 py-1.5 px-4 rounded-full bg-secondary/20 border border-secondary/30 text-secondary text-xs font-bold tracking-wider uppercase mb-4">
                <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                Error 403 &bull; Akses Dibatasi
            </span>

            <!-- Heading -->
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-3">
                Akses Dibatasi
            </h1>

            <!-- Message -->
            <p class="text-white/80 text-sm sm:text-base leading-relaxed max-w-xl mx-auto">
                {{ $exception?->getMessage() ?: 'Anda tidak memiliki hak akses (permission) untuk membuka halaman atau melakukan tindakan ini.' }}
            </p>

            <!-- Active User Context -->
            @if(session()->has('user'))
                @php
                    $u = session('user');
                    $userName = is_array($u) ? ($u['nama'] ?? ($u['name'] ?? 'Pengguna')) : 'Pengguna';
                    $userRole = is_array($u) ? ($u['role'] ?? ($u['role_name'] ?? ($u['roles'][0]['name'] ?? 'Akun Terdaftar'))) : 'Akun Terdaftar';
                @endphp
                <div class="mt-6 inline-flex items-center gap-3 px-4 py-2 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-xs text-white/90">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span>Masuk sebagai: <strong class="text-secondary font-bold">{{ $userName }}</strong> (<span class="capitalize">{{ $userRole }}</span>)</span>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t border-white/15 w-full flex flex-col sm:flex-row items-center justify-center gap-3">
                <button
                    type="button"
                    onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ session()->has('access_token') ? route('dashboard') : url('/') }}'"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl border-2 border-white/30 hover:border-white text-white font-bold text-sm bg-white/5 hover:bg-white/15 transition cursor-pointer select-none"
                >
                    <svg class="w-4 h-4 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Kembali ke Halaman Sebelumnya</span>
                </button>

                @if(session()->has('access_token'))
                    <a
                        href="{{ route('dashboard') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-secondary hover:brightness-95 active:scale-[0.99] text-primary font-bold text-sm shadow-sm transition cursor-pointer select-none"
                    >
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Ke Dashboard</span>
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-secondary hover:brightness-95 active:scale-[0.99] text-primary font-bold text-sm shadow-sm transition cursor-pointer select-none"
                    >
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        <span>Masuk ke Akun</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
