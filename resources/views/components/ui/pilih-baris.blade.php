@props([
    'id' => null,
    'alpineId' => null,
    'name' => 'ids[]',
    'color' => 'blue',
    'ariaLabel' => null,
    'alpineAriaLabel' => null,
])

<div class="inline-flex items-center min-h-[44px]">
    @if($alpineId)
        <input type="checkbox"
               name="{{ $name }}"
               :value="{{ $alpineId }}"
               :checked="terpilih.includes(String({{ $alpineId }}))"
               @change="toggleSatu({{ $alpineId }})"
               @if($alpineAriaLabel)
                   :aria-label="{{ $alpineAriaLabel }}"
               @elseif($ariaLabel)
                   aria-label="{{ $ariaLabel }}"
               @endif
               {{ $attributes->merge(['class' => 'rounded border-2 border-slate-300 dark:border-slate-600 dark:bg-slate-900 ' . match($color) {
                   'red' => 'text-red-600 focus:ring-red-500 dark:text-red-500 dark:focus:ring-red-600',
                   'emerald' => 'text-emerald-600 focus:ring-emerald-500 dark:text-emerald-500 dark:focus:ring-emerald-600',
                   default => 'text-blue-600 focus:ring-blue-500 dark:text-blue-500 dark:focus:ring-blue-600',
               } . ' shadow-xs dark:[color-scheme:dark] cursor-pointer']) }}>
    @else
        <input type="checkbox"
               name="{{ $name }}"
               value="{{ $id }}"
               :checked="terpilih.includes(String('{{ $id }}'))"
               @change="toggleSatu('{{ $id }}')"
               @if($ariaLabel)
                   aria-label="{{ $ariaLabel }}"
               @endif
               {{ $attributes->merge(['class' => 'rounded border-2 border-slate-300 dark:border-slate-600 dark:bg-slate-900 ' . match($color) {
                   'red' => 'text-red-600 focus:ring-red-500 dark:text-red-500 dark:focus:ring-red-600',
                   'emerald' => 'text-emerald-600 focus:ring-emerald-500 dark:text-emerald-500 dark:focus:ring-emerald-600',
                   default => 'text-blue-600 focus:ring-blue-500 dark:text-blue-500 dark:focus:ring-blue-600',
               } . ' shadow-xs dark:[color-scheme:dark] cursor-pointer']) }}>
    @endif
</div>
