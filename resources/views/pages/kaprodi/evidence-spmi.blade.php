@extends('layouts.app')

@section('title', 'Evidence SPMI')

@section('content')
<style>
    /* Custom Slim & Themed Horizontal Scrollbar for Evidence Tabs */
    .custom-tabs-scroll {
        scrollbar-width: thin;
        scrollbar-color: #94A3B8 #F1F5F9;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }
    .custom-tabs-scroll::-webkit-scrollbar {
        height: 5px;
    }
    .custom-tabs-scroll::-webkit-scrollbar-track {
        background: #F1F5F9;
        border-radius: 9999px;
        margin: 0 1.5rem;
    }
    .custom-tabs-scroll::-webkit-scrollbar-thumb {
        background-color: #94A3B8;
        border-radius: 9999px;
        transition: background-color 0.2s ease;
    }
    .custom-tabs-scroll::-webkit-scrollbar-thumb:hover {
        background-color: #012650;
    }
</style>

<div class="max-w-[1120px] mx-auto space-y-6 pb-12 font-['Plus_Jakarta_Sans']">

    {{-- 1. Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <h2 class="text-2xl sm:text-3xl font-bold text-black tracking-tight">
                Evidence SPMI
            </h2>
            <p class="text-base sm:text-lg text-black/80 font-normal leading-7">
                Kelola dan unggah bukti mutu operasional program studi per project akreditasi.
            </p>
        </div>
        @if (Route::has('spmi.overview'))
            <a href="{{ route('spmi.overview', ['id' => $selectedProjectId]) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-sky-900 hover:bg-sky-950 text-white font-bold text-xs sm:text-sm rounded-xl transition-all shadow-xs shrink-0 self-start sm:self-auto">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Rekapitulasi Dokumen Asesor</span>
            </a>
        @endif
    </div>

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="size-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 cursor-pointer text-lg leading-none">&times;</button>
        </div>
    @endif

    @if (session('warning'))
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-semibold flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="size-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <span>{{ session('warning') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700 cursor-pointer text-lg leading-none">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="size-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 cursor-pointer text-lg leading-none">&times;</button>
        </div>
    @endif

    {{-- 2. Project Akreditasi Selector Card --}}
    <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 sm:p-6 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="w-full sm:w-auto flex-1 min-w-0 max-w-3xl space-y-1.5">
            <label for="projectSelector" class="block text-gray-500 text-[11px] font-bold tracking-wider uppercase">
                PILIH PROJECT AKREDITASI
            </label>
            <div class="relative w-full sm:w-fit max-w-full">
                <select id="projectSelector"
                    onchange="adjustProjectSelectorWidth(); window.location.href='{{ route('spmi.evidence') }}?project_id=' + this.value"
                    class="w-full sm:w-auto min-w-full sm:min-w-fit max-w-full h-11 bg-gray-50/80 hover:bg-gray-100/80 text-primary font-bold text-xs sm:text-sm rounded-xl px-4 pr-11 appearance-none border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-xs cursor-pointer transition-colors">
                    @foreach ($projects as $p)
                        <option value="{{ $p['id'] }}" title="{{ $p['nama_project'] }} ({{ $p['tahun_target_submit'] ?? 2026 }})" {{ (int) $p['id'] === (int) $selectedProjectId ? 'selected' : '' }}>
                            {{ $p['nama_project'] }} ({{ $p['tahun_target_submit'] ?? 2026 }})
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <div class="text-right hidden sm:block">
                <span class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider">LEMBAGA AKREDITASI</span>
                <span class="text-xs sm:text-sm font-bold text-slate-800">{{ $activeProject['nama_lembaga'] ?? 'LAM INFOKOM' }}</span>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-2xs">
                <span class="size-2 bg-emerald-500 rounded-full animate-pulse"></span>
                <span>{{ $activeProject['status_project'] ?? 'In Progress' }}</span>
            </span>
        </div>
    </div>

    {{-- 3. Statistik Cards (3 Card Ringkasan) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">

        {{-- Card 1: Total Evidence --}}
        <div class="bg-white rounded-xl shadow-[0px_4px_7px_2px_rgba(0,0,0,0.06)] border border-[#EAEAEA] p-4 flex items-center gap-4 hover:shadow-md transition-shadow duration-150">
            <div class="size-11 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="size-5 text-sky-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-3xl font-bold text-sky-950 leading-tight">
                    {{ $summary['total_evidence'] }}
                </p>
                <p class="text-xs font-bold text-black tracking-wider uppercase mt-0.5">
                    DOKUMEN EVIDENCE
                </p>
            </div>
        </div>

        {{-- Card 2: Terisi / Terverifikasi --}}
        <div class="bg-white rounded-xl shadow-[0px_4px_7px_2px_rgba(0,0,0,0.06)] border border-[#EAEAEA] p-4 flex items-center gap-4 hover:shadow-md transition-shadow duration-150">
            <div class="size-11 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                <svg class="size-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-baseline justify-between gap-2">
                    <p class="text-3xl font-bold text-sky-950 leading-tight">
                        {{ $summary['terisi'] }} <span class="text-sm font-semibold text-slate-500">/ 4</span>
                    </p>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        {{ $summary['persen'] }}%
                    </span>
                </div>
                <p class="text-xs font-bold text-green-700 tracking-wider uppercase mt-0.5">
                    TERVERIFIKASI
                </p>
            </div>
        </div>

        {{-- Card 3: Belum Terisi --}}
        <div class="bg-white rounded-xl shadow-[0px_4px_7px_2px_rgba(0,0,0,0.06)] border border-[#EAEAEA] p-4 flex items-center gap-4 hover:shadow-md transition-shadow duration-150">
            <div class="size-11 rounded-lg bg-rose-50 flex items-center justify-center shrink-0">
                <svg class="size-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-3xl font-bold text-sky-950 leading-tight">
                    {{ $summary['belum_terisi'] }}
                </p>
                <p class="text-xs font-bold text-red-600 tracking-wider uppercase mt-0.5">
                    BELUM TERISI
                </p>
            </div>
        </div>

    </div>

    {{-- 4. Main Evidence Editor Card (Per 1 Project = 1 Bukti Evidence) --}}
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden flex flex-col">

        {{-- Header Form Panel --}}
        <div class="px-6 py-4 bg-slate-50 border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-xs font-bold text-sky-800 uppercase tracking-wider block">
                    PROJECT AKREDITASI AKTIF
                </span>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5 leading-snug">
                    {{ $activeProject['nama_project'] ?? 'Akreditasi Program Studi' }}
                </h3>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-7 px-3 rounded-full flex items-center text-xs font-bold shrink-0
                    {{ $summary['terisi'] === 4 ? 'bg-green-700/10 text-green-700' : ($summary['terisi'] > 0 ? 'bg-yellow-400/25 text-yellow-700' : 'bg-red-100 text-red-600') }}">
                    {{ $summary['terisi'] === 4 ? 'Lengkap (4/4)' : ($summary['terisi'] > 0 ? $summary['terisi'] . ' dari 4 Terisi' : 'Belum Ada (0/4)') }}
                </span>
            </div>
        </div>

        {{-- Form Evidence Terpadu --}}
        <form id="evidenceProjectForm" method="POST" action="{{ route('spmi.evidence.store') }}" class="flex flex-col">
            @csrf
            <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">

            {{-- Horizontal Tab Navigation Bar --}}
            <div class="px-6 border-b border-zinc-200 bg-white flex items-center gap-1 sm:gap-2 overflow-x-auto shrink-0 custom-tabs-scroll pb-1" id="categoryTabsContainer">
                @foreach ($kategoriEvidence as $tIndex => $kat)
                    @php
                        $tabEv = $evidenceData[$kat['key']] ?? null;
                        $tabFilled = $tabEv && (!empty($tabEv['deskripsi']) || !empty($tabEv['link_dokumen']));
                        $isFirstTab = $tIndex === 0;
                    @endphp
                    <button type="button"
                        id="tabBtn_{{ $kat['key'] }}"
                        onclick="switchCategoryTab('{{ $kat['key'] }}')"
                        class="category-tab-btn py-3.5 px-3 text-xs sm:text-sm font-semibold border-b-2 flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap {{ $isFirstTab ? 'border-sky-800 text-sky-800 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                        <span>{{ $kat['label'] }}</span>
                        <span id="tabDot_{{ $kat['key'] }}" class="size-2 rounded-full {{ $tabFilled ? 'bg-emerald-500 ring-2 ring-emerald-100' : 'bg-zinc-300' }}"></span>
                    </button>
                @endforeach
            </div>

            {{-- Tab Content Area (4 Kategori Dokumen Bukti) --}}
            <div class="p-6 space-y-5 flex-1 min-h-[300px]">
                @foreach ($kategoriEvidence as $cIndex => $kat)
                    @php
                        $evData = $evidenceData[$kat['key']] ?? null;
                        $isFilled = $evData && (!empty($evData['deskripsi']) || !empty($evData['link_dokumen']));
                        $hasLink = !empty($evData['link_dokumen']);
                        $isFirst = $cIndex === 0;
                    @endphp

                    <div id="categoryContent_{{ $kat['key'] }}" class="category-content-panel space-y-4 {{ $isFirst ? 'block' : 'hidden' }}">

                        {{-- Status Banner Kategori Terpilih --}}
                        <div class="bg-slate-50 rounded-xl p-4 border border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
                            <div class="flex items-start gap-3">
                                <div id="categoryIconBox_{{ $kat['key'] }}" class="size-9 rounded-lg flex items-center justify-center font-bold text-sm shrink-0 {{ $isFilled ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-200 text-zinc-500' }}">
                                    <span id="categoryIcon_{{ $kat['key'] }}">{{ $isFilled ? '✓' : '•' }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p id="categoryStatusLabel_{{ $kat['key'] }}" class="text-xs font-bold {{ $isFilled ? 'text-emerald-800' : 'text-slate-700' }}">
                                        {{ $kat['label'] }} — {{ $isFilled ? 'Status: Terisi' : 'Status: Belum Terisi' }}
                                    </p>
                                    <p class="text-xs text-slate-500 leading-relaxed mt-0.5">
                                        {{ $kat['deskripsi'] }}
                                    </p>
                                </div>
                            </div>

                            {{-- Quick Preview Link Drive --}}
                            <div class="shrink-0 flex items-center justify-end">
                                <a href="{{ $hasLink ? $evData['link_dokumen'] : '#' }}"
                                    id="previewLinkBtn_{{ $kat['key'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="px-3.5 py-2 bg-white border border-zinc-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-2xs inline-flex items-center gap-1.5 transition-colors {{ $hasLink ? 'inline-flex' : 'hidden' }}">
                                    <svg class="size-4 text-sky-800" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z"/>
                                    </svg>
                                    <span>Buka Dokumen Google Drive ↗</span>
                                </a>
                            </div>
                        </div>

                        {{-- Hidden Evidence ID jika ada --}}
                        <input type="hidden"
                            name="evidences[{{ $kat['key'] }}][id]"
                            id="evidence_id_{{ $kat['key'] }}"
                            value="{{ $evData['id'] ?? '' }}">

                        {{-- Input Deskripsi Dokumen --}}
                        <div class="space-y-1.5">
                            <label for="evidence_deskripsi_{{ $kat['key'] }}" class="block text-xs font-bold text-slate-800">
                                Deskripsi Ringkas Dokumen Mutu Institusi
                                <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <textarea name="{{ $kat['field_deskripsi'] }}"
                                id="evidence_deskripsi_{{ $kat['key'] }}"
                                rows="3"
                                placeholder="{{ $kat['placeholder_deskripsi'] ?? 'Tuliskan ringkasan bukti dokumen mutu atau catatan pendukung...' }}"
                                class="w-full bg-white border border-zinc-300 rounded-lg p-3 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-sky-800 focus:ring-2 focus:ring-sky-800/10 transition-colors">{{ $evData['deskripsi'] ?? '' }}</textarea>
                        </div>

                        {{-- Input Tautan Dokumen --}}
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label for="evidence_link_{{ $kat['key'] }}" class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <svg class="size-4 text-sky-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                    </svg>
                                    <span>Tautan Berkas / Folder Google Drive</span>
                                </label>
                                <span class="text-[11px] text-slate-400 font-medium">Format: https://drive.google.com/...</span>
                            </div>
                            <div class="relative">
                                <input type="url"
                                    name="{{ $kat['field_link'] }}"
                                    id="evidence_link_{{ $kat['key'] }}"
                                    value="{{ $evData['link_dokumen'] ?? '' }}"
                                    placeholder="{{ $kat['placeholder_link'] ?? 'https://drive.google.com/drive/folders/...' }}"
                                    class="w-full h-11 bg-white border border-zinc-300 rounded-lg pl-3.5 pr-28 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-sky-800 focus:ring-2 focus:ring-sky-800/10 transition-colors">
                                @if(!empty($evData['link_dokumen']))
                                    <div class="absolute inset-y-0 right-2 flex items-center">
                                        <a href="{{ $evData['link_dokumen'] }}" target="_blank" rel="noopener noreferrer"
                                            class="text-xs text-sky-800 hover:text-sky-950 font-bold px-2 py-1 bg-sky-50 rounded hover:bg-sky-100 transition-colors inline-flex items-center gap-1">
                                            <span>Buka Link</span>
                                            <span>↗</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 flex items-center gap-1">
                                <span class="text-sky-800 font-bold">&bull;</span>
                                Pastikan izin akses link Google Drive disetel ke "Siapa saja yang memiliki link dapat melihat" agar dapat diverifikasi asesor.
                            </p>
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- Footer Action Bar --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-zinc-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-xs text-slate-500">
                    Menyimpan seluruh bukti dokumen untuk project akreditasi ini (semua isian boleh dicicil).
                </span>
                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <button type="submit"
                        class="h-10 px-6 bg-sky-900 hover:bg-sky-950 text-white font-bold text-sm rounded-lg transition-colors shadow-xs cursor-pointer">
                        Kirim Evidence
                    </button>
                </div>
            </div>
        </form>

    </div>

</div>

{{-- Interactive JavaScript --}}
<script>
    const kategoriEvidence = @json($kategoriEvidence);
    let currentCategoryKey = '{{ $kategoriEvidence[0]['key'] ?? 'bukti_evaluasi' }}';

    // Menyesuaikan lebar select box agar selalu mengikuti panjang teks project yang dipilih
    function adjustProjectSelectorWidth() {
        const sel = document.getElementById('projectSelector');
        if (!sel) return;

        const selectedOption = sel.options[sel.selectedIndex];
        const text = selectedOption ? selectedOption.text : sel.value;

        // Gunakan off-screen span dengan styling font yang sama persis untuk mengukur lebar teks
        const tempSpan = document.createElement('span');
        const computed = window.getComputedStyle(sel);
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

        // Tambahkan padding kiri (16px) + padding kanan untuk chevron (44px) + margin pengaman (16px)
        const totalPadding = 76;
        const requiredWidth = textWidth + totalPadding;

        // Pada mobile, gunakan lebar penuh; pada desktop/tablet, ikuti panjang teks
        const parentWidth = sel.parentElement?.parentElement?.clientWidth || window.innerWidth;
        if (window.innerWidth < 640) {
            sel.style.width = '100%';
        } else {
            sel.style.width = `${Math.min(requiredWidth, Math.max(parentWidth - 10, 360))}px`;
        }
    }

    // Beralih Tab Kategori (Bukti Evaluasi, Pengendalian, Dampak, Implementasi)
    function switchCategoryTab(targetKey) {
        currentCategoryKey = targetKey;

        kategoriEvidence.forEach(kat => {
            const btn = document.getElementById(`tabBtn_${kat.key}`);
            const content = document.getElementById(`categoryContent_${kat.key}`);

            if (kat.key === targetKey) {
                btn.className = 'category-tab-btn py-3.5 px-3 text-xs sm:text-sm font-bold border-b-2 border-sky-800 text-sky-800 flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap';
                if (content) content.classList.remove('hidden');
                // Auto scroll tab aktif ke posisi nyaman jika terpotong
                btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                btn.className = 'category-tab-btn py-3.5 px-3 text-xs sm:text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap';
                if (content) content.classList.add('hidden');
            }
        });
    }

    // Scroll horizontal dengan mouse wheel & touch/drag
    function initHorizontalTabScroll() {
        const tabsContainer = document.getElementById('categoryTabsContainer');
        if (!tabsContainer) return;

        tabsContainer.addEventListener('wheel', function (e) {
            if (e.deltaY !== 0) {
                e.preventDefault();
                tabsContainer.scrollLeft += e.deltaY * 1.2;
            }
        }, { passive: false });
    }

    document.addEventListener('DOMContentLoaded', () => {
        switchCategoryTab('{{ $kategoriEvidence[0]['key'] ?? 'bukti_evaluasi' }}');
        adjustProjectSelectorWidth();
        initHorizontalTabScroll();
    });

    window.addEventListener('resize', adjustProjectSelectorWidth);

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(adjustProjectSelectorWidth);
    }
</script>
@endsection
