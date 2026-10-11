@props(['name' => 'file', 'id' => null, 'accept' => null, 'required' => false, 'help' => null])

@php
    $id = $id ?? $name;
@endphp

<div class="w-full min-w-0">
    <input type="file"
           name="{{ $name }}"
           id="{{ $id }}"
           @if($accept) accept="{{ $accept }}" @endif
           @if($required) required @endif
           {{ $attributes->merge(['class' => 'block w-full min-w-0 min-h-[44px] text-sm text-slate-900 dark:text-slate-100 border-2 border-slate-400 dark:border-slate-500 rounded-lg cursor-pointer bg-white dark:bg-slate-950 focus:outline-hidden focus-visible:ring-4 focus-visible:ring-emerald-500 dark:[color-scheme:dark] file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:font-semibold file:text-sm file:bg-emerald-100 file:text-emerald-900 hover:file:bg-emerald-200 dark:file:bg-emerald-500/15 dark:file:text-emerald-300 dark:hover:file:bg-emerald-500/25']) }}>
    @if($help)
        <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
            {{ $help }}
        </p>
    @endif
</div>
