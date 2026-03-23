@props(['text', 'position' => 'left'])

@php
$positionClasses = match($position) {
    'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
    'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
    default => 'right-full top-1/2 -translate-y-1/2 mr-2', // left
};

$arrowClasses = match($position) {
    'top' => 'top-full left-1/2 -translate-x-1/2 -mt-1',
    'bottom' => 'bottom-full left-1/2 -translate-x-1/2 -mb-1',
    'right' => 'right-full top-1/2 -translate-y-1/2 -mr-1',
    default => 'left-full top-1/2 -translate-y-1/2 -ml-1', // left
};
@endphp

<div class="relative inline-block group" x-data="{ show: false }">
    <div @mouseenter="show = true" @mouseleave="show = false" class="inline-block">
        {{ $slot }}
    </div>
    
    <div x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute {{ $positionClasses }} z-50 px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-lg whitespace-nowrap pointer-events-none"
         style="display: none;">
        {{ $text }}
        <div class="absolute {{ $arrowClasses }} w-2 h-2 bg-gray-900 transform rotate-45"></div>
    </div>
</div>
