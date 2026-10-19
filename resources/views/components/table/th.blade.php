@props([
    'class' => '',
])

<th {{ $attributes->merge(['class' => 'py-3.5 px-4 font-bold tracking-tight text-black text-[13px] sm:text-sm text-left ' . $class]) }}>
    {{ $slot }}
</th>
