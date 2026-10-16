@props([
    'class' => '',
])

<input 
    type="file" 
    {{ $attributes->merge(['class' => 'w-full px-3 py-2 rounded-[8px] bg-white-form border border-white-bone text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition font-medium file:mr-3 file:py-1.5 file:px-3 file:rounded-[6px] file:border-0 file:text-xs file:font-bold file:bg-primary file:text-white hover:file:bg-additional cursor-pointer ' . $class]) }}
>
