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
            <div class="relative w-full max-w-xs">
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
        </div>

        <x-table>
            <x-slot:header>
                <x-table.th class="w-16 text-center">No</x-table.th>
                <x-table.th class="w-44">Kode MK</x-table.th>
                <x-table.th>Nama Mata Kuliah</x-table.th>
                <x-table.th class="w-24 text-center">SKS</x-table.th>
                <x-table.th class="w-64">Dosen Pengampu</x-table.th>
                <x-table.th class="w-20 text-center">Action</x-table.th>
            </x-slot:header>

            @forelse($mataKuliahList as $index => $mk)
                @php
                    $kodeMk = $mk['kode_mata_kuliah'] ?? ($mk['kode_mk'] ?? ($mk['kode'] ?? '-'));
                    $namaMk = $mk['nama_mata_kuliah'] ?? ($mk['nama_mk'] ?? ($mk['nama'] ?? '-'));
                    $sks = $mk['sks'] ?? '-';
                    $dosen = $mk['dosen_pengampu'] ?? ($mk['dosen'] ?? '-');
                @endphp
                <x-table.row class="matakuliah-row">
                    <x-table.cell class="text-gray-500 font-medium text-center w-16">{{ $index + 1 }}.</x-table.cell>
                    <x-table.cell class="font-mono text-gray-700 text-sm whitespace-nowrap mk-kode w-44">{{ $kodeMk }}</x-table.cell>
                    <x-table.cell class="font-medium text-gray-900 whitespace-nowrap mk-nama">{{ $namaMk }}</x-table.cell>
                    <x-table.cell class="text-gray-700 font-semibold text-sm text-center w-24">{{ $sks }} SKS</x-table.cell>
                    <x-table.cell class="text-gray-700 text-sm whitespace-nowrap w-64">{{ $dosen }}</x-table.cell>
                    <x-table.cell class="text-center w-20">
                        <div class="flex items-center justify-center">
                            @if(Route::has('mata-kuliah.show') && !empty($mk['id']))
                                <a href="{{ route('mata-kuliah.show', $mk['id']) }}" class="text-primary hover:text-additional p-1 cursor-pointer" title="Lihat Detail">
                                    <x-icon.eye class="w-5 h-5" />
                                </a>
                            @else
                                <span class="text-gray-300">-</span>
                           @endif
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="6" message="Belum ada data mata kuliah" />
            @endforelse
        </x-table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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
