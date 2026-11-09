@props([
    'headers' => [],
])

@php
    $hasCustomLayout = str_contains($attributes->get('class', ''), 'table-auto') || str_contains($attributes->get('class', ''), 'table-fixed');
    $defaultLayout = $hasCustomLayout ? '' : 'table-fixed ';
@endphp

<div class="w-full overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'w-full text-left border-collapse min-w-[640px] ' . $defaultLayout]) }}>
        <thead>
            <tr class="border-b border-gray-200 text-gray-900 font-semibold text-sm">
                @if(isset($header))
                    {{ $header }}
                @elseif(!empty($headers))
                    @foreach($headers as $h)
                        @php
                            $trimmedH = trim(strip_tags((string) $h));
                            $isNo = in_array(strtolower($trimmedH), ['no', 'no.', '#']);
                            $isAction = in_array(strtolower($trimmedH), ['action', 'aksi']);
                            $thClass = 'py-3.5 px-4 sm:px-6 font-semibold tracking-tight text-gray-900 text-sm border-b border-gray-200';
                            if ($isNo) {
                                $thClass .= ' w-16 text-left';
                            } elseif ($isAction) {
                                $thClass .= ' w-28 text-right';
                            }
                        @endphp
                        <th scope="col" class="{{ $thClass }}">
                            {{ $h }}
                        </th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
