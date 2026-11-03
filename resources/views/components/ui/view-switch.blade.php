@props([
    'active' => request()->routeIs('siswa.jadwal*') ? 'jadwal' : 'mapel',
])

@php
    $isMapel = ($active === 'mapel');
    $isJadwal = ($active === 'jadwal');
@endphp

<div class="w-full sm:max-w-md">
    <nav aria-label="Pilihan tampilan mata pelajaran" class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border-2 border-slate-300 dark:border-slate-600">
        {{-- Tombol Tampilan Per Mata Pelajaran --}}
        <a href="{{ route('siswa.mapel') }}"
           @if($isMapel) aria-current="page" @endif
           class="flex-1 min-h-[44px] py-2 px-3 rounded-lg text-xs sm:text-sm transition flex items-center justify-center gap-1.5 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 dark:focus-visible:ring-emerald-400 {{ $isMapel ? 'bg-white dark:bg-slate-900 text-emerald-800 dark:text-emerald-300 shadow-xs border-2 border-emerald-600 dark:border-emerald-400 font-bold' : 'text-slate-700 dark:text-slate-300 font-semibold border-2 border-transparent hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}">
            <x-ui.icon name="book" size="sm" class="shrink-0 {{ $isMapel ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-400' }}" />
            <span class="truncate">Per Mata Pelajaran</span>
        </a>

        {{-- Tombol Tampilan Per Hari --}}
        <a href="{{ route('siswa.jadwal') }}"
           @if($isJadwal) aria-current="page" @endif
           class="flex-1 min-h-[44px] py-2 px-3 rounded-lg text-xs sm:text-sm transition flex items-center justify-center gap-1.5 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 dark:focus-visible:ring-emerald-400 {{ $isJadwal ? 'bg-white dark:bg-slate-900 text-emerald-800 dark:text-emerald-300 shadow-xs border-2 border-emerald-600 dark:border-emerald-400 font-bold' : 'text-slate-700 dark:text-slate-300 font-semibold border-2 border-transparent hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}">
            <x-ui.icon name="calendar" size="sm" class="shrink-0 {{ $isJadwal ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-400' }}" />
            <span class="truncate">Per Hari</span>
        </a>
    </nav>
</div>
