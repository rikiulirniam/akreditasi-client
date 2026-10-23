@extends('layouts.app')

@section('title', 'Anggota UPPS')

@section('content')
<style>
    .table-container div,
    .table-container table {
        overflow-x: hidden !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    .table-container div::-webkit-scrollbar {
        display: none !important;
    }
</style>

<div class="space-y-6 w-full max-w-full overflow-hidden table-container">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900 tracking-tight">
            Anggota UPPS - {{ $upps['nama_upps'] ?? 'Detail UPPS' }}
        </h2>
        <button type="button" onclick="openAddModal()" class="bg-primary text-white font-medium px-4 py-2 rounded-xl text-sm hover:bg-additional transition flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Anggota
        </button>
    </div>

    <div class="w-full rounded-2xl border border-gray-200 overflow-hidden">
        <x-table class="w-full table-fixed">
            <x-slot:header>
                <x-table.th class="w-[6%] text-center">No</x-table.th>
                <x-table.th class="w-[40%]">Nama Anggota</x-table.th>
                <x-table.th class="w-[30%]">Jabatan</x-table.th>
                <x-table.th class="w-[12%] text-center">Status</x-table.th>
                <x-table.th class="w-[12%] text-center">Action</x-table.th>
            </x-slot:header>

            @forelse($anggotaList as $item)
                @php
                    $idAnggota = $item['id'] ?? 0;
                    $nama = is_array($item) ? ($item['nama'] ?? $item['nama_anggota'] ?? '-') : '-';
                    $jabatan = is_array($item) ? ($item['jabatan'] ?? '-') : '-';
                    $status = is_array($item) ? ($item['status'] ?? 'Aktif') : 'Aktif';
                @endphp
                <x-table.row>
                    <x-table.cell class="text-gray-500 font-medium text-center">{{ $loop->iteration }}.</x-table.cell>
                    <x-table.cell class="font-medium text-gray-900 whitespace-nowrap">{{ $nama }}</x-table.cell>
                    <x-table.cell class="text-gray-700 text-sm whitespace-nowrap">{{ $jabatan }}</x-table.cell>
                    <x-table.cell class="text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ strtolower($status) === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $status }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <!-- Edit -->
                            <button type="button" onclick='openEditModal(@json($item))' class="text-amber-500 hover:text-amber-600 p-1 cursor-pointer" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                            </button>

                            <!-- Delete -->
                            <form action="{{ route('upps.anggota.destroy', ['id' => $id, 'anggotaId' => $idAnggota]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus anggota ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-600 p-1 cursor-pointer" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </form>

                            <!-- Detail (Mata) -->
                            <button type="button" onclick='openDetailModal(@json($item))' class="text-primary hover:text-additional p-1 cursor-pointer" title="Lihat Detail">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.573 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="5" message="Belum ada data anggota UPPS" />
            @endforelse
        </x-table>
    </div>
</div>

<!-- Modal Form Tambah/Edit -->
<div id="formModal" class="fixed inset-0 z-50 hidden bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
        <h3 id="modalTitle" class="text-lg font-bold text-gray-900">Tambah Anggota UPPS</h3>
        <form id="anggotaForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Anggota</label>
                    <input type="text" name="nama" id="inputNama" required class="mt-1 w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jabatan</label>
                    <input type="text" name="jabatan" id="inputJabatan" required class="mt-1 w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-primary focus:border-primary">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="closeFormModal()" class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium bg-primary text-white hover:bg-additional">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail -->
<div id="detailModal" class="fixed inset-0 z-50 hidden bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
        <h3 class="text-lg font-bold text-gray-900">Detail Anggota UPPS</h3>
        <div class="space-y-3 text-sm">
            <div>
                <span class="block text-gray-500">Nama Anggota</span>
                <span id="detailNama" class="font-semibold text-gray-900"></span>
            </div>
            <div>
                <span class="block text-gray-500">Jabatan</span>
                <span id="detailJabatan" class="font-semibold text-gray-900"></span>
            </div>
        </div>
        <div class="flex justify-end mt-6">
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">Tutup</button>
        </div>
    </div>
</div>

<script>
    const baseUrl = "{{ url('/upps/'.$id.'/anggota') }}";

    function openAddModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Anggota UPPS';
        document.getElementById('anggotaForm').action = baseUrl;
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('inputNama').value = '';
        document.getElementById('inputJabatan').value = '';
        document.getElementById('formModal').classList.remove('hidden');
    }

    function openEditModal(item) {
        document.getElementById('modalTitle').innerText = 'Edit Anggota UPPS';
        document.getElementById('anggotaForm').action = baseUrl + '/' + item.id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('inputNama').value = item.nama || '';
        document.getElementById('inputJabatan').value = item.jabatan || '';
        document.getElementById('formModal').classList.remove('hidden');
    }

    function closeFormModal() {
        document.getElementById('formModal').classList.add('hidden');
    }

    function openDetailModal(item) {
        document.getElementById('detailNama').innerText = item.nama || '-';
        document.getElementById('detailJabatan').innerText = item.jabatan || '-';
        document.getElementById('detailModal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }
</script>
@endsection