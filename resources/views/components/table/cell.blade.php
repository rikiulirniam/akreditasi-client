@props([
    'class' => '',
])

<td {{ $attributes->merge(['class' => 'py-3.5 px-4 text-[13px] sm:text-sm text-black align-middle ' . $class]) }}>
    {{ $slot }}
</td>
