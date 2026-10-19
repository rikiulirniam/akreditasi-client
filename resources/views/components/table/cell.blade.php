@props([
    'class' => '',
])

<td {{ $attributes->merge(['class' => 'py-3.5 px-5 border-y border-[#D5D5D5] bg-[#F3F3F3] first:border-l first:rounded-l-[12px] last:border-r last:rounded-r-[12px] text-sm text-gray-900 ' . $class]) }}>
    {{ $slot }}
</td>
