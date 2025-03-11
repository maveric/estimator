@props([
    'primaryValue',
    'secondaryValue' => null,
    'label' => null,
    'format' => null,
])

<div {{ $attributes->merge(['class' => 'text-right space-y-1']) }}>
    @if($label)
        <div class="text-xs text-gray-400">{{ $label }}</div>
    @endif
    
    <div class="font-medium">
        @if($format === 'currency')
            ${{ number_format($primaryValue, 2) }}
        @else
            {{ $primaryValue }}
        @endif
    </div>
    
    <div class="text-sm text-gray-500 min-h-[1.25rem]">
        @if($secondaryValue)
            @if($format === 'currency')
                ${{ number_format($secondaryValue, 2) }}
            @else
                {{ $secondaryValue }}
            @endif
        @endif
    </div>
</div> 