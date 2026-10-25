@extends('layouts.app')

@section('title', 'Project Akreditasi')

@section('content')
<div class="space-y-6 pb-10">
    
    <!-- Toast Notification Component -->
    <x-toast />

    <!-- Bagian Header Judul & Tombol -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary tracking-tight leading-tight">Dashboard Project Akreditasi</h1>
            <p class="text-xs sm:text-sm text-[#5A6E85] mt-1">Kelola dan pantau pengajuan akreditasi program studi Anda dari sistem akreditasi terintegrasi.</p>
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
            <div class="text-gray-400 text-[10px] font-bold tracking-wider uppercase mb-2">Total Project</div>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <span id="stat-total" class="text-primary text-[28px] font-extrabold leading-none">
                    {{ $stats['total'] ?? count($projects ?? []) }}
                </span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 flex flex-col justify-center shadow-xs">
            <div class="text-gray-400 text-[10px] font-bold tracking-wider uppercase mb-2">Tahun Berjalan</div>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span id="stat-tahun" class="text-primary text-[28px] font-extrabold leading-none">
                    {{ $stats['tahun_berjalan'] ?? 0 }}
                </span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 flex flex-col justify-center shadow-xs">
            <div class="text-gray-400 text-[10px] font-bold tracking-wider uppercase mb-2">Rata-rata Kelengkapan</div>
            <div class="flex items-center gap-2.5">
                <span id="stat-kelengkapan" class="text-primary text-[28px] font-extrabold leading-none">
                    {{ $stats['rata_kelengkapan'] ?? '—' }}
                </span>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 flex flex-col justify-center shadow-xs">
            <div class="text-gray-400 text-[10px] font-bold tracking-wider uppercase mb-2">Instrumen Aktif</div>
            <div class="flex items-center gap-2.5">
                <span id="stat-instrumen" class="text-primary text-[28px] font-extrabold leading-none">
                    {{ $stats['instrumen_aktif'] ?? '2.1' }}
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
        <x-table>
            <x-slot:header>
                <x-table.th class="w-48">Program Studi</x-table.th>
                <x-table.th class="w-32">Jenjang</x-table.th>
                <x-table.th class="w-28">Jenis</x-table.th>
                <x-table.th class="w-28">Instrumen</x-table.th>
                <x-table.th class="w-24">Tahun</x-table.th>
                <x-table.th class="w-40">Kelengkapan</x-table.th>
                <x-table.th class="w-16">Nilai</x-table.th>
                <x-table.th class="w-28 text-right">Action</x-table.th>
            </x-slot:header>

            @foreach($projects as $item)
                @php
                    $id = $item['id'] ?? null;
                    $namaProject = $item['nama_project'] ?? 'Project Akreditasi';
                    $versiNomor = $item['versi_instrumen']['nomor_versi'] ?? '2.1';
                    $jenjangNama = $item['jenjang']['nama_jenjang'] ?? 'Sarjana Terapan';
                    $jenisNama = $item['jenis_instrumen']['nama_jenis'] ?? ($item['status_project'] ?? 'APS');
                    $tahunSubmit = $item['tahun_target_submit'] ?? '2026';
                @endphp
                <x-table.row>
                    <!-- Program Studi -->
                    <x-table.cell>
                        <div class="flex flex-col">
                            <span class="text-primary font-bold text-sm tracking-tight">{{ $namaProject }}</span>
                            <span class="text-gray-400 text-[10px] font-semibold uppercase mt-0.5">LAM INFOKOM · v{{ $versiNomor }}</span>
                        </div>
                    </x-table.cell>

                    <!-- Jenjang -->
                    <x-table.cell>
                        <span class="text-gray-700 text-xs font-medium">{{ $jenjangNama }}</span>
                    </x-table.cell>

                    <!-- Jenis -->
                    <x-table.cell>
                        <span class="text-gray-700 text-xs font-medium">{{ $jenisNama }}</span>
                    </x-table.cell>

                    <!-- Instrumen -->
                    <x-table.cell>
                        <span class="text-gray-700 text-xs font-medium">v{{ $versiNomor }}</span>
                    </x-table.cell>

                    <!-- Tahun -->
                    <x-table.cell>
                        <span class="text-gray-700 text-xs font-medium">{{ $tahunSubmit }}</span>
                    </x-table.cell>

                    <!-- Kelengkapan -->
                    <x-table.cell>
                        <div class="flex items-center gap-2.5">
                            <div class="w-20 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-additional w-0 transition-all duration-300"></div>
                            </div>
                            <span class="text-gray-500 text-xs font-medium">0/36</span>
                        </div>
                    </x-table.cell>

                    <!-- Nilai -->
                    <x-table.cell>
                        <span class="text-primary text-xs font-bold">0</span>
                    </x-table.cell>

                    <!-- Action -->
                    <x-table.cell class="text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a 
                                href="{{ route('kaprodi.project.detail', $id) }}" 
                                class="text-additional hover:text-primary text-xs font-bold transition-colors"
                            >
                                Detail ›
                            </a>

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
                                        class="text-red-500 hover:text-red-700 p-1 transition-colors cursor-pointer" 
                                        title="Hapus Data"
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="inputLembaga" class="block text-xs font-bold text-gray-800 mb-1.5">Lembaga Akreditasi</label>
                        <x-input 
                            type="select" 
                            name="id_lembaga"
                            id="inputLembaga"
                            class="text-xs"
                        >
                            @if(!empty($lembagaList))
                                @foreach($lembagaList as $lem)
                                    <option value="{{ $lem['id'] }}">{{ $lem['nama_lembaga'] }}</option>
                                @endforeach
                            @else
                                <option value="1">LAM-INFOKOM</option>
                            @endif
                        </x-input>
                    </div>
                    <div>
                        <label for="inputInstrumen" class="block text-xs font-bold text-gray-800 mb-1.5">Versi Instrumen</label>
                        <x-input 
                            type="select" 
                            name="id_versi_instrumen"
                            id="inputInstrumen"
                            class="text-xs"
                        >
                            @if(!empty($versiList))
                                @foreach($versiList as $v)
                                    <option value="{{ $v['id'] }}">v{{ $v['nomor_versi'] }}</option>
                                @endforeach
                            @else
                                <option value="1">v2.1</option>
                            @endif
                        </x-input>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1.5">Status Project</label>
                        <div class="flex flex-wrap gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="status_project" value="draft" class="peer sr-only" checked>
                                <div class="bg-slate-100 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg peer-checked:bg-primary peer-checked:text-white hover:bg-slate-200 transition-colors">Draft</div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status_project" value="pengisian" class="peer sr-only">
                                <div class="bg-slate-100 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg peer-checked:bg-primary peer-checked:text-white hover:bg-slate-200 transition-colors">Pengisian</div>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label for="inputJenjang" class="block text-xs font-bold text-gray-800 mb-1.5">Jenjang</label>
                        <x-input 
                            type="select" 
                            name="id_jenjang"
                            id="inputJenjang"
                            class="text-xs"
                        >
                            <option value="1">Sarjana Terapan (D4)</option>
                            <option value="2">Sarjana (S1)</option>
                            <option value="3">Magister (S2)</option>
                        </x-input>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-800 mb-1.5">Jenis Akreditasi</label>
                    <div class="flex flex-wrap gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="jenis_akreditasi_radio" value="1" class="peer sr-only" checked onchange="document.getElementById('hiddenJenisInstrumen').value = this.value">
                            <div class="bg-slate-100 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg peer-checked:bg-primary peer-checked:text-white hover:bg-slate-200 transition-colors">Akreditasi Pertama (APS)</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="jenis_akreditasi_radio" value="2" class="peer sr-only" onchange="document.getElementById('hiddenJenisInstrumen').value = this.value">
                            <div class="bg-slate-100 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg peer-checked:bg-primary peer-checked:text-white hover:bg-slate-200 transition-colors">Reakreditasi</div>
                        </label>
                    </div>
                </div>

                <div class="w-full sm:w-1/2 sm:pr-2">
                    <label for="inputTahun" class="block text-xs font-bold text-gray-800 mb-1.5">Tahun Target Submit <span class="text-red-500">*</span></label>
                    <x-input 
                        type="select" 
                        name="tahun_target_submit"
                        id="inputTahun"
                        class="text-xs"
                    >
                        <option value="2026" selected>2026</option>
                        <option value="2025">2025</option>
                        <option value="2027">2027</option>
                    </x-input>
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
                        <div class="text-gray-700 text-xs font-semibold">■ Kriteria BAN-PT / LAM</div>
                        <div class="text-primary text-sm font-extrabold">9 Kriteria</div>
                    </div>
                    <div class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl flex justify-between items-center">
                        <div class="text-gray-700 text-xs font-semibold">■ Subkriteria</div>
                        <div class="text-primary text-sm font-extrabold">18 Subkriteria</div>
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
                    [Nama Project] · Sarjana Terapan · v2.1
                </div>

                <div class="w-full flex flex-col gap-2.5 pt-2">
                    <div class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl flex justify-between items-center">
                        <div class="text-gray-700 text-xs font-semibold">■ Total Indikator Penilaian</div>
                        <div class="text-primary text-sm font-extrabold">36 Indikator</div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-slate-100">
                    <button type="button" onclick="switchStep(2)" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-gray-600 text-xs font-bold rounded-xl transition-colors cursor-pointer flex items-center justify-center">&larr; Ubah</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-additional text-white text-xs font-bold rounded-xl transition-colors cursor-pointer flex items-center justify-center">Simpan ke Server API</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // --- FUNGSI MODAL ---
    function openModal() {
        const modal = document.getElementById('modal-project');
        if (modal) {
            modal.classList.remove('hidden');
            switchStep(1);
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
            const jenjangSelect = document.getElementById('inputJenjang');
            const jenjangText = jenjangSelect?.options[jenjangSelect.selectedIndex]?.text || 'Sarjana Terapan';
            const instrumenSelect = document.getElementById('inputInstrumen');
            const instrumenText = instrumenSelect?.options[instrumenSelect.selectedIndex]?.text || 'v2.1';
            const summaryEl = document.getElementById('summaryText');
            if (summaryEl) {
                summaryEl.innerText = `${nama} · ${jenjangText} · ${instrumenText}`;
            }
        }
    }
</script>
@endsection