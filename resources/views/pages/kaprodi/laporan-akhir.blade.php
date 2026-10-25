@extends('layouts.app')

@section('title', 'Laporan Akhir')

@section('content')
<div class="max-w-[1120px] mx-auto space-y-6 pb-14 font-['Plus_Jakarta_Sans']">

    {{-- 1. Header Section --}}
    <div class="space-y-1">
        <h2 class="text-2xl sm:text-3xl font-bold text-black tracking-tight">
            Laporan Akhir
        </h2>
        <p class="text-base sm:text-lg text-black/80 font-normal leading-7">
            Unduh atau cetak rekapitulasi self-assessment dan hasil audit mutu prodi.
        </p>
    </div>

    {{-- 2. Project Card Banner --}}
    <div class="p-5 bg-slate-50/80 rounded-md border border-zinc-200 flex flex-wrap items-end justify-between gap-4">
        <div class="w-96 max-w-full space-y-1.5">
            <label class="block text-black/60 text-xs font-bold tracking-wider uppercase">
                PROJECT
            </label>
            <div class="relative">
                <select class="w-full h-12 bg-white text-black/85 font-medium text-sm rounded-md px-4 pr-10 appearance-none border border-zinc-300 focus:outline-sky-800 shadow-xs cursor-pointer transition-colors">
                    <option value="lam-infokom-2027" selected>Akreditasi LAM INFOKOM D4 TRK 2027</option>
                    <option value="lam-infokom-2026">Akreditasi LAM INFOKOM D4 TRK 2026</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-sky-950">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="pb-1.5">
            <span class="h-8 px-3.5 bg-green-700/10 text-green-700 rounded-full flex items-center gap-2 text-xs font-semibold">
                <span class="size-1.5 bg-green-700 rounded-full"></span>
                <span>{{ $statusProject ?? 'Dinilai' }}</span>
            </span>
        </div>
    </div>

    {{-- 3. Ringkasan Kesiapan (Dark Navy Banner) --}}
    <div class="p-6 bg-sky-950 rounded-md text-white shadow-md space-y-6">
        
        {{-- Top: Score & Status --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <span class="block text-white/70 text-xs font-bold tracking-wider uppercase">
                    RINGKASAN KESIAPAN
                </span>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl sm:text-5xl font-extrabold text-white leading-none tracking-tight">
                        {{ $summaryKesiapan['skor'] ?? '3,08' }}
                    </span>
                    <span class="text-white/60 text-lg font-normal">
                        / {{ $summaryKesiapan['skor_max'] ?? '4,00' }}
                    </span>
                </div>
                <div class="pt-1">
                    <span class="h-7 px-3 bg-yellow-400 text-sky-950 text-xs font-bold rounded-full inline-flex items-center">
                        {{ $summaryKesiapan['status'] ?? 'Mendekati target' }}
                    </span>
                </div>
            </div>

            {{-- Progress: Kelengkapan Borang --}}
            <div class="w-full sm:w-64 space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-white/70 font-normal">Kelengkapan borang</span>
                    <span class="text-white font-bold">{{ $summaryKesiapan['kelengkapan_borang'] ?? 61 }}%</span>
                </div>
                <div class="w-full h-2.5 bg-white/20 rounded-full overflow-hidden">
                    <div class="h-2.5 bg-yellow-400 rounded-full transition-all duration-500 ease-out" style="width: {{ $summaryKesiapan['kelengkapan_borang'] ?? 61 }}%"></div>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-white/10 pt-5">
            {{-- Metrics Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <p class="text-3xl font-bold text-white leading-tight">
                        {{ $summaryKesiapan['indikator_tercapai'] ?? 14 }}
                    </p>
                    <p class="text-white/70 text-xs font-normal mt-1">
                        Indikator tercapai
                    </p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-white leading-tight">
                        {{ $summaryKesiapan['di_bawah_target'] ?? 6 }}
                    </p>
                    <p class="text-white/70 text-xs font-normal mt-1">
                        Di bawah target
                    </p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-white leading-tight">
                        {{ $summaryKesiapan['temuan_terbuka'] ?? 5 }}
                    </p>
                    <p class="text-white/70 text-xs font-normal mt-1">
                        Temuan audit terbuka
                    </p>
                </div>
            </div>
        </div>

    </div>

    {{-- 4. Sticky Top Action Bar (Cetak & Unduh Laporan) --}}
    <div class="p-4 sm:px-5 sm:py-4 bg-white rounded-md border border-zinc-300 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-black/60 text-sm font-normal">
                <span id="selectedCount">4</span> bagian dipilih · format <strong id="formatBadge" class="text-black font-semibold">PDF</strong>
            </span>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button"
                onclick="window.print()"
                class="h-11 px-6 bg-zinc-300 hover:bg-zinc-400 text-black/75 text-base font-semibold rounded-md transition-colors duration-150 cursor-pointer flex items-center gap-2">
                <svg class="w-4 h-4 text-black/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.03-.42-2.11-.52-3.23C5.74 5.92 9.4 2.5 14 2.5s8.26 3.42 7.8 8.099c-.1 1.12-.28 2.2-.52 3.23M17 17v4H7v-4m10 0H7m10 0h3.5a1.5 1.5 0 001.5-1.5v-4a1.5 1.5 0 00-1.5-1.5H4a1.5 1.5 0 00-1.5 1.5v4A1.5 1.5 0 004 17h3" />
                </svg>
                <span>Cetak</span>
            </button>
            <button type="button"
                onclick="triggerDownload()"
                class="h-11 px-6 bg-sky-800 hover:bg-sky-900 text-white text-base font-semibold rounded-md transition-colors duration-150 shadow-xs hover:shadow cursor-pointer flex items-center gap-2">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Unduh laporan</span>
            </button>
        </div>
    </div>

    {{-- 5. Split Panel: Pilih Isi Laporan (Kiri) & Pratinjau Dokumen (Kanan) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- Panel Kiri: Form Pilihan & Format (5 / 12) --}}
        <div class="lg:col-span-5 bg-white rounded-md border border-zinc-300 p-5 space-y-6 shadow-xs">
            
            {{-- Bagian 1: Pilih Isi Laporan --}}
            <div class="space-y-3">
                <h3 class="text-black text-lg font-bold leading-7">
                    Pilih isi laporan
                </h3>

                <div class="space-y-1.5">
                    
                    {{-- Checkbox 1: Ringkasan Eksekutif --}}
                    <label class="flex items-center gap-3 p-2 rounded-md hover:bg-slate-50 cursor-pointer select-none transition-colors">
                        <input type="checkbox"
                            class="report-section-checkbox size-4 text-sky-800 rounded border-zinc-300 focus:ring-sky-800 cursor-pointer"
                            data-target="previewRingkasan"
                            checked
                            onchange="updateSectionToggle(this)">
                        <span class="text-sm text-black font-normal leading-5">Ringkasan eksekutif</span>
                    </label>

                    {{-- Checkbox 2: Rekap Skor per Kriteria --}}
                    <label class="flex items-center gap-3 p-2 rounded-md hover:bg-slate-50 cursor-pointer select-none transition-colors">
                        <input type="checkbox"
                            class="report-section-checkbox size-4 text-sky-800 rounded border-zinc-300 focus:ring-sky-800 cursor-pointer"
                            data-target="previewRekap"
                            checked
                            onchange="updateSectionToggle(this)">
                        <span class="text-sm text-black font-normal leading-5">Rekap skor per kriteria</span>
                    </label>

                    {{-- Checkbox 3: Gap Analysis --}}
                    <label class="flex items-center gap-3 p-2 rounded-md hover:bg-slate-50 cursor-pointer select-none transition-colors">
                        <input type="checkbox"
                            class="report-section-checkbox size-4 text-sky-800 rounded border-zinc-300 focus:ring-sky-800 cursor-pointer"
                            data-target="previewGap"
                            checked
                            onchange="updateSectionToggle(this)">
                        <span class="text-sm text-black font-normal leading-5">Gap analysis (indikator di bawah target)</span>
                    </label>

                    {{-- Checkbox 4: Daftar Bukti per Indikator --}}
                    <label class="flex items-center gap-3 p-2 rounded-md hover:bg-slate-50 cursor-pointer select-none transition-colors">
                        <input type="checkbox"
                            class="report-section-checkbox size-4 text-sky-800 rounded border-zinc-300 focus:ring-sky-800 cursor-pointer"
                            data-target="previewBukti"
                            onchange="updateSectionToggle(this)">
                        <span class="text-sm text-black font-normal leading-5">Daftar bukti per indikator</span>
                    </label>

                    {{-- Checkbox 5: Hasil Audit Mutu Internal (AMI) --}}
                    <label class="flex items-center gap-3 p-2 rounded-md hover:bg-slate-50 cursor-pointer select-none transition-colors">
                        <input type="checkbox"
                            class="report-section-checkbox size-4 text-sky-800 rounded border-zinc-300 focus:ring-sky-800 cursor-pointer"
                            data-target="previewAmi"
                            checked
                            onchange="updateSectionToggle(this)">
                        <span class="text-sm text-black font-normal leading-5">Hasil audit mutu internal (AMI)</span>
                    </label>

                    {{-- Checkbox 6: Tindak Lanjut dan Keputusan RTM --}}
                    <label class="flex items-center gap-3 p-2 rounded-md hover:bg-slate-50 cursor-pointer select-none transition-colors">
                        <input type="checkbox"
                            class="report-section-checkbox size-4 text-sky-800 rounded border-zinc-300 focus:ring-sky-800 cursor-pointer"
                            data-target="previewRtm"
                            onchange="updateSectionToggle(this)">
                        <span class="text-sm text-black font-normal leading-5">Tindak lanjut dan keputusan RTM</span>
                    </label>

                </div>
            </div>

            {{-- Bagian 2: Format Pilihan --}}
            <div class="space-y-2">
                <span class="block text-black/60 text-xs font-bold tracking-wider uppercase">
                    FORMAT
                </span>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button"
                        class="format-btn h-11 rounded-md text-sm font-semibold transition-all duration-150 cursor-pointer flex items-center justify-center bg-sky-800/10 text-sky-800 outline outline-2 outline-sky-800"
                        data-format="PDF"
                        onclick="setFormat('PDF', this)">
                        PDF
                    </button>
                    <button type="button"
                        class="format-btn h-11 rounded-md text-sm font-semibold transition-all duration-150 cursor-pointer flex items-center justify-center border border-zinc-300 text-black/60 hover:text-black hover:border-zinc-400"
                        data-format="Excel"
                        onclick="setFormat('Excel', this)">
                        Excel
                    </button>
                    <button type="button"
                        class="format-btn h-11 rounded-md text-sm font-semibold transition-all duration-150 cursor-pointer flex items-center justify-center border border-zinc-300 text-black/60 hover:text-black hover:border-zinc-400"
                        data-format="Word"
                        onclick="setFormat('Word', this)">
                        Word
                    </button>
                </div>
            </div>

            {{-- Bagian 3: Tahun Audit --}}
            <div class="space-y-1.5">
                <label for="filterTahunAudit" class="block text-black/60 text-xs font-bold tracking-wider uppercase">
                    TAHUN AUDIT
                </label>
                <div class="relative w-full">
                    <select id="filterTahunAudit"
                        class="w-full h-12 bg-zinc-200/90 text-black/80 font-medium text-sm rounded-md px-4 pr-10 appearance-none border border-zinc-300 focus:outline-sky-800 cursor-pointer transition-colors">
                        <option value="2026" selected>2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-sky-950">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        {{-- Panel Kanan: Pratinjau Dokumen (7 / 12) --}}
        <div class="lg:col-span-7 bg-slate-50/80 rounded-md border border-zinc-300 overflow-hidden shadow-xs flex flex-col">
            
            {{-- Toolbar Pratinjau --}}
            <div class="px-5 py-3.5 bg-white border-b border-zinc-300 flex items-center justify-between">
                <h3 class="text-black text-lg font-bold leading-7">
                    Pratinjau
                </h3>

                {{-- Zoom Controls --}}
                <div class="p-0.5 bg-white rounded-md border border-zinc-300 flex items-center gap-1 shadow-2xs">
                    <button type="button"
                        onclick="adjustZoom(-10)"
                        class="size-7 rounded-sm flex items-center justify-center text-black font-bold text-base hover:bg-slate-100 transition-colors cursor-pointer"
                        title="Perkecil">
                        −
                    </button>
                    <span id="zoomLevelDisplay" class="w-12 text-center text-xs font-semibold text-black select-none">
                        80%
                    </span>
                    <button type="button"
                        onclick="adjustZoom(10)"
                        class="size-7 rounded-sm flex items-center justify-center text-black font-bold text-base hover:bg-slate-100 transition-colors cursor-pointer"
                        title="Perbesar">
                        +
                    </button>
                </div>
            </div>

            {{-- Container Kertas Dokumen (Scrollable) --}}
            <div class="p-4 sm:p-6 overflow-x-auto max-h-[820px] overflow-y-auto flex justify-center bg-slate-100/70">
                
                {{-- Lembar Kertas Laporan (Document Sheet) --}}
                <div id="previewDocumentPaper"
                    class="w-[640px] min-h-[860px] bg-white p-8 sm:p-10 shadow-lg border border-zinc-200 space-y-6 transition-transform duration-150 origin-top select-none"
                    style="transform: scale(0.85); transform-origin: top center;">
                    
                    {{-- Header Kertas Laporan --}}
                    <div class="pb-3 border-b-2 border-sky-950 flex items-center gap-3.5">
                        <div class="size-10 bg-sky-950 rounded-sm flex items-center justify-center shrink-0">
                            <svg class="size-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zm0 3.27L18.18 9 12 11.73 5.82 9 12 6.27z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-black/60 text-[9px] font-bold tracking-wider uppercase">
                                SISTEM AKREDITASI PROGRAM STUDI · LAM INFOKOM
                            </span>
                            <h4 class="text-sky-950 text-sm font-extrabold leading-tight mt-0.5">
                                Laporan Akhir Akreditasi Prodi D4 Teknologi Rekayasa Komputer
                            </h4>
                            <span class="block text-black/60 text-[9px] font-normal mt-0.5">
                                Tahun audit 2026 · Akreditasi LAM INFOKOM D4 TRK 2027
                            </span>
                        </div>
                    </div>

                    {{-- Bagian 1 Dokumen: Ringkasan Eksekutif --}}
                    <div id="previewRingkasan" class="space-y-1.5">
                        <div class="pl-2 border-l-4 border-yellow-400">
                            <h5 class="text-sky-950 text-xs font-extrabold">Ringkasan eksekutif</h5>
                        </div>
                        <p class="text-black text-[11px] leading-relaxed pl-3">
                            Skor total self-assessment prodi adalah <strong class="font-bold text-black">3,08 dari 4,00</strong> dengan kelengkapan borang 61%. Sebanyak 6 dari 9 kriteria masih di bawah target 3,25, terutama pada kriteria Mahasiswa, SDM, dan Penelitian. Terdapat 5 temuan audit yang masih terbuka.
                        </p>
                    </div>

                    {{-- Bagian 2 Dokumen: Rekap Skor per Kriteria --}}
                    <div id="previewRekap" class="space-y-2">
                        <div class="pl-2 border-l-4 border-yellow-400">
                            <h5 class="text-sky-950 text-xs font-extrabold">Rekap skor per kriteria</h5>
                        </div>
                        <div class="pl-3 overflow-x-auto">
                            <table class="w-full text-left text-[10px] border-collapse">
                                <thead>
                                    <tr class="bg-sky-950 text-white font-bold">
                                        <th class="px-2 py-1.5 w-12">Kode</th>
                                        <th class="px-2 py-1.5">Kriteria</th>
                                        <th class="px-2 py-1.5 text-right w-14">Skor</th>
                                        <th class="px-2 py-1.5 text-right w-14">Target</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200">
                                    @foreach ($rekapKriteria as $k)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-2 py-1 text-sky-800 font-bold">{{ $k['kode'] }}</td>
                                            <td class="px-2 py-1 text-black font-normal">{{ $k['nama'] }}</td>
                                            <td class="px-2 py-1 text-right font-semibold {{ $k['tercapai'] ? 'text-black' : 'text-red-700' }}">
                                                {{ $k['skor'] }}
                                            </td>
                                            <td class="px-2 py-1 text-right text-black/60">{{ $k['target'] }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-slate-100 font-bold border-t-2 border-zinc-300">
                                        <td colspan="2" class="px-2 py-1.5 text-black">Skor total</td>
                                        <td class="px-2 py-1.5 text-right text-black">3,08</td>
                                        <td class="px-2 py-1.5 text-right text-black">3,25</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Bagian 3 Dokumen: Gap Analysis --}}
                    <div id="previewGap" class="space-y-1.5">
                        <div class="pl-2 border-l-4 border-yellow-400">
                            <h5 class="text-sky-950 text-xs font-extrabold">Gap analysis</h5>
                        </div>
                        <div class="pl-3 divide-y divide-zinc-200 text-[10px]">
                            @foreach ($gapAnalysis as $gap)
                                <div class="py-1 flex items-center justify-between">
                                    <div>
                                        <span class="font-bold text-sky-800">{{ $gap['kode'] }}</span>
                                        <span class="text-black ml-1">{{ $gap['indikator'] }}</span>
                                    </div>
                                    <span class="font-semibold text-red-700">{{ $gap['skor'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Bagian 4 Dokumen: Daftar Bukti per Indikator (Hidden by default, shown via checkbox) --}}
                    <div id="previewBukti" class="space-y-1.5 hidden">
                        <div class="pl-2 border-l-4 border-yellow-400">
                            <h5 class="text-sky-950 text-xs font-extrabold">Daftar bukti per indikator</h5>
                        </div>
                        <p class="text-black text-[11px] leading-relaxed pl-3 italic text-black/60">
                            Tercatat 28 dokumen bukti telah diunggah dan terverifikasi untuk kriteria tata pamong dan proses pembelajaran.
                        </p>
                    </div>

                    {{-- Bagian 5 Dokumen: Hasil Audit Mutu Internal (AMI) 2026 --}}
                    <div id="previewAmi" class="space-y-1.5">
                        <div class="pl-2 border-l-4 border-yellow-400">
                            <h5 class="text-sky-950 text-xs font-extrabold">Hasil audit mutu internal (AMI) 2026</h5>
                        </div>
                        <p class="text-black text-[11px] leading-relaxed pl-3">
                            6 temuan: 3 selesai, 1 dalam perbaikan, 2 menunggu tanggapan (STD-04 evaluasi kurikulum, STD-07 rasio dosen-mahasiswa).
                        </p>
                    </div>

                    {{-- Bagian 6 Dokumen: Tindak Lanjut dan Keputusan RTM (Hidden by default, shown via checkbox) --}}
                    <div id="previewRtm" class="space-y-1.5 hidden">
                        <div class="pl-2 border-l-4 border-yellow-400">
                            <h5 class="text-sky-950 text-xs font-extrabold">Tindak lanjut dan keputusan RTM</h5>
                        </div>
                        <p class="text-black text-[11px] leading-relaxed pl-3">
                            RTM tanggal 12 Juni 2026 memutuskan percepatan rekrutmen 2 dosen tetap dan pengesahan roadmap penelitian prodi 2025-2029.
                        </p>
                    </div>

                </div>
            </div>

        </div>

    </div>

</div>

{{-- Interactive JavaScript for Checkbox Toggles, Formats, and Document Zoom --}}
<script>
    let currentZoom = 85;

    // Toggle document preview sections based on checkboxes
    function updateSectionToggle(checkbox) {
        const targetId = checkbox.getAttribute('data-target');
        const sectionEl = document.getElementById(targetId);
        if (sectionEl) {
            if (checkbox.checked) {
                sectionEl.classList.remove('hidden');
            } else {
                sectionEl.classList.add('hidden');
            }
        }
        updateSelectedCount();
    }

    // Update count of selected parts in the action bar
    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('.report-section-checkbox:checked');
        const countDisplay = document.getElementById('selectedCount');
        if (countDisplay) {
            countDisplay.textContent = checkedBoxes.length;
        }
    }

    // Set export format (PDF, Excel, Word)
    function setFormat(formatName, buttonEl) {
        document.querySelectorAll('.format-btn').forEach(btn => {
            btn.className = 'format-btn h-11 rounded-md text-sm font-semibold transition-all duration-150 cursor-pointer flex items-center justify-center border border-zinc-300 text-black/60 hover:text-black hover:border-zinc-400';
        });

        buttonEl.className = 'format-btn h-11 rounded-md text-sm font-semibold transition-all duration-150 cursor-pointer flex items-center justify-center bg-sky-800/10 text-sky-800 outline outline-2 outline-sky-800';
        
        const badge = document.getElementById('formatBadge');
        if (badge) {
            badge.textContent = formatName;
        }
    }

    // Adjust document preview zoom level
    function adjustZoom(delta) {
        currentZoom = Math.min(120, Math.max(50, currentZoom + delta));
        const paper = document.getElementById('previewDocumentPaper');
        const display = document.getElementById('zoomLevelDisplay');
        if (paper) {
            paper.style.transform = `scale(${currentZoom / 100})`;
        }
        if (display) {
            display.textContent = `${currentZoom}%`;
        }
    }

    // Trigger download simulation with notification
    function triggerDownload() {
        const format = document.getElementById('formatBadge').textContent || 'PDF';
        alert(`Sedang menyiapkan berkas Laporan Akhir Akreditasi dalam format ${format}...`);
    }
</script>
@endsection
