@extends('layouts.app')

@section('title', 'Data Lulusan')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Data Lulusan</h2>
            <p class="text-sm font-medium text-gray-500 mt-1">
                Informasi data kelulusan, masa studi, IPK riil, dan capaian kelulusan tepat waktu mahasiswa.
            </p>
        </div>
    </div>

    <!-- Summary Cards (Kartu Ringkasan Lulusan - Endpoint Lian API-D2) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Lulusan -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Lulusan</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    {{ number_format($ringkasan['total_lulusan'] ?? 0) }}
                </div>
                <p class="text-xs font-medium text-gray-500">Mahasiswa lulus</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-primary flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-5.25 6.557q0-.36.03-.715" />
                </svg>
            </div>
        </div>

        <!-- Card 2: Lulus Tepat Waktu -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Lulus Tepat Waktu</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 tracking-tight">
                    {{ number_format($ringkasan['persen_lulus_tepat_waktu'] ?? 0, 1) }}%
                </div>
                <p class="text-xs font-medium text-gray-500">
                    {{ number_format($ringkasan['jumlah_lulus_tepat_waktu'] ?? 0) }} dari {{ number_format($ringkasan['total_lulusan'] ?? 0) }} lulusan
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
        </div>

        <!-- Card 3: Rata-Rata IPK -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Rata-Rata IPK</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    {{ number_format($ringkasan['rata_rata_ipk'] ?? 0, 2) }}
                </div>
                <p class="text-xs font-medium text-gray-500">Skala 4.00</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
            </div>
        </div>

        <!-- Card 4: Rata-Rata Masa Studi -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Rata-Rata Masa Studi</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    {{ number_format($ringkasan['rata_rata_masa_studi_bulan'] ?? 0, 1) }}
                    <span class="text-sm font-semibold text-gray-500">Bln</span>
                </div>
                <p class="text-xs font-medium text-gray-500">
                    ~ {{ round(($ringkasan['rata_rata_masa_studi_bulan'] ?? 0) / 12, 1) }} Tahun
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Main Content: Filters & Table -->
    <div class="space-y-4">
        <!-- Top Action / Filter Bar -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[200px] max-w-xs">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                    <x-input 
                        type="text" 
                        id="searchLulusanInput"
                        placeholder="Cari nama, NIM, jenjang..." 
                        value="{{ $search ?? '' }}"
                        class="pl-10 text-xs"
                    />
                </div>

                <!-- Filter: Program Studi -->
                @if(!empty($prodiOptions))
                <div class="relative w-44 sm:w-52">
                    <select 
                        id="filterProdiSelect"
                        class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition truncate"
                    >
                        <option value="">Semua Program Studi</option>
                        @foreach($prodiOptions as $prodi)
                            <option value="{{ $prodi['id'] }}" {{ (string)($selectedProdi ?? '') === (string)$prodi['id'] ? 'selected' : '' }}>
                                {{ $prodi['nama'] }} {{ !empty($prodi['jenjang']) ? '('.$prodi['jenjang'].')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                        <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
                @endif

                <!-- Filter: Angkatan (Tahun Masuk) -->
                <div class="relative w-36 sm:w-40">
                    <select 
                        id="filterAngkatanSelect"
                        class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                    >
                        <option value="">Semua Angkatan</option>
                        @foreach($angkatanOptions as $akt)
                            <option value="{{ $akt }}" {{ (string)($selectedAngkatan ?? '') === (string)$akt ? 'selected' : '' }}>
                                {{ $akt }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                        <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>

                <!-- Filter: Tahun Kelulusan -->
                <div class="relative w-36 sm:w-40">
                    <select 
                        id="filterTahunLulusSelect"
                        class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                    >
                        <option value="">Semua Thn Lulus</option>
                        @foreach($tahunLulusOptions as $thnL)
                            <option value="{{ $thnL }}" {{ (string)($selectedTahunLulus ?? '') === (string)$thnL ? 'selected' : '' }}>
                                {{ $thnL }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                        <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>

                <!-- Reset Filter Button -->
                @php
                    $hasActiveFilter = !empty($search) || !empty($selectedProdi) || !empty($selectedAngkatan) || !empty($selectedTahunLulus);
                @endphp
                <button 
                    type="button" 
                    id="resetFilterBtn"
                    class="{{ $hasActiveFilter ? 'inline-flex' : 'hidden' }} items-center gap-1.5 px-3 py-2.5 rounded-[8px] bg-white-form hover:bg-gray-200 text-gray-600 hover:text-gray-900 text-xs font-semibold border border-white-bone transition cursor-pointer"
                    title="Reset Semua Filter"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset
                </button>
            </div>
        </div>

        <!-- Table: Data Lulusan -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-14">No</x-table.th>
                <x-table.th>NIM</x-table.th>
                <x-table.th>Nama Mahasiswa</x-table.th>
                <x-table.th class="w-24">Jenjang</x-table.th>
                <x-table.th class="w-28">Tahun Masuk</x-table.th>
                <x-table.th class="whitespace-nowrap">Tanggal Lulus</x-table.th>
                <x-table.th class="w-28">Masa Studi</x-table.th>
                <x-table.th class="w-24">IPK</x-table.th>
                <x-table.th class="w-36 text-right">Kelulusan</x-table.th>
            </x-slot:header>

            @forelse($lulusanList as $index => $item)
                @php
                    $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                    $nama = $item['nama_mahasiswa'] ?? '-';
                    $nim = $item['nim_display'] ?? $item['nim'] ?? '-';
                    $tahunMasuk = $item['tahun_masuk'] ?? '-';
                    $jenjang = $item['jenjang_prodi'] ?? '-';
                    $rawKelulusan = $item['tanggal_kelulusan'] ?? '';
                    $formattedKelulusan = !empty($rawKelulusan) ? \Carbon\Carbon::parse($rawKelulusan)->format('d-m-Y') : '-';
                    $masaStudi = $item['masa_studi_bulan'] ?? null;
                    $ipk = $item['ipk_riil'] ?? null;
                    $lulusTepatWaktu = $item['lulus_tepat_waktu'] ?? null;
                @endphp
                <x-table.row 
                    class="lulusan-row"
                    data-nama="{{ strtolower($nama) }}"
                    data-nim="{{ strtolower($nim) }}"
                    data-jenjang="{{ strtolower($jenjang) }}"
                    data-tahun-masuk="{{ $tahunMasuk }}"
                >
                    <!-- No -->
                    <x-table.cell class="text-gray-500 font-medium w-14">
                        {{ $rowNumber }}
                    </x-table.cell>

                    <!-- NIM -->
                    <x-table.cell class="whitespace-nowrap">
                        <span class="font-mono text-xs sm:text-sm font-semibold text-gray-800">
                            {{ $nim }}
                        </span>
                    </x-table.cell>

                    <!-- Nama Mahasiswa -->
                    <x-table.cell>
                        <span class="font-bold text-sm text-gray-900 tracking-tight">
                            {{ $nama }}
                        </span>
                    </x-table.cell>

                    <!-- Jenjang Prodi -->
                    <x-table.cell class="w-24">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-700">
                            {{ $jenjang }}
                        </span>
                    </x-table.cell>

                    <!-- Tahun Masuk -->
                    <x-table.cell class="w-28">
                        <span class="text-sm font-medium text-gray-700">
                            {{ $tahunMasuk }}
                        </span>
                    </x-table.cell>

                    <!-- Tanggal Lulus -->
                    <x-table.cell class="whitespace-nowrap">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">
                            {{ $formattedKelulusan }}
                        </span>
                    </x-table.cell>

                    <!-- Masa Studi -->
                    <x-table.cell class="w-28 whitespace-nowrap">
                        @if($masaStudi !== null)
                            <span class="text-sm font-medium text-gray-800">
                                {{ $masaStudi }} bln
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </x-table.cell>

                    <!-- IPK -->
                    <x-table.cell class="w-24">
                        @if($ipk !== null)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ (float)$ipk >= 3.5 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-800' }}">
                                {{ number_format((float)$ipk, 2) }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </x-table.cell>

                    <!-- Kelulusan Tepat Waktu (Badge) -->
                    <x-table.cell class="w-36 text-right">
                        @if($lulusTepatWaktu === true)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                Tepat Waktu
                            </span>
                        @elseif($lulusTepatWaktu === false)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Terlambat
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty 
                    colspan="9" 
                    message="Belum ada data lulusan" 
                    submessage="Data lulusan dari endpoint API belum tersedia atau filter yang dipilih tidak memiliki hasil." 
                />
            @endforelse
            <tr id="noLulusanFilterMatchRow" class="hidden">
                <td colspan="9" class="text-center py-10 text-gray-400 text-xs font-medium">
                    Tidak ada data lulusan yang sesuai dengan filter yang dipilih.
                </td>
            </tr>
        </x-table>

        <!-- Pagination -->
        <x-pagination :paginator="$pagination" />
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchLulusanInput');
        const prodiFilter = document.getElementById('filterProdiSelect');
        const angkatanFilter = document.getElementById('filterAngkatanSelect');
        const tahunLulusFilter = document.getElementById('filterTahunLulusSelect');
        const resetBtn = document.getElementById('resetFilterBtn');
        const noFilterMatchRow = document.getElementById('noLulusanFilterMatchRow');
        const rows = document.querySelectorAll('.lulusan-row');

        let debounceTimer = null;

        function applyClientSearch() {
            const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
            let visibleCount = 0;

            rows.forEach(row => {
                const rowNama = row.getAttribute('data-nama') || '';
                const rowNim = row.getAttribute('data-nim') || '';
                const rowJenjang = row.getAttribute('data-jenjang') || '';
                const rowTahun = row.getAttribute('data-tahun-masuk') || '';

                const matchesKeyword = !keyword || (rowNama.includes(keyword) || rowNim.includes(keyword) || rowJenjang.includes(keyword) || rowTahun.includes(keyword));

                if (matchesKeyword) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noFilterMatchRow) {
                noFilterMatchRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
            }
        }

        function syncUrlParams() {
            const params = new URLSearchParams(window.location.search);
            const searchVal = searchInput ? searchInput.value.trim() : '';
            const prodiVal = prodiFilter ? prodiFilter.value : '';
            const angkatanVal = angkatanFilter ? angkatanFilter.value : '';
            const tahunLulusVal = tahunLulusFilter ? tahunLulusFilter.value : '';

            if (searchVal) {
                params.set('search', searchVal);
            } else {
                params.delete('search');
            }

            if (prodiVal) {
                params.set('id_prodi', prodiVal);
            } else {
                params.delete('id_prodi');
            }

            if (angkatanVal) {
                params.set('angkatan', angkatanVal);
            } else {
                params.delete('angkatan');
            }

            if (tahunLulusVal) {
                params.set('tahun_lulus', tahunLulusVal);
            } else {
                params.delete('tahun_lulus');
            }

            params.set('page', '1');

            window.location.search = params.toString();
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                applyClientSearch();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    syncUrlParams();
                }, 600);
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(debounceTimer);
                    syncUrlParams();
                }
            });
        }

        [prodiFilter, angkatanFilter, tahunLulusFilter].forEach(el => {
            if (el) {
                el.addEventListener('change', function () {
                    syncUrlParams();
                });
            }
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                window.location.href = "{{ route('lulusan.index') }}";
            });
        }
    });
</script>
@endsection

