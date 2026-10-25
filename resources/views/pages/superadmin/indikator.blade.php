@extends('layouts.app')

@section('title', 'Master LAM INFOKOM - Indikator')

@section('content')
<div class="space-y-6">

    {{-- Breadcrumbs --}}
    <nav class="flex text-[11px] font-medium text-gray-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2 overflow-x-auto whitespace-nowrap pb-2 md:pb-0">
            <li class="inline-flex items-center"><span class="text-gray-400">Master Data Akreditasi</span></li>
            <li>
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <a href="{{ route('akreditasi.superadmin.lembaga_akreditasi') }}" class="hover:text-[#005A9C] transition">Lembaga Akreditasi: LAM-INFOKOM</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <a href="{{ route('akreditasi.superadmin.instrumen') }}" class="hover:text-[#005A9C] transition">Versi Instrumen: 2.1</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <a href="{{ route('akreditasi.superadmin.kriteria') }}" class="hover:text-[#005A9C] transition">Instrumen : INS-001</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <a href="{{ route('akreditasi.superadmin.subkriteria') }}" class="hover:text-[#005A9C] transition">Kriteria: C1</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <span class="font-bold text-gray-800">Subkriteria: C1.1</span>
                </div>
            </li>
        </ol>
    </nav>

    {{-- Banner Utama --}}
    <section class="flex flex-col justify-between gap-4 rounded-xl bg-[#06284F] p-6 shadow-sm sm:flex-row sm:items-start">
        <div class="text-white max-w-2xl">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-300">DETAIL SUBKRITERIA</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">C1.1</h2>
            <p class="mt-2 text-xs text-gray-300/80">Turunan kriteria sebagai kelompok indikator.</p>
        </div>
        
        <div class="flex shrink-0 items-center gap-3 mt-2 sm:mt-0">
            <button onclick="toggleModal('modal-edit')" class="rounded-lg bg-[#FFD400] px-6 py-2 text-sm font-bold text-[#06284F] transition hover:bg-yellow-500 shadow-sm">
                Edit
            </button>
            <button onclick="toggleModal('modal-hapus')" class="rounded-lg bg-[#D32F2F] px-5 py-2 text-sm font-bold text-white transition hover:bg-red-700 shadow-sm">
                Hapus
            </button>
        </div>
    </section>

    {{-- Metadata Info --}}
    <section class="grid grid-cols-1 gap-6 px-2 py-2 sm:grid-cols-3">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">KRITERIA</p>
            <p class="mt-1 text-xs font-medium text-gray-900">C1</p>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">KODE SUBKRITERIA</p>
            <p class="mt-1 text-xs font-medium text-gray-900">C1.1</p>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">NAMA SUBKRITERIA</p>
            <p class="mt-1 text-xs font-medium text-gray-900">Kejelasan & kerealistisan VMTS</p>
        </div>
        <div class="sm:col-span-3">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">LINK</p>
            <p class="mt-1 text-xs font-medium text-gray-900">-</p>
        </div>
    </section>

    {{-- Tabel Indikator --}}
    <section class="mt-4">
        <div class="mb-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Indikator</h3>
                <p class="mt-0.5 text-xs text-gray-500">5 data terkait</p>
            </div>
            <button onclick="toggleModal('modal-tambah')" class="inline-flex h-9 items-center justify-center rounded-lg bg-[#005A9C] px-5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#06284F]">
                + Tambah Indikator
            </button>
        </div>

        <div class="overflow-x-auto border-t border-gray-200">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="text-[11px] font-medium text-gray-500">
                    <tr>
                        <th scope="col" class="px-4 py-3 pb-4">Standar Mutu</th>
                        <th scope="col" class="px-4 py-3 pb-4">Kode Indikator</th>
                        <th scope="col" class="px-4 py-3 pb-4">Deskripsi Indikator</th>
                        <th scope="col" class="px-4 py-3 pb-4 w-1/3">Parameter Penilaian</th>
                        <th scope="col" class="px-4 py-3 pb-4">Link</th>
                        <th scope="col" class="px-4 py-3 pb-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    
                    @php
                        $indikator = array_fill(0, 2, [
                            'standar' => 'STD-PND-01',
                            'kode' => 'C1.1.1',
                            'deskripsi' => 'Ketersediaan dokumen formal — kejelasan & kerealistisan vmts',
                            'parameter' => 'Kelengkapan, konsistensi, dan ketercapaian ketersediaan dokumen formal — kejelasan & kerealistisan vmts.',
                            'link' => '-'
                        ]);
                    @endphp

                    @foreach ($indikator as $item)
                    <tr class="transition hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-4 text-xs font-medium text-gray-800">{{ $item['standar'] }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-xs text-gray-800">{{ $item['kode'] }}</td>
                        <td class="px-4 py-4 text-xs text-gray-800">{{ $item['deskripsi'] }}</td>
                        <td class="px-4 py-4 text-xs text-gray-800 leading-relaxed">{{ $item['parameter'] }}</td>
                        <td class="px-4 py-4 text-xs text-gray-800">{{ $item['link'] }}</td>
                        <td class="px-4 py-4 text-right">
                            <button onclick="window.location.href='{{ route('akreditasi.superadmin.rubrik') }}'" class="text-[#005A9C] hover:text-[#06284F] transition">                                
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </section>

    {{-- ========================================== --}}
    {{-- KUMPULAN MODAL & SCRIPT --}}
    {{-- ========================================== --}}

{{-- Modal Tambah Indikator --}}
    <div id="modal-tambah" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50">
        {{-- max-h-[90vh] dan overflow-y-auto biar formnya bisa di-scroll kalau layarnya kekecilan --}}
        <div class="relative w-full max-w-2xl p-4 max-h-[90vh] overflow-y-auto">
            <div class="relative rounded-2xl bg-white shadow-lg">
                <!-- Header -->
                <div class="sticky top-0 z-10 flex items-center justify-between rounded-t-2xl border-b border-gray-200 bg-white p-4 md:p-5">
                    <h3 class="text-lg font-bold text-[#06284F]">Tambah Indikator</h3>
                    <button type="button" onclick="toggleModal('modal-tambah')" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg p-1.5 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
                <!-- Body (Form) -->
                <div class="p-4 md:p-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Standar Mutu <span class="text-red-500">*</span></label>
                            <input type="text" placeholder="Contoh: STD-PND-01" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Kode Indikator <span class="text-red-500">*</span></label>
                            <input type="text" placeholder="Contoh: C1.1.1" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-xs font-bold text-gray-700">Deskripsi Indikator <span class="text-red-500">*</span></label>
                            <textarea rows="3" placeholder="Masukkan deskripsi indikator" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none"></textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-xs font-bold text-gray-700">Parameter Penilaian <span class="text-red-500">*</span></label>
                            <textarea rows="3" placeholder="Masukkan parameter penilaian" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none"></textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-xs font-bold text-gray-700">Link Referensi</label>
                            <input type="url" placeholder="Masukkan URL referensi jika ada" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="sticky bottom-0 z-10 flex items-center justify-end rounded-b-2xl border-t border-gray-200 bg-white p-4 md:p-5 gap-3">
                    <button onclick="toggleModal('modal-tambah')" type="button" class="rounded-lg bg-gray-200 px-5 py-2 text-xs font-bold text-gray-700 hover:bg-gray-300 transition">Batal</button>
                    <button onclick="toggleModal('modal-tambah')" type="button" class="rounded-lg bg-[#005A9C] px-5 py-2 text-xs font-bold text-white hover:bg-[#06284F] transition">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Edit (Banner) --}}
    <div id="modal-edit" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50">
        <div class="relative w-full max-w-lg p-4">
            <div class="relative rounded-2xl bg-white shadow-lg">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 p-4 md:p-5">
                    <h3 class="text-lg font-bold text-[#06284F]">Edit Subkriteria</h3>
                    <button type="button" onclick="toggleModal('modal-edit')" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg p-1.5 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
                <!-- Body (Form) -->
                <div class="p-4 md:p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Kriteria Utama</label>
                            <input type="text" value="C1" disabled class="block w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-xs text-gray-500 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Kode Subkriteria <span class="text-red-500">*</span></label>
                            <input type="text" value="C1.1" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                        </div>
                        <div class="col-span-2">
                            <label class="mb-2 block text-xs font-bold text-gray-700">Nama Subkriteria <span class="text-red-500">*</span></label>
                            <input type="text" value="Kejelasan & kerealistisan VMTS" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                        </div>
                        <div class="col-span-2">
                            <label class="mb-2 block text-xs font-bold text-gray-700">Link Referensi</label>
                            <input type="url" placeholder="Masukkan URL referensi jika ada" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="flex items-center justify-end rounded-b border-t border-gray-200 p-4 md:p-5 gap-3">
                    <button onclick="toggleModal('modal-edit')" type="button" class="rounded-lg bg-gray-200 px-5 py-2 text-xs font-bold text-gray-700 hover:bg-gray-300 transition">Batal</button>
                    <button onclick="toggleModal('modal-edit')" type="button" class="rounded-lg bg-[#005A9C] px-5 py-2 text-xs font-bold text-white hover:bg-[#06284F] transition">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Hapus --}}
    <div id="modal-hapus" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50">
        <div class="relative w-full max-w-md p-4">
            <div class="relative rounded-2xl bg-white p-6 text-center shadow-lg">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center">
                    <svg class="h-10 w-10 text-[#D32F2F]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L1 21h22L12 2zm1 16h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-bold text-gray-900">Hapus Data?</h3>
                <p class="mb-6 text-xs text-gray-500">Apakah Anda yakin ingin menghapus data ini? Tindakan ini bersifat permanen.</p>
                <div class="flex justify-center gap-3">
                    <button onclick="toggleModal('modal-hapus')" class="rounded-lg bg-gray-200 px-8 py-2 text-xs font-bold text-gray-700 hover:bg-gray-300">Batal</button>
                    <button onclick="toggleModal('modal-hapus')" class="rounded-lg bg-[#D32F2F] px-8 py-2 text-xs font-bold text-white hover:bg-red-700">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Modal --}}
    <script>
        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
    </script>
</div>
@endsection