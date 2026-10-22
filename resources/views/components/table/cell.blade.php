@props([
    'class' => '',
])

@php
    // Strip restrictive fixed column widths (e.g. w-36, w-48, w-56, w-64, w-72, w-80, w-96, max-w-*)
    // so columns can expand freely and distribute evenly across spacious screens,
    // while keeping compact widths like w-16 (No) and w-24/w-28/w-32 (Action).
    $cleanClass = preg_replace('/\b(?:w-(?:36|40|44|48|52|56|60|64|72|80|96)|max-w-\S+)\b/', '', $class);
@endphp

<td {{ $attributes->except('class')->merge(['class' => 'py-4 px-4 sm:px-6 text-sm text-gray-800 align-middle border-b border-gray-200 ' . trim($cleanClass)]) }}>
    {{ $slot }}
</td>
