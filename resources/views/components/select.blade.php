@props([
    'class' => '',
])

<x-input.select {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</x-input.select>
