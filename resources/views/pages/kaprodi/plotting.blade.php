@extends('layouts.app')

@section('title', 'Plotting Tim Akreditasi')

@section('content')
<div class="space-y-6 pb-10">
    
    <!-- Toast Notification Component -->
    <x-toast />

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary tracking-tight leading-tight">Plotting Tim Akreditasi</h1>
            <p class="text-xs sm:text-sm text-[#5A6E85] mt-1">Bagikan tanggung jawab pengisian setiap indikator borang kepada Dosen atau Operator prodi.</p>
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

    <!-- Main Container Card -->
    <div class="bg-white rounded-2xl border border-[#EAEAEA] shadow-xs flex flex-col overflow-hidden">
        
        <!-- Top Section: Project Selector & Progress -->
        <div class="bg-gray-50/70 p-5 sm:p-6 border-b border-[#EAEAEA] flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="w-full md:w-1/2 flex flex-col gap-1.5">
                <label for="projectSelector" class="text-[11px] font-bold text-gray-500 tracking-wider uppercase">Pilih Project Akreditasi</label>
                <div class="max-w-md">
                    <x-input 
                        type="select" 
                        name="project_id" 
                        id="projectSelector"
                        class="text-xs font-semibold"
                    >
                        @if(!empty($projects))
                            @foreach($projects as $p)
                                <option value="{{ $p['id'] }}">
                                    {{ $p['nama_project'] ?? 'Project Akreditasi' }} ({{ $p['tahun_target_submit'] ?? 2026 }})
                                </option>
                            @endforeach
                        @else
                            <option value="1">Akreditasi LAM INFOKOM D4 TRK 2027</option>
                        @endif
                    </x-input>
                </div>
            </div>
            
            <div class="w-full md:w-[320px] flex flex-col gap-2">
                <div class="flex justify-between items-center text-xs font-medium">
                    <span class="text-gray-600">Progres Penugasan PIC</span>
                    <span id="pic-ratio-text" class="font-extrabold text-primary">6 dari 8 indikator</span>
                </div>
                <div class="w-full h-2.5 bg-white border border-[#EAEAEA] rounded-full overflow-hidden p-0.5">
                    <div id="pic-progress-bar" class="h-full bg-additional rounded-full transition-all duration-300" style="width: 75%"></div>
                </div>
                <span class="text-[11px] text-gray-400">Satu indikator borang hanya boleh memiliki satu PIC utama.</span>
            </div>
        </div>

        <!-- Sticky Action Bar -->
        <div class="px-5 sm:px-6 py-3.5 bg-white border-b border-[#EAEAEA] flex flex-col sm:flex-row justify-between items-center gap-4 sticky top-0 z-20">
            <p id="action-hint" class="text-xs sm:text-sm text-gray-500 font-medium">
                Pilih checkbox indikator di kiri dan pilih anggota tim di kanan untuk menetapkan PIC.
            </p>
            <div class="flex items-center gap-3 shrink-0">
                <button 
                    type="button" 
                    id="btn-hapus" 
                    class="px-5 py-2.5 bg-slate-100 text-gray-400 text-xs font-bold rounded-xl cursor-not-allowed transition-colors select-none" 
                    disabled
                >
                    Hapus Pilihan
                </button>
                <button 
                    type="button" 
                    id="btn-tetapkan" 
                    class="px-5 py-2.5 bg-slate-100 text-gray-400 text-xs font-bold rounded-xl cursor-not-allowed transition-colors select-none" 
                    disabled
                >
                    Tetapkan PIC
                </button>
            </div>
        </div>

        <!-- Split Layout (Indikator & Anggota Tim) -->
        <div class="flex flex-col lg:flex-row min-h-[540px]">
            
            <!-- LEFT PANEL: Daftar Indikator -->
            <div class="w-full lg:w-[60%] border-r border-[#EAEAEA] flex flex-col">
                <!-- Header Panel Indikator & Filter Tabs -->
                <div class="p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-[#EAEAEA] bg-white">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-extrabold text-primary tracking-tight">Daftar Indikator</h2>
                        <span id="selected-counter" class="text-xs px-2 py-0.5 bg-sky-50 text-additional font-bold rounded-full hidden">0 dipilih</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 select-none">
                        <button type="button" onclick="setFilter('semua')" id="filter-semua" class="filter-tab px-3.5 py-1.5 bg-white text-gray-700 hover:bg-gray-100 hover:text-gray-900 text-xs font-bold rounded-lg border border-[#EAEAEA] transition-colors cursor-pointer">
                            Semua
                        </button>
                        <button type="button" onclick="setFilter('belum')" id="filter-belum" class="filter-tab px-3.5 py-1.5 bg-white text-gray-700 hover:bg-gray-100 hover:text-gray-900 text-xs font-bold rounded-lg border border-[#EAEAEA] transition-colors cursor-pointer">
                            Belum ada PIC
                        </button>
                        <button type="button" onclick="setFilter('sudah')" id="filter-sudah" class="filter-tab px-3.5 py-1.5 bg-primary text-white border-primary hover:bg-additional hover:text-white text-xs font-bold rounded-lg border transition-colors cursor-pointer">
                            Sudah ada PIC
                        </button>
                    </div>
                </div>

                <!-- List Indikator Groups -->
                <div id="indicator-container" class="flex flex-col p-3 divide-y divide-gray-100">
                    
                    <!-- K1 Group -->
                    <div class="indicator-group py-2" data-kriteria="K1">
                        <label class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-xl cursor-pointer transition-colors group select-none">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" onchange="toggleGroupCheck(this, 'K1')" class="group-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                <span class="text-sm font-extrabold text-primary group-hover:text-additional transition-colors">K1 · Visi, Misi, Tujuan dan Strategi</span>
                            </div>
                            <span class="text-xs text-gray-400 font-semibold">1 indikator</span>
                        </label>

                        <div class="flex flex-col pl-7 pr-2 space-y-1 mt-1">
                            <!-- K1.1 -->
                            <div class="indicator-item flex items-center justify-between p-2.5 hover:bg-slate-50 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all" data-has-pic="true" data-pic-name="Dr. Rina Kusuma" data-kriteria="K1">
                                <label class="flex items-center gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                    <span class="text-xs font-bold text-additional shrink-0">K1.1</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium">Kejelasan visi, misi, dan ketercapaian strategi prodi</span>
                                </label>
                                <div class="pic-badge-wrapper shrink-0">
                                    <div class="px-2.5 py-1 bg-emerald-50 border border-emerald-200 rounded-full flex items-center gap-1.5 shadow-2xs">
                                        <span class="text-xs font-bold text-emerald-700 pic-label">Dr. Rina</span>
                                        <button type="button" onclick="removePic(this)" class="text-emerald-700 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer" title="Lepas PIC">×</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- K2 Group -->
                    <div class="indicator-group py-2" data-kriteria="K2">
                        <label class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-xl cursor-pointer transition-colors group select-none">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" onchange="toggleGroupCheck(this, 'K2')" class="group-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                <span class="text-sm font-extrabold text-primary group-hover:text-additional transition-colors">K2 · Tata Pamong, Tata Kelola, dan Kerja Sama</span>
                            </div>
                            <span class="text-xs text-gray-400 font-semibold">3 indikator</span>
                        </label>

                        <div class="flex flex-col pl-7 pr-2 space-y-1 mt-1">
                            <!-- K2.1 -->
                            <div class="indicator-item flex items-center justify-between p-2.5 hover:bg-slate-50 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all" data-has-pic="true" data-pic-name="Budi Santoso M.Kom." data-kriteria="K2">
                                <label class="flex items-center gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                    <span class="text-xs font-bold text-additional shrink-0">K2.1</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium">Sistem tata pamong dan efektivitas kepemimpinan</span>
                                </label>
                                <div class="pic-badge-wrapper shrink-0">
                                    <div class="px-2.5 py-1 bg-emerald-50 border border-emerald-200 rounded-full flex items-center gap-1.5 shadow-2xs">
                                        <span class="text-xs font-bold text-emerald-700 pic-label">Budi</span>
                                        <button type="button" onclick="removePic(this)" class="text-emerald-700 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer" title="Lepas PIC">×</button>
                                    </div>
                                </div>
                            </div>

                            <!-- K2.2 -->
                            <div class="indicator-item flex items-center justify-between p-2.5 hover:bg-slate-50 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all" data-has-pic="false" data-pic-name="" data-kriteria="K2">
                                <label class="flex items-center gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                    <span class="text-xs font-bold text-additional shrink-0">K2.2</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium">Siklus penjaminan mutu internal (SPMI)</span>
                                </label>
                                <div class="pic-badge-wrapper shrink-0">
                                    <span class="text-xs text-gray-400 italic">Belum ada</span>
                                </div>
                            </div>

                            <!-- K2.3 -->
                            <div class="indicator-item flex items-center justify-between p-2.5 hover:bg-slate-50 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all" data-has-pic="true" data-pic-name="Sari Wulandari" data-kriteria="K2">
                                <label class="flex items-center gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                    <span class="text-xs font-bold text-additional shrink-0">K2.3</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium">Kepuasan dan keberlanjutan kerja sama mitra</span>
                                </label>
                                <div class="pic-badge-wrapper shrink-0">
                                    <div class="px-2.5 py-1 bg-emerald-50 border border-emerald-200 rounded-full flex items-center gap-1.5 shadow-2xs">
                                        <span class="text-xs font-bold text-emerald-700 pic-label">Sari</span>
                                        <button type="button" onclick="removePic(this)" class="text-emerald-700 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer" title="Lepas PIC">×</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- K3 Group -->
                    <div class="indicator-group py-2" data-kriteria="K3">
                        <label class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-xl cursor-pointer transition-colors group select-none">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" onchange="toggleGroupCheck(this, 'K3')" class="group-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                <span class="text-sm font-extrabold text-primary group-hover:text-additional transition-colors">K3 · Mahasiswa</span>
                            </div>
                            <span class="text-xs text-gray-400 font-semibold">4 indikator</span>
                        </label>

                        <div class="flex flex-col pl-7 pr-2 space-y-1 mt-1">
                            <!-- K3.1 -->
                            <div class="indicator-item flex items-center justify-between p-2.5 hover:bg-slate-50 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all" data-has-pic="true" data-pic-name="Andi Pratama" data-kriteria="K3">
                                <label class="flex items-center gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                    <span class="text-xs font-bold text-additional shrink-0">K3.1</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium">Kualitas input dan rasio pendaftar mahasiswa baru</span>
                                </label>
                                <div class="pic-badge-wrapper shrink-0">
                                    <div class="px-2.5 py-1 bg-emerald-50 border border-emerald-200 rounded-full flex items-center gap-1.5 shadow-2xs">
                                        <span class="text-xs font-bold text-emerald-700 pic-label">Andi</span>
                                        <button type="button" onclick="removePic(this)" class="text-emerald-700 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer" title="Lepas PIC">×</button>
                                    </div>
                                </div>
                            </div>

                            <!-- K3.2 -->
                            <div class="indicator-item flex items-center justify-between p-2.5 hover:bg-slate-50 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all" data-has-pic="true" data-pic-name="Budi Santoso M.Kom." data-kriteria="K3">
                                <label class="flex items-center gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                    <span class="text-xs font-bold text-additional shrink-0">K3.2</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium">Layanan kemahasiswaan dan bimbingan karir</span>
                                </label>
                                <div class="pic-badge-wrapper shrink-0">
                                    <div class="px-2.5 py-1 bg-emerald-50 border border-emerald-200 rounded-full flex items-center gap-1.5 shadow-2xs">
                                        <span class="text-xs font-bold text-emerald-700 pic-label">Budi</span>
                                        <button type="button" onclick="removePic(this)" class="text-emerald-700 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer" title="Lepas PIC">×</button>
                                    </div>
                                </div>
                            </div>

                            <!-- K3.3 -->
                            <div class="indicator-item flex items-center justify-between p-2.5 hover:bg-slate-50 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all" data-has-pic="false" data-pic-name="" data-kriteria="K3">
                                <label class="flex items-center gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                    <span class="text-xs font-bold text-additional shrink-0">K3.3</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium">Prestasi akademik dan non-akademik mahasiswa</span>
                                </label>
                                <div class="pic-badge-wrapper shrink-0">
                                    <span class="text-xs text-gray-400 italic">Belum ada</span>
                                </div>
                            </div>

                            <!-- K3.4 -->
                            <div class="indicator-item flex items-center justify-between p-2.5 hover:bg-slate-50 rounded-xl border border-transparent hover:border-[#EAEAEA] transition-all" data-has-pic="true" data-pic-name="Dr. Rina Kusuma" data-kriteria="K3">
                                <label class="flex items-center gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" class="ind-checkbox w-4 h-4 rounded text-primary focus:ring-primary/20 border-gray-300 cursor-pointer">
                                    <span class="text-xs font-bold text-additional shrink-0">K3.4</span>
                                    <span class="text-xs sm:text-sm text-gray-700 font-medium">Masa tunggu lulusan dan kesesuaian bidang kerja</span>
                                </label>
                                <div class="pic-badge-wrapper shrink-0">
                                    <div class="px-2.5 py-1 bg-emerald-50 border border-emerald-200 rounded-full flex items-center gap-1.5 shadow-2xs">
                                        <span class="text-xs font-bold text-emerald-700 pic-label">Dr. Rina</span>
                                        <button type="button" onclick="removePic(this)" class="text-emerald-700 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer" title="Lepas PIC">×</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RIGHT PANEL: Anggota Tim -->
            <div class="w-full lg:w-[40%] flex flex-col bg-white">
                <div class="p-5 border-b border-[#EAEAEA]">
                    <h2 class="text-base font-extrabold text-primary tracking-tight">Anggota Tim Akreditasi</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Dosen dan Operator yang terdaftar pada prodi ini</p>
                </div>

                <!-- List Anggota -->
                <div class="flex-1 p-5 flex flex-col gap-3 overflow-y-auto bg-gray-50/40">
                    
                    <!-- Card Anggota 1 -->
                    <label class="block cursor-pointer">
                        <input type="radio" name="anggota_pic" value="Dr. Rina Kusuma" data-short-name="Dr. Rina" class="peer sr-only">
                        <div class="p-4 bg-white border border-[#EAEAEA] rounded-2xl flex flex-col gap-2 hover:border-additional hover:shadow-xs peer-checked:border-additional peer-checked:ring-2 peer-checked:ring-additional/20 peer-checked:bg-sky-50/20 transition-all">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-sm font-bold text-primary leading-tight">Dr. Rina Kusuma</h3>
                                    <p class="text-xs text-gray-500 font-medium mt-0.5">Dosen · <span class="member-count font-bold text-primary">2</span> indikator</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary">Dosen</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mt-1">
                                <div class="h-full bg-additional rounded-full" style="width: 50%"></div>
                            </div>
                        </div>
                    </label>

                    <!-- Card Anggota 2 -->
                    <label class="block cursor-pointer">
                        <input type="radio" name="anggota_pic" value="Budi Santoso M.Kom." data-short-name="Budi" class="peer sr-only">
                        <div class="p-4 bg-white border border-[#EAEAEA] rounded-2xl flex flex-col gap-2 hover:border-additional hover:shadow-xs peer-checked:border-additional peer-checked:ring-2 peer-checked:ring-additional/20 peer-checked:bg-sky-50/20 transition-all">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-sm font-bold text-primary leading-tight">Budi Santoso M.Kom.</h3>
                                    <p class="text-xs text-gray-500 font-medium mt-0.5">Dosen · <span class="member-count font-bold text-primary">2</span> indikator</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary">Dosen</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mt-1">
                                <div class="h-full bg-additional rounded-full" style="width: 50%"></div>
                            </div>
                        </div>
                    </label>

                    <!-- Card Anggota 3 -->
                    <label class="block cursor-pointer">
                        <input type="radio" name="anggota_pic" value="Sari Wulandari" data-short-name="Sari" class="peer sr-only">
                        <div class="p-4 bg-white border border-[#EAEAEA] rounded-2xl flex flex-col gap-2 hover:border-additional hover:shadow-xs peer-checked:border-additional peer-checked:ring-2 peer-checked:ring-additional/20 peer-checked:bg-sky-50/20 transition-all">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-sm font-bold text-primary leading-tight">Sari Wulandari</h3>
                                    <p class="text-xs text-gray-500 font-medium mt-0.5">Operator · <span class="member-count font-bold text-primary">1</span> indikator</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Operator</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mt-1">
                                <div class="h-full bg-additional rounded-full" style="width: 25%"></div>
                            </div>
                        </div>
                    </label>

                    <!-- Card Anggota 4 -->
                    <label class="block cursor-pointer">
                        <input type="radio" name="anggota_pic" value="Andi Pratama" data-short-name="Andi" class="peer sr-only">
                        <div class="p-4 bg-white border border-[#EAEAEA] rounded-2xl flex flex-col gap-2 hover:border-additional hover:shadow-xs peer-checked:border-additional peer-checked:ring-2 peer-checked:ring-additional/20 peer-checked:bg-sky-50/20 transition-all">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-sm font-bold text-primary leading-tight">Andi Pratama</h3>
                                    <p class="text-xs text-gray-500 font-medium mt-0.5">Operator · <span class="member-count font-bold text-primary">1</span> indikator</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Operator</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mt-1">
                                <div class="h-full bg-additional rounded-full" style="width: 25%"></div>
                            </div>
                        </div>
                    </label>

                </div>

                <!-- Deadline Section -->
                <div class="p-5 border-t border-[#EAEAEA] bg-white">
                    <label for="inputDeadline" class="block text-[11px] font-bold text-gray-500 tracking-wider uppercase mb-1.5">TENGGAT PENGISIAN BORANG</label>
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
        const checkboxes = document.querySelectorAll('.ind-checkbox');
        const radios = document.querySelectorAll('input[name="anggota_pic"]');
        const btnTetapkan = document.getElementById('btn-tetapkan');
        const btnHapus = document.getElementById('btn-hapus');
        const selectedCounter = document.getElementById('selected-counter');
        const actionHint = document.getElementById('action-hint');

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
                btnTetapkan.disabled = false;
                btnTetapkan.classList.remove('bg-slate-200', 'text-white', 'cursor-not-allowed');
                btnTetapkan.classList.add('bg-primary', 'text-white', 'hover:bg-additional', 'cursor-pointer', 'shadow-sm');
                actionHint.innerHTML = `<span class="text-primary font-bold">Siap:</span> Tetapkan <span class="font-bold text-additional">${selectedRadio.value}</span> sebagai PIC untuk ${checkedBoxes.length} indikator.`;
            } else {
                btnTetapkan.disabled = true;
                btnTetapkan.classList.add('bg-slate-200', 'text-white', 'cursor-not-allowed');
                btnTetapkan.classList.remove('bg-primary', 'hover:bg-additional', 'cursor-pointer', 'shadow-sm');
                actionHint.innerText = 'Pilih checkbox indikator di kiri dan pilih anggota tim di kanan untuk menetapkan PIC.';
            }
        }

        checkboxes.forEach(cb => cb.addEventListener('change', checkFormState));
        radios.forEach(radio => radio.addEventListener('change', checkFormState));
        
        btnHapus.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            document.querySelectorAll('.group-checkbox').forEach(gcb => gcb.checked = false);
            radios.forEach(radio => radio.checked = false);
            checkFormState();
        });

        // Event listener saat tombol Tetapkan diklik
        btnTetapkan.addEventListener('click', function() {
            const selectedRadio = Array.from(radios).find(r => r.checked);
            if (!selectedRadio) return;

            const shortName = selectedRadio.getAttribute('data-short-name') || selectedRadio.value.split(' ')[0];
            const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);

            checkedBoxes.forEach(cb => {
                const item = cb.closest('.indicator-item');
                if (item) {
                    item.setAttribute('data-has-pic', 'true');
                    item.setAttribute('data-pic-name', selectedRadio.value);

                    const badgeWrapper = item.querySelector('.pic-badge-wrapper');
                    if (badgeWrapper) {
                        badgeWrapper.innerHTML = `
                            <div class="px-2.5 py-1 bg-emerald-50 border border-emerald-200 rounded-full flex items-center gap-1.5 shadow-2xs">
                                <span class="text-xs font-bold text-emerald-700 pic-label">${shortName}</span>
                                <button type="button" onclick="removePic(this)" class="text-emerald-700 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer" title="Lepas PIC">×</button>
                            </div>
                        `;
                    }
                    cb.checked = false;
                }
            });

            document.querySelectorAll('.group-checkbox').forEach(gcb => gcb.checked = false);
            updateOverallProgress();
            checkFormState();
        });
    });

    // Checkbox select all per group
    function toggleGroupCheck(groupCheckbox, kriteriaCode) {
        const group = groupCheckbox.closest('.indicator-group');
        if (!group) return;
        const indCheckboxes = group.querySelectorAll('.ind-checkbox');
        indCheckboxes.forEach(cb => {
            // Hanya check yang terlihat (tidak tersembunyi karena filter)
            if (cb.closest('.indicator-item').style.display !== 'none') {
                cb.checked = groupCheckbox.checked;
            }
        });
        const event = new Event('change');
        if (indCheckboxes.length > 0) {
            indCheckboxes[0].dispatchEvent(event);
        }
    }

    // Lepas PIC dari indikator
    function removePic(button) {
        const item = button.closest('.indicator-item');
        if (item) {
            item.setAttribute('data-has-pic', 'false');
            item.setAttribute('data-pic-name', '');
            const badgeWrapper = item.querySelector('.pic-badge-wrapper');
            if (badgeWrapper) {
                badgeWrapper.innerHTML = `<span class="text-xs text-gray-400 italic">Belum ada</span>`;
            }
            updateOverallProgress();
        }
    }

    // Kelas aktif vs inaktif untuk tab filter
    const TAB_ACTIVE = ['bg-primary', 'text-white', 'border-primary', 'hover:bg-additional', 'hover:text-white'];
    const TAB_INACTIVE = ['bg-white', 'text-gray-700', 'border-[#EAEAEA]', 'hover:bg-gray-100', 'hover:text-gray-900'];

    // Filter Tab (Semua, Belum ada PIC, Sudah ada PIC)
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
            if (filterType === 'semua') {
                item.style.display = 'flex';
            } else if (filterType === 'belum') {
                item.style.display = hasPic ? 'none' : 'flex';
            } else if (filterType === 'sudah') {
                item.style.display = hasPic ? 'flex' : 'none';
            }
        });
    }

    // Inisialisasi filter awal saat halaman dimuat
    setFilter('sudah');

    // Update rasio progress bar atas
    function updateOverallProgress() {
        const allItems = document.querySelectorAll('.indicator-item');
        const withPicItems = document.querySelectorAll('.indicator-item[data-has-pic="true"]');
        const total = allItems.length;
        const count = withPicItems.length;

        const ratioText = document.getElementById('pic-ratio-text');
        if (ratioText) {
            ratioText.innerText = `${count} dari ${total} indikator`;
        }

        const progressBar = document.getElementById('pic-progress-bar');
        if (progressBar && total > 0) {
            const pct = Math.round((count / total) * 100);
            progressBar.style.width = pct + '%';
        }
    }
</script>
@endsection