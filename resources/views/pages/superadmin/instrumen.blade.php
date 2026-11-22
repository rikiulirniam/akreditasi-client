@extends('layouts.app')

@section('title', 'Master Data Instrumen - Versi ' . ($versi['nomor_versi'] ?? ''))

@section('content')
<div class="space-y-6">

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

    {{-- Breadcrumbs --}}
    <nav class="flex text-xs sm:text-sm font-medium text-gray-500 py-1" aria-label="Breadcrumb">
        <ol class="inline-flex flex-wrap items-center gap-y-1">
            <li class="inline-flex items-center">
                <a href="{{ route('akreditasi.superadmin.lembaga_akreditasi') }}" class="hover:text-primary transition font-medium">Master Data Akreditasi</a>
            </li>
            <li class="inline-flex items-center">
                <span class="mx-1 text-gray-400">/</span>
                <a href="{{ route('akreditasi.superadmin.versi_instrumen', $lembagaId) }}" class="hover:text-primary transition font-medium">{{ $lembaga['nama_lembaga'] ?? 'Lembaga Akreditasi' }}</a>
            </li>
            <li class="inline-flex items-center" aria-current="page">
                <span class="mx-1 text-gray-400">/</span>
                <span class="font-bold text-gray-800">Versi {{ $versi['nomor_versi'] ?? '-' }}</span>
            </li>
        </ol>
    </nav>

    {{-- Banner Utama: DETAIL VERSI INSTRUMEN --}}
    <section class="flex flex-col justify-between gap-5 rounded-2xl bg-primary p-6 sm:p-7 shadow-xs sm:flex-row sm:items-center text-white">
        <div class="min-w-0">
            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-secondary">
                DETAIL VERSI INSTRUMEN
            </p>
            <div class="mt-1 flex items-center gap-3 flex-wrap">
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Versi {{ $versi['nomor_versi'] ?? '-' }}
                </h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ !empty($versi['status_aktif']) ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/40' : 'bg-white/20 text-white/80' }}">
                    {{ !empty($versi['status_aktif']) ? 'Aktif' : 'Non-Aktif' }}
                </span>
            </div>
            <p class="mt-2 text-sm text-white/80">
                Lembaga: <span class="font-bold text-white">{{ $lembaga['nama_lembaga'] ?? '-' }}</span> &bull; 
                Berlaku sejak: <span class="text-white">{{ !empty($versi['tanggal_berlaku']) ? date('d M Y', strtotime($versi['tanggal_berlaku'])) : 'Tidak ditentukan' }}</span>
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a 
                href="{{ route('akreditasi.superadmin.versi_instrumen', $lembagaId) }}" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/15 hover:bg-white/25 text-white text-xs sm:text-sm font-bold transition cursor-pointer shadow-xs"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Daftar Versi
            </a>
        </div>
    </section>

    {{-- Metadata Info --}}
    <section class="grid grid-cols-1 sm:grid-cols-4 gap-4 rounded-2xl border border-[#EAEAEA] bg-white p-5 sm:p-6 shadow-xs">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">LEMBAGA</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900">{{ $lembaga['nama_lembaga'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">NOMOR VERSI</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900">v{{ $versi['nomor_versi'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">TANGGAL BERLAKU</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900">
                {{ !empty($versi['tanggal_berlaku']) ? date('d M Y', strtotime($versi['tanggal_berlaku'])) : '-' }}
            </p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">TOTAL INSTRUMEN</p>
            <p class="mt-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    {{ count($instrumens ?? []) }} Instrumen Terdaftar
                </span>
            </p>
        </div>
    </section>

    {{-- Tabel Instrumen --}}
    <section class="space-y-4">
        {{-- Header Tabel --}}
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Daftar Instrumen</h3>
                <p class="text-xs sm:text-sm text-gray-500">{{ count($instrumens ?? []) }} data instrumen pada versi ini</p>
            </div>
            <button onclick="toggleModal('modal-tambah-instrumen')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer">                
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Instrumen
            </button>
        </div>

        {{-- Kontainer Tabel --}}
        <div class="bg-white rounded-2xl border border-[#EAEAEA] shadow-xs overflow-hidden">
            <x-table>
                <x-slot:header>
                    <x-table.th class="w-36">ID / UID</x-table.th>
                    <x-table.th>Jenis Instrumen</x-table.th>
                    <x-table.th class="w-48">Jenjang</x-table.th>
                    <x-table.th class="w-36">Kriteria</x-table.th>
                    <x-table.th class="w-32 text-right">Aksi</x-table.th>
                </x-slot:header>

                @forelse ($instrumens ?? [] as $item)
                    <x-table.row 
                        onclick="if (!event.target.closest('button, form, a')) { window.location.href='{{ route('akreditasi.superadmin.kriteria', ['lembagaId' => $lembagaId, 'instrumenId' => $item['id']]) }}'; }" 
                        class="cursor-pointer hover:bg-sky-50/60 transition-colors group"
                    >
                        <x-table.cell>
                            <span class="font-mono font-bold text-sm text-primary tracking-tight">
                                {{ is_numeric($item['id'] ?? null) ? 'INS-'.str_pad($item['id'], 3, '0', STR_PAD_LEFT) : ($item['id'] ?? '-') }}
                            </span>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="font-semibold text-sm text-gray-900 group-hover:text-primary transition-colors">
                                {{ $item['jenis_instrumen']['nama_jenis'] ?? ($item['jenis_akreditasi']['nama_jenis'] ?? ($item['nama_instrumen'] ?? 'Instrumen Akreditasi')) }}
                            </span>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 group-hover:bg-white group-hover:shadow-xs transition-all">
                                {{ $item['jenjang']['nama_jenjang'] ?? ($item['jenjang'] ?? 'Semua Jenjang') }}
                            </span>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="text-sm text-gray-500 font-medium">
                                {{ $item['kriterias_count'] ?? ($item['kriteria_count'] ?? count($item['kriterias'] ?? [])) }} Kriteria
                            </span>
                        </x-table.cell>
                        <x-table.cell class="w-32 text-right">
                            <div class="flex items-center justify-end gap-1" onclick="event.stopPropagation()">
                                <!-- Hierarchy Icon (Tree Structure) -->
                                <button 
                                    type="button" 
                                    class="text-[#0284C7] hover:text-[#0369A1] transition p-1.5 rounded-lg hover:bg-sky-50 cursor-pointer"
                                    title="Lihat Hierarki Instrumen (7 Level)"
                                    onclick="openHierarchyModal('{{ $item['id'] }}', '{{ addslashes($item['jenis_instrumen']['nama_jenis'] ?? ($item['nama_instrumen'] ?? 'Instrumen')) }}')"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                    </svg>
                                </button>

                                <!-- Edit Icon (Yellow) -->
                                <button 
                                    type="button" 
                                    class="text-[#F59E0B] hover:text-[#D97706] transition p-1.5 rounded-lg hover:bg-amber-50 cursor-pointer"
                                    title="Edit Instrumen"
                                    onclick="openEditInstrumenModal({{ json_encode($item) }})"
                                >
                                    <x-icon.edit class="w-5 h-5" />
                                </button>

                                <!-- Delete Icon (Red) -->
                                <button 
                                    type="button" 
                                    class="text-[#DC2626] hover:text-[#B91C1C] transition p-1.5 rounded-lg hover:bg-rose-50 cursor-pointer"
                                    title="Hapus Instrumen"
                                    data-confirm-delete
                                    data-action="{{ route('akreditasi.superadmin.instrumen.destroy', ['lembagaId' => $lembagaId, 'instrumenId' => $item['id']]) }}"
                                    data-title="Hapus Instrumen?"
                                    data-message="Apakah Anda yakin ingin menghapus instrumen ini? Seluruh kriteria dan indikator terkait akan ikut terhapus."
                                >
                                    <x-icon.delete class="w-5 h-5" />
                                </button>

                                <!-- Arrow icon -->
                                <a 
                                    href="{{ route('akreditasi.superadmin.kriteria', ['lembagaId' => $lembagaId, 'instrumenId' => $item['id']]) }}"
                                    class="inline-flex p-1.5 text-primary hover:text-additional transition-all"
                                    title="Buka Kriteria"
                                >                                
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="5" class="py-8 text-center text-sm font-medium text-gray-500">
                            Belum ada instrumen yang terdaftar pada versi ini. Tambahkan instrumen melalui tombol di atas.
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table>
        </div>
    </section>

</div>

{{-- Modal Tambah Instrumen --}}
<div id="modal-tambah-instrumen" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Tambah Instrumen Baru</h3>
                <button type="button" onclick="toggleModal('modal-tambah-instrumen')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('akreditasi.superadmin.instrumen.store', ['lembagaId' => $lembagaId, 'versiId' => $versiId]) }}" method="POST">
                @csrf
                <input type="hidden" name="id_versi_instrumen" value="{{ $versiId }}">
                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Versi Instrumen</label>
                        <input type="text" disabled value="v{{ $versi['nomor_versi'] ?? '-' }} ({{ !empty($versi['status_aktif']) ? 'Aktif' : 'Non-Aktif' }})" class="block w-full rounded-xl border border-gray-200 bg-gray-100 p-3 text-sm text-gray-700 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Jenis Instrumen <span class="text-red-500">*</span></label>
                        <select name="id_jenis_instrumen" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="1">APS untuk Pemenuhan Syarat Minimum Program Studi Baru</option>
                            <option value="2">Instrumen Akreditasi Program Studi (IAPS)</option>
                            <option value="3">Reakreditasi Program Studi</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Jenjang <span class="text-red-500">*</span></label>
                        <select name="id_jenjang" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="1">Sarjana (S1)</option>
                            <option value="2">Sarjana Terapan (D4)</option>
                            <option value="3">Diploma Tiga (D3)</option>
                            <option value="4">Magister (S2)</option>
                            <option value="5">Magister Terapan (S2 Terapan)</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 sm:p-6 bg-gray-50">
                    <button onclick="toggleModal('modal-tambah-instrumen')" type="button" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Batal</button>
                    <button type="submit" class="rounded-2xl bg-primary px-6 py-2.5 text-sm font-bold text-white hover:bg-additional transition cursor-pointer shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Instrumen --}}
<div id="modal-edit-instrumen" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Edit Instrumen</h3>
                <button type="button" onclick="toggleModal('modal-edit-instrumen')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="form-edit-instrumen" action="#" method="POST">
                @csrf
                @method('PUT')
                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Versi Instrumen <span class="text-red-500">*</span></label>
                        <select id="edit-versi-instrumen" name="id_versi_instrumen" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                            @forelse ($versis ?? [] as $vOption)
                                <option value="{{ $vOption['id'] }}" {{ (string)$vOption['id'] === (string)$versiId ? 'selected' : '' }}>
                                    v{{ $vOption['nomor_versi'] }} {{ !empty($vOption['status_aktif']) ? '(Aktif)' : '' }}
                                </option>
                            @empty
                                <option value="{{ $versiId }}">Versi Default (v{{ $versi['nomor_versi'] ?? '1.0' }})</option>
                            @endforelse
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Jenis Instrumen <span class="text-red-500">*</span></label>
                        <select id="edit-jenis-instrumen" name="id_jenis_instrumen" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="1">APS untuk Pemenuhan Syarat Minimum Program Studi Baru</option>
                            <option value="2">Instrumen Akreditasi Program Studi (IAPS)</option>
                            <option value="3">Reakreditasi Program Studi</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Jenjang <span class="text-red-500">*</span></label>
                        <select id="edit-jenjang-instrumen" name="id_jenjang" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="1">Sarjana (S1)</option>
                            <option value="2">Sarjana Terapan (D4)</option>
                            <option value="3">Diploma Tiga (D3)</option>
                            <option value="4">Magister (S2)</option>
                            <option value="5">Magister Terapan (S2 Terapan)</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 sm:p-6 bg-gray-50">
                    <button onclick="toggleModal('modal-edit-instrumen')" type="button" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Batal</button>
                    <button type="submit" class="rounded-2xl bg-primary px-6 py-2.5 text-sm font-bold text-white hover:bg-additional transition cursor-pointer shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-modal.confirm-delete />

{{-- Modal Hierarki Instrumen (7 Level) --}}
<div id="modal-hierarki-instrumen" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/60 backdrop-blur-[2px] p-4">
    <div class="relative w-full max-w-4xl max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        {{-- Header Modal --}}
        <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6 bg-slate-50/70 shrink-0">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-extrabold uppercase tracking-wider bg-primary/10 text-primary">
                        7 Tingkat Struktur
                    </span>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Struktur Hierarki Instrumen</h3>
                </div>
                <p id="hierarki-instrumen-subtitle" class="text-xs sm:text-sm text-gray-500 mt-1 font-medium">Memuat data instrumen...</p>
            </div>
            <button type="button" onclick="toggleModal('modal-hierarki-instrumen')" class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="p-5 sm:p-6 overflow-y-auto space-y-4">
            {{-- Loading State --}}
            <div id="hierarki-loading" class="py-12 flex flex-col items-center justify-center text-center space-y-3">
                <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <p class="text-sm font-semibold text-gray-600">Mengambil struktur hierarki instrumen dari backend...</p>
            </div>

            {{-- Error State --}}
            <div id="hierarki-error" class="hidden rounded-2xl bg-rose-50 border border-rose-200 p-6 text-center space-y-3">
                <div class="mx-auto w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center font-bold">!</div>
                <p id="hierarki-error-message" class="text-sm font-semibold text-rose-800">Gagal memuat hierarki instrumen.</p>
                <button type="button" onclick="if (currentHierarchyInstrumenId) loadHierarchyData(currentHierarchyInstrumenId)" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                    Coba Lagi
                </button>
            </div>

            {{-- Empty State --}}
            <div id="hierarki-empty" class="hidden rounded-2xl border border-dashed border-gray-300 p-12 text-center text-sm font-medium text-gray-500">
                Struktur hierarki belum memiliki kriteria atau butir penilaian aktif.
            </div>

            {{-- Content State --}}
            <div id="hierarki-content" class="hidden space-y-4">
                {{-- Metadata Overview (Level 1, 2, 3) --}}
                <div class="rounded-xl border border-gray-200 bg-slate-50 p-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block">Tingkat 1: Lembaga</span>
                        <span id="hierarki-lembaga-badge" class="text-xs font-bold text-gray-900 mt-0.5 block">-</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block">Tingkat 2: Versi Instrumen</span>
                        <span id="hierarki-versi-badge" class="text-xs font-bold text-gray-900 mt-0.5 block">-</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block">Tingkat 3: Instrumen & Jenjang</span>
                        <span id="hierarki-jenjang-badge" class="text-xs font-bold text-primary mt-0.5 block leading-tight">-</span>
                    </div>
                    <div class="col-span-full pt-2 border-t border-gray-200/80 flex items-center justify-between text-xs text-gray-600">
                        <span class="font-semibold text-gray-500">Statistik Data:</span>
                        <span id="hierarki-stats" class="font-bold text-gray-800">-</span>
                    </div>
                </div>

                {{-- Tree Hierarchy Container (Level 4 - 7) --}}
                <div id="hierarki-tree" class="space-y-4">
                    {{-- Rendered via JavaScript --}}
                </div>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="flex items-center justify-end border-t border-gray-200 p-4 bg-gray-50 shrink-0">
            <button type="button" onclick="toggleModal('modal-hierarki-instrumen')" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
    }

    function openEditInstrumenModal(item) {
        const form = document.getElementById('form-edit-instrumen');
        const versiSelect = document.getElementById('edit-versi-instrumen');
        const jenisSelect = document.getElementById('edit-jenis-instrumen');
        const jenjangSelect = document.getElementById('edit-jenjang-instrumen');

        if (form && item) {
            form.action = "{{ route('akreditasi.superadmin.instrumen.update', ['lembagaId' => $lembagaId, 'instrumenId' => ':id']) }}".replace(':id', item.id);
            if (versiSelect) versiSelect.value = item.id_versi_instrumen || (item.versi_instrumen ? item.versi_instrumen.id : '{{ $versiId }}');
            if (jenisSelect) jenisSelect.value = item.id_jenis_instrumen || '1';
            if (jenjangSelect) jenjangSelect.value = item.id_jenjang || '1';
            toggleModal('modal-edit-instrumen');
        }
    }

    let currentHierarchyInstrumenId = null;

    function openHierarchyModal(instrumenId, title) {
        currentHierarchyInstrumenId = instrumenId;
        const subtitle = document.getElementById('hierarki-instrumen-subtitle');
        if (subtitle) {
            subtitle.textContent = title ? 'Instrumen: ' + title : 'Memuat data...';
        }
        toggleModal('modal-hierarki-instrumen');
        loadHierarchyData(instrumenId);
    }

    function loadHierarchyData(instrumenId) {
        const loadingEl = document.getElementById('hierarki-loading');
        const errorEl = document.getElementById('hierarki-error');
        const contentEl = document.getElementById('hierarki-content');
        const emptyEl = document.getElementById('hierarki-empty');
        const errorMessageEl = document.getElementById('hierarki-error-message');

        loadingEl.classList.remove('hidden');
        errorEl.classList.add('hidden');
        contentEl.classList.add('hidden');
        emptyEl.classList.add('hidden');

        const url = "{{ route('akreditasi.superadmin.instrumen.hierarchy', ['lembagaId' => $lembagaId, 'instrumenId' => ':id']) }}".replace(':id', instrumenId);

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async response => {
            let res = {};
            try { res = await response.json(); } catch(e) {}
            if (!response.ok || (res.success !== undefined && res.success === false)) {
                throw new Error(res.message || 'Gagal memuat hierarki instrumen.');
            }
            return res.data;
        })
        .then(data => {
            loadingEl.classList.add('hidden');
            if (!data || !data.kriteria || data.kriteria.length === 0) {
                emptyEl.classList.remove('hidden');
                return;
            }
            renderHierarchy(data);
            contentEl.classList.remove('hidden');
        })
        .catch(err => {
            loadingEl.classList.add('hidden');
            errorEl.classList.remove('hidden');
            if (errorMessageEl) {
                errorMessageEl.textContent = err.message || 'Terjadi kesalahan saat memuat data.';
            }
        });
    }

    function renderHierarchy(data) {
        // Populate level 1-3 badges
        const lembagaEl = document.getElementById('hierarki-lembaga-badge');
        const versiEl = document.getElementById('hierarki-versi-badge');
        const jenjangEl = document.getElementById('hierarki-jenjang-badge');

        if (lembagaEl) lembagaEl.textContent = data.lembaga?.nama || '{{ $lembaga['nama_lembaga'] ?? '-' }}';
        if (versiEl) versiEl.textContent = 'v' + (data.versi_instrumen?.nomor_versi || '{{ $versi['nomor_versi'] ?? '-' }}');
        if (jenjangEl) jenjangEl.textContent = (data.jenis_instrumen?.nama || '-') + ' (' + (data.jenjang?.nama || 'Sarjana Terapan') + ')';

        const kriterias = data.kriteria || [];
        let totalSub = 0;
        let totalInd = 0;
        kriterias.forEach(k => {
            const subs = k.subkriteria || [];
            totalSub += subs.length;
            subs.forEach(s => {
                totalInd += (s.indikator || []).length;
            });
        });

        const statsEl = document.getElementById('hierarki-stats');
        if (statsEl) {
            statsEl.textContent = kriterias.length + ' Kriteria • ' + totalSub + ' Subkriteria • ' + totalInd + ' Indikator';
        }

        const treeContainer = document.getElementById('hierarki-tree');
        treeContainer.innerHTML = '';

        kriterias.forEach(kriteria => {
            const subkriterias = kriteria.subkriteria || [];

            let subHtml = '';
            if (subkriterias.length === 0) {
                subHtml = '<p class="text-xs text-gray-400 italic py-2 pl-4">Belum ada subkriteria pada kriteria ini.</p>';
            } else {
                subkriterias.forEach(sub => {
                    const indikators = sub.indikator || [];
                    let indHtml = '';
                    if (indikators.length === 0) {
                        indHtml = '<p class="text-xs text-gray-400 italic py-1 pl-4">Belum ada indikator pada subkriteria ini.</p>';
                    } else {
                        indikators.forEach(ind => {
                            let rubrikHtml = '';
                            if (ind.rubriks && Array.isArray(ind.rubriks) && ind.rubriks.length > 0) {
                                rubrikHtml = '<div class="mt-2.5 grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-gray-100">' +
                                    ind.rubriks.map(r => {
                                        const skor = r.skor || 0;
                                        const color = skor >= 4 ? 'bg-emerald-50 text-emerald-800 border-emerald-200' :
                                                      skor == 3 ? 'bg-sky-50 text-sky-800 border-sky-200' :
                                                      skor == 2 ? 'bg-amber-50 text-amber-800 border-amber-200' :
                                                                  'bg-rose-50 text-rose-800 border-rose-200';
                                        return `<div class="p-2 rounded-lg border text-xs ${color}">
                                            <span class="font-bold">Tingkat 7: Skor ${skor}</span> &bull; ${r.deskripsi_rubrik || r.deskripsi_kondisi || '-'}
                                        </div>`;
                                    }).join('') +
                                '</div>';
                            } else {
                                rubrikHtml = `<div class="mt-2 flex items-center justify-between text-xs text-gray-500 pt-1.5 border-t border-gray-100">
                                    <span class="flex items-center gap-1.5 font-medium">
                                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Tingkat 7: 4 Skala Rubrik Penilaian (Skor 1 - 4)
                                    </span>
                                </div>`;
                            }

                            indHtml += `
                                <div class="bg-white rounded-xl p-3.5 border border-gray-200 shadow-2xs hover:border-primary/40 transition">
                                    <div class="flex items-start justify-between gap-2 flex-wrap">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-md font-mono text-xs font-bold bg-sky-100 text-sky-800">${ind.kode || '-'}</span>
                                            <span class="text-xs font-bold text-gray-800">Tingkat 6: Indikator</span>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Bobot: ${ind.bobot !== undefined ? ind.bobot : 1}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-700 mt-1.5 leading-relaxed font-medium">${ind.nama || '-'}</p>
                                    ${ind.target_sumber_data ? `<p class="text-[11px] text-gray-500 mt-1 font-mono">Sumber: ${ind.target_sumber_data}</p>` : ''}
                                    ${rubrikHtml}
                                </div>
                            `;
                        });
                    }

                    subHtml += `
                        <div class="ml-2 sm:ml-4 pl-3 sm:pl-4 border-l-2 border-primary/20 space-y-2 mt-3">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md font-mono text-xs font-bold bg-amber-100 text-amber-800">${sub.kode || '-'}</span>
                                <span class="text-xs font-bold text-gray-800">Tingkat 5: Subkriteria &bull; ${sub.nama || '-'}</span>
                            </div>
                            <div class="space-y-2 mt-2">
                                ${indHtml}
                            </div>
                        </div>
                    `;
                });
            }

            const kriteriaCard = document.createElement('div');
            kriteriaCard.className = 'rounded-2xl border border-gray-200 bg-gray-50/70 p-4 sm:p-5 space-y-3';
            kriteriaCard.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg font-mono text-xs font-bold bg-primary text-white tracking-tight">${kriteria.kode || '-'}</span>
                        <h5 class="text-sm sm:text-base font-extrabold text-gray-900 tracking-tight">Tingkat 4: Kriteria &bull; ${kriteria.nama || '-'}</h5>
                    </div>
                    <span class="text-xs font-semibold text-gray-500 bg-white px-2.5 py-1 rounded-lg border border-gray-200 shadow-2xs">${subkriterias.length} Subkriteria</span>
                </div>
                ${subHtml}
            `;
            treeContainer.appendChild(kriteriaCard);
        });
    }
</script>
@endsection
