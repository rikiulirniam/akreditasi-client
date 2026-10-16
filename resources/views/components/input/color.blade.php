@props([
    'class' => '',
])

<input 
    type="color" 
    {{ $attributes->merge(['class' => 'w-full h-10 p-1 rounded-[8px] bg-white-form border border-white-bone cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition ' . $class]) }}
>
