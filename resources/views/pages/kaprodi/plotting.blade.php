@extends('layouts.app')

@section('title', 'Plotting Tim Akreditasi')

@section('content')
@php
    $totalIndikator = 8;
    $assignedPjCount = count(array_filter($assignments ?? [], fn($a) => !empty($a['pj'])));
    $assignedPicCount = count(array_filter($assignments ?? [], fn($a) => !empty($a['pic'])));
    $pjPercent = $totalIndikator > 0 ? round(($assignedPjCount / $totalIndikator) * 100) : 0;
    $picPercent = $totalIndikator > 0 ? round(($assignedPicCount / $totalIndikator) * 100) : 0;

    $getMemberStats = function($memberName) use ($assignments) {
        $pj = 0;
        $pic = 0;
        foreach ($assignments ?? [] as $a) {
            if (($a['pj'] ?? null) === $memberName) $pj++;
            if (($a['pic'] ?? null) === $memberName) $pic++;
        }
        return ['pj' => $pj, 'pic' => $pic, 'total' => $pj + $pic];
    };

    $rinaStats = $getMemberStats('Dr. Rina Kusuma');
    $budiStats = $getMemberStats('Budi Santoso M.Kom.');
    $sariStats = $getMemberStats('Sari Wulandari');
    $andiStats = $getMemberStats('Andi Pratama');

    $getShortName = function($fullName) {
        if (!$fullName) return '';
        if (str_starts_with($fullName, 'Dr. Rina')) return 'Dr. Rina';
        if (str_starts_with($fullName, 'Budi')) return 'Budi';
        if (str_starts_with($fullName, 'Sari')) return 'Sari';
        if (str_starts_with($fullName, 'Andi')) return 'Andi';
        return explode(' ', $fullName)[0];
    };
@endphp

