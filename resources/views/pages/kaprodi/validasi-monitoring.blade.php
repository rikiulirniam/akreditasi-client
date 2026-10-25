@extends('layouts.app')

@section('title', 'Validasi & Monitoring')

@section('content')
<div class="space-y-6 max-w-[1098px] mx-auto">

    {{-- Page Header --}}
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-black tracking-tight">
            Validasi & Monitoring Formulir
        </h2>
        <p class="text-base sm:text-lg text-black mt-2 leading-7">
            Periksa narasi dan bukti dokumen dari tim, lalu kirim project ke Reviewer setelah semuanya siap.
        </p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Siap Ditinjau --}}
        <div class="bg-white rounded-xl shadow-md border border-[#EAEAEA] p-5 flex items-start gap-4 hover:shadow-lg transition-shadow duration-200">
            <div class="w-11 h-11 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p id="statSiapDitinjau" class="text-3xl font-bold text-primary leading-tight">{{ $summary['siap_ditinjau'] }}</p>
                <p class="text-xs font-bold text-black tracking-wider uppercase mt-1">Siap Ditinjau</p>
            </div>
        </div>

        {{-- Menunggu Validasi --}}
        <div class="bg-white rounded-xl shadow-md border border-[#EAEAEA] p-5 flex items-start gap-4 hover:shadow-lg transition-shadow duration-200">
            <div class="w-11 h-11 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p id="statMenungguValidasi" class="text-3xl font-bold text-primary leading-tight">{{ $summary['menunggu_validasi'] }}</p>
                <p class="text-xs font-bold text-black/60 tracking-wider uppercase mt-1">Menunggu Validasi</p>
            </div>
        </div>

        {{-- Perlu Revisi --}}
        <div class="bg-white rounded-xl shadow-md border border-[#EAEAEA] p-5 flex items-start gap-4 hover:shadow-lg transition-shadow duration-200">
            <div class="w-11 h-11 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
            </div>
            <div class="min-w-0">
                <p id="statPerluRevisi" class="text-3xl font-bold text-primary leading-tight">{{ $summary['perlu_revisi'] }}</p>
                <p class="text-xs font-bold text-black/60 tracking-wider uppercase mt-1">Perlu Revisi</p>
            </div>
        </div>

        {{-- Belum Lengkap --}}
        <div class="bg-white rounded-xl shadow-md border border-[#EAEAEA] p-5 flex items-start gap-4 hover:shadow-lg transition-shadow duration-200">
            <div class="w-11 h-11 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p id="statBelumLengkap" class="text-3xl font-bold text-primary leading-tight">{{ $summary['belum_lengkap'] }}</p>
                <p class="text-xs font-bold text-black/60 tracking-wider uppercase mt-1">Belum Lengkap</p>
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div>
        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
            @php
                $progressPercent = $total > 0 ? ($summary['siap_ditinjau'] / $total) * 100 : 0;
            @endphp
            <div id="progressBarFill" class="h-2 bg-primary rounded-full transition-all duration-500 ease-out" style="width: {{ $progressPercent }}%"></div>
        </div>
        <p class="text-xs mt-2">
            <span id="progressReadyCount" class="text-green-700 font-bold">{{ $summary['siap_ditinjau'] }} dari {{ $total }}</span>
            <span class="text-black/60"> indikator siap ditinjau Reviewer</span>
        </p>
    </div>

    {{-- Main Content: Split Panel --}}
    <div class="bg-white rounded-xl border border-zinc-300 overflow-hidden flex flex-col" style="min-height: 620px;">
        <div class="flex-1 flex flex-col lg:flex-row min-h-0">

            {{-- Left Panel: Filter Tabs + Indicator List --}}
            <div class="w-full lg:w-[55%] xl:w-[50%] p-5 flex flex-col border-b lg:border-b-0 lg:border-r border-zinc-200 min-h-0">

                {{-- Filter Tabs --}}
                <div class="flex flex-wrap gap-2 mb-4" id="filterTabs">
                    @php
                        $filters = [
                            'semua' => 'Semua',
                            'siap_ditinjau' => 'Siap ditinjau',
                            'menunggu_validasi' => 'Menunggu validasi',
                            'perlu_revisi' => 'Perlu revisi',
                            'belum_lengkap' => 'Belum lengkap',
                        ];
                    @endphp

                    @foreach ($filters as $key => $label)
                        <button
                            type="button"
                            data-filter="{{ $key }}"
                            class="filter-tab h-8 px-3.5 rounded-full text-xs font-semibold transition-all duration-200 cursor-pointer border
                                {{ $activeFilter === $key
                                    ? 'bg-primary text-white border-primary'
                                    : 'bg-white text-black/60 border-zinc-300 hover:border-primary/40 hover:text-primary' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Indicator List --}}
                <div class="flex-1 overflow-y-auto space-y-1.5 pr-1" id="indikatorList">
                    @forelse ($indikators as $index => $ind)
                        <button
                            type="button"
                            data-index="{{ $index }}"
                            data-kode="{{ $ind['kode'] }}"
                            class="indikator-item w-full text-left p-3 rounded-lg transition-all duration-150 cursor-pointer flex items-center gap-3 hover:bg-primary/5 group border border-transparent"
                        >
                            {{-- Kode --}}
                            <div class="w-10 shrink-0">
                                <span class="text-xs font-bold text-primary leading-5">{{ $ind['kode'] }}</span>
                            </div>

                            {{-- Nama & PIC --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-black truncate">{{ $ind['nama'] }}</p>
                                <p class="text-xs text-black/60 mt-0.5">PIC: {{ $ind['pic'] }}</p>
                            </div>

                            {{-- Status Badge --}}
                            @php
                                $statusConfig = match ($ind['status']) {
                                    'siap_ditinjau' => ['bg' => 'bg-green-700/10', 'text' => 'text-green-700', 'label' => 'Siap ditinjau'],
                                    'menunggu_validasi' => ['bg' => 'bg-primary/10', 'text' => 'text-primary', 'label' => 'Menunggu validasi'],
                                    'perlu_revisi' => ['bg' => 'bg-red-700/10', 'text' => 'text-red-700', 'label' => 'Perlu revisi'],
                                    'belum_lengkap' => ['bg' => 'bg-yellow-400/25', 'text' => 'text-yellow-700', 'label' => 'Belum lengkap'],
                                    default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-500', 'label' => $ind['status']],
                                };
                            @endphp
                            <span class="indikator-badge h-7 px-3 rounded-full flex items-center shrink-0 {{ $statusConfig['bg'] }}">
                                <span class="text-xs font-semibold {{ $statusConfig['text'] }} whitespace-nowrap">{{ $statusConfig['label'] }}</span>
                            </span>
                        </button>
                    @empty
                        <div class="flex-1 flex items-center justify-center py-12">
                            <p class="text-sm text-black/40">Tidak ada indikator ditemukan untuk filter ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Right Panel: Detail View --}}
            <div class="w-full lg:w-[45%] xl:w-[50%] bg-slate-50/60 p-5 flex flex-col min-h-0" id="detailPanel">

                {{-- Empty State (default) --}}
                <div id="detailEmpty" class="flex-1 flex items-center justify-center">
                    <div class="text-center max-w-[260px]">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-primary/5 flex items-center justify-center">
                            <svg class="w-8 h-8 text-primary/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <p class="text-sm text-black/60 leading-5">Pilih indikator di kiri untuk memeriksa narasi dan buktinya.</p>
                    </div>
                </div>

                {{-- Detail Content (hidden initially, shown via JS) --}}
                <div id="detailContent" class="flex-1 flex-col min-h-0 hidden">

                    {{-- Detail Header --}}
                    <div class="pb-4 border-b border-zinc-200 mb-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span id="detailKode" class="text-sm font-bold text-primary"></span>
                            <span id="detailStatusBadge" class="h-6 px-2.5 rounded-full text-[11px] font-semibold inline-flex items-center"></span>
                        </div>
                        <h3 id="detailNama" class="text-lg font-bold text-black"></h3>
                        <p id="detailPic" class="text-xs text-black/50 mt-1"></p>
                    </div>

                    {{-- Detail Body --}}
                    <div class="flex-1 overflow-y-auto space-y-5 pr-1">

                        {{-- Narasi Section --}}
                        <div>
                            <h4 class="text-xs font-bold text-black/50 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                                </svg>
                                Narasi
                            </h4>
                            <div id="detailNarasi" class="text-sm text-black/80 leading-relaxed bg-white rounded-lg p-4 border border-zinc-200"></div>
                        </div>

                        {{-- Bukti Section --}}
                        <div>
                            <h4 class="text-xs font-bold text-black/50 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                </svg>
                                Bukti Dokumen
                            </h4>
                            <div id="detailBukti" class="space-y-2"></div>
                        </div>

                        {{-- Revision Notes Section (shown only for perlu_revisi) --}}
                        <div id="detailRevisiContainer" class="hidden">
                            <h4 class="text-xs font-bold text-red-600 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                                Catatan Revisi
                            </h4>
                            <div id="detailRevisi" class="text-sm text-red-700 leading-relaxed bg-red-50 rounded-lg p-4 border border-red-200"></div>
                        </div>

                        {{-- Validation Actions --}}
                        <div id="detailActions" class="hidden pt-2">
                            <div class="flex flex-wrap gap-2.5">
                                <button
                                    type="button"
                                    id="btnSetujui"
                                    class="h-10 px-5 bg-green-700 text-white text-sm font-semibold rounded-lg hover:bg-green-800 active:scale-95 transition-all duration-150 cursor-pointer flex items-center gap-2 shadow-xs"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <span id="btnSetujuiText">Setujui</span>
                                </button>
                                <button
                                    type="button"
                                    id="btnMintaRevisi"
                                    class="h-10 px-5 bg-white text-red-700 text-sm font-semibold rounded-lg border border-red-300 hover:bg-red-50 active:scale-95 transition-all duration-150 cursor-pointer flex items-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                    <span>Minta Revisi</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Bar: Submit to Reviewer --}}
        @php
            $belumSiap = $total - $summary['siap_ditinjau'];
            $isReady = $belumSiap === 0;
        @endphp
        <div class="shrink-0 px-5 py-4 bg-white border-t border-zinc-300 flex items-center justify-between shadow-[0px_-8px_24px_-12px_rgba(0,0,0,0.12)]">
            <div id="bottomBarStatus">
                @if ($isReady)
                    <p class="text-sm">
                        <span class="font-bold text-green-700">Semua indikator siap!</span>
                        <span class="text-black/60"> · siap dikirim ke Reviewer</span>
                    </p>
                @else
                    <p class="text-sm">
                        <span class="font-bold text-black">{{ $belumSiap }} indikator belum siap</span>
                        <span class="text-black/60"> · semua harus siap sebelum dikirim</span>
                    </p>
                @endif
            </div>
            <button
                type="button"
                id="btnKirimReviewer"
                class="h-11 px-6 rounded-lg text-base font-semibold text-white transition-all duration-200 cursor-pointer
                    {{ $isReady
                        ? 'bg-primary hover:bg-additional shadow-md hover:shadow-lg active:scale-[0.98]'
                        : 'bg-primary/30 cursor-not-allowed' }}"
                {{ $isReady ? '' : 'disabled' }}
            >
                Kirim ke Reviewer
            </button>
        </div>
    </div>
