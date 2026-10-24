@props([
    'siswaId',
    'siswaNama' => '',
    'selected' => 'hadir',
])

<fieldset class="w-full">
    <legend class="sr-only">Status kehadiran untuk {{ $siswaNama ?: 'siswa #' . $siswaId }}</legend>
    <div class="grid grid-cols-2 min-[360px]:grid-cols-4 gap-2">
        {{-- Tombol Hadir --}}
        <label class="cursor-pointer flex flex-col items-center justify-center gap-1 p-2 rounded-xl border-2 text-sm font-bold transition select-none min-h-[48px] focus-within:ring-4 focus-within:ring-emerald-500/50"
               :class="students[{{ $siswaId }}]?.status === 'hadir'
                   ? 'bg-emerald-700 text-white border-emerald-800 dark:bg-emerald-500 dark:text-slate-950 dark:border-emerald-300 shadow-xs'
                   : 'bg-emerald-50/70 text-emerald-950 border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-200 dark:border-emerald-500/50 hover:bg-emerald-100 dark:hover:bg-emerald-500/25'">
            <input type="radio"
                   x-model="students[{{ $siswaId }}].status"
                   name="siswa[{{ $siswaId }}][status]"
                   value="hadir"
                   class="sr-only"
                   aria-label="Hadir">
            <x-ui.icon name="check" size="sm" class="w-4 h-4 shrink-0" aria-hidden="true" />
            <span class="text-sm font-bold tracking-tight whitespace-nowrap">Hadir</span>
        </label>

        {{-- Tombol Izin --}}
        <label class="cursor-pointer flex flex-col items-center justify-center gap-1 p-2 rounded-xl border-2 text-sm font-bold transition select-none min-h-[48px] focus-within:ring-4 focus-within:ring-blue-500/50"
               :class="students[{{ $siswaId }}]?.status === 'izin'
                   ? 'bg-blue-700 text-white border-blue-800 dark:bg-blue-500 dark:text-slate-950 dark:border-blue-300 shadow-xs'
                   : 'bg-blue-50/70 text-blue-950 border-blue-400 dark:bg-blue-500/15 dark:text-blue-200 dark:border-blue-500/50 hover:bg-blue-100 dark:hover:bg-blue-500/25'">
            <input type="radio"
                   x-model="students[{{ $siswaId }}].status"
                   name="siswa[{{ $siswaId }}][status]"
                   value="izin"
                   class="sr-only"
                   aria-label="Izin">
            <x-ui.icon name="info" size="sm" class="w-4 h-4 shrink-0" aria-hidden="true" />
            <span class="text-sm font-bold tracking-tight whitespace-nowrap">Izin</span>
        </label>

        {{-- Tombol Sakit --}}
        <label class="cursor-pointer flex flex-col items-center justify-center gap-1 p-2 rounded-xl border-2 text-sm font-bold transition select-none min-h-[48px] focus-within:ring-4 focus-within:ring-amber-500/50"
               :class="students[{{ $siswaId }}]?.status === 'sakit'
                   ? 'bg-amber-600 text-slate-950 border-amber-700 dark:bg-amber-400 dark:text-slate-950 dark:border-amber-200 shadow-xs'
                   : 'bg-amber-50/70 text-amber-950 border-amber-400 dark:bg-amber-500/15 dark:text-amber-200 dark:border-amber-500/50 hover:bg-amber-100 dark:hover:bg-amber-500/25'">
            <input type="radio"
                   x-model="students[{{ $siswaId }}].status"
                   name="siswa[{{ $siswaId }}][status]"
                   value="sakit"
                   class="sr-only"
                   aria-label="Sakit">
            <x-ui.icon name="plus" size="sm" class="w-4 h-4 shrink-0" aria-hidden="true" />
            <span class="text-sm font-bold tracking-tight whitespace-nowrap">Sakit</span>
        </label>

        {{-- Tombol Alpa --}}
        <label class="cursor-pointer flex flex-col items-center justify-center gap-1 p-2 rounded-xl border-2 text-sm font-bold transition select-none min-h-[48px] focus-within:ring-4 focus-within:ring-red-500/50"
               :class="students[{{ $siswaId }}]?.status === 'alpa'
                   ? 'bg-red-700 text-white border-red-800 dark:bg-red-500 dark:text-slate-950 dark:border-red-300 shadow-xs'
                   : 'bg-red-50/70 text-red-950 border-red-400 dark:bg-red-500/15 dark:text-red-200 dark:border-red-500/50 hover:bg-red-100 dark:hover:bg-red-500/25'">
            <input type="radio"
                   x-model="students[{{ $siswaId }}].status"
                   name="siswa[{{ $siswaId }}][status]"
                   value="alpa"
                   class="sr-only"
                   aria-label="Alpa">
            <x-ui.icon name="x" size="sm" class="w-4 h-4 shrink-0" aria-hidden="true" />
            <span class="text-sm font-bold tracking-tight whitespace-nowrap">Alpa</span>
        </label>
    </div>
</fieldset>
