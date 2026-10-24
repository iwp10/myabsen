@props([
    'size' => 'md',
    'name' => null,
])

@php
$sizeClass = \App\Support\Ui::iconClass($size);

// Validasi nama ikon yang didukung
$supportedIcons = [
    'arrow-left',
    'arrow-right',
    'chevron-left',
    'chevron-right',
    'chevron-down',
    'menu',
    'check',
    'x',
    'info',
    'plus',
    'clock',
    'calendar',
    'calendar-check',
    'history',
    'book',
    'search',
    'filter',
    'user',
    'layout-grid',
    'file-text',
    'chart-bar',
];

if ($name !== null && ! in_array($name, $supportedIcons, true) && $slot->isEmpty()) {
    if (app()->environment('local', 'testing')) {
        throw new \InvalidArgumentException("Ikon '{$name}' tidak dikenali pada x-ui.icon. Daftarkan ikon di resources/views/components/ui/icon.blade.php.");
    }
}
@endphp

<svg xmlns="http://www.w3.org/2000/svg"
     width="24"
     height="24"
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="2"
     stroke-linecap="round"
     stroke-linejoin="round"
     {{ $attributes->merge(['class' => $sizeClass]) }}>
    @if($name === 'arrow-left')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M5 12l14 0" />
        <path d="M5 12l6 6" />
        <path d="M5 12l6 -6" />
    @elseif($name === 'arrow-right')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M5 12l14 0" />
        <path d="M13 18l6 -6" />
        <path d="M13 6l6 6" />
    @elseif($name === 'chevron-left')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M15 6l-6 6l6 6" />
    @elseif($name === 'chevron-right')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M9 6l6 6l-6 6" />
    @elseif($name === 'chevron-down')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M6 9l6 6l6 -6" />
    @elseif($name === 'menu')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M4 6l16 0" />
        <path d="M4 12l16 0" />
        <path d="M4 18l16 0" />
    @elseif($name === 'check')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M5 12l5 5l10 -10" />
    @elseif($name === 'x')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M18 6l-12 12" />
        <path d="M6 6l12 12" />
    @elseif($name === 'info')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
        <path d="M12 9h.01" />
        <path d="M11 12h1v4h1" />
    @elseif($name === 'plus')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M12 5l0 14" />
        <path d="M5 12l14 0" />
    @elseif($name === 'clock')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
        <path d="M12 7v5l3 3" />
    @elseif($name === 'calendar')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
        <path d="M16 3v4" />
        <path d="M8 3v4" />
        <path d="M4 11h16" />
    @elseif($name === 'calendar-check')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" />
        <path d="M16 3v4" />
        <path d="M8 3v4" />
        <path d="M4 11h16" />
        <path d="M15 19l2 2l4 -4" />
    @elseif($name === 'history')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M12 8l0 4l2 2" />
        <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
    @elseif($name === 'book')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0" />
        <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0" />
        <path d="M3 6l0 13" />
        <path d="M12 6l0 13" />
    @elseif($name === 'search')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
        <path d="M21 21l-6 -6" />
    @elseif($name === 'filter')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.828 4.828a2 2 0 0 0 -.586 1.414v4.172l-4 2v-6.172a2 2 0 0 0 -.586 -1.414l-4.828 -4.828a2 2 0 0 1 -.586 -1.414v-2.172z" />
    @elseif($name === 'user')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
    @elseif($name === 'layout-grid')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
        <path d="M14 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
        <path d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
        <path d="M14 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
    @elseif($name === 'file-text')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
        <path d="M9 9h1" />
        <path d="M9 13h6" />
        <path d="M9 17h6" />
    @elseif($name === 'chart-bar')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M3 12m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
        <path d="M9 8m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
        <path d="M15 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
        <path d="M4 20l14 0" />
    @elseif($slot->isNotEmpty())
        {{ $slot }}
    @else
        {{-- Ikon cadangan netral (lingkaran) di mode produksi bila nama tak terdaftar --}}
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <circle cx="12" cy="12" r="9" />
    @endif
</svg>
