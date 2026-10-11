@props(['type' => 'success', 'message' => null, 'autoDismiss' => false])

@php
$isSuccess = $type === 'success';
$isError = $type === 'error' || $type === 'danger';
$isWarning = $type === 'warning';

$containerClasses = match ($type) {
    'success' => 'bg-emerald-100 text-emerald-900 border-2 border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50',
    'error', 'danger' => 'bg-red-100 text-red-900 border-2 border-red-400 dark:bg-red-500/15 dark:text-red-300 dark:border-red-500/50',
    'warning' => 'bg-amber-100 text-amber-900 border-2 border-amber-400 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/50',
    default => 'bg-blue-100 text-blue-900 border-2 border-blue-400 dark:bg-blue-500/15 dark:text-blue-300 dark:border-blue-500/50',
};

$iconColor = match ($type) {
    'success' => 'text-emerald-700 dark:text-emerald-400',
    'error', 'danger' => 'text-red-700 dark:text-red-400',
    'warning' => 'text-amber-700 dark:text-amber-400',
    default => 'text-blue-700 dark:text-blue-400',
};
@endphp

<div
    @if ($autoDismiss)
        x-data="{ show: true }"
        x-show="show"
        x-transition
        x-init="setTimeout(() => show = false, 5000)"
    @endif
    {{ $attributes->merge(['class' => "p-4 rounded-xl flex items-center gap-3 shadow-sm {$containerClasses}"]) }}
    role="alert"
>
    @if ($isSuccess)
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 {{ $iconColor }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12l5 5l10 -10"></path>
        </svg>
    @elseif ($isError)
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 {{ $iconColor }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6l-12 12"></path>
            <path d="M6 6l12 12"></path>
        </svg>
    @elseif ($isWarning)
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 {{ $iconColor }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 9v4"></path>
            <path d="M12 17h.01"></path>
            <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"></path>
        </svg>
    @else
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 {{ $iconColor }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
            <path d="M12 9h.01" />
            <path d="M11 12h1v4h1" />
        </svg>
    @endif

    <span class="font-medium text-sm">
        {{ $message ?? $slot }}
    </span>
</div>
