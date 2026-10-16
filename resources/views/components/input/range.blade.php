@props([
    'class' => '',
])

<input 
    type="range" 
    {{ $attributes->merge(['class' => 'w-full h-2 rounded-[8px] bg-white-bone accent-primary cursor-pointer focus:outline-none transition ' . $class]) }}
>
