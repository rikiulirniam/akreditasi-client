@extends('layouts.app')

@section('title', 'Detail Perguruan Tinggi')

@section('content')
<div class="space-y-8">
    <div>
        <!-- Top Card: Detail Perguruan Tinggi -->
        <div class="bg-white rounded-[28px] p-6 sm:p-7 border border-[#CACACA] shadow-xs">
            <!-- Card Header: Logo, University Name, and Edit Data Button -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img 
                        src="{{ $pt['logo_url'] }}" 
                        alt="{{ $pt['nama_pt'] }}" 
                        class="w-14 h-14 object-contain shrink-0 drop-shadow-xs"
                        onerror="this.onerror=null; this.src='https://web.polines.ac.id/wp-content/uploads/2022/01/Logo-Polines-96dpi-200px-150x150.png';"
                    >
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                        {{ $pt['nama_pt'] }}
                    </h3>
                </div>
                <div>
                    <button 
                        type="button" 
                        id="openEditPtModalBtn"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#EAB308] hover:bg-[#CA8A04] text-white font-bold text-sm shadow-sm transition cursor-pointer"
                    >
                        <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Edit Data
                    </button>
                </div>
            </div>

            <!-- Divider -->
            <hr class="border-[#CACACA]/60 my-5">

            <!-- Card Body: 2 Columns Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10 text-sm">
                <!-- Left Column: Pimpinan & Alamat -->
                <div class="space-y-5">
                    <!-- Pimpinan -->
                    <div class="flex items-start gap-3.5">
                        <div class="p-2 rounded-xl bg-gray-100 text-gray-600 shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold text-gray-900 block text-xs uppercase tracking-wider mb-0.5">Pimpinan:</span>
                            <p class="text-sm font-medium text-gray-800">{{ $pt['pimpinan'] ?: '-' }}</p>
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div class="flex items-start gap-3.5">
                        <div class="p-2 rounded-xl bg-gray-100 text-gray-600 shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold text-gray-900 block text-xs uppercase tracking-wider mb-0.5">Alamat:</span>
                            <p class="text-sm font-medium text-gray-800 leading-relaxed">{{ $pt['alamat'] ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Visi & Misi -->
                <div class="space-y-4">
                    <!-- Visi -->
                    <div>
                        <span class="font-bold text-gray-900 block text-xs uppercase tracking-wider mb-1">Visi:</span>
                        <p class="text-sm font-medium text-gray-800 leading-relaxed">
                            {{ $pt['visi'] ?: '-' }}
                        </p>
                    </div>

                    <!-- Misi -->
                    <div>
                        <span class="font-bold text-gray-900 block text-xs uppercase tracking-wider mb-1">Misi:</span>
                        @php
                            $misiItems = array_filter(array_map('trim', explode("
", $pt['misi'] ?? '')));
                        @endphp
                        @if(!empty($misiItems))
                            <ul class="list-disc list-outside pl-5 space-y-1.5 text-sm font-medium text-gray-800 leading-relaxed">
                                @foreach($misiItems as $m)
                                    <li>{{ ltrim($m, '-•* ') }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm font-medium text-gray-500 italic">Belum ada misi yang diisi.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Daftar Program Studi -->
    <div class="space-y-4">
        <h3 class="text-xl font-bold text-gray-900 tracking-tight">Daftar Program Studi</h3>

        <!-- Search Input Matching existing pages -->
        <div class="relative flex-1 max-w-sm">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </span>
            <x-input 
                type="text" 
                id="searchProdiInput"
                placeholder="Cari prodi..." 
                class="pl-10 text-xs"
            />
        </div>

        <!-- Table Program Studi Matching other tables -->
        <x-table>
            <x-slot:header>
                <x-table.th>No</x-table.th>
                <x-table.th>Nama Prodi</x-table.th>
                <x-table.th>Akreditasi</x-table.th>
                <x-table.th>Nama UPPS</x-table.th>
                <x-table.th>Action</x-table.th>
            </x-slot:header>

            @forelse($prodiList as $index => $item)
                <x-table.row class="prodi-row">
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $index + 1 }}
                    </x-table.cell>
                    <x-table.cell class="w-72">
                        <span class="font-extrabold text-sm text-gray-900 prodi-name tracking-tight font-mono">
                            {{ $item['nama_prodi'] ?? '-' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="w-36">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 prodi-akreditasi">
                            {{ $item['akreditasi'] ?? '-' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="text-sm font-medium text-gray-700 prodi-upps">
                            {{ $item['nama_upps'] ?? '-' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="w-24">
                        <div class="flex items-center justify-end">
                            <button 
                                type="button" 
                                class="text-primary hover:text-additional p-1 cursor-pointer transition"
                                title="Lihat Detail"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="5" message="Belum ada data program studi" submessage="Data program studi akan terhubung dengan UPPS dan perguruan tinggi." />
            @endforelse
        </x-table>
    </div>
</div>

<!-- Modal: Update Data Perguruan Tinggi (Matching Screenshot 2) -->
<div 
    id="editPtModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editPtModalTitle"
>
    <div class="pt-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editPtModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Update Data Perguruan Tinggi
        </h3>

        <form action="{{ route('perguruan-tinggi.update', $pt['id'] ?? 1) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" name="kode_pt" value="{{ $pt['kode_pt'] ?? 'PT001' }}">

            <!-- Field 1: Nama Perguruan Tinggi -->
            <div>
                <label for="ptNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama Perguruan Tinggi
                </label>
                <input 
                    type="text" 
                    name="nama_pt" 
                    id="ptNamaInput" 
                    value="{{ $pt['nama_pt'] }}"
                    placeholder="Masukkan nama" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Pimpinan Perguruan Tinggi -->
            <div>
                <label for="ptPimpinanInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Pimpinan Perguruan Tinggi
                </label>
                <input 
                    type="text" 
                    name="pimpinan" 
                    id="ptPimpinanInput" 
                    value="{{ $pt['pimpinan'] }}"
                    placeholder="Masukkan nama pimpinan" 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 3: Alamat -->
            <div>
                <label for="ptAlamatInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Alamat
                </label>
                <input 
                    type="text" 
                    name="alamat" 
                    id="ptAlamatInput" 
                    value="{{ $pt['alamat'] }}"
                    placeholder="Masukkan Alamat" 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 4: Visi -->
            <div>
                <label for="ptVisiInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Visi
                </label>
                <textarea 
                    name="visi" 
                    id="ptVisiInput" 
                    rows="2"
                    placeholder="Masukkan Visi" 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500 resize-none"
                >{{ $pt['visi'] }}</textarea>
            </div>

            <!-- Field 5: Misi -->
            <div>
                <label for="ptMisiInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Misi
                </label>
                <textarea 
                    name="misi" 
                    id="ptMisiInput" 
                    rows="3"
                    placeholder="Masukkan Misi" 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500 resize-none leading-relaxed"
                >{{ $pt['misi'] }}</textarea>
            </div>

            <!-- Action Buttons: Batal & Simpan -->
            <div class="grid grid-cols-2 gap-4 pt-3">
                <button 
                    type="button" 
                    id="closeEditPtModalBtn"
                    class="w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-[#01568D] hover:bg-primary text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Search Prodi in table
        const searchInput = document.getElementById('searchProdiInput');
        const rows = document.querySelectorAll('.prodi-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const name = row.querySelector('.prodi-name')?.textContent.toLowerCase() || '';
                    const akreditasi = row.querySelector('.prodi-akreditasi')?.textContent.toLowerCase() || '';
                    const upps = row.querySelector('.prodi-upps')?.textContent.toLowerCase() || '';
                    row.style.display = (name.includes(keyword) || akreditasi.includes(keyword) || upps.includes(keyword)) ? '' : 'none';
                });
            });
        }

        // Modal Helpers
        const modal = document.getElementById('editPtModal');
        const card = modal?.querySelector('.pt-modal-card');
        const openBtn = document.getElementById('openEditPtModalBtn');
        const closeBtn = document.getElementById('closeEditPtModalBtn');

        function openModal() {
            if (!modal) return;
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);

        modal?.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeModal();
        });

        // File Input Label updater
        const fileInput = document.getElementById('ptLogoInput');
        const fileNameSpan = document.getElementById('ptLogoFileName');

        if (fileInput && fileNameSpan) {
            fileInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    fileNameSpan.textContent = this.files[0].name;
                    fileNameSpan.classList.remove('text-gray-600');
                    fileNameSpan.classList.add('text-gray-900', 'font-bold');
                }
            });
        }
    });
</script>
@endsection
