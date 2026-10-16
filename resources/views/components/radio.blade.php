@props([
    'class' => '',
])

<x-input.radio {{ $attributes->merge(['class' => $class]) }} />