</div>

{{-- Modal Minta Revisi --}}
<div id="modalRevisi" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-zinc-200 transform transition-all scale-100">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-black">Catatan Permintaan Revisi</h3>
                    <p class="text-xs text-black/60 mt-0.5" id="modalRevisiSubtitle">Indikator</p>
                </div>
            </div>
            <button type="button" id="btnBatalRevisiX" class="text-black/40 hover:text-black cursor-pointer p-1 rounded-lg hover:bg-zinc-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="py-4">
            <label for="catatanRevisiInput" class="block text-xs font-bold text-black uppercase tracking-wider mb-2">
                Catatan Revisi / Dokumen yang Perlu Dilengkapi <span class="text-red-500">*</span>
            </label>
            <textarea
                id="catatanRevisiInput"
                rows="4"
                class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-sm text-black focus:outline-hidden focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
                placeholder="Tuliskan catatan perbaikan atau dokumen bukti yang perlu dilengkapi oleh PIC..."
            ></textarea>
            <p id="catatanRevisiError" class="text-xs text-red-600 mt-1.5 hidden">Harap isi catatan revisi terlebih dahulu.</p>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-zinc-100">
            <button
                type="button"
                id="btnBatalRevisi"
                class="h-9 px-4 rounded-lg border border-zinc-300 text-xs font-semibold text-black/70 hover:bg-zinc-50 transition-colors cursor-pointer"
            >
                Batal
            </button>
            <button
                type="button"
                id="btnSubmitRevisi"
                class="h-9 px-4 rounded-lg bg-red-700 text-xs font-semibold text-white hover:bg-red-800 transition-colors shadow-xs cursor-pointer flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                </svg>
                <span id="btnSubmitRevisiText">Kirim Revisi</span>
            </button>
        </div>
    </div>
