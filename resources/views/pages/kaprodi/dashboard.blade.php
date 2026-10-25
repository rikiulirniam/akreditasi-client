@extends('layouts.app')

@section('title', 'Dashboard Kaprodi')

@section('content')
<div class="space-y-6">

    <!-- Toast Notification Component -->
    <x-toast />

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-extrabold text-primary tracking-tight">Dashboard</h1>
        <p class="text-xs sm:text-sm text-[#5A6E85] mt-1">Monitoring progress pengisian borang prodi, rekap skor sementara, dan status audit SPMI.</p>
    </div>

    <!-- Card 1: Monitoring Progres -->
    <div class="bg-white rounded-2xl border border-[#EAEAEA] p-6 shadow-xs">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-base font-extrabold text-primary tracking-tight">Monitoring progres pengisian borang prodi</h2>
                <p class="text-xs text-gray-500 mt-1">Indikator yang sudah diisi tim, per kriteria</p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-extrabold text-additional">61%</div>
                <div class="text-[11px] text-gray-400 mt-0.5">122 dari 200 indikator</div>
            </div>
        </div>

        <x-table>
            <x-slot:header>
                <x-table.th class="w-1/3">Kriteria</x-table.th>
                <x-table.th class="w-1/2">Progres</x-table.th>
                <x-table.th class="text-center">Terisi</x-table.th>
                <x-table.th class="text-right">Status</x-table.th>
            </x-slot:header>

            @php
                $kriteriaProgress = [
                    [
                        'kriteria' => 'Tata Pamong dan Kerja Sama',
                        'persen' => 80,
                        'bar_color' => 'bg-emerald-600',
                        'terisi' => '16/20',
                        'status' => 'Lengkap',
                        'badge' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                    ],
                    [
                        'kriteria' => 'Mahasiswa',
                        'persen' => 62,
                        'bar_color' => 'bg-additional',
                        'terisi' => '31/50',
                        'status' => 'Berjalan',
                        'badge' => 'bg-sky-50 text-additional border-sky-100',
                    ],
                    [
                        'kriteria' => 'Sumber Daya Manusia',
                        'persen' => 70,
                        'bar_color' => 'bg-additional',
                        'terisi' => '28/40',
                        'status' => 'Berjalan',
                        'badge' => 'bg-sky-50 text-additional border-sky-100',
                    ],
                    [
                        'kriteria' => 'Keuangan, Sarana dan Prasarana',
                        'persen' => 45,
                        'bar_color' => 'bg-amber-400',
                        'terisi' => '18/40',
                        'status' => 'Tertinggal',
                        'badge' => 'bg-amber-50 text-amber-600 border-amber-100',
                    ],
                    [
                        'kriteria' => 'Pendidikan',
                        'persen' => 58,
                        'bar_color' => 'bg-additional',
                        'terisi' => '29/50',
                        'status' => 'Berjalan',
                        'badge' => 'bg-sky-50 text-additional border-sky-100',
                    ],
                ];
            @endphp

            @foreach($kriteriaProgress as $row)
                <x-table.row>
                    <x-table.cell class="font-semibold text-gray-800 text-xs">
                        {{ $row['kriteria'] }}
                    </x-table.cell>
                    <x-table.cell class="pr-4">
                        <div class="flex items-center gap-3">
                            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $row['bar_color'] }} rounded-full" style="width: {{ $row['persen'] }}%"></div>
                            </div>
                            <span class="text-xs font-bold text-gray-700 min-w-8">{{ $row['persen'] }}%</span>
                        </div>
                    </x-table.cell>
                    <x-table.cell class="text-xs text-gray-500 text-center font-medium">
                        {{ $row['terisi'] }}
                    </x-table.cell>
                    <x-table.cell class="text-right">
                        <span class="inline-block px-2.5 py-1 {{ $row['badge'] }} text-[10px] font-bold rounded-md border">
                            {{ $row['status'] }}
                        </span>
                    </x-table.cell>
                </x-table.row>
            @endforeach
        </x-table>
    </div>

    <!-- Grid Bawah (2 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Card 2: Rekap Skor Sementara -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-6 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-base font-extrabold text-primary tracking-tight">Rekap skor sementara</h2>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Hasil self-assessment, skala 0-4. Garis tegak menandai target 3,0</p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-extrabold text-additional">3,08</div>
                        <div class="text-[11px] text-gray-400 mt-0.5">rata-rata dari 4,00</div>
                    </div>
                </div>

                <div class="space-y-5">
                    <!-- Item 1 -->
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-gray-800 mb-2">
                            <span>Tata Pamong dan Kerja Sama</span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900">3,40</span>
                                <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-semibold rounded">Tercapai</span>
                            </div>
                        </div>
                        <div class="relative w-full h-2 bg-gray-100 rounded-full">
                            <div class="absolute top-0 left-0 h-full bg-emerald-600 rounded-full" style="width: 85%"></div>
                            <div class="absolute top-[-3px] bottom-[-3px] w-0.5 bg-gray-900 z-10" style="left: 75%" title="Target 3.0"></div>
                        </div>
                    </div>
                    <!-- Item 2 -->
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-gray-800 mb-2">
                            <span>Mahasiswa</span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900">2,90</span>
                                <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-semibold rounded">Di bawah target</span>
                            </div>
                        </div>
                        <div class="relative w-full h-2 bg-gray-100 rounded-full">
                            <div class="absolute top-0 left-0 h-full bg-amber-400 rounded-full" style="width: 72.5%"></div>
                            <div class="absolute top-[-3px] bottom-[-3px] w-0.5 bg-gray-900 z-10" style="left: 75%" title="Target 3.0"></div>
                        </div>
                    </div>
                    <!-- Item 3 -->
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-gray-800 mb-2">
                            <span>Sumber Daya Manusia</span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900">3,20</span>
                                <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-semibold rounded">Tercapai</span>
                            </div>
                        </div>
                        <div class="relative w-full h-2 bg-gray-100 rounded-full">
                            <div class="absolute top-0 left-0 h-full bg-emerald-600 rounded-full" style="width: 80%"></div>
                            <div class="absolute top-[-3px] bottom-[-3px] w-0.5 bg-gray-900 z-10" style="left: 75%" title="Target 3.0"></div>
                        </div>
                    </div>
                    <!-- Item 4 -->
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-gray-800 mb-2">
                            <span>Pendidikan</span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900">2,80</span>
                                <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-semibold rounded">Di bawah target</span>
                            </div>
                        </div>
                        <div class="relative w-full h-2 bg-gray-100 rounded-full">
                            <div class="absolute top-0 left-0 h-full bg-amber-400 rounded-full" style="width: 70%"></div>
                            <div class="absolute top-[-3px] bottom-[-3px] w-0.5 bg-gray-900 z-10" style="left: 75%" title="Target 3.0"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Status Audit SPMI -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-6 shadow-xs">
            <h2 class="text-base font-extrabold text-primary tracking-tight">Status audit SPMI</h2>
            <p class="text-xs text-gray-500 mt-1">Siklus PPEPP, AMI, dan RTM</p>

            <div class="grid grid-cols-3 gap-3 mt-5 mb-6">
                <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-100 text-center">
                    <div class="text-2xl font-extrabold text-primary">5</div>
                    <div class="text-[11px] text-gray-500 mt-1 font-medium">Temuan terbuka</div>
                </div>
                <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-100 text-center">
                    <div class="text-2xl font-extrabold text-primary">2</div>
                    <div class="text-[11px] text-gray-500 mt-1 font-medium">Menunggu tanggapan</div>
                </div>
                <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-100 text-center">
                    <div class="text-2xl font-extrabold text-primary">3</div>
                    <div class="text-[11px] text-gray-500 mt-1 font-medium">Selesai</div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                    <div class="flex-1">
                        <div class="text-xs font-bold text-additional">STD-04 <span class="font-medium text-gray-700">Dokumen evaluasi kurikulum belum diperbarui</span></div>
                        <div class="text-[11px] text-gray-400 mt-1">Tenggat 20 Okt</div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2 py-1 bg-amber-50 text-amber-600 text-[10px] font-bold rounded border border-amber-100 whitespace-nowrap">Menunggu tanggapan</span>
                        <button type="button" class="px-4 py-1.5 bg-additional hover:bg-primary text-white text-xs font-bold rounded-lg transition-colors cursor-pointer shadow-xs">Tanggapi</button>
                    </div>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                    <div class="flex-1">
                        <div class="text-xs font-bold text-additional">STD-07 <span class="font-medium text-gray-700">Rasio dosen-mahasiswa melebihi batas</span></div>
                        <div class="text-[11px] text-gray-400 mt-1">Tenggat 27 Okt</div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2 py-1 bg-amber-50 text-amber-600 text-[10px] font-bold rounded border border-amber-100 whitespace-nowrap">Menunggu tanggapan</span>
                        <button type="button" class="px-4 py-1.5 bg-additional hover:bg-primary text-white text-xs font-bold rounded-lg transition-colors cursor-pointer shadow-xs">Tanggapi</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection