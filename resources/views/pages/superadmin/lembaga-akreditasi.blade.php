@extends('layouts.app')

@section('title', 'Master Data Instrumen')

@section('content')
<div class="space-y-6">

    {{-- Bagian Header & Tombol Tambah --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between border-b border-gray-200 pb-5">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-[#005A9C]">
                Master Data Instrumen
            </p>
            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-[#06284F]">
                Lembaga Akreditasi
            </h2>
            <p class="mt-1 text-xs text-gray-500 font-medium">
                1 Data terkait
            </p>
        </div>

        <button onclick="toggleModal('modal-tambah-lembaga')" class="inline-flex h-9 items-center justify-center rounded-lg bg-[#005A9C] px-4 text-xs font-semibold text-white transition hover:bg-[#06284F] shadow-sm">
            + Tambah Lembaga
        </button>
    </div>

    {{-- Daftar Card Lembaga --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        
        <article class="flex min-h-[140px] flex-col justify-between rounded-xl border border-gray-200 bg-[#F4F7F9] p-5 transition hover:shadow-md">
            <div>
                <h3 class="text-sm font-extrabold text-[#06284F]">LAM INFOKOM</h3>
                <p class="mt-1.5 text-xs leading-relaxed text-gray-600">
                    Lembaga Akreditasi Mandiri Informatika dan Komputer
                </p>
            </div>
            
            <div class="mt-4 flex justify-end">
                <button onclick="window.location.href='{{ route('akreditasi.superadmin.instrumen') }}'" class="flex h-7 w-7 items-center justify-center rounded-full bg-[#06284F] text-white transition hover:bg-[#005A9C]">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </article>

        {{-- Tambahkan <article> lain di sini jika datanya lebih dari satu --}}
        
    </div>

</div>

{{-- ========================================== --}}
    {{-- KUMPULAN MODAL DAN SCRIPT --}}
    {{-- ========================================== --}}

    {{-- Modal Tambah Lembaga --}}
    <div id="modal-tambah-lembaga" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50">
        <div class="relative w-full max-w-2xl p-4">
            <div class="relative rounded-2xl bg-white shadow-lg">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 p-4 md:p-5">
                    <h3 class="text-lg font-bold text-[#06284F]">Tambah Lembaga Akreditasi</h3>
                    <button type="button" onclick="toggleModal('modal-tambah-lembaga')" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg p-1.5 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
                <!-- Body (Form) -->
                <div class="p-4 md:p-5 space-y-4">
                    <h4 class="text-xs font-bold text-gray-900">Identitas 1</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Kode Lembaga <span class="text-red-500">*</span></label>
                            <input type="text" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Nama Lembaga <span class="text-red-500">*</span></label>
                            <input type="text" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Website</label>
                            <input type="url" placeholder="https://contoh.com/dokumen" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Status Aktif</label>
                            <select class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:ring-[#005A9C] focus:outline-none">
                                <option>Aktif</option>
                                <option>Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="flex items-center justify-between rounded-b border-t border-gray-200 p-4 md:p-5">
                    <button type="button" class="rounded-lg bg-gray-200 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-300 transition">+ Tambah isi entitas</button>
                    <div class="flex gap-3">
                        <button onclick="toggleModal('modal-tambah-lembaga')" type="button" class="rounded-lg bg-gray-200 px-5 py-2 text-xs font-bold text-gray-700 hover:bg-gray-300 transition">Batal</button>
                        <button onclick="toggleModal('modal-tambah-lembaga')" type="button" class="rounded-lg-[#005A9C] px-5 py-2 text-xs font-bold text-white bg-[#005A9C] hover:bg-[#06284F] transition">Simpan</button>
                    </div>
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