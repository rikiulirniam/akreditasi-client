@extends('layouts.app')

@section('title', 'Capaian Pembelajaran Lulusan (CPL)')

@section('content')
<div class="space-y-6">
    @if($stats['total'] === 0 && empty($search) && empty($selectedKategori) && empty($selectedKurikulum))
        <!-- ======================================================== -->
        <!-- EMPTY STATE: Belum Ada Data CPL                         -->
        <!-- ======================================================== -->
        <div class="bg-[#F0F2F5] rounded-[24px] p-12 sm:p-20 border border-gray-200/50 flex flex-col items-center justify-center text-center min-h-[460px] shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <p class="text-sm sm:text-base font-semibold text-gray-800 leading-relaxed max-w-md mx-auto mb-6">
                Kamu belum ada data Capaian Pembelajaran Lulusan (CPL),<br>
                silakan tekan tombol tambah untuk Tambah CPL.
            </p>

            @haspermission('cpl.create')
            <button 
                type="button" 
                class="open-create-cpl-btn inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer"
            >
                <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tambah CPL
            </button>
            @endhaspermission
        </div>
    @else
        <!-- ======================================================== -->
        <!-- ACTION BAR: Filter, Pencarian, & Tombol Tambah           -->
        <!-- ======================================================== -->
        <div class="space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <!-- Search & Filters Form -->
                <form method="GET" action="{{ route('cpl.index') }}" id="cplFilterForm" class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-sm">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </span>
                        <x-input 
                            type="text" 
                            name="search"
                            id="searchCplInput"
                            value="{{ $search }}"
                            placeholder="Cari kode atau deskripsi CPL..." 
                            class="pl-10 text-xs"
                        />
                    </div>

                    <!-- Filter Kategori -->
                    <div class="relative w-full sm:w-48">
                        <select 
                            name="kategori" 
                            id="filterKategoriSelect" 
                            onchange="document.getElementById('cplFilterForm').submit()"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                        >
                            <option value="">Semua Kategori</option>
                            <option value="Sikap" {{ $selectedKategori === 'Sikap' ? 'selected' : '' }}>Sikap</option>
                            <option value="Pengetahuan" {{ $selectedKategori === 'Pengetahuan' ? 'selected' : '' }}>Pengetahuan</option>
                            <option value="Keterampilan Umum" {{ $selectedKategori === 'Keterampilan Umum' ? 'selected' : '' }}>Keterampilan Umum</option>
                            <option value="Keterampilan Khusus" {{ $selectedKategori === 'Keterampilan Khusus' ? 'selected' : '' }}>Keterampilan Khusus</option>
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>

                    <!-- Filter Kurikulum -->
                    <div class="relative w-full sm:w-48">
                        <select 
                            name="kurikulum_id" 
                            id="filterKurikulumSelect" 
                            onchange="document.getElementById('cplFilterForm').submit()"
                            class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                        >
                            <option value="">Semua Kurikulum</option>
                            @foreach($kurikulumList as $k)
                                @php
                                    $kid = $k['id'] ?? '';
                                    $knama = $k['nama_kurikulum'] ?? $k['nama'] ?? 'Kurikulum #'.$kid;
                                @endphp
                                <option value="{{ $kid }}" {{ (string) $selectedKurikulum === (string) $kid ? 'selected' : '' }}>
                                    {{ $knama }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                            <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </span>
                    </div>

                    @if(!empty($search) || !empty($selectedKategori) || !empty($selectedKurikulum))
                        <a 
                            href="{{ route('cpl.index') }}" 
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-500 hover:text-red-600 transition"
                            title="Reset Filter"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Reset
                        </a>
                    @endif
                </form>

                <!-- Tambah CPL Button -->
                @haspermission('cpl.create')
                <div>
                    <button 
                        type="button" 
                        class="open-create-cpl-btn w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah CPL
                    </button>
                </div>
                @endhaspermission
            </div>

            <!-- ======================================================== -->
            <!-- TABLE: Data Capaian Pembelajaran Lulusan (CPL)           -->
            <!-- ======================================================== -->
            <x-table>
                <x-slot:header>
                    <x-table.th class="w-16">No</x-table.th>
                    <x-table.th class="w-32">Kode CPL</x-table.th>
                    <x-table.th class="w-44">Kategori</x-table.th>
                    <x-table.th>Deskripsi CPL</x-table.th>
                    <x-table.th class="w-48">Kurikulum</x-table.th>
                    <x-table.th class="w-28 text-right">Action</x-table.th>
                </x-slot:header>

                @forelse($cplList as $index => $item)
                    @php
                        $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                        $id = $item['id'] ?? '';
                        $kode = $item['kode_cpl'] ?? '-';
                        $kategori = trim((string) ($item['kategori'] ?? '-'));
                        $deskripsi = $item['deskripsi_cpl'] ?? '-';
                        $kId = $item['id_kurikulum'] ?? ($item['kurikulum']['id'] ?? '');
                        $kurikulumNama = $item['kurikulum']['nama_kurikulum'] ?? $item['kurikulum']['nama'] ?? ($kId ? 'Kurikulum #'.$kId : '-');

                        // Badge styling based on category
                        $kategoriLower = strtolower($kategori);
                        $badgeClass = match ($kategoriLower) {
                            'sikap' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'pengetahuan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'keterampilan umum' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'keterampilan khusus' => 'bg-purple-50 text-purple-700 border-purple-200',
                            default => 'bg-gray-50 text-gray-700 border-gray-200',
                        };
                    @endphp
                    <x-table.row class="cpl-row">
                        <!-- No -->
                        <x-table.cell class="text-gray-600 font-medium w-16 align-top pt-4">
                            {{ $rowNumber }}.
                        </x-table.cell>

                        <!-- Kode CPL -->
                        <x-table.cell class="w-32 align-top pt-4">
                            <span class="inline-flex items-center font-mono font-bold text-xs px-2.5 py-1 rounded-lg bg-gray-100 text-gray-900 border border-gray-200">
                                {{ $kode }}
                            </span>
                        </x-table.cell>

                        <!-- Kategori -->
                        <x-table.cell class="w-44 align-top pt-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badgeClass }}">
                                {{ $kategori }}
                            </span>
                        </x-table.cell>

                        <!-- Deskripsi CPL -->
                        <x-table.cell class="align-top pt-4">
                            <p class="text-xs sm:text-sm text-gray-800 leading-relaxed font-normal whitespace-normal">
                                {{ $deskripsi }}
                            </p>
                        </x-table.cell>

                        <!-- Kurikulum -->
                        <x-table.cell class="w-48 align-top pt-4">
                            <span class="text-xs sm:text-sm font-semibold text-gray-700 block">
                                {{ $kurikulumNama }}
                            </span>
                        </x-table.cell>

                        <!-- Action: Edit & Delete -->
                        <x-table.cell class="w-28 align-top pt-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                @haspermission('cpl.update')
                                <!-- Edit Icon (Yellow) -->
                                <button 
                                    type="button" 
                                    class="open-edit-cpl-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                    title="Edit CPL"
                                    data-id="{{ $id }}"
                                    data-action="{{ route('cpl.update', $id) }}"
                                    data-kode="{{ $kode }}"
                                    data-kategori="{{ $kategori }}"
                                    data-deskripsi="{{ $deskripsi }}"
                                    data-kurikulum-id="{{ $kId }}"
                                >
                                    <x-icon.edit class="w-5 h-5" />
                                </button>
                                @endhaspermission

                                @haspermission('cpl.delete')
                                <!-- Delete Icon (Red) -->
                                <button 
                                    type="button" 
                                    class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                    title="Hapus CPL"
                                    data-confirm-delete
                                    data-action="{{ route('cpl.destroy', $id) }}"
                                    data-title="Hapus Data CPL?"
                                    data-message="Apakah Anda yakin ingin menghapus Capaian Pembelajaran Lulusan {{ $kode }}? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                                >
                                    <x-icon.delete class="w-5 h-5" />
                                </button>
                                @endhaspermission
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.empty colspan="6" message="Belum ada data CPL" submessage="Silakan sesuaikan kata kunci pencarian atau klik Tambah CPL." />
                @endforelse
            </x-table>

            <!-- Template Pagination Component: 10 Data Per Halaman -->
            <x-pagination :paginator="$pagination" />
        </div>
    @endif
</div>

<!-- ======================================================== -->
<!-- MODAL: Tambahkan CPL                                      -->
<!-- ======================================================== -->
<div 
    id="createCplModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createCplModalTitle"
>
    <div class="cpl-modal-card relative bg-white rounded-[28px] p-8 max-w-[480px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createCplModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan CPL
        </h3>

        <form action="{{ route('cpl.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Field 1: Kurikulum -->
            <div>
                <label for="createKurikulumCplSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kurikulum
                </label>
                <div class="relative">
                    <select 
                        name="id_kurikulum" 
                        id="createKurikulumCplSelect" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Kurikulum</option>
                        @foreach($kurikulumList as $k)
                            @php
                                $kid = $k['id'] ?? '';
                                $knama = $k['nama_kurikulum'] ?? $k['nama'] ?? 'Kurikulum #'.$kid;
                            @endphp
                            <option value="{{ $kid }}" {{ old('id_kurikulum') == $kid ? 'selected' : '' }}>
                                {{ $knama }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field 2: Kode CPL -->
            <div>
                <label for="createKodeCplInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kode CPL
                </label>
                <input 
                    type="text" 
                    name="kode_cpl" 
                    id="createKodeCplInput" 
                    value="{{ old('kode_cpl') }}"
                    placeholder="Contoh: CPL-01" 
                    maxlength="50" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500 uppercase"
                >
            </div>

            <!-- Field 3: Kategori -->
            <div>
                <label for="createKategoriCplSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kategori
                </label>
                <div class="relative">
                    <select 
                        name="kategori" 
                        id="createKategoriCplSelect" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Kategori</option>
                        <option value="Sikap" {{ old('kategori') === 'Sikap' ? 'selected' : '' }}>Sikap</option>
                        <option value="Pengetahuan" {{ old('kategori') === 'Pengetahuan' ? 'selected' : '' }}>Pengetahuan</option>
                        <option value="Keterampilan Umum" {{ old('kategori') === 'Keterampilan Umum' ? 'selected' : '' }}>Keterampilan Umum</option>
                        <option value="Keterampilan Khusus" {{ old('kategori') === 'Keterampilan Khusus' ? 'selected' : '' }}>Keterampilan Khusus</option>
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field 4: Deskripsi CPL -->
            <div>
                <label for="createDeskripsiCplInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Deskripsi CPL
                </label>
                <textarea 
                    name="deskripsi_cpl" 
                    id="createDeskripsiCplInput" 
                    rows="4"
                    placeholder="Tuliskan rumusan capaian pembelajaran lulusan..." 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500 resize-none"
                >{{ old('deskripsi_cpl') }}</textarea>
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-cpl-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: Edit CPL                                          -->
<!-- ======================================================== -->
<div 
    id="editCplModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editCplModalTitle"
>
    <div class="cpl-modal-card relative bg-white rounded-[28px] p-8 max-w-[480px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editCplModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Edit CPL
        </h3>

        <form id="editCplForm" action="#" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field 1: Kurikulum -->
            <div>
                <label for="editKurikulumCplSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kurikulum
                </label>
                <div class="relative">
                    <select 
                        name="id_kurikulum" 
                        id="editKurikulumCplSelect" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih Kurikulum</option>
                        @foreach($kurikulumList as $k)
                            @php
                                $kid = $k['id'] ?? '';
                                $knama = $k['nama_kurikulum'] ?? $k['nama'] ?? 'Kurikulum #'.$kid;
                            @endphp
                            <option value="{{ $kid }}">{{ $knama }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field 2: Kode CPL -->
            <div>
                <label for="editKodeCplInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kode CPL
                </label>
                <input 
                    type="text" 
                    name="kode_cpl" 
                    id="editKodeCplInput" 
                    placeholder="Contoh: CPL-01" 
                    maxlength="50" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500 uppercase"
                >
            </div>

            <!-- Field 3: Kategori -->
            <div>
                <label for="editKategoriCplSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Kategori
                </label>
                <div class="relative">
                    <select 
                        name="kategori" 
                        id="editKategoriCplSelect" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih Kategori</option>
                        <option value="Sikap">Sikap</option>
                        <option value="Pengetahuan">Pengetahuan</option>
                        <option value="Keterampilan Umum">Keterampilan Umum</option>
                        <option value="Keterampilan Khusus">Keterampilan Khusus</option>
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field 4: Deskripsi CPL -->
            <div>
                <label for="editDeskripsiCplInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Deskripsi CPL
                </label>
                <textarea 
                    name="deskripsi_cpl" 
                    id="editDeskripsiCplInput" 
                    rows="4"
                    placeholder="Tuliskan rumusan capaian pembelajaran lulusan..." 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500 resize-none"
                ></textarea>
            </div>

            <!-- Modal Action Buttons (Batal & Perbarui) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-cpl-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal Helper Functions
        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.cpl-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
            document.body.style.overflow = 'hidden';
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.cpl-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
            document.body.style.overflow = '';
        }

        const createModal = document.getElementById('createCplModal');
        const editModal = document.getElementById('editCplModal');

        // Open Create Modal
        document.querySelectorAll('.open-create-cpl-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                showModal(createModal);
            });
        });

        // Open Edit Modal
        const editForm = document.getElementById('editCplForm');
        const editKurikulumSelect = document.getElementById('editKurikulumCplSelect');
        const editKodeInput = document.getElementById('editKodeCplInput');
        const editKategoriSelect = document.getElementById('editKategoriCplSelect');
        const editDeskripsiInput = document.getElementById('editDeskripsiCplInput');

        document.querySelectorAll('.open-edit-cpl-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const action = this.dataset.action;
                const kurikulumId = this.dataset.kurikulumId;
                const kode = this.dataset.kode;
                const kategori = this.dataset.kategori;
                const deskripsi = this.dataset.deskripsi;

                if (editForm && action) {
                    editForm.action = action;
                }
                if (editKurikulumSelect && kurikulumId) {
                    editKurikulumSelect.value = kurikulumId;
                }
                if (editKodeInput && kode) {
                    editKodeInput.value = kode;
                }
                if (editKategoriSelect && kategori) {
                    editKategoriSelect.value = kategori;
                }
                if (editDeskripsiInput && deskripsi) {
                    editDeskripsiInput.value = deskripsi;
                }

                showModal(editModal);
            });
        });

        // Close Modals
        document.querySelectorAll('.close-cpl-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                hideModal(createModal);
                hideModal(editModal);
            });
        });

        // Click Backdrop to Close
        [createModal, editModal].forEach(modal => {
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        hideModal(modal);
                    }
                });
            }
        });

        // Press Escape to Close
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideModal(createModal);
                hideModal(editModal);
            }
        });
    });
</script>
@endsection
