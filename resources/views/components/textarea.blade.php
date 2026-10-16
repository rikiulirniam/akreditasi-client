@props([
    'class' => '',
])

<x-input.textarea {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</x-input.textarea>
