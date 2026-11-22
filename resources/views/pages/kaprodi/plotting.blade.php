@extends('layouts.app')

@section('title', 'Plotting Tim Akreditasi')

@section('content')
@php
    // Definisi 9 Kriteria Standar LAM-INFOKOM
    $kriteriaStructure = [
        'K1' => [
            'nama' => 'K1 · Visi, Misi, Tujuan dan Strategi',
            'indikator' => [
                ['id' => 1, 'code' => 'K1.1', 'desc' => 'Kejelasan visi, misi, tujuan, dan ketercapaian strategi pengembangan program studi TRK.', 'bobot' => 3.5],
            ],
        ],
        'K2' => [
            'nama' => 'K2 · Tata Pamong, Tata Kelola dan Kerjasama',
            'indikator' => [
                ['id' => 2, 'code' => 'K2.1', 'desc' => 'Sistem tata pamong, kepemimpinan publik, dan efektivitas pengelolaan operasional prodi.', 'bobot' => 4.0],
                ['id' => 3, 'code' => 'K2.2', 'desc' => 'Sistem penjaminan mutu internal (SPMI) dan siklus PPEPP secara berkelanjutan.', 'bobot' => 4.0],
                ['id' => 4, 'code' => 'K2.3', 'desc' => 'Efektivitas kerjasama strategis tridharma tingkat nasional maupun internasional.', 'bobot' => 3.0],
            ],
        ],
        'K3' => [
            'nama' => 'K3 · Mahasiswa',
            'indikator' => [
                ['id' => 5, 'code' => 'K3.1', 'desc' => 'Kualitas input calon mahasiswa baru, rasio pendaftar, dan daya tampung prodi.', 'bobot' => 3.0],
                ['id' => 6, 'code' => 'K3.2', 'desc' => 'Layanan kemahasiswaan (bimbingan karir, konseling, kewirausahaan, dan penalaran).', 'bobot' => 3.0],
                ['id' => 7, 'code' => 'K3.3', 'desc' => 'Prestasi mahasiswa di bidang akademik dan non-akademik tingkat nasional/internasional.', 'bobot' => 3.5],
                ['id' => 8, 'code' => 'K3.4', 'desc' => 'Masa tunggu lulusan, tingkat kepuasan pengguna lulusan, dan kesesuaian bidang kerja.', 'bobot' => 4.0],
            ],
        ],
        'K4' => [
            'nama' => 'K4 · Sumber Daya Manusia',
            'indikator' => [
                ['id' => 9, 'code' => 'K4.1', 'desc' => 'Kualifikasi akademik dan kecukupan Dosen Tetap Program Studi (DTPS).', 'bobot' => 4.0],
                ['id' => 10, 'code' => 'K4.2', 'desc' => 'Beban kerja dosen (EWMP), rasio dosen terhadap mahasiswa, dan produktivitas tridharma.', 'bobot' => 3.5],
                ['id' => 11, 'code' => 'K4.3', 'desc' => 'Kualifikasi dan kecukupan tenaga kependidikan (laboran, teknisi, dan pustakawan).', 'bobot' => 3.0],
            ],
        ],
        'K5' => [
            'nama' => 'K5 · Keuangan, Sarana dan Prasarana',
            'indikator' => [
                ['id' => 12, 'code' => 'K5.1', 'desc' => 'Kecukupan alokasi dana operasional, penelitian, pkm, dan investasi sarana prodi.', 'bobot' => 3.5],
                ['id' => 13, 'code' => 'K5.2', 'desc' => 'Kelayakan dan kecukupan laboratorium komputer, perangkat keras, dan sistem informasi.', 'bobot' => 4.0],
            ],
        ],
        'K6' => [
            'nama' => 'K6 · Pendidikan',
            'indikator' => [
                ['id' => 14, 'code' => 'K6.1', 'desc' => 'Kesesuaian kurikulum berbasis OBE (Outcome-Based Education) dan Standar KKNI.', 'bobot' => 4.0],
                ['id' => 15, 'code' => 'K6.2', 'desc' => 'Rencana pembelajaran semester (RPS), metode pembelajaran partisipatif dan proyek.', 'bobot' => 3.5],
                ['id' => 16, 'code' => 'K6.3', 'desc' => 'Sistem monitoring dan evaluasi proses pembelajaran serta penilaian mahasiswa.', 'bobot' => 3.5],
                ['id' => 17, 'code' => 'K6.4', 'desc' => 'Integrasi hasil penelitian dan pengabdian dosen ke dalam materi perkuliahan.', 'bobot' => 3.0],
            ],
        ],
        'K7' => [
            'nama' => 'K7 · Penelitian',
            'indikator' => [
                ['id' => 18, 'code' => 'K7.1', 'desc' => 'Relevansi peta jalan (roadmap) penelitian dosen dan mahasiswa dengan prodi TRK.', 'bobot' => 3.5],
                ['id' => 19, 'code' => 'K7.2', 'desc' => 'Produktivitas publikasi penelitian dosen pada jurnal terakreditasi dan bereputasi.', 'bobot' => 4.0],
            ],
        ],
        'K8' => [
            'nama' => 'K8 · Pengabdian kepada Masyarakat',
            'indikator' => [
                ['id' => 20, 'code' => 'K8.1', 'desc' => 'Peta jalan dan implementasi pengabdian kepada masyarakat (PkM) yang terarah.', 'bobot' => 3.0],
                ['id' => 21, 'code' => 'K8.2', 'desc' => 'Penerapan teknologi tepat guna dan tingkat kepuasan mitra masyarakat.', 'bobot' => 3.5],
            ],
        ],
        'K9' => [
            'nama' => 'K9 · Luaran dan Capaian Tridharma',
            'indikator' => [
                ['id' => 22, 'code' => 'K9.1', 'desc' => 'IPK rata-rata lulusan, masa studi mahasiswa, dan persentase kelulusan tepat waktu.', 'bobot' => 4.0],
                ['id' => 23, 'code' => 'K9.2', 'desc' => 'Paten, hak cipta (HKI), buku ajar, dan produk teknologi yang dihasilkan prodi.', 'bobot' => 4.0],
                ['id' => 24, 'code' => 'K9.3', 'desc' => 'Capaian pembelajaran lulusan (CPL) dan akreditasi sertifikasi kompetensi mahasiswa.', 'bobot' => 4.0],
            ],
        ],
    ];

    // Hitung total indikator
    $totalIndikator = 0;
    foreach ($kriteriaStructure as $k) {
        $totalIndikator += count($k['indikator']);
    }

    $assignedPjCount = count(array_filter($assignments ?? [], fn($a) => !empty($a['pj'])));
    $assignedPicCount = count(array_filter($assignments ?? [], fn($a) => !empty($a['pic'])));
    $pjPercent = $totalIndikator > 0 ? round(($assignedPjCount / $totalIndikator) * 100) : 0;
    $picPercent = $totalIndikator > 0 ? round(($assignedPicCount / $totalIndikator) * 100) : 0;

    $toString = function($val, $default = '-') {
        if (is_null($val)) return $default;
        if (is_string($val)) return trim($val) ?: $default;
        if (is_numeric($val)) return (string)$val;
        if (is_array($val)) {
            foreach (['nama_bidang', 'nama_jabatan', 'nama', 'name', 'label', 'keahlian'] as $k) {
                if (!empty($val[$k]) && is_string($val[$k])) return trim($val[$k]);
            }
            $first = reset($val);
            if (is_string($first)) return trim($first);
            if (is_array($first) && !empty($first['nama'])) return trim($first['nama']);
            return $default;
        }
        return (string)$val;
    };

    $getShortName = function($fullName) use ($toString) {
        $clean = trim($toString($fullName, ''));
        if (!$clean) return '';
        // Bersihkan gelar kehormatan / depan (misal Prof., Dr., Ir., Drs., Dra., Eng.)
        $nameOnly = preg_replace('/^(prof\.|dr\.|ir\.|drs\.|dra\.|eng\.)\s*/i', '', $clean);
        $nameOnly = preg_replace('/^(prof\.|dr\.|ir\.|drs\.|dra\.|eng\.)\s*/i', '', $nameOnly);
        $nameOnly = preg_replace('/^(prof\.|dr\.|ir\.|drs\.|dra\.|eng\.)\s*/i', '', $nameOnly);
        $nameOnly = trim($nameOnly);

        $parts = explode(' ', $nameOnly);
        $firstWord = $parts[0] ?? $clean;

        return ucfirst(strtolower($firstWord));
    };
