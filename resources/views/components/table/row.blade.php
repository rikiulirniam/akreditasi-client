@props([
    'class' => '',
])

<tr {{ $attributes->merge(['class' => 'bg-[#F3F3F3] hover:bg-[#E9E9E9] transition-all duration-150 group ' . $class]) }}>
    {{ $slot }}
</tr>
