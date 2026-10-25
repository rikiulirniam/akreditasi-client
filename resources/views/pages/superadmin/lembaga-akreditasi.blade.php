@extends('layouts.app')

@section('title', 'Master Data Instrumen')

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
            <li class="inline-flex items-center" aria-current="page">
                <span class="font-bold text-gray-800">Master Data Akreditasi</span>
            </li>
        </ol>
    </nav>

    {{-- Bagian Header & Tombol Tambah --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5">
        <div>
            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-additional">
                Master Data Instrumen
            </p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight text-primary">
                Lembaga Akreditasi
            </h2>
            <p class="mt-1 text-xs sm:text-sm text-gray-500 font-medium">
                {{ count($lembagas ?? []) }} data terkait
            </p>
        </div>

        <button 
            type="button"
            onclick="toggleModal('modal-tambah-lembaga')" 
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Lembaga
        </button>
    </div>

    {{-- Daftar Card Lembaga --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($lembagas ?? [] as $lembaga)
            <div 
                onclick="if (!event.target.closest('button, form, a')) { window.location.href = '{{ route('akreditasi.superadmin.instrumen', $lembaga['id']) }}'; }"
                class="group flex min-h-[175px] flex-col justify-between rounded-2xl border border-[#EAEAEA] bg-white p-6 shadow-xs transition-all duration-200 hover:border-primary/50 hover:shadow-md hover:-translate-y-0.5 cursor-pointer relative"
            >
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 pr-2">
                            <h3 class="text-base sm:text-lg font-extrabold text-primary tracking-tight group-hover:text-additional transition-colors line-clamp-1">
                                {{ $lembaga['nama_lembaga'] ?? '-' }}
                            </h3>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                    Lembaga
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    {{ $lembaga['instrumen_count'] ?? 0 }} Instrumen
                                </span>
                            </div>
                        </div>

                        {{-- Action Buttons (Yellow Edit, Red Delete) --}}
                        <div class="flex items-center gap-1 shrink-0" onclick="event.stopPropagation()">
                            <button 
                                type="button" 
                                class="text-[#F59E0B] hover:text-[#D97706] transition p-1.5 rounded-lg hover:bg-amber-50 cursor-pointer"
                                title="Edit Lembaga"
                                onclick="openEditLembagaModal({{ json_encode($lembaga) }})"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>

                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1.5 rounded-lg hover:bg-rose-50 cursor-pointer"
                                title="Hapus Lembaga"
                                data-confirm-delete
                                data-action="{{ route('akreditasi.superadmin.lembaga_akreditasi.destroy', $lembaga['id']) }}"
                                data-title="Hapus Lembaga Akreditasi?"
                                data-message="Apakah Anda yakin ingin menghapus lembaga '{{ $lembaga['nama_lembaga'] ?? '' }}'? Seluruh data instrumen terkait akan ikut terhapus."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <p class="mt-2 text-sm leading-relaxed text-gray-600 line-clamp-2">
                        {{ $lembaga['keterangan'] ?? 'Lembaga Akreditasi' }}
                    </p>

                    @if (!empty($lembaga['versi_instrumen']))
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($lembaga['versi_instrumen'] as $vi)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 group-hover:bg-sky-50 group-hover:text-primary transition-colors">
                                    v{{ $vi['nomor_versi'] }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-400 group-hover:text-primary transition-colors flex items-center gap-1.5">
                        Buka {{ $lembaga['instrumen_count'] ?? 0 }} Instrumen
                    </span>
                    <span 
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 group-hover:bg-primary group-hover:text-white transition-all duration-200 group-hover:translate-x-1 shadow-xs"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center text-sm font-medium text-gray-500">
                Belum ada data lembaga akreditasi dari API. Klik tombol "Tambah Lembaga" untuk menambahkan.
            </div>
        @endforelse
    </div>

</div>

{{-- Modal Tambah Lembaga --}}
<div id="modal-tambah-lembaga" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-xl">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Tambah Lembaga Akreditasi</h3>
                <button type="button" onclick="toggleModal('modal-tambah-lembaga')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('akreditasi.superadmin.lembaga_akreditasi.store') }}" method="POST">
                @csrf
                <div class="p-5 sm:p-6 space-y-4">
                    <h4 class="text-sm font-bold text-gray-900">Identitas Lembaga</h4>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Nama Lembaga <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_lembaga" required placeholder="Contoh: LAM-INFOKOM" class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Keterangan</label>
                        <textarea name="keterangan" rows="3" placeholder="Deskripsi lengkap mengenai lembaga akreditasi..." class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 sm:p-6 bg-gray-50">
                    <button onclick="toggleModal('modal-tambah-lembaga')" type="button" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Batal</button>
                    <button type="submit" class="rounded-2xl bg-primary px-6 py-2.5 text-sm font-bold text-white hover:bg-additional transition cursor-pointer shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Lembaga --}}
<div id="modal-edit-lembaga" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-xl">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Edit Lembaga Akreditasi</h3>
                <button type="button" onclick="toggleModal('modal-edit-lembaga')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="form-edit-lembaga" action="#" method="POST">
                @csrf
                @method('PUT')
                <div class="p-5 sm:p-6 space-y-4">
                    <h4 class="text-sm font-bold text-gray-900">Identitas Lembaga</h4>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Nama Lembaga <span class="text-red-500">*</span></label>
                        <input type="text" id="edit-nama-lembaga" name="nama_lembaga" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Keterangan</label>
                        <textarea id="edit-keterangan-lembaga" name="keterangan" rows="3" class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 sm:p-6 bg-gray-50">
                    <button onclick="toggleModal('modal-edit-lembaga')" type="button" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Batal</button>
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

    function openEditLembagaModal(lembaga) {
        const form = document.getElementById('form-edit-lembaga');
        const namaInput = document.getElementById('edit-nama-lembaga');
        const ketInput = document.getElementById('edit-keterangan-lembaga');

        if (form && lembaga) {
            form.action = "{{ route('akreditasi.superadmin.lembaga_akreditasi.update', ':id') }}".replace(':id', lembaga.id);
            if (namaInput) namaInput.value = lembaga.nama_lembaga || '';
            if (ketInput) ketInput.value = lembaga.keterangan || '';
            toggleModal('modal-edit-lembaga');
        }
    }
</script>
@endsection