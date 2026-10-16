@props([
    'headers' => [],
])

<x-table.index :headers="$headers" {{ $attributes }}>
    @if(isset($header))
        <x-slot:header>
            {{ $header }}
        </x-slot:header>
    @endif

    {{ $slot }}
</x-table.index>
