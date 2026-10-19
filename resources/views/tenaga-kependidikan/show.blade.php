@extends('layouts.app')

@section('meta_title', 'Detail Tenaga Kependidikan')

@section('title')
    <span class="inline-flex items-center gap-2.5 sm:gap-3.5 flex-wrap">
        <a href="{{ route('tenaga-kependidikan.index') }}" class="hover:text-additional transition text-black">Tenaga Kependidikan</a>
        <svg class="w-5 h-5 sm:w-7 sm:h-7 stroke-[3] text-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <span>Detail</span>
    </span>
@endsection

@section('subtitle', $tenagaKependidikan['nama'])

@section('content')
<div class="space-y-2.5 pb-2">
    @if($detailApiError)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-medium text-amber-900" role="alert">
            {{ $detailApiError }}
        </div>
    @endif

    <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-12">
        <section class="min-h-[292px] overflow-hidden rounded-[7px] bg-[#EEF3F7] lg:col-span-7" aria-label="Identitas tenaga kependidikan">
            <div class="px-6 pt-5 pb-3">
                <h2 class="text-2xl font-bold leading-tight text-black sm:text-[26px]">
                    {{ $tenagaKependidikan['nama'] ?: 'Nama belum tersedia' }}
                </h2>
                <p class="mt-1 text-sm font-medium text-gray-600">
                    {{ $tenagaKependidikan['jabatan'] ?: 'Belum diisi' }}
                </p>
            </div>

            <div class="mt-3 border-t border-b border-[#BAC7D5] divide-y divide-[#BAC7D5]">
                <div class="flex h-[38px] items-center gap-3 px-6 text-xs font-medium text-black">
                    <svg class="h-4 w-4 shrink-0 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.272-.71l1.992-7.302H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" /></svg>
                    <span class="truncate">{{ $tenagaKependidikan['bidang_keahlian'] ?: 'Belum diisi' }}</span>
                </div>
                <div class="flex h-[38px] items-center gap-3 px-6 text-xs font-medium text-black">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v9A2.25 2.25 0 0 1 18.75 16.5H5.25A2.25 2.25 0 0 1 3 14.25v-9ZM8.25 21h7.5M12 16.5V21m-5.25 0h10.5" /></svg>
                    <span class="truncate">{{ $tenagaKependidikan['kualifikasi'] !== '-' ? $tenagaKependidikan['kualifikasi'] : 'Belum diisi' }}</span>
                </div>
                <div class="flex h-[38px] items-center gap-3 px-6 text-xs font-medium text-black">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75 12 13.5l9-6.75M4.5 4.5h15A1.5 1.5 0 0 1 21 6v12a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18V6a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
                    <span class="truncate">{{ $tenagaKependidikan['email'] ?: 'Belum diisi' }}</span>
                </div>
                <div class="flex h-[38px] items-center gap-3 px-6 text-xs font-medium text-black">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 10.5h7.5m-7.5 3.75h4.5M6 3.75h9.75L19.5 7.5v12.75A1.5 1.5 0 0 1 18 21.75H6A1.5 1.5 0 0 1 4.5 20.25V5.25A1.5 1.5 0 0 1 6 3.75Z" /></svg>
                    <span class="truncate">{{ $tenagaKependidikan['status'] ?: 'Belum diisi' }}</span>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-[7px] bg-[#EEF3F7] lg:col-span-5" aria-labelledby="qualificationHeading">
            <div class="flex h-[47px] items-center justify-center bg-[#012650] px-5">
                <h3 id="qualificationHeading" class="text-sm font-semibold text-white">Kualifikasi</h3>
            </div>
            <div class="max-h-[292px] min-h-[244px] divide-y divide-[#BAC7D5] overflow-y-auto px-3">
                @forelse($kualifikasis as $qualification)
                    <article class="flex min-h-[80px] items-center gap-2.5 py-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-secondary text-[#012650]">
                            <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3Zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9ZM17 15.99l-5 2.73-5-2.73v-3.72l5 2.73 5-2.73v3.72Z" /></svg>
                        </span>
                        <div class="min-w-0 flex-1 leading-tight">
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-semibold text-black">{{ $qualification['gelar'] ?: 'Pendidikan' }}</span>
                                @if($qualification['jenjang'])
                                    <span class="rounded-[4px] bg-[#012650] px-1.5 py-0.5 text-[10px] font-bold text-secondary">{{ $qualification['jenjang'] }}</span>
                                @endif
                            </div>
                            <p class="truncate text-[10px] font-medium text-gray-800">{{ $qualification['institusi'] ?: 'Institusi belum tersedia dari API' }}</p>
                            <p class="truncate text-[10px] text-gray-800">
                                {{ $qualification['program_studi'] ?: $tenagaKependidikan['kualifikasi'] }}
                                @if($qualification['tahun_lulus']) - Lulus {{ $qualification['tahun_lulus'] }} @endif
                            </p>
                        </div>
                    </article>
                @empty
                    <div class="flex min-h-[244px] items-center justify-center px-5 text-center text-xs text-gray-600">
                        Jenjang pendidikan Tendik belum tersedia dari API.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="min-h-[290px] overflow-hidden rounded-[7px] bg-[#EEF3F7]" aria-labelledby="certificationHeading">
        <div class="flex h-[47px] items-center justify-between bg-[#012650] px-4 sm:px-6">
            <h3 id="certificationHeading" class="text-sm font-semibold text-white tracking-wide">Sertifikasi</h3>
            <button 
                type="button" 
                disabled 
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[6px] bg-[#01568D] text-white text-xs font-semibold opacity-90 cursor-not-allowed select-none transition shadow-sm"
                title="Fitur ini belum dapat diakses"
            >
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd" />
                </svg>
                <span>Tambah Sertifikasi</span>
            </button>
        </div>

        <div class="min-h-[243px] p-4 sm:p-5">
            @if($detailApiError)
                <p class="mb-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900" role="alert">{{ $detailApiError }}</p>
            @endif
            @if(count($sertifikasis) > 0)
                <div class="overflow-x-auto rounded-[6px] bg-white border border-[#E5E7EB]">
                    <table class="w-full text-left text-xs sm:text-sm border-collapse">
                        <thead>
                            <tr class="text-black font-bold border-b border-[#E5E7EB] bg-white">
                                <th class="py-3 px-4 w-12 text-left">No</th>
                                <th class="py-3 px-4 text-left">Jenis Sertifikasi</th>
                                <th class="py-3 px-4 text-left">Nomor Sertifikasi</th>
                                <th class="py-3 px-4 text-left">Status Sertifikasi</th>
                                <th class="py-3 px-4 text-left">Tahun Sertifikasi</th>
                                <th class="py-3 px-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB] bg-white">
                            @foreach($sertifikasis as $index => $sertifikasi)
                                @php
                                    $status = $sertifikasi['status'] ?? 'Aktif';
                                    $isExpired = Str::lower($status) === 'kadaluarsa' || Str::lower($status) === 'expired';
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="py-3 px-4 font-normal text-gray-700">{{ $index + 1 }}.</td>
                                    <td class="py-3 px-4 font-medium text-black">
                                        <div>{{ $sertifikasi['jenis_sertifikasi'] ?? 'Sertifikasi' }}</div>
                                        @if(!empty($sertifikasi['lembaga_penerbit']))
                                            <div class="text-[11px] font-normal text-gray-500">{{ $sertifikasi['lembaga_penerbit'] }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-800">{{ $sertifikasi['nomor_sertifikat'] ?? '-' }}</td>
                                    <td class="py-3 px-4 font-semibold {{ $isExpired ? 'text-[#991B1B]' : 'text-[#22C55E]' }}">
                                        {{ $status }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-800">{{ $sertifikasi['tahun_sertifikasi'] ?? '-' }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center justify-center gap-2.5 opacity-90 cursor-default pointer-events-none" aria-disabled="true">
                                            <!-- Edit Icon (Yellow) -->
                                            <span class="text-[#FAB005]">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </span>
                                            <!-- Delete Icon (Solid Red) -->
                                            <span class="text-[#DC2626]">
                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                                    <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.353 6.83a.75.75 0 0 1 .75.75v7.5a.75.75 0 0 1-1.5 0v-7.5a.75.75 0 0 1 .75-.75Zm4.5 0a.75.75 0 0 1 .75.75v7.5a.75.75 0 0 1-1.5 0v-7.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                            <!-- Detail Icon (Navy) -->
                                            <span class="text-[#003B6D]">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex min-h-[210px] items-center justify-center text-center">
                    <p class="text-3xl font-bold text-black sm:text-[36px]">Belum ada sertifikasi</p>
                </div>
            @endif
        </div>
    </section>
</div>

@endsection
