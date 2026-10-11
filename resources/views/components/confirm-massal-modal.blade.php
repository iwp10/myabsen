<div x-data="{
        action: '',
        title: '',
        type: '',
        items: [],
        confirmText: 'Lanjutkan',
        confirmColor: 'danger',
        method: 'POST',
        submitting: false,

        get countTotal() {
            return this.items.length;
        },
        get countNonaktif() {
            return this.items.filter(i => Boolean(i.hasHistory)).length;
        },
        get countPermanen() {
            return this.items.filter(i => !Boolean(i.hasHistory)).length;
        },
        get first10Items() {
            return this.items.slice(0, 10);
        },
        get remainingCount() {
            return Math.max(0, this.items.length - 10);
        }
    }"
    @open-confirm-massal-modal.window="
        action = $event.detail.action;
        title = $event.detail.title || 'Konfirmasi Aksi Massal';
        type = $event.detail.type || '';
        items = $event.detail.items || [];
        confirmText = $event.detail.confirmText || 'Lanjutkan';
        confirmColor = $event.detail.confirmColor || 'danger';
        method = $event.detail.method || 'POST';
        submitting = false;
        $dispatch('open-modal', 'global-confirm-massal-modal');
    ">

    <x-modal name="global-confirm-massal-modal" focusable maxWidth="lg">
        <form :action="action" method="POST" @submit="submitting = true" class="p-6 relative">
            @csrf
            <template x-for="item in items" :key="item.id">
                <input type="hidden" name="ids[]" :value="item.id">
            </template>

            <!-- Tombol Tutup (X) -->
            <button type="button" x-bind:disabled="submitting" x-on:click="$dispatch('close-modal', 'global-confirm-massal-modal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none disabled:opacity-50">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="flex items-start gap-4">
                <!-- Ikon Modal Sesuai Tipe -->
                <div class="flex-shrink-0 w-11 h-11 rounded-full flex items-center justify-center"
                     :class="{
                         'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400': confirmColor === 'danger',
                         'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400': confirmColor === 'warning',
                         'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400': confirmColor === 'primary'
                     }">
                    <template x-if="confirmColor === 'danger'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </template>
                    <template x-if="confirmColor === 'warning'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </template>
                    <template x-if="confirmColor === 'primary'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </template>
                </div>

                <div class="flex-1 min-w-0 pr-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100" x-text="title"></h2>

                    <!-- Konten Khusus Hapus Siswa -->
                    <template x-if="type === 'siswa-hapus'">
                        <div class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            <!-- Bagian Siswa Berriwayat (Nonaktif) -->
                            <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 flex items-start gap-2.5">
                                <svg class="size-5 shrink-0 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="countNonaktif + ' siswa'"></span>
                                    <span> akan </span>
                                    <span class="font-semibold text-amber-700 dark:text-amber-400">DINONAKTIFKAN</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Memiliki riwayat absensi, akun tetap tersimpan, dan dapat dipulihkan lewat menu Data Terhapus.</p>
                                </div>
                            </div>

                            <!-- Bagian Siswa Tanpa Riwayat (Permanen) -->
                            <div x-show="countPermanen > 0" class="p-3 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-300 dark:border-red-700 flex items-start gap-2.5">
                                <svg class="size-5 shrink-0 text-red-600 dark:text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <span class="font-bold text-red-900 dark:text-red-200" x-text="countPermanen + ' siswa'"></span>
                                    <span class="text-red-800 dark:text-red-300"> akan </span>
                                    <span class="font-bold text-red-700 dark:text-red-400">DIHAPUS PERMANEN</span>
                                    <p class="text-xs text-red-700 dark:text-red-300 mt-0.5 font-medium">Belum punya riwayat absensi. Data beserta akun akan dihapus selamanya dan tidak dapat dikembalikan!</p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Konten Khusus Reset Password Siswa -->
                    <template x-if="type === 'siswa-reset'">
                        <div class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                            <p>
                                Password <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="countTotal"></span> siswa akan direset ke password awal dan mereka wajib menggantinya saat login berikutnya.
                            </p>
                        </div>
                    </template>

                    <!-- Konten Khusus Pemulihan Arsip -->
                    <template x-if="type === 'arsip-pulihkan'">
                        <div class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                            <p>
                                Pulihkan <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="countTotal"></span> data yang dipilih ke daftar master data aktif?
                            </p>
                        </div>
                    </template>

                    <!-- Daftar Nama Siswa / Data (Maksimal 10 Nama Pertama) -->
                    <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-900/60 rounded-lg border border-gray-200 dark:border-gray-700 text-xs">
                        <p class="font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Rincian data yang dipilih:</p>
                        <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto">
                            <template x-for="item in first10Items" :key="item.id">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200" x-text="item.name"></span>
                            </template>
                            <span x-show="remainingCount > 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold text-gray-500 dark:text-gray-400" x-text="'dan ' + remainingCount + ' lainnya'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button type="button" x-bind:disabled="submitting" x-on:click="$dispatch('close-modal', 'global-confirm-massal-modal')"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                    Batal
                </button>

                <template x-if="confirmColor === 'danger'">
                    <button type="submit" x-bind:disabled="submitting || items.length === 0"
                        class="inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800 text-white font-semibold text-xs uppercase tracking-widest rounded-md shadow-sm transition ease-in-out duration-150 disabled:opacity-50">
                        <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="submitting ? 'Memproses...' : confirmText"></span>
                    </button>
                </template>

                <template x-if="confirmColor === 'warning'">
                    <button type="submit" x-bind:disabled="submitting || items.length === 0"
                        class="inline-flex items-center justify-center px-4 py-2 bg-amber-600 hover:bg-amber-700 focus:bg-amber-700 active:bg-amber-800 text-white font-semibold text-xs uppercase tracking-widest rounded-md shadow-sm transition ease-in-out duration-150 disabled:opacity-50">
                        <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="submitting ? 'Memproses...' : confirmText"></span>
                    </button>
                </template>

                <template x-if="confirmColor === 'primary'">
                    <button type="submit" x-bind:disabled="submitting || items.length === 0"
                        class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 text-white font-semibold text-xs uppercase tracking-widest rounded-md shadow-sm transition ease-in-out duration-150 disabled:opacity-50">
                        <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="submitting ? 'Memproses...' : confirmText"></span>
                    </button>
                </template>
            </div>
        </form>
    </x-modal>
</div>
