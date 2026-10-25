@props([
    'class' => '',
])

<x-input.year {{ $attributes->merge(['class' => $class]) }} />
