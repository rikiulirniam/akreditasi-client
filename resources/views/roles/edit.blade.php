@extends('layouts.app')

@section('title', 'Ubah Peran')

@section('content')
<div class="space-y-6 w-full">
    <!-- Form Card without border/shadow, full width -->
    <form action="{{ route('roles.update', $role['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            
            <!-- Section: Informasi Pokok -->
            <div>
                <h3 class="text-sm font-black text-primary uppercase tracking-wider mb-3">
                    Informasi Pokok
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Nama Peran <span class="text-rose-500">*</span>
                        </label>
                        <x-input 
                            type="text" 
                            name="name" 
                            id="name" 
                            value="{{ old('name', $role['name'] ?? '') }}"
                            placeholder="Contoh: KAPRODI, DOSEN, REVIEWER"
                            required
                            class="uppercase font-bold tracking-wide text-sm font-mono"
                        />
                    </div>

                    <div>
                        <label for="guard_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Guard Name
                        </label>
                        <x-input 
                            type="text" 
                            name="guard_name" 
                            id="guard_name" 
                            value="{{ old('guard_name', $role['guard_name'] ?? 'api') }}"
                            placeholder="api"
                        />
                    </div>
                </div>
            </div>

            <!-- Section: Konfigurasi Hak Akses -->
            <div class="pt-4 border-t border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                    <div>
                        <h3 class="text-sm font-black text-primary uppercase tracking-wider">
                            Konfigurasi Hak Akses
                        </h3>
                        <p class="text-xs text-gray-form mt-0.5">Centang hak akses yang ingin diberikan pada peran ini.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            id="roleSelectAllGlobalBtn"
                            class="text-xs font-bold text-additional hover:underline cursor-pointer"
                        >
                            Pilih Semua
                        </button>
                        <span class="text-gray-300">|</span>
                        <button 
                            type="button" 
                            id="roleDeselectAllGlobalBtn"
                            class="text-xs font-bold text-gray-500 hover:text-gray-700 hover:underline cursor-pointer"
                        >
                            Batal Semua
                        </button>
                    </div>
                </div>

                <!-- Grid of Module Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($groupedPermissions as $group => $perms)
                        <div class="module-perm-card rounded-2xl border border-gray-200 bg-gray-50/60 p-4 space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-gray-200/80">
                                <span class="text-xs font-black text-primary uppercase font-mono tracking-wider">
                                    MODUL {{ $group }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <button 
                                        type="button" 
                                        class="mod-select-all-btn text-[11px] font-bold text-additional hover:underline cursor-pointer"
                                    >
                                        Pilih Semua
                                    </button>
                                    <span class="text-gray-300 text-[10px]">|</span>
                                    <button 
                                        type="button" 
                                        class="mod-deselect-all-btn text-[11px] font-bold text-gray-400 hover:text-gray-600 cursor-pointer"
                                    >
                                        Batal
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($perms as $perm)
                                    @php
                                        $isChecked = in_array($perm['name'], old('permissions', $rolePermissionNames ?? []));
                                    @endphp
                                    <label class="flex items-center gap-2.5 p-2 rounded-xl border border-gray-200 bg-white hover:border-primary/40 hover:shadow-2xs transition cursor-pointer select-none">
                                        <input 
                                            type="checkbox" 
                                            name="permissions[]" 
                                            value="{{ $perm['name'] }}" 
                                            class="role-perm-cb w-4 h-4 rounded text-primary accent-primary cursor-pointer"
                                            {{ $isChecked ? 'checked' : '' }}
                                        >
                                        <span class="text-xs font-mono text-gray-800 font-medium truncate" title="{{ $perm['name'] }}">
                                            {{ $perm['name'] }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 p-8 text-center text-xs text-gray-400 bg-gray-50 rounded-2xl">
                            Belum ada hak akses yang tersedia.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                <a 
                    href="{{ route('roles.index') }}" 
                    class="py-2.5 px-6 rounded-2xl bg-[#D9D9D9] hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="py-2.5 px-8 rounded-2xl bg-primary hover:bg-additional text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const allCheckboxes = document.querySelectorAll('.role-perm-cb');
        const selectAllGlobalBtn = document.getElementById('roleSelectAllGlobalBtn');
        const deselectAllGlobalBtn = document.getElementById('roleDeselectAllGlobalBtn');

        if (selectAllGlobalBtn) {
            selectAllGlobalBtn.addEventListener('click', function () {
                allCheckboxes.forEach(cb => cb.checked = true);
            });
        }

        if (deselectAllGlobalBtn) {
            deselectAllGlobalBtn.addEventListener('click', function () {
                allCheckboxes.forEach(cb => cb.checked = false);
            });
        }

        document.querySelectorAll('.module-perm-card').forEach(cardEl => {
            const modSelectAll = cardEl.querySelector('.mod-select-all-btn');
            const modDeselect = cardEl.querySelector('.mod-deselect-all-btn');
            const modCheckboxes = cardEl.querySelectorAll('.role-perm-cb');

            if (modSelectAll) {
                modSelectAll.addEventListener('click', function () {
                    modCheckboxes.forEach(cb => cb.checked = true);
                });
            }

            if (modDeselect) {
                modDeselect.addEventListener('click', function () {
                    modCheckboxes.forEach(cb => cb.checked = false);
                });
            }
        });
    });
</script>
@endsection
