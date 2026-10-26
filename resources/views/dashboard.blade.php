@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6 w-full">

    <!-- 1. Hero Banner: Pusat Kendali Sistem -->
    <div class="relative overflow-hidden rounded-2xl bg-[#0F3560] p-6 sm:p-8 text-white shadow-sm">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="max-w-2xl">
                <p class="text-xs font-bold text-secondary uppercase tracking-wider mb-2">
                    PUSAT KENDALI SISTEM
                </p>
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white mb-2 leading-tight">
                    Selamat datang, {{ $user['nama'] ?? 'Superadmin' }}
                </h2>
                <p class="text-white/80 text-xs sm:text-sm font-normal leading-relaxed">
                    Pantau indikator kerja utama dan kesiapan data akreditasi Program Studi dari satu tempat
                </p>
            </div>

            <!-- Status Sistem Badge -->
            <div class="shrink-0 self-start lg:self-center">
                <div class="border border-white/25 rounded-xl px-5 py-3.5 bg-white/5 min-w-[200px]">
                    <p class="text-[11px] font-semibold text-white/70 uppercase tracking-wider mb-1">STATUS SISTEM</p>
                    <div class="flex items-center gap-2 text-xs sm:text-sm font-medium text-white">
                        <span class="w-2.5 h-2.5 rounded-full {{ ($statusSistem['is_normal'] ?? true) ? 'bg-secondary' : 'bg-amber-400' }} inline-block shrink-0"></span>
                        <span>{{ $statusSistem['text'] ?? 'Semua layanan normal' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Top 6 KPI Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">

        <!-- KPI 1: Mahasiswa Aktif -->
        <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex flex-col justify-between group min-h-[140px]">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#6BB8EB] text-gray-900 flex items-center justify-center shadow-xs border-b-2 border-black/10 shrink-0">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>
            </div>
            <div class="mt-2.5 sm:mt-3 min-w-0">
                <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-700 uppercase tracking-tight block whitespace-nowrap" title="Mahasiswa Aktif">Mahasiswa Aktif</span>
                <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $kpi['mahasiswa_aktif'] ?? 425 }}</p>
                <p class="text-[8.5px] sm:text-[9px] font-semibold text-gray-500 uppercase tracking-tight mt-1 whitespace-nowrap">SEMESTER BERJALAN</p>
            </div>
        </div>

        <!-- KPI 2: Mahasiswa Baru -->
        <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex flex-col justify-between group min-h-[140px]">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#6BE877] text-gray-900 flex items-center justify-center shadow-xs border-b-2 border-black/10 shrink-0">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <line x1="19" y1="8" x2="19" y2="14" />
                        <line x1="22" y1="11" x2="16" y2="11" />
                    </svg>
                </div>
            </div>
            <div class="mt-2.5 sm:mt-3 min-w-0">
                <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-700 uppercase tracking-tight block whitespace-nowrap" title="Mahasiswa Baru">Mahasiswa Baru</span>
                <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $kpi['mahasiswa_baru'] ?? 112 }}</p>
                <p class="text-[8.5px] sm:text-[9px] font-semibold text-gray-500 uppercase tracking-tight mt-1 whitespace-nowrap">ANGKATAN {{ $kpi['tahun_angkatan_baru'] ?? '2026' }}</p>
            </div>
        </div>

        <!-- KPI 3: Lulus Tepat Waktu -->
        <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex flex-col justify-between group min-h-[140px]">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#E3BF22] text-gray-900 flex items-center justify-center shadow-xs border-b-2 border-black/10 shrink-0">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                        <path d="M6 12v5c3 3 9 3 12 0v-5" />
                    </svg>
                </div>
            </div>
            <div class="mt-2.5 sm:mt-3 min-w-0">
                <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-700 uppercase tracking-tight block whitespace-nowrap" title="Lulus Tepat Waktu">Lulus Tepat Waktu</span>
                <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $kpi['lulus_tepat_waktu'] ?? 87 }} %</p>
                <p class="text-[8.5px] sm:text-[9px] font-semibold text-gray-500 uppercase tracking-tight mt-1 whitespace-nowrap">TARGET MINIMUM : 85%</p>
            </div>
        </div>

        <!-- KPI 4: Rata-Rata IPK -->
        <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex flex-col justify-between group min-h-[140px]">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#C57841] text-gray-900 flex items-center justify-center shadow-xs border-b-2 border-black/10 shrink-0">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="10" y1="2" x2="14" y2="2" />
                        <line x1="12" y1="2" x2="12" y2="5" />
                        <circle cx="12" cy="14" r="8" />
                        <path d="M7 14h2.5l1.5-3 2 6 1.5-3h2.5" />
                    </svg>
                </div>
            </div>
            <div class="mt-2.5 sm:mt-3 min-w-0">
                <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-700 uppercase tracking-tight block whitespace-nowrap" title="Rata-Rata IPK">Rata-Rata IPK</span>
                <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ number_format($kpi['rata_rata_ipk'] ?? 3.78, 2) }}</p>
                <p class="text-[8.5px] sm:text-[9px] font-semibold text-gray-500 uppercase tracking-tight mt-1 whitespace-nowrap">SKALA 4.00</p>
            </div>
        </div>

        <!-- KPI 5: Prestasi Nasional -->
        <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex flex-col justify-between group min-h-[140px]">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#B79EE6] text-gray-900 flex items-center justify-center shadow-xs border-b-2 border-black/10 shrink-0">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m8 9-3-6h2.5l2 4.5" />
                        <path d="m16 9 3-6h-2.5l-2 4.5" />
                        <circle cx="12" cy="15" r="6" />
                        <path d="m12 12.5.8 1.7 1.8.2-1.3 1.3.4 1.8-1.7-.9-1.7.9.4-1.8-1.3-1.3 1.8-.2z" fill="currentColor" stroke="none" />
                    </svg>
                </div>
            </div>
            <div class="mt-2.5 sm:mt-3 min-w-0">
                <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-700 uppercase tracking-tight block whitespace-nowrap" title="Prestasi Nasional">Prestasi Nasional</span>
                <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $kpi['prestasi_nasional'] ?? 97 }}</p>
                <p class="text-[8.5px] sm:text-[9px] font-semibold text-gray-500 uppercase tracking-tight mt-1 whitespace-nowrap">TAHUN 2026</p>
            </div>
        </div>

        <!-- KPI 6: Prestasi Internasional -->
        <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex flex-col justify-between group min-h-[140px]">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#E36F75] text-gray-900 flex items-center justify-center shadow-xs border-b-2 border-black/10 shrink-0">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 4h10v6a5 5 0 0 1-10 0V4Z" />
                        <path d="M7 6H4v4h3" />
                        <path d="M17 6h3v4h-3" />
                        <line x1="12" y1="15" x2="12" y2="19" />
                        <line x1="8" y1="19" x2="16" y2="19" />
                    </svg>
                </div>
            </div>
            <div class="mt-2.5 sm:mt-3 min-w-0">
                <span class="text-[9px] sm:text-[9.5px] font-semibold text-gray-700 uppercase tracking-tight block whitespace-nowrap" title="Prestasi Internasional">Prestasi Internasional</span>
                <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $kpi['prestasi_internasional'] ?? 34 }}</p>
                <p class="text-[8.5px] sm:text-[9px] font-semibold text-gray-500 uppercase tracking-tight mt-1 whitespace-nowrap">TAHUN 2026</p>
            </div>
        </div>

    </div>

    <!-- 3. Section: Ringkasan Tri Dharma -->
    <div class="space-y-3">
        <!-- 8 Cards Grid (4x2 on desktop) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-3">

            <!-- Card 1: DTPS -->
            <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex items-center gap-3 sm:gap-3.5 group min-h-[88px]">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-[#E8C86A] text-gray-900 flex items-center justify-center shrink-0 shadow-xs border-b-2 border-black/10">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="7" r="4" />
                        <path d="M5.5 20a6.5 6.5 0 0 1 13 0" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-600 uppercase tracking-tight block truncate" title="DTPS">DTPS</span>
                    <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $tridharma['dtps'] ?? 20 }}</p>
                </div>
            </div>

            <!-- Card 2: Dosen S3 -->
            <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex items-center gap-3 sm:gap-3.5 group min-h-[88px]">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-[#7B6EE2] text-gray-900 flex items-center justify-center shrink-0 shadow-xs border-b-2 border-black/10">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                        <path d="M6 12v5c3 3 9 3 12 0v-5" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-600 uppercase tracking-tight block truncate" title="Dosen S3">Dosen S3</span>
                    <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $tridharma['dosen_s3'] ?? 8 }}</p>
                </div>
            </div>

            <!-- Card 3: Lektor Kepala -->
            <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex items-center gap-3 sm:gap-3.5 group min-h-[88px]">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-[#E3906F] text-gray-900 flex items-center justify-center shrink-0 shadow-xs border-b-2 border-black/10">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="7" r="4" />
                        <path d="M5 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2" />
                        <path d="M12 15v4" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-600 uppercase tracking-tight block truncate" title="Lektor Kepala">Lektor Kepala</span>
                    <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $tridharma['lektor_kepala'] ?? 3 }}</p>
                </div>
            </div>

            <!-- Card 4: Penelitian -->
            <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex items-center gap-3 sm:gap-3.5 group min-h-[88px]">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-[#2CD4D9] text-gray-900 flex items-center justify-center shrink-0 shadow-xs border-b-2 border-black/10">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3.5" y="3.5" width="4.5" height="17" rx="2" />
                        <rect x="10" y="10" width="4.5" height="10.5" rx="2" />
                        <rect x="16.5" y="14" width="4" height="6.5" rx="1.5" />
                        <circle cx="15.5" cy="8.5" r="4" fill="#2CD4D9" />
                        <line x1="18.5" y1="11.5" x2="21.5" y2="14.5" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-600 uppercase tracking-tight block truncate" title="Penelitian">Penelitian</span>
                    <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $tridharma['penelitian'] ?? 35 }}</p>
                </div>
            </div>

            <!-- Card 5: Publikasi Scopus -->
            <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex items-center gap-3 sm:gap-3.5 group min-h-[88px]">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-[#9990E4] text-gray-900 flex items-center justify-center shrink-0 shadow-xs border-b-2 border-black/10">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="3" width="16" height="18" rx="3" />
                        <line x1="7.5" y1="7" x2="16.5" y2="7" />
                        <rect x="7.5" y="10" width="9" height="5" rx="1" />
                        <line x1="7.5" y1="18" x2="12.5" y2="18" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-600 uppercase tracking-tight block truncate" title="Publikasi Scopus">Publikasi Scopus</span>
                    <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $tridharma['publikasi_scopus'] ?? 14 }}</p>
                </div>
            </div>

            <!-- Card 6: HKI -->
            <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex items-center gap-3 sm:gap-3.5 group min-h-[88px]">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-[#DCF06B] text-gray-900 flex items-center justify-center shrink-0 shadow-xs border-b-2 border-black/10">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M14.5 9.5a3.5 3.5 0 0 0-2.5-1 3.5 3.5 0 0 0-3.5 3.5 3.5 3.5 0 0 0 3.5 3.5c1 0 1.9-.4 2.5-1" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-600 uppercase tracking-tight block truncate" title="HKI">HKI</span>
                    <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $tridharma['hki'] ?? 8 }}</p>
                </div>
            </div>

            <!-- Card 7: PKM -->
            <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex items-center gap-3 sm:gap-3.5 group min-h-[88px]">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-[#5EECE3] text-gray-900 flex items-center justify-center shrink-0 shadow-xs border-b-2 border-black/10">
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a2.5 2.5 0 0 1-2.5-2.5Z" />
                        <path d="M8 2v8l2.5-2 2.5 2V2" />
                        <line x1="8" y1="17" x2="16" y2="17" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-600 uppercase tracking-tight block truncate" title="PKM">PKM</span>
                    <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $tridharma['pkm'] ?? 22 }}</p>
                </div>
            </div>

            <!-- Card 8: Kerja Sama Aktif -->
            <div class="p-3 sm:p-3.5 rounded-2xl bg-[#D6DEE5] border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] hover:-translate-y-1 hover:shadow-[0_12px_24px_-4px_rgba(15,23,42,0.16),0_6px_10px_-3px_rgba(15,23,42,0.1),inset_0_1px_0_0_rgba(255,255,255,0.8)] transition-all duration-200 flex items-center gap-3 sm:gap-3.5 group min-h-[88px]">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-[#5DE386] text-gray-900 flex items-center justify-center shrink-0 shadow-xs border-b-2 border-black/10">
                    <svg class="w-6 h-6 sm:w-6.5 sm:h-6.5 text-gray-900" viewBox="0 0 122.88 67.72" fill="currentColor">
                        <path fill-rule="evenodd" d="M3.24,16.78l2.3,28.47c.31,2.94,1,4.83,1.84,5.87a2.82,2.82,0,0,0,2.85.85l.27,0H21.78a3.84,3.84,0,0,0,3.31-1.32l.05-.06A8.31,8.31,0,0,0,26.32,46V14.57a1.21,1.21,0,0,1-.07-.43.49.49,0,0,1,0-.12A2.93,2.93,0,0,0,25,11.92a7.16,7.16,0,0,0-3.88-.78H7a4.3,4.3,0,0,0-3.19,1.17c-.67.8-.91,2.18-.58,4.33v.14ZM93.1,15.57a10.86,10.86,0,0,1,.09-1.43L88,10.65c-.75-.5-1.57-1.08-2.4-1.66-3-2.08-6-4.22-9.33-5.09a26.12,26.12,0,0,0-5.94-.82,20.38,20.38,0,0,0-5.46.6,14,14,0,0,0-3,1.14,10.75,10.75,0,0,0-2.31,1.64L56,10.63a1.59,1.59,0,0,1-.44.52L45.89,22.66a4.21,4.21,0,0,0,.84,1.85,4.09,4.09,0,0,0,1.78,1.26,5.77,5.77,0,0,0,2.52.31,8.21,8.21,0,0,0,4.42-1.83L58.14,22c.68-.56,1.25-1.08,1.81-1.59a24.11,24.11,0,0,1,3.78-3c3-1.8,6-1.86,9,3.12L87.93,48H93.1V15.57Zm1.23-4.39a5.67,5.67,0,0,1,.8-.9A8.24,8.24,0,0,1,100.8,8.5H114c2.77,0,5,.49,6.58,1.69,1.73,1.34,2.55,3.37,2.2,6.29l-2.92,30.21a10.68,10.68,0,0,1-2.2,6.27,6.74,6.74,0,0,1-5.7,2.22H100.09a6.06,6.06,0,0,1-4.26-1.31,8,8,0,0,1-2.15-2.81H89.57a7.09,7.09,0,0,1,.08,4.52,7.43,7.43,0,0,1-2.47,3.5,8.47,8.47,0,0,1-3.87,1.71,7.68,7.68,0,0,1-4.17-.46,10.83,10.83,0,0,1-4.91,3.13A8.53,8.53,0,0,1,69,63.22a10.44,10.44,0,0,1-5.56,3.46,10.22,10.22,0,0,1-6.62-1,9.76,9.76,0,0,1-2.29,1.35,8.61,8.61,0,0,1-3.55.65,9.66,9.66,0,0,1-6.55-2.39,19.2,19.2,0,0,1-3.68-5.09L34.19,48.8H29.11a9,9,0,0,1-1.51,3.63l-.07.1A6.74,6.74,0,0,1,21.7,55H10.61a5.66,5.66,0,0,1-5.56-1.89c-1.3-1.51-2.2-4-2.58-7.57v-.13L.17,17.05c-.46-3.06,0-5.23,1.28-6.71A7.13,7.13,0,0,1,6.87,8.06H21.1a10,10,0,0,1,5.57,1.27h0a5.73,5.73,0,0,1,2.43,3.25h9.55a27.48,27.48,0,0,1,7.13-3.67A19.39,19.39,0,0,1,54,8.27l3.27-3.89.12-.13a13.71,13.71,0,0,1,3-2.16A17.83,17.83,0,0,1,64.06.69,24.23,24.23,0,0,1,70.35,0,29.26,29.26,0,0,1,77,.92h0c3.81,1,7.1,3.31,10.3,5.55.77.54,1.53,1.07,2.35,1.62l4.65,3.09ZM15.77,38.76a2.66,2.66,0,1,1-2.65,2.65,2.66,2.66,0,0,1,2.65-2.65ZM86.36,50.92a1.57,1.57,0,0,1-.88-1L70.06,22.09c-1.71-2.86-3.25-2.94-4.75-2.05A22.52,22.52,0,0,0,62,22.71c-.68.62-1.36,1.24-1.92,1.7l-2.73,2.25a11.38,11.38,0,0,1-6.09,2.5,9,9,0,0,1-3.86-.5,7.28,7.28,0,0,1-3.11-2.22,7.44,7.44,0,0,1-1.59-4.11,1.58,1.58,0,0,1,.45-1.22l8.33-9.91a15,15,0,0,0-4.81.67,25.56,25.56,0,0,0-6.63,3.48,1.5,1.5,0,0,1-1,.33H29.41v30h5.67a1.55,1.55,0,0,1,1.38.84l7,12.15a17.79,17.79,0,0,0,3,4.34,6.77,6.77,0,0,0,4.58,1.6,5.63,5.63,0,0,0,2.3-.42,5.8,5.8,0,0,0,1.31-.75L50.14,55a1.54,1.54,0,0,1,2.71-1.45l4.94,9.23a6.67,6.67,0,0,0,8.87-1.54L59.55,50.06A1.54,1.54,0,0,1,60,47.93a1.56,1.56,0,0,1,2.13.47l7.49,11.77a5.81,5.81,0,0,0,3.78.32,7.65,7.65,0,0,0,3.44-2.24L70.07,45.47A1.55,1.55,0,0,1,72.8,44l7,13.22a4.44,4.44,0,0,0,3,.5,5.27,5.27,0,0,0,2.45-1.08,4.27,4.27,0,0,0,1.45-2,4.12,4.12,0,0,0-.23-3,1.48,1.48,0,0,1-.12-.71Zm20.32-12.16A2.66,2.66,0,1,1,104,41.41a2.66,2.66,0,0,1,2.65-2.65Z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[9.5px] sm:text-[10px] font-semibold text-gray-600 uppercase tracking-tight block truncate" title="Kerja Sama Aktif">Kerja Sama Aktif</span>
                    <p class="text-2xl sm:text-[28px] font-black text-[#183A5F] tracking-tight mt-0.5 leading-none">{{ $tridharma['kerjasama_aktif'] ?? 17 }}</p>
                </div>
            </div>

        </div>
    </div>

    <!-- 4. Bottom Grid: Tren Chart & Prestasi Mahasiswa Terbaru -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">

        <!-- Left: Tren Rata-Rata IPK & Kelulusan -->
        <div class="bg-[#D6DEE5] rounded-2xl border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] p-5 sm:p-6 shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] flex flex-col justify-between">
            <div>
                <h3 class="text-base sm:text-lg font-black text-[#183A5F] tracking-tight uppercase">
                    TREN RATA-RATA IPK & KELULUSAN
                </h3>

                <!-- Custom Dual-Axis Visual SVG Chart -->
                <div class="relative w-full mt-4">
                    @php
                        $years = $tren['years'] ?? ['2022', '2023', '2024', '2025', '2026'];
                        $ipkData = $tren['ipk'] ?? [3.30, 3.40, 3.52, 3.52, 3.52];
                        $kelulusanData = $tren['kelulusan'] ?? [73, 88, 77, 87, 85];
                        $totalYears = max(1, count($years));

                        $ipkPoints = [];
                        $kelulusanPoints = [];

                        foreach ($years as $idx => $year) {
                            $x = 90 + ($idx * 90);
                            $ipk = (float) ($ipkData[$idx] ?? 3.50);
                            $pct = (float) ($kelulusanData[$idx] ?? 80);

                            // Skala IPK 2.10 (y=250) s/d 3.50 (y=45)
                            $yIpk = 250 - (($ipk - 2.10) / 1.40) * 205;
                            $yIpk = max(35, min(250, round($yIpk, 1)));

                            // Skala Kelulusan 50% (y=250) s/d 90% (y=45)
                            $yPct = 250 - (($pct - 50) / 40) * 205;
                            $yPct = max(35, min(250, round($yPct, 1)));

                            $ipkPoints[] = ['x' => $x, 'y' => $yIpk, 'val' => $ipk];
                            $kelulusanPoints[] = ['x' => $x, 'y' => $yPct, 'val' => $pct];
                        }

                        // Bangun kurva halus cubic bezier SVG
                        $buildSvgPath = function (array $points): string {
                            if (empty($points)) return '';
                            $d = 'M ' . $points[0]['x'] . ' ' . $points[0]['y'];
                            $len = count($points);
                            for ($i = 0; $i < $len - 1; $i++) {
                                $p0 = $points[$i];
                                $p1 = $points[$i + 1];
                                $dx = ($p1['x'] - $p0['x']) / 2;
                                $d .= ' C ' . ($p0['x'] + $dx) . ' ' . $p0['y'] . ', ' . ($p1['x'] - $dx) . ' ' . $p1['y'] . ', ' . $p1['x'] . ' ' . $p1['y'];
                            }
                            return $d;
                        };

                        $ipkPath = $buildSvgPath($ipkPoints);
                        $kelulusanPath = $buildSvgPath($kelulusanPoints);
                    @endphp

                    <svg viewBox="0 0 520 280" class="w-full h-auto overflow-visible select-none" preserveAspectRatio="xMidYMid meet">
                        <!-- Horizontal Gridlines & Y-Axis values -->
                        <!-- Row: 3.50 / 90% -->
                        <line x1="52" y1="45" x2="465" y2="45" stroke="#CAD5DE" stroke-width="1" />
                        <text x="44" y="49" text-anchor="end" font-size="10.5" font-weight="700" fill="#334155">3.50</text>
                        <text x="473" y="49" text-anchor="start" font-size="10.5" font-weight="700" fill="#334155">90%</text>

                        <!-- Row: 3.40 -->
                        <line x1="48" y1="70" x2="55" y2="70" stroke="#CAD5DE" stroke-width="1" />
                        <text x="44" y="74" text-anchor="end" font-size="10.5" font-weight="700" fill="#334155">3.40</text>

                        <!-- Row: 3.30 / 80% -->
                        <line x1="52" y1="95" x2="465" y2="95" stroke="#CAD5DE" stroke-width="1" />
                        <text x="44" y="99" text-anchor="end" font-size="10.5" font-weight="700" fill="#334155">3.30</text>
                        <text x="473" y="99" text-anchor="start" font-size="10.5" font-weight="700" fill="#334155">80%</text>

                        <!-- Row: 3.00 / 70% -->
                        <line x1="52" y1="145" x2="465" y2="145" stroke="#CAD5DE" stroke-width="1" />
                        <text x="44" y="149" text-anchor="end" font-size="10.5" font-weight="700" fill="#334155">3.00</text>
                        <text x="473" y="149" text-anchor="start" font-size="10.5" font-weight="700" fill="#334155">70%</text>

                        <!-- Row: 2.50 -->
                        <line x1="52" y1="190" x2="465" y2="190" stroke="#CAD5DE" stroke-width="1" />
                        <text x="44" y="194" text-anchor="end" font-size="10.5" font-weight="700" fill="#334155">2.50</text>

                        <!-- Row: 2.20 / 60% -->
                        <line x1="52" y1="225" x2="465" y2="225" stroke="#CAD5DE" stroke-width="1" />
                        <text x="44" y="229" text-anchor="end" font-size="10.5" font-weight="700" fill="#334155">2.20</text>
                        <text x="473" y="200" text-anchor="start" font-size="10.5" font-weight="700" fill="#334155">60%</text>

                        <!-- Baseline: 2.10 / 50% -->
                        <line x1="52" y1="250" x2="465" y2="250" stroke="#94A3B8" stroke-width="1.5" />
                        <text x="44" y="254" text-anchor="end" font-size="10.5" font-weight="700" fill="#334155">2.10</text>
                        <text x="473" y="254" text-anchor="start" font-size="10.5" font-weight="700" fill="#334155">50%</text>

                        <!-- Ticks on Baseline for years -->
                        @foreach ($years as $idx => $year)
                            @php $x = 90 + ($idx * 90); @endphp
                            <line x1="{{ $x }}" y1="243" x2="{{ $x }}" y2="257" stroke="#94A3B8" stroke-width="1.5" />
                        @endforeach

                        <!-- IPK Dynamic Line (Navy #183A5F) -->
                        <path d="{{ $ipkPath }}" fill="none" stroke="#183A5F" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />

                        <!-- Kelulusan Dynamic Line (Green #52A776) -->
                        <path d="{{ $kelulusanPath }}" fill="none" stroke="#52A776" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />

                        <!-- IPK Points & Text Above -->
                        @foreach ($ipkPoints as $pt)
                            <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4.5" fill="#183A5F" />
                            <text x="{{ $pt['x'] }}" y="{{ $pt['y'] - 13 }}" text-anchor="middle" font-size="11" font-weight="800" fill="#183A5F">{{ number_format($pt['val'], 2) }}</text>
                        @endforeach

                        <!-- Kelulusan Points & Text Below -->
                        @foreach ($kelulusanPoints as $pt)
                            <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4.5" fill="#52A776" />
                            <text x="{{ $pt['x'] }}" y="{{ $pt['y'] + 18 }}" text-anchor="middle" font-size="11" font-weight="800" fill="#334155">{{ round($pt['val']) }}%</text>
                        @endforeach

                        <!-- X-Axis Years Labels -->
                        @foreach ($years as $idx => $year)
                            @php $x = 90 + ($idx * 90); @endphp
                            <text x="{{ $x }}" y="270" text-anchor="middle" font-size="11.5" font-weight="700" fill="#334155">{{ $year }}</text>
                        @endforeach
                    </svg>
                </div>
            </div>
        </div>

        <!-- Right: Prestasi Mahasiswa Terbaru -->
        <div class="bg-[#D6DEE5] rounded-2xl border border-[#CAD5DE] border-b-[3px] border-b-[#B8C6D4] p-5 sm:p-6 shadow-[0_8px_16px_-2px_rgba(15,23,42,0.12),0_3px_6px_-2px_rgba(15,23,42,0.08),inset_0_1px_0_0_rgba(255,255,255,0.7)] flex flex-col justify-between overflow-hidden">
            <div>
                <h3 class="text-base sm:text-lg font-black text-[#183A5F] tracking-tight uppercase">
                    PRESTASI MAHASISWA TERBARU
                </h3>

                <!-- Table -->
                <div class="mt-4 w-full overflow-x-auto">
                    <table class="w-full text-left text-[11px] sm:text-xs">
                        <thead>
                            <tr class="border-y border-[#CAD5DE] text-[#2D3748] font-bold whitespace-nowrap">
                                <th class="py-2.5 px-1.5 sm:px-2 font-bold w-7 text-left">No</th>
                                <th class="py-2.5 px-1.5 sm:px-2 font-bold text-left">Nama Mahasiswa</th>
                                <th class="py-2.5 px-1.5 sm:px-2 font-bold text-left">Nama Lomba</th>
                                <th class="py-2.5 px-1.5 sm:px-2 font-bold text-left">Tingkat</th>
                                <th class="py-2.5 px-1.5 sm:px-2 font-bold text-center w-12">Tahun</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#CAD5DE]">
                            @forelse($prestasiTerbaru as $item)
                                <tr class="hover:bg-white/30 transition border-b border-[#CAD5DE] whitespace-nowrap">
                                    <td class="py-2.5 px-1.5 sm:px-2 text-gray-700 font-medium">{{ $item['no'] }}.</td>
                                    <td class="py-2.5 px-1.5 sm:px-2 text-[#1E293B] font-medium">{{ $item['nama'] }}</td>
                                    <td class="py-2.5 px-1.5 sm:px-2 text-gray-700 font-medium">
                                        <div class="truncate max-w-[180px] sm:max-w-[220px] xl:max-w-[280px]" title="{{ $item['lomba'] }}">
                                            {{ $item['lomba'] }}
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-1.5 sm:px-2 text-gray-700 font-medium">
                                        {{ $item['tingkat'] }}
                                    </td>
                                    <td class="py-2.5 px-1.5 sm:px-2 text-gray-700 font-medium text-center">{{ $item['tahun'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-gray-500 font-medium">
                                        Belum ada data prestasi mahasiswa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
