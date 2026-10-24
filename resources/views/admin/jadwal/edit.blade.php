<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Edit Jadwal') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-ui.page-header title="Edit Jadwal" :description="'Perbarui jadwal pelajaran ' . $jadwal->mapel->nama . ' - ' . $jadwal->kelas->nama" />

            <x-ui.card>
                <form action="{{ route('admin.jadwal.update', $jadwal) }}" method="POST" novalidate class="space-y-5">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <x-input-label for="kelas_id" value="Kelas" />
                        <x-ui.select name="kelas_id" id="kelas_id" required wrapperClass="mt-1">
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id', $jadwal->kelas_id) == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </x-ui.select>
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
                            <x-ui.select name="tahun_ajaran" id="tahun_ajaran" required wrapperClass="mt-1">
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
                            <x-ui.select name="semester" id="semester" required wrapperClass="mt-1">
                                <option value="Ganjil" {{ old('semester', $jadwal->semester) == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="Genap" {{ old('semester', $jadwal->semester) == 'Genap' ? 'selected' : '' }}>Genap</option>
                            </x-ui.select>
                            <x-input-error :messages="$errors->get('semester')" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t-2 border-slate-200 dark:border-slate-700">
                        <a href="{{ route('admin.jadwal.index') }}" class="{{ \App\Support\Ui::tombol('secondary') }} w-full sm:w-auto">
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
