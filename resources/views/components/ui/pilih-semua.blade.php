@props([
    'ariaLabel' => 'Pilih semua di halaman ini',
    'color' => 'blue',
    'label' => null,
    'count' => null,
])

<div class="inline-flex items-center gap-2 min-h-[44px]">
    <input type="checkbox"
           :checked="semuaTerpilih"
           x-effect="$el.indeterminate = Boolean(sebagian)"
           @change="toggleSemua($event.target.checked)"
           aria-label="{{ $ariaLabel }}"
           {{ $attributes->merge(['class' => 'rounded border-2 border-slate-300 dark:border-slate-600 dark:bg-slate-900 ' . match($color) {
               'red' => 'text-red-600 focus:ring-red-500 dark:text-red-500 dark:focus:ring-red-600',
               'emerald' => 'text-emerald-600 focus:ring-emerald-500 dark:text-emerald-500 dark:focus:ring-emerald-600',
               default => 'text-blue-600 focus:ring-blue-500 dark:text-blue-500 dark:focus:ring-blue-600',
           } . ' shadow-xs dark:[color-scheme:dark] cursor-pointer']) }}>
    @if($label)
        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $label }}</span>
    @elseif($count !== null)
        <span class="sr-only">Pilih semua di halaman ini ({{ $count }})</span>
    @else
        {{ $slot }}
    @endif
</div>
