@props([
    'class' => '',
])

<div class="relative inline-flex items-center w-full bg-white-form border-2 border-primary rounded-[8px] overflow-hidden {{ $class }}">
    <select {{ $attributes->merge(['class' => 'w-full bg-transparent appearance-none pl-3.5 pr-14 py-2.5 font-semibold text-gray-900 text-sm focus:outline-none cursor-pointer']) }}>
        {{ $slot }}
    </select>
    <div class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-[6px] bg-secondary border-2 border-primary flex items-center justify-center pointer-events-none shadow-xs">
        <svg class="w-4 h-4 text-primary stroke-primary" stroke-width="3" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </div>
</div>
