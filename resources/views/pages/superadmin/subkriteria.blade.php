@extends('layouts.app')

@section('title', 'Master Data Subkriteria')

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
            <li class="inline-flex items-center" aria-current="page">
                <span class="mx-1 text-gray-400">/</span>
                <span class="font-bold text-gray-800">{{ ($kriteria['kode_kriteria'] ?? 'C1') . ' - ' . ($kriteria['nama_kriteria'] ?? 'Kriteria') }}</span>
            </li>
        </ol>
    </nav>

    {{-- Banner Utama --}}
    <section class="flex flex-col justify-between gap-5 rounded-2xl bg-primary p-6 sm:p-7 shadow-xs sm:flex-row sm:items-center text-white">
        <div class="min-w-0">
            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-secondary">
                DETAIL KRITERIA
            </p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight font-mono">
                {{ $kriteria['kode_kriteria'] ?? 'C1' }}
            </h2>
            @if(!empty($kriteria['nama_kriteria']))
                <p class="mt-2 text-base sm:text-lg font-semibold text-white/95 leading-relaxed">
                    {{ $kriteria['nama_kriteria'] }}
                </p>
            @endif
            <p class="mt-2 text-xs sm:text-sm text-white/75">
                Kelola subkriteria yang menjadi rujukan indikator penilaian.
            </p>
        </div>
    </section>

    {{-- Metadata Info --}}
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-4 rounded-2xl border border-[#EAEAEA] bg-white p-5 sm:p-6 shadow-xs">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">KODE KRITERIA</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900 font-mono">{{ $kriteria['kode_kriteria'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">NAMA KRITERIA</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900">{{ $kriteria['nama_kriteria'] ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">TOTAL SUBKRITERIA</p>
            <p class="mt-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    {{ count($subkriterias ?? []) }} Subkriteria
                </span>
            </p>
        </div>
    </section>

    {{-- Tabel Subkriteria --}}
    <section class="space-y-4">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Daftar Subkriteria</h3>
                <p class="text-xs sm:text-sm text-gray-500">{{ count($subkriterias ?? []) }} data terkait</p>
            </div>
            <button onclick="toggleModal('modal-tambah')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Subkriteria
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-[#EAEAEA] shadow-xs overflow-hidden">
            <x-table>
                <x-slot:header>
                    <x-table.th class="w-44">Kode Subkriteria</x-table.th>
                    <x-table.th>Nama Subkriteria</x-table.th>
                    <x-table.th class="w-36">Indikator</x-table.th>
                    <x-table.th class="w-32 text-right">Aksi</x-table.th>
                </x-slot:header>

                @forelse ($subkriterias ?? [] as $item)
                    <x-table.row 
                        onclick="if (!event.target.closest('button, form, a')) { window.location.href='{{ route('akreditasi.superadmin.indikator', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId, 'kriteriaId' => $kriteriaId, 'subkriteriaId' => $item['id']]) }}'; }" 
                        class="cursor-pointer hover:bg-sky-50/60 transition-colors group"
                    >
                        <x-table.cell>
                            <span class="font-mono font-bold text-sm text-primary tracking-tight">
                                {{ $item['kode_subkriteria'] ?? ($item['kode'] ?? '-') }}
                            </span>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="font-semibold text-sm text-gray-900 group-hover:text-primary transition-colors">
                                {{ $item['nama_subkriteria'] ?? ($item['nama'] ?? '-') }}
                            </span>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="text-sm text-gray-500 font-medium">
                                {{ $item['indikator_count'] ?? ($item['indikators_count'] ?? count($item['indikator'] ?? [])) }} Indikator
                            </span>
                        </x-table.cell>
                        <x-table.cell class="w-32 text-right">
                            <div class="flex items-center justify-end gap-1" onclick="event.stopPropagation()">
                                <!-- Edit Icon (Yellow) -->
                                <button 
                                    type="button" 
                                    class="text-[#F59E0B] hover:text-[#D97706] transition p-1.5 rounded-lg hover:bg-amber-50 cursor-pointer"
                                    title="Edit Subkriteria"
                                    onclick="openEditSubkriteriaModal({{ json_encode($item) }})"
                                >
                                    <x-icon.edit class="w-5 h-5" />
                                </button>

                                <!-- Delete Icon (Red) -->
                                <button 
                                    type="button" 
                                    class="text-[#DC2626] hover:text-[#B91C1C] transition p-1.5 rounded-lg hover:bg-rose-50 cursor-pointer"
                                    title="Hapus Subkriteria"
                                    data-confirm-delete
                                    data-action="{{ route('akreditasi.superadmin.subkriteria.destroy', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId, 'kriteriaId' => $kriteriaId, 'subkriteriaId' => $item['id']]) }}"
                                    data-title="Hapus Subkriteria?"
                                    data-message="Apakah Anda yakin ingin menghapus subkriteria '{{ $item['kode_subkriteria'] ?? '' }} - {{ $item['nama_subkriteria'] ?? '' }}'? Seluruh butir indikator dan rubrik terkait akan ikut terhapus."
                                >
                                    <x-icon.delete class="w-5 h-5" />
                                </button>

                                <!-- Arrow icon -->
                                <a 
                                    href="{{ route('akreditasi.superadmin.indikator', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId, 'kriteriaId' => $kriteriaId, 'subkriteriaId' => $item['id']]) }}"
                                    class="inline-flex p-1.5 text-primary hover:text-additional transition-all"
                                    title="Lihat Indikator"
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
                        <x-table.cell colspan="4" class="py-8 text-center text-sm font-medium text-gray-500">
                            Belum ada subkriteria yang terdaftar. Klik tombol di atas untuk menambahkan.
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table>
        </div>
    </section>

</div>

{{-- Modal Tambah Subkriteria --}}
<div id="modal-tambah" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Tambah Subkriteria Baru</h3>
                <button type="button" onclick="toggleModal('modal-tambah')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('akreditasi.superadmin.subkriteria.store', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId, 'kriteriaId' => $kriteriaId]) }}" method="POST">
                @csrf
                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Kode Subkriteria <span class="text-red-500">*</span></label>
                        <input type="text" name="kode_subkriteria" required placeholder="Contoh: C1.1" class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Nama Subkriteria <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_subkriteria" required placeholder="Contoh: Kejelasan & kerealistisan VMTS" class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
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

{{-- Modal Edit Subkriteria --}}
<div id="modal-edit-subkriteria" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Edit Subkriteria</h3>
                <button type="button" onclick="toggleModal('modal-edit-subkriteria')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="form-edit-subkriteria" action="#" method="POST">
                @csrf
                @method('PUT')
                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Kode Subkriteria <span class="text-red-500">*</span></label>
                        <input type="text" id="edit-kode-subkriteria" name="kode_subkriteria" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Nama Subkriteria <span class="text-red-500">*</span></label>
                        <input type="text" id="edit-nama-subkriteria" name="nama_subkriteria" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 sm:p-6 bg-gray-50">
                    <button onclick="toggleModal('modal-edit-subkriteria')" type="button" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Batal</button>
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

    function openEditSubkriteriaModal(item) {
        const form = document.getElementById('form-edit-subkriteria');
        const kodeInput = document.getElementById('edit-kode-subkriteria');
        const namaInput = document.getElementById('edit-nama-subkriteria');

        if (form && item) {
            form.action = "{{ route('akreditasi.superadmin.subkriteria.update', ['lembagaId' => $lembagaId, 'instrumenId' => $instrumenId, 'kriteriaId' => $kriteriaId, 'subkriteriaId' => ':id']) }}".replace(':id', item.id);
            if (kodeInput) kodeInput.value = item.kode_subkriteria || item.kode || '';
            if (namaInput) namaInput.value = item.nama_subkriteria || item.nama || '';
            toggleModal('modal-edit-subkriteria');
        }
    }
</script>
@endsection