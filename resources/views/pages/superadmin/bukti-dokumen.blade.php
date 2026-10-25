@extends('layouts.app')

@section('title', 'Manajemen Bukti Dokumen')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="border-b border-gray-200 pb-5">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-primary tracking-tight">Manajemen Bukti Dokumen</h1>
        <p class="text-xs sm:text-sm text-gray-500 font-medium mt-1">Kelola tautan bukti pendukung untuk setiap butir indikator akreditasi.</p>
    </div>

    {{-- Form Tambahkan Link Bukti --}}
    <section class="rounded-2xl border border-[#EAEAEA] bg-white p-6 shadow-xs">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight">Tambahkan Link Bukti</h3>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Simpan tautan dokumen dari Google Drive, OneDrive, atau repositori lainnya.</p>
            </div>
            <div class="text-right">
                <span id="totalDokumen" class="text-2xl sm:text-3xl font-extrabold text-primary">0</span>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Dokumen</p>
            </div>
        </div>

        {{-- Form Input --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="mb-1.5 block text-xs sm:text-sm font-bold text-gray-700">Pilih Indikator</label>
                <select id="inputIndikator" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="">-- Pilih Indikator --</option>
                    <option value="C1.1.1">C1.1.1 - Ketersediaan dokumen formal</option>
                    <option value="C1.1.2">C1.1.2 - Tingkat ketercapaian target</option>
                    <option value="C2.1.1">C2.1.1 - Sistem tata pamong</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs sm:text-sm font-bold text-gray-700">Jenis Dokumen</label>
                <select id="inputJenis" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="Dokumen Kebijakan">Dokumen Kebijakan</option>
                    <option value="Dokumen Prosedur">Dokumen Prosedur</option>
                    <option value="Dokumen Formulir">Dokumen Formulir</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs sm:text-sm font-bold text-gray-700">Masa Berlaku</label>
                <input type="date" id="inputMasaBerlaku" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
            <div class="sm:col-span-5">
                <label class="mb-1.5 block text-xs sm:text-sm font-bold text-gray-700">Nama Dokumen</label>
                <input type="text" id="inputNama" placeholder="Nama lengkap dokumen" class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
            <div class="sm:col-span-5">
                <label class="mb-1.5 block text-xs sm:text-sm font-bold text-gray-700">URL Dokumen</label>
                <input type="url" id="inputUrl" placeholder="https://drive.google.com/..." class="block w-full rounded-[8px] border border-white-bone bg-white-form p-3 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
            <div class="sm:col-span-2">
                <button type="button" onclick="tambahDokumen()" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-primary text-white text-sm font-bold shadow-sm hover:bg-additional transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Simpan Link
                </button>
            </div>
        </div>
    </section>

    {{-- Daftar Bukti Dokumen --}}
    <section class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Daftar Bukti Dokumen</h3>
                <p class="text-xs sm:text-sm text-gray-500">Kelola dan tinjau masa berlaku dokumen bukti.</p>
            </div>
            <div>
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                    <input type="text" placeholder="Cari nama dokumen..." class="block w-full rounded-[8px] border border-white-bone bg-white-form pl-9 pr-3 py-2 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                </div>
            </div>
        </div>

        {{-- Tabel Daftar Dokumen menggunakan format x-table akademik --}}
        <div class="bg-white rounded-2xl border border-[#EAEAEA] shadow-xs overflow-hidden">
            <x-table>
                <x-slot:header>
                    <x-table.th>Dokumen</x-table.th>
                    <x-table.th class="w-48">Indikator</x-table.th>
                    <x-table.th class="w-44">Jenis</x-table.th>
                    <x-table.th class="w-36">Masa Berlaku</x-table.th>
                    <x-table.th class="w-28">Status</x-table.th>
                    <x-table.th class="w-24 text-right">Aksi</x-table.th>
                </x-slot:header>

                <tbody id="tabelDokumenBody" class="text-gray-800">
                    {{-- Diisi secara dinamis oleh JavaScript --}}
                </tbody>
            </x-table>
        </div>
    </section>

</div>

{{-- Script JS Manajemen Dokumen --}}
<script>
    let dokumenList = [
        {
            id: 1,
            nama: "SK Rencana Strategis Fakultas 2020-2024",
            indikator: "C1.1.1",
            jenis: "Dokumen Kebijakan",
            masaBerlaku: "31 Des 2024",
            status: "Berlaku",
            url: "https://drive.google.com/contoh-sk-renstra"
        },
        {
            id: 2,
            nama: "SOP Penyusunan & Sosialisasi VMTS",
            indikator: "C1.1.2",
            jenis: "Dokumen Prosedur",
            masaBerlaku: "15 Agu 2025",
            status: "Berlaku",
            url: "https://drive.google.com/contoh-sop-vmts"
        }
    ];

    function renderTabel() {
        const tbody = document.getElementById('tabelDokumenBody');
        const totalEl = document.getElementById('totalDokumen');
        if (!tbody) return;

        totalEl.innerText = dokumenList.length;

        if (dokumenList.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-base font-semibold text-gray-900">Belum ada bukti dokumen</p>
                            <p class="text-sm text-gray-500 mt-0.5">Silakan tambahkan link dokumen bukti di atas.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = dokumenList.map((item, index) => `
            <tr class="hover:bg-gray-50/80 transition-colors border-b border-gray-100 last:border-b-0">
                <td class="py-4 px-4 sm:px-6 align-middle">
                    <p class="font-semibold text-sm text-gray-900">${item.nama}</p>
                    <a href="${item.url}" target="_blank" class="inline-flex items-center gap-1 text-xs text-primary font-medium hover:underline mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Buka tautan
                    </a>
                </td>
                <td class="py-4 px-4 sm:px-6 align-middle">
                    <span class="font-mono font-bold text-sm text-primary">${item.indikator}</span>
                </td>
                <td class="py-4 px-4 sm:px-6 align-middle">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                        ${item.jenis}
                    </span>
                </td>
                <td class="py-4 px-4 sm:px-6 align-middle text-sm text-gray-600 font-medium">
                    ${item.masaBerlaku}
                </td>
                <td class="py-4 px-4 sm:px-6 align-middle">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        ${item.status}
                    </span>
                </td>
                <td class="py-4 px-4 sm:px-6 align-middle text-right w-24">
                    <button type="button" onclick="hapusDokumen(${item.id})" class="text-[#DC2626] hover:text-[#B91C1C] transition p-1 cursor-pointer" title="Hapus Dokumen">
                        <svg class="w-5 h-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function tambahDokumen() {
        const indikator = document.getElementById('inputIndikator').value;
        const jenis = document.getElementById('inputJenis').value;
        const masaBerlaku = document.getElementById('inputMasaBerlaku').value;
        const nama = document.getElementById('inputNama').value;
        const url = document.getElementById('inputUrl').value;

        if (!nama || !url || !indikator) {
            alert('Mohon lengkapi indikator, nama dokumen, dan link dokumen.');
            return;
        }

        dokumenList.unshift({
            id: Date.now(),
            nama: nama,
            indikator: indikator,
            jenis: jenis,
            masaBerlaku: masaBerlaku || "Tidak ditentukan",
            status: "Berlaku",
            url: url
        });

        document.getElementById('inputNama').value = '';
        document.getElementById('inputUrl').value = '';
        document.getElementById('inputMasaBerlaku').value = '';

        renderTabel();
    }

    function hapusDokumen(id) {
        if (confirm('Apakah Anda yakin ingin menghapus dokumen bukti ini?')) {
            dokumenList = dokumenList.filter(d => d.id !== id);
            renderTabel();
        }
    }

    document.addEventListener('DOMContentLoaded', renderTabel);
</script>
@endsection