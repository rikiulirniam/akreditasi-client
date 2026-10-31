@extends('layouts.app')

@section('title', 'Master Data Rubrik')

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
                <a href="{{ route('akreditasi.superadmin.instrumen', $lembagaId) }}" class="hover:text-primary transition font-medium">{{ $lembaga['nama_lembaga'] ?? 'Lembaga' }}</a>
            </li>
            <li class="inline-flex items-center">
                <span class="mx-1 text-gray-400">/</span>
                <a href="{{ route('akreditasi.superadmin.kriteria', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId]) }}" class="hover:text-primary transition font-medium">{{ $instrumenTitle }}</a>
            </li>
            <li class="inline-flex items-center">
                <span class="mx-1 text-gray-400">/</span>
                <a href="{{ route('akreditasi.superadmin.subkriteria', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId, 'kriteriaId' => $kriteriaId]) }}" class="hover:text-primary transition font-medium">{{ $kriteria['kode_kriteria'] ?? 'Kriteria' }}</a>
            </li>
            <li class="inline-flex items-center">
                <span class="mx-1 text-gray-400">/</span>
                <a href="{{ route('akreditasi.superadmin.indikator', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId, 'kriteriaId' => $kriteriaId, 'subkriteriaId' => $subkriteriaId]) }}" class="hover:text-primary transition font-medium">{{ $subkriteria['kode_subkriteria'] ?? 'Subkriteria' }}</a>
            </li>
            <li class="inline-flex items-center" aria-current="page">
                <span class="mx-1 text-gray-400">/</span>
                <span class="font-bold text-gray-800">{{ ($indikator['kode_indikator'] ?? 'Indikator') . ' - ' . \Illuminate\Support\Str::limit($indikator['deskripsi_indikator'] ?? '', 35) }}</span>
            </li>
        </ol>
    </nav>

    {{-- Banner Utama --}}
    <section class="flex flex-col justify-between gap-5 rounded-2xl bg-primary p-6 sm:p-7 shadow-xs sm:flex-row sm:items-center text-white">
        <div class="min-w-0">
            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-secondary">
                DETAIL INDIKATOR
            </p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight font-mono">
                {{ $indikator['kode_indikator'] ?? 'Indikator' }}
            </h2>
            @if(!empty($indikator['deskripsi_indikator']))
                <p class="mt-2 text-base sm:text-lg font-semibold text-white/95 leading-relaxed">
                    {{ $indikator['deskripsi_indikator'] }}
                </p>
            @endif
            <p class="mt-2 text-xs sm:text-sm text-white/75">
                Kelola skala skor rubrik penilaian untuk indikator ini.
            </p>
        </div>
    </section>

    {{-- Metadata Info --}}
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-5 rounded-2xl border border-[#EAEAEA] bg-white p-5 sm:p-6 shadow-xs">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">KODE INDIKATOR</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900 font-mono">{{ $indikator['kode_indikator'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">SUBKRITERIA</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900 font-mono">
                {{ ($subkriteria['kode_subkriteria'] ?? '-') . ' - ' . ($subkriteria['nama_subkriteria'] ?? '-') }}
            </p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">TOTAL RUBRIK</p>
            <p class="mt-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    {{ count($rubriks ?? []) }} Skala Terdaftar
                </span>
            </p>
        </div>
        <div class="sm:col-span-3">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">DESKRIPSI INDIKATOR</p>
            <p class="mt-1.5 text-sm font-medium text-gray-800 leading-relaxed">{{ $indikator['deskripsi_indikator'] ?? '-' }}</p>
        </div>
    </section>

    {{-- Tabel Rubrik --}}
    <section class="space-y-4">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Daftar Rubrik Penilaian</h3>
                <p class="text-xs sm:text-sm text-gray-500">{{ count($rubriks ?? []) }} skala penilaian terkait</p>
            </div>
            <button onclick="toggleModal('modal-tambah')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Rubrik
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-[#EAEAEA] shadow-xs overflow-hidden">
            <x-table>
                <x-slot:header>
                    <x-table.th class="w-24 text-center">Skor</x-table.th>
                    <x-table.th>Deskripsi Kondisi / Rubrik Penilaian</x-table.th>
                    <x-table.th class="w-44 text-center">Tingkat Capaian</x-table.th>
                    <x-table.th class="w-24 text-right">Aksi</x-table.th>
                </x-slot:header>

                @forelse ($rubriks ?? [] as $item)
                    @php
                        $skor = (int) ($item['skor'] ?? 0);
                        $badgeClass = match(true) {
                            $skor >= 4 => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            $skor == 3 => 'bg-sky-50 text-sky-700 border-sky-200',
                            $skor == 2 => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-rose-50 text-rose-700 border-rose-200',
                        };
                        $capaianLabel = match(true) {
                            $skor >= 4 => 'Sangat Baik (4)',
                            $skor == 3 => 'Baik (3)',
                            $skor == 2 => 'Cukup (2)',
                            default => 'Kurang (' . $skor . ')',
                        };
                    @endphp
                    <x-table.row class="hover:bg-sky-50/40 transition-colors">
                        <x-table.cell class="text-center">
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full font-bold text-sm bg-primary/10 text-primary">
                                {{ $skor }}
                            </span>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="text-sm font-medium text-gray-800 leading-relaxed block py-1">
                                {{ $item['deskripsi_rubrik'] ?? ($item['deskripsi_kondisi'] ?? ($item['deskripsi'] ?? '-')) }}
                            </span>
                        </x-table.cell>
                        <x-table.cell class="text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                {{ $capaianLabel }}
                            </span>
                        </x-table.cell>
                        <x-table.cell class="w-24 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <!-- Edit Icon (Yellow) -->
                                <button 
                                    type="button" 
                                    class="text-[#F59E0B] hover:text-[#D97706] transition p-1.5 rounded-lg hover:bg-amber-50 cursor-pointer"
                                    title="Edit Rubrik"
                                    onclick="openEditRubrikModal({{ json_encode($item) }})"
                                >
                                    <x-icon.edit class="w-5 h-5" />
                                </button>

                                <!-- Delete Icon (Red) -->
                                <button 
                                    type="button" 
                                    class="text-[#DC2626] hover:text-[#B91C1C] transition p-1.5 rounded-lg hover:bg-rose-50 cursor-pointer"
                                    title="Hapus Rubrik"
                                    data-confirm-delete
                                    data-action="{{ route('akreditasi.superadmin.rubrik.destroy', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId, 'kriteriaId' => $kriteriaId, 'subkriteriaId' => $subkriteriaId, 'indikatorId' => $indikatorId, 'rubrikId' => $item['id']]) }}"
                                    data-title="Hapus Rubrik Penilaian?"
                                    data-message="Apakah Anda yakin ingin menghapus opsi rubrik skor {{ $skor }} ini? Tindakan ini bersifat permanen."
                                >
                                    <x-icon.delete class="w-5 h-5" />
                                </button>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="4" class="py-8 text-center text-sm font-medium text-gray-500">
                            Belum ada opsi rubrik skor untuk indikator ini. Klik tombol di atas untuk menambahkan.
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table>
        </div>
    </section>

</div>

{{-- Modal Tambah Rubrik --}}
<div id="modal-tambah" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Tambah Rubrik Penilaian</h3>
                <button type="button" onclick="toggleModal('modal-tambah')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('akreditasi.superadmin.rubrik.store', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId, 'kriteriaId' => $kriteriaId, 'subkriteriaId' => $subkriteriaId, 'indikatorId' => $indikatorId]) }}" method="POST">
                @csrf
                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Skor Capaian <span class="text-red-500">*</span></label>
                        <select name="skor" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="4">4 - Sangat Baik / Melampaui SN-Dikti</option>
                            <option value="3">3 - Baik / Memenuhi SN-Dikti</option>
                            <option value="2">2 - Cukup / Kurang Memenuhi</option>
                            <option value="1">1 - Kurang / Tidak Memenuhi</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Deskripsi Rubrik / Kondisi Pemenuhan <span class="text-red-500">*</span></label>
                        <textarea name="deskripsi_rubrik" rows="4" required placeholder="Jelaskan kondisi dan tolok ukur yang harus dipenuhi untuk mencapai skor ini..." class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 sm:p-6 bg-gray-50">
                    <button onclick="toggleModal('modal-tambah')" type="button" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Batal</button>
                    <button type="submit" class="rounded-2xl bg-primary px-6 py-2.5 text-sm font-bold text-white hover:bg-additional transition cursor-pointer shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Rubrik --}}
<div id="modal-edit-rubrik" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Edit Rubrik Penilaian</h3>
                <button type="button" onclick="toggleModal('modal-edit-rubrik')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="form-edit-rubrik" action="#" method="POST">
                @csrf
                @method('PUT')
                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Skor Capaian <span class="text-red-500">*</span></label>
                        <select id="edit-skor-rubrik" name="skor" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="4">4 - Sangat Baik / Melampaui SN-Dikti</option>
                            <option value="3">3 - Baik / Memenuhi SN-Dikti</option>
                            <option value="2">2 - Cukup / Kurang Memenuhi</option>
                            <option value="1">1 - Kurang / Tidak Memenuhi</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Deskripsi Rubrik / Kondisi Pemenuhan <span class="text-red-500">*</span></label>
                        <textarea id="edit-deskripsi-rubrik" name="deskripsi_rubrik" rows="4" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 sm:p-6 bg-gray-50">
                    <button onclick="toggleModal('modal-edit-rubrik')" type="button" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Batal</button>
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

    function openEditRubrikModal(item) {
        const form = document.getElementById('form-edit-rubrik');
        const skorSelect = document.getElementById('edit-skor-rubrik');
        const deskripsiInput = document.getElementById('edit-deskripsi-rubrik');

        if (form && item) {
            form.action = "{{ route('akreditasi.superadmin.rubrik.update', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId, 'kriteriaId' => $kriteriaId, 'subkriteriaId' => $subkriteriaId, 'indikatorId' => $indikatorId, 'rubrikId' => ':id']) }}".replace(':id', item.id);
            if (skorSelect) skorSelect.value = item.skor || '4';
            if (deskripsiInput) deskripsiInput.value = item.deskripsi_rubrik || (item.deskripsi_kondisi || (item.deskripsi || ''));
            toggleModal('modal-edit-rubrik');
        }
    }
</script>
@endsection