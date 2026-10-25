@extends('layouts.app')

@section('title', 'Master Data Instrumen')

@section('content')
<div class="space-y-6">

    <x-toast />

    @if (session('success'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm font-semibold text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Bagian Header & Tombol Tambah --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5">
        <div>
            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-additional">
                Master Data Instrumen
            </p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight text-primary">
                Lembaga Akreditasi
            </h2>
            <p class="mt-1 text-xs sm:text-sm text-gray-500 font-medium">
                {{ count($lembagas ?? []) }} data terkait
            </p>
        </div>

        <button 
            type="button"
            onclick="toggleModal('modal-tambah-lembaga')" 
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Lembaga
        </button>
    </div>

    {{-- Daftar Card Lembaga --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($lembagas ?? [] as $lembaga)
            <article class="flex min-h-[160px] flex-col justify-between rounded-2xl border border-[#EAEAEA] bg-white p-6 shadow-xs transition hover:shadow-md">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-primary tracking-tight">
                        {{ $lembaga['nama_lembaga'] ?? '-' }}
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">
                        {{ $lembaga['keterangan'] ?? 'Lembaga Akreditasi' }}
                    </p>
                    @if (!empty($lembaga['versi_instrumen']))
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($lembaga['versi_instrumen'] as $vi)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-100 text-gray-700">
                                    v{{ $vi['nomor_versi'] }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                <div class="mt-5 flex justify-end">
                    <button 
                        type="button"
                        onclick="window.location.href='{{ route('akreditasi.superadmin.instrumen') }}'" 
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-white transition hover:bg-additional cursor-pointer shadow-xs"
                        title="Buka Instrumen"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center text-sm font-medium text-gray-500">
                Belum ada data lembaga akreditasi dari API. Klik tombol "Tambah Lembaga" untuk menambahkan.
            </div>
        @endforelse
    </div>

</div>

{{-- Modal Tambah Lembaga --}}
<div id="modal-tambah-lembaga" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50 p-4">
    <div class="relative w-full max-w-xl">
        <div class="relative rounded-2xl bg-white shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 p-5 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Tambah Lembaga Akreditasi</h3>
                <button type="button" onclick="toggleModal('modal-tambah-lembaga')" class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-xl p-2 transition cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <!-- Body (Form) -->
            <form action="{{ route('akreditasi.superadmin.lembaga_akreditasi.store') }}" method="POST">
                @csrf
                <div class="p-5 sm:p-6 space-y-4">
                    <h4 class="text-sm font-bold text-gray-900">Identitas Lembaga</h4>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Nama Lembaga <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_lembaga" required placeholder="Contoh: LAM-INFOKOM" class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold text-gray-700">Keterangan</label>
                        <textarea name="keterangan" rows="3" placeholder="Deskripsi lengkap mengenai lembaga akreditasi..." class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"></textarea>
                    </div>
                </div>
                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 sm:p-6 bg-gray-50">
                    <button onclick="toggleModal('modal-tambah-lembaga')" type="button" class="rounded-xl bg-white border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Batal</button>
                    <button type="submit" class="rounded-2xl bg-primary px-6 py-2.5 text-sm font-bold text-white hover:bg-additional transition cursor-pointer shadow-sm">Simpan</button>
                </div>
            </form>
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