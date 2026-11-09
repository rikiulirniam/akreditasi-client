@extends('layouts.app')

@section('browser-title', 'Kerja Sama')

@section('title', 'Kerja Sama')

@section('subtitle')
    @if(!$apiError)
        {{ number_format((int) ($pagination['total'] ?? 0), 0, ',', '.') }} Data ditemukan
    @endif
@endsection

@section('content')
@php
    $hasActiveFilters = filled($filters['search'] ?? null)
        || filled($filters['status'] ?? null)
        || filled($filters['tingkat'] ?? null);
    $selectedStatus = (string) ($filters['status'] ?? '');
    $selectedLevel = (string) ($filters['tingkat'] ?? '');
    $startNumber = (int) ($pagination['from'] ?? (((int) ($pagination['current_page'] ?? 1) - 1) * (int) ($pagination['per_page'] ?? 10) + 1));
    $isEditing = $editing !== [];
    $formId = $editing['id'] ?? $editing['id_kerja_sama'] ?? null;
    $selectedTypeId = old('id_jenis_kerjasama', $editing['id_jenis_kerjasama'] ?? $editing['jenis_kerjasama']['id_jenis_kerjasama'] ?? '');
    $selectedDocumentType = old('jenis_dokumen', $editing['jenis_dokumen'] ?? '');
    $inputClass = 'w-full rounded-md border bg-white-form px-3.5 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2';
    $fieldClass = static fn (string $field): string => $errors->has($field)
        ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-200'
        : 'border-gray-300 focus:border-primary focus:ring-primary/20';
    $formAction = $isEditing
        ? route('kerja-sama.update', $formId)
        : route('kerja-sama.store');
    $closeModalUrl = route('kerja-sama.index', request()->only(['search', 'status', 'tingkat', 'page']));
@endphp

