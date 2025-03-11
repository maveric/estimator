@props([
    'label',
    'type' => 'text',
    'name',
    'model' => null,
    'step' => null,
    'error' => null
])

<div>
    <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <input 
        type="{{ $type }}"
        @if($step) step="{{ $step }}" @endif
        @if($model) wire:model="{{ $model }}" @endif
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500']) }}
    >
    @error($name) 
        <span class="text-red-500 text-xs">{{ $message }}</span> 
    @enderror
</div> 