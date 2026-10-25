@props([
    'colspan' => 1,
    'message' => 'Tidak ada data ditemukan.',
    'submessage' => null,
])

<tr>
    <td colspan="{{ $colspan }}" class="py-12 px-6 border-b border-gray-200 text-center text-gray-500">
        <p class="text-base font-semibold text-gray-700">{{ $message }}</p>
        @if($submessage)
            <p class="text-xs text-gray-400 mt-1">{{ $submessage }}</p>
        @endif
    </td>
</tr>
