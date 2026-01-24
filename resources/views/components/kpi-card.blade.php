@props(['title', 'color' => 'blue'])

@php
    $colors = [
        'blue' => 'border-l-blue-500 text-blue-600 bg-blue-50 dark:bg-blue-900/10',
        'green' => 'border-l-green-500 text-green-600 bg-green-50 dark:bg-green-900/10',
        'red' => 'border-l-red-500 text-red-600 bg-red-50 dark:bg-red-900/10',
        'amber' => 'border-l-amber-500 text-amber-600 bg-amber-50 dark:bg-amber-900/10',
    ][$color];
@endphp

<div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-5 border-l-4 {{ explode(' ', $colors)[0] }}">
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $title }}</h3>
    <div class="mt-2">
        {{ $slot }}
    </div>
</div>
