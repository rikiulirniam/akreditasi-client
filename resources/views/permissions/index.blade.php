@extends('layouts.app')

@section('title', 'Manajemen Hak Akses')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
                <x-input 
                    type="text" 
                    id="searchPermissionInput"
                    placeholder="Cari nama modul atau aksi..." 
                    class="pl-10 text-xs"
                />
            </div>
            <button 
                type="button" 
                id="openCreatePermissionBtn"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Hak Akses
            </button>
        </div>

        <x-table>
            <x-slot:header>
                <x-table.th>No</x-table.th>
                <x-table.th>Nama Modul</x-table.th>
                <x-table.th>Guard</x-table.th>
                <x-table.th>Daftar Hak Akses (Aksi)</x-table.th>
                <x-table.th>Action</x-table.th>
            </x-slot:header>

            @forelse($modules as $index => $item)
                @php
                    $moduleName = $item['module'] ?? '-';
                    $actions = $item['actions'] ?? [];
                    $actionCount = count($actions);
                    $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                @endphp
                <x-table.row class="perm-module-row">
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $rowNumber }}
                    </x-table.cell>
                    <x-table.cell class="w-48">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-sm text-gray-900 perm-module-name tracking-tight font-mono">
                                {{ $moduleName }}
                            </span>
                        </div>
                    </x-table.cell>
                    <x-table.cell class="w-24">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700">
                            {{ $item['guard_name'] ?? 'api' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell>
                        <div class="flex items-center gap-1.5 flex-wrap max-w-2xl perm-actions-list">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-additional/10 text-additional">
                                {{ $actionCount }} Aksi
                            </span>
                            @foreach($actions as $action)
                                @php
                                    $actionLower = strtolower($action);
                                    $colorClass = match($actionLower) {
                                        'create' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'read' => 'bg-sky-50 text-sky-700 border-sky-200',
                                        'update' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'delete' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-mono font-medium border {{ $colorClass }}">
                                    {{ $action }}
                                </span>
                            @endforeach
                        </div>
                    </x-table.cell>
                    <x-table.cell class="w-28">
                        <div class="flex items-center justify-end gap-3">
                            <button 
                                type="button" 
                                class="edit-module-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Modul"
                                data-module="{{ $moduleName }}"
                                data-guard="{{ $item['guard_name'] ?? 'api' }}"
                                data-actions="{{ json_encode($actions) }}"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>
                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Modul"
                                data-confirm-delete
                                data-action="{{ route('permissions.destroy-module', $moduleName) }}"
                                data-title="Hapus Modul Hak Akses?"
                                data-message="Apakah Anda yakin ingin menghapus semua hak akses untuk modul '{{ $moduleName }}' ({{ $actionCount }} aksi)? Tindakan ini bersifat permanen."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="5" message="Belum ada data modul hak akses" submessage="Silakan klik tombol Tambah Hak Akses untuk membuat modul hak akses baru." />
            @endforelse
        </x-table>

        <x-pagination :paginator="$pagination" />
    </div>
</div>

<!-- Modal Form Hak Akses Modul (Create & Edit Bulk) -->
<div 
    id="permissionModuleModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="moduleModalTitle"
>
    <div class="perm-modal-card relative bg-white rounded-3xl p-6 sm:p-8 max-w-[550px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <form id="permissionModuleForm" method="POST" action="{{ route('permissions.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="moduleFormMethod" value="POST">

            <!-- Field: Nama Modul -->
            <div>
                <label for="permModuleInput" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Nama Modul <span class="text-rose-500">*</span>
                </label>
                <x-input 
                    type="text" 
                    name="module" 
                    id="permModuleInput" 
                    placeholder="Contoh: user, mahasiswa, borang"
                    required
                    class="font-mono text-sm"
                />
                <p class="text-[11px] text-gray-400 mt-1">
                    Prefix nama hak akses (contoh: <code class="text-primary font-bold">mahasiswa.create</code>).
                </p>
            </div>

            <!-- Field: Guard Name -->
            <div>
                <label for="permGuardInput" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Guard Name
                </label>
                <x-input 
                    type="text" 
                    name="guard_name" 
                    id="permGuardInput" 
                    value="api"
                    placeholder="api"
                />
            </div>

            <!-- Field: Pilihan Aksi CRUD Default -->
            <div class="pt-1">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Aksi CRUD Default
                    </label>
                    <button 
                        type="button" 
                        id="toggleSelectAllCrudBtn"
                        class="text-xs font-bold text-additional hover:underline cursor-pointer flex items-center gap-1"
                    >
                        Pilih Semua CRUD
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <!-- Create -->
                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200 bg-gray-50/60 hover:bg-white hover:border-emerald-300 transition cursor-pointer select-none">
                        <input type="checkbox" name="actions[]" value="create" class="crud-checkbox w-4 h-4 rounded text-primary accent-primary cursor-pointer" checked>
                        <div class="text-xs font-medium">
                            <span class="font-bold text-emerald-700 font-mono">create</span>
                            <span class="text-gray-400 block text-[10px]">Membuat data</span>
                        </div>
                    </label>

                    <!-- Read -->
                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200 bg-gray-50/60 hover:bg-white hover:border-sky-300 transition cursor-pointer select-none">
                        <input type="checkbox" name="actions[]" value="read" class="crud-checkbox w-4 h-4 rounded text-primary accent-primary cursor-pointer" checked>
                        <div class="text-xs font-medium">
                            <span class="font-bold text-sky-700 font-mono">read</span>
                            <span class="text-gray-400 block text-[10px]">Melihat data</span>
                        </div>
                    </label>

                    <!-- Update -->
                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200 bg-gray-50/60 hover:bg-white hover:border-amber-300 transition cursor-pointer select-none">
                        <input type="checkbox" name="actions[]" value="update" class="crud-checkbox w-4 h-4 rounded text-primary accent-primary cursor-pointer" checked>
                        <div class="text-xs font-medium">
                            <span class="font-bold text-amber-700 font-mono">update</span>
                            <span class="text-gray-400 block text-[10px]">Mengubah data</span>
                        </div>
                    </label>

                    <!-- Delete -->
                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200 bg-gray-50/60 hover:bg-white hover:border-rose-300 transition cursor-pointer select-none">
                        <input type="checkbox" name="actions[]" value="delete" class="crud-checkbox w-4 h-4 rounded text-primary accent-primary cursor-pointer" checked>
                        <div class="text-xs font-medium">
                            <span class="font-bold text-rose-700 font-mono">delete</span>
                            <span class="text-gray-400 block text-[10px]">Menghapus data</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Field: Aksi Tambahan / Custom Postfix -->
            <div class="pt-1">
                <label for="customActionInput" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Aksi Tambahan
                </label>
                <div class="flex items-center gap-2">
                    <x-input 
                        type="text" 
                        id="customActionInput" 
                        placeholder="Contoh: export, import, approve, print"
                        class="text-xs font-mono"
                    />
                    <button 
                        type="button" 
                        id="addCustomActionBtn"
                        class="px-4 py-2.5 rounded-xl bg-primary hover:bg-additional text-white text-xs font-bold transition shrink-0 cursor-pointer shadow-xs"
                    >
                        + Tambah
                    </button>
                </div>

                <!-- Custom Actions Chips Container -->
                <div id="customActionsContainer" class="flex flex-wrap gap-2 mt-2 empty:hidden"></div>
            </div>

            <!-- Pratinjau Hak Akses Realtime -->
            <div class="p-3 rounded-2xl bg-gray-50 border border-gray-200 text-xs">
                <div class="flex items-center justify-between text-gray-500 font-semibold mb-1.5">
                    <span>Pratinjau Hak Akses:</span>
                    <span id="previewCountBadge" class="text-primary font-bold font-mono">4 hak akses</span>
                </div>
                <div id="previewPermissionsList" class="flex flex-wrap gap-1.5 min-h-[24px]">
                    <span class="text-gray-400 italic text-[11px]">Ketik nama modul untuk melihat pratinjau...</span>
                </div>
            </div>

            <!-- Buttons -->
            <div class="grid grid-cols-2 gap-4 w-full pt-2">
                <button 
                    type="button" 
                    id="closeModuleModalBtn"
                    class="w-full py-3 px-6 rounded-2xl bg-[#D9D9D9] hover:bg-gray-300 text-black font-bold text-sm sm:text-base transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-2xl bg-primary hover:bg-additional text-white font-bold text-sm sm:text-base shadow-sm transition cursor-pointer text-center"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Table Search
        const searchInput = document.getElementById('searchPermissionInput');
        const rows = document.querySelectorAll('.perm-module-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const modName = row.querySelector('.perm-module-name')?.textContent.toLowerCase() || '';
                    const actionsText = row.querySelector('.perm-actions-list')?.textContent.toLowerCase() || '';
                    row.style.display = (modName.includes(keyword) || actionsText.includes(keyword)) ? '' : 'none';
                });
            });
        }

        // Modal Elements
        const modal = document.getElementById('permissionModuleModal');
        const card = modal?.querySelector('.perm-modal-card');
        const form = document.getElementById('permissionModuleForm');
        const titleEl = document.getElementById('moduleModalTitle');
        const subtitleEl = document.getElementById('moduleModalSubtitle');
        const methodInput = document.getElementById('moduleFormMethod');
        const moduleInput = document.getElementById('permModuleInput');
        const guardInput = document.getElementById('permGuardInput');
        const crudCheckboxes = document.querySelectorAll('.crud-checkbox');
        const selectAllCrudBtn = document.getElementById('toggleSelectAllCrudBtn');
        const customActionInput = document.getElementById('customActionInput');
        const addCustomActionBtn = document.getElementById('addCustomActionBtn');
        const customActionsContainer = document.getElementById('customActionsContainer');
        const previewList = document.getElementById('previewPermissionsList');
        const previewBadge = document.getElementById('previewCountBadge');
        const openCreateBtn = document.getElementById('openCreatePermissionBtn');
        const closeBtn = document.getElementById('closeModuleModalBtn');

        let customActions = new Set();

        function getActiveActions() {
            const actions = [];
            crudCheckboxes.forEach(cb => {
                if (cb.checked) actions.push(cb.value);
            });
            customActions.forEach(act => actions.push(act));
            return actions;
        }

        function updatePreview() {
            const rawMod = moduleInput.value.toLowerCase().trim().replace(/[^a-z0-9_-]/g, '');
            const actions = getActiveActions();

            previewBadge.textContent = `${actions.length} hak akses`;

            if (!rawMod) {
                previewList.innerHTML = '<span class="text-gray-400 italic text-[11px]">Ketik nama modul untuk melihat pratinjau...</span>';
                return;
            }

            if (actions.length === 0) {
                previewList.innerHTML = '<span class="text-rose-400 italic text-[11px]">Pilih minimal satu aksi hak akses.</span>';
                return;
            }

            previewList.innerHTML = actions.map(act => {
                return `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-mono font-medium bg-white text-primary border border-primary/20 shadow-xs">${rawMod}.${act}</span>`;
            }).join('');
        }

        function renderCustomActions() {
            customActionsContainer.innerHTML = '';
            customActions.forEach(act => {
                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-mono font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 select-none';
                chip.innerHTML = `
                    <input type="hidden" name="actions[]" value="${act}">
                    <span>${act}</span>
                    <button type="button" class="remove-custom-btn text-indigo-400 hover:text-rose-600 transition cursor-pointer font-bold leading-none">&times;</button>
                `;
                chip.querySelector('.remove-custom-btn').addEventListener('click', function () {
                    customActions.delete(act);
                    renderCustomActions();
                    updatePreview();
                });
                customActionsContainer.appendChild(chip);
            });
        }

        function addCustomAction() {
            const val = customActionInput.value.toLowerCase().trim().replace(/[^a-z0-9_-]/g, '');
            if (!val) return;

            if (['create', 'read', 'update', 'delete'].includes(val)) {
                crudCheckboxes.forEach(cb => {
                    if (cb.value === val) cb.checked = true;
                });
            } else {
                customActions.add(val);
                renderCustomActions();
            }

            customActionInput.value = '';
            updatePreview();
        }

        if (addCustomActionBtn) {
            addCustomActionBtn.addEventListener('click', addCustomAction);
        }

        if (customActionInput) {
            customActionInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addCustomAction();
                }
            });
        }

        crudCheckboxes.forEach(cb => {
            cb.addEventListener('change', updatePreview);
        });

        if (selectAllCrudBtn) {
            selectAllCrudBtn.addEventListener('click', function () {
                const allChecked = Array.from(crudCheckboxes).every(cb => cb.checked);
                crudCheckboxes.forEach(cb => {
                    cb.checked = !allChecked;
                });
                selectAllCrudBtn.textContent = allChecked ? 'Pilih Semua CRUD' : 'Batal Pilih Semua';
                updatePreview();
            });
        }

        if (moduleInput) {
            moduleInput.addEventListener('input', updatePreview);
        }

        function openModal() {
            if (!modal) return;
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
            setTimeout(() => moduleInput?.focus(), 100);
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

        // Open Create Modal
        if (openCreateBtn) {
            openCreateBtn.addEventListener('click', function () {
                form.action = "{{ route('permissions.store') }}";
                methodInput.value = 'POST';
                if (titleEl) titleEl.textContent = 'Tambah Hak Akses Modul';
                if (subtitleEl) subtitleEl.textContent = 'Ketik nama modul, lalu pilih aksi CRUD atau tambahkan aksi kustom secara massal.';
                moduleInput.value = '';
                moduleInput.readOnly = false;
                guardInput.value = 'api';
                crudCheckboxes.forEach(cb => cb.checked = true);
                if (selectAllCrudBtn) selectAllCrudBtn.textContent = 'Batal Pilih Semua';
                customActions.clear();
                renderCustomActions();
                updatePreview();
                openModal();
            });
        }

        // Open Edit Modal for a Module
        document.querySelectorAll('.edit-module-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const mod = this.getAttribute('data-module');
                const guard = this.getAttribute('data-guard');
                let actions = [];
                try {
                    actions = JSON.parse(this.getAttribute('data-actions') || '[]');
                } catch (e) {
                    actions = [];
                }

                form.action = `/permissions/module/${encodeURIComponent(mod)}`;
                methodInput.value = 'PUT';
                
                moduleInput.value = mod;
                moduleInput.readOnly = false;
                guardInput.value = guard || 'api';

                const standardCrud = ['create', 'read', 'update', 'delete'];
                crudCheckboxes.forEach(cb => {
                    cb.checked = actions.includes(cb.value);
                });

                const allChecked = Array.from(crudCheckboxes).every(cb => cb.checked);
                if (selectAllCrudBtn) {
                    selectAllCrudBtn.textContent = allChecked ? 'Batal Pilih Semua' : 'Pilih Semua CRUD';
                }

                customActions.clear();
                actions.forEach(act => {
                    if (!standardCrud.includes(act)) {
                        customActions.add(act);
                    }
                });
                renderCustomActions();
                updatePreview();
                openModal();
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', closeModal);

        modal?.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal && !modal.classList.contains('pointer-events-none')) {
                closeModal();
            }
        });
    });
</script>
@endsection
