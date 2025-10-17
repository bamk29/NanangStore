@props([
    'type' => 'info',
    'message' => null,
])

@php
    $baseClasses = 'text-white px-6 py-4 border-0 rounded relative mb-4 shadow-lg';
    $typeClasses = [
        'success' => 'bg-green-500',
        'error'   => 'bg-red-500',
        'warning' => 'bg-yellow-500',
        'info'    => 'bg-blue-500',
    ];
    $wrapperClasses = $baseClasses . ' ' . ($typeClasses[$type] ?? $typeClasses['info']);
@endphp

<div {{ $attributes->merge(['class' => $wrapperClasses]) }} role="alert">
    <span class="inline-block align-middle">
        @if($message)
            {{ $message }}
        @else
            {{ $slot }}
        @endif
    </span>
</div>
