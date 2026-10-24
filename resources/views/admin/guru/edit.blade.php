<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Edit Guru') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-ui.page-header title="Edit Guru" :description="'Perbarui data guru pengajar ' . $guru->user->name" />

            <x-ui.card>
                <form action="{{ route('admin.guru.update', $guru) }}" method="POST" novalidate class="space-y-5">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <x-input-label for="name" value="Nama Lengkap" />
                        <x-text-input type="text" name="name" id="name" class="mt-1 block w-full" :value="old('name', $guru->user->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="nip" value="NIP / Username" />
                        <x-text-input type="text" name="nip" id="nip" class="mt-1 block w-full" :value="old('nip', $guru->nip)" required />
                        <x-input-error :messages="$errors->get('nip')" class="mt-1" />
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t-2 border-slate-200 dark:border-slate-700">
                        <a href="{{ route('admin.guru.index') }}" class="{{ \App\Support\Ui::tombol('secondary') }} w-full sm:w-auto">
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
