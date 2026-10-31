@extends('layouts.app')

@section('title', 'Laporan Akhir')

@section('content')
    <div class="space-y-6 pb-12">

        <!-- Toast Notification Component -->
        <x-toast />

        <!-- 1. Header Halaman -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-primary tracking-tight leading-tight">
                    Laporan Akhir
                </h1>
                <p class="text-xs sm:text-sm text-[#5A6E85] mt-1">
                    Unduh atau cetak rekapitulasi self-assessment dan hasil audit mutu program studi terintegrasi.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('kaprodi.project') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-colors cursor-pointer">
                    &larr; Dashboard Project
                </a>
            </div>
        </div>

        <!-- 2. Project Card Banner & Selector -->
        <div
            class="bg-white rounded-2xl border border-[#EAEAEA] p-5 sm:p-6 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="w-96 max-w-full space-y-1.5">
                <label for="projectSelector" class="block text-gray-500 text-[11px] font-bold tracking-wider uppercase">
                    PILIH PROJECT AKREDITASI
                </label>
                <div class="relative">
                    <select id="projectSelector"
                        class="w-full h-11 bg-gray-50/80 hover:bg-gray-100/80 text-primary font-bold text-xs sm:text-sm rounded-xl px-4 pr-10 appearance-none border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-xs cursor-pointer transition-colors">
                        @if (!empty($projects))
                            @foreach ($projects as $p)
                                <option value="{{ $p['id'] ?? $loop->index }}"
                                    {{ ($p['nama_project'] ?? '') === $project ? 'selected' : '' }}>
                                    {{ $p['nama_project'] ?? 'Project Akreditasi' }}
                                    ({{ $p['tahun_target_submit'] ?? 2026 }})
                                </option>
                            @endforeach
                        @else
                            <option value="lam-infokom-2027" selected>{{ $project }}</option>
                            <option value="lam-infokom-2026">Akreditasi LAM INFOKOM D4 TRK 2026</option>
                        @endif
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-2xs">
                    <span class="size-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span>{{ $statusProject ?? 'Dinilai' }}</span>
                </span>
            </div>
        </div>

        <!-- 3. Ringkasan Kesiapan (Hero Card Navy Gradient) -->
        <div
            class="relative overflow-hidden bg-gradient-to-br from-primary via-[#01356e] to-additional rounded-2xl p-6 sm:p-7 text-white shadow-sm space-y-6">
            <!-- Subtle Glow Decors -->
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute right-1/3 -top-12 w-48 h-48 bg-secondary/10 rounded-full blur-xl pointer-events-none">
            </div>

            <!-- Top Row: Skor & Progress -->
            <div class="relative z-1 flex flex-wrap items-start justify-between gap-6">
                <div class="space-y-2">
                    <span class="block text-white/70 text-[11px] font-bold tracking-wider uppercase">
                        RINGKASAN KESIAPAN AKREDITASI
                    </span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl sm:text-5xl font-extrabold text-white leading-none tracking-tight">
                            {{ $summaryKesiapan['skor'] ?? '3,08' }}
                        </span>
                        <span class="text-white/60 text-lg font-medium">
                            / {{ $summaryKesiapan['skor_max'] ?? '4,00' }}
                        </span>
                    </div>
                    <div class="pt-1">
                        <span
                            class="h-7 px-3.5 bg-secondary text-primary text-xs font-extrabold rounded-full inline-flex items-center shadow-xs">
                            {{ $summaryKesiapan['status'] ?? 'Mendekati target' }}
                        </span>
                    </div>
                </div>

                <!-- Progress: Kelengkapan Borang -->
                <div class="w-full sm:w-72 space-y-2.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-white/80 font-medium">Kelengkapan Borang</span>
                        <span
                            class="text-white font-extrabold text-sm">{{ $summaryKesiapan['kelengkapan_borang'] ?? 61 }}%</span>
                    </div>
                    <div class="w-full h-3 bg-white/20 rounded-full overflow-hidden p-0.5">
                        <div class="h-full bg-secondary rounded-full transition-all duration-700 ease-out shadow-xs"
                            style="width: {{ $summaryKesiapan['kelengkapan_borang'] ?? 61 }}%"></div>
                    </div>
                    <p class="text-[11px] text-white/60">Terhitung otomatis dari capaian pengisian indikator prodi.</p>
                </div>
            </div>

            <!-- Divider & Metrics Grid -->
            <div class="relative z-1 border-t border-white/15 pt-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10 backdrop-blur-xs">
                        <p class="text-3xl font-extrabold text-white leading-tight">
                            {{ $summaryKesiapan['indikator_tercapai'] ?? 14 }}
                        </p>
                        <p class="text-white/70 text-xs font-medium mt-1">
                            Indikator tercapai target
                        </p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10 backdrop-blur-xs">
                        <p class="text-3xl font-extrabold text-white leading-tight">
                            {{ $summaryKesiapan['di_bawah_target'] ?? 6 }}
                        </p>
                        <p class="text-white/70 text-xs font-medium mt-1">
                            Indikator di bawah target
                        </p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10 backdrop-blur-xs">
                        <p class="text-3xl font-extrabold text-white leading-tight">
                            {{ $summaryKesiapan['temuan_terbuka'] ?? 5 }}
                        </p>
                        <p class="text-white/70 text-xs font-medium mt-1">
                            Temuan audit terbuka
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Top Action Bar (Cetak / Simpan PDF) -->
        <div
            class="bg-white rounded-2xl border border-[#EAEAEA] p-4 sm:px-6 sm:py-4 shadow-xs flex flex-wrap items-center justify-between gap-4 sticky top-4 z-10 backdrop-blur-md bg-white/95">
            <div class="flex items-center gap-2 text-xs sm:text-sm text-[#5A6E85] font-medium">
                <span id="selectedCount" class="font-extrabold text-primary text-base">4</span>
                <span>bagian dipilih</span>
                <span class="text-gray-300">·</span>
                <span>format</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-primary/10 text-primary font-bold text-xs uppercase tracking-wider">
                    PDF
                </span>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="window.print()"
                    class="h-10 px-6 bg-primary hover:bg-additional text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs hover:shadow transition cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.72 13.829c-.24-1.03-.42-2.11-.52-3.23C5.74 5.92 9.4 2.5 14 2.5s8.26 3.42 7.8 8.099c-.1 1.12-.28 2.2-.52 3.23M17 17v4H7v-4m10 0H7m10 0h3.5a1.5 1.5 0 001.5-1.5v-4a1.5 1.5 0 00-1.5-1.5H4a1.5 1.5 0 00-1.5 1.5v4A1.5 1.5 0 004 17h3" />
                    </svg>
                    <span>Cetak / Simpan PDF</span>
                </button>
            </div>
        </div>

        <!-- 5. Split Panel: Form Pilihan (Kiri) & Pratinjau Dokumen (Kanan) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Panel Kiri: Form Pilihan & Format (5 / 12) -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-[#EAEAEA] p-6 space-y-6 shadow-xs">

                <!-- Bagian 1: Pilih Isi Laporan -->
                <div class="space-y-3">
                    <div>
                        <h3 class="text-base font-extrabold text-primary tracking-tight">
                            Pilih Isi Laporan
                        </h3>
                        <p class="text-xs text-[#5A6E85] mt-0.5">
                            Centang komponen yang ingin dicantumkan dalam lembar laporan.
                        </p>
                    </div>

                    <div class="space-y-2">

                        <!-- Checkbox 1: Ringkasan Eksekutif -->
                        <label
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50/80 transition-all cursor-pointer select-none">
                            <input type="checkbox"
                                class="report-section-checkbox size-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer accent-primary"
                                data-target="previewRingkasan" checked onchange="updateSectionToggle(this)">
                            <span class="text-xs sm:text-sm font-medium text-gray-800">Ringkasan eksekutif</span>
                        </label>

                        <!-- Checkbox 2: Rekap Skor per Kriteria -->
                        <label
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50/80 transition-all cursor-pointer select-none">
                            <input type="checkbox"
                                class="report-section-checkbox size-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer accent-primary"
                                data-target="previewRekap" checked onchange="updateSectionToggle(this)">
                            <span class="text-xs sm:text-sm font-medium text-gray-800">Rekap skor per kriteria</span>
                        </label>

                        <!-- Checkbox 3: Gap Analysis -->
                        <label
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50/80 transition-all cursor-pointer select-none">
                            <input type="checkbox"
                                class="report-section-checkbox size-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer accent-primary"
                                data-target="previewGap" checked onchange="updateSectionToggle(this)">
                            <span class="text-xs sm:text-sm font-medium text-gray-800">Gap analysis (indikator di bawah
                                target)</span>
                        </label>

                        <!-- Checkbox 4: Daftar Bukti per Indikator -->
                        <label
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50/80 transition-all cursor-pointer select-none">
                            <input type="checkbox"
                                class="report-section-checkbox size-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer accent-primary"
                                data-target="previewBukti" onchange="updateSectionToggle(this)">
                            <span class="text-xs sm:text-sm font-medium text-gray-800">Daftar bukti per indikator</span>
                        </label>

                        <!-- Checkbox 5: Hasil Audit Mutu Internal (AMI) -->
                        <label
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50/80 transition-all cursor-pointer select-none">
                            <input type="checkbox"
                                class="report-section-checkbox size-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer accent-primary"
                                data-target="previewAmi" checked onchange="updateSectionToggle(this)">
                            <span class="text-xs sm:text-sm font-medium text-gray-800">Hasil audit mutu internal
                                (AMI)</span>
                        </label>

                        <!-- Checkbox 6: Tindak Lanjut dan Keputusan RTM -->
                        <label
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50/80 transition-all cursor-pointer select-none">
                            <input type="checkbox"
                                class="report-section-checkbox size-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer accent-primary"
                                data-target="previewRtm" onchange="updateSectionToggle(this)">
                            <span class="text-xs sm:text-sm font-medium text-gray-800">Tindak lanjut dan keputusan
                                RTM</span>
                        </label>

                    </div>
                </div>

                <!-- Bagian 2: Tahun Audit -->
                <div class="space-y-1.5 pt-2 border-t border-gray-100">
                    <label for="filterTahunAudit"
                        class="block text-gray-500 text-[11px] font-bold tracking-wider uppercase">
                        TAHUN AUDIT
                    </label>
                    <div class="relative w-full">
                        <select id="filterTahunAudit"
                            class="w-full h-11 bg-gray-50/80 hover:bg-gray-100/80 text-primary font-bold text-xs sm:text-sm rounded-xl px-4 pr-10 appearance-none border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-xs cursor-pointer transition-colors">
                            <option value="2026" {{ $selectedYear == 2026 ? 'selected' : '' }}>2026</option>
                            <option value="2025" {{ $selectedYear == 2025 ? 'selected' : '' }}>2025</option>
                            <option value="2024" {{ $selectedYear == 2024 ? 'selected' : '' }}>2024</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-primary">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Panel Kanan: Pratinjau Dokumen (7 / 12) -->
            <div
                class="lg:col-span-7 bg-white rounded-2xl border border-[#EAEAEA] overflow-hidden shadow-xs flex flex-col">

                <!-- Toolbar Pratinjau -->
                <div class="px-6 py-4 bg-gray-50/80 border-b border-[#EAEAEA] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-additional" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-sm sm:text-base font-extrabold text-primary tracking-tight">
                            Pratinjau Lembar Laporan
                        </h3>
                    </div>

                    <!-- Zoom Controls -->
                    <div class="p-1 bg-white rounded-xl border border-gray-200 flex items-center gap-1 shadow-2xs">
                        <button type="button" onclick="adjustZoom(-10)"
                            class="size-7 rounded-lg flex items-center justify-center text-gray-500 hover:text-primary hover:bg-gray-100 font-extrabold text-base transition cursor-pointer"
                            title="Perkecil">
                            −
                        </button>
                        <span id="zoomLevelDisplay" class="w-12 text-center text-xs font-bold text-primary select-none">
                            85%
                        </span>
                        <button type="button" onclick="adjustZoom(10)"
                            class="size-7 rounded-lg flex items-center justify-center text-gray-500 hover:text-primary hover:bg-gray-100 font-extrabold text-base transition cursor-pointer"
                            title="Perbesar">
                            +
                        </button>
                    </div>
                </div>

                <!-- Container Kertas Dokumen (Scrollable) -->
                <div class="p-4 sm:p-6 overflow-x-auto max-h-[820px] overflow-y-auto flex justify-center bg-[#F8FAFC]">

                    <!-- Lembar Kertas Laporan (Document Sheet) -->
                    <div id="previewDocumentPaper"
                        class="w-[640px] min-h-[860px] bg-white p-8 sm:p-10 shadow-lg rounded-xl border border-gray-200/90 space-y-6 transition-transform duration-150 origin-top select-none"
                        style="transform: scale(0.85); transform-origin: top center;">

                        <!-- Header Kertas Laporan (Kop Dokumen) -->
                        <div class="pb-4 border-b-2 border-primary flex items-center gap-4">
                            <div
                                class="size-12 bg-primary rounded-xl flex items-center justify-center shrink-0 text-white shadow-xs">
                                <svg class="size-6 text-secondary" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zm0 3.27L18.18 9 12 11.73 5.82 9 12 6.27z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-additional text-[10px] font-extrabold tracking-widest uppercase">
                                    SISTEM AKREDITASI PROGRAM STUDI · LAM INFOKOM
                                </span>
                                <h4 class="text-primary text-sm font-extrabold leading-tight mt-0.5">
                                    Laporan Akhir Akreditasi Program Studi D4 Teknologi Rekayasa Komputer
                                </h4>
                                <span class="block text-gray-500 text-[10px] font-medium mt-0.5">
                                    Tahun audit <span id="docTahunDisplay">{{ $selectedYear }}</span> · <span
                                        id="docProjectDisplay">{{ $project }}</span>
                                </span>
                            </div>
                        </div>

                        <!-- Bagian 1 Dokumen: Ringkasan Eksekutif -->
                        <div id="previewRingkasan" class="space-y-2">
                            <div class="pl-2.5 border-l-4 border-secondary">
                                <h5 class="text-primary text-xs font-extrabold uppercase tracking-wide">
                                    Ringkasan Eksekutif
                                </h5>
                            </div>
                            <p class="text-gray-700 text-[11px] leading-relaxed pl-3 font-normal">
                                Skor total self-assessment prodi adalah <strong
                                    class="font-bold text-primary">{{ $summaryKesiapan['skor'] ?? '3,08' }} dari
                                    {{ $summaryKesiapan['skor_max'] ?? '4,00' }}</strong> dengan kelengkapan borang
                                {{ $summaryKesiapan['kelengkapan_borang'] ?? 61 }}%. Sebanyak
                                {{ $summaryKesiapan['di_bawah_target'] ?? 6 }} dari {{ count($rekapKriteria) }} kriteria
                                masih di bawah target 3,25, terutama pada kriteria Mahasiswa, SDM, dan Penelitian. Terdapat
                                {{ $summaryKesiapan['temuan_terbuka'] ?? 5 }} temuan audit yang masih terbuka.
                            </p>
                        </div>

                        <!-- Bagian 2 Dokumen: Rekap Skor per Kriteria -->
                        <div id="previewRekap" class="space-y-2.5">
                            <div class="pl-2.5 border-l-4 border-secondary">
                                <h5 class="text-primary text-xs font-extrabold uppercase tracking-wide">
                                    Rekap Skor per Kriteria
                                </h5>
                            </div>
                            <div class="pl-3 overflow-x-auto">
                                <table
                                    class="w-full text-left text-[10px] border-collapse rounded-lg overflow-hidden border border-gray-100">
                                    <thead>
                                        <tr class="bg-primary text-white font-bold">
                                            <th class="px-3 py-2 w-14">Kode</th>
                                            <th class="px-3 py-2">Kriteria</th>
                                            <th class="px-3 py-2 text-right w-16">Skor</th>
                                            <th class="px-3 py-2 text-right w-16">Target</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($rekapKriteria as $k)
                                            <tr class="hover:bg-gray-50/80 transition-colors">
                                                <td class="px-3 py-1.5 text-additional font-bold">{{ $k['kode'] }}</td>
                                                <td class="px-3 py-1.5 text-gray-800 font-medium">{{ $k['nama'] }}</td>
                                                <td
                                                    class="px-3 py-1.5 text-right font-bold {{ $k['tercapai'] ? 'text-primary' : 'text-rose-600' }}">
                                                    {{ $k['skor'] }}
                                                </td>
                                                <td class="px-3 py-1.5 text-right text-gray-500 font-medium">
                                                    {{ $k['target'] }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-gray-50 font-extrabold border-t-2 border-primary/20">
                                            <td colspan="2" class="px-3 py-2 text-primary font-extrabold">Skor Total
                                            </td>
                                            <td class="px-3 py-2 text-right text-primary font-extrabold">
                                                {{ $summaryKesiapan['skor'] ?? '3,08' }}</td>
                                            <td class="px-3 py-2 text-right text-gray-600">3,25</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Bagian 3 Dokumen: Gap Analysis -->
                        <div id="previewGap" class="space-y-2">
                            <div class="pl-2.5 border-l-4 border-secondary">
                                <h5 class="text-primary text-xs font-extrabold uppercase tracking-wide">
                                    Gap Analysis (Indikator di Bawah Target)
                                </h5>
                            </div>
                            <div class="pl-3 divide-y divide-gray-100 text-[10px]">
                                @foreach ($gapAnalysis as $gap)
                                    <div class="py-1.5 flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-additional">{{ $gap['kode'] }}</span>
                                            <span class="text-gray-700 font-medium">{{ $gap['indikator'] }}</span>
                                        </div>
                                        <span
                                            class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 font-bold text-[10px] border border-rose-200/60">{{ $gap['skor'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Bagian 4 Dokumen: Daftar Bukti per Indikator -->
                        <div id="previewBukti" class="space-y-2 hidden">
                            <div class="pl-2.5 border-l-4 border-secondary">
                                <h5 class="text-primary text-xs font-extrabold uppercase tracking-wide">
                                    Daftar Bukti per Indikator
                                </h5>
                            </div>
                            <div class="pl-3">
                                <div
                                    class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-[11px] text-gray-600 italic leading-relaxed">
                                    Tercatat 28 dokumen bukti telah diunggah dan terverifikasi untuk kriteria tata pamong
                                    dan proses pembelajaran.
                                </div>
                            </div>
                        </div>

                        <!-- Bagian 5 Dokumen: Hasil AMI -->
                        <div id="previewAmi" class="space-y-2">
                            <div class="pl-2.5 border-l-4 border-secondary">
                                <h5 class="text-primary text-xs font-extrabold uppercase tracking-wide">
                                    Hasil Audit Mutu Internal (AMI) {{ $selectedYear }}
                                </h5>
                            </div>
                            <p class="text-gray-700 text-[11px] leading-relaxed pl-3 font-normal">
                                6 temuan: 3 selesai, 1 dalam perbaikan, 2 menunggu tanggapan (STD-04 evaluasi kurikulum,
                                STD-07 rasio dosen-mahasiswa).
                            </p>
                        </div>

                        <!-- Bagian 6 Dokumen: Tindak Lanjut dan Keputusan RTM -->
                        <div id="previewRtm" class="space-y-2 hidden">
                            <div class="pl-2.5 border-l-4 border-secondary">
                                <h5 class="text-primary text-xs font-extrabold uppercase tracking-wide">
                                    Tindak Lanjut & Keputusan RTM
                                </h5>
                            </div>
                            <div class="pl-3">
                                <div
                                    class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-[11px] text-gray-700 leading-relaxed">
                                    RTM memutuskan percepatan rekrutmen 2 dosen tetap dan pengesahan roadmap penelitian
                                    prodi 2025–2029.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 12mm 15mm;
            }

            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                min-height: 100% !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            header, aside, #sidebar, [role="navigation"], .sticky, button, #toast-container, .toast-container {
                display: none !important;
            }

            body * {
                visibility: hidden;
            }

            #previewDocumentPaper,
            #previewDocumentPaper * {
                visibility: visible;
            }

            #previewDocumentPaper {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                transform: none !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                background: #ffffff !important;
            }

            #previewRingkasan, #previewRekap, #previewGap, #previewBukti, #previewAmi, #previewRtm {
                page-break-inside: avoid;
                break-inside: avoid;
                margin-bottom: 1.25rem !important;
            }

            table {
                page-break-inside: auto;
                width: 100% !important;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }
        }
    </style>

    <!-- Interactive JavaScript for Checkbox Toggles and Document Zoom -->
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

        // Adjust document preview zoom level
        function adjustZoom(delta) {
            currentZoom = Math.min(125, Math.max(50, currentZoom + delta));
            const paper = document.getElementById('previewDocumentPaper');
            const display = document.getElementById('zoomLevelDisplay');
            if (paper) {
                paper.style.transform = `scale(${currentZoom / 100})`;
            }
            if (display) {
                display.textContent = `${currentZoom}%`;
            }
        }

        // Synchronize Project and Year Selectors with Document Paper
        const filterTahunAudit = document.getElementById('filterTahunAudit');
        if (filterTahunAudit) {
            filterTahunAudit.addEventListener('change', function() {
                const docTahun = document.getElementById('docTahunDisplay');
                if (docTahun) docTahun.textContent = this.value;
            });
        }

        const projectSelector = document.getElementById('projectSelector');
        if (projectSelector) {
            projectSelector.addEventListener('change', function() {
                const docProj = document.getElementById('docProjectDisplay');
                const selectedText = this.options[this.selectedIndex]?.text || '';
                if (docProj && selectedText) {
                    docProj.textContent = selectedText.split('(')[0].trim();
                }
            });
        }
    </script>
@endsection
