@props([
    'class' => '',
])

<tr {{ $attributes->merge(['class' => 'border-b border-gray-200 hover:bg-gray-50/60 transition-colors duration-150 group ' . $class]) }}>
    {{ $slot }}
</tr>
