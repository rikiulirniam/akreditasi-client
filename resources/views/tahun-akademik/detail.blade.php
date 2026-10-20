@extends('layouts.app')

@section('title', 'Detail Kelas')

@section('content')
<style>
    /* Sembunyikan scrollbar horizontal di seluruh elemen tabel */
    .table-container div,
    .table-container table {
        overflow-x: hidden !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    .table-container div::-webkit-scrollbar {
        display: none !important;
    }
</style>

<div class="space-y-8 w-full max-w-full overflow-hidden table-container">
    <!-- Tabel 1: Daftar Mahasiswa -->
    <div class="space-y-4 w-full">
        <h2 class="text-xl font-bold text-gray-900 tracking-tight">
            Daftar Mahasiswa Kelas {{ $kelas['nama'] ?? $kelas['nama_kelas'] ?? '' }} (Tahun Akademik {{ $tahunAkademik['tahun'] ?? '' }})
        </h2>

        <div class="w-full rounded-2xl border border-gray-200 overflow-hidden">
            <x-table class="w-full table-fixed">
                <x-slot:header>
                    <x-table.th class="w-[6%] text-center">No</x-table.th>
                    <x-table.th class="w-[18%]">NIM</x-table.th>
                    <x-table.th class="w-[34%]">Nama Mahasiswa</x-table.th>
                    <x-table.th class="w-[14%] text-center">Status</x-table.th>
                    <x-table.th class="w-[10%] text-center">IPK</x-table.th>
                    <x-table.th class="w-[10%] text-center">Prestasi</x-table.th>
                    <x-table.th class="w-[8%] text-center">Action</x-table.th>
                </x-slot:header>

                @forelse($mahasiswaList as $index => $mhs)
                    @php 
                        $status = $mhs['status'] ?? 'Aktif';
                        $namaMahasiswa = $mhs['nama'] ?? $mhs['nama_mahasiswa'] ?? $mhs['name'] ?? '-';
                        
                        // Menampilkan data asli dari API, jika tidak ada/null maka tampilkan '-'
                        $ipkVal = $mhs['ipk'] ?? $mhs['gpa'] ?? null;
                        $ipk = ($ipkVal !== null && $ipkVal !== '') ? number_format((float)$ipkVal, 2) : '-';
                        
                        $prestasiVal = $mhs['jumlah_prestasi'] ?? $mhs['prestasi'] ?? null;
                        if ($prestasiVal !== null && $prestasiVal !== '') {
                            $prestasi = is_numeric($prestasiVal) ? $prestasiVal . ' Prestasi' : $prestasiVal;
                        } else {
                            $prestasi = '-';
                        }
                    @endphp
                    <x-table.row>
                        <x-table.cell class="text-gray-500 font-medium text-center">{{ $index + 1 }}.</x-table.cell>
                        <x-table.cell class="font-mono text-gray-700 text-sm whitespace-nowrap">{{ $mhs['nim'] ?? '-' }}</x-table.cell>
                        <x-table.cell class="font-medium text-gray-900 whitespace-nowrap">{{ $namaMahasiswa }}</x-table.cell>
                        <x-table.cell class="text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ strtolower($status) === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $status }}
                            </span>
                        </x-table.cell>
                        <x-table.cell class="font-bold text-gray-900 text-center">{{ $ipk }}</x-table.cell>
                        <x-table.cell class="text-gray-700 text-sm text-center whitespace-nowrap">{{ $prestasi }}</x-table.cell>
                        <x-table.cell class="text-center">
                            <div class="flex items-center justify-center">
                                <button type="button" class="text-primary hover:text-additional p-1 cursor-pointer" title="Lihat Detail">
                                    <x-icon.eye class="w-5 h-5" />
                                </button>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.empty colspan="7" message="Belum ada data mahasiswa" />
                @endforelse
            </x-table>
        </div>
    </div>

    <!-- Tabel 2: Daftar Mata Kuliah -->
    <div class="space-y-4 pt-4 w-full">
        <h2 class="text-xl font-bold text-gray-900 tracking-tight">
            Daftar Mata Kuliah Kelas {{ $kelas['nama'] ?? $kelas['nama_kelas'] ?? '' }} (Tahun Akademik {{ $tahunAkademik['tahun'] ?? '' }})
        </h2>

        <div class="w-full rounded-2xl border border-gray-200 overflow-hidden">
            <x-table class="w-full table-fixed">
                <x-slot:header>
                    <x-table.th class="w-[6%] text-center">No</x-table.th>
                    <x-table.th class="w-[18%]">Kode MK</x-table.th>
                    <x-table.th class="w-[34%]">Nama Mata Kuliah</x-table.th>
                    <x-table.th class="w-[10%] text-center">SKS</x-table.th>
                    <x-table.th class="w-[24%]">Dosen Pengampu</x-table.th>
                    <x-table.th class="w-[8%] text-center">Action</x-table.th>
                </x-slot:header>

                @forelse($matakuliahList as $index => $mk)
                    <x-table.row>
                        <x-table.cell class="text-gray-500 font-medium text-center">{{ $index + 1 }}.</x-table.cell>
                        <x-table.cell class="font-mono text-gray-700 text-sm whitespace-nowrap">{{ $mk['kode_mk'] ?? $mk['kode'] ?? '-' }}</x-table.cell>
                        <x-table.cell class="font-medium text-gray-900 whitespace-nowrap">{{ $mk['nama_mk'] ?? $mk['nama'] ?? '-' }}</x-table.cell>
                        <x-table.cell class="text-gray-700 font-semibold text-sm text-center">{{ $mk['sks'] ?? '-' }} SKS</x-table.cell>
                        <x-table.cell class="text-gray-700 text-sm whitespace-nowrap">{{ $mk['dosen'] ?? $mk['dosen_pengampu'] ?? '-' }}</x-table.cell>
                        <x-table.cell class="text-center">
                            <div class="flex items-center justify-center">
                                <button type="button" class="text-primary hover:text-additional p-1 cursor-pointer" title="Lihat Detail">
                                    <x-icon.eye class="w-5 h-5" />
                                </button>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.empty colspan="6" message="Belum ada data mata kuliah" />
                @endforelse
            </x-table>
        </div>
    </div>
</div>
@endsection