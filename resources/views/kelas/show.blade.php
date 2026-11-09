@extends('layouts.app')

@php
    $namaKelas = $kelas['kelas'] ?? ($kelas['nama_kelas'] ?? ($kelas['nama'] ?? 'Kelas'));
    $angkatan = $kelas['angkatan']['angkatan'] ?? ($kelas['angkatan'] ?? '-');
    $namaProdi = $kelas['prodi']['nama_prodi'] ?? ($kelas['prodi']['nama'] ?? ($kelas['nama_prodi'] ?? '-'));
    $tahunAkademik = $tahunAkademikAktif ?? ($kelas['tahun_akademik']['nama_tahun_akademik'] ?? ($kelas['tahun_akademik']['tahun'] ?? '-'));
@endphp

@section('title', 'Detail Kelas')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb / Back Link -->
    <div class="flex items-center justify-between gap-3">
        <h2 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight">
            Detail Kelas
        </h2>
        <a 
            href="{{ route('kelas.index') }}" 
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white-form hover:bg-gray-200 text-gray-800 text-xs sm:text-sm font-bold transition cursor-pointer"
            title="Kembali ke Daftar Kelas"
        >
            <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    <!-- Main Detail Card -->
    <div class="bg-white rounded-2xl border border-gray-200/90 p-6 sm:p-8 shadow-xs">
        <!-- Card Header: Title & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-gray-100">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                {{ $namaKelas }}
            </h1>

            <div class="flex items-center gap-3 shrink-0">
                @haspermission('kelas.delete')
                <button 
                    type="button" 
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#C9352B] hover:bg-[#b02c23] text-white font-bold text-xs sm:text-sm transition cursor-pointer shadow-xs"
                    data-confirm-delete
                    data-action="{{ route('kelas.destroy', $kelas['id']) }}"
                    data-title="Hapus Data Kelas?"
                    data-message="Apakah Anda yakin ingin menghapus data Kelas {{ $namaKelas }}? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                >
                    <x-icon.delete class="w-4 h-4" />
                    <span>Hapus Data</span>
                </button>
                @endhaspermission
            </div>
        </div>

        <!-- 2-Column Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8 pt-6 text-xs sm:text-sm">
            <!-- Left Column -->
            <div class="space-y-4">
                <!-- Nama Kelas -->
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-md bg-blue-50 text-primary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-5.25 6.557q0-.36.03-.715" />
                        </svg>
                    </div>
                    <span class="text-gray-900">
                        <span class="font-bold text-gray-900">Nama Kelas:</span>
                        <span class="font-mono font-bold ml-1 text-primary">{{ $namaKelas }}</span>
                    </span>
                </div>

                <!-- Program Studi -->
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-md bg-blue-50 text-primary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                        </svg>
                    </div>
                    <span class="text-gray-900">
                        <span class="font-bold text-gray-900">Program Studi:</span>
                        <span class="ml-1 text-gray-700">{{ $namaProdi }}</span>
                    </span>
                </div>

                <!-- Tahun Angkatan -->
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-md bg-blue-50 text-primary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <span class="text-gray-900">
                        <span class="font-bold text-gray-900">Tahun Angkatan:</span>
                        <span class="ml-1 text-gray-700">{{ $angkatan }}</span>
                    </span>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
                <!-- Tahun Akademik -->
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-md bg-blue-50 text-primary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-gray-900">
                        <span class="font-bold text-gray-900">Tahun Akademik:</span>
                        <span class="ml-1 text-gray-700">{{ $tahunAkademik }}</span>
                    </span>
                </div>

                <!-- Jumlah Mahasiswa -->
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-md bg-blue-50 text-primary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <span class="text-gray-900">
                        <span class="font-bold text-gray-900">Jumlah Mahasiswa:</span>
                        <span class="ml-1 font-bold text-primary">{{ count($mahasiswaList) }} Mahasiswa</span>
                    </span>
                </div>

                <!-- Jumlah Mata Kuliah -->
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-md bg-blue-50 text-primary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <span class="text-gray-900">
                        <span class="font-bold text-gray-900">Jumlah Mata Kuliah:</span>
                        <span class="ml-1 font-bold text-primary">{{ count($mataKuliahList) }} Mata Kuliah</span>
                        <span class="text-[11px] text-emerald-600 font-semibold ml-1">(Semester Aktif)</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel 1: Daftar Mahasiswa -->
    <div class="space-y-4 pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h3 class="text-lg font-bold text-gray-900 tracking-tight">
                Daftar Mahasiswa Kelas {{ $namaKelas }}
            </h3>
            <div class="relative w-full max-w-xs">
                <input 
                    type="text" 
                    id="searchMahasiswaInput" 
                    placeholder="Cari mahasiswa..." 
                    class="w-full px-4 py-2 pr-10 rounded-lg bg-[#E5E7EB] border-none text-xs font-medium text-gray-800 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
            </div>
        </div>

        <x-table>
            <x-slot:header>
                <x-table.th class="w-16 text-center">No</x-table.th>
                <x-table.th class="w-44">NIM</x-table.th>
                <x-table.th>Nama Mahasiswa</x-table.th>
                <x-table.th class="w-28 text-center">Status</x-table.th>
                <x-table.th class="w-24 text-center">IPK</x-table.th>
                <x-table.th class="w-32 text-center">Prestasi</x-table.th>
                <x-table.th class="w-20 text-center">Action</x-table.th>
            </x-slot:header>

            @forelse($mahasiswaList as $index => $mhs)
                @php 
                    $status = $mhs['status'] ?? 'Aktif';
                    $namaMahasiswa = $mhs['nama_mahasiswa'] ?? ($mhs['nama'] ?? ($mhs['name'] ?? '-'));
                    $nimMahasiswa = $mhs['nim_display'] ?? ($mhs['nim'] ?? '-');
                    
                    $ipkVal = $mhs['ipk'] ?? ($mhs['gpa'] ?? null);
                    $ipk = ($ipkVal !== null && $ipkVal !== '') ? number_format((float)$ipkVal, 2) : '-';
                    
                    $prestasiVal = $mhs['jumlah_prestasi'] ?? ($mhs['prestasi'] ?? null);
                    if ($prestasiVal !== null && $prestasiVal !== '') {
                        $prestasi = is_numeric($prestasiVal) ? $prestasiVal . ' Prestasi' : $prestasiVal;
                    } else {
                        $prestasi = '-';
                    }
                @endphp
                <x-table.row class="mahasiswa-row">
                    <x-table.cell class="text-gray-500 font-medium text-center w-16">{{ $index + 1 }}.</x-table.cell>
                    <x-table.cell class="font-mono text-gray-700 text-sm whitespace-nowrap mahasiswa-nim w-44">{{ $nimMahasiswa }}</x-table.cell>
                    <x-table.cell class="font-medium text-gray-900 whitespace-nowrap mahasiswa-nama">{{ $namaMahasiswa }}</x-table.cell>
                    <x-table.cell class="text-center w-28">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ strtolower($status) === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $status }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="font-bold text-gray-900 text-center w-24">{{ $ipk }}</x-table.cell>
                    <x-table.cell class="text-gray-700 text-sm text-center whitespace-nowrap w-32">{{ $prestasi }}</x-table.cell>
                    <x-table.cell class="text-center w-20">
                        <div class="flex items-center justify-center">
                            @if(Route::has('mahasiswa.show') && !empty($mhs['id']))
                                <a href="{{ route('mahasiswa.show', $mhs['id']) }}" class="text-primary hover:text-additional p-1 cursor-pointer" title="Lihat Detail">
                                    <x-icon.eye class="w-5 h-5" />
                                </a>
                            @else
                                <button type="button" class="text-primary hover:text-additional p-1 cursor-pointer" title="Lihat Detail">
                                    <x-icon.eye class="w-5 h-5" />
                                </button>
                            @endif
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="7" message="Belum ada data mahasiswa" />
            @endforelse
        </x-table>
    </div>

    <!-- Tabel 2: Daftar Mata Kuliah -->
    <div class="space-y-4 pt-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h3 class="text-lg font-bold text-gray-900 tracking-tight">
                Daftar Mata Kuliah Kelas {{ $namaKelas }} @if($tahunAkademik !== '-') (Tahun Akademik {{ $tahunAkademik }}) @endif
            </h3>
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="relative w-full sm:w-64">
                    <input 
                        type="text" 
                        id="searchMataKuliahInput" 
                        placeholder="Cari mata kuliah..." 
                        class="w-full px-4 py-2 pr-10 rounded-lg bg-[#E5E7EB] border-none text-xs font-medium text-gray-800 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                </div>

                @haspermission('kelas.update')
                <button 
                    type="button" 
                    class="open-create-mk-kelas-btn inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-white text-xs sm:text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Mata Kuliah
                </button>
                @endhaspermission
            </div>
        </div>

        <x-table>
            <x-slot:header>
                <x-table.th class="w-16 text-center">No</x-table.th>
                <x-table.th class="w-44">Kode MK</x-table.th>
                <x-table.th>Nama Mata Kuliah</x-table.th>
                <x-table.th class="w-24 text-center">SKS</x-table.th>
                <x-table.th class="w-64">Dosen Pengampu</x-table.th>
                <x-table.th class="w-28 text-center">Action</x-table.th>
            </x-slot:header>

            @forelse($mataKuliahList as $index => $mk)
                @php
                    $kodeMk = $mk['kode_mata_kuliah'] ?? ($mk['kode_mk'] ?? ($mk['kode'] ?? '-'));
                    $namaMk = $mk['nama_mata_kuliah'] ?? ($mk['nama_mk'] ?? ($mk['nama'] ?? '-'));
                    $sks = $mk['sks'] ?? '-';
                    $dosen = $mk['dosen_pengampu'] ?? ($mk['dosen'] ?? '-');
                    $enrollmentId = $mk['enrollment_mata_kuliah_id'] ?? ($mk['enrollment_id'] ?? null);
                    $mkAkId = $mk['mata_kuliah_angkatan_kelas_id'] ?? null;
                    $mkTaId = $mk['tahun_akademik_id'] ?? ($activeTahunAkademikId ?? 1);
                    $dosenId = $mk['dosen_id'] ?? null;
                @endphp
                <x-table.row class="matakuliah-row">
                    <x-table.cell class="text-gray-500 font-medium text-center w-16">{{ $index + 1 }}.</x-table.cell>
                    <x-table.cell class="font-mono text-gray-700 text-sm whitespace-nowrap mk-kode w-44">{{ $kodeMk }}</x-table.cell>
                    <x-table.cell class="font-medium text-gray-900 whitespace-nowrap mk-nama">{{ $namaMk }}</x-table.cell>
                    <x-table.cell class="text-gray-700 font-semibold text-sm text-center w-24">{{ $sks }} SKS</x-table.cell>
                    <x-table.cell class="text-gray-700 text-sm whitespace-nowrap w-64">
                        @if($dosen && $dosen !== '-')
                            <span class="inline-flex items-center gap-1.5 font-medium text-gray-900">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                {{ $dosen }}
                            </span>
                        @else
                            <span class="text-gray-400 italic">Belum ditentukan</span>
                        @endif
                    </x-table.cell>
                    <x-table.cell class="text-center w-28">
                        <div class="flex items-center justify-center gap-2">
                            @if(Route::has('mata-kuliah.show') && !empty($mk['id']))
                                <a href="{{ route('mata-kuliah.show', $mk['id']) }}" class="text-primary hover:text-additional p-1 cursor-pointer transition" title="Lihat Detail Mata Kuliah">
                                    <x-icon.eye class="w-5 h-5" />
                                </a>
                            @endif

                            <!-- Bukti Pembelajaran Button (Navy / Document Icon) -->
                            <button 
                                type="button" 
                                class="open-bukti-pembelajaran-btn text-primary hover:text-additional p-1 cursor-pointer transition relative disabled:cursor-not-allowed disabled:opacity-40"
                                title="Kelola Bukti Pembelajaran (RPS, Kontrak, Presensi, Soal Ujian)"
                                data-action="{{ route('kelas.mata-kuliah.bukti.store', [$kelas['id'], $enrollmentId ?: ($mkAkId ?: $mk['id'])]) }}"
                                data-destroy-base-url="{{ url('/kelas/'.$kelas['id'].'/mata-kuliah/'.($enrollmentId ?: ($mkAkId ?: $mk['id'])).'/bukti') }}"
                                data-mk-nama="{{ $namaMk }}"
                                data-mk-kode="{{ $kodeMk }}"
                                data-items='@json($mk['evidence_list'] ?? [])'
                                @if(empty($enrollmentId)) disabled @endif
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                @if(!empty($mk['evidence_count']) && $mk['evidence_count'] > 0)
                                    <span class="absolute -top-1 -right-1 px-1.5 py-0.2 rounded-full text-[9px] font-extrabold bg-emerald-600 text-white shadow-2xs">{{ $mk['evidence_count'] }}</span>
                                @endif
                            </button>

                            @haspermission('kelas.update')
                            @if(!empty($enrollmentId))
                                <!-- Edit Dosen Button (Yellow) -->
                                <button 
                                    type="button" 
                                    class="open-edit-dosen-btn text-[#F59E0B] hover:text-[#D97706] p-1 cursor-pointer transition"
                                    title="Ubah Dosen Pengampu"
                                    data-action="{{ route('kelas.mata-kuliah.update', [$kelas['id'], $enrollmentId]) }}"
                                    data-mk-id="{{ $mk['id'] }}"
                                    data-mk-nama="{{ $namaMk }}"
                                    data-kode="{{ $kodeMk }}"
                                    data-ta-id="{{ $mkTaId }}"
                                    data-dosen-id="{{ $dosenId }}"
                                >
                                    <x-icon.edit class="w-5 h-5" />
                                </button>
                            @endif

                            @if(!empty($mkAkId))
                                <!-- Delete Mata Kuliah dari Kelas (Red) -->
                                <button 
                                    type="button" 
                                    class="text-[#DC2626] hover:text-[#B91C1C] p-1 cursor-pointer transition"
                                    title="Hapus Mata Kuliah dari Kelas"
                                    data-confirm-delete
                                    data-action="{{ route('kelas.mata-kuliah.destroy', [$kelas['id'], $mkAkId]) }}"
                                    data-title="Hapus Mata Kuliah dari Kelas?"
                                    data-message="Apakah Anda yakin ingin menghapus mata kuliah {{ $namaMk }} ({{ $kodeMk }}) dari kelas {{ $namaKelas }}? Tindakan ini bersifat permanen."
                                >
                                    <x-icon.delete class="w-5 h-5" />
                                </button>
                            @endif
                            @endhaspermission
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="6" message="Belum ada data mata kuliah yang ditugaskan ke kelas ini" submessage="Klik tombol Tambah Mata Kuliah untuk menugaskan mata kuliah dan dosen pengampu." />
            @endforelse
        </x-table>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: Tambah Mata Kuliah & Dosen ke Kelas               -->
