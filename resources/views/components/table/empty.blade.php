@props([
    'colspan' => 1,
    'message' => 'Belum ada data tersedia',
    'submessage' => '',
])

<tr>
    <td colspan="{{ $colspan }}" class="py-12 px-6 border border-[#D5D5D5] rounded-[12px] text-center bg-[#F3F3F3] text-gray-600">
        <p class="text-base font-bold text-gray-800">{{ $message }}</p>
        @if($submessage)
            <p class="text-sm text-gray-500 mt-1">{{ $submessage }}</p>
        @endif
    </td>
</tr>
