@props(['action' => null, 'method' => 'GET', 'formClass' => ''])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-xl p-4 sm:p-5 shadow-xs mb-6']) }}>
    @if($action)
        <form action="{{ $action }}" method="{{ $method }}" class="{{ $formClass ?: 'flex flex-col md:flex-row md:items-center md:justify-between gap-3' }}">
            {{ $slot }}
        </form>
    @else
        <div class="{{ $formClass ?: 'flex flex-col md:flex-row md:items-center md:justify-between gap-3' }}">
            {{ $slot }}
        </div>
    @endif
</div>
