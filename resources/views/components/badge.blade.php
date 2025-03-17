

<span @class([
    'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset',
    'bg-gray-50 text-gray-600 ring-gray-500/10' => $color === 'gray',
    'bg-green-50 text-green-700 ring-green-600/20' => $color === 'green',
    'bg-red-50 text-red-700 ring-red-600/10' => $color === 'red',
    'bg-yellow-50 text-yellow-700 ring-yellow-600/20' => $color === 'yellow',
    'bg-blue-50 text-blue-700 ring-blue-600/20' => $color === 'blue',
])>
    {{ $slot }}
</span>