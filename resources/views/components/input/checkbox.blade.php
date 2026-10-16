@props([
    'class' => '',
])

<input 
    type="checkbox" 
    {{ $attributes->merge(['class' => 'w-5 h-5 rounded-[4px] border-2 border-primary bg-white-form text-primary accent-primary focus:ring-2 focus:ring-primary/20 transition cursor-pointer ' . $class]) }}
>
