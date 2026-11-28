@extends($isReadOnly ? 'layouts.guest' : 'layouts.app')

@section('title', 'Rekapitulasi Dokumen Evidence')

@section('content')
<div class="max-w-[1120px] mx-auto space-y-6 pb-16 font-['Plus_Jakarta_Sans']">

    {{-- 1. Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2.5">
                <h2 class="text-2xl sm:text-3xl font-bold text-black tracking-tight">
                    Rekapitulasi Dokumen Evidence
                </h2>
                @if(!empty($isSimulation))
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        Simulasi
                    </span>
                @endif
                @if($isReadOnly)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        Mode Asesor Eksternal (Read-Only)
                    </span>
                @endif
            </div>
            <p class="text-base sm:text-lg text-black/80 font-normal leading-7">
                Ringkasan dokumen 4 pilar penjaminan mutu program studi untuk keperluan verifikasi asesor.
            </p>
        </div>

        @if(!$isReadOnly)
            <button type="button" onclick="copyPublicLink('{{ $publicUrl }}')"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-sky-900 hover:bg-sky-950 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer shrink-0 self-start sm:self-auto">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                </svg>
                <span>Salin Token Akses Asesor Eksternal</span>
            </button>
        @endif
    </div>

    {{-- Error State Banner --}}
    @if(!empty($apiError))
        <div role="alert" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs sm:text-sm text-amber-800 flex items-center gap-2.5">
            <svg class="size-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span>{{ $apiError }}</span>
        </div>
    @endif

    {{-- Toast Notification --}}
    <div id="copyToast" class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-2.5 text-xs sm:text-sm font-semibold transition-opacity opacity-0 pointer-events-none">
        <svg class="size-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        <span>Link Read-Only Asesor Berhasil Disalin ke Clipboard!</span>
    </div>

    {{-- 2. Project Akreditasi Selector Card (Desain persis card evidence spmi) --}}
    <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 sm:p-6 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="w-full sm:w-auto flex-1 min-w-0 max-w-3xl space-y-1.5">
            <label for="projectSelector" class="block text-gray-500 text-[11px] font-bold tracking-wider uppercase">
                PILIH PROJECT AKREDITASI
            </label>
            <div class="relative w-full sm:w-fit max-w-full">
                @if(!$isReadOnly)
                    <select id="projectSelector"
                        onchange="adjustProjectSelectorWidth(); window.location.href='{{ route('spmi.overview') }}/' + this.value"
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
                @else
                    <div class="h-11 px-4 bg-gray-50/80 text-primary font-bold text-xs sm:text-sm rounded-xl border border-gray-200 flex items-center">
                        {{ $activeProject['nama_project'] ?? 'Project Akreditasi' }} ({{ $activeProject['tahun_target_submit'] ?? 2026 }})
                    </div>
                @endif
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

    {{-- 3. Section: 4 Pilar Dokumen Mutu Institusi (SPMI) --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900">
                    Dokumen Mutu Institusi (4 Pilar Evidence SPMI)
                </h3>
                <p class="text-xs sm:text-sm text-slate-500">
                    Tautan berkas Google Drive laporan evaluasi, rapat tinjauan manajemen, capaian IKU, dan SOP implementasi.
                </p>
            </div>
            @if(!$isReadOnly)
                <a href="{{ route('spmi.evidence', ['project_id' => $selectedProjectId]) }}"
                    class="text-xs font-bold text-sky-800 hover:text-sky-950 inline-flex items-center gap-1">
                    <span>Kelola Dokumen</span>
                    <span>&rarr;</span>
                </a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            {{-- Pilar 1: Bukti Evaluasi --}}
            @php
                $p1 = $evidenceData['bukti_evaluasi'] ?? [];
                $hasP1 = !empty($p1['link_dokumen']) || !empty($p1['deskripsi']);
            @endphp
            <div class="bg-white rounded-xl border border-zinc-200 p-5 shadow-xs flex flex-col justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            Pilar 1 &bull; Evaluasi Mutu
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold {{ $hasP1 ? 'text-emerald-700' : 'text-slate-400' }}">
                            <span class="size-2 rounded-full {{ $hasP1 ? 'bg-emerald-500' : 'bg-zinc-300' }}"></span>
                            <span>{{ $hasP1 ? 'Terisi' : 'Belum Ada' }}</span>
                        </span>
                    </div>
                    <h4 class="text-base font-bold text-slate-900">Laporan Audit Mutu Internal (AMI & Monev)</h4>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        {{ !empty($p1['deskripsi']) ? $p1['deskripsi'] : 'Laporan hasil Audit Mutu Internal (AMI), analisis gap kurikulum, dan evaluasi capaian standar prodi.' }}
                    </p>
                </div>
                <div>
                    @if(!empty($p1['link_dokumen']))
                        <a href="{{ $p1['link_dokumen'] }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 text-sky-800 border border-zinc-200 rounded-lg text-xs font-bold transition-colors">
                            <span>Buka Berkas Google Drive</span>
                            <span>↗</span>
                        </a>
                    @else
                        <span class="text-xs font-medium text-slate-400 italic">Belum ada tautan Google Drive</span>
                    @endif
                </div>
            </div>

            {{-- Pilar 2: Pengendalian --}}
            @php
                $p2 = $evidenceData['bukti_pengendalian'] ?? [];
                $hasP2 = !empty($p2['link_dokumen']) || !empty($p2['deskripsi']);
            @endphp
            <div class="bg-white rounded-xl border border-zinc-200 p-5 shadow-xs flex flex-col justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Pilar 2 &bull; Pengendalian Mutu
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold {{ $hasP2 ? 'text-emerald-700' : 'text-slate-400' }}">
                            <span class="size-2 rounded-full {{ $hasP2 ? 'bg-emerald-500' : 'bg-zinc-300' }}"></span>
                            <span>{{ $hasP2 ? 'Terisi' : 'Belum Ada' }}</span>
                        </span>
                    </div>
                    <h4 class="text-base font-bold text-slate-900">Rapat Tinjauan Manajemen (RTM & RTL)</h4>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        {{ !empty($p2['deskripsi']) ? $p2['deskripsi'] : 'Notulensi RTM, dokumen Rencana Tindak Lanjut (RTL), dan bukti perbaikan berkelanjutan siklus PPEPP.' }}
                    </p>
                </div>
                <div>
                    @if(!empty($p2['link_dokumen']))
                        <a href="{{ $p2['link_dokumen'] }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 text-sky-800 border border-zinc-200 rounded-lg text-xs font-bold transition-colors">
                            <span>Buka Berkas Google Drive</span>
                            <span>↗</span>
                        </a>
                    @else
                        <span class="text-xs font-medium text-slate-400 italic">Belum ada tautan Google Drive</span>
                    @endif
                </div>
            </div>

            {{-- Pilar 3: Dampak Tridharma --}}
            @php
                $p3 = $evidenceData['bukti_dampak_tridharma'] ?? [];
                $hasP3 = !empty($p3['link_dokumen']) || !empty($p3['deskripsi']);
            @endphp
            <div class="bg-white rounded-xl border border-zinc-200 p-5 shadow-xs flex flex-col justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Pilar 3 &bull; Dampak Tridharma
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold {{ $hasP3 ? 'text-emerald-700' : 'text-slate-400' }}">
                            <span class="size-2 rounded-full {{ $hasP3 ? 'bg-emerald-500' : 'bg-zinc-300' }}"></span>
                            <span>{{ $hasP3 ? 'Terisi' : 'Belum Ada' }}</span>
                        </span>
                    </div>
                    <h4 class="text-base font-bold text-slate-900">Capaian IKU & Survei Kepuasan Stakeholder</h4>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        {{ !empty($p3['deskripsi']) ? $p3['deskripsi'] : 'Rekapitulasi publikasi ilmiah dosen/mahasiswa, HKI, hasil tracer study, dan survei kepuasan mitra pengguna.' }}
                    </p>
                </div>
                <div>
                    @if(!empty($p3['link_dokumen']))
                        <a href="{{ $p3['link_dokumen'] }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 text-sky-800 border border-zinc-200 rounded-lg text-xs font-bold transition-colors">
                            <span>Buka Berkas Google Drive</span>
                            <span>↗</span>
                        </a>
                    @else
                        <span class="text-xs font-medium text-slate-400 italic">Belum ada tautan Google Drive</span>
                    @endif
                </div>
            </div>

            {{-- Pilar 4: Dokumen Implementasi --}}
            @php
                $p4 = $evidenceData['dokumen_implementasi'] ?? [];
                $hasP4 = !empty($p4['link_dokumen']) || !empty($p4['deskripsi']);
            @endphp
            <div class="bg-white rounded-xl border border-zinc-200 p-5 shadow-xs flex flex-col justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-sky-50 text-sky-800 border border-sky-200">
                            Pilar 4 &bull; Implementasi Mutu
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold {{ $hasP4 ? 'text-emerald-700' : 'text-slate-400' }}">
                            <span class="size-2 rounded-full {{ $hasP4 ? 'bg-emerald-500' : 'bg-zinc-300' }}"></span>
                            <span>{{ $hasP4 ? 'Terisi' : 'Belum Ada' }}</span>
                        </span>
                    </div>
                    <h4 class="text-base font-bold text-slate-900">Buku Kebijakan SPMI, Manual Mutu & SOP</h4>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        {{ !empty($p4['deskripsi']) ? $p4['deskripsi'] : 'Buku standar mutu institusi, manual mutu PPEPP, SOP akademik harian, dan petunjuk operasional lab.' }}
                    </p>
                </div>
                <div>
                    @if(!empty($p4['link_dokumen']))
                        <a href="{{ $p4['link_dokumen'] }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 text-sky-800 border border-zinc-200 rounded-lg text-xs font-bold transition-colors">
                            <span>Buka Berkas Google Drive</span>
                            <span>↗</span>
                        </a>
                    @else
                        <span class="text-xs font-medium text-slate-400 italic">Belum ada tautan Google Drive</span>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- 4. Daftar Indikator: Sudah & Belum Memiliki Evidence --}}
    @php
        $withEv = $indicatorsWithEvidence ?? [];
        $withoutEv = $indicatorsWithoutEvidence ?? [];
        $stats = $indicatorStats ?? ['total' => 0, 'terisi' => 0, 'persen' => 0];
    @endphp
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-lg font-bold text-slate-900">
                    Daftar Indikator &amp; Status Bukti Dukung
                </h3>
                <p class="text-xs sm:text-sm text-slate-500">
                    Pemisahan indikator yang sudah memiliki tautan Google Drive, belum diisi, dan yang berstatus Data tidak tersedia.
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 self-start sm:self-auto">
                {{ $stats['terisi'] }} / {{ $stats['total'] }} Indikator Terisi ({{ $stats['persen'] }}%)
            </span>
        </div>

        @if(empty($withEv) && empty($withoutEv))
            <div class="bg-white rounded-2xl border border-dashed border-zinc-300 p-8 text-center">
                <p class="text-sm font-bold text-slate-600">Belum ada data indikator pada project ini.</p>
                <p class="text-xs text-slate-400 mt-1">Data indikator akan muncul setelah plotting tim atau sinkronisasi API selesai.</p>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Kolom 1: Indikator Sudah Memiliki Evidence --}}
                <div class="bg-white rounded-2xl border border-zinc-200 p-5 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
                        <h4 class="text-sm font-bold text-emerald-800 flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-emerald-500"></span>
                            <span>Sudah Memiliki Evidence ({{ count($withEv) }})</span>
                        </h4>
                    </div>
                    <div class="space-y-3">
                        @forelse($withEv as $ind)
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-zinc-200/80 space-y-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-extrabold bg-sky-100 text-sky-800">
                                        {{ $ind['kode_indikator'] }}
                                    </span>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if(!empty($ind['is_simulation']))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Simulasi</span>
                                        @endif
                                        @if(!empty($ind['is_readonly']))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">Read-Only (UPPS)</span>
                                        @endif
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Tersedia</span>
                                    </div>
                                </div>
                                <p class="text-xs font-semibold text-slate-800 leading-snug">{{ $ind['deskripsi_indikator'] }}</p>
                                <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                                    <span class="text-[11px] text-slate-500">PIC: <strong>{{ $ind['nama_dosen'] }}</strong></span>
                                    <a href="{{ $ind['link_evidence'] }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white hover:bg-sky-50 text-sky-800 border border-zinc-200 text-xs font-bold transition-colors">
                                        <span>Buka Drive</span>
                                        <span>↗</span>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-xs text-slate-400">Belum ada indikator yang memiliki tautan evidence.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Kolom 2: Indikator Belum Memiliki Evidence --}}
                <div class="bg-white rounded-2xl border border-zinc-200 p-5 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
                        <h4 class="text-sm font-bold text-amber-800 flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-amber-500"></span>
                            <span>Belum Memiliki Evidence ({{ count($withoutEv) }})</span>
                        </h4>
                    </div>
                    <div class="space-y-3">
                        @forelse($withoutEv as $ind)
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-zinc-200/80 space-y-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-extrabold bg-slate-200 text-slate-700">
                                        {{ $ind['kode_indikator'] }}
                                    </span>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if(!empty($ind['is_simulation']))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Simulasi</span>
                                        @endif
                                        @if(empty($ind['data_available']))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Data tidak tersedia</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Belum diisi</span>
                                        @endif
                                        @if(!empty($ind['is_readonly']))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">Read-Only</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-xs font-semibold text-slate-800 leading-snug">{{ $ind['deskripsi_indikator'] }}</p>
                                <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                                    <span class="text-[11px] text-slate-500">PIC: <strong>{{ $ind['nama_dosen'] }}</strong></span>
                                    <span class="text-[11px] text-slate-400 italic">
                                        {{ empty($ind['data_available']) ? 'Data tidak tersedia' : 'Menunggu tautan Google Drive' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-xs text-emerald-600 font-semibold">Seluruh indikator sudah memiliki tautan evidence.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>

{{-- Interactive JavaScript --}}
<script>
    function copyPublicLink(url) {
        if (!url) return;
        navigator.clipboard.writeText(url).then(() => {
            const toast = document.getElementById('copyToast');
            if (!toast) return;
            toast.classList.remove('opacity-0', 'pointer-events-none');
            toast.classList.add('opacity-100');
            setTimeout(() => {
                toast.classList.remove('opacity-100');
                toast.classList.add('opacity-0', 'pointer-events-none');
            }, 3000);
        }).catch(err => {
            prompt('Salin tautan asesor berikut:', url);
        });
    }

    function adjustProjectSelectorWidth() {
        const sel = document.getElementById('projectSelector');
        if (!sel) return;

        const selectedOption = sel.options[sel.selectedIndex];
        const text = selectedOption ? selectedOption.text : sel.value;

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

        const totalPadding = 76;
        const requiredWidth = textWidth + totalPadding;

        const parentWidth = sel.parentElement?.parentElement?.clientWidth || window.innerWidth;
        if (window.innerWidth < 640) {
            sel.style.width = '100%';
        } else {
            sel.style.width = `${Math.min(requiredWidth, Math.max(parentWidth - 10, 360))}px`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        adjustProjectSelectorWidth();
    });

    window.addEventListener('resize', adjustProjectSelectorWidth);

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(adjustProjectSelectorWidth);
    }
</script>
@endsection
