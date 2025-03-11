@props([
    'variant' => 'primary',
    'type' => 'button'
])

@php
    $baseClasses = 'px-4 py-2 text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2';
    
    $variants = [
        'primary' => 'text-white bg-blue-600 border border-transparent hover:bg-blue-700 focus:ring-blue-500',
        'secondary' => 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:ring-blue-500',
        'danger' => 'text-red-600 hover:text-red-900',
        'link' => 'text-blue-600 hover:text-blue-900'
    ];
    
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<button 
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</button> 