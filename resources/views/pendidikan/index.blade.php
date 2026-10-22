@extends('layouts.app')

@section('browser-title', 'Pendidikan')

@section('title', 'Pendidikan')

@section('subtitle')
    @if(!$apiError)
        @if($isDosen ?? false)
            Riwayat Pendidikan Saya &bull; {{ number_format((int) ($pagination['total'] ?? 0), 0, ',', '.') }} Data ditemukan
        @else
            {{ number_format((int) ($pagination['total'] ?? 0), 0, ',', '.') }} Data ditemukan
        @endif
    @endif
@endsection

@section('content')
@php
    $startNumber = (int) ($pagination['from'] ?? (((int) ($pagination['current_page'] ?? 1) - 1) * (int) ($pagination['per_page'] ?? 10) + 1));
    $selectedJenjang = (string) ($filters['jenjang'] ?? '');
    $searchQuery = (string) ($filters['search'] ?? '');
    $idDosen = !empty($filters['id_dosen']) ? (int) $filters['id_dosen'] : null;
    $idTendik = !empty($filters['id_tendik']) ? (int) $filters['id_tendik'] : null;
    $isDosen = $isDosen ?? false;
@endphp

<div class="space-y-5 pb-8">
    {{-- Banner for Logged-in Dosen --}}
    @if($isDosen && $authDosen)
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-[#EBF5FB] border border-[#BCE1F5] rounded-xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-[#01568D] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#01568D]">Akun Dosen</span>
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-tight">
                        {{ $authDosen['nama_dosen'] }}
                    </h3>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Anda hanya dapat melihat dan mengelola data riwayat pendidikan milik Anda sendiri.
                    </p>
                </div>
            </div>
            <a href="{{ route('dosen.show', $authDosen['id_dosen']) }}" class="inline-flex items-center gap-2 text-xs sm:text-sm font-bold text-[#01568D] hover:text-[#012650] hover:underline transition self-start sm:self-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Lihat Profil Dosen</span>
            </a>
        </div>
    {{-- Banner for Dosen / Tendik context if navigated from detail page (Non-Dosen user) --}}
    @elseif($selectedDosen)
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-[#EBF5FB] border border-[#BCE1F5] rounded-xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-[#01568D] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#01568D]">Konteks Dosen</span>
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-tight">
                        {{ $selectedDosen['nama'] }}
                    </h3>
                </div>
            </div>
            <a href="{{ route('dosen.show', $selectedDosen['id']) }}" class="inline-flex items-center gap-2 text-xs sm:text-sm font-bold text-[#01568D] hover:text-[#012650] hover:underline transition self-start sm:self-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke Detail Dosen</span>
            </a>
        </div>
    @elseif($selectedTendik)
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-[#EBF5FB] border border-[#BCE1F5] rounded-xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-[#01568D] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#01568D]">Konteks Tenaga Kependidikan</span>
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-tight">
                        {{ $selectedTendik['nama'] }}
                    </h3>
                </div>
            </div>
            <a href="{{ route('tenaga-kependidikan.show', $selectedTendik['id']) }}" class="inline-flex items-center gap-2 text-xs sm:text-sm font-bold text-[#01568D] hover:text-[#012650] hover:underline transition self-start sm:self-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke Detail Tendik</span>
            </a>
        </div>
    @endif

    @if($apiError)
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700 shadow-xs" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span>{{ $apiError }}</span>
            </div>
        </div>
    @endif

    {{-- Filter Bar (Search + Dropdown Jenjang + Button Tambah Pendidikan) --}}
    <form method="GET" action="{{ route('pendidikan.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" id="pendidikan-filter-form">
        @if(!$isDosen && $idDosen)
            <input type="hidden" name="id_dosen" value="{{ $idDosen }}">
        @endif
        @if(!$isDosen && $idTendik)
            <input type="hidden" name="id_tendik" value="{{ $idTendik }}">
        @endif

        <div class="flex flex-1 flex-wrap items-center gap-2.5">
            {{-- Search Input (Matching Image 3) --}}
            <div class="group flex h-[34px] w-[248px] max-w-full shrink-0 overflow-hidden rounded-md border border-primary bg-white-form p-[2px]">
                <label class="sr-only" for="pendidikan-search">Cari nama</label>
                <input
                    id="pendidikan-search"
                    type="text"
                    name="search"
                    value="{{ $searchQuery }}"
                    placeholder="Cari nama"
                    class="min-w-0 flex-1 border-0 bg-transparent px-3 py-1 text-xs text-gray-900 placeholder:text-gray-600 focus:outline-none focus:ring-0"
                >
                <button type="submit" class="inline-flex h-full w-[34px] shrink-0 items-center justify-center rounded-[5px] text-black transition {{ filled($searchQuery) ? 'border border-primary bg-[#ffc800]' : 'border border-transparent bg-transparent' }}" aria-label="Cari">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </div>

            {{-- Jenjang Filter Dropdown with yellow chevron box (Clean Non-duplicate list) --}}
            <input type="hidden" name="jenjang" value="{{ $selectedJenjang }}" id="pendidikan-jenjang-value">
            <div class="relative">
                <button type="button" class="flex h-[34px] w-[140px] shrink-0 items-center justify-between overflow-hidden rounded-md border border-primary bg-white-form text-left text-[11px] text-gray-900 cursor-pointer" data-filter-trigger aria-expanded="false" aria-controls="pendidikan-jenjang-menu">
                    <span class="min-w-0 flex-1 truncate px-2 font-medium">
                        {{ $selectedJenjang !== '' ? $selectedJenjang : 'Jenjang' }}
                    </span>
                    <span class="mx-[2px] my-[2px] flex h-[26px] w-7 shrink-0 items-center justify-center rounded-[5px] border border-primary bg-[#ffc800]">
                        <svg class="h-4 w-4 transition-transform" data-filter-chevron fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </button>
                <div id="pendidikan-jenjang-menu" class="absolute left-0 top-full z-20 mt-1 hidden max-h-52 w-full overflow-y-auto rounded-md border border-primary bg-white-form p-1.5 shadow-lg" data-filter-menu>
                    <button type="button" class="block w-full rounded px-2 py-1 text-left text-[11px] text-gray-800 hover:bg-white hover:text-black font-semibold cursor-pointer" data-filter-value="" data-filter-label="Jenjang">
                        Semua Jenjang
                    </button>
                    @foreach($jenjangOptions as $opt)
                        <button type="button" class="block w-full rounded px-2 py-1 text-left text-[11px] text-gray-800 hover:bg-white hover:text-black font-medium cursor-pointer" data-filter-value="{{ $opt }}" data-filter-label="{{ $opt }}">
                            {{ $opt }}
                        </button>
                    @endforeach
                </div>
            </div>

            @if(filled($searchQuery) || filled($selectedJenjang))
                <a href="{{ route('pendidikan.index', $isDosen ? [] : request()->only(['id_dosen', 'id_tendik'])) }}" class="inline-flex h-[34px] items-center gap-1 rounded-md border border-gray-300 bg-white px-2.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition" title="Reset filter">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Reset</span>
                </a>
            @endif
        </div>

        {{-- Button Tambah Pendidikan (Matching user's preferred label) --}}
        <button 
            type="button" 
            id="openCreateModalBtn"
            class="inline-flex h-[34px] shrink-0 items-center justify-center gap-1.5 rounded-md bg-additional px-3.5 text-xs font-bold text-white transition hover:bg-primary shadow-xs cursor-pointer select-none"
        >
            <span class="inline-flex h-3.5 w-3.5 items-center justify-center rounded-full bg-white text-additional">
                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" />
                </svg>
            </span>
            <span>Tambah Pendidikan</span>
        </button>
    </form>

    {{-- Table (Matching Image 3) --}}
    <x-table id="pendidikan-table" class="!border-collapse !border-spacing-0 text-left text-[13px]">
        <x-slot:header>
            <x-table.th class="!rounded-none !bg-white !px-3 !py-2.5 !text-center !text-[13px] !font-bold border-y border-gray-300 w-12">No</x-table.th>
            <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Universitas</x-table.th>
            <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Program Studi</x-table.th>
            <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Jenjang</x-table.th>
            <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Tahun Lulus</x-table.th>
            <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-center !text-[13px] !font-bold border-y border-gray-300 w-28">Action</x-table.th>
        </x-slot:header>

        @forelse($pendidikanList as $index => $item)
            @php
                $pendidikanId = $item['id_pendidikan'] ?? $item['id'] ?? null;
                $universitas = $item['universitas'] ?? $item['nama_institusi'] ?? '-';
                $programStudi = $item['program_studi'] ?? $item['prodi'] ?? '-';
                $jenjangRaw = $item['jenjang'] ?? '-';
                $jenjang = \App\Http\Controllers\PendidikanController::normalizeJenjang($jenjangRaw);
                $tahunLulus = $item['tahun_lulus'] ?? '-';
                $dosenCount = isset($item['dosen_count']) ? (int) $item['dosen_count'] : null;
            @endphp
            <x-table.row class="bg-white transition-colors hover:bg-gray-50/50" data-row-id="{{ $pendidikanId }}">
                <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-3 !py-3.5 text-center text-sm font-medium text-gray-900">
                    {{ $startNumber + $index }}.
                </x-table.cell>
                <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm font-semibold text-gray-900">
                    <div>{{ $universitas }}</div>
                    @if(!$isDosen && $dosenCount !== null && $dosenCount > 0)
                        <div class="text-[11px] font-normal text-gray-500 mt-0.5">
                            {{ $dosenCount }} dosen terhubung
                        </div>
                    @endif
                </x-table.cell>
                <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm text-gray-900 font-medium">
                    {{ $programStudi }}
                </x-table.cell>
                <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm font-semibold text-gray-900">
                    {{ $jenjang }}
                </x-table.cell>
                <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm text-gray-900 font-medium">
                    {{ $tahunLulus }}
                </x-table.cell>
                <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5">
                    @if($pendidikanId)
                        <div class="flex items-center justify-center gap-3.5">
                            {{-- Edit Button (triggers Edit Modal) --}}
                            <button 
                                type="button" 
                                class="open-edit-btn text-amber-500 hover:text-amber-600 transition cursor-pointer"
                                title="Edit pendidikan"
                                aria-label="Edit pendidikan"
                                data-id="{{ $pendidikanId }}"
                                data-universitas="{{ $universitas }}"
                                data-prodi="{{ $programStudi }}"
                                data-jenjang="{{ $jenjang }}"
                                data-tahun="{{ $tahunLulus }}"
                            >
                                <x-icon.edit class="h-5 w-5" />
                            </button>

                            {{-- Delete Button (triggers Confirm Delete Modal) --}}
                            <form 
                                method="POST" 
                                action="{{ route('pendidikan.destroy', ['pendidikan' => $pendidikanId] + ($isDosen ? [] : request()->only(['search', 'jenjang', 'id_dosen', 'id_tendik']))) }}"
                                data-confirm-delete
                                data-title="Hapus Data Pendidikan?"
                                data-message="Apakah Anda yakin ingin menghapus data Pendidikan ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700 transition cursor-pointer" title="Hapus pendidikan" aria-label="Hapus pendidikan">
                                    <x-icon.delete class="h-5 w-5" />
                                </button>
                            </form>
                        </div>
                    @else
                        <span class="text-gray-400 text-center block">-</span>
                    @endif
                </x-table.cell>
            </x-table.row>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 border-y border-gray-300">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        <p class="font-semibold text-gray-700">
                            @if($isDosen)
                                Anda belum memiliki riwayat pendidikan tercatat
                            @else
                                Tidak ada data pendidikan ditemukan
                            @endif
                        </p>
                        @if(filled($searchQuery) || filled($selectedJenjang))
                            <p class="text-xs text-gray-500">Coba ubah kata kunci pencarian atau reset filter.</p>
                        @else
                            <p class="text-xs text-gray-500">Klik tombol 'Tambah Pendidikan' untuk menambahkan data baru.</p>
                        @endif
                    </div>
                </td>
            </tr>
        @endforelse
    </x-table>

    {{-- Pagination (Matching Image 3) --}}
    <x-pagination :paginator="$pagination" />
</div>

{{-- MODAL 1: Tambahkan Pendidikan (Exact Match Image 4) --}}
<div 
    id="createPendidikanModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createModalTitle"
>
    <div class="modal-card-box relative bg-white rounded-[24px] p-7 sm:p-8 max-w-[440px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createModalTitle" class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan Pendidikan
        </h3>

        <form action="{{ route('pendidikan.store') }}" method="POST" class="space-y-4" id="createPendidikanForm">
            @csrf

            {{-- Hidden preserved query params or automatic Dosen linkage --}}
            @if($isDosen && $authDosen)
                <input type="hidden" name="target_type" value="dosen">
                <input type="hidden" name="id_dosen" value="{{ $authDosen['id_dosen'] }}">
            @else
                @if($idDosen)
                    <input type="hidden" name="id_dosen" value="{{ $idDosen }}">
                @endif
                @if($idTendik)
                    <input type="hidden" name="id_tendik" value="{{ $idTendik }}">
                @endif
            @endif

            {{-- Field 1: Universitas --}}
            <div>
                <label for="createUniversitas" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Universitas
                </label>
                <input 
                    type="text" 
                    name="universitas" 
                    id="createUniversitas" 
                    placeholder="Isi Nama Universitas" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            {{-- Field 2: Program Studi --}}
            <div>
                <label for="createProgramStudi" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Program Studi
                </label>
                <input 
                    type="text" 
                    name="program_studi" 
                    id="createProgramStudi" 
                    placeholder="Isi Program Studi" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            {{-- Field 3: Jenjang Pendidikan (Clean non-duplicate options) --}}
            <div>
                <label for="createJenjang" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jenjang Pendidikan
                </label>
                <div class="relative">
                    <select 
                        name="jenjang" 
                        id="createJenjang" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih jenjang pendidikan</option>
                        @foreach($jenjangOptions as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            {{-- Field 4: Tahun Lulus --}}
            <div>
                <label for="createTahunLulus" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tahun Lulus
                </label>
                <input 
                    type="number" 
                    name="tahun_lulus" 
                    id="createTahunLulus" 
                    min="1900" 
                    max="2100" 
                    placeholder="Isi tahun terakhir lulus" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            {{-- Field 5: SDM Linkage (Only shown for non-Dosen) --}}
            @if(!$isDosen)
                <div class="pt-2 border-t border-gray-200">
                    <label class="block text-xs font-bold text-gray-900 mb-1.5">
                        Tautkan ke Dosen / Tendik <span class="text-xs font-normal text-gray-500">(Opsional)</span>
                    </label>

                    @if($selectedDosen)
                        <div class="p-2.5 rounded-[10px] bg-[#EBF5FB] border border-[#BCE1F5] text-xs font-semibold text-[#01568D] flex items-center justify-between">
                            <span>Ditautkan ke: {{ $selectedDosen['nama'] }}</span>
                            <input type="hidden" name="target_type" value="dosen">
                            <input type="hidden" name="id_dosen" value="{{ $selectedDosen['id'] }}">
                        </div>
                    @elseif($selectedTendik)
                        <div class="p-2.5 rounded-[10px] bg-[#EBF5FB] border border-[#BCE1F5] text-xs font-semibold text-[#01568D] flex items-center justify-between">
                            <span>Ditautkan ke: {{ $selectedTendik['nama'] }}</span>
                            <input type="hidden" name="target_type" value="tendik">
                            <input type="hidden" name="id_tendik" value="{{ $selectedTendik['id'] }}">
                        </div>
                    @else
                        <div class="grid grid-cols-3 gap-2 mb-2">
                            <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                <input type="radio" name="target_type" value="none" checked class="create-target-radio">
                                <span>Tidak</span>
                            </label>
                            <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                <input type="radio" name="target_type" value="dosen" class="create-target-radio">
                                <span>Dosen</span>
                            </label>
                            <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                <input type="radio" name="target_type" value="tendik" class="create-target-radio">
                                <span>Tendik</span>
                            </label>
                        </div>

                        {{-- Dosen select --}}
                        <div id="createDosenSelectWrapper" class="hidden mt-2">
                            <div class="relative">
                                <select name="id_dosen" id="createDosenSelect" class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-xs font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer">
                                    <option value="" disabled selected>Pilih Dosen</option>
                                    @foreach($dosenOptions as $d)
                                        <option value="{{ $d['id'] }}">{{ $d['nama'] }}</option>
                                    @endforeach
                                </select>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" /></svg>
                                </span>
                            </div>
                        </div>

                        {{-- Tendik select --}}
                        <div id="createTendikSelectWrapper" class="hidden mt-2">
                            <div class="relative">
                                <select name="id_tendik" id="createTendikSelect" class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-xs font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer">
                                    <option value="" disabled selected>Pilih Tenaga Kependidikan</option>
                                    @foreach($tendikOptions as $t)
                                        <option value="{{ $t['id'] }}">{{ $t['nama'] }}</option>
                                    @endforeach
                                </select>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" /></svg>
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Action Buttons: Batal & Simpan (Matching Image 4) --}}
            <div class="grid grid-cols-2 gap-4 pt-3">
                <button 
                    type="button" 
                    id="closeCreateModalBtn"
                    class="w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-[#01568D] hover:bg-primary text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: Edit Data Pendidikan --}}
<div 
    id="editPendidikanModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editModalTitle"
>
    <div class="modal-card-box relative bg-white rounded-[24px] p-7 sm:p-8 max-w-[440px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editModalTitle" class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Edit Data Pendidikan
        </h3>

        <form action="#" method="POST" class="space-y-4" id="editPendidikanForm">
            @csrf
            @method('PUT')

            {{-- Field 1: Universitas --}}
            <div>
                <label for="editUniversitas" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Universitas
                </label>
                <input 
                    type="text" 
                    name="universitas" 
                    id="editUniversitas" 
                    placeholder="Isi Nama Universitas" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            {{-- Field 2: Program Studi --}}
            <div>
                <label for="editProgramStudi" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Program Studi
                </label>
                <input 
                    type="text" 
                    name="program_studi" 
                    id="editProgramStudi" 
                    placeholder="Isi Program Studi" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            {{-- Field 3: Jenjang Pendidikan --}}
            <div>
                <label for="editJenjang" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jenjang Pendidikan
                </label>
                <div class="relative">
                    <select 
                        name="jenjang" 
                        id="editJenjang" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih jenjang pendidikan</option>
                        @foreach($jenjangOptions as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            {{-- Field 4: Tahun Lulus --}}
            <div>
                <label for="editTahunLulus" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tahun Lulus
                </label>
                <input 
                    type="number" 
                    name="tahun_lulus" 
                    id="editTahunLulus" 
                    min="1900" 
                    max="2100" 
                    placeholder="Isi tahun terakhir lulus" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            {{-- Action Buttons: Batal & Simpan --}}
            <div class="grid grid-cols-2 gap-4 pt-3">
                <button 
                    type="button" 
                    id="closeEditModalBtn"
                    class="w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-[#01568D] hover:bg-primary text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Confirmation Delete Modal (Exact Match Image 5) --}}
<x-modal.confirm-delete 
    id="confirmDeleteModal" 
    title="Hapus Data Pendidikan?" 
    message="Apakah Anda yakin ingin menghapus data Pendidikan ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan." 
/>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Dropdown menu handling for Jenjang Filter
        const filterTrigger = document.querySelector('[data-filter-trigger]');
        const filterMenu = document.querySelector('[data-filter-menu]');
        const filterChevron = document.querySelector('[data-filter-chevron]');
        const jenjangValueInput = document.getElementById('pendidikan-jenjang-value');
        const filterForm = document.getElementById('pendidikan-filter-form');

        if (filterTrigger && filterMenu) {
            filterTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                const isHidden = filterMenu.classList.contains('hidden');
                if (isHidden) {
                    filterMenu.classList.remove('hidden');
                    filterTrigger.setAttribute('aria-expanded', 'true');
                    filterChevron?.classList.add('rotate-180');
                } else {
                    filterMenu.classList.add('hidden');
                    filterTrigger.setAttribute('aria-expanded', 'false');
                    filterChevron?.classList.remove('rotate-180');
                }
            });

            filterMenu.querySelectorAll('[data-filter-value]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const val = this.getAttribute('data-filter-value');
                    if (jenjangValueInput) {
                        jenjangValueInput.value = val;
                    }
                    filterMenu.classList.add('hidden');
                    filterChevron?.classList.remove('rotate-180');
                    if (filterForm) {
                        filterForm.submit();
                    }
                });
            });

            document.addEventListener('click', function (e) {
                if (!filterTrigger.contains(e.target) && !filterMenu.contains(e.target)) {
                    filterMenu.classList.add('hidden');
                    filterChevron?.classList.remove('rotate-180');
                }
            });
        }

        // Generic Modal Helpers
        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.modal-card-box');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.modal-card-box');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        // Create Modal Elements
        const createModal = document.getElementById('createPendidikanModal');
        const openCreateBtn = document.getElementById('openCreateModalBtn');
        const closeCreateBtn = document.getElementById('closeCreateModalBtn');

        if (openCreateBtn && createModal) {
            openCreateBtn.addEventListener('click', () => showModal(createModal));
        }

        if (closeCreateBtn && createModal) {
            closeCreateBtn.addEventListener('click', () => hideModal(createModal));
        }

        // Target Radio switches for Dosen/Tendik inside Create Modal
        const targetRadios = document.querySelectorAll('.create-target-radio');
        const dosenWrapper = document.getElementById('createDosenSelectWrapper');
        const tendikWrapper = document.getElementById('createTendikSelectWrapper');

        targetRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === 'dosen') {
                    dosenWrapper?.classList.remove('hidden');
                    tendikWrapper?.classList.add('hidden');
                } else if (this.value === 'tendik') {
                    tendikWrapper?.classList.remove('hidden');
                    dosenWrapper?.classList.add('hidden');
                } else {
                    dosenWrapper?.classList.add('hidden');
                    tendikWrapper?.classList.add('hidden');
                }
            });
        });

        // Edit Modal Elements
        const editModal = document.getElementById('editPendidikanModal');
        const editForm = document.getElementById('editPendidikanForm');
        const closeEditBtn = document.getElementById('closeEditModalBtn');
        const editUniv = document.getElementById('editUniversitas');
        const editProdi = document.getElementById('editProgramStudi');
        const editJenjang = document.getElementById('editJenjang');
        const editTahun = document.getElementById('editTahunLulus');

        document.querySelectorAll('.open-edit-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const univ = this.getAttribute('data-universitas') || '';
                const prodi = this.getAttribute('data-prodi') || '';
                const jenjang = this.getAttribute('data-jenjang') || '';
                const tahun = this.getAttribute('data-tahun') || '';

                if (editForm) {
                    editForm.action = '{{ url("pendidikan") }}/' + id;
                }
                if (editUniv) editUniv.value = univ !== '-' ? univ : '';
                if (editProdi) editProdi.value = prodi !== '-' ? prodi : '';
                if (editJenjang) editJenjang.value = jenjang !== '-' ? jenjang : '';
                if (editTahun) editTahun.value = tahun !== '-' ? tahun : '';

                showModal(editModal);
            });
        });

        if (closeEditBtn && editModal) {
            closeEditBtn.addEventListener('click', () => hideModal(editModal));
        }

        // Close Modals on backdrop click or Escape key
        [createModal, editModal].forEach(m => {
            if (!m) return;
            m.addEventListener('click', function (e) {
                if (e.target === m) hideModal(m);
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideModal(createModal);
                hideModal(editModal);
            }
        });
    });
</script>
@endsection
