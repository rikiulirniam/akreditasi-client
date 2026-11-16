@props([
    'closeId' => 'closeModalBtn',
    'backId' => null,
    'cancelText' => 'Batal',
    'submitText' => 'Simpan',
])

<div class="grid grid-cols-2 gap-4 pt-3">
    @if(!empty($backId))
        <button
            type="button"
            id="{{ $backId }}"
            class="back-to-step1-btn w-full py-2.5 px-4 rounded-[8px] bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center flex items-center justify-center gap-2"
        >
            <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            <span>Kembali</span>
        </button>
    @else
        <button
            type="button"
            id="{{ $closeId }}"
            class="w-full py-2.5 px-6 rounded-[8px] bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
        >
            {{ $cancelText }}
        </button>
    @endif

    <button
        type="submit"
        class="btn-submit-luaran w-full py-2.5 px-6 rounded-[8px] bg-[#01568D] hover:bg-primary text-white font-bold text-sm shadow-xs transition cursor-pointer text-center flex items-center justify-center gap-2"
    >
        <span class="btn-text">{{ $submitText }}</span>
        <svg class="btn-spinner hidden w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </button>
</div>
