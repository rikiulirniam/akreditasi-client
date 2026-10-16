@props([
    'class' => '',
])

<tr {{ $attributes->merge(['class' => 'bg-white hover:bg-gray-50/70 transition-all duration-150 group ' . $class]) }}>
    {{ $slot }}
</tr>
