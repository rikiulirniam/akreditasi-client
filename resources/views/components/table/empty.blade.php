@props([
    'colspan' => 1,
    'message' => 'Belum ada data tersedia',
    'submessage' => '',
])

<tr>
    <td colspan="{{ $colspan }}" class="py-12 px-6 border-b border-gray-200 text-center text-gray-400">
        <p class="text-sm font-semibold text-gray-600">{{ $message }}</p>
        @if($submessage)
            <p class="text-xs text-gray-400 mt-1">{{ $submessage }}</p>
        @endif
    </td>
</tr>
