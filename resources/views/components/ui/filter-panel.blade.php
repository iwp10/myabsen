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

    {{-- SATU Formulir Filter (tanpa duplikasi markup form atau input) --}}
    <form method="{{ strtoupper($method) === 'POST' ? 'POST' : 'GET' }}"
          action="{{ $action }}"
          class="w-full space-y-3">

        {{-- Baris Kontrol Utama di Mobile (< md) & Pencarian Terpadu --}}
        <div class="space-y-2.5">
            {{-- Kotak Pencarian Teks di Luar Panel (Jika Diberikan) - Selalu terlihat di semua lebar --}}
            @if(isset($searchSlot))
                <div class="w-full">
                    {{ $searchSlot }}
                </div>
            @endif

            {{-- Tombol Buka/Tutup Filter Mobile (< md) - Lebar penuh, min tinggi 44px, ikon x-ui.icon, TIDAK PERNAH hidden/x-cloak --}}
            <div class="md:hidden">
                <button type="button"
                        @click="open = !open"
                        :aria-expanded="open ? 'true' : 'false'"
                        class="w-full inline-flex items-center justify-between px-4 py-2.5 min-h-[44px] rounded-xl border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-xs uppercase tracking-wider shadow-xs hover:bg-slate-50 dark:hover:bg-slate-700/60 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/50 transition">
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
            </div>

            {{-- Deretan Chip Filter Aktif (Tampil di semua lebar dengan tombol hapus 44px) --}}
            @if(!empty($activeFilters))
                <div class="flex flex-wrap items-center gap-2 pt-0.5">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 mr-1">Filter aktif:</span>
                    @foreach($activeFilters as $key => $filter)
                        @php
                            $url = $filter['clear_url'] ?? $filter['removeUrl'] ?? $filter['url'] ?? '#';
                            $label = $filter['label'] ?? ($key . ': ' . ($filter['value'] ?? ''));
                        @endphp
                        <span class="inline-flex items-center rounded-full text-xs font-semibold bg-emerald-100 text-emerald-900 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/50 pl-3 pr-1 py-0.5 min-h-[44px]">
                            <span>{{ $label }}</span>
                            <a href="{{ $url }}"
                               class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] p-2 hover:text-emerald-950 dark:hover:text-white rounded-full transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                               title="Hapus filter {{ $label }}"
                               aria-label="Hapus filter {{ $label }}">
                                <x-ui.icon name="x" size="sm" class="w-3.5 h-3.5 text-emerald-800 dark:text-emerald-300" />
                            </a>
                        </span>
                    @endforeach
                    @if(!empty($resetUrl) && $count > 0)
                        <a href="{{ $resetUrl }}"
                           class="inline-flex items-center justify-center px-3 py-1.5 min-h-[44px] rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-200 underline focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                           title="Reset semua filter">
                            Reset Semua
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- Formulir Filter: Panel Lipat di Mobile (<md) saat JS aktif, Terbuka Inline di Desktop (md:) dan No-JS --}}
        <div x-show="open"
             x-cloak
             class="filter-panel-content bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-xl p-4 sm:p-5 shadow-xs transition md:!block">

            {{-- Header Panel di Mobile (< md) --}}
            <div class="flex items-center justify-between pb-3 mb-4 border-b-2 border-slate-200 dark:border-slate-700 md:hidden">
                <h4 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                    <x-ui.icon name="filter" size="sm" class="text-emerald-700 dark:text-emerald-400" />
                    <span>{{ $title }}</span>
                </h4>
                <button type="button"
                        @click="open = false"
                        class="p-2 rounded-lg text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white min-h-[44px] min-w-[44px] flex items-center justify-center focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                        aria-label="Tutup panel filter">
                    <x-ui.icon name="x" size="md" />
                </button>
            </div>

            {{-- Slot Konten Kontrol Input & Dropdown --}}
            <div class="space-y-3 md:space-y-0">
                {{ $slot }}
            </div>

            {{-- Baris Aksi Bawah di Mobile (< md) --}}
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
        </div>
    </form>

    {{-- Progressive Enhancement Fallback bila JS dimatikan --}}
    <noscript>
        <div class="p-3 bg-amber-50 text-amber-900 border-2 border-amber-300 rounded-xl text-xs dark:bg-amber-950/30 dark:text-amber-200 dark:border-amber-700">
            Panel filter dibuka secara otomatis karena JavaScript tidak aktif pada peramban Anda.
        </div>
    </noscript>
</div>
