<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Tambah Siswa') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-ui.page-header title="Tambah Siswa" description="Masukkan data siswa baru ke dalam sistem" />

            <x-ui.card>
                <form action="{{ route('admin.siswa.store') }}" method="POST" novalidate class="space-y-5">
                    @csrf
                    
                    <div>
                        <x-input-label for="name" value="Nama Lengkap" />
                        <x-text-input type="text" name="name" id="name" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="nis" value="NIS (Nomor Induk Siswa)" />
                        <x-text-input type="text" name="nis" id="nis" class="mt-1 block w-full" :value="old('nis')" required />
                        <x-input-error :messages="$errors->get('nis')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="kelas_id" value="Kelas" />
                        <x-ui.select name="kelas_id" id="kelas_id" required wrapperClass="mt-1">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->tingkat }} {{ $k->nama }} - {{ $k->jurusan->kode }}
                                </option>
                            @endforeach
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('kelas_id')" class="mt-1" />
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t-2 border-slate-200 dark:border-slate-700">
                        <a href="{{ route('admin.siswa.index') }}" class="{{ \App\Support\Ui::tombol('secondary') }} w-full sm:w-auto">
                            Batal
                        </a>
                        <button type="submit" class="{{ \App\Support\Ui::tombol('primary') }} w-full sm:w-auto">
                            Simpan
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
