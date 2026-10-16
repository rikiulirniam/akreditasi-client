@props([
    'class' => '',
])

<x-input.checkbox {{ $attributes->merge(['class' => $class]) }} />
