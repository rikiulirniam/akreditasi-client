@extends('layouts.app')

@section('title', 'Manajemen Peran')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary tracking-tight">Manajemen Peran</h2>
            <p class="text-sm text-gray-form mt-1">Kelola peran pengguna dan konfigurasi hak akses sistem.</p>
        </div>
        <div>
            <a 
                href="{{ route('roles.create') }}" 
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Peran
            </a>
        </div>
    </div>

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
                    id="searchRoleInput"
                    placeholder="Cari nama peran..." 
                    class="pl-10 text-xs"
                />
            </div>
        </div>

        <x-table>
            <x-slot:header>
                <x-table.th>No</x-table.th>
                <x-table.th>Nama Peran</x-table.th>
                <x-table.th>Guard</x-table.th>
                <x-table.th>Hak Akses</x-table.th>
                <x-table.th>Action</x-table.th>
            </x-slot:header>

            @forelse($roles as $index => $role)
                @php
                    $rolePerms = $role['permissions'] ?? [];
                    $permCount = count($rolePerms);
                    $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                @endphp
                <x-table.row class="role-row">
                    <x-table.cell class="text-gray-500 font-medium w-16">
                        {{ $rowNumber }}
                    </x-table.cell>
                    <x-table.cell class="w-56">
                        <span class="font-extrabold text-sm text-gray-900 role-name tracking-tight font-mono">
                            {{ $role['name'] ?? '-' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell class="w-24">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700">
                            {{ $role['guard_name'] ?? 'api' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell>
                        <div class="flex items-center gap-1.5 flex-wrap max-w-xl">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-additional/10 text-additional">
                                {{ $permCount }} Hak Akses
                            </span>
                            @foreach(array_slice($rolePerms, 0, 3) as $perm)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ $perm['name'] ?? '' }}
                                </span>
                            @endforeach
                            @if($permCount > 3)
                                <span class="text-[10px] text-gray-400 font-medium">+{{ $permCount - 3 }} lainnya</span>
                            @endif
                        </div>
                    </x-table.cell>
                    <x-table.cell class="w-28">
                        <div class="flex items-center justify-end gap-3">
                            <a 
                                href="{{ route('roles.edit', $role['id']) }}" 
                                class="text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Peran"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </a>
                            <button 
                                type="button" 
                                class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer"
                                title="Hapus Peran"
                                data-confirm-delete
                                data-action="{{ route('roles.destroy', $role['id']) }}"
                                data-title="Hapus Peran?"
                                data-message="Apakah Anda yakin ingin menghapus peran '{{ $role['name'] }}'? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                            >
                                <x-icon.delete class="w-5 h-5" />
                            </button>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="5" message="Belum ada data peran" submessage="Silakan klik tombol Tambah Peran untuk membuat peran baru." />
            @endforelse
        </x-table>

        <x-pagination :paginator="$pagination" />
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchRoleInput');
        const rows = document.querySelectorAll('.role-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const name = row.querySelector('.role-name')?.textContent.toLowerCase() || '';
                    row.style.display = name.includes(keyword) ? '' : 'none';
                });
            });
        }
    });
</script>
@endsection
