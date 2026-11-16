{{-- resources/views/pages/dosen/my_assignments.blade.php --}}
@extends('layouts.app')

@section('title', 'Tugas Saya - Tim Akreditasi')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Toast Notification -->
    <x-toast />

    <!-- 1. Header Halaman -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-extrabold text-primary tracking-tight leading-tight">Tugas Saya</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-primary/10 text-primary">
                    DOSEN TIM AKREDITASI
                </span>
            </div>
            <p class="text-xs sm:text-sm text-[#5A6E85] mt-1">
                Daftar butir indikator borang yang ditugaskan kepada Anda sebagai <strong>Penanggung Jawab (PJ)</strong> atau <strong>Pelaksana (PIC)</strong>.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-[#EAEAEA] text-gray-700 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Masuk sebagai: <strong>{{ $userName }}</strong></span>
            </span>
        </div>
    </div>

    <!-- 2. Kartu Metrik Ringkasan -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Butir Ditugaskan -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Tugas</span>
                <span class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-primary font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-primary leading-none">{{ $stats['total'] }}</div>
                <p class="text-[11px] text-gray-400 mt-1">Butir indikator borang</p>
            </div>
        </div>

        <!-- Peran PJ -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Sebagai PJ</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-700 font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-indigo-800 leading-none">{{ $stats['pj'] }}</div>
                <p class="text-[11px] text-gray-400 mt-1">Penanggung jawab mutu</p>
            </div>
        </div>

        <!-- Peran PIC -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Sebagai PIC</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-700 font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-emerald-800 leading-none">{{ $stats['pic'] }}</div>
                <p class="text-[11px] text-gray-400 mt-1">Pelaksana pengisi bukti</p>
            </div>
        </div>

        <!-- Progress Kelengkapan Bukti -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Bukti Terunggah</span>
                <span class="text-xs font-black text-primary">{{ $stats['persen'] }}%</span>
            </div>
            <div class="mt-3 space-y-1.5">
                <div class="text-2xl font-extrabold text-primary leading-none">{{ $stats['terisi'] }} / {{ $stats['total'] }}</div>
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $stats['persen'] }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Panel Utama Tabel Penugasan -->
    <div class="bg-white rounded-2xl border border-[#EAEAEA] shadow-xs overflow-hidden">
        <!-- Header Panel & Filter -->
        <div class="p-5 border-b border-[#EAEAEA] flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white">
            <div>
                <h2 class="text-base font-extrabold text-primary tracking-tight">Daftar Indikator yang Ditugaskan</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Khusus peran <strong>PIC</strong>, silakan unggah tautan folder/berkas Google Drive yang memuat seluruh bukti pendukung.
                </p>
            </div>

            <!-- Filter Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 select-none">
                <button type="button" onclick="filterAssignments('semua')" id="tab-semua" class="filter-btn px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg border border-primary transition-colors cursor-pointer">
                    Semua ({{ count($assignments) }})
                </button>
                <button type="button" onclick="filterAssignments('belum')" id="tab-belum" class="filter-btn px-3 py-1.5 bg-white text-gray-700 hover:bg-gray-100 text-xs font-bold rounded-lg border border-[#EAEAEA] transition-colors cursor-pointer">
                    Belum Ada Bukti
                </button>
                <button type="button" onclick="filterAssignments('ada')" id="tab-ada" class="filter-btn px-3 py-1.5 bg-white text-gray-700 hover:bg-gray-100 text-xs font-bold rounded-lg border border-[#EAEAEA] transition-colors cursor-pointer">
                    Sudah Ada Bukti
                </button>
                <button type="button" onclick="filterAssignments('pic')" id="tab-pic" class="filter-btn px-3 py-1.5 bg-white text-gray-700 hover:bg-gray-100 text-xs font-bold rounded-lg border border-[#EAEAEA] transition-colors cursor-pointer">
                    Peran PIC
                </button>
                <button type="button" onclick="filterAssignments('pj')" id="tab-pj" class="filter-btn px-3 py-1.5 bg-white text-gray-700 hover:bg-gray-100 text-xs font-bold rounded-lg border border-[#EAEAEA] transition-colors cursor-pointer">
                    Peran PJ
                </button>
            </div>
        </div>

        <!-- Tabel List Penugasan -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-[#EAEAEA]">
                        <th class="py-3.5 px-5 w-24">Kode</th>
                        <th class="py-3.5 px-5">Butir Indikator Penilaian</th>
                        <th class="py-3.5 px-5 w-32 text-center">Peran Anda</th>
                        <th class="py-3.5 px-5 w-40 text-center">Status Bukti</th>
                        <th class="py-3.5 px-5 w-52 text-right">Aksi Bukti Google Drive</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs sm:text-sm" id="table-assignments-body">
                    @forelse($assignments as $item)
                        @php
                            $roleUpper = strtoupper($item['role'] ?? 'PIC');
                            $hasBukti = !empty($item['has_bukti']);
                            $evidenceUrl = $item['evidence_url'] ?? null;
                            $evidenceNotes = $item['evidence_notes'] ?? '';
                            $code = $item['kode_indikator'] ?? 'Indikator';
                            $desc = $item['deskripsi'] ?? 'Deskripsi butir indikator';
                            $bobot = $item['bobot'] ?? 1.0;
                            $indId = $item['id_indikator'] ?? 1;
                            $projId = $item['id_project'] ?? 1;
                        @endphp
                        <tr 
                            class="assignment-row hover:bg-slate-50/60 transition-colors"
                            data-has-bukti="{{ $hasBukti ? 'true' : 'false' }}"
                            data-role="{{ strtolower($roleUpper) }}"
                            id="row-{{ $code }}"
                        >
                            <!-- Kode Indikator -->
                            <td class="py-4 px-5 align-top">
                                <span class="inline-block px-2.5 py-1 rounded-lg bg-sky-50 text-additional font-extrabold text-xs border border-sky-100">
                                    {{ $code }}
                                </span>
                                <div class="text-[10px] text-gray-400 font-semibold mt-1">Bobot: {{ $bobot }}</div>
                            </td>

                            <!-- Judul & Deskripsi -->
                            <td class="py-4 px-5 align-top">
                                <div class="font-bold text-primary text-sm leading-snug">{{ $desc }}</div>
                                <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    <span>{{ $item['nama_project'] ?? 'Project Akreditasi' }}</span>
                                </div>
                                @if(!empty($evidenceNotes))
                                    <div class="mt-2 text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-200/60 text-gray-600">
                                        <strong class="text-primary font-semibold">Catatan Bukti:</strong> {{ $evidenceNotes }}
                                    </div>
                                @endif
                            </td>

                            <!-- Peran Dosen -->
                            <td class="py-4 px-5 align-top text-center">
                                @if($roleUpper === 'PJ')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                        PJ (Penanggung Jawab)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        PIC (Pelaksana)
                                    </span>
                                @endif
                            </td>

                            <!-- Status Bukti -->
                            <td class="py-4 px-5 align-top text-center status-cell">
                                @if($hasBukti)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        Tautan Terisi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Belum Ada Bukti
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi Drive -->
                            <td class="py-4 px-5 align-top text-right space-y-1.5">
                                @if($hasBukti && $evidenceUrl)
                                    <div>
                                        <a 
                                            href="{{ $evidenceUrl }}" 
                                            target="_blank" 
                                            rel="noopener noreferrer" 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-additional text-xs font-bold border border-sky-100 transition-colors"
                                            title="Buka Google Drive"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                            </svg>
                                            Buka Drive
                                        </a>
                                    </div>
                                @endif

                                <div>
                                    <button 
                                        type="button" 
                                        onclick="openUploadEvidenceModal('modal-upload-evidence-link', {
                                            indicatorId: '{{ $indId }}',
                                            projectId: '{{ $projId }}',
                                            code: '{{ $code }}',
                                            description: '{{ addslashes($desc) }}',
                                            existingUrl: '{{ $evidenceUrl ?? '' }}',
                                            existingNotes: '{{ addslashes($evidenceNotes) }}'
                                        })" 
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-primary hover:bg-additional text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        <span>{{ $hasBukti ? 'Ubah Link' : 'Unggah Link Drive' }}</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-sm font-bold text-gray-500">Belum ada butir penugasan untuk Anda</p>
                                    <p class="text-xs text-gray-400">Kaprodi belum menetapkan indikator borang kepada akun Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Upload Evidence Link Component -->