</div>

{{-- Toast Notification --}}
<div id="toastNotification" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium bg-white">
    <div id="toastIcon" class="w-6 h-6 rounded-full flex items-center justify-center shrink-0"></div>
    <span id="toastMessage" class="text-black/80"></span>
</div>

{{-- JavaScript for Indicator Selection, Filter, and Status Actions --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Indicator data passed from PHP
    const allIndikators = @json($allIndikators ?? []);
    const indikators = @json($indikators);
    let selectedIndex = null;

    // Elements
    const detailEmpty = document.getElementById('detailEmpty');
    const detailContent = document.getElementById('detailContent');
    const detailKode = document.getElementById('detailKode');
    const detailNama = document.getElementById('detailNama');
    const detailPic = document.getElementById('detailPic');
    const detailStatusBadge = document.getElementById('detailStatusBadge');
    const detailNarasi = document.getElementById('detailNarasi');
    const detailBukti = document.getElementById('detailBukti');
    const detailRevisiContainer = document.getElementById('detailRevisiContainer');
    const detailRevisi = document.getElementById('detailRevisi');
    const detailActions = document.getElementById('detailActions');

    // Action buttons & modals
    const btnSetujui = document.getElementById('btnSetujui');
    const btnSetujuiText = document.getElementById('btnSetujuiText');
    const btnMintaRevisi = document.getElementById('btnMintaRevisi');
    const modalRevisi = document.getElementById('modalRevisi');
    const modalRevisiSubtitle = document.getElementById('modalRevisiSubtitle');
    const catatanRevisiInput = document.getElementById('catatanRevisiInput');
    const catatanRevisiError = document.getElementById('catatanRevisiError');
    const btnBatalRevisi = document.getElementById('btnBatalRevisi');
    const btnBatalRevisiX = document.getElementById('btnBatalRevisiX');
    const btnSubmitRevisi = document.getElementById('btnSubmitRevisi');
    const btnSubmitRevisiText = document.getElementById('btnSubmitRevisiText');

    // Summary & Bottom elements
    const statSiapDitinjau = document.getElementById('statSiapDitinjau');
    const statMenungguValidasi = document.getElementById('statMenungguValidasi');
    const statPerluRevisi = document.getElementById('statPerluRevisi');
    const statBelumLengkap = document.getElementById('statBelumLengkap');
    const progressBarFill = document.getElementById('progressBarFill');
    const progressReadyCount = document.getElementById('progressReadyCount');
    const bottomBarStatus = document.getElementById('bottomBarStatus');
    const btnKirimReviewer = document.getElementById('btnKirimReviewer');

    const statusStyles = {
        siap_ditinjau: { bg: 'bg-green-700/10', text: 'text-green-700', label: 'Siap ditinjau' },
        menunggu_validasi: { bg: 'bg-primary/10', text: 'text-primary', label: 'Menunggu validasi' },
        perlu_revisi: { bg: 'bg-red-700/10', text: 'text-red-700', label: 'Perlu revisi' },
        belum_lengkap: { bg: 'bg-yellow-400/25', text: 'text-yellow-700', label: 'Belum lengkap' },
    };

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toastNotification');
        const toastIcon = document.getElementById('toastIcon');
        const toastMessage = document.getElementById('toastMessage');

        toastMessage.textContent = message;

        if (type === 'success') {
            toast.className = 'fixed bottom-6 right-6 z-50 transform translate-y-0 opacity-100 transition-all duration-300 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border border-green-200 bg-white text-sm font-medium';
            toastIcon.className = 'w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center shrink-0';
            toastIcon.innerHTML = `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>`;
        } else if (type === 'warning') {
            toast.className = 'fixed bottom-6 right-6 z-50 transform translate-y-0 opacity-100 transition-all duration-300 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border border-amber-200 bg-white text-sm font-medium';
            toastIcon.className = 'w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0';
            toastIcon.innerHTML = `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>`;
        } else {
            toast.className = 'fixed bottom-6 right-6 z-50 transform translate-y-0 opacity-100 transition-all duration-300 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border border-red-200 bg-white text-sm font-medium';
            toastIcon.className = 'w-6 h-6 rounded-full bg-red-100 text-red-700 flex items-center justify-center shrink-0';
            toastIcon.innerHTML = `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>`;
        }

        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-20', 'opacity-0');
        }, 3500);
    }

    function updateSummaryUI(summary, total) {
        if (!summary) return;

        if (statSiapDitinjau) statSiapDitinjau.textContent = summary.siap_ditinjau;
        if (statMenungguValidasi) statMenungguValidasi.textContent = summary.menunggu_validasi;
        if (statPerluRevisi) statPerluRevisi.textContent = summary.perlu_revisi;
        if (statBelumLengkap) statBelumLengkap.textContent = summary.belum_lengkap;

        const totalItems = total || {{ $total }};
        const percent = totalItems > 0 ? (summary.siap_ditinjau / totalItems) * 100 : 0;

        if (progressBarFill) {
            progressBarFill.style.width = percent + '%';
        }

        if (progressReadyCount) {
            progressReadyCount.textContent = `${summary.siap_ditinjau} dari ${totalItems}`;
        }

        const belumSiap = totalItems - summary.siap_ditinjau;
        const isReady = belumSiap === 0;

        if (bottomBarStatus) {
            if (isReady) {
                bottomBarStatus.innerHTML = `
                    <p class="text-sm">
                        <span class="font-bold text-green-700">Semua indikator siap!</span>
                        <span class="text-black/60"> · siap dikirim ke Reviewer</span>
                    </p>
                `;
            } else {
                bottomBarStatus.innerHTML = `
                    <p class="text-sm">
                        <span class="font-bold text-black">${belumSiap} indikator belum siap</span>
                        <span class="text-black/60"> · semua harus siap sebelum dikirim</span>
                    </p>
                `;
            }
        }

        if (btnKirimReviewer) {
            if (isReady) {
                btnKirimReviewer.disabled = false;
                btnKirimReviewer.className = 'h-11 px-6 rounded-lg text-base font-semibold text-white transition-all duration-200 cursor-pointer bg-primary hover:bg-additional shadow-md hover:shadow-lg active:scale-[0.98]';
            } else {
                btnKirimReviewer.disabled = true;
                btnKirimReviewer.className = 'h-11 px-6 rounded-lg text-base font-semibold text-white transition-all duration-200 bg-primary/30 cursor-not-allowed';
            }
        }
    }

    function updateListBadge(kode, newStatus) {
        const itemBtn = document.querySelector(`.indikator-item[data-kode="${kode}"]`);
        if (!itemBtn) return;

        const badge = itemBtn.querySelector('.indikator-badge');
        if (!badge) return;

        const s = statusStyles[newStatus] || statusStyles.belum_lengkap;
        badge.className = `indikator-badge h-7 px-3 rounded-full flex items-center shrink-0 ${s.bg}`;
        badge.innerHTML = `<span class="text-xs font-semibold ${s.text} whitespace-nowrap">${s.label}</span>`;
    }

    function showDetail(index) {
        const ind = indikators[index];
        if (!ind) return;

        selectedIndex = index;

        // Highlight active row
        document.querySelectorAll('.indikator-item').forEach((el, i) => {
            if (i === index) {
                el.classList.add('bg-primary/5', 'border-primary/20');
                el.classList.remove('border-transparent');
            } else {
                el.classList.remove('bg-primary/5', 'border-primary/20');
                el.classList.add('border-transparent');
            }
        });

        // Show content, hide empty state
        detailEmpty.classList.add('hidden');
        detailContent.classList.remove('hidden');
        detailContent.classList.add('flex');

        // Populate header
        detailKode.textContent = ind.kode;
        detailNama.textContent = ind.nama;
        detailPic.textContent = 'PIC: ' + ind.pic;

        // Status badge
        const s = statusStyles[ind.status] || statusStyles.belum_lengkap;
        detailStatusBadge.className = 'h-6 px-2.5 rounded-full text-[11px] font-semibold inline-flex items-center ' + s.bg + ' ' + s.text;
        detailStatusBadge.textContent = s.label;

        // Narasi
        if (ind.narasi) {
            detailNarasi.textContent = ind.narasi;
            detailNarasi.classList.remove('italic', 'text-black/40');
        } else {
            detailNarasi.textContent = 'Belum ada narasi yang ditulis.';
            detailNarasi.classList.add('italic', 'text-black/40');
        }

        // Bukti
        detailBukti.innerHTML = '';
        if (ind.bukti && ind.bukti.length > 0) {
            ind.bukti.forEach(b => {
                const el = document.createElement('a');
                el.href = b.url || '#';
                el.target = '_blank';
                el.className = 'flex items-center gap-3 p-3 bg-white rounded-lg border border-zinc-200 hover:border-primary/30 hover:bg-primary/[0.02] transition-all duration-150 group';
                el.innerHTML = `
                    <div class="w-9 h-9 rounded-lg bg-primary/5 flex items-center justify-center shrink-0 group-hover:bg-primary/10 transition-colors">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-black/80 group-hover:text-primary transition-colors truncate">${b.nama}</span>
                    <svg class="w-4 h-4 text-black/20 ml-auto shrink-0 group-hover:text-primary/50 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                `;
                detailBukti.appendChild(el);
            });
        } else {
            detailBukti.innerHTML = '<p class="text-sm italic text-black/40 bg-white rounded-lg p-4 border border-zinc-200">Belum ada dokumen bukti yang diunggah.</p>';
        }

        // Revision notes
        if (ind.status === 'perlu_revisi' && ind.catatan_revisi) {
            detailRevisiContainer.classList.remove('hidden');
            detailRevisi.textContent = ind.catatan_revisi;
        } else {
            detailRevisiContainer.classList.add('hidden');
        }

        // Actions (show only for menunggu_validasi status)
        if (ind.status === 'menunggu_validasi') {
            detailActions.classList.remove('hidden');
        } else {
            detailActions.classList.add('hidden');
        }
    }

    // Bind indicator item clicks
    document.querySelectorAll('.indikator-item').forEach(el => {
        el.addEventListener('click', function () {
            const index = parseInt(this.getAttribute('data-index'), 10);
            showDetail(index);
        });
    });

    // Handle "Setujui" Button Click
    if (btnSetujui) {
        btnSetujui.addEventListener('click', async function () {
            if (selectedIndex === null) return;
            const ind = indikators[selectedIndex];
            if (!ind || ind.status !== 'menunggu_validasi') return;

            btnSetujui.disabled = true;
            btnSetujuiText.textContent = 'Menyetujui...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch("{{ route('validasi-monitoring.update-status') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        kode: ind.kode,
                        status: 'siap_ditinjau',
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    ind.status = 'siap_ditinjau';
                    delete ind.catatan_revisi;

                    // Update UI
                    updateListBadge(ind.kode, 'siap_ditinjau');
                    const s = statusStyles.siap_ditinjau;
                    detailStatusBadge.className = 'h-6 px-2.5 rounded-full text-[11px] font-semibold inline-flex items-center ' + s.bg + ' ' + s.text;
                    detailStatusBadge.textContent = s.label;
                    detailActions.classList.add('hidden');
                    detailRevisiContainer.classList.add('hidden');

                    updateSummaryUI(data.summary, data.total);
                    showToast(data.message || `Indikator ${ind.kode} berhasil disetujui!`, 'success');
                } else {
                    showToast('Gagal menyetujui indikator. Silakan coba lagi.', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Terjadi kesalahan jaringan.', 'error');
            } finally {
                btnSetujui.disabled = false;
                btnSetujuiText.textContent = 'Setujui';
            }
        });
    }

    // Modal Minta Revisi Handlers
    function openModalRevisi() {
        if (selectedIndex === null) return;
        const ind = indikators[selectedIndex];
        if (!ind) return;

        modalRevisiSubtitle.textContent = `${ind.kode} - ${ind.nama}`;
        catatanRevisiInput.value = ind.catatan_revisi || '';
        catatanRevisiError.classList.add('hidden');
        modalRevisi.classList.remove('hidden');
        setTimeout(() => catatanRevisiInput.focus(), 50);
    }

    function closeModalRevisi() {
        modalRevisi.classList.add('hidden');
        catatanRevisiError.classList.add('hidden');
    }

    if (btnMintaRevisi) {
        btnMintaRevisi.addEventListener('click', openModalRevisi);
    }
    if (btnBatalRevisi) {
        btnBatalRevisi.addEventListener('click', closeModalRevisi);
    }
    if (btnBatalRevisiX) {
        btnBatalRevisiX.addEventListener('click', closeModalRevisi);
    }
    if (modalRevisi) {
        modalRevisi.addEventListener('click', function (e) {
            if (e.target === modalRevisi) {
                closeModalRevisi();
            }
        });
    }

    // Handle "Kirim Revisi" in Modal
    if (btnSubmitRevisi) {
        btnSubmitRevisi.addEventListener('click', async function () {
            if (selectedIndex === null) return;
            const ind = indikators[selectedIndex];
            if (!ind) return;

            const catatan = catatanRevisiInput.value.trim();
            if (!catatan) {
                catatanRevisiError.classList.remove('hidden');
                catatanRevisiInput.focus();
                return;
            }

            btnSubmitRevisi.disabled = true;
            btnSubmitRevisiText.textContent = 'Mengirim...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch("{{ route('validasi-monitoring.update-status') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        kode: ind.kode,
                        status: 'perlu_revisi',
                        catatan_revisi: catatan,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    ind.status = 'perlu_revisi';
                    ind.catatan_revisi = catatan;

                    // Update UI
                    updateListBadge(ind.kode, 'perlu_revisi');
                    const s = statusStyles.perlu_revisi;
                    detailStatusBadge.className = 'h-6 px-2.5 rounded-full text-[11px] font-semibold inline-flex items-center ' + s.bg + ' ' + s.text;
                    detailStatusBadge.textContent = s.label;

                    detailRevisiContainer.classList.remove('hidden');
                    detailRevisi.textContent = catatan;
                    detailActions.classList.add('hidden');

                    updateSummaryUI(data.summary, data.total);
                    closeModalRevisi();
                    showToast(data.message || `Catatan revisi untuk ${ind.kode} berhasil dikirim!`, 'warning');
                } else {
                    showToast('Gagal mengirim revisi. Silakan coba lagi.', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Terjadi kesalahan jaringan.', 'error');
            } finally {
                btnSubmitRevisi.disabled = false;
                btnSubmitRevisiText.textContent = 'Kirim Revisi';
            }
        });
    }

    // Filter tabs — reload page with filter query param
    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            const filter = this.getAttribute('data-filter');
            const url = new URL(window.location.href);
            if (filter === 'semua') {
                url.searchParams.delete('filter');
            } else {
                url.searchParams.set('filter', filter);
            }
            window.location.href = url.toString();
        });
    });
});
</script>
@endsection
