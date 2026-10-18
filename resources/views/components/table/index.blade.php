@props([
    'headers' => [],
])

<div class="w-full overflow-x-auto rounded-[14px] border border-[#E6E6E6] bg-white shadow-sm">
    <table {{ $attributes->merge(['class' => 'w-full border-separate border-spacing-y-2 text-left text-xs']) }}>
        <thead>
            <tr class="text-gray-800 font-semibold">
                @if(isset($header))
                    {{ $header }}
                @elseif(!empty($headers))
                    @foreach($headers as $h)
                        <th class="py-3.5 px-5 bg-[#D9D9D9] first:rounded-l-[12px] last:rounded-r-[12px] font-bold tracking-tight text-gray-900 text-sm">
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
