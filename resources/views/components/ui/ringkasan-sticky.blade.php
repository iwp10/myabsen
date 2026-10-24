@props([
    'totalSiswa' => 0,
])

<div class="sticky top-16 z-20 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-2 border-slate-300 dark:border-slate-600 rounded-xl p-2.5 sm:p-3 shadow-md transition mb-3">
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 sm:gap-3">
        {{-- Ringkasan Jumlah Hadir, Izin, Sakit, Alpa --}}
        <div class="grid grid-cols-2 min-[360px]:grid-cols-4 gap-1.5 sm:gap-2 flex-1 text-center">
            <div class="p-1.5 rounded-lg bg-emerald-100 text-emerald-950 border border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-200 dark:border-emerald-500/50">
                <span class="block text-xs font-bold uppercase tracking-wider">Hadir</span>
                <span class="font-extrabold text-base sm:text-lg tabular-nums" x-text="summary.hadir">0</span>
            </div>
            <div class="p-1.5 rounded-lg bg-blue-100 text-blue-950 border border-blue-400 dark:bg-blue-500/15 dark:text-blue-200 dark:border-blue-500/50">
                <span class="block text-xs font-bold uppercase tracking-wider">Izin</span>
                <span class="font-extrabold text-base sm:text-lg tabular-nums" x-text="summary.izin">0</span>
            </div>
            <div class="p-1.5 rounded-lg bg-amber-100 text-amber-950 border border-amber-400 dark:bg-amber-500/15 dark:text-amber-200 dark:border-amber-500/50">
                <span class="block text-xs font-bold uppercase tracking-wider">Sakit</span>
                <span class="font-extrabold text-base sm:text-lg tabular-nums" x-text="summary.sakit">0</span>
            </div>
            <div class="p-1.5 rounded-lg bg-red-100 text-red-950 border border-red-400 dark:bg-red-500/15 dark:text-red-200 dark:border-red-500/50">
                <span class="block text-xs font-bold uppercase tracking-wider">Alpa</span>
                <span class="font-extrabold text-base sm:text-lg tabular-nums" x-text="summary.alpa">0</span>
            </div>
        </div>

        {{-- Tombol Cepat "Semua Hadir" & Total Siswa --}}
        <div class="flex items-center justify-between sm:justify-end gap-2 shrink-0">
            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 sm:text-right hidden sm:block">
                <span>Total: <strong class="text-slate-900 dark:text-white" x-text="summary.total || {{ $totalSiswa }}"></strong> siswa</span>
            </div>
            <button type="button"
                    @click="setSemuaHadir()"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3 py-2 min-h-[44px] rounded-xl border-2 border-emerald-500 dark:border-emerald-400 bg-emerald-50 hover:bg-emerald-100 text-emerald-950 dark:bg-emerald-500/15 dark:hover:bg-emerald-500/25 dark:text-emerald-300 font-bold text-xs uppercase tracking-wider transition shadow-xs focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/50">
                <x-ui.icon name="check" size="sm" class="text-emerald-700 dark:text-emerald-400" />
                <span>Semua Hadir</span>
            </button>
        </div>
    </div>
</div>
