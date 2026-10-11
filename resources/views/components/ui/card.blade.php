@props(['padding' => 'p-5 sm:p-6'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-xl shadow-xs ' . $padding]) }}>
    {{ $slot }}
</div>
