@extends('layouts.app')

@section('title', 'Master Data Rubrik')

@section('content')
<div class="space-y-6">

    {{-- Breadcrumbs --}}
    <nav class="flex text-xs sm:text-sm font-medium text-gray-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 sm:space-x-2">
            <li class="inline-flex items-center">
                <span class="text-gray-400">Master Data Akreditasi</span>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <a href="{{ route('akreditasi.superadmin.lembaga_akreditasi') }}" class="hover:text-primary transition font-medium">Lembaga Akreditasi</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <a href="{{ route('akreditasi.superadmin.instrumen') }}" class="hover:text-primary transition font-medium">Instrumen</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <a href="{{ route('akreditasi.superadmin.kriteria') }}" class="hover:text-primary transition font-medium">Kriteria</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <a href="{{ route('akreditasi.superadmin.subkriteria') }}" class="hover:text-primary transition font-medium">Subkriteria</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <a href="{{ route('akreditasi.superadmin.indikator') }}" class="hover:text-primary transition font-medium">Indikator</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <span class="font-bold text-gray-800">Rubrik</span>
                </div>
            </li>
        </ol>
    </nav>

    {{-- Banner Utama --}}
    <section class="flex flex-col justify-between gap-5 rounded-2xl bg-primary p-6 sm:p-7 shadow-xs sm:flex-row sm:items-center text-white">
        <div class="min-w-0">
            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-secondary">
                DETAIL INDIKATOR
            </p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">
                C1.1.1 - Ketersediaan Dokumen Formal
            </h2>
            <p class="mt-2 text-sm text-white/80">
                Kelola skala skor rubrik penilaian untuk indikator ini.
            </p>
        </div>
        
        <div class="flex shrink-0 items-center gap-3">
            <button onclick="toggleModal('modal-edit')" class="rounded-2xl bg-secondary px-6 py-2.5 text-sm font-bold text-primary transition hover:brightness-95 shadow-sm cursor-pointer">
                Edit
            </button>
            <button onclick="toggleModal('modal-hapus')" class="rounded-2xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-rose-700 shadow-sm cursor-pointer">
                Hapus
            </button>
        </div>
    </section>

    {{-- Metadata Info --}}
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-5 rounded-2xl border border-[#EAEAEA] bg-white p-5 sm:p-6 shadow-xs">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">SUBKRITERIA</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900 font-mono">C1.1</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">STANDAR MUTU</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900 font-mono">STD-PND-01</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">KODE INDIKATOR</p>
            <p class="mt-1.5 text-base font-semibold text-gray-900 font-mono">C1.1.1</p>
        </div>
        <div class="sm:col-span-2">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">DESKRIPSI INDIKATOR</p>
            <p class="mt-1.5 text-sm font-semibold text-gray-900 leading-relaxed">Ketersediaan dokumen formal — kejelasan & kerealistisan VMTS</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">LINK DOKUMEN</p>
            <p class="mt-1.5 text-sm font-medium text-gray-500 leading-relaxed">-</p>
        </div>
    </section>

    {{-- Tabel Rubrik --}}
    <section class="space-y-4">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Daftar Rubrik Penilaian</h3>
                <p class="text-xs sm:text-sm text-gray-500">5 skala penilaian terkait</p>
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
                    <x-table.th class="w-24">Skor</x-table.th>
                    <x-table.th>Deskripsi Rubrik</x-table.th>
                    <x-table.th class="w-36">Nilai Bobot</x-table.th>
                    <x-table.th class="w-24 text-right">Aksi</x-table.th>
                </x-slot:header>

                @php
                    $rubrik = [
                        [
                            'skor' => '4',
                            'deskripsi' => 'Sangat baik, seluruh kriteria terpenuhi dan terbukti konsisten',
                            'bobot' => '4'
                        ],
                        [
                            'skor' => '3',
                            'deskripsi' => 'Baik, sebagian besar kriteria terpenuhi dengan bukti lengkap',
                            'bobot' => '3'
                        ],
                        [
                            'skor' => '2',
                            'deskripsi' => 'Cukup, sebagian terlaksana namun bukti belum lengkap',
                            'bobot' => '2'
                        ],
                        [
                            'skor' => '1',
                            'deskripsi' => 'Kurang, hanya sebagian kecil terpenuhi',
                            'bobot' => '1'
                        ],
                        [
                            'skor' => '0',
                            'deskripsi' => 'Tidak ada bukti pendukung yang relevan',
                            'bobot' => '0'
                        ],
                    ];
                @endphp

                @foreach ($rubrik as $item)
                    <x-table.row>
                        <x-table.cell>
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-primary/10 text-primary font-bold text-sm">
                                {{ $item['skor'] }}
                            </span>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="font-medium text-sm text-gray-900 leading-relaxed">
                                {{ $item['deskripsi'] }}
                            </span>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="font-semibold text-sm text-gray-800">
                                {{ $item['bobot'] }}
                            </span>
                        </x-table.cell>
                        <x-table.cell class="w-24 text-right">
                            <button onclick="window.location.href='{{ route('akreditasi.superadmin.bobot') }}'" class="p-1.5 text-primary hover:text-additional transition cursor-pointer" title="Lihat Bobot">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>
        </div>
    </section>

</div>

{{-- Modal Tambah Rubrik --}}
<div id="modal-tambah" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Tambah Rubrik Baru</h3>
                <button type="button" onclick="toggleModal('modal-tambah')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 sm:p-6 space-y-4">
                <div>
                    <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Skor (0 - 4) <span class="text-red-500">*</span></label>
                    <select class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="4">4 - Sangat Baik</option>
                        <option value="3">3 - Baik</option>
                        <option value="2">2 - Cukup</option>
                        <option value="1">1 - Kurang</option>
                        <option value="0">0 - Tidak Ada Bukti</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Nilai Bobot <span class="text-red-500">*</span></label>
                    <input type="number" step="0.1" placeholder="Contoh: 2.0" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                </div>
                <div>
                    <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Deskripsi Rubrik <span class="text-red-500">*</span></label>
                    <textarea rows="3" placeholder="Masukkan deskripsi capaian untuk skor ini" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"></textarea>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 sm:p-6 bg-gray-50">
                <button onclick="toggleModal('modal-tambah')" type="button" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Batal</button>
                <button onclick="toggleModal('modal-tambah')" type="button" class="rounded-2xl bg-primary px-6 py-2.5 text-sm font-bold text-white hover:bg-additional transition cursor-pointer shadow-sm">Simpan</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modal-edit" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Edit Indikator</h3>
                <button type="button" onclick="toggleModal('modal-edit')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 sm:p-6 space-y-4">
                <div>
                    <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Kode Indikator</label>
                    <input type="text" value="C1.1.1" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                </div>
                <div>
                    <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Deskripsi</label>
                    <textarea rows="3" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">Ketersediaan dokumen formal — kejelasan & kerealistisan VMTS</textarea>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 sm:p-6 bg-gray-50">
                <button onclick="toggleModal('modal-edit')" type="button" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Batal</button>
                <button onclick="toggleModal('modal-edit')" type="button" class="rounded-2xl bg-primary px-6 py-2.5 text-sm font-bold text-white hover:bg-additional transition cursor-pointer shadow-sm">Simpan</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Hapus --}}
<div id="modal-hapus" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-md">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden p-6 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 mb-4">
                <svg class="h-8 w-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 tracking-tight">Hapus Indikator?</h3>
            <p class="mt-2 text-sm text-gray-600">Seluruh rubrik dan bobot di bawah indikator ini akan ikut terhapus.</p>
            <div class="mt-6 flex justify-center gap-3">
                <button onclick="toggleModal('modal-hapus')" type="button" class="rounded-xl bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200 transition cursor-pointer">Batal</button>
                <button onclick="toggleModal('modal-hapus')" type="button" class="rounded-2xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-rose-700 transition cursor-pointer shadow-sm">Hapus</button>
            </div>
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
</script>
@endsection