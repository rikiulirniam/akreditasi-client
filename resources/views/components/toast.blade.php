@props([
    'position' => 'top-right', // 'top-right', 'top-center', 'bottom-right'
])

@php
    $initialToasts = [];

    if (session()->has('toast')) {
        $toastData = session('toast');
        if (is_array($toastData)) {
            $initialToasts[] = [
                'id' => uniqid('toast_'),
                'type' => $toastData['type'] ?? 'info',
                'title' => $toastData['title'] ?? null,
                'message' => $toastData['message'] ?? '',
                'duration' => $toastData['duration'] ?? 4500,
            ];
        } else {
            $initialToasts[] = [
                'id' => uniqid('toast_'),
                'type' => 'info',
                'title' => null,
                'message' => (string) $toastData,
                'duration' => 4500,
            ];
        }
    }

    if (session()->has('success')) {
        $initialToasts[] = [
            'id' => uniqid('toast_'),
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => (string) session('success'),
            'duration' => 4500,
        ];
    }

    if (session()->has('error')) {
        $initialToasts[] = [
            'id' => uniqid('toast_'),
            'type' => 'error',
            'title' => 'Terjadi Kesalahan',
            'message' => (string) session('error'),
            'duration' => 5000,
        ];
    }

    if (session()->has('warning')) {
        $initialToasts[] = [
            'id' => uniqid('toast_'),
            'type' => 'warning',
            'title' => 'Peringatan',
            'message' => (string) session('warning'),
            'duration' => 5000,
        ];
    }

    if (session()->has('info')) {
        $initialToasts[] = [
            'id' => uniqid('toast_'),
            'type' => 'info',
            'title' => 'Informasi',
            'message' => (string) session('info'),
            'duration' => 4500,
        ];
    }

    if (session()->has('status')) {
        $initialToasts[] = [
            'id' => uniqid('toast_'),
            'type' => 'info',
            'title' => null,
            'message' => (string) session('status'),
            'duration' => 4500,
        ];
    }
@endphp

<!-- Toast Notification Container -->
<div 
    id="toast-container" 
    class="fixed top-5 right-5 z-[99999] flex flex-col gap-3 pointer-events-none w-full max-w-[420px] sm:max-w-[460px] px-4 sm:px-0 transition-all duration-200"
    aria-live="polite"
    aria-atomic="true"
>
    <!-- Template for Delete Notification (Exact Match with Foto 3) -->
    <template id="toast-delete-template">
        <div class="toast-item toast-delete-item pointer-events-auto relative w-full overflow-hidden rounded-2xl bg-[#FEE2E2] border-b-[5px] border-[#C53030] shadow-xl transition-all duration-300 transform translate-x-8 opacity-0 hover:shadow-2xl">
            <div class="flex items-center justify-between gap-4 px-5 py-3.5 sm:py-4">
                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                    <!-- Red Trash Can Outline Icon (Matching Foto 3) -->
                    <div class="shrink-0 text-[#C53030]">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18" />
                            <path d="M19 6v14c0 1.1-.9 2-2 2H7c-1.1 0-2-.9-2-2V6" />
                            <path d="M8 6V4c0-1.1.9-2 2-2h4c1.1 0 2 .9 2 2v2" />
                            <line x1="10" y1="11" x2="10" y2="17" />
                            <line x1="14" y1="11" x2="14" y2="17" />
                        </svg>
                    </div>

                    <!-- Notification Message (Matching Foto 3) -->
                    <p class="toast-message text-base sm:text-[17px] font-bold text-[#C53030] tracking-tight truncate leading-tight">
                        Data berhasil dihapus
                    </p>
                </div>

                <!-- Red Close Button (Matching Foto 3) -->
                <button type="button" class="toast-close-btn p-1 -mr-1 text-[#C53030] hover:text-[#991B1B] hover:bg-red-200/50 rounded-lg transition cursor-pointer shrink-0" aria-label="Tutup">
                    <svg class="w-5 h-5 stroke-[2.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </template>

    <!-- Template for Client-side Toasts -->
    <template id="toast-template">
        <div class="toast-item pointer-events-auto relative w-full overflow-hidden rounded-2xl border shadow-xl transition-all duration-300 transform translate-x-8 opacity-0 hover:shadow-2xl">
            <div class="flex items-start gap-3.5 p-4">
                <!-- Icon Badge -->
                <div class="toast-icon-wrapper w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-xs">
                    <!-- Icon SVG injected here -->
                </div>

                <!-- Text Content -->
                <div class="flex-1 min-w-0 pt-0.5">
                    <h5 class="toast-title text-sm font-bold tracking-tight leading-snug hidden"></h5>
                    <p class="toast-message text-xs leading-relaxed break-words"></p>
                </div>

                <!-- Close Button -->
                <button type="button" class="toast-close-btn -mr-1 -mt-1 p-1.5 rounded-lg transition cursor-pointer" aria-label="Tutup">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Progress Countdown Bar -->
            <div class="toast-progress-bar h-1 w-full bg-black/5 overflow-hidden">
                <div class="toast-progress-fill h-full w-full origin-left transition-all ease-linear"></div>
            </div>
        </div>
    </template>
