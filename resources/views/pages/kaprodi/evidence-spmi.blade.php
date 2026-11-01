@extends('layouts.app')

@section('title', 'Evidence SPMI')

@section('content')
@php
    $selectedIndex = 0;
    foreach ($standarList as $i => $s) {
        if ((int) $s['id'] === (int) ($selectedStandarId ?? 1)) {
            $selectedIndex = $i;
            break;
        }
    }
    $activeStandar = $standarList[$selectedIndex] ?? ($standarList[0] ?? []);
    $activeStandarId = $activeStandar['id'] ?? 1;
@endphp

<div class="max-w-[1120px] mx-auto space-y-6 pb-12 font-['Plus_Jakarta_Sans']">

    {{-- 1. Header Section --}}
    <div class="space-y-1">
        <h2 class="text-2xl sm:text-3xl font-bold text-black tracking-tight">
            Evidence SPMI
        </h2>
        <p class="text-base sm:text-lg text-black/80 font-normal leading-7">
            Kelola dan unggah bukti mutu operasional program studi per standar mutu.
        </p>
    </div>

    {{-- 2. Statistik Cards (3 Card Ringkasan) --}}
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
                    TOTAL EVIDENCE
                </p>
            </div>
        </div>

        {{-- Card 2: Terverifikasi / Terisi --}}
        <div class="bg-white rounded-xl shadow-[0px_4px_7px_2px_rgba(0,0,0,0.06)] border border-[#EAEAEA] p-4 flex items-center gap-4 hover:shadow-md transition-shadow duration-150">
            <div class="size-11 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                <svg class="size-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-3xl font-bold text-sky-950 leading-tight">
                    {{ $summary['terisi'] }}
                </p>
                <p class="text-xs font-bold text-black-700 tracking-wider uppercase mt-0.5">
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
                <p class="text-xs font-bold text-black-600 tracking-wider uppercase mt-0.5">
                    BELUM TERISI
                </p>
            </div>
        </div>

    </div>

    {{-- 3. Filter Section (Tahun) --}}
    <div class="pt-2">
        <div class="w-36">
            <label for="tahunSelect" class="block text-black/60 text-xs font-bold tracking-wider uppercase mb-1.5">
                TAHUN AUDIT
            </label>
            <div class="relative">
                <select id="tahunSelect"
                    onchange="window.location.href='{{ route('spmi.evidence') }}?tahun=' + this.value + '&standar={{ $activeStandarId }}'"
                    class="w-36 h-11 bg-zinc-200/90 text-black/80 font-medium text-sm rounded-md px-3.5 pr-8 appearance-none border border-zinc-300 focus:outline-sky-800 cursor-pointer transition-colors">
                    @for ($y = (int) date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-sky-950">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Layout Tabbed Focus (2 Kolom: Kiri Daftar Standar, Kanan Editor Tabbed) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- Kolom Kiri: Daftar Standar Mutu (4 / 12) --}}
        <div class="lg:col-span-4 min-h-0 flex flex-col">
            <div id="standarListCard" class="bg-white rounded-xl border border-zinc-200 p-4 shadow-xs flex flex-col overflow-hidden">
                <div class="standar-header flex items-center justify-between pb-2.5 border-b border-zinc-100 shrink-0">
                    <span class="text-xs font-bold text-black uppercase tracking-wider">Standar Mutu</span>
                    <span class="text-xs font-semibold px-2 py-0.5 bg-zinc-100 text-zinc-600 rounded-full">{{ count($standarList) }} Standar</span>
                </div>

                {{-- Scrollable List of Standards --}}
                <div class="space-y-2 flex-1 overflow-y-auto mt-2.5 pr-1 min-h-0" id="standarCardsContainer">
                    @foreach ($standarList as $index => $standar)
                        @php
                            $standarEvidence = $evidenceData[$standar['id']] ?? [];
                            $filledCount = 0;
                            foreach ($standarEvidence as $ev) {
                                if (!empty($ev['deskripsi']) || !empty($ev['link_dokumen'])) {
                                    $filledCount++;
                                }
                            }

                            $isComplete = $filledCount === 4;
                            $hasSome = $filledCount > 0;
                            $badgeLabel = $isComplete ? 'Lengkap' : ($hasSome ? $filledCount . '/4' : '0/4');
                            $isActive = $index === $selectedIndex;
                        @endphp

                        <div class="standar-card p-3.5 rounded-lg transition-all duration-150 cursor-pointer {{ $isActive ? 'bg-sky-900 text-white shadow-xs' : 'bg-slate-50/80 hover:bg-slate-100 border border-zinc-200/80 text-black' }}"
                            data-index="{{ $index }}"
                            onclick="selectStandar({{ $index }})">

                            {{-- Header Item --}}
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold {{ $isActive ? 'text-sky-200' : 'text-sky-800' }}">
                                    {{ $standar['kode'] }}
                                </span>
                                <span class="standar-badge text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $isActive ? 'bg-white/20 text-white' : ($isComplete ? 'bg-emerald-100 text-emerald-800' : ($hasSome ? 'bg-amber-100 text-amber-800' : 'bg-zinc-200 text-zinc-600')) }}">
                                    {{ $badgeLabel }}
                                </span>
                            </div>

                            {{-- Nama Standar --}}
                            <p class="text-sm font-medium leading-5 mt-1 line-clamp-2 {{ $isActive ? 'text-white' : 'text-slate-800' }}">
                                {{ $standar['nama'] }}
                            </p>

                            {{-- Progress Bar Mini --}}
                            <div class="mt-2.5">
                                <div class="w-full rounded-full h-1 {{ $isActive ? 'bg-white/20' : 'bg-zinc-200' }}">
                                    <div class="standar-progress-bar h-1 rounded-full transition-all duration-300 {{ $isActive ? 'bg-emerald-400' : 'bg-sky-800' }}" style="width: {{ $filledCount > 0 ? ($filledCount / 4) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Editor Evidence Tabbed (8 / 12) --}}
        <div class="lg:col-span-8 flex flex-col">
            <div id="evidenceFormCard" class="bg-white rounded-xl border border-zinc-200 shadow-xs overflow-hidden flex flex-col">

                {{-- Header Panel Standar Terpilih --}}
                <div class="px-6 py-4 bg-slate-50 border-b border-zinc-200 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <span id="panelStandarKode" class="text-xs font-bold text-sky-800 uppercase tracking-wider block">
                            {{ $activeStandar['kode'] ?? '' }}
                        </span>
                        <h3 id="panelStandarNama" class="text-base sm:text-lg font-bold text-slate-900 mt-0.5 leading-snug">
                            {{ $activeStandar['nama'] ?? '' }}
                        </h3>
                    </div>
                    <span id="panelBadge" class="h-7 px-3 rounded-full flex items-center text-xs font-bold shrink-0
                        @php
                            $activeEvidence = $evidenceData[$activeStandarId] ?? [];
                            $activeFilled = 0;
                            foreach ($activeEvidence as $ev) {
                                if (!empty($ev['deskripsi']) || !empty($ev['link_dokumen'])) {
                                    $activeFilled++;
                                }
                            }
                        @endphp
                        {{ $activeFilled === 4 ? 'bg-green-700/10 text-green-700' : ($activeFilled > 0 ? 'bg-yellow-400/25 text-yellow-700' : 'bg-red-100 text-red-600') }}">
                        {{ $activeFilled === 4 ? 'Lengkap (4/4)' : ($activeFilled > 0 ? $activeFilled . ' dari 4 Terisi' : 'Belum Ada (0/4)') }}
                    </span>
                </div>

                {{-- Form Terpadu Berbasis Horizontal Tabs --}}
                <form id="evidenceStandarForm" method="POST" action="{{ route('spmi.evidence.store') }}" class="flex flex-col">
                    @csrf
                    <input type="hidden" name="standar_id" id="formStandarId" value="{{ $activeStandarId }}">
                    <input type="hidden" name="tahun" value="{{ $selectedYear }}">

                    {{-- Horizontal Tab Navigation Bar --}}
                    <div class="px-6 border-b border-zinc-200 bg-white flex items-center gap-1 sm:gap-2 overflow-x-auto shrink-0" id="categoryTabsContainer">
                        @foreach ($kategoriEvidence as $tIndex => $kat)
                            @php
                                $tabEv = $evidenceData[$activeStandarId][$kat['key']] ?? null;
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

                    {{-- Tab Content Area (Masing-masing kategori ditampilkan secara bergantian) --}}
                    <div class="p-6 space-y-5 flex-1 flex flex-col justify-start min-h-[300px]">
                        @foreach ($kategoriEvidence as $cIndex => $kat)
                            @php
                                $evData = $evidenceData[$activeStandarId][$kat['key']] ?? null;
                                $isFilled = $evData && (!empty($evData['deskripsi']) || !empty($evData['link_dokumen']));
                                $hasLink = !empty($evData['link_dokumen']);
                                $isFirst = $cIndex === 0;
                            @endphp

                            <div id="categoryContent_{{ $kat['key'] }}" class="category-content-panel space-y-4 flex-1 flex flex-col justify-start {{ $isFirst ? '' : 'hidden' }}">

                                {{-- Status Banner Kategori Terpilih --}}
                                <div class="bg-slate-50 rounded-xl p-4 border border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
                                    <div class="flex items-start gap-3">
                                        <div id="categoryIconBox_{{ $kat['key'] }}" class="size-9 rounded-lg flex items-center justify-center font-bold text-sm shrink-0 {{ $isFilled ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-200 text-zinc-500' }}">
                                            <span id="categoryIcon_{{ $kat['key'] }}">{{ $isFilled ? '✓' : '•' }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p id="categoryStatusLabel_{{ $kat['key'] }}" class="text-xs font-bold {{ $isFilled ? 'text-emerald-800' : 'text-slate-700' }}">
                                                {{ $isFilled ? 'Status: Terisi' : 'Status: Belum Terisi' }}
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

                                {{-- Hidden Evidence ID --}}
                                <input type="hidden"
                                    name="evidences[{{ $kat['key'] }}][id]"
                                    id="evidence_id_{{ $kat['key'] }}"
                                    value="{{ $evData['id'] ?? '' }}">

                                {{-- Input Deskripsi Dokumen --}}
                                <div class="space-y-1.5">
                                    <label for="evidence_deskripsi_{{ $kat['key'] }}" class="block text-xs font-bold text-slate-800">
                                        Deskripsi Ringkas Dokumen Evidence
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

                    {{-- Footer Action Bar (Selalu Terlihat di Bagian Bawah Card) --}}
                    <div class="px-6 py-4 bg-slate-50 border-t border-zinc-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <span class="text-xs text-slate-500">
                            Menyimpan seluruh bukti dokumen untuk standar mutu ini
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

    </div>

</div>

{{-- Interactive JavaScript --}}
<script>
    const standarData = @json($standarList);
    const evidenceData = @json($evidenceData);
    const kategoriEvidence = @json($kategoriEvidence);
    const selectedYear = {{ $selectedYear }};
    let currentCategoryKey = '{{ $kategoriEvidence[0]['key'] ?? 'bukti_evaluasi' }}';

    // Beralih Tab Kategori (Bukti Evaluasi, Pengendalian, Dampak, Implementasi)
    function switchCategoryTab(targetKey) {
        currentCategoryKey = targetKey;

        // 1. Update Tab Button Styles
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

        syncEqualHeight();
    }

    // Memilih Standar Mutu dari Daftar Kiri
    function selectStandar(index) {
        const standar = standarData[index];
        if (!standar) return;

        // 1. Update Card Outlines di Kolom Kiri
        const cards = document.querySelectorAll('.standar-card');
        cards.forEach((card, i) => {
            const codeEl = card.querySelector('.text-xs');
            const nameEl = card.querySelector('p');
            const progressTrack = card.querySelector('.rounded-full.h-1');
            const progressBar = card.querySelector('.standar-progress-bar');
            const badgeEl = card.querySelector('.standar-badge');

            if (i === index) {
                card.className = 'standar-card p-3.5 rounded-lg transition-all duration-150 cursor-pointer bg-sky-900 text-white shadow-xs';
                if (codeEl) codeEl.className = 'text-xs font-bold text-sky-200';
                if (nameEl) nameEl.className = 'text-sm font-medium leading-5 mt-1 line-clamp-2 text-white';
                if (progressTrack) progressTrack.className = 'w-full rounded-full h-1 bg-white/20';
                if (progressBar) progressBar.className = 'standar-progress-bar h-1 rounded-full transition-all duration-300 bg-emerald-400';
                if (badgeEl) badgeEl.className = 'standar-badge text-[11px] font-semibold px-2 py-0.5 rounded-full bg-white/20 text-white';
            } else {
                card.className = 'standar-card p-3.5 rounded-lg transition-all duration-150 cursor-pointer bg-slate-50/80 hover:bg-slate-100 border border-zinc-200/80 text-black';
                if (codeEl) codeEl.className = 'text-xs font-bold text-sky-800';
                if (nameEl) nameEl.className = 'text-sm font-medium leading-5 mt-1 line-clamp-2 text-slate-800';
                if (progressTrack) progressTrack.className = 'w-full rounded-full h-1 bg-zinc-200';
                if (progressBar) progressBar.className = 'standar-progress-bar h-1 rounded-full transition-all duration-300 bg-sky-800';
                
                // Kembalikan badge warna normal
                const stdEv = evidenceData[standarData[i].id] || {};
                let fCount = 0;
                for (const k in stdEv) {
                    if (stdEv[k] && (stdEv[k].deskripsi || stdEv[k].link_dokumen)) fCount++;
                }
                if (badgeEl) {
                    badgeEl.className = `standar-badge text-[11px] font-semibold px-2 py-0.5 rounded-full ${fCount === 4 ? 'bg-emerald-100 text-emerald-800' : (fCount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-zinc-200 text-zinc-600')}`;
                }
            }
        });

        // 2. Update Header Panel Kanan & Hidden Standar ID
        document.getElementById('panelStandarKode').textContent = standar.kode;
        document.getElementById('panelStandarNama').textContent = standar.nama;
        const formStandarId = document.getElementById('formStandarId');
        if (formStandarId) formStandarId.value = standar.id;

        // 3. Update Ringkasan Badge Standar
        const standarEvidence = evidenceData[standar.id] || {};
        let filledCount = 0;
        for (const key in standarEvidence) {
            const ev = standarEvidence[key];
            if (ev && (ev.deskripsi || ev.link_dokumen)) {
                filledCount++;
            }
        }

        const panelBadge = document.getElementById('panelBadge');
        if (filledCount === 4) {
            panelBadge.className = 'h-7 px-3 rounded-full flex items-center text-xs font-bold shrink-0 bg-green-700/10 text-green-700';
            panelBadge.textContent = 'Lengkap (4/4)';
        } else if (filledCount > 0) {
            panelBadge.className = 'h-7 px-3 rounded-full flex items-center text-xs font-bold shrink-0 bg-yellow-400/25 text-yellow-700';
            panelBadge.textContent = filledCount + ' dari 4 Terisi';
        } else {
            panelBadge.className = 'h-7 px-3 rounded-full flex items-center text-xs font-bold shrink-0 bg-red-100 text-red-600';
            panelBadge.textContent = 'Belum Ada (0/4)';
        }

        // 4. Update Inputs & Tabs untuk Setiap Kategori Evidence
        kategoriEvidence.forEach((kat) => {
            const kategoriKey = kat.key;
            const evidence = standarEvidence[kategoriKey] || null;
            const hasContent = evidence && (evidence.deskripsi || evidence.link_dokumen);
            const hasLink = evidence && evidence.link_dokumen && evidence.link_dokumen.trim() !== '';

            // Update Tab Dot Indicator
            const tabDot = document.getElementById(`tabDot_${kategoriKey}`);
            if (tabDot) {
                tabDot.className = `size-2 rounded-full ${hasContent ? 'bg-emerald-500 ring-2 ring-emerald-100' : 'bg-zinc-300'}`;
            }

            // Update Status Banner dalam Tab
            const iconBox = document.getElementById(`categoryIconBox_${kategoriKey}`);
            const icon = document.getElementById(`categoryIcon_${kategoriKey}`);
            const statusLabel = document.getElementById(`categoryStatusLabel_${kategoriKey}`);
            const previewBtn = document.getElementById(`previewLinkBtn_${kategoriKey}`);

            if (iconBox) {
                iconBox.className = `size-9 rounded-lg flex items-center justify-center font-bold text-sm shrink-0 ${hasContent ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-200 text-zinc-500'}`;
            }
            if (icon) {
                icon.textContent = hasContent ? '✓' : '•';
            }
            if (statusLabel) {
                statusLabel.className = `text-xs font-bold ${hasContent ? 'text-emerald-800' : 'text-slate-700'}`;
                statusLabel.textContent = hasContent ? 'Status: Terisi' : 'Status: Belum Terisi';
            }
            if (previewBtn) {
                if (hasLink) {
                    previewBtn.href = evidence.link_dokumen;
                    previewBtn.classList.remove('hidden');
                    previewBtn.classList.add('inline-flex');
                } else {
                    previewBtn.classList.add('hidden');
                    previewBtn.classList.remove('inline-flex');
                }
            }

            // Update Form Input Values
            const idInput = document.getElementById(`evidence_id_${kategoriKey}`);
            const deskripsiInput = document.getElementById(`evidence_deskripsi_${kategoriKey}`);
            const linkInput = document.getElementById(`evidence_link_${kategoriKey}`);

            if (idInput) idInput.value = evidence ? (evidence.id || '') : '';
            if (deskripsiInput) deskripsiInput.value = evidence ? (evidence.deskripsi || '') : '';
            if (linkInput) linkInput.value = evidence ? (evidence.link_dokumen || '') : '';
        });

        syncEqualHeight();
    }

    // Sinkronisasi Tinggi Kartu Kiri & Kanan Secara Presisi
    function syncEqualHeight() {
        const rightCard = document.getElementById('evidenceFormCard');
        const leftCard = document.getElementById('standarListCard');
        const listContainer = document.getElementById('standarCardsContainer');
        if (!rightCard || !leftCard || !listContainer) return;

        if (window.innerWidth >= 1024) {
            const rightHeight = rightCard.offsetHeight;
            if (rightHeight > 0) {
                leftCard.style.height = rightHeight + 'px';
                const headerEl = leftCard.querySelector('.standar-header');
                const headerHeight = headerEl ? headerEl.offsetHeight : 30;
                const paddingAndMargin = 32 + 10; // p-4 (32px) + mt-2.5 (10px)
                const listHeight = Math.max(200, rightHeight - headerHeight - paddingAndMargin);

                listContainer.style.height = listHeight + 'px';
                listContainer.style.maxHeight = listHeight + 'px';
                listContainer.style.overflowY = 'auto';
            }
        } else {
            leftCard.style.height = 'auto';
            listContainer.style.height = 'auto';
            listContainer.style.maxHeight = '420px';
            listContainer.style.overflowY = 'auto';
        }
    }

    // Event Listener untuk Resize & Observers
    window.addEventListener('resize', syncEqualHeight);
    window.addEventListener('load', syncEqualHeight);

    if (window.ResizeObserver) {
        const ro = new ResizeObserver(() => {
            syncEqualHeight();
        });
        const rightCard = document.getElementById('evidenceFormCard');
        if (rightCard) ro.observe(rightCard);
    }

    // Inisialisasi pada saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        selectStandar({{ $selectedIndex }});
        switchCategoryTab('{{ $kategoriEvidence[0]['key'] ?? 'bukti_evaluasi' }}');
        syncEqualHeight();
        setTimeout(syncEqualHeight, 50);
        setTimeout(syncEqualHeight, 250);
    });
</script>
@endsection
