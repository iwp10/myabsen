@props([
    'size' => 'md',
    'name' => null,
])

@php
$sizeClass = \App\Support\Ui::iconClass($size);
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
    @if($name === 'arrow-right')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M5 12l14 0" />
        <path d="M13 18l6 -6" />
        <path d="M13 6l6 6" />
    @elseif($name === 'chevron-right')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M9 6l6 6l-6 6" />
    @elseif($name === 'check')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M5 12l5 5l10 -10" />
    @elseif($name === 'x')
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M18 6l-12 12" />
        <path d="M6 6l12 12" />
    @else
        {{ $slot }}
    @endif
</svg>
