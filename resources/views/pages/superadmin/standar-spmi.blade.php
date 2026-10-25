@extends('layouts.app')

@section('title', 'Standar Mutu SPMI')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-primary tracking-tight">Standar Mutu SPMI</h1>
            <p class="text-xs sm:text-sm text-gray-500 font-medium mt-1">Kelola standar mutu internal perguruan tinggi sebagai rujukan akreditasi.</p>
        </div>
        <button onclick="toggleModal('modal-tambah')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Standar
        </button>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3 flex-1 max-w-xl">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
                <input 
                    type="text" 
                    id="searchStandar" 
                    placeholder="Cari kode atau nama standar..." 
                    class="block w-full rounded-[8px] border border-white-bone bg-white-form pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
            </div>
            
            <select class="appearance-none px-3.5 py-2.5 pr-8 rounded-[8px] border border-white-bone bg-white-form text-sm font-medium text-gray-700 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer">
                <option>Semua Tahun</option>
                <option>2026</option>
                <option>2025</option>
                <option>2024</option>
            </select>
        </div>
    </div>

    {{-- Tabel Master Standar SPMI menggunakan format x-table akademik --}}
    <div class="bg-white rounded-2xl border border-[#EAEAEA] shadow-xs overflow-hidden">
        <x-table>
            <x-slot:header>
                <x-table.th class="w-36">Kode Standar</x-table.th>
                <x-table.th>Nama Standar Mutu</x-table.th>
                <x-table.th class="w-36">Kategori</x-table.th>
                <x-table.th class="w-36">Tahun / Versi</x-table.th>
                <x-table.th class="w-28">Status</x-table.th>
                <x-table.th class="w-28 text-right">Aksi</x-table.th>
            </x-slot:header>

            @php
                $standarSpmiList = [
                    [
                        'kode' => 'STD-PND-01',
                        'nama' => 'Standar Kompetensi Lulusan',
                        'kategori' => 'Pendidikan',
                        'versi' => '2024 / v2',
                        'status' => 'Aktif',
                    ],
                    [
                        'kode' => 'STD-PND-02',
                        'nama' => 'Standar Isi Pembelajaran',
                        'kategori' => 'Pendidikan',
                        'versi' => '2024 / v2',
                        'status' => 'Aktif',
                    ],
                    [
                        'kode' => 'STD-PLT-01',
                        'nama' => 'Standar Hasil Penelitian',
                        'kategori' => 'Penelitian',
                        'versi' => '2024 / v1',
                        'status' => 'Aktif',
                    ],
                    [
                        'kode' => 'STD-PGM-01',
                        'nama' => 'Standar Hasil Pengabdian kepada Masyarakat',
                        'kategori' => 'Pengabdian',
                        'versi' => '2024 / v1',
                        'status' => 'Aktif',
                    ],
                ];
            @endphp

            @foreach ($standarSpmiList as $std)
                <x-table.row>
                    <x-table.cell>
                        <span class="font-mono font-bold text-sm text-primary tracking-tight">
                            {{ $std['kode'] }}
                        </span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="font-semibold text-sm text-gray-900">
                            {{ $std['nama'] }}
                        </span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            {{ $std['kategori'] }}
                        </span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="text-sm font-medium text-gray-700">
                            {{ $std['versi'] }}
                        </span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            {{ $std['status'] }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="w-28 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <button onclick="toggleModal('modal-edit')" class="text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer" title="Edit">
                                <x-icon.edit class="w-5 h-5" />
                            </button>
                            <button onclick="toggleModal('modal-hapus')" class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer" title="Hapus">
                                <x-icon.delete class="w-5 h-5" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @endforeach
        </x-table>
    </div>

</div>

{{-- Modal Tambah Standar --}}
<div id="modal-tambah" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Tambah Standar Mutu</h3>
                <button type="button" onclick="toggleModal('modal-tambah')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 sm:p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Kode Standar <span class="text-red-500">*</span></label>
                        <input type="text" placeholder="Contoh: STD-PND-01" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Kategori <span class="text-red-500">*</span></label>
                        <select class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="">-- Pilih --</option>
                            <option>Pendidikan</option>
                            <option>Penelitian</option>
                            <option>Pengabdian</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Nama Standar Mutu <span class="text-red-500">*</span></label>
                    <input type="text" placeholder="Masukkan nama standar mutu" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Tahun</label>
                        <input type="text" value="2026" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Nomor Versi</label>
                        <input type="text" value="v1" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
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
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Edit Standar Mutu</h3>
                <button type="button" onclick="toggleModal('modal-edit')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 sm:p-6 space-y-4">
                <div>
                    <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Kode Standar</label>
                    <input type="text" value="STD-PND-01" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                </div>
                <div>
                    <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Nama Standar Mutu</label>
                    <input type="text" value="Standar Kompetensi Lulusan" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
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
            <h3 class="text-lg font-bold text-gray-900 tracking-tight">Hapus Standar Mutu?</h3>
            <p class="mt-2 text-sm text-gray-600">Seluruh rujukan indikator pada standar mutu ini akan terpengaruh.</p>
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