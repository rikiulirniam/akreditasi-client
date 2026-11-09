@extends('layouts.app')

@section('title', 'Daftar Kelas - Tahun Akademik')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900 tracking-tight">
            Daftar Kelas (Tahun Akademik {{ $tahunAkademik['tahun'] ?? '2025/2026' }} {{ $tahunAkademik['semester'] ?? '' }})
        </h2>
    </div>

    <!-- Table: Daftar Kelas -->
    <x-table>
        <x-slot:header>
            <x-table.th class="w-16">No</x-table.th>
            <x-table.th>Prodi</x-table.th>
            <x-table.th>Kelas</x-table.th>
            <x-table.th>Jumlah Mahasiswa</x-table.th>
            <x-table.th class="w-20 text-right">Action</x-table.th>
        </x-slot:header>

        @forelse($kelasList as $index => $item)
            @php
                $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                $tahun = $item['tahun_akademik']['tahun'] ?? $tahunAkademik['tahun'] ?? '2025';
                $prodi = $item['prodi']['nama'] ?? 'Teknologi Rekayasa Komputer';
                $namaKelas = $item['kelas'] ?? $item['nama'] ?? $item['nama_kelas'] ?? 'TI-1A';
                $jumlahMahasiswa = $item['jumlah_mahasiswa'] ?? 25;
            @endphp
            <x-table.row>
                <x-table.cell class="text-gray-500 font-medium w-16">
                    {{ $rowNumber }}.
                </x-table.cell>

                <x-table.cell class="text-gray-700">
                    {{ $prodi }}
                </x-table.cell>

                <x-table.cell class="font-bold text-gray-900">
                    {{ $namaKelas }}
                </x-table.cell>

                <x-table.cell class="text-gray-700">
                    {{ $jumlahMahasiswa }} Mahasiswa
                </x-table.cell>

                <x-table.cell class="w-20">
                    <div class="flex items-center justify-end">
                        <!-- Eye / View Icon (Navy) -> Ke Halaman 3 (Detail Mahasiswa & Matkul) -->
                        <a 
                            href="{{ route('tahun-akademik.detail-kelas', ['id' => $tahunAkademik['id'] ?? 1, 'kelasId' => $item['id'] ?? 1]) }}" 
                            class="text-primary hover:text-additional transition p-1 cursor-pointer"
                            title="Lihat Detail Kelas"
                        >
                            <x-icon.eye class="w-5 h-5" />
                        </a>
                    </div>
                </x-table.cell>
            </x-table.row>
        @empty
            <x-table.empty colspan="6" message="Belum ada data kelas" submessage="Tidak ada kelas terdaftar pada tahun akademik ini." />
        @endforelse
    </x-table>

    <!-- Pagination -->
    <x-pagination :paginator="$pagination" />
</div>
@endsection