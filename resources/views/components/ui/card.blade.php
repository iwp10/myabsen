@props(['padding' => 'p-5 sm:p-6'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-xs ' . $padding]) }}>
    {{ $slot }}
</div>
