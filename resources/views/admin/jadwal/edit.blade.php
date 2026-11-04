<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Edit Jadwal') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-ui.page-header title="Edit Jadwal" :description="'Perbarui jadwal pelajaran ' . $jadwal->mapel->nama . ' - ' . \App\Support\Ui::labelKelas($jadwal->kelas)" />

            <x-ui.card>
                <form action="{{ route('admin.jadwal.update', $jadwal) }}" method="POST" novalidate class="space-y-5"
                      x-data="kelolaFilterKelas({
                          daftarKelas: {{ json_encode(\App\Support\Ui::sortKelas($kelas)->map(fn($k) => [
                              'id' => (string) $k->id,
                              'nama' => $k->nama,
                              'tahun_ajaran' => $k->tahun_ajaran,
                              'semester' => $k->semester,
                              'label' => \App\Support\Ui::labelKelas($k),
                          ])->values()->all()) }},
                          tahunAjaranAwal: '{{ old('tahun_ajaran', $jadwal->tahun_ajaran) }}',
                          semesterAwal: '{{ old('semester', $jadwal->semester) }}',
                          kelasIdAwal: '{{ old('kelas_id', (string) $jadwal->kelas_id) }}'
                      })">
                    @csrf
                    @method('PUT')
                    @if(request('kembali'))
                        <input type="hidden" name="kembali" value="{{ request('kembali') }}">
                    @endif
                    
                    <div>
                        <x-input-label for="kelas_id" value="Kelas" />
                        <x-ui.select name="kelas_id" id="kelas_id" required wrapperClass="mt-1" x-model="kelasId" @change="onKelasChange($event.target.value)">
                            <option value="">-- Pilih Kelas --</option>
                            <option value="" disabled x-show="!adaKelas" x-cloak>Belum ada kelas di periode ini</option>
                            @foreach(\App\Support\Ui::sortKelas($kelas) as $k)
                                <option value="{{ $k->id }}"
                                        data-tahun-ajaran="{{ $k->tahun_ajaran }}"
                                        data-semester="{{ $k->semester }}"
                                        title="{{ \App\Support\Ui::labelKelas($k) }}"
                                        x-show="tahunAjaran === '{{ $k->tahun_ajaran }}' && semester === '{{ $k->semester }}'"
                                        {{ old('kelas_id', $jadwal->kelas_id) == $k->id ? 'selected' : '' }}>
                                    {{ \App\Support\Ui::labelKelas($k) }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        {{-- Peringatan saat kelas dikosongkan karena periode diubah --}}
                        <p x-show="pesanPilihUlang" x-cloak class="text-xs text-amber-700 dark:text-amber-400 mt-1 font-semibold flex items-center gap-1">
                            <x-ui.icon name="info" size="sm" class="inline shrink-0" />
                            <span>Pilih ulang kelas sesuai periode</span>
                        </p>

                        {{-- Opsi / Tautan saat tidak ada kelas di periode ini --}}
                        <div x-show="!adaKelas" x-cloak class="mt-2 p-3 bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-700 rounded-xl text-xs text-amber-800 dark:text-amber-300 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 shadow-xs">
                            <div class="flex items-center gap-1.5">
                                <x-ui.icon name="info" size="sm" class="shrink-0 text-amber-700 dark:text-amber-400" />
                                <span>Belum ada kelas di periode ini.</span>
                            </div>
                            <a href="{{ route('admin.pergantian-periode.index') }}#salin" class="font-bold underline hover:text-amber-950 dark:hover:text-amber-100 shrink-0 inline-flex items-center gap-1">
                                <span>Salin kelas dari periode sebelumnya &rarr;</span>
                            </a>
                        </div>

                        <x-input-error :messages="$errors->get('kelas_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="mapel_id" value="Mata Pelajaran" />
                        <x-ui.select name="mapel_id" id="mapel_id" required wrapperClass="mt-1">
                            @foreach($mapel as $m)
                                <option value="{{ $m->id }}" {{ old('mapel_id', $jadwal->mapel_id) == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('mapel_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="guru_id" value="Guru Pengajar" />
                        <x-ui.select name="guru_id" id="guru_id" required wrapperClass="mt-1">
                            @foreach($guru as $g)
                                <option value="{{ $g->id }}" {{ old('guru_id', $jadwal->guru_id) == $g->id ? 'selected' : '' }}>{{ $g->user->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('guru_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="hari" value="Hari" />
                        <x-ui.select name="hari" id="hari" required wrapperClass="mt-1">
                            @foreach(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $h)
                                <option value="{{ $h }}" {{ old('hari', $jadwal->hari) == $h ? 'selected' : '' }}>{{ ucfirst($h) }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('hari')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="jam_mulai" value="Jam Mulai" />
                            <x-text-input type="time" name="jam_mulai" id="jam_mulai" class="mt-1 block w-full" :value="old('jam_mulai', substr($jadwal->jam_mulai, 0, 5))" required />
                            <x-input-error :messages="$errors->get('jam_mulai')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="jam_selesai" value="Jam Selesai" />
                            <x-text-input type="time" name="jam_selesai" id="jam_selesai" class="mt-1 block w-full" :value="old('jam_selesai', substr($jadwal->jam_selesai, 0, 5))" required />
                            <x-input-error :messages="$errors->get('jam_selesai')" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tahun_ajaran" value="Tahun Ajaran" />
                            <x-ui.select name="tahun_ajaran" id="tahun_ajaran" required wrapperClass="mt-1" x-model="tahunAjaran" @change="onPeriodeChange()">
                                @foreach($daftarTahunAjaran as $ta)
                                    <option value="{{ $ta }}" {{ old('tahun_ajaran', $jadwal->tahun_ajaran) === $ta ? 'selected' : '' }}>
                                        {{ $ta }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                            <x-input-error :messages="$errors->get('tahun_ajaran')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="semester" value="Semester" />
                            <x-ui.select name="semester" id="semester" required wrapperClass="mt-1" x-model="semester" @change="onPeriodeChange()">
                                <option value="Ganjil" {{ old('semester', $jadwal->semester) == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="Genap" {{ old('semester', $jadwal->semester) == 'Genap' ? 'selected' : '' }}>Genap</option>
                            </x-ui.select>
                            <x-input-error :messages="$errors->get('semester')" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t-2 border-slate-200 dark:border-slate-700">
                        <a href="{{ \App\Support\Kembali::tujuan(request('kembali'), route('admin.jadwal.index'), ['/admin/jadwal']) }}" class="{{ \App\Support\Ui::tombol('secondary') }} w-full sm:w-auto">
                            Batal
                        </a>
                        <button type="submit" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
