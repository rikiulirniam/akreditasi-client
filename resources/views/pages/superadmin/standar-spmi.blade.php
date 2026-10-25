@extends('layouts.app')

@section('title', 'Master Standar SPMI')

@section('content')
<div class="space-y-6">

    {{-- Header & Tombol Tambah --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Master Standar Mutu</h1>
            <p class="mt-1 text-xs text-gray-500">Standar mutu internal sebagai acuan pelaksanaan SPMI.</p>
        </div>
        <button onclick="toggleModal('modal-tambah')" class="inline-flex h-9 items-center justify-center rounded-lg bg-[#005A9C] px-5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#06284F]">
            + Tambah Standar Mutu
        </button>
    </div>

    {{-- Filter Bar (Search & Dropdown) --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <input type="text" placeholder="Cari nama atau kode standar.." class="block w-full rounded-lg border border-gray-300 bg-[#E9E9E9] p-2.5 text-xs text-gray-700 focus:border-[#005A9C] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
        </div>
        <select class="block w-full sm:w-40 rounded-lg border border-gray-300 bg-[#E9E9E9] p-2.5 text-xs text-gray-700 focus:border-[#005A9C] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
            <option>Semua Kategori</option>
            <option>Pendidikan</option>
            <option>Penelitian</option>
            <option>Pengabdian</option>
        </select>
        <select class="block w-full sm:w-40 rounded-lg border border-gray-300 bg-[#E9E9E9] p-2.5 text-xs text-gray-700 focus:border-[#005A9C] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
            <option>Semua Tahun</option>
            <option>2024</option>
            <option>2023</option>
            <option>2022</option>
        </select>
    </div>

    {{-- Tabel Master Standar SPMI --}}
    <div class="overflow-x-auto border-t border-gray-200 mt-2">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="text-[11px] font-medium text-gray-500 border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-4 py-3 pb-4">Kode Standar</th>
                    <th scope="col" class="px-4 py-3 pb-4">Nama Standar Mutu</th>
                    <th scope="col" class="px-4 py-3 pb-4">Kategori</th>
                    <th scope="col" class="px-4 py-3 pb-4">Tahun / Versi</th>
                    <th scope="col" class="px-4 py-3 pb-4">Status</th>
                    <th scope="col" class="px-4 py-3 pb-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                
                {{-- Data Contoh --}}
                <tr class="transition hover:bg-gray-50">
                    <td class="whitespace-nowrap px-4 py-4 text-xs font-medium text-gray-800">STD-PND-01</td>
                    <td class="px-4 py-4 text-xs text-gray-800">Standar Kompetensi Lulusan</td>
                    <td class="px-4 py-4 text-xs text-gray-800">Pendidikan</td>
                    <td class="px-4 py-4 text-xs text-gray-800">2024 / v2</td>
                    <td class="px-4 py-4 text-xs text-gray-800">
                        <span class="inline-flex items-center rounded border border-green-500 bg-white px-2 py-0.5 text-[10px] font-medium text-green-600 shadow-sm">Aktif</span>
                    </td>
                    <td class="px-4 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="toggleModal('modal-edit')" class="text-yellow-500 hover:text-yellow-700 transition" title="Edit">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button onclick="toggleModal('modal-hapus')" class="text-red-500 hover:text-red-700 transition" title="Hapus">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

    {{-- ========================================== --}}
    {{-- KUMPULAN MODAL & SCRIPT --}}
    {{-- ========================================== --}}

    {{-- Modal Tambah Standar --}}
    <div id="modal-tambah" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50">
        <div class="relative w-full max-w-lg p-4">
            <div class="relative rounded-2xl bg-white shadow-lg">
                <div class="flex items-center justify-between border-b border-gray-200 p-4 md:p-5">
                    <h3 class="text-lg font-bold text-[#06284F]">Tambah Standar Mutu</h3>
                    <button type="button" onclick="toggleModal('modal-tambah')" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg p-1.5 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
                <div class="p-4 md:p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Kode Standar <span class="text-red-500">*</span></label>
                            <input type="text" placeholder="Contoh: STD-PND-01" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Kategori <span class="text-red-500">*</span></label>
                            <select class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
                                <option value="">-- Pilih --</option>
                                <option>Pendidikan</option>
                                <option>Penelitian</option>
                                <option>Pengabdian</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="mb-2 block text-xs font-bold text-gray-700">Nama Standar Mutu <span class="text-red-500">*</span></label>
                            <input type="text" placeholder="Masukkan nama standar mutu" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Tahun / Versi <span class="text-red-500">*</span></label>
                            <input type="text" placeholder="Contoh: 2024 / v2" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Status <span class="text-red-500">*</span></label>
                            <select class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
                                <option>Aktif</option>
                                <option>Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end rounded-b border-t border-gray-200 p-4 md:p-5 gap-3">
                    <button onclick="toggleModal('modal-tambah')" type="button" class="rounded-lg bg-gray-200 px-5 py-2 text-xs font-bold text-gray-700 hover:bg-gray-300 transition">Batal</button>
                    <button onclick="toggleModal('modal-tambah')" type="button" class="rounded-lg bg-[#005A9C] px-5 py-2 text-xs font-bold text-white hover:bg-[#06284F] transition">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Edit Standar --}}
    <div id="modal-edit" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50">
        <div class="relative w-full max-w-lg p-4">
            <div class="relative rounded-2xl bg-white shadow-lg">
                <div class="flex items-center justify-between border-b border-gray-200 p-4 md:p-5">
                    <h3 class="text-lg font-bold text-[#06284F]">Edit Standar Mutu</h3>
                    <button type="button" onclick="toggleModal('modal-edit')" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg p-1.5 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
                <div class="p-4 md:p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Kode Standar <span class="text-red-500">*</span></label>
                            <input type="text" value="STD-PND-01" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Kategori <span class="text-red-500">*</span></label>
                            <select class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
                                <option selected>Pendidikan</option>
                                <option>Penelitian</option>
                                <option>Pengabdian</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="mb-2 block text-xs font-bold text-gray-700">Nama Standar Mutu <span class="text-red-500">*</span></label>
                            <input type="text" value="Standar Kompetensi Lulusan" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Tahun / Versi <span class="text-red-500">*</span></label>
                            <input type="text" value="2024 / v2" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-700">Status <span class="text-red-500">*</span></label>
                            <select class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-900 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
                                <option selected>Aktif</option>
                                <option>Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
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
                <p class="mb-6 text-xs text-gray-500">Apakah Anda yakin ingin menghapus standar mutu ini? Tindakan ini bersifat permanen.</p>
                <div class="flex justify-center gap-3">
                    <button onclick="toggleModal('modal-hapus')" class="rounded-lg bg-gray-200 px-8 py-2 text-xs font-bold text-gray-700 hover:bg-gray-300 transition">Batal</button>
                    <button onclick="toggleModal('modal-hapus')" class="rounded-lg bg-[#D32F2F] px-8 py-2 text-xs font-bold text-white hover:bg-red-700 transition">Hapus</button>
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