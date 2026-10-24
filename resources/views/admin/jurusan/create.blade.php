<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Tambah Jurusan') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-ui.page-header title="Tambah Jurusan" description="Masukkan data keahlian jurusan baru ke dalam sistem" />

            <x-ui.card>
                <form action="{{ route('admin.jurusan.store') }}" method="POST" novalidate class="space-y-5">
                    @csrf
                    
                    <div>
                        <x-input-label for="nama" value="Nama Jurusan (Contoh: Rekayasa Perangkat Lunak)" />
                        <x-text-input type="text" name="nama" id="nama" class="mt-1 block w-full" :value="old('nama')" required autofocus />
                        <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="kode" value="Kode Jurusan (Contoh: RPL, TKJ, DKV)" />
                        <x-text-input type="text" name="kode" id="kode" class="mt-1 block w-full" :value="old('kode')" required />
                        <x-input-error :messages="$errors->get('kode')" class="mt-1" />
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t-2 border-slate-200 dark:border-slate-700">
                        <a href="{{ route('admin.jurusan.index') }}" class="{{ \App\Support\Ui::tombol('secondary') }} w-full sm:w-auto">
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