<div class="space-y-5 pb-8">
    @if($apiError)
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700" role="alert">
            {{ $apiError }}
        </div>
    @else
        @if(count($cooperations) > 0 || $hasActiveFilters || $openModal)
            <form method="GET" action="{{ route('kerja-sama.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center" id="kerja-sama-filter-form">
                <div class="flex flex-1 flex-wrap items-center gap-2">
                    <div class="group flex h-[34px] w-[248px] max-w-full shrink-0 overflow-hidden rounded-md border border-primary bg-white-form p-[2px]">
                        <label class="sr-only" for="kerja-sama-search">Cari kerja sama</label>
                        <input
                            id="kerja-sama-search"
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Cari nama"
                            class="min-w-0 flex-1 border-0 bg-transparent px-3 py-1 text-xs text-gray-900 placeholder:text-gray-600 focus:outline-none focus:ring-0"
                        >
                        <button type="submit" class="inline-flex h-full w-[34px] shrink-0 items-center justify-center rounded-[5px] text-black transition {{ filled($filters['search'] ?? null) ? 'border border-primary bg-[#ffc800]' : 'border border-transparent bg-transparent' }}" data-search-button aria-label="Cari">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </button>
                    </div>

                    <input type="hidden" name="status" value="{{ $selectedStatus }}" id="kerja-sama-status-value">
                    <div class="relative">
                        <button type="button" class="flex h-[34px] w-[140px] shrink-0 items-center justify-between overflow-hidden rounded-md border border-primary bg-white-form text-left text-[11px] text-gray-900" data-filter-trigger aria-expanded="false" aria-controls="kerja-sama-status-menu">
                            <span class="min-w-0 flex-1 truncate px-2">{{ $selectedStatus !== '' ? $selectedStatus : 'Semua Status' }}</span>
                            <span class="mx-[2px] my-[2px] flex h-[26px] w-7 shrink-0 items-center justify-center rounded-[5px] border border-primary bg-[#ffc800]">
                                <svg class="h-4 w-4 transition-transform" data-filter-chevron fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                </svg>
                            </span>
                        </button>
                        <div id="kerja-sama-status-menu" class="absolute left-0 top-full z-20 mt-1 hidden w-full rounded-md border border-primary bg-white-form p-1.5 shadow-sm" data-filter-menu>
                            <button type="button" class="block w-full rounded px-1.5 py-0.5 text-left text-[10px] text-gray-700 hover:text-gray-950 hover:underline" data-filter-value="" data-filter-label="Semua Status">Semua Status</button>
                            @foreach(['Aktif', 'Non-aktif', 'Sedang proses'] as $status)
                                <button type="button" class="block w-full rounded px-1.5 py-0.5 text-left text-[10px] text-gray-700 hover:text-gray-950 hover:underline" data-filter-value="{{ $status }}" data-filter-label="{{ $status }}">{{ $status }}</button>
                            @endforeach
                        </div>
                    </div>

                    <input type="hidden" name="tingkat" value="{{ $selectedLevel }}" id="kerja-sama-level-value">
                    <div class="relative">
                        <button type="button" class="flex h-[34px] w-[128px] shrink-0 items-center justify-between overflow-hidden rounded-md border border-primary bg-white-form text-left text-[11px] text-gray-900" data-filter-trigger aria-expanded="false" aria-controls="kerja-sama-level-menu">
                            <span class="min-w-0 flex-1 truncate px-2">{{ $selectedLevel !== '' ? $selectedLevel : 'Semua Tingkat' }}</span>
                            <span class="mx-[2px] my-[2px] flex h-[26px] w-7 shrink-0 items-center justify-center rounded-[5px] border border-primary bg-[#ffc800]">
                                <svg class="h-4 w-4 transition-transform" data-filter-chevron fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                </svg>
                            </span>
                        </button>
                        <div id="kerja-sama-level-menu" class="absolute left-0 top-full z-20 mt-1 hidden w-full rounded-md border border-primary bg-white-form p-1.5 shadow-sm" data-filter-menu>
                            <button type="button" class="block w-full rounded px-1.5 py-0.5 text-left text-[10px] text-gray-700 hover:text-gray-950 hover:underline" data-filter-value="" data-filter-label="Semua Tingkat">Semua Tingkat</button>
                            @foreach(['Lokal', 'Nasional', 'Internasional'] as $tingkat)
                                <button type="button" class="block w-full rounded px-1.5 py-0.5 text-left text-[10px] text-gray-700 hover:text-gray-950 hover:underline" data-filter-value="{{ $tingkat }}" data-filter-label="{{ $tingkat }}">{{ $tingkat }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <a href="{{ route('kerja-sama.index', request()->only(['search', 'status', 'tingkat', 'page']) + ['create' => 1]) }}" class="inline-flex h-[34px] shrink-0 items-center justify-center gap-1.5 rounded-md bg-additional px-3 text-xs font-bold text-white transition hover:bg-primary">
                    <span class="inline-flex h-3.5 w-3.5 items-center justify-center rounded-full bg-white text-additional">
                        <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" />
                        </svg>
                    </span>
                    Tambah Kerja Sama
                </a>
            </form>
            <script>
                (function () {
                    const form = document.getElementById('kerja-sama-filter-form');
                    if (!form) return;

                    const searchInput = document.getElementById('kerja-sama-search');
                    const searchButton = form.querySelector('[data-search-button]');
                    if (searchInput && searchButton) {
                        const updateSearchButton = function () {
                            const isSearching = searchInput.value.trim() !== ''
                                || document.activeElement === searchInput;
                            searchButton.classList.toggle('bg-[#ffc800]', isSearching);
                            searchButton.classList.toggle('bg-transparent', !isSearching);
                            searchButton.classList.toggle('border-primary', isSearching);
                            searchButton.classList.toggle('border-transparent', !isSearching);
                        };

                        searchInput.addEventListener('input', updateSearchButton);
                        searchInput.addEventListener('focus', updateSearchButton);
                        searchInput.addEventListener('blur', updateSearchButton);
                        updateSearchButton();
                    }

                    const closeMenus = function () {
                        form.querySelectorAll('[data-filter-menu]').forEach(function (menu) {
                            menu.classList.add('hidden');
                        });
                        form.querySelectorAll('[data-filter-trigger]').forEach(function (trigger) {
                            trigger.setAttribute('aria-expanded', 'false');
                            trigger.querySelector('[data-filter-chevron]')?.classList.remove('rotate-180');
                        });
                    };

                    form.querySelectorAll('[data-filter-trigger]').forEach(function (trigger) {
                        trigger.addEventListener('click', function () {
                            const menu = document.getElementById(trigger.getAttribute('aria-controls'));
                            if (!menu) return;

                            const shouldOpen = menu.classList.contains('hidden');
                            closeMenus();
                            if (shouldOpen) {
                                menu.classList.remove('hidden');
                                trigger.setAttribute('aria-expanded', 'true');
                                trigger.querySelector('[data-filter-chevron]')?.classList.add('rotate-180');
                            }
                        });
                    });

                    form.querySelectorAll('[data-filter-menu] [data-filter-value]').forEach(function (option) {
                        option.addEventListener('click', function () {
                            const menu = option.closest('[data-filter-menu]');
                            if (!menu) return;

                            const field = menu.id === 'kerja-sama-status-menu' ? 'status' : 'tingkat';
                            const input = document.getElementById('kerja-sama-' + (field === 'status' ? 'status' : 'level') + '-value');
                            const trigger = form.querySelector('[aria-controls="' + menu.id + '"]');
                            if (!input || !trigger) return;

                            input.value = option.dataset.filterValue || '';
                            trigger.querySelector('span').textContent = option.dataset.filterLabel || '';
                            form.requestSubmit();
                        });
                    });

                    document.addEventListener('click', function (event) {
                        if (!form.contains(event.target)) closeMenus();
                    });
                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') closeMenus();
                    });
                })();
            </script>

            @if($cooperations !== [])
                <x-table id="kerja-sama-table" class="!border-collapse !border-spacing-0 text-left text-[13px]">
                    <x-slot:header>
                        <x-table.th class="!rounded-none !bg-white !px-3 !py-2.5 !text-center !text-[13px] !font-bold border-y border-gray-300">No</x-table.th>
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Judul Kerja Sama</x-table.th>
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Nama Mitra</x-table.th>
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Jenis Dokumen</x-table.th>
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Periode</x-table.th>
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Status</x-table.th>
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-center !text-[13px] !font-bold border-y border-gray-300">Action</x-table.th>
                    </x-slot:header>

                    @foreach($cooperations as $index => $cooperation)
                        @php
                            $cooperationId = $cooperation['id'] ?? $cooperation['id_kerja_sama'] ?? null;
                            $title = trim((string) ($cooperation['judul_kerja_sama'] ?? '-'));
                            $titleLines = array_chunk(preg_split('/\s+/', $title) ?: [], 2);
                            $partnerName = $cooperation['nama_mitra']
                                ?? $cooperation['nama_instansi']
                                ?? $cooperation['mitra']['nama_mitra']
                                ?? $cooperation['mitra']['nama_instansi']
                                ?? '-';
                            $documentType = $cooperation['jenis_dokumen']
                                ?? $cooperation['nama_jenis_kerjasama']
                                ?? $cooperation['jenis_kerjasama']['nama_jenis_kerjasama']
                                ?? '-';
                            $startYear = substr((string) ($cooperation['tanggal_mulai'] ?? ''), 0, 4);
                            $endYear = substr((string) ($cooperation['tanggal_selesai'] ?? ''), 0, 4);
                            $period = ($startYear !== '' ? $startYear : '-').'–'.($endYear !== '' ? $endYear : '-');
                            $status = $cooperation['status'] ?? '-';
                            $statusClass = match (strtolower((string) $status)) {
                                'aktif' => 'text-emerald-600',
                                'sedang proses' => 'text-amber-600',
                                'non-aktif' => 'text-red-700',
                                default => 'text-gray-700',
                            };
                        @endphp
                        <x-table.row class="bg-white transition-colors hover:bg-gray-50/40">
                            <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-3 !py-3.5 text-center text-sm text-gray-900">{{ $startNumber + $index }}.</x-table.cell>
                            <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm font-medium leading-[1.2] text-gray-900">
                                @foreach($titleLines as $line)
                                    <span class="block break-words">{{ implode(' ', $line) }}</span>
                                @endforeach
                            </x-table.cell>
                            <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm text-gray-900">{{ $partnerName }}</x-table.cell>
                            <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm text-gray-900">{{ $documentType }}</x-table.cell>
                            <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm text-gray-900">{{ $period }}</x-table.cell>
                            <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5 !text-sm">
                                <span class="font-medium {{ $statusClass }}">{{ $status }}</span>
                            </x-table.cell>
                            <x-table.cell class="!rounded-none !border-x-0 border-y border-gray-300 !px-4 !py-3.5">
                                @if($cooperationId)
                                    <div class="flex items-center justify-center gap-4">
                                        <a href="{{ route('kerja-sama.edit', $cooperationId) }}" class="text-amber-500 transition hover:text-amber-700" title="Edit kerja sama" aria-label="Edit kerja sama">
                                            <x-icon.edit class="h-5 w-5" />
                                        </a>
                                        <form method="POST" action="{{ route('kerja-sama.destroy', $cooperationId) }}" data-confirm-delete data-title="Hapus Data Kerja sama?" data-message="Apakah Anda yakin ingin menghapus data ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 transition hover:text-red-800" title="Hapus kerja sama" aria-label="Hapus kerja sama">
                                                <x-icon.delete class="h-5 w-5" />
                                            </button>
                                        </form>
                                        <a href="{{ route('kerja-sama.show', $cooperationId) }}" class="text-primary transition hover:text-additional" title="Lihat detail kerja sama" aria-label="Lihat detail kerja sama">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.5 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </a>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </x-table.cell>
                        </x-table.row>
                    @endforeach
                </x-table>
                <x-pagination :paginator="$pagination" />
            @elseif($hasActiveFilters)
                <x-table id="kerja-sama-table" class="!border-collapse !border-spacing-0 text-left text-[13px]">
                    <x-slot:header>
                        <x-table.th class="!rounded-none !bg-white !px-3 !py-2.5 !text-center !text-[13px] !font-bold border-y border-gray-300">No</x-table.th>
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Judul Kerja Sama</x-table.th>
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Nama Mitra</x-table.th>
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Jenis Dokumen</x-table.th>
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Periode</x-table.th>
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-[13px] !font-bold border-y border-gray-300">Status</x-table.th>
                        <x-table.th class="!rounded-none !bg-white !px-4 !py-2.5 !text-center !text-[13px] !font-bold border-y border-gray-300">Action</x-table.th>
                    </x-slot:header>
                </x-table>
                <div class="py-8 text-center text-sm text-gray-500">Tidak ada kerja sama yang sesuai dengan pencarian.</div>
                <x-pagination :paginator="$pagination" />
            @endif
        @endif
    @endif
