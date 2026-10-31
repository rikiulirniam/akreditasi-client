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

        let activeDeleteRow = null;

        // Auto attach listener for any button with data-confirm-delete
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-confirm-delete]');
            if (!btn) return;

            e.preventDefault();
            activeDeleteRow = btn.closest('tr');
            const action = btn.getAttribute('data-action')
                || btn.getAttribute('href')
                || (btn instanceof HTMLFormElement ? btn.action : btn.form?.action)
                || '#';
            const title = btn.getAttribute('data-title') || 'Hapus Data?';
            const message = btn.getAttribute('data-message') || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.';

            openModal({ action, title, message });
        });

        if (form) {
            form.addEventListener('submit', function (e) {
                if (!activeDeleteRow) return; // proceed with default form submit if not from a table row
                e.preventDefault();

                const targetRow = activeDeleteRow;
                const actionUrl = form.action;
                const csrfToken = form.querySelector('input[name="_token"]')?.value 
                    || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                    || '';

                closeModal();

                // 1. Initial fade-out state
                targetRow.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                targetRow.style.opacity = '0.35';
                targetRow.style.pointerEvents = 'none';

                fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                })
                .then(async response => {
                    let data = {};
                    try { data = await response.json(); } catch (err) {}

                    if (!response.ok || (data.success !== undefined && data.success === false)) {
                        throw new Error(data.message || 'Gagal menghapus data.');
                    }

                    // 2. Animate row height collapsing so rows below it smoothly slide upwards!
                    targetRow.style.opacity = '0';
                    targetRow.style.transform = 'translateY(-6px)';

                    const cells = targetRow.querySelectorAll('td');
                    cells.forEach(td => {
                        td.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                        td.style.paddingTop = '0px';
                        td.style.paddingBottom = '0px';
                        td.style.borderTopWidth = '0px';
                        td.style.borderBottomWidth = '0px';
                        td.style.height = '0px';
                        td.style.lineHeight = '0px';
                        td.style.fontSize = '0px';
                        td.style.boxShadow = 'none';
                        Array.from(td.children).forEach(child => {
                            child.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                            child.style.maxHeight = '0px';
                            child.style.opacity = '0';
                            child.style.overflow = 'hidden';
                            child.style.margin = '0px';
                            child.style.padding = '0px';
                        });
                    });

                    setTimeout(() => {
                        const table = targetRow.closest('table');
                        targetRow.remove();

                        // Re-number remaining rows in the table if there is a number column
                        if (table) {
                            const remainingRows = table.querySelectorAll('tbody tr:not(.empty-state-row)');
                            remainingRows.forEach((r, idx) => {
                                const firstCell = r.querySelector('td:first-child');
                                if (firstCell && /^\s*\d+\.?\s*$/.test(firstCell.textContent)) {
                                    firstCell.textContent = (idx + 1);
                                }
                            });
                        }

                        if (window.showToast) {
                            window.showToast({
                                type: 'delete',
                                title: '',
                                message: data.message || 'Data berhasil dihapus'
                            });
                        }
                        activeDeleteRow = null;
                    }, 360);
                })
                .catch(error => {
                    targetRow.style.opacity = '1';
                    targetRow.style.transform = 'none';
                    targetRow.style.pointerEvents = 'auto';

                    if (window.showToast) {
                        window.showToast({
                            type: 'error',
                            title: 'Gagal Menghapus',
                            message: error.message || 'Gagal menghapus data.'
                        });
                    } else {
                        alert(error.message || 'Gagal menghapus data.');
                    }
                    activeDeleteRow = null;
                });
            });
        }
    })();
</script>
