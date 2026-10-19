@extends('layouts.app')

@section('title')
    <span class="inline-flex items-center gap-2.5 sm:gap-3.5 flex-wrap">
        <a href="{{ route('tenaga-pendidikan.index') }}" class="hover:text-additional transition text-black">Tenaga Pendidikan</a>
        <svg class="w-5 h-5 sm:w-7 sm:h-7 stroke-[3] text-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <span>Detail</span>
    </span>
@endsection

@section('subtitle', $tenagaKependidikan['nama'])

@section('content')
<div class="space-y-2.5 pb-2">
    @if($detailApiError)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-medium text-amber-900" role="alert">
            {{ $detailApiError }}
        </div>
    @endif

    <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-12">
        <section class="flex min-h-[292px] flex-col justify-between overflow-hidden rounded-[7px] bg-[#EDF3F7] lg:col-span-7" aria-label="Identitas tenaga kependidikan">
            <div class="px-6 pt-5 pb-4">
                <h2 class="text-2xl font-bold leading-tight text-black sm:text-[26px]">
                    {{ $tenagaKependidikan['nama'] ?: 'Nama belum tersedia' }}
                </h2>
                <p class="mt-0.5 text-sm font-medium text-gray-600">
                    {{ $tenagaKependidikan['jabatan'] ?: 'Belum diisi' }}
                </p>
            </div>

            <div class="mt-auto divide-y divide-[#AAB5BE]">
                <div class="flex h-[33px] items-center gap-3 px-6 text-xs font-medium text-black">
                    <svg class="h-4 w-4 shrink-0 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.272-.71l1.992-7.302H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" /></svg>
                    <span class="truncate">{{ $tenagaKependidikan['bidang_keahlian'] ?: 'Belum diisi' }}</span>
                </div>
                <div class="flex h-[33px] items-center gap-3 px-6 text-xs font-medium text-black">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v9A2.25 2.25 0 0 1 18.75 16.5H5.25A2.25 2.25 0 0 1 3 14.25v-9ZM8.25 21h7.5M12 16.5V21m-5.25 0h10.5" /></svg>
                    <span class="truncate">{{ $tenagaKependidikan['kualifikasi'] !== '-' ? $tenagaKependidikan['kualifikasi'] : 'Belum diisi' }}</span>
                </div>
                <div class="flex h-[33px] items-center gap-3 px-6 text-xs font-medium text-black">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75 12 13.5l9-6.75M4.5 4.5h15A1.5 1.5 0 0 1 21 6v12a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18V6a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
                    <span class="truncate">{{ $tenagaKependidikan['email'] ?: 'Belum diisi' }}</span>
                </div>
                <div class="flex h-[33px] items-center gap-3 px-6 text-xs font-medium text-black">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 10.5h7.5m-7.5 3.75h4.5M6 3.75h9.75L19.5 7.5v12.75A1.5 1.5 0 0 1 18 21.75H6A1.5 1.5 0 0 1 4.5 20.25V5.25A1.5 1.5 0 0 1 6 3.75Z" /></svg>
                    <span class="truncate">{{ $tenagaKependidikan['status'] ?: 'Belum diisi' }}</span>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-[7px] bg-[#EDF3F7] lg:col-span-5" aria-labelledby="qualificationHeading">
            <div class="flex h-[47px] items-center justify-center bg-[#062B53] px-5">
                <h3 id="qualificationHeading" class="text-sm font-semibold text-white">Kualifikasi</h3>
            </div>
            <div class="max-h-[292px] min-h-[244px] divide-y divide-[#AAB5BE] overflow-y-auto px-3">
                @forelse($kualifikasis as $qualification)
                    <article class="flex min-h-[80px] items-center gap-2.5 py-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-secondary text-[#062B53]">
                            <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3Zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9ZM17 15.99l-5 2.73-5-2.73v-3.72l5 2.73 5-2.73v3.72Z" /></svg>
                        </span>
                        <div class="min-w-0 flex-1 leading-tight">
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-semibold text-black">{{ $qualification['gelar'] ?: 'Pendidikan' }}</span>
                                @if($qualification['jenjang'])
                                    <span class="rounded-[4px] bg-[#062B53] px-1.5 py-0.5 text-[10px] font-bold text-secondary">{{ $qualification['jenjang'] }}</span>
                                @endif
                            </div>
                            <p class="truncate text-[10px] font-medium text-gray-800">{{ $qualification['institusi'] ?: 'Institusi belum tersedia dari API' }}</p>
                            <p class="truncate text-[10px] text-gray-800">
                                {{ $qualification['program_studi'] ?: $tenagaKependidikan['kualifikasi'] }}
                                @if($qualification['tahun_lulus']) - Lulus {{ $qualification['tahun_lulus'] }} @endif
                            </p>
                        </div>
                    </article>
                @empty
                    <div class="flex min-h-[244px] items-center justify-center px-5 text-center text-xs text-gray-600">
                        Jenjang pendidikan Tendik belum tersedia dari API.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="min-h-[290px] overflow-hidden rounded-[7px] bg-[#EDF3F7]" aria-labelledby="certificationHeading">
        <div class="flex h-[47px] items-center justify-between bg-[#062B53] px-4 sm:px-5">
            <h3 id="certificationHeading" class="text-sm font-semibold text-white">Sertifikasi</h3>
            <button type="button" id="openSertifikasiModalBtn" class="inline-flex h-7 items-center gap-1.5 rounded-[6px] bg-[#00649B] px-2.5 text-[10px] font-semibold text-white transition hover:bg-[#00517d]">
                <span class="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-white">
                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </span>
                Tambah Sertifikasi
            </button>
        </div>

        <div class="min-h-[243px] p-4 sm:p-5">
            @if($detailApiError)
                <p class="mb-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900" role="alert">{{ $detailApiError }}</p>
            @endif
            @if(count($sertifikasis) > 0)
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($sertifikasis as $sertifikasi)
                        <article class="flex min-h-[116px] flex-col justify-between rounded-md border border-[#D5DEE5] bg-white p-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h4 class="line-clamp-2 text-sm font-semibold text-black">{{ $sertifikasi['jenis_sertifikasi'] ?? 'Sertifikasi' }}</h4>
                                    <p class="mt-1 text-xs text-gray-700">{{ $sertifikasi['lembaga_penerbit'] ?? 'Lembaga penerbit belum diisi' }}</p>
                                </div>
                                @if(!empty($sertifikasi['id']))
                                    <form method="POST" action="{{ route('tenaga-pendidikan.sertifikasi.destroy', [$tenagaKependidikan['id'], $sertifikasi['id']]) }}" data-confirm-delete data-title="Hapus Sertifikasi?" data-message="Hapus sertifikasi ini? Tindakan ini tidak dapat dibatalkan.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-gray-400 hover:text-rose-600" aria-label="Hapus sertifikasi">
                                            <x-icon.delete class="h-4 w-4" />
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="mt-3 flex justify-between border-t border-gray-100 pt-2 text-[10px] text-gray-500">
                                <span>No: {{ $sertifikasi['nomor_sertifikat'] ?? '-' }}</span>
                                <span>Tahun: {{ $sertifikasi['tahun_sertifikasi'] ?? '-' }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="flex min-h-[210px] items-center justify-center text-center">
                    <p class="text-3xl font-bold text-black sm:text-[36px]">Belum ada sertifikasi</p>
                </div>
            @endif
        </div>
    </section>
</div>

<div id="sertifikasiModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 p-4 opacity-0 pointer-events-none transition-all duration-200" role="dialog" aria-modal="true" aria-labelledby="sertifikasiModalTitle">
    <div id="sertifikasiModalCard" class="max-h-[92vh] w-full max-w-[510px] overflow-y-auto rounded-[12px] bg-white p-6 shadow-2xl transition-all duration-200 scale-95">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h2 id="sertifikasiModalTitle" class="text-lg font-bold text-black">Tambah Sertifikasi</h2>
            <button type="button" class="close-sertifikasi-modal rounded p-1 text-gray-500 hover:bg-gray-100" aria-label="Tutup">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" action="{{ route('tenaga-pendidikan.sertifikasi.store', $tenagaKependidikan['id']) }}" class="mt-4 space-y-3">
            @csrf
            <label class="block text-xs font-semibold text-gray-800">Nama sertifikasi <span class="text-rose-600">*</span>
                <input name="jenis_sertifikasi" value="{{ old('jenis_sertifikasi') }}" required class="mt-1 block w-full rounded-md border-0 bg-[#E0E0E0] px-3 py-2.5 text-sm" placeholder="Nama sertifikasi">
                @error('jenis_sertifikasi')<span class="mt-1 block text-rose-600">{{ $message }}</span>@enderror
            </label>
            <label class="block text-xs font-semibold text-gray-800">Bidang studi
                <input name="bidang_studi" value="{{ old('bidang_studi') }}" class="mt-1 block w-full rounded-md border-0 bg-[#E0E0E0] px-3 py-2.5 text-sm" placeholder="Bidang studi">
            </label>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <label class="block text-xs font-semibold text-gray-800">Nomor sertifikat
                    <input name="nomor_sertifikat" value="{{ old('nomor_sertifikat') }}" class="mt-1 block w-full rounded-md border-0 bg-[#E0E0E0] px-3 py-2.5 text-sm">
                </label>
                <label class="block text-xs font-semibold text-gray-800">Lembaga penerbit
                    <input name="lembaga_penerbit" value="{{ old('lembaga_penerbit') }}" class="mt-1 block w-full rounded-md border-0 bg-[#E0E0E0] px-3 py-2.5 text-sm">
                </label>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <label class="block text-xs font-semibold text-gray-800">Tahun sertifikasi <span class="text-rose-600">*</span>
                    <input name="tahun_sertifikasi" type="number" min="1900" max="{{ now()->year + 1 }}" value="{{ old('tahun_sertifikasi') }}" required class="mt-1 block w-full rounded-md border-0 bg-[#E0E0E0] px-3 py-2.5 text-sm">
                    @error('tahun_sertifikasi')<span class="mt-1 block text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs font-semibold text-gray-800">Tanggal kedaluwarsa
                    <input name="tanggal_kedaluwarsa" type="date" value="{{ old('tanggal_kedaluwarsa') }}" class="mt-1 block w-full rounded-md border-0 bg-[#E0E0E0] px-3 py-2.5 text-sm">
                </label>
            </div>
            <label class="block text-xs font-semibold text-gray-800">Evidence sertifikat (PDF/JPG/PNG, maks 10 MB)
                <input name="evidence_file" type="file" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full rounded-md border-0 bg-[#E0E0E0] px-3 py-2 text-xs">
                @error('evidence_file')<span class="mt-1 block text-rose-600">{{ $message }}</span>@enderror
            </label>
            <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                <button type="button" class="close-sertifikasi-modal rounded-md bg-[#D9D9D9] px-4 py-2 text-sm font-semibold text-black">Batal</button>
                <button type="submit" id="submitSertifikasiBtn" class="rounded-md bg-[#00649B] px-4 py-2 text-sm font-semibold text-white">Simpan Sertifikasi</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('sertifikasiModal');
    const card = document.getElementById('sertifikasiModalCard');
    const openButton = document.getElementById('openSertifikasiModalBtn');
    const closeButtons = document.querySelectorAll('.close-sertifikasi-modal');
    const form = modal?.querySelector('form');
    const submitButton = document.getElementById('submitSertifikasiBtn');

    function setOpen(isOpen) {
        if (!modal || !card) return;
        modal.classList.toggle('opacity-0', !isOpen);
        modal.classList.toggle('pointer-events-none', !isOpen);
        modal.classList.toggle('opacity-100', isOpen);
        card.classList.toggle('scale-95', !isOpen);
        card.classList.toggle('scale-100', isOpen);
        document.body.classList.toggle('overflow-hidden', isOpen);
        if (isOpen) modal.querySelector('[name="jenis_sertifikasi"]')?.focus();
    }

    openButton?.addEventListener('click', () => setOpen(true));
    closeButtons.forEach(button => button.addEventListener('click', () => setOpen(false)));
    modal?.addEventListener('click', event => {
        if (event.target === modal) setOpen(false);
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') setOpen(false);
    });
    form?.addEventListener('submit', () => {
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Menyimpan...';
        }
    });

    @if($errors->has('jenis_sertifikasi') || $errors->has('tahun_sertifikasi') || $errors->has('evidence_file'))
        setOpen(true);
    @endif
});
</script>
@endsection
