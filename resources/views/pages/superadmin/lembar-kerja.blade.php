@extends('layouts.app')

@section('title', 'Lembar Kerja Prodi')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-primary tracking-tight">Lembar Kerja Prodi</h1>
            <p class="text-xs sm:text-sm text-gray-500 font-medium mt-1">Tulis narasi pemenuhan dan deskripsi capaian setiap indikator akreditasi.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2.5">
            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3.5 py-1.5 text-xs font-semibold text-emerald-700 border border-emerald-200">0 siap divalidasi</span>
            <span class="inline-flex items-center rounded-full bg-gray-100 px-3.5 py-1.5 text-xs font-semibold text-gray-700">0 draft</span>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="flex items-center max-w-md">
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </span>
            <input 
                type="text" 
                id="searchIndikator" 
                placeholder="Cari kode atau deskripsi indikator..." 
                class="block w-full rounded-[8px] border border-white-bone bg-white-form pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
        </div>
    </div>

    {{-- Grid Kartu Indikator --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        @php
            $listIndikator = [
                ['kode' => 'C1.1.1', 'desc' => 'Ketersediaan dokumen formal — kejelasan & kerealistisan VMTS', 'deadline' => '21 Okt 2026'],
                ['kode' => 'C1.1.2', 'desc' => 'Tingkat ketercapaian target — kejelasan & kerealistisan VMTS', 'deadline' => '24 Okt 2026'],
                ['kode' => 'C1.2.1', 'desc' => 'Rasio dan kecukupan — mekanisme penyusunan & sosialisasi', 'deadline' => '27 Okt 2026'],
                ['kode' => 'C1.2.2', 'desc' => 'Monitoring dan evaluasi berkala — mekanisme penyusunan & sosialisasi', 'deadline' => '30 Okt 2026'],
                ['kode' => 'C2.1.1', 'desc' => 'Tingkat ketercapaian target — sistem tata pamong', 'deadline' => '2 Nov 2026'],
                ['kode' => 'C2.1.2', 'desc' => 'Rasio dan kecukupan — sistem tata pamong', 'deadline' => '5 Nov 2026'],
                ['kode' => 'C2.2.1', 'desc' => 'Monitoring dan evaluasi berkala — kerja sama tridharma', 'deadline' => '8 Nov 2026'],
                ['kode' => 'C2.2.2', 'desc' => 'Ketersediaan dokumen formal — kerja sama tridharma', 'deadline' => '11 Nov 2026'],
                ['kode' => 'C3.1.1', 'desc' => 'Rasio dan kecukupan — kualitas input mahasiswa', 'deadline' => '14 Nov 2026'],
                ['kode' => 'C3.1.2', 'desc' => 'Monitoring dan evaluasi berkala — kualitas input mahasiswa', 'deadline' => '17 Nov 2026'],
                ['kode' => 'C3.2.1', 'desc' => 'Ketersediaan dokumen formal — layanan kemahasiswaan', 'deadline' => '20 Nov 2026'],
                ['kode' => 'C3.2.2', 'desc' => 'Tingkat ketercapaian target — layanan kemahasiswaan', 'deadline' => '23 Nov 2026'],
            ];
        @endphp

        @foreach ($listIndikator as $item)
        <div class="card-item relative flex flex-col justify-between rounded-2xl border border-[#EAEAEA] bg-white p-6 shadow-xs transition hover:shadow-md">
            <div>
                <div class="flex items-center justify-between">
                    <span class="rounded-xl bg-primary/10 px-3 py-1 font-mono text-xs sm:text-sm font-bold text-primary">{{ $item['kode'] }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Belum diisi</span>
                </div>
                <p class="mt-4 text-sm sm:text-base font-semibold text-gray-900 leading-relaxed">{{ $item['desc'] }}</p>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4 text-xs font-medium text-gray-500">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    0 dokumen bukti
                </span>
                <span>Deadline: {{ $item['deadline'] }}</span>
            </div>
        </div>
        @endforeach
    </div>

</div>

{{-- Script JS untuk Pencarian --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('searchIndikator');
        const cards = document.querySelectorAll('.card-item');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                let keyword = searchInput.value.toLowerCase().trim();

                cards.forEach(function(card) {
                    let textCard = card.innerText.toLowerCase();
                    if (textCard.includes(keyword)) {
                        card.style.display = "flex";
                    } else {
                        card.style.display = "none";
                    }
                });
            });
        }
    });
</script>
@endsection