<div class="space-y-6 pb-12">
    
    <!-- Toast Notification Component -->
    <x-toast />

    <!-- 1. Header Halaman -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary tracking-tight leading-tight">Plotting Tim Akreditasi</h1>
            <p class="text-xs sm:text-sm text-[#5A6E85] mt-1">
                Tetapkan Penanggung Jawab (PJ) dan Pelaksana (PIC) untuk setiap indikator borang akreditasi program studi.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a 
                href="{{ route('kaprodi.project') }}" 
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-colors cursor-pointer"
            >
                &larr; Daftar Project
            </a>
        </div>
    </div>

    <!-- 2. Project Card Banner & Selector -->
    <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 sm:p-6 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        
        <!-- Dropdown Selector -->
        <div class="w-full lg:w-96 max-w-full space-y-1.5">
            <label for="projectSelector" class="block text-gray-500 text-[11px] font-bold tracking-wider uppercase">
                PILIH PROJECT AKREDITASI
            </label>
            <div class="relative">
                <select id="projectSelector" name="project_id"
                    class="w-full h-11 bg-gray-50/80 hover:bg-gray-100/80 text-primary font-bold text-xs sm:text-sm rounded-xl px-4 pr-10 appearance-none border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-xs cursor-pointer transition-colors">
                    @if(!empty($projects))
                        @foreach($projects as $p)
                            <option value="{{ $p['id'] }}" {{ (string)request('project_id', $projectId ?? $projects[0]['id'] ?? '') === (string)($p['id'] ?? '') ? 'selected' : '' }}>
                                {{ $p['nama_project'] ?? 'Project Akreditasi' }} ({{ $p['tahun_target_submit'] ?? 2026 }})
                            </option>
                        @endforeach
                    @else
                        <option value="1">Akreditasi LAM INFOKOM D4 TRK 2027</option>
                        <option value="2">Akreditasi LAM INFOKOM D4 TRK 2026</option>
                    @endif
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Progres Statistik Penugasan (PJ & PIC) -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-6">
            
            <!-- Progress PJ (Penanggung Jawab) -->
            <div class="flex flex-col gap-1.5 min-w-[160px]">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-primary inline-block"></span>
                        Penugasan PJ
                    </span>
                    <span id="pj-ratio-text" class="font-extrabold text-primary">{{ $assignedPjCount }} dari {{ $totalIndikator }}</span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div id="pj-progress-bar" class="h-full bg-primary rounded-full transition-all duration-300" style="width: {{ $pjPercent }}%"></div>
                </div>
                <span class="text-[10px] text-gray-400">Supervisor / Kriteria Lead</span>
            </div>

            <!-- Progress PIC (Pelaksana) -->
            <div class="flex flex-col gap-1.5 min-w-[160px]">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                        Penugasan PIC
                    </span>
                    <span id="pic-ratio-text" class="font-extrabold text-emerald-700">{{ $assignedPicCount }} dari {{ $totalIndikator }}</span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div id="pic-progress-bar" class="h-full bg-emerald-500 rounded-full transition-all duration-300" style="width: {{ $picPercent }}%"></div>
                </div>
                <span class="text-[10px] text-gray-400">Pengisi Borang &amp; Bukti</span>
            </div>

            <!-- Status Badge -->
            <div class="flex items-center self-start sm:self-center">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-2xs">
                    <span class="size-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span>Aktif</span>
                </span>
            </div>
        </div>
    </div>

    <!-- 3. Main Container Card (Split Layout) -->
    <div class="bg-white rounded-2xl border border-[#EAEAEA] shadow-xs flex flex-col overflow-hidden">
        
        <!-- Sticky Action Bar -->
        <div class="px-5 sm:px-6 py-3.5 bg-white border-b border-[#EAEAEA] flex flex-col sm:flex-row justify-between items-center gap-4 sticky top-0 z-20 shadow-2xs">
            <div class="flex items-center gap-3">
                <span id="selected-counter" class="text-xs px-2.5 py-1 bg-sky-50 text-additional font-extrabold rounded-full border border-sky-100 hidden">
                    0 dipilih
                </span>
                <p id="action-hint" class="text-xs sm:text-sm text-gray-500 font-medium">
                    Pilih checkbox indikator di kiri dan pilih anggota tim di kanan untuk menetapkan <strong class="text-primary">PJ</strong> atau <strong class="text-emerald-700">PIC</strong>.
                </p>
            </div>
            
            <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                <!-- Tombol Hapus Pilihan -->
                <button 
                    type="button" 
                    id="btn-hapus" 
                    class="px-4 py-2 bg-slate-100 text-gray-400 text-xs font-bold rounded-xl cursor-not-allowed transition-colors select-none" 
                    disabled
                >
                    Reset Pilihan
                </button>

                <!-- Tombol Tetapkan sebagai PIC -->
                <button 
                    type="button" 
                    id="btn-tetapkan-pic" 
                    class="px-4 py-2 bg-slate-200 text-white text-xs font-bold rounded-xl cursor-not-allowed transition-all select-none inline-flex items-center gap-1.5" 
                    disabled
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tetapkan PIC
                </button>

                <!-- Tombol Tetapkan sebagai PJ -->
                <button 
                    type="button" 
                    id="btn-tetapkan-pj" 
                    class="px-4 py-2 bg-slate-200 text-white text-xs font-bold rounded-xl cursor-not-allowed transition-all select-none inline-flex items-center gap-1.5" 
                    disabled
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    Tetapkan PJ
                </button>
            </div>
        </div>

        <!-- Split Layout (Indikator & Anggota Tim) -->
        <div class="flex flex-col lg:flex-row min-h-[560px]">
            
            <!-- LEFT PANEL: Daftar Indikator -->
            <div class="w-full lg:w-[62%] border-r border-[#EAEAEA] flex flex-col">
                
                <!-- Header Panel Indikator & Filter Tabs -->
                <div class="p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-[#EAEAEA] bg-white">
                    <div>
                        <h2 class="text-base font-extrabold text-primary tracking-tight">Daftar Indikator Borang</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Setiap indikator memiliki PJ (Penanggung Jawab) dan PIC (Pelaksana)</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 select-none">
                        <button type="button" onclick="setFilter('semua')" id="filter-semua" class="filter-tab px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg border border-primary transition-colors cursor-pointer">
                            Semua
                        </button>
                        <button type="button" onclick="setFilter('belum-pic')" id="filter-belum-pic" class="filter-tab px-3 py-1.5 bg-white text-gray-700 hover:bg-gray-100 text-xs font-bold rounded-lg border border-[#EAEAEA] transition-colors cursor-pointer">
                            Belum ada PIC
                        </button>
                        <button type="button" onclick="setFilter('belum-pj')" id="filter-belum-pj" class="filter-tab px-3 py-1.5 bg-white text-gray-700 hover:bg-gray-100 text-xs font-bold rounded-lg border border-[#EAEAEA] transition-colors cursor-pointer">
                            Belum ada PJ
                        </button>
                        <button type="button" onclick="setFilter('lengkap')" id="filter-lengkap" class="filter-tab px-3 py-1.5 bg-white text-gray-700 hover:bg-gray-100 text-xs font-bold rounded-lg border border-[#EAEAEA] transition-colors cursor-pointer">
                            Lengkap (PJ &amp; PIC)
                        </button>
                    </div>
                </div>

                <!-- List Indikator Groups -->
                <div id="indicator-container" class="flex flex-col p-3.5 divide-y divide-gray-100 flex-1 overflow-y-auto">
                    
                    <!-- ==================== K1 Group ==================== -->
                    <div class="indicator-group py-2.5" data-kriteria="K1">
                        <label class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-xl cursor-pointer transition-colors group select-none">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" onchange="toggleGroupCheck(this, 'K1')" class="group-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                <span class="text-sm font-extrabold text-primary group-hover:text-additional transition-colors">K1 · Visi, Misi, Tujuan dan Strategi</span>
                            </div>
                            <span class="text-xs text-gray-400 font-semibold">1 indikator</span>
                        </label>

                        <div class="flex flex-col pl-7 pr-2 space-y-1.5 mt-1">
                            <!-- K1.1 -->
                            @php
                                $k11_pj = $assignments['K1.1']['pj'] ?? null;
                                $k11_pic = $assignments['K1.1']['pic'] ?? null;
                            @endphp
                            <div class="indicator-item flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-slate-50/80 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all gap-3" 
                                data-has-pic="{{ $k11_pic ? 'true' : 'false' }}" data-pic-name="{{ $k11_pic ?? '' }}" 
                                data-has-pj="{{ $k11_pj ? 'true' : 'false' }}" data-pj-name="{{ $k11_pj ?? '' }}" 
                                data-kriteria="K1" data-code="K1.1">
                                <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                    <input type="checkbox" value="K1.1" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer shrink-0">
                                    <span class="text-xs font-extrabold text-additional shrink-0">K1.1</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium truncate">Kejelasan visi, misi, dan ketercapaian strategi prodi</span>
                                </label>
                                
                                <div class="indicator-badges-wrapper flex items-center gap-2 shrink-0">
                                    @if($k11_pj)
                                        <div class="pj-badge px-2.5 py-1 bg-indigo-50 border border-indigo-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-indigo-500">PJ:</span>
                                            <span class="text-xs font-bold text-indigo-800 pj-label">{{ $getShortName($k11_pj) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pj', 'K1.1')" class="text-indigo-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PJ">×</button>
                                        </div>
                                    @else
                                        <span class="pj-empty text-[11px] text-gray-400 font-medium italic">PJ: Belum ada</span>
                                    @endif

                                    @if($k11_pic)
                                        <div class="pic-badge px-2.5 py-1 bg-emerald-50 border border-emerald-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-emerald-600">PIC:</span>
                                            <span class="text-xs font-bold text-emerald-800 pic-label">{{ $getShortName($k11_pic) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pic', 'K1.1')" class="text-emerald-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PIC">×</button>
                                        </div>
                                    @else
                                        <span class="pic-empty text-[11px] text-gray-400 font-medium italic">PIC: Belum ada</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== K2 Group ==================== -->
                    <div class="indicator-group py-2.5" data-kriteria="K2">
                        <label class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-xl cursor-pointer transition-colors group select-none">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" onchange="toggleGroupCheck(this, 'K2')" class="group-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                <span class="text-sm font-extrabold text-primary group-hover:text-additional transition-colors">K2 · Tata Pamong, Tata Kelola, dan Kerja Sama</span>
                            </div>
                            <span class="text-xs text-gray-400 font-semibold">3 indikator</span>
                        </label>

                        <div class="flex flex-col pl-7 pr-2 space-y-1.5 mt-1">
                            <!-- K2.1 -->
                            @php
                                $k21_pj = $assignments['K2.1']['pj'] ?? null;
                                $k21_pic = $assignments['K2.1']['pic'] ?? null;
                            @endphp
                            <div class="indicator-item flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-slate-50/80 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all gap-3" 
                                data-has-pic="{{ $k21_pic ? 'true' : 'false' }}" data-pic-name="{{ $k21_pic ?? '' }}" 
                                data-has-pj="{{ $k21_pj ? 'true' : 'false' }}" data-pj-name="{{ $k21_pj ?? '' }}" 
                                data-kriteria="K2" data-code="K2.1">
                                <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                    <input type="checkbox" value="K2.1" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer shrink-0">
                                    <span class="text-xs font-extrabold text-additional shrink-0">K2.1</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium truncate">Sistem tata pamong dan efektivitas kepemimpinan</span>
                                </label>
                                
                                <div class="indicator-badges-wrapper flex items-center gap-2 shrink-0">
                                    @if($k21_pj)
                                        <div class="pj-badge px-2.5 py-1 bg-indigo-50 border border-indigo-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-indigo-500">PJ:</span>
                                            <span class="text-xs font-bold text-indigo-800 pj-label">{{ $getShortName($k21_pj) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pj', 'K2.1')" class="text-indigo-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PJ">×</button>
                                        </div>
                                    @else
                                        <span class="pj-empty text-[11px] text-gray-400 font-medium italic">PJ: Belum ada</span>
                                    @endif

                                    @if($k21_pic)
                                        <div class="pic-badge px-2.5 py-1 bg-emerald-50 border border-emerald-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-emerald-600">PIC:</span>
                                            <span class="text-xs font-bold text-emerald-800 pic-label">{{ $getShortName($k21_pic) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pic', 'K2.1')" class="text-emerald-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PIC">×</button>
                                        </div>
                                    @else
                                        <span class="pic-empty text-[11px] text-gray-400 font-medium italic">PIC: Belum ada</span>
                                    @endif
                                </div>
                            </div>

                            <!-- K2.2 -->
                            @php
                                $k22_pj = $assignments['K2.2']['pj'] ?? null;
                                $k22_pic = $assignments['K2.2']['pic'] ?? null;
                            @endphp
                            <div class="indicator-item flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-slate-50/80 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all gap-3" 
                                data-has-pic="{{ $k22_pic ? 'true' : 'false' }}" data-pic-name="{{ $k22_pic ?? '' }}" 
                                data-has-pj="{{ $k22_pj ? 'true' : 'false' }}" data-pj-name="{{ $k22_pj ?? '' }}" 
                                data-kriteria="K2" data-code="K2.2">
                                <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                    <input type="checkbox" value="K2.2" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer shrink-0">
                                    <span class="text-xs font-extrabold text-additional shrink-0">K2.2</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium truncate">Siklus penjaminan mutu internal (SPMI)</span>
                                </label>
                                
                                <div class="indicator-badges-wrapper flex items-center gap-2 shrink-0">
                                    @if($k22_pj)
                                        <div class="pj-badge px-2.5 py-1 bg-indigo-50 border border-indigo-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-indigo-500">PJ:</span>
                                            <span class="text-xs font-bold text-indigo-800 pj-label">{{ $getShortName($k22_pj) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pj', 'K2.2')" class="text-indigo-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PJ">×</button>
                                        </div>
                                    @else
                                        <span class="pj-empty text-[11px] text-gray-400 font-medium italic">PJ: Belum ada</span>
                                    @endif

                                    @if($k22_pic)
                                        <div class="pic-badge px-2.5 py-1 bg-emerald-50 border border-emerald-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-emerald-600">PIC:</span>
                                            <span class="text-xs font-bold text-emerald-800 pic-label">{{ $getShortName($k22_pic) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pic', 'K2.2')" class="text-emerald-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PIC">×</button>
                                        </div>
                                    @else
                                        <span class="pic-empty text-[11px] text-gray-400 font-medium italic">PIC: Belum ada</span>
                                    @endif
                                </div>
                            </div>

                            <!-- K2.3 -->
                            @php
                                $k23_pj = $assignments['K2.3']['pj'] ?? null;
                                $k23_pic = $assignments['K2.3']['pic'] ?? null;
                            @endphp
                            <div class="indicator-item flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-slate-50/80 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all gap-3" 
                                data-has-pic="{{ $k23_pic ? 'true' : 'false' }}" data-pic-name="{{ $k23_pic ?? '' }}" 
                                data-has-pj="{{ $k23_pj ? 'true' : 'false' }}" data-pj-name="{{ $k23_pj ?? '' }}" 
                                data-kriteria="K2" data-code="K2.3">
                                <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                    <input type="checkbox" value="K2.3" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer shrink-0">
                                    <span class="text-xs font-extrabold text-additional shrink-0">K2.3</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium truncate">Kepuasan dan keberlanjutan kerja sama mitra</span>
                                </label>
                                
                                <div class="indicator-badges-wrapper flex items-center gap-2 shrink-0">
                                    @if($k23_pj)
                                        <div class="pj-badge px-2.5 py-1 bg-indigo-50 border border-indigo-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-indigo-500">PJ:</span>
                                            <span class="text-xs font-bold text-indigo-800 pj-label">{{ $getShortName($k23_pj) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pj', 'K2.3')" class="text-indigo-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PJ">×</button>
                                        </div>
                                    @else
                                        <span class="pj-empty text-[11px] text-gray-400 font-medium italic">PJ: Belum ada</span>
                                    @endif

                                    @if($k23_pic)
                                        <div class="pic-badge px-2.5 py-1 bg-emerald-50 border border-emerald-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-emerald-600">PIC:</span>
                                            <span class="text-xs font-bold text-emerald-800 pic-label">{{ $getShortName($k23_pic) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pic', 'K2.3')" class="text-emerald-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PIC">×</button>
                                        </div>
                                    @else
                                        <span class="pic-empty text-[11px] text-gray-400 font-medium italic">PIC: Belum ada</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== K3 Group ==================== -->
                    <div class="indicator-group py-2.5" data-kriteria="K3">
                        <label class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-xl cursor-pointer transition-colors group select-none">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" onchange="toggleGroupCheck(this, 'K3')" class="group-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                <span class="text-sm font-extrabold text-primary group-hover:text-additional transition-colors">K3 · Mahasiswa</span>
                            </div>
                            <span class="text-xs text-gray-400 font-semibold">4 indikator</span>
                        </label>

                        <div class="flex flex-col pl-7 pr-2 space-y-1.5 mt-1">
                            <!-- K3.1 -->
                            @php
                                $k31_pj = $assignments['K3.1']['pj'] ?? null;
                                $k31_pic = $assignments['K3.1']['pic'] ?? null;
                            @endphp
                            <div class="indicator-item flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-slate-50/80 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all gap-3" 
                                data-has-pic="{{ $k31_pic ? 'true' : 'false' }}" data-pic-name="{{ $k31_pic ?? '' }}" 
                                data-has-pj="{{ $k31_pj ? 'true' : 'false' }}" data-pj-name="{{ $k31_pj ?? '' }}" 
                                data-kriteria="K3" data-code="K3.1">
                                <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                    <input type="checkbox" value="K3.1" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer shrink-0">
                                    <span class="text-xs font-extrabold text-additional shrink-0">K3.1</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium truncate">Kualitas input dan rasio pendaftar mahasiswa baru</span>
                                </label>
                                
                                <div class="indicator-badges-wrapper flex items-center gap-2 shrink-0">
                                    @if($k31_pj)
                                        <div class="pj-badge px-2.5 py-1 bg-indigo-50 border border-indigo-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-indigo-500">PJ:</span>
                                            <span class="text-xs font-bold text-indigo-800 pj-label">{{ $getShortName($k31_pj) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pj', 'K3.1')" class="text-indigo-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PJ">×</button>
                                        </div>
                                    @else
                                        <span class="pj-empty text-[11px] text-gray-400 font-medium italic">PJ: Belum ada</span>
                                    @endif

                                    @if($k31_pic)
                                        <div class="pic-badge px-2.5 py-1 bg-emerald-50 border border-emerald-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-emerald-600">PIC:</span>
                                            <span class="text-xs font-bold text-emerald-800 pic-label">{{ $getShortName($k31_pic) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pic', 'K3.1')" class="text-emerald-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PIC">×</button>
                                        </div>
                                    @else
                                        <span class="pic-empty text-[11px] text-gray-400 font-medium italic">PIC: Belum ada</span>
                                    @endif
                                </div>
                            </div>

                            <!-- K3.2 -->
                            @php
                                $k32_pj = $assignments['K3.2']['pj'] ?? null;
                                $k32_pic = $assignments['K3.2']['pic'] ?? null;
                            @endphp
                            <div class="indicator-item flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-slate-50/80 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all gap-3" 
                                data-has-pic="{{ $k32_pic ? 'true' : 'false' }}" data-pic-name="{{ $k32_pic ?? '' }}" 
                                data-has-pj="{{ $k32_pj ? 'true' : 'false' }}" data-pj-name="{{ $k32_pj ?? '' }}" 
                                data-kriteria="K3" data-code="K3.2">
                                <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                    <input type="checkbox" value="K3.2" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer shrink-0">
                                    <span class="text-xs font-extrabold text-additional shrink-0">K3.2</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium truncate">Layanan kemahasiswaan dan bimbingan karir</span>
                                </label>
                                
                                <div class="indicator-badges-wrapper flex items-center gap-2 shrink-0">
                                    @if($k32_pj)
                                        <div class="pj-badge px-2.5 py-1 bg-indigo-50 border border-indigo-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-indigo-500">PJ:</span>
                                            <span class="text-xs font-bold text-indigo-800 pj-label">{{ $getShortName($k32_pj) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pj', 'K3.2')" class="text-indigo-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PJ">×</button>
                                        </div>
                                    @else
                                        <span class="pj-empty text-[11px] text-gray-400 font-medium italic">PJ: Belum ada</span>
                                    @endif

                                    @if($k32_pic)
                                        <div class="pic-badge px-2.5 py-1 bg-emerald-50 border border-emerald-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-emerald-600">PIC:</span>
                                            <span class="text-xs font-bold text-emerald-800 pic-label">{{ $getShortName($k32_pic) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pic', 'K3.2')" class="text-emerald-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PIC">×</button>
                                        </div>
                                    @else
                                        <span class="pic-empty text-[11px] text-gray-400 font-medium italic">PIC: Belum ada</span>
                                    @endif
                                </div>
                            </div>

                            <!-- K3.3 -->
                            @php
                                $k33_pj = $assignments['K3.3']['pj'] ?? null;
                                $k33_pic = $assignments['K3.3']['pic'] ?? null;
                            @endphp
                            <div class="indicator-item flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-slate-50/80 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all gap-3" 
                                data-has-pic="{{ $k33_pic ? 'true' : 'false' }}" data-pic-name="{{ $k33_pic ?? '' }}" 
                                data-has-pj="{{ $k33_pj ? 'true' : 'false' }}" data-pj-name="{{ $k33_pj ?? '' }}" 
                                data-kriteria="K3" data-code="K3.3">
                                <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                    <input type="checkbox" value="K3.3" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer shrink-0">
                                    <span class="text-xs font-extrabold text-additional shrink-0">K3.3</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium truncate">Prestasi akademik dan non-akademik mahasiswa</span>
                                </label>
                                
                                <div class="indicator-badges-wrapper flex items-center gap-2 shrink-0">
                                    @if($k33_pj)
                                        <div class="pj-badge px-2.5 py-1 bg-indigo-50 border border-indigo-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-indigo-500">PJ:</span>
                                            <span class="text-xs font-bold text-indigo-800 pj-label">{{ $getShortName($k33_pj) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pj', 'K3.3')" class="text-indigo-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PJ">×</button>
                                        </div>
                                    @else
                                        <span class="pj-empty text-[11px] text-gray-400 font-medium italic">PJ: Belum ada</span>
                                    @endif

                                    @if($k33_pic)
                                        <div class="pic-badge px-2.5 py-1 bg-emerald-50 border border-emerald-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-emerald-600">PIC:</span>
                                            <span class="text-xs font-bold text-emerald-800 pic-label">{{ $getShortName($k33_pic) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pic', 'K3.3')" class="text-emerald-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PIC">×</button>
                                        </div>
                                    @else
                                        <span class="pic-empty text-[11px] text-gray-400 font-medium italic">PIC: Belum ada</span>
                                    @endif
                                </div>
                            </div>

                            <!-- K3.4 -->
                            @php
                                $k34_pj = $assignments['K3.4']['pj'] ?? null;
                                $k34_pic = $assignments['K3.4']['pic'] ?? null;
                            @endphp
                            <div class="indicator-item flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-slate-50/80 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all gap-3" 
                                data-has-pic="{{ $k34_pic ? 'true' : 'false' }}" data-pic-name="{{ $k34_pic ?? '' }}" 
                                data-has-pj="{{ $k34_pj ? 'true' : 'false' }}" data-pj-name="{{ $k34_pj ?? '' }}" 
                                data-kriteria="K3" data-code="K3.4">
                                <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                    <input type="checkbox" value="K3.4" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer shrink-0">
                                    <span class="text-xs font-extrabold text-additional shrink-0">K3.4</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium truncate">Masa tunggu lulusan dan kesesuaian bidang kerja</span>
                                </label>
                                
                                <div class="indicator-badges-wrapper flex items-center gap-2 shrink-0">
                                    @if($k34_pj)
                                        <div class="pj-badge px-2.5 py-1 bg-indigo-50 border border-indigo-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-indigo-500">PJ:</span>
                                            <span class="text-xs font-bold text-indigo-800 pj-label">{{ $getShortName($k34_pj) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pj', 'K3.4')" class="text-indigo-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PJ">×</button>
                                        </div>
                                    @else
                                        <span class="pj-empty text-[11px] text-gray-400 font-medium italic">PJ: Belum ada</span>
                                    @endif

                                    @if($k34_pic)
                                        <div class="pic-badge px-2.5 py-1 bg-emerald-50 border border-emerald-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-emerald-600">PIC:</span>
                                            <span class="text-xs font-bold text-emerald-800 pic-label">{{ $getShortName($k34_pic) }}</span>
                                            <button type="button" onclick="removeRole(this, 'pic', 'K3.4')" class="text-emerald-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PIC">×</button>
                                        </div>
                                    @else
                                        <span class="pic-empty text-[11px] text-gray-400 font-medium italic">PIC: Belum ada</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RIGHT PANEL: Anggota Tim Akreditasi -->
            <div class="w-full lg:w-[38%] flex flex-col bg-white">
                <div class="p-5 border-b border-[#EAEAEA]">
                    <h2 class="text-base font-extrabold text-primary tracking-tight">Anggota Tim Akreditasi</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Pilih anggota untuk ditugaskan sebagai PJ atau PIC</p>
                </div>

                <!-- Info Panduan Peran -->
                <div class="px-5 pt-4 pb-1">
                    <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl flex flex-col gap-1.5 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-primary shrink-0"></span>
                            <span class="text-gray-700"><strong>PJ (Penanggung Jawab):</strong> Bertanggung jawab atas substansi kriteria dan kelengkapan dokumen.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="text-gray-700"><strong>PIC (Pelaksana):</strong> Mengisi narasi borang dan mengunggah dokumen bukti.</span>
                        </div>
                    </div>
                </div>

                <!-- List Anggota -->
                <div class="flex-1 p-5 flex flex-col gap-3 overflow-y-auto max-h-[480px] bg-gray-50/30">
                    
                    <!-- Anggota 1 -->
                    <label class="relative block cursor-pointer select-none">
                        <input type="radio" name="anggota_pic" value="Dr. Rina Kusuma" data-short-name="Dr. Rina" class="hidden peer">
                        <div class="p-4 bg-white border border-[#EAEAEA] rounded-2xl flex flex-col gap-2 hover:border-additional hover:shadow-xs peer-checked:border-additional peer-checked:ring-2 peer-checked:ring-additional/20 peer-checked:bg-sky-50/20 transition-all">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-sm font-bold text-primary leading-tight">Dr. Rina Kusuma</h3>
                                    <p class="text-xs text-gray-500 font-medium mt-1">
                                        Dosen · 
                                        <span class="member-pj-badge font-bold text-indigo-700">{{ $rinaStats['pj'] }} PJ</span> · 
                                        <span class="member-pic-badge font-bold text-emerald-700">{{ $rinaStats['pic'] }} PIC</span>
                                    </p>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary">Dosen</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mt-1">
                                <div class="member-bar h-full bg-additional rounded-full" style="width: {{ min(100, max(15, $rinaStats['total'] * 20)) }}%"></div>
                            </div>
                        </div>
                    </label>

                    <!-- Anggota 2 -->
                    <label class="relative block cursor-pointer select-none">
                        <input type="radio" name="anggota_pic" value="Budi Santoso M.Kom." data-short-name="Budi" class="hidden peer">
                        <div class="p-4 bg-white border border-[#EAEAEA] rounded-2xl flex flex-col gap-2 hover:border-additional hover:shadow-xs peer-checked:border-additional peer-checked:ring-2 peer-checked:ring-additional/20 peer-checked:bg-sky-50/20 transition-all">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-sm font-bold text-primary leading-tight">Budi Santoso M.Kom.</h3>
                                    <p class="text-xs text-gray-500 font-medium mt-1">
                                        Dosen · 
                                        <span class="member-pj-badge font-bold text-indigo-700">{{ $budiStats['pj'] }} PJ</span> · 
                                        <span class="member-pic-badge font-bold text-emerald-700">{{ $budiStats['pic'] }} PIC</span>
                                    </p>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary">Dosen</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mt-1">
                                <div class="member-bar h-full bg-additional rounded-full" style="width: {{ min(100, max(15, $budiStats['total'] * 20)) }}%"></div>
                            </div>
                        </div>
                    </label>

                    <!-- Anggota 3 -->
                    <label class="relative block cursor-pointer select-none">
                        <input type="radio" name="anggota_pic" value="Sari Wulandari" data-short-name="Sari" class="hidden peer">
                        <div class="p-4 bg-white border border-[#EAEAEA] rounded-2xl flex flex-col gap-2 hover:border-additional hover:shadow-xs peer-checked:border-additional peer-checked:ring-2 peer-checked:ring-additional/20 peer-checked:bg-sky-50/20 transition-all">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-sm font-bold text-primary leading-tight">Sari Wulandari</h3>
                                    <p class="text-xs text-gray-500 font-medium mt-1">
                                        Operator · 
                                        <span class="member-pj-badge font-bold text-indigo-700">{{ $sariStats['pj'] }} PJ</span> · 
                                        <span class="member-pic-badge font-bold text-emerald-700">{{ $sariStats['pic'] }} PIC</span>
                                    </p>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Operator</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mt-1">
                                <div class="member-bar h-full bg-additional rounded-full" style="width: {{ min(100, max(15, $sariStats['total'] * 20)) }}%"></div>
                            </div>
                        </div>
                    </label>

                    <!-- Anggota 4 -->
                    <label class="relative block cursor-pointer select-none">
                        <input type="radio" name="anggota_pic" value="Andi Pratama" data-short-name="Andi" class="hidden peer">
                        <div class="p-4 bg-white border border-[#EAEAEA] rounded-2xl flex flex-col gap-2 hover:border-additional hover:shadow-xs peer-checked:border-additional peer-checked:ring-2 peer-checked:ring-additional/20 peer-checked:bg-sky-50/20 transition-all">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-sm font-bold text-primary leading-tight">Andi Pratama</h3>
                                    <p class="text-xs text-gray-500 font-medium mt-1">
                                        Operator · 
                                        <span class="member-pj-badge font-bold text-indigo-700">{{ $andiStats['pj'] }} PJ</span> · 
                                        <span class="member-pic-badge font-bold text-emerald-700">{{ $andiStats['pic'] }} PIC</span>
                                    </p>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Operator</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mt-1">
                                <div class="member-bar h-full bg-additional rounded-full" style="width: {{ min(100, max(15, $andiStats['total'] * 20)) }}%"></div>
                            </div>
                        </div>
                    </label>

                </div>

                <!-- Deadline Section -->
                <div class="p-5 border-t border-[#EAEAEA] bg-white">
                    <label for="inputDeadline" class="block text-[11px] font-bold text-gray-500 tracking-wider uppercase mb-1.5">
                        TENGGAT PENGISIAN BORANG
                    </label>
                    <x-input 
                        type="date" 
                        name="deadline" 
                        id="inputDeadline" 
                        value="2026-11-30" 
                        class="text-xs font-semibold"
                    />
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Window scroll lock guard - ensures outer window never gets pushed up with blank void
        window.addEventListener('scroll', function() {
            if (window.scrollY !== 0 || window.scrollX !== 0) {
                window.scrollTo(0, 0);
            }
        }, { passive: true });

        const checkboxes = document.querySelectorAll('.ind-checkbox');
        const radios = document.querySelectorAll('input[name="anggota_pic"]');
        const btnTetapkanPic = document.getElementById('btn-tetapkan-pic');
        const btnTetapkanPj = document.getElementById('btn-tetapkan-pj');
        const btnHapus = document.getElementById('btn-hapus');
        const selectedCounter = document.getElementById('selected-counter');
        const actionHint = document.getElementById('action-hint');
        const projectSelector = document.getElementById('projectSelector');

        // Navigasi saat Project Akreditasi diubah
        if (projectSelector) {
            projectSelector.addEventListener('change', function() {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('project_id', this.value);
                window.location.href = currentUrl.toString();
            });
        }

        // Cek status form tombol dan counter
        function checkFormState() {
            const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
            const selectedRadio = Array.from(radios).find(r => r.checked);

            if (checkedBoxes.length > 0) {
                btnHapus.disabled = false;
                btnHapus.classList.remove('bg-slate-100', 'text-gray-400', 'cursor-not-allowed');
                btnHapus.classList.add('bg-red-50', 'text-red-600', 'hover:bg-red-100', 'cursor-pointer');

                selectedCounter.classList.remove('hidden');
                selectedCounter.innerText = `${checkedBoxes.length} dipilih`;
            } else {
                btnHapus.disabled = true;
                btnHapus.classList.add('bg-slate-100', 'text-gray-400', 'cursor-not-allowed');
                btnHapus.classList.remove('bg-red-50', 'text-red-600', 'hover:bg-red-100', 'cursor-pointer');

                selectedCounter.classList.add('hidden');
            }

            if (checkedBoxes.length > 0 && selectedRadio) {
                // Enable PIC Button
                btnTetapkanPic.disabled = false;
                btnTetapkanPic.classList.remove('bg-slate-200', 'text-white', 'cursor-not-allowed');
                btnTetapkanPic.classList.add('bg-emerald-600', 'text-white', 'hover:bg-emerald-700', 'cursor-pointer', 'shadow-xs');

                // Enable PJ Button
                btnTetapkanPj.disabled = false;
                btnTetapkanPj.classList.remove('bg-slate-200', 'text-white', 'cursor-not-allowed');
                btnTetapkanPj.classList.add('bg-primary', 'text-white', 'hover:bg-additional', 'cursor-pointer', 'shadow-xs');

                actionHint.innerHTML = `<span class="text-primary font-bold">Siap:</span> Tetapkan <span class="font-bold text-additional">${selectedRadio.value}</span> sebagai <strong>PJ</strong> atau <strong>PIC</strong> untuk ${checkedBoxes.length} indikator terpilih.`;
            } else if (checkedBoxes.length > 0 && !selectedRadio) {
                btnTetapkanPic.disabled = true;
                btnTetapkanPic.classList.add('bg-slate-200', 'text-white', 'cursor-not-allowed');
                btnTetapkanPic.classList.remove('bg-emerald-600', 'hover:bg-emerald-700', 'cursor-pointer', 'shadow-xs');

                btnTetapkanPj.disabled = true;
                btnTetapkanPj.classList.add('bg-slate-200', 'text-white', 'cursor-not-allowed');
                btnTetapkanPj.classList.remove('bg-primary', 'hover:bg-additional', 'cursor-pointer', 'shadow-xs');

                actionHint.innerHTML = `Pilih anggota tim di sebelah kanan untuk menetapkan <strong>PJ</strong> atau <strong>PIC</strong> bagi ${checkedBoxes.length} indikator terpilih.`;
            } else {
                btnTetapkanPic.disabled = true;
                btnTetapkanPic.classList.add('bg-slate-200', 'text-white', 'cursor-not-allowed');
                btnTetapkanPic.classList.remove('bg-emerald-600', 'hover:bg-emerald-700', 'cursor-pointer', 'shadow-xs');

                btnTetapkanPj.disabled = true;
                btnTetapkanPj.classList.add('bg-slate-200', 'text-white', 'cursor-not-allowed');
                btnTetapkanPj.classList.remove('bg-primary', 'hover:bg-additional', 'cursor-pointer', 'shadow-xs');

                actionHint.innerHTML = `Pilih checkbox indikator di kiri dan pilih anggota tim di kanan untuk menetapkan <strong class="text-primary">PJ</strong> atau <strong class="text-emerald-700">PIC</strong>.`;
            }
        }

        // Event listener saat checkbox indikator berubah
        checkboxes.forEach(cb => {
            cb.addEventListener('change', checkFormState);
        });

        // Event listener saat radio anggota dipilih
        radios.forEach(r => {
            r.addEventListener('change', checkFormState);
            const parentLabel = r.closest('label');
            if (parentLabel) {
                parentLabel.addEventListener('click', function() {
                    r.checked = true;
                    checkFormState();
                });
            }
        });

        // Handler Reset Pilihan
        btnHapus.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            document.querySelectorAll('.group-checkbox').forEach(gcb => gcb.checked = false);
            radios.forEach(r => r.checked = false);
            checkFormState();
        });

        // Handler Tetapkan PIC
        btnTetapkanPic.addEventListener('click', function() {
            assignRole('pic');
        });

        // Handler Tetapkan PJ
        btnTetapkanPj.addEventListener('click', function() {
            assignRole('pj');
        });

        function assignRole(role) {
            const selectedRadio = Array.from(radios).find(r => r.checked);
            if (!selectedRadio) return;

            const fullName = selectedRadio.value;
            const shortName = selectedRadio.getAttribute('data-short-name') || fullName.split(' ')[0];
            const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);

            if (checkedBoxes.length === 0) return;

            const assignmentsPayload = [];

            checkedBoxes.forEach(cb => {
                const item = cb.closest('.indicator-item');
                if (!item) return;

                const code = item.getAttribute('data-code') || cb.value;
                assignmentsPayload.push({
                    code: code,
                    user_name: fullName
                });

                const badgesWrapper = item.querySelector('.indicator-badges-wrapper');
                if (!badgesWrapper) return;

                if (role === 'pic') {
                    item.setAttribute('data-has-pic', 'true');
                    item.setAttribute('data-pic-name', fullName);

                    const existingPic = badgesWrapper.querySelector('.pic-badge');
                    const emptyPic = badgesWrapper.querySelector('.pic-empty');

                    const newBadgeHtml = `
                        <div class="pic-badge px-2.5 py-1 bg-emerald-50 border border-emerald-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-emerald-600">PIC:</span>
                            <span class="text-xs font-bold text-emerald-800 pic-label">${shortName}</span>
                            <button type="button" onclick="removeRole(this, 'pic', '${code}')" class="text-emerald-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PIC">×</button>
                        </div>
                    `;

                    if (existingPic) {
                        existingPic.outerHTML = newBadgeHtml;
                    } else if (emptyPic) {
                        emptyPic.outerHTML = newBadgeHtml;
                    } else {
                        badgesWrapper.insertAdjacentHTML('beforeend', newBadgeHtml);
                    }
                } else if (role === 'pj') {
                    item.setAttribute('data-has-pj', 'true');
                    item.setAttribute('data-pj-name', fullName);

                    const existingPj = badgesWrapper.querySelector('.pj-badge');
                    const emptyPj = badgesWrapper.querySelector('.pj-empty');

                    const newBadgeHtml = `
                        <div class="pj-badge px-2.5 py-1 bg-indigo-50 border border-indigo-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-indigo-500">PJ:</span>
                            <span class="text-xs font-bold text-indigo-800 pj-label">${shortName}</span>
                            <button type="button" onclick="removeRole(this, 'pj', '${code}')" class="text-indigo-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PJ">×</button>
                        </div>
                    `;

                    if (existingPj) {
                        existingPj.outerHTML = newBadgeHtml;
                    } else if (emptyPj) {
                        emptyPj.outerHTML = newBadgeHtml;
                    } else {
                        badgesWrapper.insertAdjacentHTML('afterbegin', newBadgeHtml);
                    }
                }

                cb.checked = false;
            });

            document.querySelectorAll('.group-checkbox').forEach(gcb => gcb.checked = false);
            updateOverallProgress();
            updateMemberCounts();
            checkFormState();

            const roleLabel = role === 'pj' ? 'Penanggung Jawab (PJ)' : 'Pelaksana (PIC)';
            if (typeof window.showToast === 'function') {
                window.showToast(`Berhasil menetapkan ${shortName} sebagai ${roleLabel} untuk ${checkedBoxes.length} indikator.`, 'success');
            }

            // Sync ke backend API dan session
            const selectedProjectId = projectSelector ? projectSelector.value : '1';
            fetch("{{ route('kaprodi.plotting.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    project_id: selectedProjectId,
                    role: role,
                    assignments: assignmentsPayload
                })
            }).catch(err => {
                console.warn('Backend sync:', err);
            });
        }
    });

    // Checkbox select all per group
    function toggleGroupCheck(groupCheckbox, kriteriaCode) {
        const group = groupCheckbox.closest('.indicator-group');
        if (!group) return;
        const indCheckboxes = group.querySelectorAll('.ind-checkbox');
        indCheckboxes.forEach(cb => {
            const item = cb.closest('.indicator-item');
            if (item && item.style.display !== 'none') {
                cb.checked = groupCheckbox.checked;
            }
        });
        const event = new Event('change');
        if (indCheckboxes.length > 0) {
            indCheckboxes[0].dispatchEvent(event);
        }
    }

    // Lepas Peran (PJ atau PIC) dari indikator
    function removeRole(button, role, code) {
        const item = button.closest('.indicator-item');
        if (!item) return;

        const indCode = code || item.getAttribute('data-code');
        const badgesWrapper = item.querySelector('.indicator-badges-wrapper');
        if (!badgesWrapper) return;

        if (role === 'pic') {
            item.setAttribute('data-has-pic', 'false');
            item.setAttribute('data-pic-name', '');
            const badge = button.closest('.pic-badge');
            if (badge) {
                badge.outerHTML = `<span class="pic-empty text-[11px] text-gray-400 font-medium italic">PIC: Belum ada</span>`;
            }
        } else if (role === 'pj') {
            item.setAttribute('data-has-pj', 'false');
            item.setAttribute('data-pj-name', '');
            const badge = button.closest('.pj-badge');
            if (badge) {
                badge.outerHTML = `<span class="pj-empty text-[11px] text-gray-400 font-medium italic">PJ: Belum ada</span>`;
            }
        }

        updateOverallProgress();
        updateMemberCounts();

        if (typeof window.showToast === 'function') {
            window.showToast(`Penugasan ${role.toUpperCase()} berhasil dilepas.`, 'info');
        }

        // Sync pelepasan peran ke backend dan session
        const projectSelector = document.getElementById('projectSelector');
        const selectedProjectId = projectSelector ? projectSelector.value : '1';
        fetch("{{ route('kaprodi.plotting.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                project_id: selectedProjectId,
                role: role,
                assignments: [{
                    code: indCode,
                    remove: true
                }]
            })
        }).catch(err => {
            console.warn('Backend sync remove:', err);
        });
    }

    // Kelas styling tab filter
    const TAB_ACTIVE = ['bg-primary', 'text-white', 'border-primary'];
    const TAB_INACTIVE = ['bg-white', 'text-gray-700', 'border-[#EAEAEA]', 'hover:bg-gray-100'];

    // Filter Tab (Semua, Belum ada PIC, Belum ada PJ, Lengkap)
    function setFilter(filterType) {
        const tabs = document.querySelectorAll('.filter-tab');
        tabs.forEach(tab => {
            tab.classList.remove(...TAB_ACTIVE);
            tab.classList.add(...TAB_INACTIVE);
        });

        const activeTab = document.getElementById('filter-' + filterType);
        if (activeTab) {
            activeTab.classList.remove(...TAB_INACTIVE);
            activeTab.classList.add(...TAB_ACTIVE);
        }

        const items = document.querySelectorAll('.indicator-item');
        items.forEach(item => {
            const hasPic = item.getAttribute('data-has-pic') === 'true';
            const hasPj = item.getAttribute('data-has-pj') === 'true';

            if (filterType === 'semua') {
                item.style.display = 'flex';
            } else if (filterType === 'belum-pic') {
                item.style.display = !hasPic ? 'flex' : 'none';
            } else if (filterType === 'belum-pj') {
                item.style.display = !hasPj ? 'flex' : 'none';
            } else if (filterType === 'lengkap') {
                item.style.display = (hasPic && hasPj) ? 'flex' : 'none';
            }
        });
    }

    // Update rasio progress bar atas (PJ & PIC)
    function updateOverallProgress() {
        const allItems = document.querySelectorAll('.indicator-item');
        const withPicItems = document.querySelectorAll('.indicator-item[data-has-pic="true"]');
        const withPjItems = document.querySelectorAll('.indicator-item[data-has-pj="true"]');
        
        const total = allItems.length;
        const picCount = withPicItems.length;
        const pjCount = withPjItems.length;

        // Update PIC
        const picRatioText = document.getElementById('pic-ratio-text');
        if (picRatioText) picRatioText.innerText = `${picCount} dari ${total}`;
        const picProgressBar = document.getElementById('pic-progress-bar');
        if (picProgressBar && total > 0) {
            picProgressBar.style.width = Math.round((picCount / total) * 100) + '%';
        }

        // Update PJ
        const pjRatioText = document.getElementById('pj-ratio-text');
        if (pjRatioText) pjRatioText.innerText = `${pjCount} dari ${total}`;
        const pjProgressBar = document.getElementById('pj-progress-bar');
        if (pjProgressBar && total > 0) {
            pjProgressBar.style.width = Math.round((pjCount / total) * 100) + '%';
        }
    }

    // Update jumlah penugasan pada kartu anggota tim
    function updateMemberCounts() {
        const memberCards = document.querySelectorAll('input[name="anggota_pic"]');
        const allItems = document.querySelectorAll('.indicator-item');
        const total = allItems.length || 1;

        memberCards.forEach(radio => {
            const memberName = radio.value;
            const container = radio.closest('label');
            if (!container) return;

            let picCount = 0;
            let pjCount = 0;

            allItems.forEach(item => {
                if (item.getAttribute('data-pic-name') === memberName) picCount++;
                if (item.getAttribute('data-pj-name') === memberName) pjCount++;
            });

            const pjBadge = container.querySelector('.member-pj-badge');
            if (pjBadge) pjBadge.innerText = `${pjCount} PJ`;

            const picBadge = container.querySelector('.member-pic-badge');
            if (picBadge) picBadge.innerText = `${picCount} PIC`;

            const bar = container.querySelector('.member-bar');
            if (bar) {
                const combinedPct = Math.min(100, Math.round(((picCount + pjCount) / (total * 2)) * 100) * 2);
                bar.style.width = Math.max(15, combinedPct) + '%';
            }
        });
    }

    // Initial sync
    updateOverallProgress();
    updateMemberCounts();
</script>
@endsection