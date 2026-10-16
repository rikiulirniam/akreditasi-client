@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-6">
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <!-- Search & Role Filter Group -->
            <div class="flex items-center gap-3 flex-1 max-w-xl">
                <!-- Search Input -->
                <div class="relative flex-1 max-w-sm">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                    <x-input 
                        type="text" 
                        id="searchUserInput"
                        placeholder="Cari..." 
                        class="pl-10 text-xs"
                    />
                </div>

                <!-- Role Filter Dropdown -->
                <div class="relative w-44">
                    <select 
                        id="filterRoleSelect"
                        class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                    >
                        <option value="">Semua Peran</option>
                        @foreach($roles as $r)
                            @php
                                $rName = $r['name'] ?? '';
                                $isSelected = strtolower($selectedRole ?? '') === strtolower($rName);
                            @endphp
                            <option value="{{ strtolower($rName) }}" {{ $isSelected ? 'selected' : '' }}>
                                {{ ucfirst(strtolower($rName)) }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                        <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Create User Action Button -->
            <div>
                <button 
                    type="button" 
                    id="openCreateUserModalBtn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Pengguna
                </button>
            </div>
        </div>

        <x-table>
            <x-slot:header>
                <x-table.th>No</x-table.th>
                <x-table.th>Nama</x-table.th>
                <x-table.th>Peran</x-table.th>
                <x-table.th>Email</x-table.th>
                <x-table.th>Action</x-table.th>
            </x-slot:header>

            @forelse($users as $index => $u)
                @php
                    $userName = $u['nama'] ?? $u['name'] ?? '-';
                    $userEmail = $u['email'] ?? '-';
                    $userRoleRaw = $u['role'] ?? ($u['roles'][0]['name'] ?? '-');
                    $userRoleDisplay = ucfirst(strtolower($userRoleRaw));
                    $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                @endphp
                <x-table.row class="user-row" data-role="{{ strtolower($userRoleRaw) }}">
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $rowNumber }}
                    </x-table.cell>
                    <x-table.cell class="w-64">
                        <span class="font-extrabold text-sm text-gray-900 user-name tracking-tight font-mono">
                            {{ $userName }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="w-36">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700 user-role">
                            {{ $userRoleDisplay }}
                        </span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="text-sm font-medium text-gray-600 user-email">
                            {{ $userEmail }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="w-28">
                        <div class="flex items-center justify-end gap-3">
                            <button 
                                type="button" 
                                class="edit-user-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Pengguna"
                                data-id="{{ $u['id'] }}"
                                data-nama="{{ $userName }}"
                                data-email="{{ $userEmail }}"
                                data-role="{{ $userRoleRaw }}"
                                data-action="{{ route('users.update', $u['id']) }}"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>
                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Pengguna"
                                data-confirm-delete
                                data-action="{{ route('users.destroy', $u['id']) }}"
                                data-title="Hapus Pengguna?"
                                data-message="Apakah Anda yakin ingin menghapus pengguna '{{ $userName }}'? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="5" message="Belum ada data pengguna" submessage="Silakan klik tombol Tambah Pengguna untuk membuat pengguna baru." />
            @endforelse
        </x-table>

        <x-pagination :paginator="$pagination" />
    </div>
</div>

<!-- Modal 1: Tambahkan Pengguna (Matching Image 2) -->
<div 
    id="createUserModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="createUserModalTitle"
>
    <div class="user-modal-card relative bg-white rounded-[28px] p-8 max-w-[440px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="createUserModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Tambahkan Pengguna
        </h3>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Field 1: Nama -->
            <div>
                <label for="createNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama
                </label>
                <input 
                    type="text" 
                    name="nama" 
                    id="createNamaInput" 
                    placeholder="Isi nama" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Role -->
            <div>
                <label for="createRoleSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Role
                </label>
                <div class="relative">
                    <select 
                        name="role" 
                        id="createRoleSelect" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r['name'] }}">{{ ucfirst(strtolower($r['name'])) }}</option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Field 3: Email -->
            <div>
                <label for="createEmailInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Email
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="createEmailInput" 
                    placeholder="nama@polines.ac.id" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
                <p class="text-[11px] text-gray-500 mt-1">Hanya email institusi (*.polines.ac.id).</p>
            </div>

            <!-- Field 4: Password -->
            <div>
                <label for="createPasswordInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Password
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        name="password" 
                        id="createPasswordInput" 
                        placeholder="********" 
                        required 
                        class="w-full px-4 py-2.5 pr-11 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                    >
                    <button 
                        type="button" 
                        class="toggle-password-btn absolute inset-y-0 right-0 flex items-center pr-3.5 text-primary hover:text-additional transition cursor-pointer" 
                        data-target="createPasswordInput"
                        tabindex="-1"
                        aria-label="Tampilkan Password"
                    >
                        <!-- Closed Eye with Eyelashes matching screenshot -->
                        <svg class="eye-closed w-5 h-5 stroke-[2.2]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 8a10.645 10.645 0 0 0 20 0"/>
                            <path d="m4 15 1.726-2.05"/>
                            <path d="m9 18 .722-3.25"/>
                            <path d="m15 18-.722-3.25"/>
                            <path d="m20 15-1.726-2.05"/>
                        </svg>
                        <!-- Open Eye -->
                        <svg class="eye-open w-5 h-5 stroke-[2.2] hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Field 5: Konfirmasi Password -->
            <div>
                <label for="createPasswordConfirmationInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Konfirmasi Password
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        id="createPasswordConfirmationInput" 
                        placeholder="********" 
                        required 
                        class="w-full px-4 py-2.5 pr-11 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                    >
                    <button 
                        type="button" 
                        class="toggle-password-btn absolute inset-y-0 right-0 flex items-center pr-3.5 text-primary hover:text-additional transition cursor-pointer" 
                        data-target="createPasswordConfirmationInput"
                        tabindex="-1"
                        aria-label="Tampilkan Password"
                    >
                        <svg class="eye-closed w-5 h-5 stroke-[2.2]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 8a10.645 10.645 0 0 0 20 0"/>
                            <path d="m4 15 1.726-2.05"/>
                            <path d="m9 18 .722-3.25"/>
                            <path d="m15 18-.722-3.25"/>
                            <path d="m20 15-1.726-2.05"/>
                        </svg>
                        <svg class="eye-open w-5 h-5 stroke-[2.2] hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Action Buttons: Batal & Simpan -->
            <div class="grid grid-cols-2 gap-4 pt-3">
                <button 
                    type="button" 
                    id="closeCreateModalBtn"
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

