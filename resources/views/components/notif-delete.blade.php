@props([
    'message' => 'Data berhasil dihapus',
    'id' => null,
])

<div 
    @if($id) id="{{ $id }}" @endif 
    {{ $attributes->merge(['class' => 'relative flex items-center justify-between gap-4 w-full px-5 py-3.5 sm:py-4 bg-[#FEE2E2] rounded-2xl border-b-[5px] border-[#C53030] shadow-md transition-all select-none']) }}
    role="alert"
>
    <div class="flex items-center gap-3.5 min-w-0 flex-1">
        <!-- Red Trash Can Outline Icon (Matching Foto 3) -->
        <div class="shrink-0 text-[#C53030]">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18" />
                <path d="M19 6v14c0 1.1-.9 2-2 2H7c-1.1 0-2-.9-2-2V6" />
                <path d="M8 6V4c0-1.1.9-2 2-2h4c1.1 0 2 .9 2 2v2" />
                <line x1="10" y1="11" x2="10" y2="17" />
                <line x1="14" y1="11" x2="14" y2="17" />
            </svg>
        </div>

        <!-- Notification Message (Matching Foto 3) -->
        <p class="text-base sm:text-[17px] font-bold text-[#C53030] tracking-tight truncate leading-tight">
            {{ $message }}
        </p>
    </div>

    <!-- Red Close Button (Matching Foto 3) -->
    <button 
        type="button" 
        onclick="this.closest('[role=alert]').remove()" 
        class="p-1 -mr-1 text-[#C53030] hover:text-[#991B1B] hover:bg-red-200/50 rounded-lg transition cursor-pointer shrink-0" 
        aria-label="Tutup"
    >
        <svg class="w-5 h-5 stroke-[2.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
