@extends('layouts.app')

@section('title', 'Lembar Kerja Prodi')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Lembar Kerja Prodi</h1>
    </div>

    {{-- Subtitle & Filter Bar --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-gray-500">Tulis narasi pemenuhan dan deskripsi capaian setiap indikator.</p>
        
        <div class="flex flex-wrap items-center gap-2">
            <button class="rounded-full bg-gray-200 px-3 py-1 text-[11px] font-medium text-gray-700 hover:bg-gray-300 transition">0 siap divalidasi</button>
            <button class="rounded-full bg-gray-200 px-3 py-1 text-[11px] font-medium text-gray-700 hover:bg-gray-300 transition">0 draft</button>
            
            {{-- Input Pencarian --}}
            <input type="text" id="searchIndikator" placeholder="Cari kode atau indikator..." class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-700 focus:border-[#005A9C] focus:outline-none focus:ring-1 focus:ring-[#005A9C]">
        </div>
    </div>

    {{-- Grid Kartu Indikator --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        
        @php
            $listIndikator = [
                ['kode' => 'C1.1.1', 'desc' => 'Ketersediaan dokumen formal — kejelasan & kerealistisan vmts', 'deadline' => '21 Okt 2026'],
                ['kode' => 'C1.1.2', 'desc' => 'Tingkat ketercapaian target — kejelasan & kerealistisan vmts', 'deadline' => '24 Okt 2026'],
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
        {{-- Background card diubah menjadi abu-abu muda (bg-slate-50/bg-gray-50) seperti Figma --}}
        <div class="card-item relative flex flex-col justify-between rounded-xl border border-gray-200/90 bg-[#F9FAFB] p-5 shadow-[0_1px_3px_rgba(0,0,0,0.05)] transition hover:shadow-md">
            <div>
                <div class="flex items-center justify-between">
                    {{-- Badge kode menggunakan background putih bersih agar kontras --}}
                    <span class="rounded-md bg-white px-2.5 py-1 text-[11px] font-bold text-[#005A9C] shadow-sm border border-gray-200/60">{{ $item['kode'] }}</span>
                    <span class="text-[11px] font-medium text-gray-500">Belum diisi</span>
                </div>
                <p class="mt-4 text-xs font-semibold text-gray-800 leading-relaxed">{{ $item['desc'] }}</p>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-gray-200/60 pt-3 text-[10px] text-gray-400">
                <span>0 dokumen bukti</span>
                <span>Deadline {{ $item['deadline'] }}</span>
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
    });
</script>
@endsection