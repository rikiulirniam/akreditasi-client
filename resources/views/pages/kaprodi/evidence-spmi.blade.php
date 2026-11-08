@extends('layouts.app')

@section('title', 'Evidence SPMI')

@section('content')
<div class="max-w-[1120px] mx-auto space-y-6 pb-12 font-['Plus_Jakarta_Sans']">

    {{-- 1. Header Section --}}
    <div class="space-y-1">
        <h2 class="text-2xl sm:text-3xl font-bold text-black tracking-tight">
            Evidence SPMI
        </h2>
        <p class="text-base sm:text-lg text-black/80 font-normal leading-7">
            Kelola dan unggah bukti mutu operasional program studi per project akreditasi.
        </p>
    </div>

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
            <div class="px-6 border-b border-zinc-200 bg-white flex items-center gap-1 sm:gap-2 overflow-x-auto shrink-0" id="categoryTabsContainer">
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
                                    <span>Buka Dokumen ↗</span>
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
                                Deskripsi Ringkas Dokumen Evidence
                                <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <textarea name="evidences[{{ $kat['key'] }}][deskripsi]"
                                id="evidence_deskripsi_{{ $kat['key'] }}"
                                rows="3"
                                placeholder="Tuliskan ringkasan bukti dokumen atau catatan pendukung..."
                                class="w-full bg-white border border-zinc-300 rounded-lg p-3 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-sky-800 focus:ring-2 focus:ring-sky-800/10 transition-colors">{{ $evData['deskripsi'] ?? '' }}</textarea>
                        </div>

                        {{-- Input Tautan Dokumen --}}
                        <div class="space-y-1.5">
                            <label for="evidence_link_{{ $kat['key'] }}" class="block text-xs font-bold text-slate-800">
                                Tautan Dokumen (Google Drive / Cloud URL)
                                <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <input type="url"
                                name="evidences[{{ $kat['key'] }}][link_dokumen]"
                                id="evidence_link_{{ $kat['key'] }}"
                                value="{{ $evData['link_dokumen'] ?? '' }}"
                                placeholder="https://drive.google.com/..."
                                class="w-full h-11 bg-white border border-zinc-300 rounded-lg px-3.5 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-sky-800 focus:ring-2 focus:ring-sky-800/10 transition-colors">
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
            } else {
                btn.className = 'category-tab-btn py-3.5 px-3 text-xs sm:text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap';
                if (content) content.classList.add('hidden');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        switchCategoryTab('{{ $kategoriEvidence[0]['key'] ?? 'bukti_evaluasi' }}');
        adjustProjectSelectorWidth();
    });

    window.addEventListener('resize', adjustProjectSelectorWidth);

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(adjustProjectSelectorWidth);
    }
</script>
@endsection
