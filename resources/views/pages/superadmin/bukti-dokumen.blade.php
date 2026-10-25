@extends('layouts.app')

@section('title', 'Manajemen Bukti Dokumen')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Manajemen Bukti Dokumen</h1>
    </div>

    {{-- Form Tambahkan Link Bukti --}}
    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Tambahkan Link Bukti</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Simpan tautan dokumen dari Drive, OneDrive, atau sistem informasi lainnya.</p>
            </div>
            <div class="text-right">
                <span id="totalDokumen" class="text-xl font-bold text-gray-900">0</span>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider">Total dokumen</p>
            </div>
        </div>

        {{-- Form Input --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <div>
                <select id="inputIndikator" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-700 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
                    <option value="">Pilih indikator</option>
                    <option value="C1.1.1">C1.1.1 - Ketersediaan dokumen formal</option>
                    <option value="C1.1.2">C1.1.2 - Tingkat ketercapaian target</option>
                </select>
            </div>
            <div>
                <select id="inputJenis" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-700 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
                    <option value="Dokumen Kebijakan">Dokumen Kebijakan</option>
                    <option value="Dokumen Prosedur">Dokumen Prosedur</option>
                    <option value="Dokumen Formulir">Dokumen Formulir</option>
                </select>
            </div>
            <div>
                <input type="date" id="inputMasaBerlaku" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-700 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <div class="sm:col-span-5">
                <input type="text" id="inputNama" placeholder="Nama dokumen" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-700 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
            </div>
            <div class="sm:col-span-5">
                <input type="url" id="inputUrl" placeholder="https://drive.google.com/..." class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-xs text-gray-700 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
            </div>
            <div class="sm:col-span-2">
                <button type="button" onclick="tambahDokumen()" class="w-full h-full rounded-lg bg-[#005A9C] px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#06284F]">
                    Tambah Link
                </button>
            </div>
        </div>
    </section>

    {{-- Daftar Bukti Dokumen --}}
    <section class="mt-6">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Daftar Bukti Dokumen</h3>
                <p class="text-xs text-gray-500">Kelola masa berlaku dan status dokumen.</p>
            </div>
            <div>
                <input type="text" placeholder="Cari nama dokumen..." class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-700 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C] sm:w-64">
            </div>
        </div>

        {{-- Tabel Daftar Dokumen --}}
        <div class="overflow-x-auto border-t border-gray-200 bg-white shadow-sm rounded-lg">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="text-[11px] font-medium text-gray-500 border-b border-gray-200 bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3">Dokumen</th>
                        <th scope="col" class="px-4 py-3">Indikator</th>
                        <th scope="col" class="px-4 py-3">Jenis Bukti</th>
                        <th scope="col" class="px-4 py-3">Tanggal Upload</th>
                        <th scope="col" class="px-4 py-3">Masa Berlaku</th>
                        <th scope="col" class="px-4 py-3">Status</th>
                        <th scope="col" class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tabelDokumenBody" class="divide-y divide-gray-100">
                    <tr id="emptyRow">
                        <td colspan="7" class="px-4 py-12 text-center text-xs text-gray-400">
                            Belum ada dokumen bukti.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- ========================================== --}}
    {{-- MODAL SUKSES (BERHASIL DITAMBAHKAN) --}}
    {{-- ========================================== --}}
    <div id="modal-tambah-dokumen" tabindex="-1" class="hidden fixed inset-0 z-50 items-center justify-center bg-gray-900/50">
        <div class="relative w-full max-w-md p-4">
            <div class="relative rounded-2xl bg-white p-6 text-center shadow-lg">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-[#005A9C]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-bold text-gray-900">Berhasil Ditambahkan!</h3>
                <p class="mb-6 text-xs text-gray-500">Tautan bukti dokumen baru telah berhasil disimpan ke dalam sistem.</p>
                <div class="flex justify-center">
                    <button onclick="tutupModalDokumen()" class="rounded-lg bg-[#005A9C] px-8 py-2 text-xs font-bold text-white hover:bg-[#06284F] transition">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Interaksi Form, Tabel, & Tombol Hapus --}}
    <script>
        let counterDokumen = 0;

        function tambahDokumen() {
            const nama = document.getElementById('inputNama').value.trim();
            const url = document.getElementById('inputUrl').value.trim();
            const indikator = document.getElementById('inputIndikator').value;
            const jenis = document.getElementById('inputJenis').value;
            const masaBerlaku = document.getElementById('inputMasaBerlaku').value;

            if (!nama || !url || !indikator) {
                alert('Mohon isi nama dokumen, pilih indikator, dan masukkan tautan link terlebih dahulu!');
                return;
            }

            const today = new Date().toISOString().slice(0, 10);

            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) {
                emptyRow.remove();
            }

            const tbody = document.getElementById('tabelDokumenBody');
            const newRow = document.createElement('tr');
            newRow.className = "transition hover:bg-gray-50 text-xs text-gray-800";
            newRow.innerHTML = `
                <td class="px-4 py-3 font-medium"><a href="${url}" target="_blank" class="text-[#005A9C] hover:underline">${nama}</a></td>
                <td class="px-4 py-3">${indikator}</td>
                <td class="px-4 py-3">${jenis}</td>
                <td class="px-4 py-3">${today}</td>
                <td class="px-4 py-3">${masaBerlaku || '-'}</td>
                <td class="px-4 py-3"><span class="rounded bg-green-50 px-2 py-0.5 text-[10px] font-medium text-green-600 border border-green-200">Aktif</span></td>
                <td class="px-4 py-3 text-center">
                    <button onclick="hapusBaris(this)" class="text-red-500 hover:text-red-700 transition" title="Hapus">
                        <svg class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </td>
            `;
            tbody.appendChild(newRow);

            counterDokumen++;
            document.getElementById('totalDokumen').innerText = counterDokumen;

            document.getElementById('inputNama').value = '';
            document.getElementById('inputUrl').value = '';
            document.getElementById('inputIndikator').value = '';
            document.getElementById('inputMasaBerlaku').value = '';

            document.getElementById('modal-tambah-dokumen').classList.remove('hidden');
            document.getElementById('modal-tambah-dokumen').classList.add('flex');
        }

        function hapusBaris(button) {
            const row = button.closest('tr');
            row.remove();

            counterDokumen--;
            document.getElementById('totalDokumen').innerText = counterDokumen;

            // Jika tabel kosong, tampilkan kembali tulisan 'Belum ada dokumen bukti'
            const tbody = document.getElementById('tabelDokumenBody');
            if (tbody.children.length === 0) {
                tbody.innerHTML = `
                    <tr id="emptyRow">
                        <td colspan="7" class="px-4 py-12 text-center text-xs text-gray-400">
                            Belum ada dokumen bukti.
                        </td>
                    </tr>
                `;
            }
        }

        function tutupModalDokumen() {
            document.getElementById('modal-tambah-dokumen').classList.add('hidden');
            document.getElementById('modal-tambah-dokumen').classList.remove('flex');
        }
    </script>

</div>
@endsection