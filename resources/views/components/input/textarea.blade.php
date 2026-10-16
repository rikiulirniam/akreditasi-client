@props([
    'class' => '',
])

<textarea 
    {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 rounded-[8px] bg-white-form border border-white-bone text-gray-900 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition font-medium text-sm ' . $class]) }}
>{{ $slot }}</textarea>
