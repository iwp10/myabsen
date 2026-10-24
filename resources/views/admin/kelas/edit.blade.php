<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Edit Kelas') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-ui.page-header title="Edit Kelas" :description="'Perbarui data rombongan belajar ' . $kelas->nama" />

            <x-ui.card>
                <form action="{{ route('admin.kelas.update', $kelas) }}" method="POST" novalidate class="space-y-5">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <x-input-label for="nama" value="Nama Kelas" />
                        <x-text-input type="text" name="nama" id="nama" class="mt-1 block w-full" :value="old('nama', $kelas->nama)" required autofocus />
                        <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="tingkat" value="Tingkat (Contoh: 10, 11, 12)" />
                        <x-text-input type="number" name="tingkat" id="tingkat" class="mt-1 block w-full" :value="old('tingkat', $kelas->tingkat)" required min="1" max="13" />
                        <x-input-error :messages="$errors->get('tingkat')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="jurusan_id" value="Jurusan" />
                        <x-ui.select name="jurusan_id" id="jurusan_id" required wrapperClass="mt-1">
                            <option value="">-- Pilih Jurusan --</option>
                            @foreach($jurusans as $jurusan)
                                <option value="{{ $jurusan->id }}" {{ old('jurusan_id', $kelas->jurusan_id) == $jurusan->id ? 'selected' : '' }}>
                                    {{ $jurusan->nama }}
                                </option>
                            @endforeach
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('jurusan_id')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tahun_ajaran" value="Tahun Ajaran" />
                            <x-ui.select name="tahun_ajaran" id="tahun_ajaran" required wrapperClass="mt-1">
                                @foreach($daftarTahunAjaran as $ta)
                                    <option value="{{ $ta }}" {{ old('tahun_ajaran', $kelas->tahun_ajaran) === $ta ? 'selected' : '' }}>
                                        {{ $ta }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                            <x-input-error :messages="$errors->get('tahun_ajaran')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="semester" value="Semester" />
                            <x-ui.select name="semester" id="semester" required wrapperClass="mt-1">
                                <option value="Ganjil" {{ old('semester', $kelas->semester) == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="Genap" {{ old('semester', $kelas->semester) == 'Genap' ? 'selected' : '' }}>Genap</option>
                            </x-ui.select>
                            <x-input-error :messages="$errors->get('semester')" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t-2 border-slate-200 dark:border-slate-700">
                        <a href="{{ route('admin.kelas.index') }}" class="{{ \App\Support\Ui::tombol('secondary') }} w-full sm:w-auto">
                            Batal
                        </a>
                        <button type="submit" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto">
                            Perbarui
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