@endphp

<div class="space-y-6 pb-12">
    
    <!-- Toast Notification Component -->
    <x-toast />

    <!-- 1. Header Halaman -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-extrabold text-primary tracking-tight leading-tight">Plotting Tim Akreditasi</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-primary/10 text-primary">
                    KAPRODI TRK
                </span>
            </div>
            <p class="text-xs sm:text-sm text-[#5A6E85] mt-1">
                Petakan pembagian tugas dosen antara <strong>Penanggung Jawab (PJ)</strong> kriteria dan <strong>Pelaksana (PIC)</strong> bukti dukung borang.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a 
                href="{{ route('kaprodi.project') }}" 
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-colors cursor-pointer"
            >
                &larr; Daftar Project
            </a>
            <button 
                type="button" 
                onclick="openAssignDosenModal('modal-assign-dosen')"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-additional text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Modal Batch Plotting</span>
            </button>
        </div>
    </div>

    <!-- 2. Project Card Banner & Selector -->
    <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 sm:p-6 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        
        <!-- Dropdown Selector -->
        <div class="w-full lg:w-auto max-w-lg space-y-1.5">
            <label for="projectSelector" class="block text-gray-500 text-[11px] font-bold tracking-wider uppercase">
                PILIH PROJECT AKREDITASI
            </label>
            <div class="relative w-full sm:w-auto">
                <select id="projectSelector" name="project_id"
                    class="w-full sm:w-auto min-w-[280px] sm:min-w-[340px] max-w-full sm:max-w-md h-11 bg-gray-50/80 hover:bg-gray-100/80 text-primary font-bold text-xs sm:text-sm rounded-xl px-4 pr-11 appearance-none border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-xs cursor-pointer transition-colors">
                    @if(!empty($projects))
                        @foreach($projects as $p)
                            <option value="{{ $p['id'] }}" title="{{ $p['nama_project'] ?? 'Project Akreditasi' }} ({{ $p['tahun_target_submit'] ?? 2026 }})" {{ (string)request('project_id', $projectId ?? $projects[0]['id'] ?? '') === (string)($p['id'] ?? '') ? 'selected' : '' }}>
                                {{ $p['nama_project'] ?? 'Project Akreditasi' }} ({{ $p['tahun_target_submit'] ?? 2026 }})
                            </option>
                        @endforeach
                    @else
                        <option value="1" title="Akreditasi LAM INFOKOM D4 TRK 2026">Akreditasi LAM INFOKOM D4 TRK 2026</option>
                        <option value="2" title="Akreditasi LAM INFOKOM D4 TRK 2027">Akreditasi LAM INFOKOM D4 TRK 2027</option>
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
        <div class="flex flex-wrap sm:flex-nowrap items-center gap-6 shrink-0">
            
            <!-- Progress PJ (Penanggung Jawab Kriteria) -->
            <div class="flex flex-col gap-1.5 min-w-[160px]">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-600 inline-block"></span>
                        Penugasan PJ
                    </span>
                    <span id="pj-ratio-text" class="font-extrabold text-indigo-700">{{ $assignedPjCount }} dari {{ $totalIndikator }}</span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div id="pj-progress-bar" class="h-full bg-indigo-600 rounded-full transition-all duration-300" style="width: {{ $pjPercent }}%"></div>
                </div>
                <span class="text-[10px] text-gray-400">Supervisor / Kriteria Lead</span>
            </div>

            <!-- Progress PIC (Pelaksana Pengisi Bukti) -->
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
                <span class="text-[10px] text-gray-400">Pengisi Borang &amp; Bukti Drive</span>
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
                    Pilih checkbox indikator di kiri dan pilih dosen di kanan untuk menetapkan <strong class="text-indigo-700">PJ</strong> atau <strong class="text-emerald-700">PIC</strong>.
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

                <!-- Tombol Buka Modal Batch -->
                <button 
                    type="button" 
                    id="btn-modal-batch" 
                    onclick="openAssignDosenModal('modal-assign-dosen')"
                    class="px-4 py-2 bg-white text-gray-700 hover:bg-gray-100 text-xs font-bold rounded-xl border border-gray-200 transition-colors cursor-pointer select-none inline-flex items-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <span>Penugasan Batch</span>
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

        <!-- Split Layout (Indikator & Anggota Dosen) -->
        <div class="flex flex-col lg:flex-row min-h-[620px]">
            
            <!-- LEFT PANEL: Daftar 9 Kriteria & Indikator -->
            <div class="w-full lg:w-[62%] border-r border-[#EAEAEA] flex flex-col">
                
                <!-- Header Panel Indikator & Filter Tabs -->
                <div class="p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-[#EAEAEA] bg-white">
                    <div>
                        <h2 class="text-base font-extrabold text-primary tracking-tight">Daftar Indikator 9 Kriteria LAM-INFOKOM</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Dua kolom penugasan: <strong>PJ</strong> (Penanggung Jawab Kriteria) &amp; <strong>PIC</strong> (Pelaksana Pengunggah Bukti)</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 select-none">
                        <button type="button" onclick="setFilter('semua')" id="filter-semua" class="filter-tab px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg border border-primary transition-colors cursor-pointer">
                            Semua
                        </button>
                        <button type="button" onclick="setFilter('belum-pic')" id="filter-belum-pic" class="filter-tab px-3 py-1.5 bg-white text-gray-700 hover:bg-gray-100 text-xs font-bold rounded-lg border border-[#EAEAEA] transition-colors cursor-pointer">
                            Belum PIC
                        </button>
                        <button type="button" onclick="setFilter('belum-pj')" id="filter-belum-pj" class="filter-tab px-3 py-1.5 bg-white text-gray-700 hover:bg-gray-100 text-xs font-bold rounded-lg border border-[#EAEAEA] transition-colors cursor-pointer">
                            Belum PJ
                        </button>
                        <button type="button" onclick="setFilter('lengkap')" id="filter-lengkap" class="filter-tab px-3 py-1.5 bg-white text-gray-700 hover:bg-gray-100 text-xs font-bold rounded-lg border border-[#EAEAEA] transition-colors cursor-pointer">
                            Lengkap
                        </button>
                    </div>
                </div>

                <!-- List Indikator Groups (9 Kriteria) -->
                <div id="indicator-container" class="flex flex-col p-3.5 divide-y divide-gray-100 flex-1 overflow-y-auto max-h-[720px]">
                    
                    @foreach($kriteriaStructure as $kCode => $kData)
                        <div class="indicator-group py-2.5" data-kriteria="{{ $kCode }}">
                            <!-- Group Header -->
                            <label class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-xl cursor-pointer transition-colors group select-none">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" onchange="toggleGroupCheck(this, '{{ $kCode }}')" class="group-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                    <span class="text-sm font-extrabold text-primary group-hover:text-additional transition-colors">{{ $kData['nama'] }}</span>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">{{ count($kData['indikator']) }} butir</span>
                            </label>

                            <!-- Group Items -->
                            <div class="flex flex-col pl-7 pr-2 space-y-1.5 mt-1">
                                @foreach($kData['indikator'] as $ind)
                                    @php
                                        $code = $ind['code'];
                                        $pjName = $assignments[$code]['pj'] ?? null;
                                        $picName = $assignments[$code]['pic'] ?? null;
                                    @endphp
                                    <div 
                                        class="indicator-item flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-slate-50/80 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all gap-3"
                                        data-has-pic="{{ $picName ? 'true' : 'false' }}" 
                                        data-pic-name="{{ $picName ?? '' }}" 
                                        data-has-pj="{{ $pjName ? 'true' : 'false' }}" 
                                        data-pj-name="{{ $pjName ?? '' }}" 
                                        data-kriteria="{{ $kCode }}" 
                                        data-code="{{ $code }}"
                                        data-id="{{ $ind['id'] }}"
                                    >
                                        <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                            <input type="checkbox" value="{{ $code }}" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer shrink-0">
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-extrabold text-additional shrink-0">{{ $code }}</span>
                                                    <span class="text-[10px] text-gray-400 font-semibold">Bobot: {{ $ind['bobot'] }}</span>
                                                </div>
                                                <span class="text-xs sm:text-sm text-gray-700 font-medium break-words leading-tight mt-0.5">{{ $ind['desc'] }}</span>
                                            </div>
                                        </label>
                                        
                                        <!-- Kolom Penugasan: PJ dan PIC -->
                                        <div class="indicator-badges-wrapper flex items-center gap-2 shrink-0">
                                            <!-- Badge PJ -->
                                            @if($pjName)
                                                <div class="pj-badge px-2.5 py-1 bg-indigo-50 border border-indigo-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                                    <span class="text-[10px] font-extrabold uppercase tracking-wide text-indigo-500">PJ:</span>
                                                    <span class="text-xs font-bold text-indigo-800 pj-label">{{ $getShortName($pjName) }}</span>
                                                    <button type="button" onclick="removeRole(this, 'pj', '{{ $code }}')" class="text-indigo-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PJ">×</button>
                                                </div>
                                            @else
                                                <span class="pj-empty text-[11px] text-gray-400 font-medium italic">PJ: Belum ada</span>
                                            @endif

                                            <!-- Badge PIC -->
                                            @if($picName)
                                                <div class="pic-badge px-2.5 py-1 bg-emerald-50 border border-emerald-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                                                    <span class="text-[10px] font-extrabold uppercase tracking-wide text-emerald-600">PIC:</span>
                                                    <span class="text-xs font-bold text-emerald-800 pic-label">{{ $getShortName($picName) }}</span>
                                                    <button type="button" onclick="removeRole(this, 'pic', '{{ $code }}')" class="text-emerald-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PIC">×</button>
                                                </div>
                                            @else
                                                <span class="pic-empty text-[11px] text-gray-400 font-medium italic">PIC: Belum ada</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    {{-- ═══════════════════════════════════════════════════════════
                        PLACEHOLDER MODULES: Kriteria Non-Handle (C.2 & C.5)
                        Data Terkelola Institusi / Terintegrasi UPPS Polines
                        GET /api/v1/projects/{id}/placeholder-modules
                    ═══════════════════════════════════════════════════════════ --}}
                    <div class="mt-4 mb-2 pt-3 border-t border-dashed border-amber-200/80 flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            Data Terkelola Institusi / Terintegrasi UPPS
                        </span>
                        <span class="text-[11px] text-gray-400 font-semibold">C.2 & C.5 (Read-Only)</span>
                    </div>
                    @if(!empty($placeholderModules))
                        @foreach($placeholderModules as $pm)
                        <div class="indicator-group py-2.5 opacity-80" data-kriteria="{{ $pm['kode'] ?? 'placeholder' }}">
                            <div class="flex items-center justify-between p-3 bg-amber-50/60 rounded-xl border border-dashed border-amber-200/80">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 rounded border border-amber-300 bg-amber-100 flex items-center justify-center shrink-0">
                                        <svg class="w-2.5 h-2.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="text-sm font-extrabold text-amber-800">{{ $pm['nama'] ?? $pm['kode'] ?? 'Kriteria Institusi' }}</span>
                                        <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-300/80">
                                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                </svg>
                                                Terkunci (Data Pusat Polines)
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80">
                                                Terintegrasi UPPS
                                            </span>
                                        </div>
                                        <p class="text-[10px] text-amber-600/80 mt-0.5">{{ $pm['info'] ?? ($pm['keterangan'] ?? 'Dikelola unit institusi Polines (UPPS). Data terintegrasi terpusat.') }}</p>
                                    </div>
                                </div>
                                <span class="text-xs text-amber-600 font-semibold shrink-0">{{ count($pm['indikators'] ?? []) }} butir</span>
                            </div>
                            @if(!empty($pm['indikators']))
                            <div class="flex flex-col pl-7 pr-2 space-y-1.5 mt-1">
                                @foreach($pm['indikators'] as $ind)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-amber-50/30 rounded-xl border border-dashed border-amber-200/60 gap-3 cursor-not-allowed">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div class="w-4 h-4 rounded border border-amber-200 bg-amber-50 shrink-0"></div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-extrabold text-amber-700 shrink-0">{{ $ind['kode_indikator'] ?? $ind['code'] ?? '-' }}</span>
                                                <span class="text-[10px] text-amber-500 font-semibold">Bobot: {{ $ind['bobot'] ?? '-' }}</span>
                                            </div>
                                            <span class="text-xs sm:text-sm text-amber-800/80 font-medium break-words leading-tight mt-0.5">{{ $ind['deskripsi'] ?? $ind['desc'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200/80 shrink-0">
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
                                        Dikelola Institusi
                                    </span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                    @else
                        {{-- Fallback statis jika API placeholder-modules belum tersedia --}}
                        @foreach([
                            ['kode' => 'C.2', 'nama' => 'C.2 · Tata Pamong, Tata Kelola dan Kerjasama', 'info' => 'Dikelola unit institusi Polines (UPPS). Data terintegrasi terpusat.', 'indikators' => [
                                ['code' => 'K2.1', 'bobot' => 4.0, 'desc' => 'Sistem tata pamong, kepemimpinan publik, dan efektivitas pengelolaan operasional prodi.'],
                                ['code' => 'K2.2', 'bobot' => 4.0, 'desc' => 'Sistem penjaminan mutu internal (SPMI) dan siklus PPEPP secara berkelanjutan.'],
                                ['code' => 'K2.3', 'bobot' => 3.0, 'desc' => 'Efektivitas kerjasama strategis tridharma tingkat nasional maupun internasional.'],
                            ]],
                            ['kode' => 'C.5', 'nama' => 'C.5 · Keuangan, Sarana dan Prasarana', 'info' => 'Dikelola Bagian Keuangan & Sarana Polines. Data terintegrasi dari UPPS.', 'indikators' => [
                                ['code' => 'K5.1', 'bobot' => 3.5, 'desc' => 'Kecukupan alokasi dana operasional, penelitian, pkm, dan investasi sarana prodi.'],
                                ['code' => 'K5.2', 'bobot' => 4.0, 'desc' => 'Kelayakan dan kecukupan laboratorium komputer, perangkat keras, dan sistem informasi.'],
                            ]],
                        ] as $pm)
                        <div class="indicator-group py-2.5 opacity-80" data-kriteria="{{ $pm['kode'] }}">
                            <div class="flex items-center justify-between p-3 bg-amber-50/60 rounded-xl border border-dashed border-amber-200/80">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 rounded border border-amber-300 bg-amber-100 flex items-center justify-center shrink-0">
                                        <svg class="w-2.5 h-2.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="text-sm font-extrabold text-amber-800">{{ $pm['nama'] }}</span>
                                        <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-300/80">
                                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                </svg>
                                                Terkunci (Data Pusat Polines)
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80">
                                                Terintegrasi UPPS
                                            </span>
                                        </div>
                                        <p class="text-[10px] text-amber-600/80 mt-0.5">{{ $pm['info'] }}</p>
                                    </div>
                                </div>
                                <span class="text-xs text-amber-600 font-semibold shrink-0">{{ count($pm['indikators']) }} butir</span>
                            </div>
                            <div class="flex flex-col pl-7 pr-2 space-y-1.5 mt-1">
                                @foreach($pm['indikators'] as $ind)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-amber-50/30 rounded-xl border border-dashed border-amber-200/60 gap-3 cursor-not-allowed">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div class="w-4 h-4 rounded border border-amber-200 bg-amber-50 shrink-0"></div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-extrabold text-amber-700 shrink-0">{{ $ind['code'] }}</span>
                                                <span class="text-[10px] text-amber-500 font-semibold">Bobot: {{ $ind['bobot'] }}</span>
                                            </div>
                                            <span class="text-xs sm:text-sm text-amber-800/80 font-medium break-words leading-tight mt-0.5">{{ $ind['desc'] }}</span>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200/80 shrink-0">
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
                                        Dikelola Institusi
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @endif

                </div>
            </div>

            <!-- RIGHT PANEL: Daftar Dosen (Integrasi Kelompok 2 Tridharma) -->
            <div class="w-full lg:w-[38%] flex flex-col bg-white">
                <div class="p-5 border-b border-[#EAEAEA]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-extrabold text-primary tracking-tight">Anggota Tim Dosen</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary">
                            {{ count($dosenList) }} Dosen (Tridharma)
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Pilih dosen untuk ditetapkan sebagai <strong>PJ</strong> atau <strong>PIC</strong></p>

                    <!-- Pencarian Cepat Dosen (Searchable Input) -->
                    <div class="mt-3 relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="search-dosen-input" 
                            placeholder="Cari nama dosen atau NIDN..." 
                            class="w-full pl-9 pr-3.5 py-2 bg-gray-50/80 hover:bg-gray-100/50 focus:bg-white text-xs font-semibold rounded-xl border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-gray-400"
                        >
                    </div>
                </div>

                <!-- Info Panduan Peran -->
                <div class="px-5 pt-3 pb-1">
                    <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl flex flex-col gap-1.5 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                            <span class="text-gray-700"><strong>PJ (Penanggung Jawab):</strong> Bertanggung jawab atas substansi kriteria dan kelengkapan dokumen.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="text-gray-700"><strong>PIC (Pelaksana):</strong> Mengisi narasi borang dan mengunggah tautan Google Drive bukti dukung.</span>
                        </div>
                    </div>
                </div>

                <!-- List Kartu Dosen Dinamis -->
                <div id="dosen-cards-list" class="flex-1 p-5 flex flex-col gap-3 overflow-y-auto max-h-[520px] bg-gray-50/30">
                    @forelse($dosenList as $d)
                        @php
                            $dName = $toString($d['nama'] ?? 'Dosen', 'Dosen');
                            $dNidn = $toString($d['nidn'] ?? '-', '-');
                            $dKeahlian = $toString($d['bidang_keahlian'] ?? 'Teknologi Rekayasa Komputer', 'Teknologi Rekayasa Komputer');
                            $dJabatan = $toString($d['jabatan'] ?? 'Dosen', 'Dosen');
                            $dId = $d['id'] ?? $loop->iteration;
                            $dShort = $getShortName($dName);
                        @endphp
                        <label class="dosen-member-card relative block cursor-pointer select-none"
                            data-name="{{ strtolower($dName) }}"
                            data-nidn="{{ strtolower($dNidn) }}"
                            data-keahlian="{{ strtolower($dKeahlian) }}"
                        >
                            <input 
                                type="radio" 
                                name="anggota_pic" 
                                value="{{ $dName }}" 
                                data-id="{{ $dId }}"
                                data-short-name="{{ $dShort }}" 
                                class="hidden peer"
                            >
                            <div class="p-4 bg-white border border-[#EAEAEA] rounded-2xl flex flex-col gap-2 hover:border-additional hover:shadow-xs peer-checked:border-additional peer-checked:ring-2 peer-checked:ring-additional/20 peer-checked:bg-sky-50/20 transition-all">
                                <div class="flex justify-between items-start gap-2">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-sm font-bold text-primary leading-tight truncate">{{ $dName }}</h3>
                                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                                NIDN: {{ $dNidn }}
                                            </span>
                                            <span class="text-xs text-gray-500 font-medium">
                                                <span class="member-pj-badge font-bold text-indigo-700">0 PJ</span> · 
                                                <span class="member-pic-badge font-bold text-emerald-700">0 PIC</span>
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-gray-400 mt-1 truncate">{{ $dKeahlian }}</p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary shrink-0">
                                        {{ $d['jabatan'] ?? 'Dosen' }}
                                    </span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mt-1">
                                    <div class="member-bar h-full bg-additional rounded-full" style="width: 15%"></div>
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="text-center py-8 text-gray-400 text-xs">
                            Tidak ada data dosen tersedia.
                        </div>
                    @endforelse
                </div>

                <!-- Deadline Section -->
                <div class="p-5 border-t border-[#EAEAEA] bg-white">
                    <label for="inputDeadline" class="block text-[11px] font-bold text-gray-500 tracking-wider uppercase mb-1.5">
                        TENGGAT PENGISIAN BORANG AKREDITASI
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

<!-- Modal Batch Assign Dosen Component -->
<x-modal-assign-dosen id="modal-assign-dosen" :dosen-list="$dosenList" />

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.ind-checkbox');
        const radios = document.querySelectorAll('input[name="anggota_pic"]');
        const btnTetapkanPic = document.getElementById('btn-tetapkan-pic');
        const btnTetapkanPj = document.getElementById('btn-tetapkan-pj');
        const btnHapus = document.getElementById('btn-hapus');
        const selectedCounter = document.getElementById('selected-counter');
        const actionHint = document.getElementById('action-hint');
        const projectSelector = document.getElementById('projectSelector');
        const searchDosenInput = document.getElementById('search-dosen-input');

        // Pencarian Dosen di Right Panel
        if (searchDosenInput) {
            searchDosenInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.dosen-member-card');
                cards.forEach(card => {
                    const name = card.getAttribute('data-name') || '';
                    const nidn = card.getAttribute('data-nidn') || '';
                    const keahlian = card.getAttribute('data-keahlian') || '';
                    if (!query || name.includes(query) || nidn.includes(query) || keahlian.includes(query)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }

        // Menyesuaikan lebar select box agar selalu proporsional dan tidak menabrak statistik
        function adjustProjectSelectorWidth() {
            if (!projectSelector) return;
            const selectedOption = projectSelector.options[projectSelector.selectedIndex];
            const text = selectedOption ? (selectedOption.text || selectedOption.innerText) : projectSelector.value;
            if (!text) return;

            const tempSpan = document.createElement('span');
            const computed = window.getComputedStyle(projectSelector);
            tempSpan.style.position = 'absolute';
            tempSpan.style.visibility = 'hidden';
            tempSpan.style.whiteSpace = 'pre';
            tempSpan.style.fontFamily = computed.fontFamily;
            tempSpan.style.fontSize = computed.fontSize;
            tempSpan.style.fontWeight = computed.fontWeight || '700';
            tempSpan.style.letterSpacing = computed.letterSpacing;
            tempSpan.textContent = text;

            document.body.appendChild(tempSpan);
            const textWidth = Math.ceil(tempSpan.getBoundingClientRect().width);
            document.body.removeChild(tempSpan);

            const totalPadding = 64;
            const requiredWidth = textWidth + totalPadding;

            if (window.innerWidth < 640) {
                projectSelector.style.width = '100%';
            } else {
                // Batasi lebar maksimal agar tidak menabrak bar progress penugasan
                const safeWidth = Math.min(Math.max(requiredWidth, 300), 420);
                projectSelector.style.width = `${safeWidth}px`;
            }
        }

        // Navigasi saat Project Akreditasi diubah
        if (projectSelector) {
            adjustProjectSelectorWidth();
            projectSelector.addEventListener('change', function() {
                adjustProjectSelectorWidth();
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('project_id', this.value);
                window.location.href = currentUrl.toString();
            });
            window.addEventListener('resize', adjustProjectSelectorWidth);
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(adjustProjectSelectorWidth);
            }
        }

        // Cek status form tombol dan counter
        window.checkFormState = function() {
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
                btnTetapkanPj.classList.add('bg-indigo-600', 'text-white', 'hover:bg-indigo-700', 'cursor-pointer', 'shadow-xs');

                actionHint.innerHTML = `<span class="text-primary font-bold">Siap:</span> Tetapkan <span class="font-bold text-additional">${selectedRadio.value}</span> sebagai <strong>PJ</strong> atau <strong>PIC</strong> untuk ${checkedBoxes.length} butir terpilih.`;
            } else if (checkedBoxes.length > 0 && !selectedRadio) {
                btnTetapkanPic.disabled = true;
                btnTetapkanPic.classList.add('bg-slate-200', 'text-white', 'cursor-not-allowed');
                btnTetapkanPic.classList.remove('bg-emerald-600', 'hover:bg-emerald-700', 'cursor-pointer', 'shadow-xs');

                btnTetapkanPj.disabled = true;
                btnTetapkanPj.classList.add('bg-slate-200', 'text-white', 'cursor-not-allowed');
                btnTetapkanPj.classList.remove('bg-indigo-600', 'hover:bg-indigo-700', 'cursor-pointer', 'shadow-xs');

                actionHint.innerHTML = `Pilih dosen pada panel kanan untuk menetapkan <strong>PJ</strong> atau <strong>PIC</strong> bagi ${checkedBoxes.length} butir terpilih.`;
            } else {
                btnTetapkanPic.disabled = true;
                btnTetapkanPic.classList.add('bg-slate-200', 'text-white', 'cursor-not-allowed');
                btnTetapkanPic.classList.remove('bg-emerald-600', 'hover:bg-emerald-700', 'cursor-pointer', 'shadow-xs');

                btnTetapkanPj.disabled = true;
                btnTetapkanPj.classList.add('bg-slate-200', 'text-white', 'cursor-not-allowed');
                btnTetapkanPj.classList.remove('bg-indigo-600', 'hover:bg-indigo-700', 'cursor-pointer', 'shadow-xs');

                actionHint.innerHTML = `Pilih checkbox indikator di kiri dan pilih dosen di kanan untuk menetapkan <strong class="text-indigo-700">PJ</strong> atau <strong class="text-emerald-700">PIC</strong>.`;
            }
        };

        // Event listener saat checkbox indikator berubah
        checkboxes.forEach(cb => {
            cb.addEventListener('change', window.checkFormState);
        });

        // Event listener saat radio dosen dipilih
        radios.forEach(r => {
            r.addEventListener('change', window.checkFormState);
            const parentLabel = r.closest('label');
            if (parentLabel) {
                parentLabel.addEventListener('click', function() {
                    r.checked = true;
                    window.checkFormState();
                });
            }
        });

        // Handler Reset Pilihan
        btnHapus.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            document.querySelectorAll('.group-checkbox').forEach(gcb => gcb.checked = false);
            radios.forEach(r => r.checked = false);
            window.checkFormState();
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
            const userId = selectedRadio.getAttribute('data-id') || 1;
            const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);

            if (checkedBoxes.length === 0) return;

            const assignmentsPayload = [];

            checkedBoxes.forEach(cb => {
                const item = cb.closest('.indicator-item');
                if (!item) return;

                const code = item.getAttribute('data-code') || cb.value;
                const indId = parseInt(item.getAttribute('data-id') || code.replace(/\D/g, '') || 1);

                assignmentsPayload.push({
                    code: code,
                    id_indikator: indId,
                    id_user: parseInt(userId),
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

                    if (existingPic) existingPic.outerHTML = newBadgeHtml;
                    else if (emptyPic) emptyPic.outerHTML = newBadgeHtml;
                    else badgesWrapper.insertAdjacentHTML('beforeend', newBadgeHtml);
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

                    if (existingPj) existingPj.outerHTML = newBadgeHtml;
                    else if (emptyPj) emptyPj.outerHTML = newBadgeHtml;
                    else badgesWrapper.insertAdjacentHTML('afterbegin', newBadgeHtml);
                }

                cb.checked = false;
            });

            document.querySelectorAll('.group-checkbox').forEach(gcb => gcb.checked = false);
            updateOverallProgress();
            updateMemberCounts();
            window.checkFormState();

            const roleLabel = role === 'pj' ? 'Penanggung Jawab (PJ)' : 'Pelaksana (PIC)';
            if (typeof window.showToast === 'function') {
                window.showToast(`Berhasil menetapkan ${shortName} sebagai ${roleLabel} untuk ${checkedBoxes.length} butir indikator.`, 'success');
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
                console.warn('Backend sync error:', err);
            });
        }

        // Initial sync
        updateOverallProgress();
        updateMemberCounts();
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
        if (typeof window.checkFormState === 'function') {
            window.checkFormState();
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
            window.showToast(`Penugasan ${role.toUpperCase()} untuk ${indCode} berhasil dilepas.`, 'info');
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
                const picVal = item.getAttribute('data-pic-name');
                const pjVal = item.getAttribute('data-pj-name');
                if (picVal && (picVal.includes(memberName) || memberName.includes(picVal))) picCount++;
                if (pjVal && (pjVal.includes(memberName) || memberName.includes(pjVal))) pjCount++;
            });

            const pjBadge = container.querySelector('.member-pj-badge');
            if (pjBadge) pjBadge.innerText = `${pjCount} PJ`;

            const picBadge = container.querySelector('.member-pic-badge');
            if (picBadge) picBadge.innerText = `${picCount} PIC`;

            const bar = container.querySelector('.member-bar');
            if (bar) {
                const combinedPct = Math.min(100, Math.round(((picCount + pjCount) / (total * 2)) * 100) * 2);
                bar.style.width = Math.max(10, combinedPct) + '%';
            }
        });
    }
</script>
@endsection
