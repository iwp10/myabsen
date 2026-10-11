@props([
    'tahunAjaran' => null,
    'tahun_ajaran' => null,
    'semester' => null,
    'selected' => null,
    'name' => 'bulan',
    'id' => 'bulan',
    'wrapperClass' => '',
])

@php
$ta = $tahunAjaran ?? $tahun_ajaran;
$selectedVal = $selected ?? request($name);
$daftarBulan = \App\Support\Ui::getDaftarBulanPeriode($ta, $semester, $selectedVal);
@endphp

<x-ui.select id="{{ $id }}" name="{{ $name }}" :wrapper-class="$wrapperClass" {{ $attributes }}>
    <option value="">Semua bulan</option>
    @foreach($daftarBulan as $item)
        <option value="{{ $item['value'] }}" {{ (string) $selectedVal === (string) $item['value'] ? 'selected' : '' }}>
            {{ $item['label'] }}
        </option>
    @endforeach
</x-ui.select>
