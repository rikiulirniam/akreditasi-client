@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    
    <!-- Welcome Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-primary p-6 sm:p-10 text-white shadow-lg">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-block py-1 px-3 rounded-full bg-secondary/20 text-secondary text-xs font-bold tracking-wide uppercase mb-3">
                Sistem Penjaminan Mutu & Portal Akreditasi
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2">
                Selamat Datang, {{ $user['nama'] ?? 'Pengguna' }}!
            </h2>
            <p class="text-white/80 text-xs sm:text-sm leading-relaxed">
                Anda masuk sebagai <strong class="text-secondary">{{ $user['role'] ?? 'Dosen' }}</strong>. Pantau dan kelola instrumen akreditasi, data borang, serta dokumen penilaian mutu secara terintegrasi.
            </p>
        </div>

        <!-- Decorative Background Glows -->
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-additional/30 blur-2xl pointer-events-none"></div>
        <div class="absolute right-12 -bottom-16 w-72 h-72 rounded-full bg-secondary/15 blur-3xl pointer-events-none"></div>
    </div>

    <!-- Quick Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Stat Card 1 -->
        <div class="p-5 rounded-2xl bg-white border border-[#EAEAEA] shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-form uppercase tracking-wider">Instrumen</span>
                <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-primary">9 Kriteria</p>
            <p class="text-xs text-[#5A6E85] mt-1">Standar Akreditasi BAN-PT</p>
        </div>

        <!-- Stat Card 2 -->
        <div class="p-5 rounded-2xl bg-white border border-[#EAEAEA] shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-form uppercase tracking-wider">Borang Dokumen</span>
                <div class="w-9 h-9 rounded-xl bg-additional/10 text-additional flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-primary">78%</p>
            <p class="text-xs text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                <span>↑</span> Dokumen Terverifikasi
            </p>
        </div>

        <!-- Stat Card 3 -->
        <div class="p-5 rounded-2xl bg-white border border-[#EAEAEA] shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-form uppercase tracking-wider">Status Mutu</span>
                <div class="w-9 h-9 rounded-xl bg-secondary/20 text-[#B38F00] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-primary">Baik Sekali</p>
            <p class="text-xs text-[#5A6E85] mt-1">Skor Evaluasi Terakhir</p>
        </div>

        <!-- Stat Card 4 -->
        <div class="p-5 rounded-2xl bg-white border border-[#EAEAEA] shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-form uppercase tracking-wider">Koneksi API</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-primary">Aktif</p>
            <p class="text-xs text-gray-form mt-1">Layanan Akademik Online</p>
        </div>

    </div>

    <!-- User Information Detail Section -->
    <div class="bg-white rounded-3xl border border-[#EAEAEA] p-6 sm:p-8 shadow-xs">
        <h3 class="text-base font-extrabold text-primary tracking-tight mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-additional" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            Informasi Akun Anda
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <p class="text-xs text-gray-form uppercase tracking-wider font-semibold">Nama Lengkap</p>
                <p class="text-sm font-bold text-primary mt-1">{{ $user['nama'] ?? 'Kuwat' }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <p class="text-xs text-gray-form uppercase tracking-wider font-semibold">Email Terdaftar</p>
                <p class="text-sm font-bold text-primary mt-1">{{ $user['email'] ?? '-' }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <p class="text-xs text-gray-form uppercase tracking-wider font-semibold">Peran / Jabatan</p>
                <p class="text-sm font-bold text-additional mt-1">{{ $user['role'] ?? 'Kaprodi' }}</p>
            </div>
        </div>
    </div>

</div>
@endsection
