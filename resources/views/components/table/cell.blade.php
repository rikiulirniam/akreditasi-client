@props([
    'class' => '',
])

<td {{ $attributes->merge(['class' => 'py-3.5 px-6 border-y border-white-bone first:border-l first:rounded-l-2xl last:border-r last:rounded-r-2xl text-base ' . $class]) }}>
    {{ $slot }}
</td>
