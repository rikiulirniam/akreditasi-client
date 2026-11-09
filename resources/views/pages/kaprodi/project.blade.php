@extends('layouts.app')

@section('title', 'Project Akreditasi')

@section('content')
@php
    $sidebarUser = session('user', []);
    $userRole = strtolower((string) (($sidebarUser['role'] ?? null) ?: ($sidebarUser['role_name'] ?? '')));
    $isKaprodi = str_contains($userRole, 'kaprodi');
    $isAdmin = ! $isKaprodi && ! str_contains($userRole, 'reviewer');
@endphp

<div class="space-y-6 pb-10">
    
    <!-- Toast Notification Component -->
    <x-toast />

    @if (session('success'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm font-semibold text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Bagian Header Judul & Tombol -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary tracking-tight leading-tight">Dashboard Project Akreditasi</h1>
            <p class="text-xs sm:text-sm text-[#5A6E85] mt-1">Monitoring status pengajuan akreditasi prodi dan manajemen data project akreditasi terintegrasi.</p>
        </div>
        <button 
            type="button" 
            onclick="openModal()" 
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Project Baru
        </button>
    </div>

    <!-- Bagian 4 Kotak Statistik (Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <!-- Card 1 -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 flex flex-col justify-center shadow-xs">
            <div class="text-gray-500 text-xs font-bold tracking-wider uppercase mb-2">Total Project</div>
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <span id="stat-total" class="text-primary text-[28px] font-extrabold leading-none">{{ $stats['total'] ?? count($projects ?? []) }}</span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 flex flex-col justify-center shadow-xs">
            <div class="text-gray-500 text-xs font-bold tracking-wider uppercase mb-2">Tahun Berjalan</div>
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span id="stat-tahun" class="text-primary text-[28px] font-extrabold leading-none">{{ $stats['tahun_berjalan'] ?? 0 }}</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 flex flex-col justify-center shadow-xs">
            <div class="text-gray-500 text-xs font-bold tracking-wider uppercase mb-2">In Progress</div>
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
                <span id="stat-inprogress" class="text-amber-600 text-[28px] font-extrabold leading-none">
                    {{ $stats['in_progress'] ?? 0 }}
                </span>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 flex flex-col justify-center shadow-xs">
            <div class="text-gray-500 text-xs font-bold tracking-wider uppercase mb-2">Draft</div>
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </div>
                <span id="stat-draft" class="text-slate-700 text-[28px] font-extrabold leading-none">
                    {{ $stats['draft'] ?? 0 }}
                </span>
            </div>
        </div>
    </div>

    @php
        $hasProjects = ! empty($projects) && count($projects) > 0;
    @endphp

    <!-- STATE 1: KOSONG (JIKA BELUM ADA DATA DARI API) -->
    <div id="empty-state" class="w-full bg-white rounded-2xl border border-[#EAEAEA] shadow-xs p-10 mt-6 {{ $hasProjects ? 'hidden' : '' }}">
        <x-table.empty 
            colspan="8"
            message="Belum ada project akreditasi di server"
            submessage="Klik tombol '+ Project Baru' untuk membuat project akreditasi pertama Anda."
        />
    </div>

    <!-- STATE 2: ADA DATA (TABEL DARI API MENGGUNAKAN X-TABLE) -->
    <div id="data-state" class="{{ $hasProjects ? '' : 'hidden' }} bg-white rounded-2xl border border-[#EAEAEA] shadow-xs overflow-hidden mt-6">
        <x-table class="min-w-[1020px]">
            <x-slot:header>
                <x-table.th class="min-w-[220px]">Program Studi / Project</x-table.th>
                <x-table.th class="text-center w-28">Jenjang</x-table.th>
                <x-table.th class="text-center w-20">Jenis</x-table.th>
                <x-table.th class="text-center w-24">Instrumen</x-table.th>
                <x-table.th class="text-center w-20">Tahun</x-table.th>
                <x-table.th class="w-32">Kelengkapan</x-table.th>
                <x-table.th class="text-center min-w-[160px]">Status Project</x-table.th>
                <x-table.th class="min-w-[260px] text-right">Action</x-table.th>
            </x-slot:header>

            @foreach($projects as $item)
                @php
                    $id = $item['id'] ?? null;
                    $namaProject = $item['nama_project'] ?? 'Project Akreditasi';
                    $versiNomor = $item['versi_instrumen']['nomor_versi'] ?? '2.1';
                    $jenjangNama = $item['jenjang']['nama_jenjang'] ?? 'Sarjana Terapan';
                    $jenisNama = $item['jenis_instrumen']['nama_jenis'] ?? 'APS';
                    $tahunSubmit = $item['tahun_target_submit'] ?? '2026';
                    $statusRaw = strtolower((string) ($item['status_project'] ?? 'draft'));
                    $isInProgress = $statusRaw === 'in_progress' || $statusRaw === 'pengisian' || str_contains($statusRaw, 'progress');
                    $statusLabel = $isInProgress ? 'In Progress' : 'Draft';
                @endphp
                <x-table.row>
                    <!-- Program Studi / Project -->
                    <x-table.cell>
                        <div class="flex flex-col">
                            <span class="text-primary font-bold text-sm tracking-tight leading-snug">{{ $namaProject }}</span>
                            <span class="text-gray-400 text-[10px] font-semibold uppercase mt-0.5">LAM INFOKOM · v{{ $versiNomor }}</span>
                        </div>
                    </x-table.cell>

                    <!-- Jenjang -->
                    <x-table.cell class="text-center">
                        <div class="text-center font-medium text-gray-700 text-xs">{{ $jenjangNama }}</div>
                    </x-table.cell>

                    <!-- Jenis -->
                    <x-table.cell class="text-center">
                        <div class="flex items-center justify-center">
                            <span class="inline-flex px-2 py-0.5 text-[11px] font-semibold text-slate-700 bg-slate-100 rounded-md">
                                {{ $jenisNama }}
                            </span>
                        </div>
                    </x-table.cell>

                    <!-- Instrumen -->
                    <x-table.cell class="text-center">
                        <div class="text-center font-medium text-gray-700 text-xs">v{{ $versiNomor }}</div>
                    </x-table.cell>

                    <!-- Tahun -->
                    <x-table.cell class="text-center">
                        <div class="text-center font-semibold text-gray-700 text-xs">{{ $tahunSubmit }}</div>
                    </x-table.cell>

                    <!-- Kelengkapan -->
                    <x-table.cell>
                        <div class="flex items-center gap-2">
                            <div class="w-16 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-additional {{ $isInProgress ? 'w-2/5' : 'w-0' }} transition-all duration-300"></div>
                            </div>
                            <span class="text-gray-500 text-xs font-medium">{{ $isInProgress ? 'Monev' : '0/36' }}</span>
                        </div>
                    </x-table.cell>

                    <!-- Status Project (Dapat diubah oleh Kaprodi) -->
                    <x-table.cell class="text-center">
                        <div class="flex items-center justify-center">
                            @if ($isKaprodi && $id)
                                <form action="{{ route('kaprodi.project.update-status', $id) }}" method="POST" class="inline-flex items-center justify-center">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status_project" value="{{ $isInProgress ? 'draft' : 'in_progress' }}">
                                    <button type="submit" 
                                        title="Klik untuk ubah status ke {{ $isInProgress ? 'Draft' : 'In Progress' }}"
                                        class="group inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer shadow-2xs {{ $isInProgress ? 'bg-amber-100 text-amber-800 hover:bg-amber-200 border border-amber-300' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300' }}">
                                        <span class="w-2 h-2 rounded-full {{ $isInProgress ? 'bg-amber-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                        <span>{{ $statusLabel }}</span>
                                        <svg class="w-3 h-3 opacity-50 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $isInProgress ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    <span class="w-2 h-2 rounded-full {{ $isInProgress ? 'bg-amber-500' : 'bg-slate-400' }}"></span>
                                    {{ $statusLabel }}
                                </span>
                            @endif
                        </div>
                    </x-table.cell>

                    <!-- Action -->
                    <x-table.cell class="text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-1.5 whitespace-nowrap">
                            @if(Route::has('kaprodi.plotting'))
                            <a 
                                href="{{ route('kaprodi.plotting', ['project_id' => $id]) }}" 
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors"
                                title="Plotting Tim"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                </svg>
                                <span>Plotting</span>
                            </a>
                            @endif

                            <a 
                                href="{{ route('kaprodi.project.detail', $id) }}" 
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-white bg-additional hover:bg-[#008f82] rounded-lg shadow-2xs transition-colors"
                                title="Monitoring Detail"
                            >
                                <span>Monitoring</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </a>

                            <!-- Tombol Edit Project -->
                            <button 
                                type="button"
                                onclick="openEditModal({{ json_encode($item) }})"
                                class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                title="Edit Project"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                            </button>

                            @if($id)
                                <form 
                                    action="{{ route('kaprodi.project.destroy', $id) }}" 
                                    method="POST" 
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus project ini dari server?');"
                                    class="inline"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit" 
                                        class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" 
                                        title="Hapus Project"
                                    >
                                        <x-icon.delete class="w-4 h-4" />
                                    </button>
                                </form>
                            @endif
                        </div>
                    </x-table.cell>
                </x-table.row>
            @endforeach
        </x-table>
        
        <!-- Table Footer Counter -->
        <div class="py-3 px-6 bg-gray-50/60 border-t border-gray-100 text-gray-500 text-xs font-medium">
            {{ count($projects) }} baris project dari API
        </div>
    </div>

</div>

<!-- MODAL PROJECT BARU -->
<div id="modal-project" class="hidden fixed inset-0 z-50 bg-black/45 flex items-center justify-center p-4 backdrop-blur-xs select-none">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 sm:p-8 relative">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900 tracking-tight">Project Akreditasi Baru</h2>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-normal transition-colors cursor-pointer">&times;</button>
        </div>
        
        <!-- STEP TABS -->
        <div class="flex items-center gap-2 mb-6">
            <button type="button" id="tab-1" onclick="switchStep(1)" class="bg-primary text-white text-xs font-bold px-3.5 py-1.5 rounded-lg transition-colors cursor-pointer">01 Parameter</button>
            <button type="button" id="tab-2" onclick="switchStep(2)" class="bg-slate-100 text-slate-400 text-xs font-bold px-3.5 py-1.5 rounded-lg transition-colors cursor-pointer">02 Memuat Instrumen</button>
            <button type="button" id="tab-3" onclick="switchStep(3)" class="bg-slate-100 text-slate-400 text-xs font-bold px-3.5 py-1.5 rounded-lg transition-colors cursor-pointer">03 Simpan</button>
        </div>

        <form id="formProject" action="{{ route('kaprodi.project.store') }}" method="POST">
            @csrf
            
            <!-- Hidden Foreign Key IDs untuk API -->
            <input type="hidden" name="id_prodi" value="1">
            <input type="hidden" name="id_jenis_instrumen" id="hiddenJenisInstrumen" value="1">
            
            <!-- STEP 1: PARAMETER -->
            <div id="step-1" class="block space-y-4">
                <div>
                    <label for="inputNama" class="block text-xs font-bold text-gray-800 mb-1.5">Nama Project <span class="text-red-500">*</span></label>
                    <x-input 
                        type="text" 
                        name="nama_project"
                        id="inputNama" 
                        placeholder="Contoh: Akreditasi Prodi D4 Rekayasa Perangkat Lunak 2026" 
                        required
                        class="text-xs"
                    />
                </div>

                <div>
                    <label for="inputInstrumen" class="block text-xs font-bold text-gray-800 mb-1.5">
                        Instrumen <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="id_instrumen"
                        id="inputInstrumen"
                        onchange="syncInstrumenData(this, 'add')"
                        required
                        class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        @forelse ($instrumenList ?? [] as $ins)
                            <option value="{{ $ins['id'] }}"
                                data-versi="{{ $ins['id_versi_instrumen'] ?? 1 }}"
                                data-jenjang="{{ $ins['id_jenjang'] ?? 1 }}"
                                data-jenis="{{ $ins['id_jenis_instrumen'] ?? 1 }}">
                                {{ $ins['formatted_label'] }}
                            </option>
                        @empty
                            <option value="1" data-versi="1" data-jenjang="1" data-jenis="1">LAM INFOKOM - v2.1 - Sarjana Terapan - APS</option>
                        @endforelse
                    </select>
                    <input type="hidden" name="id_versi_instrumen" id="addVersiId" value="1">
                    <input type="hidden" name="id_jenjang" id="addJenjangId" value="1">
                    <input type="hidden" name="id_jenis_instrumen" id="addJenisId" value="1">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                    <div>
                        <label for="inputTahun" class="block text-xs font-bold text-gray-800 mb-1.5">Tahun Target Submit</label>
                        <input 
                            type="number" 
                            name="tahun_target_submit"
                            id="inputTahun"
                            value="{{ date('Y') }}"
                            required
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1.5">Status Project Awal</label>
                        <div class="flex flex-wrap gap-2 pt-1">
                            <label class="cursor-pointer">
                                <input type="radio" name="status_project" value="draft" class="peer sr-only" checked>
                                <div class="bg-slate-100 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg peer-checked:bg-primary peer-checked:text-white hover:bg-slate-200 transition-colors">Draft</div>
                            </label>
                            @if ($isKaprodi)
                            <label class="cursor-pointer">
                                <input type="radio" name="status_project" value="in_progress" class="peer sr-only">
                                <div class="bg-slate-100 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg peer-checked:bg-primary peer-checked:text-white hover:bg-slate-200 transition-colors">In Progress</div>
                            </label>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-slate-100">
                    <button type="button" onclick="closeModal()" class="px-5 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-xl transition-colors cursor-pointer">Batal</button>
                    <button type="button" onclick="switchStep(2)" class="px-5 py-2.5 bg-primary hover:bg-additional text-white text-xs font-bold rounded-xl transition-colors cursor-pointer">Muat Instrumen</button>
                </div>
            </div>

            <!-- STEP 2: MEMUAT INSTRUMEN -->
            <div id="step-2" class="hidden space-y-4">
                <div class="w-full flex flex-col gap-2.5 pt-2">
                    <div class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl flex justify-between items-center">
                        <div class="text-gray-700 text-xs font-semibold">■ Kriteria Penilaian</div>
                        <div class="text-primary text-sm font-extrabold">9 Kriteria</div>
                    </div>
                    <div class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl flex justify-between items-center">
                        <div class="text-gray-700 text-xs font-semibold">■ Subkriteria & Indikator</div>
                        <div class="text-primary text-sm font-extrabold">Standar LAM-INFOKOM 2.1</div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-slate-100">
                    <button type="button" onclick="switchStep(1)" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-gray-600 text-xs font-bold rounded-xl transition-colors cursor-pointer">Kembali</button>
                    <button type="button" onclick="switchStep(3)" class="px-5 py-2.5 bg-primary hover:bg-additional text-white text-xs font-bold rounded-xl transition-colors cursor-pointer">Lanjut</button>
                </div>
            </div>

            <!-- STEP 3: SIMPAN -->
            <div id="step-3" class="hidden space-y-4">
                <div id="summaryText" class="text-gray-600 text-xs font-semibold pt-2 pb-2 p-3 bg-gray-50 rounded-xl border border-gray-100">
                    Konfirmasi pembuatan project akreditasi baru ke server akreditasi.
                </div>

                <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-slate-100">
                    <button type="button" onclick="switchStep(2)" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-gray-600 text-xs font-bold rounded-xl transition-colors cursor-pointer flex items-center justify-center">&larr; Ubah</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-additional text-white text-xs font-bold rounded-xl transition-colors cursor-pointer flex items-center justify-center">Simpan Project</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT PROJECT -->
<div id="modal-edit-project" class="hidden fixed inset-0 z-50 bg-black/45 flex items-center justify-center p-4 backdrop-blur-xs select-none">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 sm:p-8 relative">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Edit Project Akreditasi</h2>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui informasi parameter project akreditasi.</p>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-normal transition-colors cursor-pointer">&times;</button>
        </div>

        <form id="formEditProject" method="POST" action="">
            @csrf
            @method('PUT')

            <input type="hidden" name="id_prodi" id="editProdiId" value="1">
            <input type="hidden" name="id_jenis_instrumen" id="editJenisId" value="1">

            <div class="space-y-4">
                <div>
                    <label for="editNamaProject" class="block text-xs font-bold text-gray-800 mb-1.5">Nama Project <span class="text-red-500">*</span></label>
                    <x-input 
                        type="text" 
                        name="nama_project"
                        id="editNamaProject" 
                        required
                        class="text-xs"
                    />
                </div>

                <div>
                    <label for="editInstrumen" class="block text-xs font-bold text-gray-800 mb-1.5">
                        Instrumen <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="id_instrumen"
                        id="editInstrumen"
                        onchange="syncInstrumenData(this, 'edit')"
                        required
                        class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        @forelse ($instrumenList ?? [] as $ins)
                            <option value="{{ $ins['id'] }}"
                                data-versi="{{ $ins['id_versi_instrumen'] ?? 1 }}"
                                data-jenjang="{{ $ins['id_jenjang'] ?? 1 }}"
                                data-jenis="{{ $ins['id_jenis_instrumen'] ?? 1 }}">
                                {{ $ins['formatted_label'] }}
                            </option>
                        @empty
                            <option value="1" data-versi="1" data-jenjang="1" data-jenis="1">LAM INFOKOM - v2.1 - Sarjana Terapan - APS</option>
                        @endforelse
                    </select>
                    <input type="hidden" name="id_versi_instrumen" id="editVersiId" value="1">
                    <input type="hidden" name="id_jenjang" id="editJenjangId" value="1">
                    <input type="hidden" name="id_jenis_instrumen" id="editJenisId" value="1">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                    <div>
                        <label for="editTahun" class="block text-xs font-bold text-gray-800 mb-1.5">Tahun Target Submit</label>
                        <input 
                            type="number" 
                            name="tahun_target_submit"
                            id="editTahun"
                            required
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1.5">Status Project</label>
                        @if ($isKaprodi)
                            <div class="flex flex-wrap gap-2 pt-1">
                                <label class="cursor-pointer">
                                    <input type="radio" name="status_project" value="draft" id="editStatusDraft" class="peer sr-only">
                                    <div class="bg-slate-100 text-gray-600 text-xs font-semibold px-4 py-2 rounded-xl peer-checked:bg-primary peer-checked:text-white hover:bg-slate-200 transition-colors">Draft</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="status_project" value="in_progress" id="editStatusInProgress" class="peer sr-only">
                                    <div class="bg-slate-100 text-gray-600 text-xs font-semibold px-4 py-2 rounded-xl peer-checked:bg-primary peer-checked:text-white hover:bg-slate-200 transition-colors">In Progress</div>
                                </label>
                            </div>
                        @else
                            <div class="p-3 bg-gray-50 rounded-xl text-xs text-gray-500 font-medium border border-gray-200">
                                Status project dikelola oleh Kaprodi (<span id="editStatusReadonly" class="font-bold text-primary">Draft</span>).
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-gray-600 text-xs font-bold rounded-xl transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-additional text-white text-xs font-bold rounded-xl transition-colors cursor-pointer">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // --- SINKRONISASI INSTRUMEN DATA ---
    function syncInstrumenData(selectEl, prefix) {
        if (!selectEl) return;
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt) return;

        const vId = opt.getAttribute('data-versi') || '1';
        const jId = opt.getAttribute('data-jenjang') || '1';
        const kId = opt.getAttribute('data-jenis') || '1';

        const vInput = document.getElementById(prefix + 'VersiId');
        const jInput = document.getElementById(prefix + 'JenjangId');
        const kInput = document.getElementById(prefix + 'JenisId');

        if (vInput) vInput.value = vId;
        if (jInput) jInput.value = jId;
        if (kInput) kInput.value = kId;
    }

    // --- FUNGSI MODAL TAMBAH ---
    function openModal() {
        const modal = document.getElementById('modal-project');
        if (modal) {
            modal.classList.remove('hidden');
            switchStep(1);
            syncInstrumenData(document.getElementById('inputInstrumen'), 'add');
        }
    }

    function closeModal() {
        const modal = document.getElementById('modal-project');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function switchStep(step) {
        document.getElementById('step-1').classList.add('hidden');
        document.getElementById('step-2').classList.add('hidden');
        document.getElementById('step-3').classList.add('hidden');
        
        for(let i=1; i<=3; i++) {
            let tab = document.getElementById('tab-' + i);
            if(tab) {
                tab.classList.remove('bg-primary', 'text-white');
                tab.classList.add('bg-slate-100', 'text-slate-400');
            }
        }

        document.getElementById('step-' + step).classList.remove('hidden');
        document.getElementById('step-' + step).classList.add('block');
        
        let activeTab = document.getElementById('tab-' + step);
        if(activeTab) {
            activeTab.classList.remove('bg-slate-100', 'text-slate-400');
            activeTab.classList.add('bg-primary', 'text-white');
        }

        if (step === 3) {
            const nama = document.getElementById('inputNama')?.value || 'Project Baru';
            const tahun = document.getElementById('inputTahun')?.value || '2026';
            const summaryEl = document.getElementById('summaryText');
            if (summaryEl) {
                summaryEl.innerText = `${nama} · Target Tahun ${tahun}`;
            }
        }
    }

    // --- FUNGSI MODAL EDIT ---
    function openEditModal(item) {
        const modal = document.getElementById('modal-edit-project');
        const form = document.getElementById('formEditProject');
        if (!modal || !form || !item) return;

        form.action = "{{ url('kaprodi/project') }}/" + item.id;
        document.getElementById('editNamaProject').value = item.nama_project || '';
        document.getElementById('editTahun').value = item.tahun_target_submit || '2026';
        
        if (document.getElementById('editInstrumen')) {
            const select = document.getElementById('editInstrumen');
            let matched = false;

            if (item.id_instrumen) {
                select.value = item.id_instrumen;
                matched = select.value == item.id_instrumen;
            }

            if (!matched) {
                const targetVersi = item.id_versi_instrumen || (item.versi_instrumen?.id);
                for (let i = 0; i < select.options.length; i++) {
                    const opt = select.options[i];
                    if (targetVersi && opt.getAttribute('data-versi') == targetVersi) {
                        select.selectedIndex = i;
                        matched = true;
                        break;
                    }
                }
            }

            syncInstrumenData(select, 'edit');
        }
        
        const status = (item.status_project || 'draft').toLowerCase();
        const isProg = status === 'in_progress' || status === 'pengisian' || status.includes('progress');
        const draftRadio = document.getElementById('editStatusDraft');
        const inProgRadio = document.getElementById('editStatusInProgress');
        if (draftRadio && inProgRadio) {
            if (isProg) {
                inProgRadio.checked = true;
            } else {
                draftRadio.checked = true;
            }
        }
        const statusReadonly = document.getElementById('editStatusReadonly');
        if (statusReadonly) {
            statusReadonly.innerText = isProg ? 'In Progress' : 'Draft';
        }

        modal.classList.remove('hidden');
    }

    function closeEditModal() {
        const modal = document.getElementById('modal-edit-project');
        if (modal) {
            modal.classList.add('hidden');
        }
    }
</script>
@endsection
