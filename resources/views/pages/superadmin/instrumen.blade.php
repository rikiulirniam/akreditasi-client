@extends('layouts.app')

@section('title', 'Master Data Instrumen')

@section('content')
<div class="space-y-6">

    {{-- Breadcrumbs --}}
    <nav class="flex text-[11px] font-medium text-gray-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <span class="text-gray-400">Master Data Akreditasi</span>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <a href="{{ route('akreditasi.superadmin.lembaga_akreditasi') }}" class="hover:text-[#005A9C] transition">Lembaga Akreditasi: LAM-INFOKOM</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <span class="mx-1 text-gray-400">/</span>
                    <span class="font-bold text-gray-800">Versi Instrumen: 2.1</span>
                </div>
            </li>
        </ol>
    </nav>

    {{-- Banner Utama (Dark Blue Card) --}}
    <section class="flex flex-col justify-between gap-4 rounded-xl bg-[#06284F] p-6 shadow-sm sm:flex-row sm:items-start">
        <div class="text-white">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-300">
                DETAIL VERSI INSTRUMEN
            </p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight">
                Versi instrumen : 2.1
            </h2>
            <p class="mt-2 text-xs text-gray-300/80">
                Versi instrumen beserta lembaga, tanggal berlaku, dan status aktif.
            </p>
        </div>
        
        <div class="flex shrink-0 items-center gap-3">
            <button onclick="toggleModal('modal-edit')" class="rounded-lg bg-[#FFD400] px-6 py-2 text-sm font-bold text-[#06284F] transition hover:bg-yellow-500 shadow-sm">                
                Edit
            </button>
            <button onclick="toggleModal('modal-hapus')" class="rounded-lg bg-[#D32F2F] px-5 py-2 text-sm font-bold text-white transition hover:bg-red-700 shadow-sm">                
                Hapus
            </button>
        </div>
    </section>

    {{-- Metadata Info --}}
    <section class="grid grid-cols-3 gap-4 px-2 py-2">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">LEMBAGA</p>
            <p class="mt-1 text-sm font-medium text-gray-900">LAM INFOKOM</p>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">NOMOR VERSI</p>
            <p class="mt-1 text-sm font-medium text-gray-900">2.1</p>
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">STATUS AKTIF</p>
            <p class="mt-1 text-sm font-medium text-gray-900">Aktif</p>
        </div>
    </section>

    {{-- Tabel Instrumen --}}
    <section class="mt-4">
        {{-- Header Tabel --}}
        <div class="mb-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h3 class="text-lg font-bold text-[#06284F]">Instrumen</h3>
                <p class="text-xs text-gray-500">4 data terkait</p>
            </div>
            <button onclick="toggleModal('modal-tambah-instrumen')" class="inline-flex h-9 items-center justify-center rounded-lg bg-[#005A9C] px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-[#06284F]">                
                + Tambah Instrumen
            </button>
        </div>

        {{-- Kontainer Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="border-b border-t border-gray-200 text-[11px] uppercase tracking-wider text-gray-500">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">UID</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Jenis Instrumen</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Jenjang</th>
                        <th scope="col" class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    
                    @php
                        $instrumens = [
                            ['uid' => 'INS-001', 'jenis' => 'APS untuk Pemenuhan Syarat Minimum Program Studi Baru', 'jenjang' => 'Sarjana Terapan'],
                            ['uid' => 'INS-002', 'jenis' => 'APS untuk Perolehan Status Terakreditasi', 'jenjang' => 'Sarjana Terapan'],
                            ['uid' => 'INS-003', 'jenis' => 'APS untuk Status Terakreditasi Unggul', 'jenjang' => 'Sarjana'],
                            ['uid' => 'INS-004', 'jenis' => 'Kriteria Unggul LAM INFOKOM 2.1', 'jenjang' => 'Sarjana Terapan'],
                        ];
                    @endphp

                    @foreach ($instrumens as $item)
                    <tr class="transition hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-4 text-xs font-medium text-gray-900">
                            {{ $item['uid'] }}
                        </td>
                        <td class="px-4 py-4 text-xs">
                            {{ $item['jenis'] }}
                        </td>
                        <td class="px-4 py-4 text-xs">
                            {{ $item['jenjang'] }}
                        </td>
                        <td class="px-4 py-4 text-right">
                            <button onclick="window.location.href='{{ route('akreditasi.superadmin.kriteria') }}'" class="text-[#005A9C] hover:text-[#06284F] transition">                                
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

</div>

{{-- Modal Edit Versi Instrumen --}}
    <div id="modal-edit" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50">
        <div class="relative w-full max-w-lg p-4">
            <div class="relative rounded-2xl bg-white shadow-lg">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 p-4 md:p-5">
                    <h3 class="text-lg font-bold text-[#06284F]">Edit Versi Instrumen</h3>
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
                            <label class="mb-2 block text-xs font-bold text-gray-700">Lembaga <span class="text-red-500">*</span></label>
                            <input type="text" value="LAM INFOKOM" disabled class="block w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-xs text-gray-500 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Nomor Versi <span class="text-red-500">*</span></label>
                            <input type="text" value="2.1" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Tanggal Berlaku</label>
                            <input type="date" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Status Aktif</label>
                            <select class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                                <option selected>Aktif</option>
                                <option>Non-Aktif</option>
                            </select>
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

    {{-- Modal Tambah Instrumen --}}
    <div id="modal-tambah-instrumen" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50">
        <div class="relative w-full max-w-lg p-4">
            <div class="relative rounded-2xl bg-white shadow-lg">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 p-4 md:p-5">
                    <h3 class="text-lg font-bold text-[#06284F]">Tambah Instrumen</h3>
                    <button type="button" onclick="toggleModal('modal-tambah-instrumen')" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg p-1.5 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
                <!-- Body (Form) -->
                <div class="p-4 md:p-5 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold text-gray-700">Kode UID <span class="text-red-500">*</span></label>
                        <input type="text" placeholder="Contoh: INS-005" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold text-gray-700">Jenis Instrumen <span class="text-red-500">*</span></label>
                        <input type="text" placeholder="Masukkan jenis instrumen" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold text-gray-700">Jenjang <span class="text-red-500">*</span></label>
                        <select class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                            <option value="">-- Pilih Jenjang --</option>
                            <option>Sarjana</option>
                            <option>Sarjana Terapan</option>
                            <option>Magister</option>
                            <option>Doktor</option>
                        </select>
                    </div>
                </div>
                <!-- Footer -->
                <div class="flex items-center justify-end rounded-b border-t border-gray-200 p-4 md:p-5 gap-3">
                    <button onclick="toggleModal('modal-tambah-instrumen')" type="button" class="rounded-lg bg-gray-200 px-5 py-2 text-xs font-bold text-gray-700 hover:bg-gray-300 transition">Batal</button>
                    <button onclick="toggleModal('modal-tambah-instrumen')" type="button" class="rounded-lg bg-[#005A9C] px-5 py-2 text-xs font-bold text-white hover:bg-[#06284F] transition">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    
{{-- Modal Hapus (Sesuai Figma) --}}
    <div id="modal-hapus" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50">
        <div class="relative w-full max-w-md p-4">
            <div class="relative rounded-2xl bg-white p-6 text-center shadow-lg">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center">
                    <svg class="h-10 w-10 text-[#D32F2F]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L1 21h22L12 2zm1 16h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-bold text-gray-900">Hapus Data?</h3>
                <p class="mb-6 text-xs text-gray-500">
                    Apakah Anda yakin ingin menghapus data ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.
                </p>
                <div class="flex justify-center gap-3">
                    <button onclick="toggleModal('modal-hapus')" class="rounded-lg bg-gray-200 px-8 py-2 text-xs font-bold text-gray-700 hover:bg-gray-300">
                        Batal
                    </button>
                    <button onclick="toggleModal('modal-hapus')" class="rounded-lg bg-[#D32F2F] px-8 py-2 text-xs font-bold text-white hover:bg-red-700">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Pembuka Jendela --}}
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
@endsection