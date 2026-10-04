<div x-data="{
        action: '',
        title: '',
        message: '',
        confirmText: '',
        method: 'POST'
    }"
    @open-confirm-modal.window="
        action = $event.detail.action;
        title = $event.detail.title;
        message = $event.detail.message;
        confirmText = $event.detail.confirmText || 'Hapus';
        method = $event.detail.method || 'POST';
        $dispatch('open-modal', 'global-confirm-modal');
    ">
    
    <x-modal name="global-confirm-modal" focusable maxWidth="md">
        <form :action="action" method="POST" class="p-6 relative">
            @csrf
            <input type="hidden" name="_method" :value="method">
            
            <!-- Tombol X untuk close modal -->
            <button type="button" x-on:click="$dispatch('close-modal', 'global-confirm-modal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100" x-text="title"></h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line" x-html="message"></p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'global-confirm-modal')">
                    Batal
                </x-secondary-button>

                <x-danger-button type="submit" x-text="confirmText"></x-danger-button>
            </div>
        </form>
    </x-modal>
</div>
