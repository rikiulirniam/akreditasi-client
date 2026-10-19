@props([
    'class' => '',
])

@php
    $slotText = trim(strip_tags((string) $slot));
    $isNo = in_array(strtolower($slotText), ['no', 'no.', '#']);
    $isAction = in_array(strtolower($slotText), ['action', 'aksi']);

    // Remove restrictive fixed column widths like w-36, w-48, w-56, w-64, w-72 so columns distribute evenly
    $cleanClass = preg_replace('/\b(?:w-(?:36|40|44|48|52|56|60|64|72|80|96)|max-w-\S+)\b/', '', $class);

    $autoWidth = '';
    if ($isNo && !str_contains($class, 'w-')) {
        $autoWidth = 'w-16 text-left ';
    } elseif ($isAction && !str_contains($class, 'w-')) {
        $autoWidth = 'w-28 text-right ';
    }
@endphp

<th scope="col" {{ $attributes->except('class')->merge(['class' => 'py-3.5 px-4 sm:px-6 font-semibold tracking-tight text-gray-900 text-sm border-b border-gray-200 ' . $autoWidth . trim($cleanClass)]) }}>
    {{ $slot }}
</th>
