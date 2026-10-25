@extends('layouts.app')

@section('title', 'Master Data Instrumen - ' . ($lembaga['nama_lembaga'] ?? 'Lembaga'))

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
    <nav class="flex text-xs sm:text-sm font-medium text-gray-500 overflow-x-auto py-1" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 sm:space-x-2 whitespace-nowrap">
            <li class="inline-flex items-center">
                <a href="{{ route('akreditasi.superadmin.lembaga_akreditasi') }}" class="hover:text-primary transition font-medium">Master Data Akreditasi</a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <span class="font-bold text-gray-800">{{ $lembaga['nama_lembaga'] ?? 'Lembaga Akreditasi' }}</span>
                </div>
            </li>
        </ol>
    </nav>

    {{-- Banner Utama --}}
    <section class="flex flex-col justify-between gap-5 rounded-2xl bg-primary p-6 sm:p-7 shadow-xs sm:flex-row sm:items-center text-white">
        <div class="min-w-0">
            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-secondary">
                DETAIL LEMBAGA AKREDITASI
            </p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">
                {{ $lembaga['nama_lembaga'] ?? 'LAM INFOKOM' }}
            </h2>
            <p class="mt-2 text-sm text-white/80">
                {{ $lembaga['keterangan'] ?? 'Daftar instrumen akreditasi pada lembaga ini.' }}
            </p>
        </div>
    </section>

    {{-- Metadata Info --}}
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-4 rounded-2xl border border-[#EAEAEA] bg-white p-5 sm:p-6 shadow-xs">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">LEMBAGA</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900">{{ $lembaga['nama_lembaga'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">KETERANGAN</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900">{{ $lembaga['keterangan'] ?? '-' }}</p>
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
                <p class="text-xs sm:text-sm text-gray-500">{{ count($instrumens ?? []) }} data terkait</p>
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
                            Belum ada instrumen yang terdaftar pada lembaga ini. Tambahkan instrumen melalui tombol di atas.
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
            <form action="{{ route('akreditasi.superadmin.instrumen.store', $lembagaId) }}" method="POST">
                @csrf
                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Versi Instrumen <span class="text-red-500">*</span></label>
                        <select name="id_versi_instrumen" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                            @forelse ($versis ?? [] as $versi)
                                <option value="{{ $versi['id'] }}">v{{ $versi['nomor_versi'] }} {{ !empty($versi['status_aktif']) ? '(Aktif)' : '' }}</option>
                            @empty
                                <option value="1">Versi Default (v2.1)</option>
                            @endforelse
                        </select>
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
                            @forelse ($versis ?? [] as $versi)
                                <option value="{{ $versi['id'] }}">v{{ $versi['nomor_versi'] }} {{ !empty($versi['status_aktif']) ? '(Aktif)' : '' }}</option>
                            @empty
                                <option value="1">Versi Default (v2.1)</option>
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
            if (versiSelect) versiSelect.value = item.id_versi_instrumen || (item.versi_instrumen ? item.versi_instrumen.id : '1');
            if (jenisSelect) jenisSelect.value = item.id_jenis_instrumen || '1';
            if (jenjangSelect) jenjangSelect.value = item.id_jenjang || '1';
            toggleModal('modal-edit-instrumen');
        }
    }
</script>
@endsection