<!-- ======================================================== -->
<div 
    id="createMataKuliahKelasModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createMataKuliahKelasModalTitle"
>
    <div class="mk-kelas-modal-card relative bg-white rounded-[28px] p-8 max-w-[500px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createMataKuliahKelasModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambah Mata Kuliah & Dosen Pengampu
        </h3>

        <form action="{{ route('kelas.mata-kuliah.store', $kelas['id']) }}" method="POST" class="space-y-4">
            @csrf

            <!-- Field 1: Pilih Mata Kuliah -->
            <div>
                <label for="createMataKuliahSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Mata Kuliah
                </label>
                <div class="relative">
                    <select 
                        name="mata_kuliah_id" 
                        id="createMataKuliahSelect" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Mata Kuliah</option>
                        @foreach($masterMkList as $m)
                            @php
                                $mId = $m['id'] ?? '';
                                $mKode = $m['kode_mata_kuliah'] ?? ($m['kode'] ?? '');
                                $mNama = $m['nama_mata_kuliah'] ?? ($m['nama'] ?? 'Mata Kuliah #'.$mId);
                                $mSks = $m['sks'] ?? null;
                            @endphp
                            <option value="{{ $mId }}">
                                [{{ $mKode }}] {{ $mNama }} @if($mSks) ({{ $mSks }} SKS) @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field 2: Pilih Dosen Pengampu -->
            <div>
                <label for="createDosenSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Dosen Pengampu
                </label>
                <div class="relative">
                    <select 
                        name="dosen_id" 
                        id="createDosenSelect" 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="">Pilih Dosen Pengampu (Opsional)</option>
                        @foreach($dosenList as $d)
                            <option value="{{ $d['id'] }}">
                                {{ $d['nama'] }} @if(!empty($d['email'])) ({{ $d['email'] }}) @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">Dosen yang dipilih akan menjadi penanggung jawab perkuliahan kelas ini.</p>
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-mk-kelas-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: Edit Dosen Pengampu                               -->
<!-- ======================================================== -->
<div 
    id="editDosenKelasModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editDosenKelasModalTitle"
