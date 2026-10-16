@props([
    'type' => 'text',
    'class' => '',
])

@if($type === 'checkbox')
    <x-input.checkbox {{ $attributes->merge(['class' => $class]) }} />
@elseif($type === 'radio')
    <x-input.radio {{ $attributes->merge(['class' => $class]) }} />
@elseif($type === 'select')
    <x-input.select {{ $attributes->merge(['class' => $class]) }}>
        {{ $slot }}
    </x-input.select>
@elseif($type === 'textarea')
    <x-input.textarea {{ $attributes->merge(['class' => $class]) }}>
        {{ $slot }}
    </x-input.textarea>
@elseif($type === 'file')
    <x-input.file {{ $attributes->merge(['class' => $class]) }} />
@elseif($type === 'color')
    <x-input.color {{ $attributes->merge(['class' => $class]) }} />
@elseif($type === 'range')
    <x-input.range {{ $attributes->merge(['class' => $class]) }} />
@else
    <x-input.index type="{{ $type }}" {{ $attributes->merge(['class' => $class]) }} />
@endif
