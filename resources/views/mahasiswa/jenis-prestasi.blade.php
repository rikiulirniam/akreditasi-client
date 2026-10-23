@extends('layouts.app')

@section('title', 'Jenis Prestasi Mahasiswa')

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
            Jenis Prestasi
        </h2>
        @if(!empty($jenisPrestasiList) && count($jenisPrestasiList) > 0)
            <button type="button" onclick="openAddModal()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Jenis Prestasi
            </button>
        @endif
    </div>

    @if(empty($jenisPrestasiList) || count($jenisPrestasiList) === 0)
        <!-- ========================================== -->
        <!-- EMPTY STATE CARD (JIKA BELUM ADA DATA)     -->
        <!-- ========================================== -->
        <div class="w-full bg-[#F8F9FA] border border-gray-200/80 rounded-[28px] p-12 sm:p-20 text-center my-6 min-h-[400px] flex flex-col items-center justify-center">
            <p class="text-base sm:text-lg font-medium text-gray-800 leading-relaxed max-w-md mx-auto mb-6">
                Kamu belum ada data Jenis Prestasi,<br>silahkan tekan tombol tambah untuk Tambah Jenis Prestasi.
            </p>
            <button type="button" onclick="openAddModal()" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl bg-primary hover:bg-additional text-white font-bold text-sm shadow-md transition cursor-pointer">
                <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Tambah Jenis Prestasi</span>
            </button>
        </div>
    @else
        <!-- ========================================== -->
        <!-- TABEL KATEGORI JENIS PRESTASI             -->
        <!-- ========================================== -->
        <div class="w-full rounded-2xl border border-gray-200 overflow-hidden">
            <x-table class="w-full table-fixed">
                <x-slot:header>
                    <x-table.th class="w-[10%] text-center">No</x-table.th>
                    <x-table.th class="w-[70%]">Nama Jenis / Kategori Prestasi</x-table.th>
                    <x-table.th class="w-[20%] text-center">Action</x-table.th>
                </x-slot:header>

                @foreach($jenisPrestasiList as $item)
                    @php
                        $id = $item['id'] ?? 0;
                        $namaJenis = $item['jenis'] ?? $item['nama_jenis'] ?? $item['kategori'] ?? '-';
                    @endphp
                    <x-table.row>
                        <x-table.cell class="text-gray-500 font-medium text-center">{{ $loop->iteration }}.</x-table.cell>
                        <x-table.cell>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ strtolower($namaJenis) === 'akademik' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                {{ $namaJenis }}
                            </span>
                        </x-table.cell>
                        <x-table.cell class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <!-- 1. EDIT (Pensil Kuning) -->
                                <button type="button" onclick='openEditModal(@json($item), "{{ $namaJenis }}")' class="text-amber-500 hover:text-amber-600 p-1 cursor-pointer" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </button>

                                <!-- 2. HAPUS (Tong Sampah Merah) -->
                                <form action="{{ route('kemahasiswaan.jenis-prestasi.destroy', ['id' => $id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Jenis Prestasi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-600 p-1 cursor-pointer" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>
        </div>
    @endif
</div>

<!-- ========================================== -->
<!-- MODAL: Tambah / Edit Jenis Prestasi        -->
<!-- ========================================== -->
<div id="formJenisPrestasiModal" class="fixed inset-0 z-50 hidden bg-black/45 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-[28px] max-w-[420px] w-full p-8 shadow-2xl space-y-5 text-left transform transition-all">
        <h3 id="modalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center">Tambah Jenis Prestasi</h3>
        <form id="jenisPrestasiForm" method="POST" action="{{ route('kemahasiswaan.jenis-prestasi.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Nama Jenis / Kategori</label>
                <input type="text" name="jenis" id="inputJenis" required placeholder="Contoh: Akademik / Non-Akademik" class="w-full px-4 py-2.5 rounded-[10px] bg-gray-100 border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/30 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4 pt-4">
                <button type="button" onclick="closeFormModal()" class="w-full py-3 px-6 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold text-sm transition cursor-pointer text-center">Batal</button>
                <button type="submit" class="w-full py-3 px-6 rounded-xl bg-primary hover:bg-additional text-white font-bold text-sm shadow-sm transition cursor-pointer text-center">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const storeUrl = "{{ route('kemahasiswaan.jenis-prestasi.store') }}";
    const updateBaseUrl = "{{ url('/kemahasiswaan/jenis-prestasi') }}";

    function openAddModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Jenis Prestasi';
        document.getElementById('jenisPrestasiForm').action = storeUrl;
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('inputJenis').value = '';
        document.getElementById('formJenisPrestasiModal').classList.remove('hidden');
    }

    function openEditModal(item, namaJenis) {
        const id = item.id || 0;
        document.getElementById('modalTitle').innerText = 'Perbarui Jenis Prestasi';
        document.getElementById('jenisPrestasiForm').action = updateBaseUrl + '/' + id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('inputJenis').value = namaJenis !== '-' ? namaJenis : '';
        document.getElementById('formJenisPrestasiModal').classList.remove('hidden');
    }

    function closeFormModal() {
        document.getElementById('formJenisPrestasiModal').classList.add('hidden');
    }
</script>
@endsection