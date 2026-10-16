@props([
    'headers' => [],
])

<div class="w-full overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'w-full border-separate border-spacing-y-2.5 text-left text-xs']) }}>
        <thead>
            <tr class="text-gray-800 font-semibold">
                @if(isset($header))
                    {{ $header }}
                @elseif(!empty($headers))
                    @foreach($headers as $h)
                        <th class="py-3.5 px-6 bg-white-form first:rounded-l-2xl last:rounded-r-2xl font-bold tracking-tight">
                            {{ $h }}
                        </th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody class="text-gray-800">
            {{ $slot }}
        </tbody>
    </table>
</div>