>
    <div class="mk-kelas-modal-card relative bg-white rounded-[28px] p-8 max-w-[500px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editDosenKelasModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Ubah Dosen Pengampu
        </h3>

        <form id="editDosenForm" action="#" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" name="mata_kuliah_id" id="editMataKuliahIdInput">
            <input type="hidden" name="tahun_akademik_id" id="editTahunAkademikIdInput">

            <!-- Read-only Info Mata Kuliah -->
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                    Mata Kuliah
                </label>
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200">
                    <p class="font-bold text-sm text-gray-900" id="editMataKuliahNamaText">-</p>
                    <p class="font-mono text-xs text-gray-600 mt-0.5" id="editMataKuliahKodeText">-</p>
                </div>
            </div>

            <!-- Field: Pilih Dosen Pengampu -->
            <div>
                <label for="editDosenSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Dosen Pengampu
                </label>
                <div class="relative">
                    <select 
                        name="dosen_id" 
                        id="editDosenSelect" 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="">Belum Ditentukan (Kosongkan)</option>
                        @foreach($dosenList as $d)
                            <option value="{{ $d['id'] }}">
                                {{ $d['nama'] }} @if(!empty($d['email'])) ({{ $d['email'] }}) @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Modal Action Buttons (Batal & Perbarui) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-mk-kelas-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: Kelola Bukti Kegiatan Pembelajaran (Detail Kelas) -->
