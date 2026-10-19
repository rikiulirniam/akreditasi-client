@props([
    'class' => '',
])

<th {{ $attributes->merge(['class' => 'py-3.5 px-6 bg-white-form first:rounded-l-2xl last:rounded-r-2xl font-bold tracking-tight text-gray-800 text-base ' . $class]) }}>
    {{ $slot }}
</th>
