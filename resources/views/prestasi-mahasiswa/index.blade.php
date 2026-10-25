@extends('layouts.app')

@section('title', 'Prestasi Mahasiswa')

@section('content')
@php
    $programStudiOptions = collect($assignmentOptions)->unique('prodi_id')->sortBy('prodi')->values();
    $jenisOptions = count($jenisOptions) > 0 ? $jenisOptions : ['Akademik', 'Non-Akademik'];
@endphp

<div class="space-y-6">
    @foreach($errorMessages as $errorMessage)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
            {{ $errorMessage }}
        </div>
    @endforeach

    @if($prestasiList === [] && $search === '' && $errorMessages === [])
        <div class="flex min-h-[460px] flex-col items-center justify-center rounded-[24px] border border-gray-200/80 bg-[#F3F3F3] p-10 text-center">
            <p class="mb-5 max-w-xl text-sm font-medium leading-relaxed text-gray-800">
                Kamu belum ada data Prestasi Mahasiswa,<br>
                silahkan tekan tombol tambah untuk Tambah Prestasi Mahasiswa.
            </p>
            <button
                type="button"
                class="open-create-prestasi-btn inline-flex items-center gap-2 rounded-xl bg-additional px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary disabled:cursor-not-allowed disabled:opacity-50"
                {{ ! $formDataReady || $assignmentOptions === [] ? 'disabled' : '' }}
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Tambah Prestasi Mahasiswa
            </button>
        </div>
    @else
        <div class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form action="{{ route('prestasi-mahasiswa.index') }}" method="GET" class="relative w-full max-w-sm">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.2-5.2m0 0A7.5 7.5 0 1 0 5.2 5.2a7.5 7.5 0 0 0 10.6 10.6Z" />
                        </svg>
                    </span>
                    <x-input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari Nama"
                        class="pl-10 text-xs"
                    />
                </form>
                <button
                    type="button"
                    id="openCreatePrestasiModalBtn"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-additional px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary disabled:cursor-not-allowed disabled:opacity-50"
                    {{ ! $formDataReady || $assignmentOptions === [] ? 'disabled' : '' }}
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Tambah Prestasi Mahasiswa
                </button>
            </div>

            <x-table>
                <x-slot:header>
                    <x-table.th class="w-14 text-center">No</x-table.th>
                    <x-table.th>Nama</x-table.th>
                    <x-table.th>NIM</x-table.th>
                    <x-table.th>Jenis Prestasi</x-table.th>
                    <x-table.th class="w-24">Tahun</x-table.th>
                    <x-table.th class="w-32">Bukti Prestasi</x-table.th>
                    <x-table.th class="w-24 text-right">Action</x-table.th>
                </x-slot:header>

                @forelse($prestasiList as $index => $item)
                    @php
                        $id = $item['id'] ?? '';
                        $nama = $item['nama_mahasiswa'] ?? '-';
                        $nim = $item['nim'] ?? '-';
                        $aktivitas = $item['nama_aktivitas'] ?? '-';
                        $jenis = $item['jenis'] ?? '-';
                        $tahun = $item['tahun'] ?? '-';
                        $evidence = $item['evidence'] ?? '';
                        $evidenceUrl = filter_var($evidence, FILTER_VALIDATE_URL) ? $evidence : ($evidence !== '' ? \Illuminate\Support\Facades\Storage::disk('public')->url($evidence) : null);
                    @endphp
                    <x-table.row>
                        <x-table.cell class="w-14 text-center text-gray-600">
                            {{ $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']) }}.
                        </x-table.cell>
                        <x-table.cell>
                            <span class="block text-sm font-medium text-gray-900">{{ $nama }}</span>
                            <span class="mt-0.5 block text-xs text-gray-500">{{ $aktivitas }}</span>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="font-mono text-sm text-gray-700">{{ $nim }}</span>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="text-sm font-medium text-gray-800">{{ $jenis }}</span>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="text-sm text-gray-700">{{ $tahun }}</span>
                        </x-table.cell>
                        <x-table.cell>
                            @if($evidenceUrl)
                                <a href="{{ $evidenceUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-additional hover:text-primary hover:underline">
                                    Lihat Bukti
                                </a>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </x-table.cell>
                        <x-table.cell class="w-24">
                            <div class="flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    class="open-edit-prestasi-btn text-amber-500 transition hover:text-amber-600"
                                    title="Edit Prestasi"
                                    data-action="{{ route('prestasi-mahasiswa.update', $id) }}"
                                    data-mahasiswa-id="{{ $item['id_mahasiswa'] ?? '' }}"
                                    data-aktivitas="{{ $aktivitas }}"
                                    data-jenis="{{ $jenis !== '-' ? $jenis : '' }}"
                                    data-tahun="{{ $tahun }}"
                                    data-evidence="{{ $evidenceUrl ?? '' }}"
                                >
                                    <x-icon.edit class="h-5 w-5" />
                                </button>
                                <button
                                    type="button"
                                    class="text-red-600 transition hover:text-red-700"
                                    title="Hapus Prestasi"
                                    data-confirm-delete
                                    data-action="{{ route('prestasi-mahasiswa.destroy', $id) }}"
                                    data-title="Hapus Data Prestasi Mahasiswa?"
                                    data-message="Apakah Anda yakin ingin menghapus prestasi {{ $aktivitas }} milik {{ $nama }}? Tindakan ini bersifat permanen."
                                >
                                    <x-icon.delete class="h-5 w-5" />
                                </button>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.empty colspan="7" message="Data prestasi tidak ditemukan" submessage="Coba gunakan kata kunci pencarian lain." />
                @endforelse
            </x-table>

            <x-pagination :paginator="$pagination" />
        </div>
    @endif
</div>

<div id="prestasiFormModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 p-4 opacity-0 pointer-events-none transition-all duration-200" role="dialog" aria-modal="true" aria-labelledby="prestasiFormTitle">
    <div class="prestasi-modal-card max-h-[92vh] w-full max-w-[460px] transform overflow-y-auto rounded-[18px] bg-white p-7 shadow-2xl transition-all duration-200 scale-95">
        <h3 id="prestasiFormTitle" class="mb-5 text-center text-lg font-bold tracking-tight text-gray-900">Tambahkan Prestasi Mahasiswa</h3>

        <div class="mb-5 flex items-center justify-center gap-3" aria-label="Tahap formulir">
            <span id="prestasiStepOneIndicator" class="flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 text-xs font-bold text-gray-900">1</span>
            <span class="h-px w-16 bg-gray-400"></span>
            <span id="prestasiStepTwoIndicator" class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-300 text-xs font-bold text-gray-700">2</span>
        </div>

        <form id="prestasiForm" action="{{ route('prestasi-mahasiswa.store') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="_method" id="prestasiFormMethod" value="POST">

            <div id="prestasiStepOne" class="space-y-3">
                <div>
                    <label for="prestasiProdiSelect" class="mb-1 block text-xs font-bold text-gray-900">Program Studi</label>
                    <select id="prestasiProdiSelect" required class="w-full appearance-none rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">Pilih Prodi</option>
                        @foreach($programStudiOptions as $programStudi)
                            <option value="{{ $programStudi['prodi_id'] }}">{{ $programStudi['prodi'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="prestasiKelasSelect" class="mb-1 block text-xs font-bold text-gray-900">Kelas</label>
                    <select id="prestasiKelasSelect" required disabled class="w-full appearance-none rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:opacity-70">
                        <option value="">Pilih Kelas</option>
                    </select>
                </div>
                <div>
                    <label for="prestasiAngkatanSelect" class="mb-1 block text-xs font-bold text-gray-900">Angkatan</label>
                    <select id="prestasiAngkatanSelect" required disabled class="w-full appearance-none rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:opacity-70">
                        <option value="">Pilih Angkatan</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-4">
                    <button type="button" id="cancelPrestasiWizardBtn" class="w-full rounded-md bg-gray-200 px-5 py-2.5 text-sm font-bold text-gray-900 transition hover:bg-gray-300">Batal</button>
                    <button type="button" id="continuePrestasiWizardBtn" class="w-full rounded-md bg-additional px-5 py-2.5 text-sm font-bold text-white transition hover:bg-primary disabled:cursor-not-allowed disabled:opacity-50" disabled>Lanjut</button>
                </div>
            </div>

            <div id="prestasiStepTwo" class="hidden space-y-3">
                <input type="hidden" id="prestasiAngkatanKelasId" name="angkatan_kelas_id">

                <div>
                    <label for="prestasiMahasiswaSelect" class="mb-1 block text-xs font-bold text-gray-900">NIM – Nama</label>
                    <select id="prestasiMahasiswaSelect" name="id_mahasiswa" required disabled class="w-full rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:opacity-70">
                        <option value="">Pilih NIM – Nama</option>
                    </select>
                </div>
                <div>
                    <label for="prestasiAktivitasInput" class="mb-1 block text-xs font-bold text-gray-900">Nama Kegiatan</label>
                    <input id="prestasiAktivitasInput" type="text" name="nama_aktivitas" maxlength="255" required placeholder="Isi Nama Kegiatan" class="w-full rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>
                <div>
                    <label for="prestasiJenisSelect" class="mb-1 block text-xs font-bold text-gray-900">Jenis Prestasi</label>
                    <select id="prestasiJenisSelect" name="jenis" required class="w-full rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">Pilih Jenis Prestasi</option>
                        @foreach($jenisOptions as $jenis)
                            <option value="{{ $jenis }}">{{ $jenis }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="prestasiTahunInput" class="mb-1 block text-xs font-bold text-gray-900">Tahun</label>
                    <input id="prestasiTahunInput" type="number" name="tahun" min="1900" max="2100" value="{{ date('Y') }}" required class="w-full rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>
                <div>
                    <label for="prestasiEvidenceInput" class="mb-1 block text-xs font-bold text-gray-900">Link Sertifikat</label>
                    <input id="prestasiEvidenceInput" type="url" name="evidence" maxlength="2048" required placeholder="https://contoh.com/sertifikat" class="w-full rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>
                <div class="grid grid-cols-2 gap-3 pt-4">
                    <button type="button" id="backPrestasiWizardBtn" class="w-full rounded-md bg-gray-200 px-5 py-2.5 text-sm font-bold text-gray-900 transition hover:bg-gray-300">Kembali</button>
                    <button type="submit" class="w-full rounded-md bg-additional px-5 py-2.5 text-sm font-bold text-white transition hover:bg-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="editPrestasiModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 p-4 opacity-0 pointer-events-none transition-all duration-200" role="dialog" aria-modal="true" aria-labelledby="editPrestasiTitle">
    <div class="edit-prestasi-card max-h-[92vh] w-full max-w-[460px] transform overflow-y-auto rounded-[18px] bg-white p-7 shadow-2xl transition-all duration-200 scale-95">
        <h3 id="editPrestasiTitle" class="mb-6 text-center text-lg font-bold text-gray-900">Update Prestasi Mahasiswa</h3>
        <form id="editPrestasiForm" method="POST" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label for="editPrestasiMahasiswa" class="mb-1 block text-xs font-bold text-gray-900">NIM – Nama</label>
                <select id="editPrestasiMahasiswa" name="id_mahasiswa" required class="w-full rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <option value="">Pilih NIM – Nama</option>
                    @foreach($mahasiswaList as $mahasiswaItem)
                        <option value="{{ $mahasiswaItem['id'] ?? '' }}">
                            {{ $mahasiswaItem['nim_display'] ?? $mahasiswaItem['nim'] ?? '-' }} – {{ $mahasiswaItem['nama_mahasiswa'] ?? $mahasiswaItem['nama'] ?? 'Mahasiswa' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="editPrestasiAktivitas" class="mb-1 block text-xs font-bold text-gray-900">Nama Kegiatan</label>
                <input id="editPrestasiAktivitas" type="text" name="nama_aktivitas" maxlength="255" required placeholder="Isi Nama Kegiatan" class="w-full rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>
            <div>
                <label for="editPrestasiJenis" class="mb-1 block text-xs font-bold text-gray-900">Jenis Prestasi</label>
                <select id="editPrestasiJenis" name="jenis" required class="w-full rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <option value="">Pilih Jenis Prestasi</option>
                    @foreach($jenisOptions as $jenis)
                        <option value="{{ $jenis }}">{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="editPrestasiTahun" class="mb-1 block text-xs font-bold text-gray-900">Tahun</label>
                <input id="editPrestasiTahun" type="number" name="tahun" min="1900" max="2100" required class="w-full rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>
            <div>
                <label for="editPrestasiEvidence" class="mb-1 block text-xs font-bold text-gray-900">Link Sertifikat (opsional)</label>
                <input id="editPrestasiEvidence" type="url" name="evidence" maxlength="2048" placeholder="https://contoh.com/sertifikat" class="w-full rounded-md bg-gray-200 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>
            <a id="editPrestasiExistingEvidence" class="hidden break-all text-xs font-medium text-additional hover:underline" target="_blank" rel="noopener noreferrer">Lihat sertifikat saat ini</a>
            <div class="grid grid-cols-2 gap-3 pt-4">
                <button type="button" id="closeEditPrestasiBtn" class="w-full rounded-md bg-gray-200 px-5 py-2.5 text-sm font-bold text-gray-900 transition hover:bg-gray-300">Batal</button>
                <button type="submit" class="w-full rounded-md bg-additional px-5 py-2.5 text-sm font-bold text-white transition hover:bg-primary">Perbarui</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const assignmentOptions = @json($assignmentOptions);
        const studentOptions = @json($studentOptions);
        const modal = document.getElementById('prestasiFormModal');
        const modalCard = modal.querySelector('.prestasi-modal-card');
        const form = document.getElementById('prestasiForm');
        const prodiSelect = document.getElementById('prestasiProdiSelect');
        const kelasSelect = document.getElementById('prestasiKelasSelect');
        const angkatanSelect = document.getElementById('prestasiAngkatanSelect');
        const cohortInput = document.getElementById('prestasiAngkatanKelasId');
        const mahasiswaSelect = document.getElementById('prestasiMahasiswaSelect');
        const stepOne = document.getElementById('prestasiStepOne');
        const stepTwo = document.getElementById('prestasiStepTwo');
        const nextButton = document.getElementById('continuePrestasiWizardBtn');
        const storeUrl = @json(route('prestasi-mahasiswa.store'));
        const editModal = document.getElementById('editPrestasiModal');
        const editCard = editModal.querySelector('.edit-prestasi-card');

        function setDialogVisible(dialog, card, visible) {
            dialog.classList.toggle('opacity-0', !visible);
            dialog.classList.toggle('pointer-events-none', !visible);
            dialog.classList.toggle('opacity-100', visible);
            card.classList.toggle('scale-95', !visible);
            card.classList.toggle('scale-100', visible);
        }

        function setOptions(select, placeholder, items, valueKey, labelKey) {
            select.replaceChildren(new Option(placeholder, ''));
            const usedValues = new Set();
            items.forEach(item => {
                const value = String(item[valueKey] ?? '');
                if (!value || usedValues.has(value)) return;
                usedValues.add(value);
                select.add(new Option(item[labelKey], value));
            });
            select.disabled = usedValues.size === 0;
        }

        function resetWizard() {
            form.reset();
            form.action = storeUrl;
            document.getElementById('prestasiFormMethod').value = 'POST';
            stepOne.classList.remove('hidden');
            stepTwo.classList.add('hidden');
            document.getElementById('prestasiStepOneIndicator').className = 'flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 text-xs font-bold text-gray-900';
            document.getElementById('prestasiStepTwoIndicator').className = 'flex h-6 w-6 items-center justify-center rounded-full bg-gray-300 text-xs font-bold text-gray-700';
            kelasSelect.replaceChildren(new Option('Pilih Kelas', ''));
            kelasSelect.disabled = true;
            angkatanSelect.replaceChildren(new Option('Pilih Angkatan', ''));
            angkatanSelect.disabled = true;
            mahasiswaSelect.replaceChildren(new Option('Pilih NIM – Nama', ''));
            mahasiswaSelect.disabled = true;
            cohortInput.value = '';
            nextButton.disabled = true;
            document.getElementById('prestasiTahunInput').value = new Date().getFullYear();
        }

        function closeCreateModal() {
            setDialogVisible(modal, modalCard, false);
        }

        function openCreateModal() {
            resetWizard();
            setDialogVisible(modal, modalCard, true);
        }

        document.getElementById('openCreatePrestasiModalBtn')?.addEventListener('click', openCreateModal);
        document.querySelectorAll('.open-create-prestasi-btn').forEach(button => button.addEventListener('click', openCreateModal));
        document.getElementById('cancelPrestasiWizardBtn').addEventListener('click', closeCreateModal);

        prodiSelect.addEventListener('change', function () {
            const matchingAssignments = assignmentOptions.filter(item => item.prodi_id === this.value);
            setOptions(kelasSelect, 'Pilih Kelas', matchingAssignments, 'kelas_id', 'kelas');
            angkatanSelect.replaceChildren(new Option('Pilih Angkatan', ''));
            angkatanSelect.disabled = true;
            nextButton.disabled = true;
        });

        kelasSelect.addEventListener('change', function () {
            const matchingAssignments = assignmentOptions.filter(item => item.prodi_id === prodiSelect.value && item.kelas_id === this.value);
            setOptions(angkatanSelect, 'Pilih Angkatan', matchingAssignments, 'angkatan_id', 'angkatan');
            nextButton.disabled = true;
        });

        angkatanSelect.addEventListener('change', function () {
            const assignment = assignmentOptions.find(item => item.prodi_id === prodiSelect.value
                && item.kelas_id === kelasSelect.value
                && item.angkatan_id === this.value);
            cohortInput.value = assignment?.id ?? '';
            const students = studentOptions.filter(item => item.assignment_id === cohortInput.value);
            setOptions(mahasiswaSelect, 'Pilih NIM – Nama', students, 'id', 'label');
            nextButton.disabled = ! assignment || students.length === 0;
        });

        nextButton.addEventListener('click', function () {
            if (!prodiSelect.value || !kelasSelect.value || !angkatanSelect.value || !cohortInput.value || mahasiswaSelect.disabled) return;
            stepOne.classList.add('hidden');
            stepTwo.classList.remove('hidden');
            document.getElementById('prestasiStepOneIndicator').className = 'flex h-6 w-6 items-center justify-center rounded-full bg-gray-300 text-xs font-bold text-gray-700';
            document.getElementById('prestasiStepTwoIndicator').className = 'flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 text-xs font-bold text-gray-900';
        });

        document.getElementById('backPrestasiWizardBtn').addEventListener('click', function () {
            stepTwo.classList.add('hidden');
            stepOne.classList.remove('hidden');
            document.getElementById('prestasiStepOneIndicator').className = 'flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 text-xs font-bold text-gray-900';
            document.getElementById('prestasiStepTwoIndicator').className = 'flex h-6 w-6 items-center justify-center rounded-full bg-gray-300 text-xs font-bold text-gray-700';
        });

        [modal, editModal].forEach(dialog => {
            let pointerStartedOnBackdrop = false;

            dialog.addEventListener('pointerdown', event => {
                pointerStartedOnBackdrop = event.target === dialog;
            });
            dialog.addEventListener('click', event => {
                if (event.target === dialog && pointerStartedOnBackdrop) {
                    setDialogVisible(dialog, dialog === editModal ? editCard : modalCard, false);
                }
                pointerStartedOnBackdrop = false;
            });
            dialog.addEventListener('pointercancel', () => {
                pointerStartedOnBackdrop = false;
            });
        });

        document.querySelectorAll('.open-edit-prestasi-btn').forEach(button => {
            button.addEventListener('click', function () {
                const editForm = document.getElementById('editPrestasiForm');
                editForm.action = this.dataset.action;
                document.getElementById('editPrestasiMahasiswa').value = this.dataset.mahasiswaId;
                document.getElementById('editPrestasiAktivitas').value = this.dataset.aktivitas;
                document.getElementById('editPrestasiJenis').value = this.dataset.jenis;
                document.getElementById('editPrestasiTahun').value = this.dataset.tahun;
                document.getElementById('editPrestasiEvidence').value = this.dataset.evidence || '';
                const existingEvidence = document.getElementById('editPrestasiExistingEvidence');
                existingEvidence.href = this.dataset.evidence || '#';
                existingEvidence.classList.toggle('hidden', !this.dataset.evidence);
                setDialogVisible(editModal, editCard, true);
            });
        });

        document.getElementById('closeEditPrestasiBtn').addEventListener('click', () => setDialogVisible(editModal, editCard, false));
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                closeCreateModal();
                setDialogVisible(editModal, editCard, false);
            }
        });
    });
</script>
@endsection