</div>

<script>
    (function () {
        const container = document.getElementById('toast-container');
        const template = document.getElementById('toast-template');
        const deleteTemplate = document.getElementById('toast-delete-template');

        // Styles & Icons definition per toast type
        const typeConfigs = {
            // Tema Hijau Cerah (Berhasil Ditambahkan / Create)
            success: {
                title: 'Berhasil Ditambahkan',
                bgClass: 'bg-[#F0FDF4]',
                borderColor: 'border-[#BBF7D0]',
                badgeBg: 'bg-[#DCFCE7] text-[#16A34A]',
                titleColor: 'text-[#14532D]',
                messageColor: 'text-[#166534]',
                progressBg: 'bg-[#22C55E]',
                closeBtnClass: 'text-[#16A34A] hover:bg-[#DCFCE7]/70',
                svg: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
            },
            // Tema Kuning Cerah (Berhasil Diedit / Diperbarui)
            update: {
                title: 'Berhasil Diperbarui',
                bgClass: 'bg-[#FEFCE8]',
                borderColor: 'border-[#FEF08A]',
                badgeBg: 'bg-[#FEF9C3] text-[#CA8A04]',
                titleColor: 'text-[#78350F]',
                messageColor: 'text-[#854D0E]',
                progressBg: 'bg-[#EAB308]',
                closeBtnClass: 'text-[#CA8A04] hover:bg-[#FEF9C3]/70',
                svg: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>'
            },
            delete: {
                title: 'Data Berhasil Dihapus',
                bgClass: 'bg-[#FEE2E2]',
                borderColor: 'border-[#C53030]',
                badgeBg: 'bg-[#FEE2E2] text-[#C53030]',
                titleColor: 'text-[#991B1B]',
                messageColor: 'text-[#C53030]',
                progressBg: 'bg-[#C53030]',
                closeBtnClass: 'text-[#C53030] hover:bg-red-200/50',
                svg: '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 6h18m-2 0v14c0 1.1-.9 2-2 2H7c-1.1 0-2-.9-2-2V6m3 0V4c0-1.1.9-2 2-2h4c1.1 0 2 .9 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>'
            },
            error: {
                title: 'Gagal',
                bgClass: 'bg-[#FFF1F2]',
                borderColor: 'border-rose-200',
                badgeBg: 'bg-rose-100 text-rose-600',
                titleColor: 'text-rose-900',
                messageColor: 'text-rose-700',
                progressBg: 'bg-rose-500',
                closeBtnClass: 'text-rose-400 hover:text-rose-600 hover:bg-rose-100',
                svg: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>'
            },
            warning: {
                title: 'Peringatan',
                bgClass: 'bg-amber-50/90',
                borderColor: 'border-amber-200',
                badgeBg: 'bg-amber-100 text-amber-600',
                titleColor: 'text-amber-900',
                messageColor: 'text-amber-700',
                progressBg: 'bg-amber-500',
                closeBtnClass: 'text-amber-400 hover:text-amber-600 hover:bg-amber-100',
                svg: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>'
            },
            info: {
                title: 'Informasi',
                bgClass: 'bg-blue-50/90',
                borderColor: 'border-blue-200',
                badgeBg: 'bg-blue-100 text-[#01568D]',
                titleColor: 'text-[#012650]',
                messageColor: 'text-[#01568D]',
                progressBg: 'bg-[#01568D]',
                closeBtnClass: 'text-blue-400 hover:text-blue-600 hover:bg-blue-100',
                svg: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>'
            }
        };
        typeConfigs.edit = typeConfigs.update;

        function createToast({ message, type = 'info', title = null, duration = 4500 }) {
            if (!container) return;

            let resolvedType = type || 'info';
            const combinedText = (((title || '') + ' ' + (message || ''))).toLowerCase();

            // Detect delete action
            const isDelete = resolvedType === 'delete' || combinedText.includes('berhasil dihapus');
            if (isDelete && deleteTemplate) {
                const clone = deleteTemplate.content.cloneNode(true);
                const toastEl = clone.querySelector('.toast-item');
                const messageEl = clone.querySelector('.toast-message');
                const closeBtn = clone.querySelector('.toast-close-btn');

                if (messageEl && message) {
                    messageEl.textContent = message;
                }

                container.appendChild(toastEl);

                requestAnimationFrame(() => {
                    toastEl.classList.remove('translate-x-8', 'opacity-0');
                    toastEl.classList.add('translate-x-0', 'opacity-100');
                });

                let timer = null;
                const dismiss = function () {
                    clearTimeout(timer);
                    toastEl.classList.remove('translate-x-0', 'opacity-100');
                    toastEl.classList.add('translate-x-8', 'opacity-0');
                    setTimeout(() => {
                        toastEl.remove();
                    }, 300);
                };

                if (closeBtn) {
                    closeBtn.addEventListener('click', dismiss);
                }

                if (duration > 0) {
                    timer = setTimeout(dismiss, duration);
                    toastEl.addEventListener('mouseenter', () => clearTimeout(timer));
                    toastEl.addEventListener('mouseleave', () => { timer = setTimeout(dismiss, 2000); });
                }
                return;
            }

            // Distinguish add (green) vs update/edit (yellow)
            if (resolvedType === 'update' || resolvedType === 'edit') {
                resolvedType = 'update';
            } else if (resolvedType === 'success') {
                if (/(diperbarui|diupdate|diedit|perubahan)/i.test(combinedText)) {
                    resolvedType = 'update';
                } else {
                    resolvedType = 'success';
                }
            }

            if (!template) return;
            const config = typeConfigs[resolvedType] || typeConfigs.info;
            const clone = template.content.cloneNode(true);
            const toastEl = clone.querySelector('.toast-item');
            const iconWrapper = clone.querySelector('.toast-icon-wrapper');
            const titleEl = clone.querySelector('.toast-title');
            const messageEl = clone.querySelector('.toast-message');
            const closeBtn = clone.querySelector('.toast-close-btn');
            const progressFill = clone.querySelector('.toast-progress-fill');

            // Apply style classes
            if (config.bgClass) toastEl.classList.add(config.bgClass);
            if (config.borderColor) toastEl.classList.add(config.borderColor);
            if (config.badgeBg) iconWrapper.className += ' ' + config.badgeBg;
            if (config.svg) iconWrapper.innerHTML = config.svg;
            if (config.progressBg) progressFill.className += ' ' + config.progressBg;
            if (config.titleColor) titleEl.classList.add(config.titleColor);
            if (config.messageColor) messageEl.classList.add(config.messageColor);
            if (config.closeBtnClass) closeBtn.className += ' ' + config.closeBtnClass;

            // Content
            const displayTitle = title || (title === null ? config.title : '');
            if (displayTitle) {
                titleEl.textContent = displayTitle;
                titleEl.classList.remove('hidden');
            }
            messageEl.textContent = message || '';

            container.appendChild(toastEl);

            container.appendChild(toastEl);

            // Animate In
            requestAnimationFrame(() => {
                toastEl.classList.remove('translate-x-8', 'opacity-0');
                toastEl.classList.add('translate-x-0', 'opacity-100');
            });

            // Countdown timer & progress bar
            let remaining = duration;
            const step = 50;
            let timer = null;
            let isPaused = false;

            function dismiss() {
                clearInterval(timer);
                toastEl.classList.remove('translate-x-0', 'opacity-100');
                toastEl.classList.add('translate-x-8', 'opacity-0');
                setTimeout(() => {
                    toastEl.remove();
                }, 300);
            }

            closeBtn.addEventListener('click', dismiss);

            if (duration > 0) {
                progressFill.style.transitionDuration = `${step}ms`;

                timer = setInterval(() => {
                    if (!isPaused) {
                        remaining -= step;
                        const percentage = Math.max(0, (remaining / duration) * 100);
                        progressFill.style.width = `${percentage}%`;

                        if (remaining <= 0) {
                            dismiss();
                        }
                    }
                }, step);

                // Pause on hover
                toastEl.addEventListener('mouseenter', () => { isPaused = true; });
                toastEl.addEventListener('mouseleave', () => { isPaused = false; });
            } else {
                progressFill.parentElement.remove();
            }
        }

        // Global Helper: window.showToast('message', 'success') or window.showToast({ message, type, title, duration })
        window.showToast = function (messageOrOptions, type = 'info', title = null, duration = 4500) {
            if (typeof messageOrOptions === 'object' && messageOrOptions !== null) {
                createToast(messageOrOptions);
            } else {
                createToast({
                    message: messageOrOptions,
                    type: type,
                    title: title,
                    duration: duration
                });
            }
        };

        // CustomEvent Listener: window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }))
        window.addEventListener('toast', function (event) {
            if (event.detail) {
                createToast(event.detail);
            }
        });

        // Initialize server flashed toasts
        const initialToasts = @json($initialToasts);
        if (Array.isArray(initialToasts) && initialToasts.length > 0) {
            initialToasts.forEach((toast, index) => {
                setTimeout(() => {
                    createToast(toast);
                }, index * 200);
            });
        }
    })();
</script>
