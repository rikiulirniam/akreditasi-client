@extends('layouts.app')

@section('title', 'Nilai Mahasiswa')

@section('content')
<div class="space-y-6">
    <!-- Header Page & Stats -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Nilai Mahasiswa</h1>
            <p class="text-xs text-gray-500 mt-1">Kelola data nilai perkuliahan mahasiswa, unduh format template, dan impor berkas nilai Excel atau CSV.</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Download Template Dropdown -->
            <div class="relative inline-block text-left" id="templateDropdownContainer">
                <button 
                    type="button" 
                    id="templateDropdownBtn"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 text-xs font-bold hover:bg-gray-50 transition shadow-xs cursor-pointer"
                >
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Format Import</span>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div 
                    id="templateDropdownMenu" 
                    class="hidden absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 z-50 p-2 space-y-1"
                >
                    <div class="px-3 py-1.5 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        Per Kelas (Single Class)
                    </div>
                    <a 
                        href="{{ route('nilai-mahasiswa.template', ['mode' => 'single', 'format' => 'xlsx']) }}" 
                        class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition"
                    >
                        <span>Template Excel (.xlsx)</span>
                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">XLSX</span>
                    </a>
                    <a 
                        href="{{ route('nilai-mahasiswa.template', ['mode' => 'single', 'format' => 'csv']) }}" 
                        class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition"
                    >
                        <span>Template CSV (.csv)</span>
                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-blue-100 text-blue-800">CSV</span>
                    </a>

                    <div class="border-t border-gray-100 my-1"></div>

                    <div class="px-3 py-1.5 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        Semua Kelas (Multi Classes)
                    </div>
                    <a 
                        href="{{ route('nilai-mahasiswa.template', ['mode' => 'multi', 'format' => 'xlsx']) }}" 
                        class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition"
                    >
                        <span>Multi-Kelas Excel (.xlsx)</span>
                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">XLSX</span>
                    </a>
                    <a 
                        href="{{ route('nilai-mahasiswa.template', ['mode' => 'multi', 'format' => 'csv']) }}" 
                        class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition"
                    >
                        <span>Multi-Kelas CSV (.csv)</span>
                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-blue-100 text-blue-800">CSV</span>
                    </a>
                </div>
            </div>

            <!-- Import Excel / CSV Button -->
            @haspermission('nilai_mahasiswa.create')
            <button 
                type="button" 
                id="openImportModalBtn"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <span>Import Nilai</span>
            </button>
            @endhaspermission

            <!-- Tambah Nilai Manual Button -->
            @haspermission('nilai_mahasiswa.create')
            <button 
                type="button" 
                id="openCreateNilaiModalBtn"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-additional text-white text-xs font-bold transition shadow-xs cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Nilai</span>
            </button>
            @endhaspermission
        </div>
    </div>

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-gray-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Total Nilai Terdata</div>
                <div class="text-xl font-black text-gray-900 mt-0.5">{{ number_format($stats['total'] ?? 0) }}</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-gray-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Nilai Lulus (≥ C)</div>
                <div class="text-xl font-black text-emerald-600 mt-0.5">{{ number_format($stats['lulus'] ?? 0) }}</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-gray-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Rata-Rata Bobot</div>
                <div class="text-xl font-black text-blue-600 mt-0.5">{{ number_format($stats['avg_bobot'] ?? 0, 2, ',', '.') }} <span class="text-xs font-normal text-gray-500">/ 4,00</span></div>
            </div>
        </div>
    </div>

    <!-- Alert Flash for Import Errors / Warnings -->
    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800">
        <div class="font-bold flex items-center gap-2 text-rose-900 mb-1">
            <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            Catatan Kesalahan Saat Import (Sebagian baris gagal):
        </div>
        <ul class="list-disc list-inside space-y-0.5 mt-2 max-h-36 overflow-y-auto font-mono text-[11px]">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('import_warnings') && count(session('import_warnings')) > 0)
    <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-800">
        <div class="font-bold flex items-center gap-2 text-amber-900 mb-1">
            <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            Peringatan Import:
        </div>
        <ul class="list-disc list-inside space-y-0.5 mt-2 max-h-36 overflow-y-auto font-mono text-[11px]">
            @foreach(session('import_warnings') as $warn)
                <li>{{ $warn }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
        <form method="GET" action="{{ route('nilai-mahasiswa.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search Keyword -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-gray-600 mb-1">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Cari NIM, Mahasiswa, Mata Kuliah, Kelas..." 
                        class="w-full pl-10 pr-4 py-2 rounded-xl bg-gray-50 border border-gray-200 text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                    />
                </div>
            </div>

            <!-- Filter Kelas -->
            <div>
                <label class="block text-[11px] font-bold text-gray-600 mb-1">Kelas</label>
                <select 
                    name="kelas_id" 
                    class="w-full px-3 py-2 rounded-xl bg-gray-50 border border-gray-200 text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                        @php $kid = $k['id'] ?? ''; @endphp
                        <option value="{{ $kid }}" {{ (string) $selectedKelasId === (string) $kid ? 'selected' : '' }}>
                            {{ $k['kelas'] ?? ($k['nama_kelas'] ?? 'Kelas #'.$kid) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Mata Kuliah -->
            <div>
                <label class="block text-[11px] font-bold text-gray-600 mb-1">Mata Kuliah</label>
                <select 
                    name="mata_kuliah_id" 
                    class="w-full px-3 py-2 rounded-xl bg-gray-50 border border-gray-200 text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
                    <option value="">Semua Mata Kuliah</option>
                    @foreach($mataKuliahList as $mk)
                        @php $mid = $mk['id'] ?? ''; @endphp
                        <option value="{{ $mid }}" {{ (string) $selectedMataKuliahId === (string) $mid ? 'selected' : '' }}>
                            {{ $mk['kode_mata_kuliah'] ?? '' }} - {{ $mk['nama_mata_kuliah'] ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Semester & Actions -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Semester</label>
                    <select 
                        name="semester" 
                        class="w-full px-3 py-2 rounded-xl bg-gray-50 border border-gray-200 text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                        <option value="">Semua Semester</option>
                        <option value="Ganjil" {{ strtolower((string)$selectedSemester) === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                        <option value="Genap" {{ strtolower((string)$selectedSemester) === 'genap' ? 'selected' : '' }}>Genap</option>
                    </select>
                </div>
                <button 
                    type="submit" 
                    class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-additional transition cursor-pointer"
                    title="Terapkan Filter"
                >
                    Filter
                </button>
                @if($search || $selectedKelasId || $selectedMataKuliahId || $selectedSemester)
                <a 
                    href="{{ route('nilai-mahasiswa.index') }}" 
                    class="px-3 py-2 rounded-xl bg-gray-200 text-gray-700 text-xs font-bold hover:bg-gray-300 transition"
                    title="Reset Filter"
                >
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table: Data Nilai Mahasiswa -->
    <x-table>
        <x-slot:header>
            <x-table.th class="w-12 text-center">No</x-table.th>
            <x-table.th>Mahasiswa</x-table.th>
            <x-table.th>Kelas</x-table.th>
            <x-table.th>Mata Kuliah</x-table.th>
            <x-table.th class="text-center">Nilai Angka</x-table.th>
            <x-table.th class="text-center">Nilai Huruf</x-table.th>
            <x-table.th class="text-center">Bobot</x-table.th>
            <x-table.th>Semester / TA</x-table.th>
            <x-table.th class="w-32 text-right">Aksi</x-table.th>
        </x-slot:header>

        @forelse($nilaiList as $index => $item)
            @php
                $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                $mhs = $item['mahasiswa'] ?? [];
                $mkAk = $item['mata_kuliah_angkatan_kelas'] ?? [];
                $mk = $mkAk['enrollment_mata_kuliah']['mata_kuliah'] ?? [];
                $kelas = $mkAk['angkatan_kelas']['kelas'] ?? [];
                $ta = $item['tahun_akademik'] ?? ($mkAk['enrollment_mata_kuliah']['tahun_akademik'] ?? []);

                $nilaiHuruf = strtoupper(trim((string) ($item['nilai_huruf'] ?? '-')));
                $badgeBg = match($nilaiHuruf) {
                    'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'AB' => 'bg-teal-100 text-teal-800 border-teal-200',
                    'B' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'BC' => 'bg-sky-100 text-sky-800 border-sky-200',
                    'C' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'D' => 'bg-orange-100 text-orange-800 border-orange-200',
                    'E' => 'bg-rose-100 text-rose-800 border-rose-200',
                    default => 'bg-gray-100 text-gray-800 border-gray-200',
                };
            @endphp
            <x-table.row>
                <!-- No -->
                <x-table.cell class="text-center text-gray-500 font-medium">
                    {{ $rowNumber }}
                </x-table.cell>

                <!-- Mahasiswa (NIM & Nama) -->
                <x-table.cell>
                    <div class="font-extrabold text-xs text-gray-900 leading-tight">
                        {{ $mhs['nama_mahasiswa'] ?? '-' }}
                    </div>
                    <div class="text-[11px] font-mono text-gray-500 mt-0.5">
                        {{ $mhs['nim'] ?? '-' }}
                    </div>
                </x-table.cell>

                <!-- Kelas -->
                <x-table.cell>
                    <span class="inline-flex px-2 py-0.5 rounded-lg bg-gray-100 text-gray-800 font-mono text-xs font-bold">
                        {{ $kelas['kelas'] ?? ($kelas['nama_kelas'] ?? '-') }}
                    </span>
                </x-table.cell>

                <!-- Mata Kuliah -->
                <x-table.cell>
                    <div class="font-bold text-xs text-gray-900">
                        {{ $mk['nama_mata_kuliah'] ?? '-' }}
                    </div>
                    <div class="text-[11px] text-gray-500 font-mono flex items-center gap-1.5 mt-0.5">
                        <span>{{ $mk['kode_mata_kuliah'] ?? '-' }}</span>
                        @if(isset($mk['sks']))
                            <span>•</span>
                            <span class="font-sans font-semibold text-primary">{{ $mk['sks'] }} SKS</span>
                        @endif
                    </div>
                </x-table.cell>

                <!-- Nilai Angka -->
                <x-table.cell class="text-center">
                    <span class="font-mono text-xs font-bold text-gray-900">
                        {{ isset($item['nilai_angka']) ? number_format((float) $item['nilai_angka'], 2, ',', '.') : '-' }}
                    </span>
                </x-table.cell>

                <!-- Nilai Huruf -->
                <x-table.cell class="text-center">
                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md border text-xs font-black {{ $badgeBg }}">
                        {{ $nilaiHuruf }}
                    </span>
                </x-table.cell>

                <!-- Bobot -->
                <x-table.cell class="text-center">
                    <span class="font-mono text-xs font-bold text-gray-700">
                        {{ isset($item['bobot']) ? number_format((float) $item['bobot'], 2, ',', '.') : '-' }}
                    </span>
                </x-table.cell>

                <!-- Semester / Tahun Akademik -->
                <x-table.cell>
                    <div class="text-xs font-semibold text-gray-800">
                        {{ $ta['nama_tahun_akademik'] ?? ($ta['tahun_akademik'] ?? '-') }}
                    </div>
                    @if(isset($ta['semester']))
                        <div class="text-[11px] text-gray-500">
                            Semester {{ $ta['semester'] }}
                        </div>
                    @endif
                </x-table.cell>

                <!-- Aksi -->
                <x-table.cell class="text-right">
                    <div class="flex items-center justify-end gap-2">
                        <!-- Lihat KHS & Transkrip Mahasiswa -->
                        @if(!empty($item['mahasiswa_id']))
                        <button 
                            type="button" 
                            class="open-khs-modal-btn text-primary hover:text-additional transition p-1 cursor-pointer"
                            title="Lihat KHS & Transkrip Mahasiswa"
                            data-mahasiswa-id="{{ $item['mahasiswa_id'] }}"
                            data-mahasiswa-nama="{{ $mhs['nama_mahasiswa'] ?? '' }}"
                            data-mahasiswa-nim="{{ $mhs['nim'] ?? '' }}"
                        >
                            <x-icon.eye class="w-4 h-4" />
                        </button>
                        @endif

                        <!-- Edit Nilai -->
                        @haspermission('nilai_mahasiswa.update')
                        <button 
                            type="button" 
                            class="open-edit-nilai-btn text-amber-500 hover:text-amber-600 transition p-1 cursor-pointer"
                            title="Edit Nilai"
                            data-id="{{ $item['id'] }}"
                            data-mahasiswa-id="{{ $item['mahasiswa_id'] ?? '' }}"
                            data-mk-ak-id="{{ $item['mata_kuliah_angkatan_kelas_id'] ?? '' }}"
                            data-tahun-akademik-id="{{ $item['tahun_akademik_id'] ?? '' }}"
                            data-nilai-angka="{{ $item['nilai_angka'] ?? '' }}"
                            data-nilai-huruf="{{ $item['nilai_huruf'] ?? '' }}"
                            data-bobot="{{ $item['bobot'] ?? '' }}"
                            data-link-nilai="{{ $item['link_nilai'] ?? '' }}"
                            data-link-evidence="{{ $item['link_evidence'] ?? '' }}"
                            data-action="{{ route('nilai-mahasiswa.update', $item['id']) }}"
                        >
                            <x-icon.edit class="w-4 h-4" />
                        </button>
                        @endhaspermission

                        <!-- Hapus Nilai -->
                        @haspermission('nilai_mahasiswa.delete')
                        <button 
                            type="button" 
                            class="text-rose-600 hover:text-rose-700 transition p-1 cursor-pointer"
                            title="Hapus Nilai"
                            data-confirm-delete
                            data-action="{{ route('nilai-mahasiswa.destroy', $item['id']) }}"
                            data-title="Hapus Nilai Mahasiswa?"
                            data-message="Apakah Anda yakin ingin menghapus nilai untuk '{{ $mhs['nama_mahasiswa'] ?? 'Mahasiswa ini' }}' pada mata kuliah '{{ $mk['nama_mata_kuliah'] ?? 'ini' }}'?"
                        >
                            <x-icon.delete class="w-4 h-4" />
                        </button>
                        @endhaspermission
                    </div>
                </x-table.cell>
            </x-table.row>
        @empty
            <x-table.empty 
                colspan="10" 
                message="Belum ada data nilai mahasiswa" 
                submessage="Silakan klik tombol Import Nilai atau Tambah Nilai untuk menginput data nilai mahasiswa." 
            />
        @endforelse
    </x-table>

    <!-- Pagination -->
    <x-pagination :pagination="$pagination" />
</div>

<!-- ======================================================== -->
<!-- MODAL: Import Nilai Mahasiswa via Excel / CSV            -->
<!-- ======================================================== -->
<div 
    id="importNilaiModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="importNilaiModalTitle"
>
    <div class="import-modal-card relative bg-white rounded-[28px] p-6 sm:p-8 max-w-lg w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
                <div>
                    <h3 id="importNilaiModalTitle" class="text-base font-black text-gray-900 tracking-tight">
                        Import Nilai Mahasiswa
                    </h3>
                    <p class="text-xs text-gray-500">Unggah berkas spreadsheet Excel (.xlsx) atau CSV (.csv)</p>
                </div>
            </div>
            <button type="button" class="close-import-modal p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="importNilaiForm" action="{{ route('nilai-mahasiswa.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Template Quick Download Hint -->
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs text-emerald-900">
                <div class="font-bold flex items-center gap-1.5 mb-1 text-emerald-950">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    Belum memiliki template?
                </div>
                <p class="text-[11px] text-emerald-800 leading-relaxed mb-2">
                    Unduh template resmi dengan daftar mahasiswa dan mata kuliah yang sudah siap diisi nilai:
                </p>
                <div class="flex flex-wrap gap-2">
                    <a 
                        href="{{ route('nilai-mahasiswa.template', ['mode' => 'single', 'format' => 'xlsx']) }}" 
                        class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition shadow-2xs"
                    >
                        Unduh Template (.xlsx)
                    </a>
                    <a 
                        href="{{ route('nilai-mahasiswa.template', ['mode' => 'multi', 'format' => 'xlsx']) }}" 
                        class="px-2.5 py-1 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold text-[11px] transition"
                    >
                        Template Multi-Kelas (.xlsx)
                    </a>
                </div>
            </div>

            <!-- Upload File Input Dropzone -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">
                    Pilih File Excel / CSV <span class="text-rose-500">*</span>
                </label>
                <div 
                    id="dropzoneContainer"
                    class="relative border-2 border-dashed border-gray-300 hover:border-emerald-500 rounded-2xl p-6 text-center cursor-pointer transition bg-gray-50/50 hover:bg-emerald-50/30"
                >
                    <input 
                        type="file" 
                        name="file" 
                        id="importFileInput" 
                        accept=".xlsx,.csv,.tsv,.txt" 
                        required 
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                    />
                    <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                        <svg class="w-10 h-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <div class="text-xs font-semibold text-gray-700" id="dropzoneFilename">
                            Klik atau tarik berkas ke sini (.xlsx, .csv)
                        </div>
                        <div class="text-[10px] text-gray-400">Ukuran maksimal file: 10 MB</div>
                    </div>
                </div>
            </div>

            <!-- Opsi Penanganan Duplikasi Data -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">
                    Opsi Keamanan Duplikasi Data <span class="text-rose-500">*</span>
                </label>
                <div class="space-y-2">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                        <input 
                            type="radio" 
                            name="duplicate_action" 
                            value="update" 
                            checked 
                            class="mt-0.5 text-emerald-600 focus:ring-emerald-500"
                        />
                        <div>
                            <div class="text-xs font-bold text-gray-900">Perbarui Nilai yang Sudah Ada (Update) - Rekomendasi</div>
                            <div class="text-[11px] text-gray-500">Jika mahasiswa sudah memiliki nilai pada mata kuliah ini, nilainya akan ditimpa dengan nilai baru dari berkas.</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                        <input 
                            type="radio" 
                            name="duplicate_action" 
                            value="skip" 
                            class="mt-0.5 text-emerald-600 focus:ring-emerald-500"
                        />
                        <div>
                            <div class="text-xs font-bold text-gray-900">Lewati Baris Duplikat (Skip)</div>
                            <div class="text-[11px] text-gray-500">Hanya memasukkan mahasiswa yang belum memiliki nilai, baris yang sudah ada nilainya tidak akan diubah.</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                        <input 
                            type="radio" 
                            name="duplicate_action" 
                            value="fail" 
                            class="mt-0.5 text-emerald-600 focus:ring-emerald-500"
                        />
                        <div>
                            <div class="text-xs font-bold text-gray-900">Gagalkan Seluruh Impor (Fail)</div>
                            <div class="text-[11px] text-gray-500">Jika terdapat minimal satu data yang duplikat, seluruh transaksi impor akan dibatalkan.</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Optional Fallback: Mata Kuliah & Kelas -->
            <div class="pt-1">
                <label class="block text-xs font-bold text-gray-900 mb-1">
                    Mata Kuliah & Kelas Default (Opsional)
                </label>
                <select 
                    name="mata_kuliah_angkatan_kelas_id" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                >
                    <option value="">Deteksi Otomatis dari Berkas Excel/CSV</option>
                    @foreach($mkAkList as $mkItem)
                        @php
                            $cId = $mkItem['id'] ?? '';
                            $cMk = $mkItem['enrollment_mata_kuliah']['mata_kuliah']['nama_mata_kuliah'] ?? 'Mata Kuliah';
                            $cKelas = $mkItem['angkatan_kelas']['kelas']['kelas'] ?? ($mkItem['angkatan_kelas']['kelas']['nama_kelas'] ?? 'Kelas');
                        @endphp
                        <option value="{{ $cId }}">{{ $cMk }} (Kelas: {{ $cKelas }})</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-gray-500 mt-1">Dibutuhkan jika file template Anda tidak memiliki kolom kode mata kuliah atau nama kelas.</p>
            </div>

            <!-- Actions -->
            <div class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-100">
                <button 
                    type="button" 
                    class="close-import-modal w-full py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    id="submitImportBtn"
                    class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm cursor-pointer text-center flex items-center justify-center gap-2"
                >
                    <span id="submitImportBtnText">Mulai Impor</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: Tambah Nilai Mahasiswa Manual                     -->
<!-- ======================================================== -->
<div 
    id="createNilaiModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createNilaiModalTitle"
>
    <div class="create-modal-card relative bg-white rounded-[28px] p-6 sm:p-8 max-w-lg w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createNilaiModalTitle" class="text-lg font-black text-gray-900 tracking-tight text-center mb-5">
            Tambah Nilai Mahasiswa
        </h3>

        <form action="{{ route('nilai-mahasiswa.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Field: Mahasiswa -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1">
                    Mahasiswa <span class="text-rose-500">*</span>
                </label>
                <select 
                    name="mahasiswa_id" 
                    required 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white-form border border-gray-200 text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
                    <option value="">Pilih Mahasiswa</option>
                    @foreach($mahasiswaList as $m)
                        <option value="{{ $m['id'] }}">
                            {{ $m['nim'] ?? '' }} - {{ $m['nama_mahasiswa'] ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Field: Mata Kuliah & Kelas -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1">
                    Mata Kuliah & Kelas <span class="text-rose-500">*</span>
                </label>
                <select 
                    name="mata_kuliah_angkatan_kelas_id" 
                    required 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white-form border border-gray-200 text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
                    <option value="">Pilih Mata Kuliah & Kelas</option>
                    @foreach($mkAkList as $item)
                        @php
                            $cId = $item['id'] ?? '';
                            $cMk = $item['enrollment_mata_kuliah']['mata_kuliah']['nama_mata_kuliah'] ?? 'Mata Kuliah';
                            $cKelas = $item['angkatan_kelas']['kelas']['kelas'] ?? ($item['angkatan_kelas']['kelas']['nama_kelas'] ?? 'Kelas');
                        @endphp
                        <option value="{{ $cId }}">{{ $cMk }} (Kelas: {{ $cKelas }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Field: Nilai Angka, Nilai Huruf, Bobot -->
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-900 mb-1">
                        Nilai Angka
                    </label>
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        max="100" 
                        name="nilai_angka" 
                        id="createNilaiAngkaInput" 
                        placeholder="85.50" 
                        class="w-full px-3 py-2 rounded-xl bg-white-form border border-gray-200 text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                    />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-900 mb-1">
                        Nilai Huruf
                    </label>
                    <select 
                        name="nilai_huruf" 
                        id="createNilaiHurufSelect"
                        class="w-full px-3 py-2 rounded-xl bg-white-form border border-gray-200 text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                        <option value="">Pilih</option>
                        <option value="A">A</option>
                        <option value="AB">AB</option>
                        <option value="B">B</option>
                        <option value="BC">BC</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-900 mb-1">
                        Bobot
                    </label>
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        max="4" 
                        name="bobot" 
                        id="createBobotInput" 
                        placeholder="4.00" 
                        class="w-full px-3 py-2 rounded-xl bg-gray-100 border border-gray-200 text-xs font-mono font-bold text-gray-900 focus:outline-none"
                    />
                </div>
            </div>

            <!-- Field: Link Nilai & Evidence -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1">
                    Link Spreadsheet / Dokumen Nilai
                </label>
                <input 
                    type="url" 
                    name="link_nilai" 
                    placeholder="https://docs.google.com/..." 
                    class="w-full px-3 py-2 rounded-xl bg-white-form border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                />
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1">
                    Link Evidence / Bukti Nilai
                </label>
                <input 
                    type="url" 
                    name="link_evidence" 
                    placeholder="https://drive.google.com/..." 
                    class="w-full px-3 py-2 rounded-xl bg-white-form border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                />
            </div>

            <div class="grid grid-cols-2 gap-3 pt-3">
                <button 
                    type="button" 
                    class="close-create-modal w-full py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-2.5 px-4 rounded-xl bg-primary hover:bg-additional text-white font-bold text-xs transition shadow-sm cursor-pointer text-center"
                >
                    Simpan Nilai
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: Edit Nilai Mahasiswa                              -->
<!-- ======================================================== -->
<div 
    id="editNilaiModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editNilaiModalTitle"
>
    <div class="edit-modal-card relative bg-white rounded-[28px] p-6 sm:p-8 max-w-lg w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editNilaiModalTitle" class="text-lg font-black text-gray-900 tracking-tight text-center mb-5">
            Edit Nilai Mahasiswa
        </h3>

        <form id="editNilaiForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field: Nilai Angka, Nilai Huruf, Bobot -->
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-900 mb-1">
                        Nilai Angka
                    </label>
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        max="100" 
                        name="nilai_angka" 
                        id="editNilaiAngkaInput" 
                        class="w-full px-3 py-2 rounded-xl bg-white-form border border-gray-200 text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                    />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-900 mb-1">
                        Nilai Huruf
                    </label>
                    <select 
                        name="nilai_huruf" 
                        id="editNilaiHurufSelect"
                        class="w-full px-3 py-2 rounded-xl bg-white-form border border-gray-200 text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                        <option value="">Pilih</option>
                        <option value="A">A</option>
                        <option value="AB">AB</option>
                        <option value="B">B</option>
                        <option value="BC">BC</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-900 mb-1">
                        Bobot
                    </label>
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        max="4" 
                        name="bobot" 
                        id="editBobotInput" 
                        class="w-full px-3 py-2 rounded-xl bg-gray-100 border border-gray-200 text-xs font-mono font-bold text-gray-900 focus:outline-none"
                    />
                </div>
            </div>

            <!-- Field: Link Nilai & Evidence -->
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1">
                    Link Dokumen Nilai
                </label>
                <input 
                    type="url" 
                    name="link_nilai" 
                    id="editLinkNilaiInput"
                    placeholder="https://docs.google.com/..." 
                    class="w-full px-3 py-2 rounded-xl bg-white-form border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                />
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1">
                    Link Evidence / Bukti
                </label>
                <input 
                    type="url" 
                    name="link_evidence" 
                    id="editLinkEvidenceInput"
                    placeholder="https://drive.google.com/..." 
                    class="w-full px-3 py-2 rounded-xl bg-white-form border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20"
                />
            </div>

            <div class="grid grid-cols-2 gap-3 pt-3">
                <button 
                    type="button" 
                    class="close-edit-modal w-full py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-2.5 px-4 rounded-xl bg-primary hover:bg-additional text-white font-bold text-xs transition shadow-sm cursor-pointer text-center"
                >
                    Perbarui Nilai
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: KHS & Transkrip Akademik Mahasiswa                -->
<!-- ======================================================== -->
<div 
    id="khsTranskripModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="khsModalTitle"
>
    <div class="khs-modal-card relative bg-white rounded-[28px] p-6 sm:p-8 max-w-2xl w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
            <div>
                <h3 id="khsModalTitle" class="text-base font-black text-gray-900 tracking-tight">
                    Catatan Akademik Mahasiswa
                </h3>
                <p class="text-xs text-gray-500" id="khsStudentSubtitle">-</p>
            </div>
            <button type="button" class="close-khs-modal p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Tab Switches: KHS vs Transkrip -->
        <div class="flex border-b border-gray-200 mb-4">
            <button 
                type="button" 
                id="tabKhsBtn"
                class="py-2.5 px-4 font-bold text-xs border-b-2 border-primary text-primary transition cursor-pointer"
            >
                Kartu Hasil Studi (KHS)
            </button>
            <button 
                type="button" 
                id="tabTranskripBtn"
                class="py-2.5 px-4 font-bold text-xs border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition cursor-pointer"
            >
                Transkrip Nilai Kumulatif (IPK)
            </button>
        </div>

        <!-- Tab Content 1: KHS -->
        <div id="tabKhsContent" class="space-y-4">
            <div id="khsLoading" class="py-8 text-center text-xs text-gray-500">Memuat data KHS...</div>
            <div id="khsDataContainer" class="hidden space-y-4">
                <!-- IP Banner -->
                <div class="p-4 rounded-2xl bg-primary/5 border border-primary/20 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-primary">Indeks Prestasi Semester (IP)</div>
                        <div class="text-xs text-gray-500 mt-0.5">Rumus: Σ (SKS × Bobot) ÷ Σ SKS</div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-black text-primary" id="khsIpValue">0.00</div>
                        <div class="text-[11px] text-gray-500" id="khsSksValue">Total 0 SKS • Kredit 0</div>
                    </div>
                </div>

                <!-- Table KHS -->
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-200">
                            <tr>
                                <th class="p-2.5">Mata Kuliah</th>
                                <th class="p-2.5 text-center">SKS</th>
                                <th class="p-2.5 text-center">Nilai</th>
                                <th class="p-2.5 text-center">Bobot</th>
                                <th class="p-2.5 text-center">Kredit (SKS × Bobot)</th>
                            </tr>
                        </thead>
                        <tbody id="khsTableBody" class="divide-y divide-gray-100">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Content 2: Transkrip Kumulatif -->
        <div id="tabTranskripContent" class="hidden space-y-4">
            <div id="transkripLoading" class="py-8 text-center text-xs text-gray-500">Memuat transkrip...</div>
            <div id="transkripDataContainer" class="hidden space-y-4">
                <!-- IPK Banner -->
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Indeks Prestasi Kumulatif (IPK)</div>
                        <div class="text-xs text-emerald-700 mt-0.5">Perhitungan seluruh semester berbobot SKS resmi</div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-black text-emerald-700" id="transkripIpkValue">0.00</div>
                        <div class="text-[11px] text-emerald-800" id="transkripSksValue">Total 0 SKS • Kredit 0</div>
                    </div>
                </div>

                <!-- Table Transkrip -->
                <div class="overflow-x-auto rounded-xl border border-gray-200 max-h-64 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-200 sticky top-0">
                            <tr>
                                <th class="p-2.5">Mata Kuliah</th>
                                <th class="p-2.5 text-center">SKS</th>
                                <th class="p-2.5 text-center">Nilai</th>
                                <th class="p-2.5 text-center">Bobot</th>
                                <th class="p-2.5 text-center">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="transkripTableBody" class="divide-y divide-gray-100">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 mt-4 text-right">
            <button 
                type="button" 
                class="close-khs-modal px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition cursor-pointer"
            >
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal Helper Functions
        function openDialog(modal) {
            if (!modal) return;
            const card = modal.querySelector('.import-modal-card, .create-modal-card, .edit-modal-card, .khs-modal-card');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function closeDialog(modal) {
            if (!modal) return;
            const card = modal.querySelector('.import-modal-card, .create-modal-card, .edit-modal-card, .khs-modal-card');
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        // Template Dropdown Toggle
        const templateDropdownBtn = document.getElementById('templateDropdownBtn');
        const templateDropdownMenu = document.getElementById('templateDropdownMenu');
        if (templateDropdownBtn && templateDropdownMenu) {
            templateDropdownBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                templateDropdownMenu.classList.toggle('hidden');
            });
            document.addEventListener('click', function () {
                templateDropdownMenu.classList.add('hidden');
            });
        }

        // Import Modal Handlers
        const importModal = document.getElementById('importNilaiModal');
        const openImportModalBtn = document.getElementById('openImportModalBtn');
        openImportModalBtn?.addEventListener('click', () => openDialog(importModal));
        document.querySelectorAll('.close-import-modal').forEach(btn => {
            btn.addEventListener('click', () => closeDialog(importModal));
        });

        // Dropzone Filename Preview
        const importFileInput = document.getElementById('importFileInput');
        const dropzoneFilename = document.getElementById('dropzoneFilename');
        if (importFileInput && dropzoneFilename) {
            importFileInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    dropzoneFilename.textContent = 'Berkas dipilih: ' + this.files[0].name;
                    dropzoneFilename.classList.add('text-emerald-700', 'font-bold');
                } else {
                    dropzoneFilename.textContent = 'Klik atau tarik berkas ke sini (.xlsx, .csv)';
                    dropzoneFilename.classList.remove('text-emerald-700', 'font-bold');
                }
            });
        }

        // Import Form Submit Loading State
        const importForm = document.getElementById('importNilaiForm');
        const submitImportBtn = document.getElementById('submitImportBtn');
        const submitImportBtnText = document.getElementById('submitImportBtnText');
        if (importForm && submitImportBtn) {
            importForm.addEventListener('submit', function () {
                submitImportBtn.disabled = true;
                submitImportBtn.classList.add('opacity-75');
                submitImportBtnText.textContent = 'Sedang Memproses Impor...';
            });
        }

        // Create Modal Handlers & Automatic Grade Calculation
        const createModal = document.getElementById('createNilaiModal');
        const openCreateNilaiModalBtn = document.getElementById('openCreateNilaiModalBtn');
        openCreateNilaiModalBtn?.addEventListener('click', () => openDialog(createModal));
        document.querySelectorAll('.close-create-modal').forEach(btn => {
            btn.addEventListener('click', () => closeDialog(createModal));
        });

        function convertNilaiAngka(angka) {
            if (isNaN(angka) || angka === '') return { huruf: '', bobot: '' };
            const n = parseFloat(angka);
            if (n >= 85) return { huruf: 'A', bobot: 4.0 };
            if (n >= 80) return { huruf: 'AB', bobot: 3.5 };
            if (n >= 70) return { huruf: 'B', bobot: 3.0 };
            if (n >= 65) return { huruf: 'BC', bobot: 2.5 };
            if (n >= 55) return { huruf: 'C', bobot: 2.0 };
            if (n >= 40) return { huruf: 'D', bobot: 1.0 };
            return { huruf: 'E', bobot: 0.0 };
        }

        const createNilaiAngkaInput = document.getElementById('createNilaiAngkaInput');
        const createNilaiHurufSelect = document.getElementById('createNilaiHurufSelect');
        const createBobotInput = document.getElementById('createBobotInput');

        createNilaiAngkaInput?.addEventListener('input', function () {
            const res = convertNilaiAngka(this.value);
            if (res.huruf) {
                createNilaiHurufSelect.value = res.huruf;
                createBobotInput.value = res.bobot.toFixed(2);
            }
        });

        createNilaiHurufSelect?.addEventListener('change', function () {
            const bobotMap = { 'A': 4.0, 'AB': 3.5, 'B': 3.0, 'BC': 2.5, 'C': 2.0, 'D': 1.0, 'E': 0.0 };
            if (bobotMap[this.value] !== undefined) {
                createBobotInput.value = bobotMap[this.value].toFixed(2);
            }
        });

        // Edit Modal Handlers
        const editModal = document.getElementById('editNilaiModal');
        const editForm = document.getElementById('editNilaiForm');
        const editNilaiAngkaInput = document.getElementById('editNilaiAngkaInput');
        const editNilaiHurufSelect = document.getElementById('editNilaiHurufSelect');
        const editBobotInput = document.getElementById('editBobotInput');
        const editLinkNilaiInput = document.getElementById('editLinkNilaiInput');
        const editLinkEvidenceInput = document.getElementById('editLinkEvidenceInput');

        document.querySelectorAll('.open-edit-nilai-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                editForm.action = this.dataset.action;
                editNilaiAngkaInput.value = this.dataset.nilaiAngka ?? '';
                editNilaiHurufSelect.value = this.dataset.nilaiHuruf ?? '';
                editBobotInput.value = this.dataset.bobot ?? '';
                editLinkNilaiInput.value = this.dataset.linkNilai ?? '';
                editLinkEvidenceInput.value = this.dataset.linkEvidence ?? '';
                openDialog(editModal);
            });
        });

        editNilaiAngkaInput?.addEventListener('input', function () {
            const res = convertNilaiAngka(this.value);
            if (res.huruf) {
                editNilaiHurufSelect.value = res.huruf;
                editBobotInput.value = res.bobot.toFixed(2);
            }
        });

        editNilaiHurufSelect?.addEventListener('change', function () {
            const bobotMap = { 'A': 4.0, 'AB': 3.5, 'B': 3.0, 'BC': 2.5, 'C': 2.0, 'D': 1.0, 'E': 0.0 };
            if (bobotMap[this.value] !== undefined) {
                editBobotInput.value = bobotMap[this.value].toFixed(2);
            }
        });

        document.querySelectorAll('.close-edit-modal').forEach(btn => {
            btn.addEventListener('click', () => closeDialog(editModal));
        });

        // KHS & Transkrip Viewer Modal Handlers
        const khsModal = document.getElementById('khsTranskripModal');
        const khsStudentSubtitle = document.getElementById('khsStudentSubtitle');
        const tabKhsBtn = document.getElementById('tabKhsBtn');
        const tabTranskripBtn = document.getElementById('tabTranskripBtn');
        const tabKhsContent = document.getElementById('tabKhsContent');
        const tabTranskripContent = document.getElementById('tabTranskripContent');

        let currentActiveMahasiswaId = null;

        tabKhsBtn?.addEventListener('click', function () {
            tabKhsBtn.classList.add('border-primary', 'text-primary');
            tabKhsBtn.classList.remove('border-transparent', 'text-gray-500');
            tabTranskripBtn.classList.remove('border-primary', 'text-primary');
            tabTranskripBtn.classList.add('border-transparent', 'text-gray-500');
            tabKhsContent.classList.remove('hidden');
            tabTranskripContent.classList.add('hidden');
        });

        tabTranskripBtn?.addEventListener('click', function () {
            tabTranskripBtn.classList.add('border-primary', 'text-primary');
            tabTranskripBtn.classList.remove('border-transparent', 'text-gray-500');
            tabKhsBtn.classList.remove('border-primary', 'text-primary');
            tabKhsBtn.classList.add('border-transparent', 'text-gray-500');
            tabTranskripContent.classList.remove('hidden');
            tabKhsContent.classList.add('hidden');

            if (currentActiveMahasiswaId) {
                loadTranskripData(currentActiveMahasiswaId);
            }
        });

        document.querySelectorAll('.open-khs-modal-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const mhsId = this.dataset.mahasiswaId;
                const mhsNama = this.dataset.mahasiswaNama;
                const mhsNim = this.dataset.mahasiswaNim;
                currentActiveMahasiswaId = mhsId;

                khsStudentSubtitle.textContent = mhsNama + ' (' + mhsNim + ')';

                // Reset ke Tab KHS
                tabKhsBtn.click();
                loadKhsData(mhsId);
                openDialog(khsModal);
            });
        });

        document.querySelectorAll('.close-khs-modal').forEach(btn => {
            btn.addEventListener('click', () => closeDialog(khsModal));
        });

        function loadKhsData(mhsId) {
            const loading = document.getElementById('khsLoading');
            const container = document.getElementById('khsDataContainer');
            const tbody = document.getElementById('khsTableBody');
            const ipVal = document.getElementById('khsIpValue');
            const sksVal = document.getElementById('khsSksValue');

            loading.classList.remove('hidden');
            container.classList.add('hidden');
            tbody.innerHTML = '';

            fetch(`/nilai-mahasiswa/khs/${mhsId}`)
                .then(r => r.json())
                .then(res => {
                    loading.classList.add('hidden');
                    container.classList.remove('hidden');

                    const d = res.data ?? {};
                    const ringkasan = d.ringkasan ?? {};
                    const items = d.items ?? [];

                    ipVal.textContent = (ringkasan.ip ?? 0).toFixed(2);
                    sksVal.textContent = `Total ${ringkasan.total_sks ?? 0} SKS • Kredit ${ringkasan.total_kredit ?? 0}`;

                    if (items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-500">Belum ada nilai terdaftar untuk KHS mahasiswa ini.</td></tr>';
                        return;
                    }

                    tbody.innerHTML = items.map(it => `
                        <tr>
                            <td class="p-2.5 font-bold text-gray-900">${it.nama_mata_kuliah ?? '-'} <span class="font-normal text-gray-500 font-mono text-[11px]">(${it.kode_mata_kuliah ?? ''})</span></td>
                            <td class="p-2.5 text-center font-mono font-bold">${it.sks ?? 0}</td>
                            <td class="p-2.5 text-center font-black text-primary">${it.nilai_huruf ?? '-'}</td>
                            <td class="p-2.5 text-center font-mono">${(it.bobot ?? 0).toFixed(2)}</td>
                            <td class="p-2.5 text-center font-mono font-bold text-gray-900">${(it.kredit ?? 0).toFixed(2)}</td>
                        </tr>
                    `).join('');
                })
                .catch(err => {
                    loading.innerHTML = '<span class="text-rose-600">Gagal memuat data KHS.</span>';
                });
        }

        function loadTranskripData(mhsId) {
            const loading = document.getElementById('transkripLoading');
            const container = document.getElementById('transkripDataContainer');
            const tbody = document.getElementById('transkripTableBody');
            const ipkVal = document.getElementById('transkripIpkValue');
            const sksVal = document.getElementById('transkripSksValue');

            loading.classList.remove('hidden');
            container.classList.add('hidden');
            tbody.innerHTML = '';

            fetch(`/nilai-mahasiswa/transkrip/${mhsId}`)
                .then(r => r.json())
                .then(res => {
                    loading.classList.add('hidden');
                    container.classList.remove('hidden');

                    const d = res.data ?? {};
                    const ringkasan = d.ringkasan ?? {};
                    const items = d.mata_kuliah ?? [];

                    ipkVal.textContent = (ringkasan.ipk ?? 0).toFixed(2);
                    sksVal.textContent = `Total ${ringkasan.total_sks ?? 0} SKS • Kredit ${ringkasan.total_kredit ?? 0}`;

                    if (items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-500">Belum ada mata kuliah yang tercatat pada transkrip.</td></tr>';
                        return;
                    }

                    tbody.innerHTML = items.map(it => `
                        <tr>
                            <td class="p-2.5 font-bold text-gray-900">${it.nama_mata_kuliah ?? '-'} <span class="font-normal text-gray-500 font-mono text-[11px]">(${it.kode_mata_kuliah ?? ''})</span></td>
                            <td class="p-2.5 text-center font-mono font-bold">${it.sks ?? 0}</td>
                            <td class="p-2.5 text-center font-black text-emerald-700">${it.nilai_huruf ?? '-'}</td>
                            <td class="p-2.5 text-center font-mono">${(it.bobot ?? 0).toFixed(2)}</td>
                            <td class="p-2.5 text-center font-mono font-bold text-gray-900">${(it.kredit ?? 0).toFixed(2)}</td>
                        </tr>
                    `).join('');
                })
                .catch(err => {
                    loading.innerHTML = '<span class="text-rose-600">Gagal memuat transkrip.</span>';
                });
        }
    });
</script>
@endsection
