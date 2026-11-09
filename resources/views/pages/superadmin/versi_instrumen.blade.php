@extends('layouts.app')

@section('title', 'Detail Lembaga Akreditasi - Versi Instrumen')

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
                <span class="font-bold text-gray-800">{{ $lembaga['nama_lembaga'] ?? 'Detail Lembaga' }}</span>
            </li>
        </ol>
    </nav>

    {{-- ========================================== --}}
    {{-- BANNER DETAIL LEMBAGA AKREDITASI --}}
    {{-- ========================================== --}}
    <section class="flex flex-col justify-between gap-5 rounded-2xl bg-primary p-6 sm:p-7 shadow-xs sm:flex-row sm:items-center text-white">
        <div class="min-w-0">
            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-secondary">
                DETAIL LEMBAGA AKREDITASI
            </p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">
                {{ $lembaga['nama_lembaga'] ?? 'LAM INFOKOM' }}
            </h2>
            <p class="mt-2 text-sm text-white/80">
                {{ $lembaga['keterangan'] ?? 'Lembaga akreditasi mandiri.' }}
            </p>
        </div>
        
        {{-- Tombol Kembali ke Daftar Lembaga --}}
        <div class="flex items-center gap-3 shrink-0">
            <a 
                href="{{ route('akreditasi.superadmin.lembaga_akreditasi') }}" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/15 hover:bg-white/25 text-white text-xs sm:text-sm font-bold transition cursor-pointer shadow-xs"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Lembaga
            </a>
        </div>
    </section>

    {{-- Metadata Info Lembaga --}}
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
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-800 border border-sky-200">
                    {{ count($versis ?? []) }} Versi Terdaftar
                </span>
            </p>
        </div>
    </section>

    {{-- ========================================== --}}
    {{-- BAGIAN DAFTAR VERSI INSTRUMEN --}}
    {{-- ========================================== --}}
    <section class="space-y-4">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Daftar Versi Instrumen</h3>
                <p class="text-xs sm:text-sm text-gray-500">{{ count($versis ?? []) }} versi instrumen terdaftar untuk lembaga ini</p>
            </div>
            <button 
                type="button"
                onclick="toggleModal('modal-tambah-versi')" 
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Versi Instrumen
            </button>
        </div>

        {{-- Grid Kartu Versi Instrumen --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($versis ?? [] as $v)
                <div 
                    onclick="if (!event.target.closest('button, form, a')) { window.location.href = '{{ route('akreditasi.superadmin.instrumen', ['lembagaId' => $lembaga['id'], 'versiId' => $v['id']]) }}'; }"
                    class="group flex min-h-[175px] flex-col justify-between rounded-2xl border border-[#EAEAEA] bg-white p-6 shadow-xs transition-all duration-200 hover:border-primary/50 hover:shadow-md hover:-translate-y-0.5 cursor-pointer relative"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 pr-2">
                                <h4 class="text-base sm:text-lg font-extrabold text-primary tracking-tight group-hover:text-additional transition-colors line-clamp-1">
                                    Versi {{ $v['nomor_versi'] ?? '-' }} <span class="text-xs font-semibold text-gray-500">v{{ $v['nomor_versi'] ?? '-' }}</span>
                                </h4>
                                <div class="flex items-center gap-1.5 flex-wrap mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ !empty($v['status_aktif']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-600' }}">
                                        {{ !empty($v['status_aktif']) ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-sky-50 text-sky-700 border border-sky-200/60">
                                        {{ $v['instrumen_count'] ?? 0 }} Instrumen
                                    </span>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex items-center gap-1 shrink-0" onclick="event.stopPropagation()">
                                <button 
                                    type="button" 
                                    class="text-[#F59E0B] hover:text-[#D97706] transition p-1.5 rounded-lg hover:bg-amber-50 cursor-pointer"
                                    title="Edit Versi"
                                    onclick="openEditVersiModal({{ json_encode($v) }})"
                                >
                                    <x-icon.edit class="w-5 h-5" />
                                </button>

                                <button 
                                    type="button" 
                                    class="text-[#DC2626] hover:text-[#B91C1C] transition p-1.5 rounded-lg hover:bg-rose-50 cursor-pointer"
                                    title="Hapus Versi"
                                    data-confirm-delete
                                    data-action="{{ route('akreditasi.superadmin.versi_instrumen.destroy', $v['id']) }}"
                                    data-title="Hapus Versi Instrumen?"
                                    data-message="Apakah Anda yakin ingin menghapus versi {{ $v['nomor_versi'] ?? '' }}? Seluruh instrumen terkait akan ikut terhapus."
                                >
                                    <x-icon.delete class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 text-xs text-gray-500 font-medium">
                            @if (!empty($v['tanggal_berlaku']))
                                <span>Berlaku sejak: {{ date('d M Y', strtotime($v['tanggal_berlaku'])) }}</span>
                            @else
                                <span class="text-gray-400">Tanggal berlaku tidak ditentukan</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-400 group-hover:text-primary transition-colors flex items-center gap-1.5">
                            Buka Daftar Instrumen
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
                    Belum ada versi instrumen yang terdaftar pada lembaga ini. Klik tombol "+ Tambah Versi Instrumen" di atas untuk menambahkan versi baru.
                </div>
            @endforelse
        </div>
    </section>

</div>

{{-- ========================================== --}}
{{-- KUMPULAN MODAL --}}
{{-- ========================================== --}}

{{-- 1. Modal Tambah Versi Instrumen --}}
<div id="modal-tambah-versi" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Tambah Versi Instrumen</h3>
                <button type="button" onclick="toggleModal('modal-tambah-versi')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('akreditasi.superadmin.versi_instrumen.store', $lembaga['id']) }}" method="POST">
                @csrf
                <input type="hidden" name="id_lembaga" value="{{ $lembaga['id'] }}">
                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Nomor Versi <span class="text-red-500">*</span></label>
                        <input type="text" name="nomor_versi" required placeholder="Contoh: 3.0" class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Tanggal Berlaku</label>
                        <input type="date" name="tanggal_berlaku" class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Keterangan</label>
                        <textarea name="keterangan" rows="2" placeholder="Keterangan singkat..." class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"></textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Status <span class="text-red-500">*</span></label>
                        <select name="status_aktif" class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 sm:p-6 bg-gray-50">
                    <button type="button" onclick="toggleModal('modal-tambah-versi')" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Batal</button>
                    <button type="submit" class="rounded-2xl bg-primary px-6 py-2.5 text-sm font-bold text-white hover:bg-additional transition cursor-pointer shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. Modal Edit Versi Instrumen --}}
<div id="modal-edit-versi" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Edit Versi Instrumen</h3>
                <button type="button" onclick="toggleModal('modal-edit-versi')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="form-edit-versi" action="#" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id_lembaga" value="{{ $lembaga['id'] }}">
                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Nomor Versi <span class="text-red-500">*</span></label>
                        <input type="text" id="edit-nomor-versi" name="nomor_versi" required class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Tanggal Berlaku</label>
                        <input type="date" id="edit-tanggal-berlaku" name="tanggal_berlaku" class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Keterangan</label>
                        <textarea id="edit-keterangan-versi" name="keterangan" rows="2" placeholder="Keterangan singkat..." class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"></textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Status <span class="text-red-500">*</span></label>
                        <select id="edit-status-aktif" name="status_aktif" class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 sm:p-6 bg-gray-50">
                    <button type="button" onclick="toggleModal('modal-edit-versi')" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Batal</button>
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

    function openEditVersiModal(versi) {
        const form = document.getElementById('form-edit-versi');
        const nomorInput = document.getElementById('edit-nomor-versi');
        const tanggalInput = document.getElementById('edit-tanggal-berlaku');
        const keteranganInput = document.getElementById('edit-keterangan-versi');
        const statusSelect = document.getElementById('edit-status-aktif');

        if (form && versi) {
            form.action = "{{ route('akreditasi.superadmin.versi_instrumen.update', ':id') }}".replace(':id', versi.id);
            if (nomorInput) nomorInput.value = versi.nomor_versi || '';
            if (tanggalInput) {
                if (versi.tanggal_berlaku) {
                    tanggalInput.value = versi.tanggal_berlaku.split('T')[0];
                } else {
                    tanggalInput.value = '';
                }
            }
            if (keteranganInput) {
                keteranganInput.value = versi.keterangan || '';
            }
            if (statusSelect) {
                statusSelect.value = (versi.status_aktif == 1 || versi.status_aktif === true) ? '1' : '0';
            }
            toggleModal('modal-edit-versi');
        }
    }
</script>
@endsection
