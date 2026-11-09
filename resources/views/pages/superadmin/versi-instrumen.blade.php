@extends('layouts.app')

@section('title', 'Master Data Versi Instrumen')

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
            <li class="inline-flex items-center" aria-current="page">
                <span class="mx-1 text-gray-400">/</span>
                <span class="font-bold text-gray-800">{{ $lembaga['nama_lembaga'] ?? 'Lembaga' }}</span>
                <span class="mx-1 text-gray-400">/</span>
                <span class="font-semibold text-gray-600">Versi Instrumen</span>
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
                {{ $lembaga['keterangan'] ?? 'Daftar versi instrumen akreditasi pada lembaga ini.' }}
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
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">TOTAL VERSI INSTRUMEN</p>
            <p class="mt-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    {{ count($versis ?? []) }} Versi Terdaftar
                </span>
            </p>
        </div>
    </section>

    {{-- Daftar Versi Instrumen --}}
    <section class="space-y-4">
        {{-- Header Bagian --}}
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Daftar Versi Instrumen</h3>
                <p class="text-xs sm:text-sm text-gray-500">Pilih salah satu versi instrumen untuk melihat daftar instrumen akreditasi.</p>
            </div>
            <button
                type="button"
                onclick="toggleModal('modal-tambah-versi')"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Versi
            </button>
        </div>

        {{-- Card Grid Versi Instrumen --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($versis ?? [] as $item)
                @php
                    $isAktif = ! empty($item['status_aktif']);
                    $versiNomor = $item['nomor_versi'] ?? '1.0';
                    $versiId = $item['id'] ?? 1;
                @endphp
                <div
                    onclick="if (!event.target.closest('button, form, a')) { window.location.href = '{{ route('akreditasi.superadmin.instrumen', ['lembagaId' => $lembagaId, 'versiId' => $versiId]) }}'; }"
                    class="group flex min-h-[175px] flex-col justify-between rounded-2xl border border-[#EAEAEA] bg-white p-6 shadow-xs transition-all duration-200 hover:border-primary/50 hover:shadow-md hover:-translate-y-0.5 cursor-pointer relative"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 pr-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-xl font-black text-primary tracking-tight group-hover:text-additional transition-colors">
                                        v{{ $versiNomor }}
                                    </h3>
                                    @if ($isAktif)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                            Non-Aktif
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 flex-wrap mt-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                        Versi Instrumen
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-sky-50 text-sky-700 border border-sky-200/60">
                                        {{ $item['instrumen_count'] ?? 0 }} Instrumen
                                    </span>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex items-center gap-1 shrink-0" onclick="event.stopPropagation()">
                                <button
                                    type="button"
                                    class="text-[#F59E0B] hover:text-[#D97706] transition p-1.5 rounded-lg hover:bg-amber-50 cursor-pointer"
                                    title="Edit Versi"
                                    onclick="openEditVersiModal({{ json_encode($item) }})"
                                >
                                    <x-icon.edit class="w-5 h-5" />
                                </button>

                                <button
                                    type="button"
                                    class="text-[#DC2626] hover:text-[#B91C1C] transition p-1.5 rounded-lg hover:bg-rose-50 cursor-pointer"
                                    title="Hapus Versi"
                                    data-confirm-delete
                                    data-action="{{ route('akreditasi.superadmin.versi_instrumen.destroy', ['lembagaId' => $lembagaId, 'versiId' => $versiId]) }}"
                                    data-title="Hapus Versi Instrumen?"
                                    data-message="Apakah Anda yakin ingin menghapus versi 'v{{ $versiNomor }}'? Seluruh data instrumen terkait versi ini akan ikut terhapus."
                                >
                                    <x-icon.delete class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <p class="mt-3 text-xs leading-relaxed text-gray-500 line-clamp-2">
                            {{ $item['keterangan'] ?? 'Standar versi instrumen akreditasi program studi.' }}
                        </p>
                    </div>

                    <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-400 group-hover:text-primary transition-colors flex items-center gap-1.5">
                            Buka {{ $item['instrumen_count'] ?? 0 }} Instrumen
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
                    Belum ada data versi instrumen untuk lembaga ini. Klik tombol "Tambah Versi" untuk menambahkan versi instrumen pertama.
                </div>
            @endforelse
        </div>
    </section>

    {{-- MODAL TAMBAH VERSI --}}
    <div id="modal-tambah-versi" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3 border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">Tambah Versi Instrumen</h3>
                <button type="button" onclick="toggleModal('modal-tambah-versi')" class="text-gray-400 hover:text-gray-500 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('akreditasi.superadmin.versi_instrumen.store', $lembagaId) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nomor Versi <span class="text-rose-500">*</span></label>
                        <input type="text" name="nomor_versi" placeholder="Contoh: 2.1 atau 3.0" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Keterangan / Catatan</label>
                        <textarea name="keterangan" rows="3" placeholder="Deskripsi atau catatan perubahan pada versi instrumen ini..." class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"></textarea>
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2 cursor-pointer pt-1">
                            <input type="checkbox" name="status_aktif" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4">
                            <span class="text-xs font-bold text-gray-700">Jadikan Versi Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" onclick="toggleModal('modal-tambah-versi')" class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary text-xs font-bold text-white hover:bg-additional transition cursor-pointer">Simpan Versi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT VERSI --}}
    <div id="modal-edit-versi" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3 border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">Edit Versi Instrumen</h3>
                <button type="button" onclick="toggleModal('modal-edit-versi')" class="text-gray-400 hover:text-gray-500 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form id="form-edit-versi" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nomor Versi <span class="text-rose-500">*</span></label>
                        <input type="text" id="edit-nomor-versi" name="nomor_versi" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Keterangan / Catatan</label>
                        <textarea id="edit-keterangan-versi" name="keterangan" rows="3" class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"></textarea>
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2 cursor-pointer pt-1">
                            <input type="checkbox" id="edit-status-aktif-versi" name="status_aktif" value="1" class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4">
                            <span class="text-xs font-bold text-gray-700">Versi Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" onclick="toggleModal('modal-edit-versi')" class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary text-xs font-bold text-white hover:bg-additional transition cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }

    function openEditVersiModal(item) {
        const form = document.getElementById('form-edit-versi');
        const nomorInput = document.getElementById('edit-nomor-versi');
        const keteranganInput = document.getElementById('edit-keterangan-versi');
        const aktifCheckbox = document.getElementById('edit-status-aktif-versi');

        if (form && item) {
            form.action = "{{ route('akreditasi.superadmin.versi_instrumen.update', ['lembagaId' => $lembagaId, 'versiId' => ':id']) }}".replace(':id', item.id);
            if (nomorInput) nomorInput.value = item.nomor_versi || '';
            if (keteranganInput) keteranganInput.value = item.keterangan || '';
            if (aktifCheckbox) aktifCheckbox.checked = !!item.status_aktif;
            toggleModal('modal-edit-versi');
        }
    }
</script>
@endsection

