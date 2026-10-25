@extends('layouts.app')

@section('title', 'Project Akreditasi')

@section('content')
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
            <p class="text-xs sm:text-sm text-[#5A6E85] mt-1">Kelola dan pantau pengajuan akreditasi program studi Anda.</p>
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
                <span id="stat-total" class="text-primary text-[28px] font-extrabold leading-none">{{ $totalProjects ?? count($projects ?? []) }}</span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 flex flex-col justify-center shadow-xs">
            <div class="text-gray-500 text-xs font-bold tracking-wider uppercase mb-2">Tahun Berjalan</div>
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span id="stat-tahun" class="text-primary text-[28px] font-extrabold leading-none">{{ $tahunBerjalanCount ?? 0 }}</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 flex flex-col justify-center shadow-xs">
            <div class="text-gray-500 text-xs font-bold tracking-wider uppercase mb-2">Rata-rata Kelengkapan</div>
            <div class="flex items-center gap-2.5">
                <span id="stat-kelengkapan" class="text-primary text-[28px] font-extrabold leading-none">—</span>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-5 flex flex-col justify-center shadow-xs">
            <div class="text-gray-500 text-xs font-bold tracking-wider uppercase mb-2">Instrumen Aktif</div>
            <div class="flex items-center gap-2.5">
                <span id="stat-instrumen" class="text-primary text-[28px] font-extrabold leading-none">2.1</span>
            </div>
        </div>
    </div>

    <!-- TABEL DATA PROJECT DARI API -->
    @if (empty($projects))
        <div id="empty-state" class="w-full bg-white rounded-2xl border border-[#EAEAEA] shadow-xs p-10 mt-6">
            <x-table.empty 
                colspan="8"
                message="Belum ada project akreditasi"
                submessage="Buat project pertama untuk memuat instrumen LAM INFOKOM secara otomatis."
            />
        </div>
    @else
        <div id="data-state" class="bg-white rounded-2xl border border-[#EAEAEA] shadow-xs overflow-hidden mt-6">
            <x-table>
                <x-slot:header>
                    <x-table.th class="w-56">Nama Project</x-table.th>
                    <x-table.th class="w-32">Jenjang</x-table.th>
                    <x-table.th class="w-36">Jenis</x-table.th>
                    <x-table.th class="w-28">Instrumen</x-table.th>
                    <x-table.th class="w-24">Tahun</x-table.th>
                    <x-table.th class="w-32">Status</x-table.th>
                    <x-table.th class="w-28 text-right">Action</x-table.th>
                </x-slot:header>

                @foreach ($projects as $project)
                    <x-table.row>
                        <!-- Program Studi / Nama Project -->
                        <x-table.cell>
                            <div class="flex flex-col">
                                <span class="text-primary font-bold text-sm tracking-tight">{{ $project['nama_project'] ?? '-' }}</span>
                                <span class="text-gray-500 text-xs font-semibold uppercase mt-0.5">
                                    {{ $project['versi_instrumen']['lembaga']['nama_lembaga'] ?? 'LAM INFOKOM' }}
                                </span>
                            </div>
                        </x-table.cell>

                        <!-- Jenjang -->
                        <x-table.cell>
                            <span class="text-gray-800 text-sm font-medium">
                                {{ $project['jenjang']['nama_jenjang'] ?? 'S1' }}
                            </span>
                        </x-table.cell>

                        <!-- Jenis -->
                        <x-table.cell>
                            <span class="text-gray-800 text-sm font-medium">
                                {{ $project['jenis_instrumen']['nama_jenis'] ?? 'APS' }}
                            </span>
                        </x-table.cell>

                        <!-- Instrumen -->
                        <x-table.cell>
                            <span class="text-gray-800 text-sm font-medium font-mono">
                                v{{ $project['versi_instrumen']['nomor_versi'] ?? '2.1' }}
                            </span>
                        </x-table.cell>

                        <!-- Tahun -->
                        <x-table.cell>
                            <span class="text-gray-800 text-sm font-medium">
                                {{ $project['tahun_target_submit'] ?? ($project['tahun_akreditasi'] ?? '2026') }}
                            </span>
                        </x-table.cell>

                        <!-- Status -->
                        <x-table.cell>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ ($project['status_project'] ?? $project['status'] ?? '') === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst((string) ($project['status_project'] ?? $project['status'] ?? 'Draft')) }}
                            </span>
                        </x-table.cell>

                        <!-- Action -->
                        <x-table.cell class="text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('kaprodi.project.detail', ['id' => $project['id']]) }}" class="text-additional hover:text-primary text-sm font-bold transition-colors">Detail ›</a>
                                <form action="{{ route('kaprodi.project.destroy', $project['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus project ini?');" class="inline">
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
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>
            
            <!-- Table Footer Counter -->
            <div class="py-3 px-6 bg-gray-50/60 border-t border-gray-100 text-gray-500 text-xs font-medium">
                {{ count($projects) }} baris project
            </div>
        </div>
    @endif

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
            
            <!-- STEP 1: PARAMETER -->
            <div id="step-1" class="block space-y-4">
                <div>
                    <label for="inputNama" class="block text-xs font-bold text-gray-800 mb-1.5">Nama Project <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="nama_project"
                        id="inputNama" 
                        required
                        placeholder="Contoh: Akreditasi Program Studi RPL 2026" 
                        class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="inputLembaga" class="block text-xs font-bold text-gray-800 mb-1.5">Lembaga</label>
                        <select 
                            id="inputLembaga"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                            @forelse ($lembagas ?? [] as $lemb)
                                <option value="{{ $lemb['id'] }}">{{ $lemb['nama_lembaga'] }}</option>
                            @empty
                                <option value="1">LAM INFOKOM</option>
                            @endforelse
                        </select>
                    </div>
                    <div>
                        <label for="inputInstrumen" class="block text-xs font-bold text-gray-800 mb-1.5">Versi Instrumen</label>
                        <select 
                            name="id_versi_instrumen"
                            id="inputInstrumen"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                            @forelse ($versis ?? [] as $ver)
                                <option value="{{ $ver['id'] }}">v{{ $ver['nomor_versi'] }}</option>
                            @empty
                                <option value="1">v2.1</option>
                            @endforelse
                        </select>
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
                        <select 
                            name="id_jenjang"
                            id="inputJenjang"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                            <option value="1">Sarjana (S1) / Sarjana Terapan (D4)</option>
                            <option value="2">Magister (S2)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="inputJenis" class="block text-xs font-bold text-gray-800 mb-1.5">Jenis Instrumen</label>
                        <select 
                            name="id_jenis_instrumen"
                            id="inputJenis"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                            <option value="1">Akreditasi Program Studi (APS)</option>
                        </select>
                    </div>
                    <div>
                        <label for="inputTahun" class="block text-xs font-bold text-gray-800 mb-1.5">Tahun Target Submit</label>
                        <input 
                            type="number" 
                            name="tahun_target_submit"
                            id="inputTahun"
                            value="{{ date('Y') }}"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                        />
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
            const tahun = document.getElementById('inputTahun')?.value || '2026';
            const summaryEl = document.getElementById('summaryText');
            if (summaryEl) {
                summaryEl.innerText = `${nama} · Target Tahun ${tahun}`;
            }
        }
    }
</script>
@endsection