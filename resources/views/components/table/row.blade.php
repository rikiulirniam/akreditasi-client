@props([
    'class' => '',
])

<tr {{ $attributes->merge(['class' => 'border-b border-[#E5E7EB] bg-white hover:bg-gray-50/60 transition-all duration-150 group ' . $class]) }}>
    {{ $slot }}
</tr>
