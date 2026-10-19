@props([
    'id' => 'confirmDeleteModal',
    'title' => 'Hapus Akun Pengguna?',
    'message' => 'Apakah Anda yakin ingin menghapus akun pengguna ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.',
    'action' => '#',
])

<div 
    id="{{ $id }}" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[1px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}Title"
>
    <!-- Modal Card Box (Exact Match with Mockup) -->
    <div class="confirm-modal-card relative bg-white rounded-[18px] p-7 sm:p-8 max-w-[430px] w-full shadow-2xl text-center transform scale-95 transition-all duration-200 overflow-hidden">
        
        <!-- Red Warning Triangle Icon (Exact Match with Mockup) -->
        <div class="mx-auto mb-4 w-12 h-12 flex items-center justify-center">
            <svg class="w-12 h-12 text-[#C02626]" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
            </svg>
        </div>

        <!-- Title -->
        <h3 id="{{ $id }}Title" class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight text-center mb-2.5">
            {{ $title }}
        </h3>

        <!-- Description -->
        <p id="{{ $id }}Message" class="text-xs sm:text-sm text-gray-700 leading-relaxed max-w-xs mx-auto mb-6 font-normal">
            {{ $message }}
        </p>

        <!-- Form and Action Buttons -->
        <form id="{{ $id }}Form" method="POST" action="{{ $action }}" class="w-full">
            @csrf
            @method('DELETE')

            <div class="grid grid-cols-2 gap-4 w-full pt-1">
                <button 
                    type="button" 
                    class="confirm-modal-cancel w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-gray-900 font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-[#C02626] hover:bg-[#991B1B] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('{{ $id }}');
        if (!modal) return;

        const card = modal.querySelector('.confirm-modal-card');
        const titleEl = document.getElementById('{{ $id }}Title');
        const messageEl = document.getElementById('{{ $id }}Message');
        const form = document.getElementById('{{ $id }}Form');
        const cancelBtn = modal.querySelector('.confirm-modal-cancel');

        function openModal({ action, title, message } = {}) {
            if (action && form) form.action = action;
            if (title && titleEl) titleEl.textContent = title;
            if (message && messageEl) messageEl.textContent = message;

            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function closeModal() {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeModal);
        }

        // Close on backdrop click
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('pointer-events-none')) {
                closeModal();
            }
        });

        // Global helper for opening confirm delete modal
        window.openConfirmDeleteModal = openModal;
        window.closeConfirmDeleteModal = closeModal;

        // Auto attach listener for any button with data-confirm-delete
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-confirm-delete]');
            if (!btn) return;

            e.preventDefault();
            const action = btn.getAttribute('data-action') || btn.getAttribute('href') || (btn.form ? btn.form.action : '#');
            const title = btn.getAttribute('data-title') || 'Hapus Data?';
            const message = btn.getAttribute('data-message') || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.';

            openModal({ action, title, message });
        });
    })();
</script>
