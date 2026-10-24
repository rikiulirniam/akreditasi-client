@extends('layouts.app')

@php
    $prestasiId = $prestasi['id'] ?? '';
    $namaMahasiswa = $mahasiswa['nama_mahasiswa'] ?? $mahasiswa['nama'] ?? $prestasi['mahasiswa']['nama_mahasiswa'] ?? '-';
    $nimMahasiswa = $mahasiswa['nim_display'] ?? $mahasiswa['nim'] ?? $prestasi['mahasiswa']['nim'] ?? '-';
    $namaAktivitas = $prestasi['nama_aktivitas'] ?? '-';
    $tahunPrestasi = $prestasi['tahun'] ?? '-';
    $jenisPrestasi = $jenisPrestasi ?? '-';
@endphp

@section('title', 'Detail Prestasi Mahasiswa')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-gray-900">Detail Prestasi Mahasiswa</h2>
            <p class="mt-1 text-sm text-gray-500">Informasi prestasi beserta evidence dan jenisnya.</p>
        </div>
        <a href="{{ route('prestasi-mahasiswa.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white-form px-4 py-2.5 text-sm font-bold text-gray-800 transition hover:bg-gray-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    @foreach($relatedErrors as $error)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
            {{ $error }}
        </div>
    @endforeach

    <section class="rounded-2xl border border-gray-200/90 bg-white p-6 shadow-xs sm:p-8">
        <div class="flex flex-col gap-4 border-b border-gray-100 pb-5 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Prestasi Mahasiswa</span>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-gray-900">{{ $namaAktivitas }}</h1>
            </div>
            <button type="button" id="openEditPrestasiDetailBtn" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-amber-600">
                <x-icon.edit class="h-4 w-4" />
                Edit Prestasi
            </button>
        </div>

        <dl class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-gray-50 p-4">
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-500">Mahasiswa</dt>
                <dd class="mt-1 font-bold text-gray-900">{{ $namaMahasiswa }}</dd>
            </div>
            <div class="rounded-xl bg-gray-50 p-4">
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-500">NIM</dt>
                <dd class="mt-1 font-mono font-semibold text-gray-900">{{ $nimMahasiswa }}</dd>
            </div>
            <div class="rounded-xl bg-gray-50 p-4">
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-500">Tahun</dt>
                <dd class="mt-1 font-bold text-gray-900">{{ $tahunPrestasi }}</dd>
            </div>
            <div class="rounded-xl bg-gray-50 p-4">
                <dt class="text-xs font-bold uppercase tracking-wide text-gray-500">Jenis Prestasi</dt>
                <dd class="mt-1 font-bold text-gray-900">{{ $jenisPrestasi }}</dd>
            </div>
        </dl>
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-gray-900">Evidence Prestasi</h3>
                    <p class="mt-1 text-sm text-gray-500">Sertifikat atau tautan bukti pendukung.</p>
                </div>
            </div>

            <form action="{{ route('prestasi-mahasiswa.evidence.store', $prestasiId) }}" method="POST" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs">
                @csrf
                <label for="newEvidenceInput" class="mb-2 block text-xs font-bold text-gray-900">Tambah Evidence</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input id="newEvidenceInput" type="text" name="evidence" maxlength="2048" required placeholder="Masukkan nama file atau link evidence" class="min-w-0 flex-1 rounded-[10px] bg-gray-100 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white transition hover:bg-additional">Tambah</button>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
                <x-table>
                    <x-slot:header>
                        <x-table.th>Evidence</x-table.th>
                        <x-table.th class="w-24 text-right">Action</x-table.th>
                    </x-slot:header>

                    @forelse($evidenceList as $evidence)
                        <x-table.row>
                            <x-table.cell>
                                @php($evidenceValue = $evidence['evidence'] ?? '-')
                                @if(filter_var($evidenceValue, FILTER_VALIDATE_URL))
                                    <a href="{{ $evidenceValue }}" target="_blank" rel="noopener noreferrer" class="break-all text-sm font-medium text-primary underline hover:text-additional">
                                        {{ $evidenceValue }}
                                    </a>
                                @else
                                    <span class="break-all text-sm font-medium text-gray-800">{{ $evidenceValue }}</span>
                                @endif
                            </x-table.cell>
                            <x-table.cell class="w-24">
                                <div class="flex items-center justify-end gap-3">
                                    <button type="button" class="edit-related-record text-amber-500 transition hover:text-amber-600" title="Edit Evidence" data-title="Edit Evidence" data-field="evidence" data-value="{{ $evidenceValue }}" data-action="{{ route('prestasi-mahasiswa.evidence.update', [$prestasiId, $evidence['id']]) }}">
                                        <x-icon.edit class="h-5 w-5" />
                                    </button>
                                    <button type="button" class="text-red-600 transition hover:text-red-700" title="Hapus Evidence" data-confirm-delete data-action="{{ route('prestasi-mahasiswa.evidence.destroy', [$prestasiId, $evidence['id']]) }}" data-title="Hapus Evidence?" data-message="Evidence ini akan dihapus dari prestasi mahasiswa.">
                                        <x-icon.delete class="h-5 w-5" />
                                    </button>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.empty colspan="2" message="Belum ada evidence" submessage="Tambahkan sertifikat atau tautan bukti prestasi." />
                    @endforelse
                </x-table>
            </div>
        </section>

        <section class="space-y-4">
            <div>
                <h3 class="text-lg font-bold tracking-tight text-gray-900">Jenis Prestasi</h3>
                <p class="mt-1 text-sm text-gray-500">Kategori untuk mengelompokkan prestasi mahasiswa.</p>
            </div>

            <form action="{{ route('prestasi-mahasiswa.jenis.store', $prestasiId) }}" method="POST" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs">
                @csrf
                <label for="newPrestasiJenisInput" class="mb-2 block text-xs font-bold text-gray-900">Tambah Jenis</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input id="newPrestasiJenisInput" type="text" name="jenis" maxlength="255" required placeholder="Contoh: Akademik atau Non-akademik" class="min-w-0 flex-1 rounded-[10px] bg-gray-100 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white transition hover:bg-additional">Tambah</button>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
                <x-table>
                    <x-slot:header>
                        <x-table.th>Jenis</x-table.th>
                        <x-table.th class="w-24 text-right">Action</x-table.th>
                    </x-slot:header>

                    @forelse($jenisList as $jenisItem)
                        <x-table.row>
                            <x-table.cell>
                                <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                                    {{ $jenisItem['jenis'] ?? '-' }}
                                </span>
                            </x-table.cell>
                            <x-table.cell class="w-24">
                                <div class="flex items-center justify-end gap-3">
                                    <button type="button" class="edit-related-record text-amber-500 transition hover:text-amber-600" title="Edit Jenis" data-title="Edit Jenis Prestasi" data-field="jenis" data-value="{{ $jenisItem['jenis'] ?? '' }}" data-action="{{ route('prestasi-mahasiswa.jenis.update', [$prestasiId, $jenisItem['id']]) }}">
                                        <x-icon.edit class="h-5 w-5" />
                                    </button>
                                    <button type="button" class="text-red-600 transition hover:text-red-700" title="Hapus Jenis" data-confirm-delete data-action="{{ route('prestasi-mahasiswa.jenis.destroy', [$prestasiId, $jenisItem['id']]) }}" data-title="Hapus Jenis Prestasi?" data-message="Jenis ini akan dihapus dari prestasi mahasiswa.">
                                        <x-icon.delete class="h-5 w-5" />
                                    </button>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.empty colspan="2" message="Belum ada jenis prestasi" submessage="Tambahkan jenis untuk melengkapi data prestasi." />
                    @endforelse
                </x-table>
            </div>
        </section>
    </div>
