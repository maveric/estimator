@props(['disabled' => false])

<input type="checkbox"
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge(['class' => 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600']) !!}>