</div>

@if($cooperations === [] && !$hasActiveFilters && !$apiError)
    <section class="flex min-h-[min(62vh,560px)] flex-col items-center justify-center px-4 py-12 text-center">
        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-additional">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18M6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75A2.25 2.25 0 0 1 6.75 4.5ZM8.25 12h7.5m-7.5 3.75h4.5" />
            </svg>
        </div>
        <h2 class="text-3xl font-extrabold tracking-tight text-black sm:text-4xl">Belum Ada Kerja Sama</h2>
        <p class="mt-1 text-sm text-gray-700 sm:text-base">Tekan tombol di bawah untuk menambah data</p>
        <a href="{{ route('kerja-sama.index', ['create' => 1]) }}" class="mt-5 inline-flex min-h-11 w-full max-w-[270px] items-center justify-center gap-2 rounded-md bg-additional px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary">
            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-additional">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" />
                </svg>
            </span>
            Tambah Kerja Sama
        </a>
    </section>
@endif

@if($openModal || $errors->any())
    <div id="kerja-sama-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/70 p-3 sm:p-5" role="dialog" aria-modal="true" aria-labelledby="kerja-sama-modal-title">
        <section class="max-h-[92vh] w-full max-w-[560px] overflow-y-auto rounded-2xl bg-white px-5 py-6 shadow-2xl sm:px-8 sm:py-7">
            <h2 id="kerja-sama-modal-title" class="mb-6 text-center text-xl font-extrabold text-gray-950">
                {{ $isEditing ? 'Perbarui Kerja Sama' : 'Tambahkan Kerja Sama' }}
            </h2>

            @if($optionsError || $errors->any())
                <div class="mb-4 rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                    @if($optionsError)
                        <p>{{ $optionsError }}</p>
                    @endif
                    @if($errors->any())
                        <p class="font-bold">Periksa kembali data yang diisi:</p>
                        <ul class="mt-1 list-inside list-disc">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ $formAction }}" class="space-y-3.5">
                @csrf
                @if($isEditing)
                    @method('PUT')
                @endif

                <div>
                    <label for="judul_kerja_sama" class="mb-1 block text-xs font-bold text-gray-900">Judul Kerja Sama</label>
                    <input id="judul_kerja_sama" name="judul_kerja_sama" type="text" required maxlength="255" value="{{ old('judul_kerja_sama', $editing['judul_kerja_sama'] ?? '') }}" placeholder="Isi nama kegiatan kerja sama" class="{{ $inputClass }} {{ $fieldClass('judul_kerja_sama') }}">
                    @error('judul_kerja_sama')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="id_mitra" class="mb-1 block text-xs font-bold text-gray-900">Nama Mitra</label>
                    <select id="id_mitra" name="id_mitra" required class="{{ $inputClass }} {{ $fieldClass('id_mitra') }}">
                        <option value="">Pilih nama Mitra</option>
                        @foreach($options['partners'] as $partner)
                            <option value="{{ $partner['id'] }}" @selected((string) old('id_mitra', $editing['id_mitra'] ?? '') === (string) $partner['id'])>{{ $partner['name'] }}</option>
                        @endforeach
                    </select>
                    @error('id_mitra')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="id_dosen" class="mb-1 block text-xs font-bold text-gray-900">Nama Dosen</label>
                    <select id="id_dosen" name="id_dosen" required class="{{ $inputClass }} {{ $fieldClass('id_dosen') }}">
                        <option value="">Pilih nama Dosen</option>
                        @foreach($options['lecturers'] as $lecturer)
                            <option value="{{ $lecturer['id'] }}" @selected((string) old('id_dosen', $editing['id_dosen'] ?? '') === (string) $lecturer['id'])>{{ $lecturer['name'] }}</option>
                        @endforeach
                    </select>
                    @error('id_dosen')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="id_jenis_kerjasama" class="mb-1 block text-xs font-bold text-gray-900">Jenis Dokumen</label>
                    <select id="id_jenis_kerjasama" name="id_jenis_kerjasama" required class="{{ $inputClass }} {{ $fieldClass('id_jenis_kerjasama') }}">
                        <option value="">Pilih Jenis Dokumen</option>
                        @foreach($options['types'] as $type)
                            <option value="{{ $type['id'] }}" data-document-type="{{ $type['name'] }}" @selected((string) $selectedTypeId === (string) $type['id'])>{{ $type['name'] }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="jenis_dokumen" id="jenis_dokumen" value="{{ $selectedDocumentType }}">
                    @error('id_jenis_kerjasama')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    @error('jenis_dokumen')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label for="tanggal_mulai" class="mb-1 block text-xs font-bold text-gray-900">Tanggal mulai</label>
                        <input id="tanggal_mulai" name="tanggal_mulai" type="date" required value="{{ old('tanggal_mulai', isset($editing['tanggal_mulai']) ? substr((string) $editing['tanggal_mulai'], 0, 10) : '') }}" class="{{ $inputClass }} {{ $fieldClass('tanggal_mulai') }}">
                        @error('tanggal_mulai')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="mb-1 block text-xs font-bold text-gray-900">Tanggal selesai</label>
                        <input id="tanggal_selesai" name="tanggal_selesai" type="date" required value="{{ old('tanggal_selesai', isset($editing['tanggal_selesai']) ? substr((string) $editing['tanggal_selesai'], 0, 10) : '') }}" class="{{ $inputClass }} {{ $fieldClass('tanggal_selesai') }}">
                        @error('tanggal_selesai')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="status" class="mb-1 block text-xs font-bold text-gray-900">Status Kerja Sama</label>
                    <select id="status" name="status" required class="{{ $inputClass }} {{ $fieldClass('status') }}">
                        <option value="">Pilih status kerja sama</option>
                        @foreach(['Aktif', 'Non-aktif', 'Sedang proses'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $editing['status'] ?? '') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label for="tingkat" class="mb-1 block text-xs font-bold text-gray-900">Tingkat Kerja Sama</label>
                        <input id="tingkat" name="tingkat" type="text" maxlength="100" value="{{ old('tingkat', $editing['tingkat'] ?? '') }}" placeholder="Contoh: Nasional" class="{{ $inputClass }} {{ $fieldClass('tingkat') }}">
                        @error('tingkat')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="nomor_dokumen" class="mb-1 block text-xs font-bold text-gray-900">Nomor Dokumen</label>
                        <input id="nomor_dokumen" name="nomor_dokumen" type="text" maxlength="255" value="{{ old('nomor_dokumen', $editing['nomor_dokumen'] ?? '') }}" placeholder="Isi nomor dokumen" class="{{ $inputClass }} {{ $fieldClass('nomor_dokumen') }}">
                        @error('nomor_dokumen')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="bentuk_kegiatan" class="mb-1 block text-xs font-bold text-gray-900">Bentuk Kegiatan</label>
                    <textarea id="bentuk_kegiatan" name="bentuk_kegiatan" rows="3" maxlength="2000" placeholder="Jelaskan bentuk kegiatan kerja sama" class="{{ $inputClass }} {{ $fieldClass('bentuk_kegiatan') }}">{{ old('bentuk_kegiatan', $editing['bentuk_kegiatan'] ?? '') }}</textarea>
                    @error('bentuk_kegiatan')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                @if(!$isEditing)
                    <div>
                        <label for="bukti_dokumen" class="mb-1 block text-xs font-bold text-gray-900">Bukti Dokumen (tautan)</label>
                        <input id="bukti_dokumen" name="bukti_dokumen" type="url" maxlength="2048" value="{{ old('bukti_dokumen') }}" placeholder="https://..." class="{{ $inputClass }} {{ $fieldClass('bukti_dokumen') }}">
                        @error('bukti_dokumen')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                @elseif(!empty($editing['bukti_dokumen']))
                    <p class="text-xs text-gray-600">Bukti dokumen yang sudah terunggah akan tetap tersimpan saat memperbarui data.</p>
                @endif

                <div class="grid grid-cols-2 gap-3 pt-3">
                    <a href="{{ $closeModalUrl }}" class="inline-flex items-center justify-center rounded-md bg-white-form px-4 py-2.5 text-sm font-semibold text-gray-900 transition hover:bg-gray-300">Batal</a>
                    <button type="submit" @disabled($optionsError || $options['partners'] === [] || $options['lecturers'] === [] || $options['types'] === []) class="inline-flex items-center justify-center rounded-md bg-additional px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary disabled:cursor-not-allowed disabled:opacity-50">
                        Simpan
                    </button>
                </div>
            </form>
        </section>
    </div>

    <script>
        (function () {
            const typeSelect = document.getElementById('id_jenis_kerjasama');
            const documentType = document.getElementById('jenis_dokumen');
            if (!typeSelect || !documentType) return;

            const syncDocumentType = function () {
                const selectedOption = typeSelect.options[typeSelect.selectedIndex];
                if (selectedOption && selectedOption.dataset.documentType) {
                    documentType.value = selectedOption.dataset.documentType;
                }
            };

            typeSelect.addEventListener('change', syncDocumentType);
            if (!documentType.value) syncDocumentType();
        })();
    </script>
@endif
@endsection
