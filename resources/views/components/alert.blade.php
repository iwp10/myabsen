@props(['type' => 'success', 'message' => null, 'autoDismiss' => false])

@php
$isSuccess = $type === 'success';
$isError = $type === 'error' || $type === 'danger';
$isWarning = $type === 'warning';

$containerClasses = match ($type) {
    'success' => 'bg-green-50 dark:bg-green-950/40 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200',
    'error', 'danger' => 'bg-red-50 dark:bg-red-950/40 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200',
    'warning' => 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200',
    default => 'bg-blue-50 dark:bg-blue-950/40 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200',
};

$iconColor = match ($type) {
    'success' => 'text-green-600 dark:text-green-400',
    'error', 'danger' => 'text-red-600 dark:text-red-400',
    'warning' => 'text-amber-600 dark:text-amber-400',
    default => 'text-blue-600 dark:text-blue-400',
};
@endphp

<div
    @if ($autoDismiss)
        x-data="{ show: true }"
        x-show="show"
        x-transition
        x-init="setTimeout(() => show = false, 5000)"
    @endif
    {{ $attributes->merge(['class' => "p-4 border rounded-lg flex items-center gap-3 shadow-sm {$containerClasses}"]) }}
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
    @endif

    <span class="font-medium text-sm">
        {{ $message ?? $slot }}
    </span>
</div>
