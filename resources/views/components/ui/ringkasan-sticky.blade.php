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

        {{-- Tombol Cepat "Semua Hadir", "Semua Alpa", & Total Siswa --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between lg:justify-end gap-2 shrink-0">
            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 sm:text-right hidden xl:block">
                <span>Total: <strong class="text-slate-900 dark:text-white" x-text="summary.total || {{ $totalSiswa }}"></strong> siswa</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 w-full sm:w-auto">
                <button type="button"
                        @click="setSemuaHadir()"
                        :disabled="(summary.total || {{ $totalSiswa }}) === 0"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3 py-2 min-h-[44px] rounded-xl border-2 border-emerald-500 dark:border-emerald-400 bg-emerald-50 hover:bg-emerald-100 text-emerald-950 dark:bg-emerald-500/15 dark:hover:bg-emerald-500/25 dark:text-emerald-300 font-bold text-xs uppercase tracking-wider transition shadow-xs focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <x-ui.icon name="check" size="sm" class="text-emerald-700 dark:text-emerald-400" />
                    <span>Semua Hadir</span>
                </button>

                <div class="relative w-full sm:w-auto">
                    <button type="button"
                            x-ref="btnSemuaAlpa"
                            @click="bukaKonfirmasiAlpa()"
                            :disabled="(summary.total || {{ $totalSiswa }}) === 0"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3 py-2 min-h-[44px] rounded-xl border-2 border-red-500 dark:border-red-400 bg-red-50 hover:bg-red-100 text-red-950 dark:bg-red-500/15 dark:hover:bg-red-500/25 dark:text-red-300 font-bold text-xs uppercase tracking-wider transition shadow-xs focus:outline-none focus-visible:ring-4 focus-visible:ring-red-500/50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <x-ui.icon name="x" size="sm" class="text-red-700 dark:text-red-400" />
                        <span>Semua Alpa</span>
                    </button>

                    {{-- Panel Konfirmasi Dua Langkah di Tempat --}}
                    <div x-show="konfirmasiAlpa"
                         x-cloak
                         x-ref="panelKonfirmasiAlpa"
                         @click.outside="tutupKonfirmasiAlpa()"
                         @keydown.escape.window="tutupKonfirmasiAlpa()"
                         class="absolute left-0 right-0 sm:left-auto sm:right-0 top-full mt-2 z-30 w-full sm:w-72 bg-white dark:bg-slate-800 border-2 border-red-400 dark:border-red-500/50 rounded-xl p-3 shadow-xl dark:shadow-black/40">
                        <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white mb-2.5">
                            Ubah <span x-text="summary.total || {{ $totalSiswa }}"></span> siswa menjadi Alpa?
                        </p>
                        <div class="flex items-center justify-end gap-2">
                            <button type="button"
                                    @click="tutupKonfirmasiAlpa()"
                                    class="inline-flex items-center justify-center px-3 py-2 min-h-[44px] rounded-lg border-2 border-slate-400 dark:border-slate-500 bg-white hover:bg-slate-100 text-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-100 font-bold text-xs uppercase tracking-wider transition focus:outline-none focus-visible:ring-4 focus-visible:ring-slate-400/50">
                                Batal
                            </button>
                            <button type="button"
                                    x-ref="btnKonfirmasiYa"
                                    @click="eksekusiSemuaAlpa()"
                                    class="inline-flex items-center justify-center px-3 py-2 min-h-[44px] rounded-lg border-2 border-red-700 dark:border-red-400 bg-red-700 hover:bg-red-800 text-white dark:bg-red-500 dark:hover:bg-red-400 dark:text-slate-950 font-bold text-xs uppercase tracking-wider transition shadow-xs focus:outline-none focus-visible:ring-4 focus-visible:ring-red-500/50">
                                Ya, Semua Alpa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pengumuman Aksesibilitas Pembaca Layar --}}
    <div class="sr-only" aria-live="polite" aria-atomic="true" x-text="pesanPengumuman"></div>
</div>