<!-- Modal 2: Perbarui Data Pengguna (Matching Image 3) -->
<div 
    id="editUserModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editUserModalTitle"
>
    <div class="user-modal-card relative bg-white rounded-[28px] p-8 max-w-[440px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editUserModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Perbarui Data Pengguna
        </h3>

        <form id="editUserForm" action="#" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field 1: Nama -->
            <div>
                <label for="editNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama
                </label>
                <input 
                    type="text" 
                    name="nama" 
                    id="editNamaInput" 
                    placeholder="Isi nama" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field 2: Role -->
            <div>
                <label for="editRoleSelect" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Role
                </label>
                <div class="relative">
                    <select 
                        name="role" 
                        id="editRoleSelect" 
                        required 
                        class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r['name'] }}">{{ ucfirst(strtolower($r['name'])) }}</option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
                        <svg class="w-5 h-5 stroke-[3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Field 3: Perbarui Email (Matching Label in Image 3) -->
            <div>
                <label for="editEmailInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Perbarui Email
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="editEmailInput" 
                    placeholder="nama@polines.ac.id" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                >
                <p class="text-[11px] text-gray-500 mt-1">Hanya email institusi (*.polines.ac.id).</p>
            </div>

            <!-- Field 4: Perbarui Password (Matching Label in Image 3) -->
            <div>
                <label for="editPasswordInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Perbarui Password
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        name="password" 
                        id="editPasswordInput" 
                        placeholder="********" 
                        class="w-full px-4 py-2.5 pr-11 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                    >
                    <button 
                        type="button" 
                        class="toggle-password-btn absolute inset-y-0 right-0 flex items-center pr-3.5 text-primary hover:text-additional transition cursor-pointer" 
                        data-target="editPasswordInput"
                        tabindex="-1"
                        aria-label="Tampilkan Password"
                    >
                        <svg class="eye-closed w-5 h-5 stroke-[2.2]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 8a10.645 10.645 0 0 0 20 0"/>
                            <path d="m4 15 1.726-2.05"/>
                            <path d="m9 18 .722-3.25"/>
                            <path d="m15 18-.722-3.25"/>
                            <path d="m20 15-1.726-2.05"/>
                        </svg>
                        <svg class="eye-open w-5 h-5 stroke-[2.2] hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Field 5: Konfirmasi Password -->
            <div>
                <label for="editPasswordConfirmationInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Konfirmasi Password
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        id="editPasswordConfirmationInput" 
                        placeholder="********" 
                        class="w-full px-4 py-2.5 pr-11 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
                    >
                    <button 
                        type="button" 
                        class="toggle-password-btn absolute inset-y-0 right-0 flex items-center pr-3.5 text-primary hover:text-additional transition cursor-pointer" 
                        data-target="editPasswordConfirmationInput"
                        tabindex="-1"
                        aria-label="Tampilkan Password"
                    >
                        <svg class="eye-closed w-5 h-5 stroke-[2.2]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 8a10.645 10.645 0 0 0 20 0"/>
                            <path d="m4 15 1.726-2.05"/>
                            <path d="m9 18 .722-3.25"/>
                            <path d="m15 18-.722-3.25"/>
                            <path d="m20 15-1.726-2.05"/>
                        </svg>
                        <svg class="eye-open w-5 h-5 stroke-[2.2] hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Action Buttons: Batal & Perbarui (Matching Button Label in Image 3) -->
            <div class="grid grid-cols-2 gap-4 pt-3">
                <button 
                    type="button" 
                    id="closeEditModalBtn"
                    class="w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-[#01568D] hover:bg-primary text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Table Search & Role Filter
        const searchInput = document.getElementById('searchUserInput');
        const roleFilter = document.getElementById('filterRoleSelect');
        const rows = document.querySelectorAll('.user-row');

        function filterRows() {
            const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const selectedRole = roleFilter ? roleFilter.value.toLowerCase().trim() : '';

            rows.forEach(row => {
                const name = row.querySelector('.user-name')?.textContent.toLowerCase() || '';
                const roleText = row.querySelector('.user-role')?.textContent.toLowerCase() || '';
                const email = row.querySelector('.user-email')?.textContent.toLowerCase() || '';
                const rowRoleAttr = row.getAttribute('data-role') || '';

                const matchesKeyword = !keyword || (name.includes(keyword) || roleText.includes(keyword) || email.includes(keyword));
                const matchesRole = !selectedRole || rowRoleAttr === selectedRole || roleText.trim() === selectedRole;

                row.style.display = (matchesKeyword && matchesRole) ? '' : 'none';
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterRows);
        }

        if (roleFilter) {
            roleFilter.addEventListener('change', filterRows);
        }

        // Modal Helpers
        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.user-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.user-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        // Create Modal Elements
        const createModal = document.getElementById('createUserModal');
        const openCreateBtn = document.getElementById('openCreateUserModalBtn');
        const closeCreateBtn = document.getElementById('closeCreateModalBtn');

        if (openCreateBtn) {
            openCreateBtn.addEventListener('click', function () {
                showModal(createModal);
                setTimeout(() => document.getElementById('createNamaInput')?.focus(), 100);
            });
        }

        if (closeCreateBtn) {
            closeCreateBtn.addEventListener('click', function () {
                hideModal(createModal);
            });
        }

        // Edit Modal Elements
        const editModal = document.getElementById('editUserModal');
        const editForm = document.getElementById('editUserForm');
        const closeEditBtn = document.getElementById('closeEditModalBtn');
        const editNama = document.getElementById('editNamaInput');
        const editRole = document.getElementById('editRoleSelect');
        const editEmail = document.getElementById('editEmailInput');
        const editPassword = document.getElementById('editPasswordInput');
        const editPasswordConfirmation = document.getElementById('editPasswordConfirmationInput');

        document.querySelectorAll('.edit-user-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const action = this.getAttribute('data-action');
                const nama = this.getAttribute('data-nama');
                const email = this.getAttribute('data-email');
                const role = this.getAttribute('data-role');

                if (editForm) editForm.action = action;
                if (editNama) editNama.value = nama || '';
                if (editEmail) editEmail.value = email || '';
                if (editRole) editRole.value = role || '';
                if (editPassword) editPassword.value = '';
                if (editPasswordConfirmation) editPasswordConfirmation.value = '';

                showModal(editModal);
                setTimeout(() => editNama?.focus(), 100);
            });
        });

        if (closeEditBtn) {
            closeEditBtn.addEventListener('click', function () {
                hideModal(editModal);
            });
        }

        // Close on Backdrop Click
        [createModal, editModal].forEach(m => {
            m?.addEventListener('click', function (e) {
                if (e.target === m) hideModal(m);
            });
        });

        // Close on Escape Key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideModal(createModal);
                hideModal(editModal);
            }
        });

        // Password Visibility Toggle
        document.querySelectorAll('.toggle-password-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const eyeOpen = this.querySelector('.eye-open');
                const eyeClosed = this.querySelector('.eye-closed');

                if (input) {
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    if (eyeOpen && eyeClosed) {
                        eyeClosed.classList.toggle('hidden', isPassword);
                        eyeOpen.classList.toggle('hidden', !isPassword);
                    }
                }
            });
        });
    });
</script>
@endsection
