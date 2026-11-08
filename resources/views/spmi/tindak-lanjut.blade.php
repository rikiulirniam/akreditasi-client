@extends('layouts.app')

@section('title', 'Tindak Lanjut SPMI')

@section('content')
<div class="max-w-[1120px] mx-auto space-y-6 pb-12 font-['Plus_Jakarta_Sans']">

    {{-- 1. Header Section --}}
    <div class="space-y-1">
        <h2 class="text-2xl sm:text-3xl font-bold text-black tracking-tight">
            Tindak Lanjut SPMI
        </h2>
        <p class="text-base sm:text-lg text-black/80 font-normal leading-7">
            Isi tanggapan dan rencana perbaikan atas temuan audit pada siklus AMI dan RTM.
        </p>
    </div>

    {{-- 2. Statistik Cards (4 Card Ringkasan) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-1">
        
        {{-- Card 1: Temuan Terbuka --}}
        <div class="bg-white rounded-md shadow-[0px_4px_7px_2px_rgba(0,0,0,0.06)] border border-[#EAEAEA] p-4 flex items-center gap-4 hover:shadow-md transition-shadow duration-150">
            <div class="size-11 rounded-md bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="size-5 text-sky-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-3xl font-bold text-sky-950 leading-tight">
                    {{ $summary['temuan_terbuka'] ?? 5 }}
                </p>
                <p class="text-xs font-bold text-black tracking-wider uppercase mt-0.5">
                    TEMUAN TERBUKA
                </p>
            </div>
        </div>

        {{-- Card 2: Menunggu Tanggapan --}}
        <div class="bg-white rounded-md shadow-[0px_4px_7px_2px_rgba(0,0,0,0.06)] border border-[#EAEAEA] p-4 flex items-center gap-4 hover:shadow-md transition-shadow duration-150">
            <div class="size-11 rounded-md bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="size-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-3xl font-bold text-sky-950 leading-tight">
                    {{ $summary['menunggu_tanggapan'] ?? 2 }}
                </p>
                <p class="text-xs font-bold text-black/60 tracking-wider uppercase mt-0.5">
                    MENUNGGU TANGGAPAN
                </p>
            </div>
        </div>

        {{-- Card 3: Dalam Perbaikan --}}
        <div class="bg-white rounded-md shadow-[0px_4px_7px_2px_rgba(0,0,0,0.06)] border border-[#EAEAEA] p-4 flex items-center gap-4 hover:shadow-md transition-shadow duration-150">
            <div class="size-11 rounded-md bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="size-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-3xl font-bold text-sky-950 leading-tight">
                    {{ $summary['dalam_perbaikan'] ?? 1 }}
                </p>
                <p class="text-xs font-bold text-black/60 tracking-wider uppercase mt-0.5">
                    DALAM PERBAIKAN
                </p>
            </div>
        </div>

        {{-- Card 4: Selesai --}}
        <div class="bg-white rounded-md shadow-[0px_4px_7px_2px_rgba(0,0,0,0.06)] border border-[#EAEAEA] p-4 flex items-center gap-4 hover:shadow-md transition-shadow duration-150">
            <div class="size-11 rounded-md bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="size-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-3xl font-bold text-sky-950 leading-tight">
                    {{ $summary['selesai'] ?? 3 }}
                </p>
                <p class="text-xs font-bold text-black/60 tracking-wider uppercase mt-0.5">
                    SELESAI
                </p>
            </div>
        </div>

    </div>

    {{-- 3. Filter Section (Tahun Audit) --}}
    <div class="pt-2">
        <div class="w-36">
            <label for="tahunAuditSelect" class="block text-black/60 text-xs font-bold tracking-wider uppercase mb-1.5">
                TAHUN AUDIT
            </label>
            <div class="relative">
                <select id="tahunAuditSelect"
                    class="w-36 h-11 bg-zinc-200/90 text-black/80 font-medium text-sm rounded-md px-3.5 pr-8 appearance-none border border-zinc-300 focus:outline-sky-800 cursor-pointer transition-colors">
                    <option value="2026" {{ ($selectedYear ?? 2026) == 2026 ? 'selected' : '' }}>2026</option>
                    <option value="2025" {{ ($selectedYear ?? 2026) == 2025 ? 'selected' : '' }}>2025</option>
                    <option value="2024" {{ ($selectedYear ?? 2026) == 2024 ? 'selected' : '' }}>2024</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-sky-950">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Tab Navigasi Interaktif (Temuan AMI & Keputusan RTM) --}}
    <div class="border-b border-zinc-300">
        <nav class="flex gap-6" aria-label="Tabs SPMI">
            <button type="button"
                id="tabBtnAmi"
                class="tab-btn pb-3 text-sm font-semibold transition-all duration-150 cursor-pointer border-b-2 {{ ($activeTab ?? 'ami') === 'ami' ? 'text-sky-800 border-sky-800' : 'text-black/60 border-transparent hover:text-sky-800' }}"
                onclick="switchTab('ami')">
                Temuan AMI
            </button>
            <button type="button"
                id="tabBtnRtm"
                class="tab-btn pb-3 text-sm font-semibold transition-all duration-150 cursor-pointer border-b-2 {{ ($activeTab ?? 'ami') === 'rtm' ? 'text-sky-800 border-sky-800' : 'text-black/60 border-transparent hover:text-sky-800' }}"
                onclick="switchTab('rtm')">
                Keputusan RTM
            </button>
        </nav>
    </div>

    {{-- 5. KONTEN TAB 1: TEMUAN AMI (Two Column Split Layout) --}}
    <div id="tabContentAmi" class="{{ ($activeTab ?? 'ami') === 'ami' ? 'block' : 'hidden' }}">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {{-- Kolom Kiri: Daftar Temuan (5 / 12) --}}
            <div class="lg:col-span-5 space-y-3">
                <div class="flex items-center gap-1.5">
                    <span class="text-black text-lg font-bold">Daftar temuan</span>
                    <span class="text-black/60 text-sm font-normal">({{ count($temuanAmi) }})</span>
                </div>

                {{-- Scrollable List of Findings --}}
                <div class="space-y-2.5 max-h-[640px] overflow-y-auto pr-1" id="findingCardsContainer">
                    @foreach ($temuanAmi as $index => $item)
                        @php
                            $badgeClass = match($item['status']) {
                                'menunggu_tanggapan' => 'bg-yellow-400/25 text-yellow-700',
                                'dalam_perbaikan' => 'bg-sky-800/10 text-sky-800',
                                'selesai' => 'bg-green-700/10 text-green-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                            $isActive = $index === 0;
                        @endphp
                        
                        <div class="finding-card p-4 rounded-md transition-all duration-150 cursor-pointer {{ $isActive ? 'bg-sky-800/5 outline outline-2 outline-offset-[-2px] outline-sky-800' : 'bg-white border border-zinc-300 hover:border-sky-800/40' }}"
                            data-index="{{ $index }}"
                            onclick="selectFinding({{ $index }})">
                            
                            {{-- Header Card --}}
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="text-sky-800 text-xs font-bold leading-5">{{ $item['kode'] }}</span>
                                    <span class="text-black/60 text-xs font-normal leading-5">· {{ $item['standar'] }}</span>
                                </div>
                                <span class="h-7 px-3 rounded-full flex items-center shrink-0 {{ $badgeClass }}">
                                    <span class="text-xs font-semibold whitespace-nowrap">{{ $item['status_label'] }}</span>
                                </span>
                            </div>

                            {{-- Deskripsi Singkat Temuan --}}
                            <p class="text-black text-sm font-medium leading-5 mt-2 line-clamp-2">
                                {{ $item['ringkasan'] }}
                            </p>

                            {{-- Tanggal & Tenggat --}}
                            <div class="text-xs text-black/60 mt-2">
                                {{ $item['siklus'] }} {{ $item['tanggal_audit'] }} · Tenggat <span class="text-black font-semibold">{{ $item['tenggat'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Kolom Kanan: Detail & Form Tanggapan & Rencana Perbaikan (7 / 12) --}}
            <div class="lg:col-span-7">
                <div class="bg-white rounded-md border border-zinc-300 overflow-hidden shadow-xs">
                    
                    {{-- Header Panel Form --}}
                    <div class="px-5 py-4 bg-slate-50 border-b border-zinc-300 flex items-center justify-between gap-3">
                        <h3 class="text-black text-base font-bold leading-6">
                            Tanggapan dan rencana perbaikan
                        </h3>
                        <span id="detailBadge" class="h-7 px-3 bg-yellow-400/25 text-yellow-700 rounded-full flex items-center text-xs font-semibold">
                            Menunggu tanggapan
                        </span>
                    </div>

                    {{-- Form Container --}}
                    <form method="POST" action="{{ route('spmi.tindak-lanjut.store') }}" class="p-5 space-y-4">
                        @csrf
                        <input type="hidden" name="finding_id" id="formFindingId" value="{{ $temuanAmi[0]['id'] ?? 1 }}">

                        {{-- Kotak Detail Standar Mutu, Temuan, dan Rekomendasi --}}
                        <div class="p-4 bg-slate-50 rounded-md border border-zinc-200/80 space-y-3.5">
                            <div>
                                <span class="block text-black/60 text-xs font-bold tracking-wider uppercase">
                                    STANDAR MUTU
                                </span>
                                <p id="detailStandar" class="text-black text-sm font-semibold mt-0.5">
                                    {{ $temuanAmi[0]['judul'] ?? '' }}
                                </p>
                            </div>

                            <div>
                                <span class="block text-black/60 text-xs font-bold tracking-wider uppercase">
                                    TEMUAN
                                </span>
                                <p id="detailDeskripsi" class="text-black text-sm font-normal leading-relaxed mt-0.5">
                                    {{ $temuanAmi[0]['deskripsi'] ?? '' }}
                                </p>
                            </div>

                            <div>
                                <span class="block text-black/60 text-xs font-bold tracking-wider uppercase">
                                    REKOMENDASI AUDITOR
                                </span>
                                <p id="detailRekomendasi" class="text-black text-sm font-normal leading-relaxed mt-0.5">
                                    {{ $temuanAmi[0]['rekomendasi'] ?? '' }}
                                </p>
                            </div>
                        </div>

                        {{-- Input 1: Tanggapan Prodi --}}
                        <div class="space-y-1.5">
                            <label for="inputTanggapan" class="block text-xs font-semibold text-black">
                                Tanggapan prodi <span class="text-red-700">*</span>
                            </label>
                            <textarea id="inputTanggapan"
                                name="tanggapan"
                                rows="3"
                                required
                                placeholder="Tuliskan tanggapan dan klarifikasi dari pihak program studi..."
                                class="w-full bg-white border border-zinc-300 rounded-md p-3 text-sm text-black placeholder:text-black/40 focus:outline-sky-800 transition-colors"></textarea>
                        </div>

                        {{-- Input 2: Rencana Perbaikan --}}
                        <div class="space-y-1.5">
                            <label for="inputRencana" class="block text-xs font-semibold text-black">
                                Rencana perbaikan <span class="text-red-700">*</span>
                            </label>
                            <textarea id="inputRencana"
                                name="rencana_perbaikan"
                                rows="3"
                                required
                                placeholder="Jelaskan tindakan nyata dan tahapan perbaikan yang akan dilakukan..."
                                class="w-full bg-white border border-zinc-300 rounded-md p-3 text-sm text-black placeholder:text-black/40 focus:outline-sky-800 transition-colors"></textarea>
                        </div>

                        {{-- Input Row: Target Selesai & Penanggung Jawab --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label for="inputTarget" class="block text-xs font-semibold text-black">
                                    Target selesai <span class="text-red-700">*</span>
                                </label>
                                <input type="date"
                                    id="inputTarget"
                                    name="target_selesai"
                                    value="{{ $temuanAmi[0]['target_selesai'] ?? '' }}"
                                    required
                                    class="w-full bg-white border border-zinc-300 rounded-md h-11 px-3 text-sm text-black focus:outline-sky-800 transition-colors">
                            </div>

                            <div class="space-y-1.5">
                                <label for="inputPj" class="block text-xs font-semibold text-black">
                                    Penanggung jawab <span class="text-red-700">*</span>
                                </label>
                                <div class="relative">
                                    <select id="inputPj"
                                        name="penanggung_jawab"
                                        required
                                        class="w-full bg-white border border-zinc-300 rounded-md h-11 px-3 pr-8 text-sm text-black appearance-none focus:outline-sky-800 transition-colors cursor-pointer">
                                        <option value="" disabled selected>Pilih anggota tim</option>
                                        @foreach ($anggotaTim as $namaPj)
                                            <option value="{{ $namaPj }}">{{ $namaPj }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-black">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Input 3: Bukti Perbaikan (Opsional) --}}
                        <div class="space-y-1.5">
                            <label for="inputBukti" class="block text-xs font-semibold text-black">
                                Bukti perbaikan <span class="text-black/60 font-normal">(opsional · tautan dokumen)</span>
                            </label>
                            <input type="url"
                                id="inputBukti"
                                name="bukti_url"
                                placeholder="https://drive.google.com/..."
                                class="w-full bg-white border border-zinc-300 rounded-md h-11 px-4 text-sm text-black placeholder:text-black/40 focus:outline-sky-800 transition-colors">
                        </div>

                        {{-- Input 4: Status Penyelesaian --}}
                        <div class="space-y-1.5">
                            <label for="inputStatus" class="block text-xs font-semibold text-black">
                                Status penyelesaian
                            </label>
                            <div class="relative">
                                <select id="inputStatus"
                                    name="status"
                                    class="w-full bg-white border border-zinc-300 rounded-md h-11 px-3.5 pr-8 text-sm text-black appearance-none focus:outline-sky-800 transition-colors cursor-pointer">
                                    <option value="menunggu_tanggapan">Menunggu tanggapan</option>
                                    <option value="dalam_perbaikan">Dalam perbaikan</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-black">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-3 flex items-center justify-end gap-3">
                            <button type="submit"
                                name="action"
                                value="draft"
                                class="h-11 px-6 bg-zinc-300 hover:bg-zinc-400 text-black/75 text-base font-semibold rounded-md transition-colors duration-150 cursor-pointer">
                                Simpan draf
                            </button>
                            <button type="submit"
                                name="action"
                                value="submit"
                                class="h-11 px-6 bg-sky-800 hover:bg-sky-900 text-white text-base font-semibold rounded-md transition-colors duration-150 shadow-xs hover:shadow cursor-pointer">
                                Kirim tanggapan
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>

    </div>

    {{-- 6. KONTEN TAB 2: KEPUTUSAN RTM (Table View) --}}
    <div id="tabContentRtm" class="{{ ($activeTab ?? 'ami') === 'rtm' ? 'block' : 'hidden' }}">
        
        <div class="bg-white rounded-md border border-zinc-300 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[760px]">
                    <thead>
                        <tr class="bg-sky-950 text-white text-sm font-semibold">
                            <th scope="col" class="w-40 px-5 py-4">Tanggal rapat</th>
                            <th scope="col" class="px-5 py-4">Keputusan</th>
                            <th scope="col" class="w-64 px-5 py-4">Tindak lanjut</th>
                            <th scope="col" class="w-48 px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-300 text-sm">
                        @forelse ($keputusanRtm as $rtm)
                            @php
                                $statusBadge = match($rtm['status']) {
                                    'dalam_perbaikan' => 'bg-sky-800/10 text-sky-800',
                                    'menunggu_tanggapan' => 'bg-yellow-400/25 text-yellow-700',
                                    'selesai' => 'bg-green-700/10 text-green-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <tr class="odd:bg-white even:bg-slate-50/70 hover:bg-sky-50/30 transition-colors">
                                <td class="px-5 py-4 text-black text-sm whitespace-nowrap">
                                    {{ $rtm['tanggal_rapat'] }}
                                </td>
                                <td class="px-5 py-4 text-black text-sm font-medium leading-5">
                                    {{ $rtm['keputusan'] }}
                                </td>
                                <td class="px-5 py-4 text-black/60 text-sm leading-5">
                                    {{ $rtm['tindak_lanjut'] }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="h-7 px-3 rounded-full inline-flex items-center text-xs font-semibold {{ $statusBadge }}">
                                        {{ $rtm['status_label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-black/40">
                                    Belum ada data keputusan RTM untuk tahun ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

{{-- Interactive JavaScript for Tab Switching and Finding Selection --}}
<script>
    const temuanData = @json($temuanAmi);

    // Switch between Temuan AMI and Keputusan RTM tabs
    function switchTab(tabName) {
        const tabBtnAmi = document.getElementById('tabBtnAmi');
        const tabBtnRtm = document.getElementById('tabBtnRtm');
        const tabContentAmi = document.getElementById('tabContentAmi');
        const tabContentRtm = document.getElementById('tabContentRtm');

        if (tabName === 'ami') {
            tabBtnAmi.className = 'tab-btn pb-3 text-sm font-semibold transition-all duration-150 cursor-pointer border-b-2 text-sky-800 border-sky-800';
            tabBtnRtm.className = 'tab-btn pb-3 text-sm font-semibold transition-all duration-150 cursor-pointer border-b-2 text-black/60 border-transparent hover:text-sky-800';
            tabContentAmi.classList.remove('hidden');
            tabContentRtm.classList.add('hidden');
        } else {
            tabBtnRtm.className = 'tab-btn pb-3 text-sm font-semibold transition-all duration-150 cursor-pointer border-b-2 text-sky-800 border-sky-800';
            tabBtnAmi.className = 'tab-btn pb-3 text-sm font-semibold transition-all duration-150 cursor-pointer border-b-2 text-black/60 border-transparent hover:text-sky-800';
            tabContentRtm.classList.remove('hidden');
            tabContentAmi.classList.add('hidden');
        }
    }

    // Select a Finding Card on the left and update the form on the right
    function selectFinding(index) {
        const item = temuanData[index];
        if (!item) return;

        // 1. Update Card Outlines
        const cards = document.querySelectorAll('.finding-card');
        cards.forEach((card, i) => {
            if (i === index) {
                card.className = 'finding-card p-4 rounded-md transition-all duration-150 cursor-pointer bg-sky-800/5 outline outline-2 outline-offset-[-2px] outline-sky-800';
            } else {
                card.className = 'finding-card p-4 rounded-md transition-all duration-150 cursor-pointer bg-white border border-zinc-300 hover:border-sky-800/40';
            }
        });

        // 2. Update Detail Box
        document.getElementById('formFindingId').value = item.id;
        document.getElementById('detailStandar').textContent = item.judul;
        document.getElementById('detailDeskripsi').textContent = item.deskripsi;
        document.getElementById('detailRekomendasi').textContent = item.rekomendasi;

        // 3. Update Badge in Form Header
        const badgeEl = document.getElementById('detailBadge');
        badgeEl.textContent = item.status_label;
        if (item.status === 'menunggu_tanggapan') {
            badgeEl.className = 'h-7 px-3 bg-yellow-400/25 text-yellow-700 rounded-full flex items-center text-xs font-semibold';
        } else if (item.status === 'dalam_perbaikan') {
            badgeEl.className = 'h-7 px-3 bg-sky-800/10 text-sky-800 rounded-full flex items-center text-xs font-semibold';
        } else {
            badgeEl.className = 'h-7 px-3 bg-green-700/10 text-green-700 rounded-full flex items-center text-xs font-semibold';
        }

        // 4. Update Form Inputs
        document.getElementById('inputTanggapan').value = item.tanggapan || '';
        document.getElementById('inputRencana').value = item.rencana_perbaikan || '';
        document.getElementById('inputTarget').value = item.target_selesai || '';
        document.getElementById('inputPj').value = item.penanggung_jawab || '';
        document.getElementById('inputBukti').value = item.bukti_url || '';
        document.getElementById('inputStatus').value = item.status;
    }
</script>
@endsection
