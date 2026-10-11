@props(['p', 'tanggal' => null, 'status' => null, 'keterangan' => null])

@php
    $chipClasses = \App\Support\Ui::pertemuanChip($status);
    $huruf = \App\Support\Ui::statusHuruf($status);
    $iconName = \App\Support\Ui::statusIcon($status);

    $nomorPertemuan = ltrim((string) $p, 'P');
    if ($tanggal instanceof \Carbon\CarbonInterface) {
        $tglStr = $tanggal->locale('id')->isoFormat('D MMM');
    } else {
        $tglStr = (string) $tanggal;
    }
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl p-2.5 flex flex-col items-center justify-between text-center min-w-0 shadow-xs transition-colors ' . $chipClasses]) }}
     @if($keterangan) title="Keterangan: {{ $keterangan }}" @endif>
    {{-- Label Pertemuan --}}
    <span class="font-extrabold text-xs tracking-tight block">
        P{{ $nomorPertemuan }}
    </span>

    {{-- Tanggal Pertemuan (Minimal 12px / text-xs) --}}
    @if($tglStr !== '')
        <span class="text-xs font-semibold leading-tight mt-1 truncate max-w-full block">
            {{ $tglStr }}
        </span>
    @endif

    {{-- Ikon + Huruf Status (H/I/S/A/-) --}}
    <div class="inline-flex items-center justify-center gap-1 font-black text-xs mt-1.5 pt-1 border-t border-current/20 w-full">
        @if($iconName === 'check')
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10" />
            </svg>
        @elseif($iconName === 'info')
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                <path d="M12 9h.01" />
                <path d="M11 12h1v4h1" />
            </svg>
        @elseif($iconName === 'plus')
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 5l0 14" />
                <path d="M5 12l14 0" />
            </svg>
        @elseif($iconName === 'x')
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M18 6l-12 12" />
                <path d="M6 6l12 12" />
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                <path d="M12 7v5l3 3" />
            </svg>
        @endif
        <span>{{ $huruf }}</span>
    </div>
</div>
