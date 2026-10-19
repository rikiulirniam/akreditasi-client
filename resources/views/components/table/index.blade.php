@props([
    'headers' => [],
])

<div class="w-full overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'w-full text-left text-xs sm:text-sm border-collapse']) }}>
        <thead>
            <tr class="text-black font-bold border-t border-b border-[#D1D5DB] bg-white">
                @if(isset($header))
                    {{ $header }}
                @elseif(!empty($headers))
                    @foreach($headers as $h)
                        <th class="py-3.5 px-4 font-bold tracking-tight text-black text-[13px] sm:text-sm">
                            {{ $h }}
                        </th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody class="text-black divide-y divide-[#E5E7EB]">
            {{ $slot }}
        </tbody>
    </table>
</div>
