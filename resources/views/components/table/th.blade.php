@props([
    'class' => '',
])

<th {{ $attributes->merge(['class' => 'py-3.5 px-5 bg-[#D9D9D9] first:rounded-l-[12px] last:rounded-r-[12px] font-bold tracking-tight text-gray-900 text-sm ' . $class]) }}>
    {{ $slot }}
</th>
