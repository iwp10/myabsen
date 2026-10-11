@props([
    'tahunAjaran' => null,
    'tahun_ajaran' => null,
    'semester' => null,
    'selected' => null,
    'name' => 'bulan',
    'id' => 'bulan',
])

@php
$ta = $tahunAjaran ?? $tahun_ajaran;
$selectedVal = $selected ?? request($name);
$daftarBulan = \App\Support\Ui::getDaftarBulanPeriode($ta, $semester, $selectedVal);
@endphp

<select id="{{ $id }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'mt-1 block w-full border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-lg text-base sm:text-xs min-h-[44px] py-2 px-3 font-medium']) }}>
    <option value="">Semua bulan</option>
    @foreach($daftarBulan as $item)
        <option value="{{ $item['value'] }}" {{ (string) $selectedVal === (string) $item['value'] ? 'selected' : '' }}>
            {{ $item['label'] }}
        </option>
    @endforeach
</select>