<x-modal-upload-evidence-link id="modal-upload-evidence-link" />

<script>
    const TAB_ACTIVE = ['bg-primary', 'text-white', 'border-primary'];
    const TAB_INACTIVE = ['bg-white', 'text-gray-700', 'border-[#EAEAEA]', 'hover:bg-gray-100'];

    function filterAssignments(type) {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove(...TAB_ACTIVE);
            btn.classList.add(...TAB_INACTIVE);
        });

        const activeBtn = document.getElementById('tab-' + type);
        if (activeBtn) {
            activeBtn.classList.remove(...TAB_INACTIVE);
            activeBtn.classList.add(...TAB_ACTIVE);
        }

        const rows = document.querySelectorAll('.assignment-row');
        rows.forEach(row => {
            const hasBukti = row.getAttribute('data-has-bukti') === 'true';
            const role = row.getAttribute('data-role');

            if (type === 'semua') {
                row.style.display = '';
            } else if (type === 'belum') {
                row.style.display = !hasBukti ? '' : 'none';
            } else if (type === 'ada') {
                row.style.display = hasBukti ? '' : 'none';
            } else if (type === 'pic') {
                row.style.display = role === 'pic' ? '' : 'none';
            } else if (type === 'pj') {
                row.style.display = role === 'pj' ? '' : 'none';
            }
        });
    }

    // Callback setelah bukti berhasil disimpan
    window.onEvidenceSaved = function(code, url, notes) {
        const row = document.getElementById('row-' + code);
        if (!row) return;

        row.setAttribute('data-has-bukti', 'true');
        const statusCell = row.querySelector('.status-cell');
        if (statusCell) {
            statusCell.innerHTML = `
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Tautan Terisi
                </span>
            `;
        }

        const actionCell = row.querySelector('td:last-child');
        if (actionCell) {
            const descEl = row.querySelector('.font-bold.text-primary.text-sm');
            const desc = descEl ? descEl.innerText.replace(/'/g, "\\'") : '';
            const safeNotes = (notes || '').replace(/'/g, "\\'");
            
            actionCell.innerHTML = `
                <div class="space-y-1.5">
                    <div>
                        <a 
                            href="${url}" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-additional text-xs font-bold border border-sky-100 transition-colors"
                            title="Buka Google Drive"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            Buka Drive
                        </a>
                    </div>
                    <div>
                        <button 
                            type="button" 
                            onclick="openUploadEvidenceModal('modal-upload-evidence-link', {
                                indicatorId: '1',
                                projectId: '1',
                                code: '${code}',
                                description: '${desc}',
                                existingUrl: '${url}',
                                existingNotes: '${safeNotes}'
                            })" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-primary hover:bg-additional text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Ubah Link</span>
                        </button>
                    </div>
                </div>
            `;
        }
    };
</script>
@endsection