<!-- ======================================================== -->
<div 
    id="buktiPembelajaranModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="buktiPembelajaranModalTitle"
>
    <div class="mk-kelas-modal-card relative bg-white rounded-[28px] p-6 sm:p-8 max-w-[620px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto space-y-5">
        <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-4">
            <div>
                <h3 id="buktiPembelajaranModalTitle" class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">
                    Bukti Kegiatan Pembelajaran
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    <span id="buktiModalMkNama" class="font-bold text-primary"></span>
                    <span id="buktiModalMkKode" class="font-mono text-gray-600 ml-1"></span>
                </p>
            </div>
            <button type="button" class="close-bukti-modal text-gray-400 hover:text-gray-700 transition p-1 cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Daftar Dokumen Terunggah / Ditautkan -->
        <div class="space-y-2.5">
            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center justify-between">
                <span>Daftar Bukti Ditautkan</span>
                <span id="buktiCountBadge" class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-primary/10 text-primary">0 Dokumen</span>
            </h4>
            <div id="buktiItemsContainer" class="space-y-2 max-h-56 overflow-y-auto pr-1">
                <!-- Injected via JavaScript -->
            </div>
            <div id="buktiEmptyState" class="p-4 rounded-xl border border-dashed border-gray-300 text-center text-xs text-gray-500 bg-gray-50/70 hidden">
                Belum ada bukti kegiatan pembelajaran yang ditautkan untuk mata kuliah ini di kelas ini.
            </div>
        </div>

        <!-- Form Tambah Bukti Baru -->
        <div class="border-t border-gray-100 pt-4 space-y-3">
            <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">
                + Tambah Tautan Bukti Baru
            </h4>

            <form id="storeBuktiForm" action="" method="POST" class="space-y-3">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="buktiJenisDokumenSelect" class="block text-xs font-bold text-gray-900 mb-1">
                            Jenis Dokumen <span class="text-red-500">*</span>
                        </label>
                        <select 
                            name="jenis_dokumen" 
                            id="buktiJenisDokumenSelect" 
                            required 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white-form border border-gray-200 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none cursor-pointer"
                        >
                            <option value="RPS">RPS (Rencana Pembelajaran Semester)</option>
                            <option value="Kontrak Kuliah">Kontrak Kuliah</option>
                            <option value="Presensi">Presensi / Daftar Hadir</option>
                            <option value="Soal Ujian">Soal Ujian (UTS / UAS / Kuis)</option>
                            <option value="Portofolio Tugas">Portofolio Tugas & Rubrik</option>
                            <option value="Lainnya">Lainnya / Dokumentasi</option>
                        </select>
                    </div>

                    <div>
                        <label for="buktiJudulInput" class="block text-xs font-bold text-gray-900 mb-1">
                            Judul / Keterangan Dokumen <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="judul" 
                            id="buktiJudulInput" 
                            placeholder="Contoh: Kontrak Kuliah Gasal 2026" 
                            required 
                            maxlength="255"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white-form border border-gray-200 text-xs font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none"
                        >
                    </div>
                </div>

                <div>
                    <label for="buktiUrlInput" class="block text-xs font-bold text-gray-900 mb-1">
                        Tautan / URL Dokumen <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="url" 
                        name="url" 
                        id="buktiUrlInput" 
                        placeholder="https://drive.google.com/... atau tautan penyimpanan cloud" 
                        required 
                        maxlength="2048"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white-form border border-gray-200 text-xs font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none font-mono"
                    >
                    <p class="text-[11px] text-gray-500 mt-1">
                        Pastikan link dapat diakses oleh auditor LAM INFOKOM (set permission publik atau shared drive).
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button 
                        type="button" 
                        class="close-bukti-modal px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition cursor-pointer"
                    >
                        Tutup
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-primary hover:bg-additional text-white text-xs font-bold shadow-sm transition cursor-pointer inline-flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Simpan Bukti Pembelajaran</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal Helper Functions
        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.mk-kelas-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
            document.body.style.overflow = 'hidden';
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.mk-kelas-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
            document.body.style.overflow = '';
        }

        const createModal = document.getElementById('createMataKuliahKelasModal');
        const editModal = document.getElementById('editDosenKelasModal');
        const buktiModal = document.getElementById('buktiPembelajaranModal');

        // Bukti Pembelajaran Modal Handlers
        const storeBuktiForm = document.getElementById('storeBuktiForm');
        const buktiModalMkNama = document.getElementById('buktiModalMkNama');
        const buktiModalMkKode = document.getElementById('buktiModalMkKode');
        const buktiItemsContainer = document.getElementById('buktiItemsContainer');
        const buktiEmptyState = document.getElementById('buktiEmptyState');
        const buktiCountBadge = document.getElementById('buktiCountBadge');

        function getBadgeClassByJenis(jenis) {
            switch ((jenis || '').toLowerCase()) {
                case 'rps':
                    return 'bg-blue-50 text-blue-700 border-blue-200';
                case 'kontrak kuliah':
                    return 'bg-purple-50 text-purple-700 border-purple-200';
                case 'presensi':
                    return 'bg-emerald-50 text-emerald-700 border-emerald-200';
                case 'soal ujian':
                    return 'bg-amber-50 text-amber-700 border-amber-200';
                case 'portofolio tugas':
                    return 'bg-cyan-50 text-cyan-700 border-cyan-200';
                default:
                    return 'bg-gray-100 text-gray-700 border-gray-200';
            }
        }

        document.querySelectorAll('.open-bukti-pembelajaran-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const action = this.dataset.action;
                const destroyBaseUrl = this.dataset.destroyBaseUrl;
                const mkNama = this.dataset.mkNama;
                const mkKode = this.dataset.mkKode;
                const items = JSON.parse(this.dataset.items || '[]');

                if (storeBuktiForm && action) {
                    storeBuktiForm.action = action;
                }
                const submitBuktiButton = storeBuktiForm?.querySelector('button[type="submit"]');
                if (submitBuktiButton) {
                    submitBuktiButton.disabled = !action;
                    submitBuktiButton.classList.toggle('opacity-50', !action);
                }
                if (buktiModalMkNama) buktiModalMkNama.textContent = mkNama || 'Mata Kuliah';
                if (buktiModalMkKode) buktiModalMkKode.textContent = mkKode ? `(${mkKode})` : '';

                if (buktiCountBadge) {
                    buktiCountBadge.textContent = `${items.length} Dokumen`;
                }

                if (buktiItemsContainer) {
                    buktiItemsContainer.innerHTML = '';
                    if (items.length === 0) {
                        buktiEmptyState?.classList.remove('hidden');
                    } else {
                        buktiEmptyState?.classList.add('hidden');
                        items.forEach((item, index) => {
                            const badgeClass = getBadgeClassByJenis(item.jenis_dokumen);
                            const itemEl = document.createElement('div');
                            itemEl.className = 'flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50/80 hover:bg-gray-100/60 transition gap-3';
                            const content = document.createElement('div');
                            content.className = 'flex min-w-0 items-center gap-2.5';
                            const badge = document.createElement('span');
                            badge.className = `inline-flex shrink-0 items-center rounded-full border px-2 py-0.5 text-[10px] font-extrabold ${badgeClass}`;
                            badge.textContent = item.jenis_dokumen || 'Dokumen';
                            const detail = document.createElement('div');
                            detail.className = 'min-w-0';
                            const title = document.createElement('p');
                            title.className = 'truncate text-xs font-bold text-gray-900';
                            title.textContent = item.judul || 'Bukti Pembelajaran';
                            const link = document.createElement('a');
                            link.className = 'block truncate font-mono text-[11px] text-primary underline hover:text-additional';
                            link.target = '_blank';
                            link.rel = 'noopener noreferrer';
                            link.textContent = item.url || 'Tautan tidak tersedia';
                            try {
                                const parsedUrl = new URL(item.url);
                                if (['http:', 'https:'].includes(parsedUrl.protocol)) {
                                    link.href = parsedUrl.href;
                                } else {
                                    link.removeAttribute('target');
                                    link.removeAttribute('rel');
                                }
                            } catch {
                                link.removeAttribute('target');
                                link.removeAttribute('rel');
                            }
                            detail.append(title, link);
                            content.append(badge, detail);
                            itemEl.append(content);

                            if (destroyBaseUrl) {
                                const form = document.createElement('form');
                                form.action = `${destroyBaseUrl}/${encodeURIComponent(item.id ?? index)}`;
                                form.method = 'POST';
                                form.addEventListener('submit', event => {
                                    if (!confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
                                        event.preventDefault();
                                    }
                                });
                                const token = document.createElement('input');
                                token.type = 'hidden';
                                token.name = '_token';
                                token.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
                                const method = document.createElement('input');
                                method.type = 'hidden';
                                method.name = '_method';
                                method.value = 'DELETE';
                                const deleteButton = document.createElement('button');
                                deleteButton.type = 'submit';
                                deleteButton.className = 'rounded-lg p-1 text-rose-600 transition hover:bg-rose-50 hover:text-rose-800';
                                deleteButton.title = 'Hapus Bukti';
                                deleteButton.textContent = 'Hapus';
                                form.append(token, method, deleteButton);
                                itemEl.append(form);
                            }

                            buktiItemsContainer.appendChild(itemEl);
                        });
                    }
                }

                showModal(buktiModal);
            });
        });

        document.querySelectorAll('.close-bukti-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                hideModal(buktiModal);
            });
        });

        // Open Create Modal
        document.querySelectorAll('.open-create-mk-kelas-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                showModal(createModal);
            });
        });

        // Open Edit Dosen Modal
        const editDosenForm = document.getElementById('editDosenForm');
        const editMataKuliahIdInput = document.getElementById('editMataKuliahIdInput');
        const editTahunAkademikIdInput = document.getElementById('editTahunAkademikIdInput');
        const editDosenSelect = document.getElementById('editDosenSelect');
        const editMataKuliahNamaText = document.getElementById('editMataKuliahNamaText');
        const editMataKuliahKodeText = document.getElementById('editMataKuliahKodeText');

        document.querySelectorAll('.open-edit-dosen-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const action = this.dataset.action;
                const mkId = this.dataset.mkId;
                const mkNama = this.dataset.mkNama;
                const kode = this.dataset.kode;
                const taId = this.dataset.taId;
                const dosenId = this.dataset.dosenId;

                if (editDosenForm && action) {
                    editDosenForm.action = action;
                }
                if (editMataKuliahIdInput && mkId) {
                    editMataKuliahIdInput.value = mkId;
                }
                if (editTahunAkademikIdInput && taId) {
                    editTahunAkademikIdInput.value = taId;
                }
                if (editDosenSelect) {
                    editDosenSelect.value = dosenId || '';
                }
                if (editMataKuliahNamaText && mkNama) {
                    editMataKuliahNamaText.textContent = mkNama;
                }
                if (editMataKuliahKodeText && kode) {
                    editMataKuliahKodeText.textContent = kode;
                }

                showModal(editModal);
            });
        });

        // Close Modals
        document.querySelectorAll('.close-mk-kelas-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                hideModal(createModal);
                hideModal(editModal);
            });
        });

        // Click Backdrop to Close
        [createModal, editModal, buktiModal].forEach(modal => {
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        hideModal(modal);
                    }
                });
            }
        });

        // Escape Key to Close
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideModal(createModal);
                hideModal(editModal);
                hideModal(buktiModal);
            }
        });

        // Search Mahasiswa Realtime Filtering
        const searchMahasiswa = document.getElementById('searchMahasiswaInput');
        const mhsRows = document.querySelectorAll('.mahasiswa-row');
        if (searchMahasiswa) {
            searchMahasiswa.addEventListener('input', function () {
                const kw = this.value.toLowerCase().trim();
                mhsRows.forEach(row => {
                    const nama = row.querySelector('.mahasiswa-nama')?.textContent.toLowerCase() || '';
                    const nim = row.querySelector('.mahasiswa-nim')?.textContent.toLowerCase() || '';
                    row.style.display = (!kw || nama.includes(kw) || nim.includes(kw)) ? '' : 'none';
                });
            });
        }

        // Search Mata Kuliah Realtime Filtering
        const searchMataKuliah = document.getElementById('searchMataKuliahInput');
        const mkRows = document.querySelectorAll('.matakuliah-row');
        if (searchMataKuliah) {
            searchMataKuliah.addEventListener('input', function () {
                const kw = this.value.toLowerCase().trim();
                mkRows.forEach(row => {
                    const nama = row.querySelector('.mk-nama')?.textContent.toLowerCase() || '';
                    const kode = row.querySelector('.mk-kode')?.textContent.toLowerCase() || '';
                    row.style.display = (!kw || nama.includes(kw) || kode.includes(kw)) ? '' : 'none';
                });
            });
        }
    });
</script>
@endsection