</div>

<div id="editPrestasiDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 p-4 opacity-0 pointer-events-none transition-all duration-200" role="dialog" aria-modal="true" aria-labelledby="editPrestasiDetailTitle">
    <div class="edit-prestasi-detail-card max-h-[92vh] w-full max-w-[460px] transform overflow-y-auto rounded-[28px] bg-white p-8 shadow-2xl transition-all duration-200 scale-95">
        <h3 id="editPrestasiDetailTitle" class="mb-6 text-center text-xl font-bold text-gray-900">Perbarui Prestasi Mahasiswa</h3>
        <form action="{{ route('prestasi-mahasiswa.update', $prestasiId) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="editPrestasiMahasiswaId" class="mb-1.5 block text-xs font-bold text-gray-900">Mahasiswa</label>
                <select id="editPrestasiMahasiswaId" name="id_mahasiswa" required class="w-full rounded-[10px] bg-gray-100 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    @if(! collect($mahasiswaList)->contains(fn ($item) => (string) ($item['id'] ?? '') === (string) ($prestasi['id_mahasiswa'] ?? '')))
                        <option value="{{ $prestasi['id_mahasiswa'] ?? '' }}" selected>{{ $namaMahasiswa }} — {{ $nimMahasiswa }}</option>
                    @endif
                    @foreach($mahasiswaList as $mahasiswaItem)
                        <option value="{{ $mahasiswaItem['id'] ?? '' }}" {{ (string) ($mahasiswaItem['id'] ?? '') === (string) ($prestasi['id_mahasiswa'] ?? '') ? 'selected' : '' }}>
                            {{ $mahasiswaItem['nama_mahasiswa'] ?? $mahasiswaItem['nama'] ?? 'Mahasiswa' }} — {{ $mahasiswaItem['nim_display'] ?? $mahasiswaItem['nim'] ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="editPrestasiAktivitas" class="mb-1.5 block text-xs font-bold text-gray-900">Nama Aktivitas</label>
                <input id="editPrestasiAktivitas" type="text" name="nama_aktivitas" value="{{ $namaAktivitas }}" maxlength="255" required class="w-full rounded-[10px] bg-gray-100 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>
            <div>
                <label for="editPrestasiJenis" class="mb-1.5 block text-xs font-bold text-gray-900">Jenis Prestasi</label>
                <input id="editPrestasiJenis" type="text" name="jenis" value="{{ $jenisPrestasi !== '-' ? $jenisPrestasi : '' }}" maxlength="255" required class="w-full rounded-[10px] bg-gray-100 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>
            <div>
                <label for="editPrestasiTahun" class="mb-1.5 block text-xs font-bold text-gray-900">Tahun</label>
                <input id="editPrestasiTahun" type="number" name="tahun" value="{{ $tahunPrestasi }}" min="1900" max="2100" required class="w-full rounded-[10px] bg-gray-100 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>
            <div>
                <label for="editPrestasiCertificateDetail" class="mb-1.5 block text-xs font-bold text-gray-900">Sertifikat (opsional)</label>
                <input id="editPrestasiCertificateDetail" type="file" name="certificate" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-[10px] bg-gray-100 px-3 py-2.5 text-sm text-gray-700">
            </div>
            <div class="grid grid-cols-2 gap-4 pt-3">
                <button type="button" id="closeEditPrestasiDetailBtn" class="w-full rounded-xl bg-gray-200 px-5 py-3 text-sm font-bold text-gray-800 transition hover:bg-gray-300">Batal</button>
                <button type="submit" class="w-full rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white transition hover:bg-additional">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="editRelatedRecordModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/45 p-4 opacity-0 pointer-events-none transition-all duration-200" role="dialog" aria-modal="true" aria-labelledby="editRelatedRecordTitle">
    <div class="edit-related-record-card w-full max-w-[460px] transform rounded-[28px] bg-white p-8 shadow-2xl transition-all duration-200 scale-95">
        <h3 id="editRelatedRecordTitle" class="mb-6 text-center text-xl font-bold text-gray-900"></h3>
        <form id="editRelatedRecordForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label id="editRelatedRecordLabel" for="editRelatedRecordInput" class="mb-1.5 block text-xs font-bold text-gray-900"></label>
                <input id="editRelatedRecordInput" type="text" name="" maxlength="2048" required class="w-full rounded-[10px] bg-gray-100 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>
            <div class="grid grid-cols-2 gap-4 pt-3">
                <button type="button" id="closeEditRelatedRecordBtn" class="w-full rounded-xl bg-gray-200 px-5 py-3 text-sm font-bold text-gray-800 transition hover:bg-gray-300">Batal</button>
                <button type="submit" class="w-full rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white transition hover:bg-additional">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function setDialogVisible(dialog, visible) {
            const card = dialog.querySelector('.edit-prestasi-detail-card, .edit-related-record-card');
            dialog.classList.toggle('opacity-0', !visible);
            dialog.classList.toggle('pointer-events-none', !visible);
            dialog.classList.toggle('opacity-100', visible);
            card.classList.toggle('scale-95', !visible);
            card.classList.toggle('scale-100', visible);
        }

        const prestasiModal = document.getElementById('editPrestasiDetailModal');
        const relatedModal = document.getElementById('editRelatedRecordModal');
        document.getElementById('openEditPrestasiDetailBtn').addEventListener('click', () => setDialogVisible(prestasiModal, true));
        document.getElementById('closeEditPrestasiDetailBtn').addEventListener('click', () => setDialogVisible(prestasiModal, false));
        document.getElementById('closeEditRelatedRecordBtn').addEventListener('click', () => setDialogVisible(relatedModal, false));

        document.querySelectorAll('.edit-related-record').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('editRelatedRecordTitle').textContent = this.dataset.title;
                document.getElementById('editRelatedRecordLabel').textContent = this.dataset.field === 'evidence' ? 'Evidence' : 'Jenis';
                const input = document.getElementById('editRelatedRecordInput');
                input.name = this.dataset.field;
                input.value = this.dataset.value;
                input.type = this.dataset.field === 'evidence' && this.dataset.value.startsWith('http') ? 'url' : 'text';
                document.getElementById('editRelatedRecordForm').action = this.dataset.action;
                setDialogVisible(relatedModal, true);
            });
        });

        [prestasiModal, relatedModal].forEach(dialog => {
            dialog.addEventListener('click', event => {
                if (event.target === dialog) setDialogVisible(dialog, false);
            });
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                setDialogVisible(prestasiModal, false);
                setDialogVisible(relatedModal, false);
            }
        });
    });
</script>
@endsection
