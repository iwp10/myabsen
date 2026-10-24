@props([
    'hide' => false,
])

@auth
    @php
        $role = Auth::user()->role;
        $isGuruOrSiswa = in_array($role, ['guru', 'siswa'], true);
        $isFormAbsensi = request()->routeIs('guru.absensi.*');
        $shouldShow = $isGuruOrSiswa && ! $hide && ! $isFormAbsensi;
        $navLinks = $shouldShow ? \App\Support\Ui::bottomNavLinks($role) : [];
    @endphp

    @if($shouldShow && !empty($navLinks))
        <nav aria-label="Navigasi bawah mobile"
             class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t-2 border-slate-300 dark:border-slate-700 shadow-lg pb-[env(safe-area-inset-bottom,0px)]">
            <div class="flex items-stretch justify-around max-w-lg mx-auto min-h-[56px]">
                @foreach($navLinks as $item)
                    @php
                        $patterns = explode('|', $item['pattern']);
                        $isActive = false;
                        foreach ($patterns as $pattern) {
                            if (request()->routeIs($pattern)) {
                                $isActive = true;
                                break;
                            }
                        }
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       @if($isActive) aria-current="page" @endif
                       class="flex-1 flex flex-col items-center justify-center py-2 px-1 text-center transition-colors min-h-[44px] focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 {{ $isActive ? 'text-emerald-800 dark:text-emerald-300 bg-emerald-50/80 dark:bg-emerald-500/10 border-t-2 border-emerald-600 dark:border-emerald-400 -mt-[2px] font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 border-t-2 border-transparent font-medium' }}">
                        <x-ui.icon :name="$item['icon']" size="md" class="{{ $isActive ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-400' }}" />
                        <span class="text-[11px] leading-tight mt-1 truncate max-w-full">
                            {{ $item['label'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </nav>
    @endif
@endauth
