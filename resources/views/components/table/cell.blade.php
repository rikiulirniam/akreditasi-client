@props([
    'class' => '',
])

@php
    // Strip restrictive fixed column widths so general columns distribute evenly,
    // while keeping widths for action and specialized columns.
    $isActionOrCustom = str_contains($class, 'text-right') || str_contains($class, 'w-[') || str_contains($class, 'min-w-');
    $cleanClass = $isActionOrCustom ? $class : preg_replace('/\b(?:w-(?:36|40|44|48|52|56|60|64|72|80|96)|max-w-\S+)\b/', '', $class);

    $defaultAlign = (str_contains($class, 'text-center') || str_contains($class, 'text-right') || str_contains($class, 'text-justify')) ? '' : 'text-left ';
@endphp

<td {{ $attributes->except('class')->merge(['class' => 'py-3.5 px-4 sm:px-6 text-sm text-gray-800 align-middle border-b border-gray-200 ' . $defaultAlign . trim($cleanClass)]) }}>
    {{ $slot }}
</td>
