@props([
    'action' => '',
    'method' => 'GET',
    'activeCount' => null,
    'resetUrl' => '',
    'activeFilters' => [],
    'title' => 'Filter & Penyaringan',
    'searchSlot' => null,
])

@php
    $count = $activeCount !== null ? $activeCount : count($activeFilters);
@endphp

<div x-data="{ open: false }"
     @keydown.escape.window="open = false"
     class="w-full space-y-3">

    {{-- Baris Kontrol Utama di Mobile (< md) --}}
    <div class="md:hidden space-y-2.5">
        {{-- Pencarian Cepat di Luar Panel (Jika Diberikan) --}}
        @if(isset($searchSlot))
            <div class="w-full">
                {{ $searchSlot }}
            </div>
        @endif

        <div class="flex items-center gap-2">
            {{-- Tombol Buka/Tutup Filter Mobile --}}
            <button type="button"
                    @click="open = !open"
                    :aria-expanded="open"
                    class="flex-1 inline-flex items-center justify-between px-4 py-2.5 min-h-[44px] rounded-xl border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-xs uppercase tracking-wider shadow-xs hover:bg-slate-50 dark:hover:bg-slate-700/60 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/50 transition">
                <span class="inline-flex items-center gap-2">
                    <x-ui.icon name="filter" size="sm" class="text-emerald-700 dark:text-emerald-400" />
                    <span>Filter & Penyaringan</span>
                </span>

                <span class="inline-flex items-center gap-1.5">
                    @if($count > 0)
                        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-extrabold rounded-full bg-emerald-700 text-white dark:bg-emerald-400 dark:text-slate-950">
                            {{ $count }}
                        </span>
                    @endif
                    <x-ui.icon name="chevron-down" size="sm" class="transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                </span>
            </button>

            @if($count > 0 && !empty($resetUrl))
                <a href="{{ $resetUrl }}"
                   class="inline-flex items-center justify-center px-3.5 py-2.5 min-h-[44px] rounded-xl border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-bold uppercase tracking-wider transition shadow-xs"
                   title="Reset semua filter">
                    Reset
                </a>
            @endif
        </div>

        {{-- Deretan Chip Filter Aktif yang Bisa Dihapus di Mobile --}}
        @if(!empty($activeFilters))
            <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 mr-1">Filter aktif:</span>
                @foreach($activeFilters as $key => $filter)
                    @php
                        $url = $filter['clear_url'] ?? $filter['removeUrl'] ?? $filter['url'] ?? '#';
                        $label = $filter['label'] ?? ($key . ': ' . ($filter['value'] ?? ''));
                    @endphp
                    <a href="{{ $url }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50 hover:bg-emerald-200 dark:hover:bg-emerald-500/25 transition min-h-[32px]"
                       title="Hapus filter {{ $label }}">
                        <span>{{ $label }}</span>
                        <x-ui.icon name="x" size="sm" class="w-3.5 h-3.5 text-emerald-800 dark:text-emerald-300" />
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Formulir Filter: Panel Lipat di Mobile, Terbuka Inline di Desktop --}}
    <form method="{{ strtoupper($method) === 'POST' ? 'POST' : 'GET' }}"
          action="{{ $action }}"
          :class="open ? 'block' : 'hidden md:block'"
          class="hidden md:block bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-xl p-4 sm:p-5 shadow-xs transition">

        <div class="flex items-center justify-between pb-3 mb-4 border-b-2 border-slate-200 dark:border-slate-700 md:hidden">
            <h4 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                <x-ui.icon name="filter" size="sm" class="text-emerald-700 dark:text-emerald-400" />
                <span>{{ $title }}</span>
            </h4>
            <button type="button"
                    @click="open = false"
                    class="p-1.5 rounded-lg text-slate-600 hover:text-slate-800 dark:text-slate-300 dark:hover:text-slate-100 min-h-[44px] min-w-[44px] flex items-center justify-center"
                    aria-label="Tutup panel filter">
                <x-ui.icon name="x" size="md" />
            </button>
        </div>

        {{-- Slot Konten Kontrol Input & Dropdown --}}
        <div class="space-y-3 md:space-y-0">
            {{ $slot }}
        </div>

        {{-- Baris Bawah di Mobile (< md) --}}
        <div class="mt-4 pt-3 border-t-2 border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-stretch gap-2 md:hidden">
            <button type="submit"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 min-h-[44px] rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 font-bold text-xs uppercase tracking-wider shadow-xs transition">
                Terapkan Filter
            </button>
            @if(!empty($resetUrl) && $count > 0)
                <a href="{{ $resetUrl }}"
                   class="w-full inline-flex items-center justify-center px-4 py-2.5 min-h-[44px] rounded-xl border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 font-bold text-xs uppercase tracking-wider text-center transition shadow-xs">
                    Reset Semua Filter
                </a>
            @endif
        </div>
    </form>

    {{-- Banner Filter Aktif di Desktop (md: ke atas) --}}
    @if(!empty($activeFilters))
        <div class="hidden md:flex px-4 py-2.5 bg-emerald-50 dark:bg-emerald-950/20 border-2 border-emerald-300 dark:border-emerald-700 rounded-xl items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-bold text-emerald-800 dark:text-emerald-300">Filter aktif:</span>
                @foreach($activeFilters as $key => $filter)
                    @php
                        $url = $filter['clear_url'] ?? $filter['removeUrl'] ?? $filter['url'] ?? '#';
                        $label = $filter['label'] ?? ($key . ': ' . ($filter['value'] ?? ''));
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50 font-semibold">
                        <span>{{ $label }}</span>
                        <a href="{{ $url }}" class="hover:text-emerald-950 dark:hover:text-white font-bold ml-1" aria-label="Hapus filter {{ $label }}">×</a>
                    </span>
                @endforeach
            </div>
            @if(!empty($resetUrl))
                <a href="{{ $resetUrl }}" class="font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-200 underline">
                    Reset Semua Filter
                </a>
            @endif
        </div>
    @endif

    {{-- Progressive Enhancement Fallback bila JS dimatikan --}}
    <noscript>
        <div class="p-3 bg-amber-50 text-amber-900 border-2 border-amber-300 rounded-xl text-xs dark:bg-amber-950/30 dark:text-amber-200 dark:border-amber-700">
            Panel filter dibuka secara otomatis karena JavaScript tidak aktif pada peramban Anda.
        </div>
    </noscript>
</div>
