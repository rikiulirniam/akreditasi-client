@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => 'yyyy',
    'min' => 1900,
    'max' => 2100,
    'step' => 1,
    'class' => '',
    'containerClass' => '',
    'icon' => true,
    'picker' => true,
])

@php
    $inputId = $id ?? ($name ? $name . '_year_' . \Illuminate\Support\Str::random(6) : 'year_' . \Illuminate\Support\Str::random(8));
    $minYear = (int) $min;
    $maxYear = (int) $max;
    $currentYear = (int) date('Y');
@endphp

<div 
    class="relative w-full {{ $containerClass }}"
    @if($picker)
        data-year-picker-container
        data-min="{{ $minYear }}"
        data-max="{{ $maxYear }}"
    @endif
>
    <input
        type="text"
        inputmode="numeric"
        pattern="[0-9]{4}"
        maxlength="4"
        autocomplete="off"
        @if($name) name="{{ $name }}" @endif
        id="{{ $inputId }}"
        @if($value !== null && $value !== '') value="{{ $value }}" @endif
        placeholder="{{ $placeholder }}"
        min="{{ $minYear }}"
        max="{{ $maxYear }}"
        @if($picker) data-year-input @endif
        {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 ' . ($icon ? 'pr-10 ' : '') . 'rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-400 cursor-pointer transition ' . $class]) }}
    >

    @if($icon)
        @if($picker)
            <button
                type="button"
                data-year-picker-toggle
                tabindex="-1"
                title="Pilih tahun"
                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-700 hover:text-primary transition-colors cursor-pointer focus:outline-none"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </button>
        @else
            <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-700">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
        @endif
    @endif

    @if($picker)
        <div
            data-year-picker-popover
            class="hidden absolute z-50 left-0 mt-1.5 w-64 bg-white rounded-2xl shadow-xl border border-gray-200 p-3 transition-all duration-150 origin-top"
        >
            <!-- Header Decade Navigation -->
            <div class="flex items-center justify-between pb-2 mb-2 border-b border-gray-100">
                <button
                    type="button"
                    data-year-prev-decade
                    class="p-1 rounded-lg text-gray-500 hover:text-primary hover:bg-gray-100 transition cursor-pointer"
                    title="10 tahun sebelumnya"
                >
                    <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <span data-year-range-label class="text-xs font-bold text-gray-900 tracking-wide font-sans"></span>
                <button
                    type="button"
                    data-year-next-decade
                    class="p-1 rounded-lg text-gray-500 hover:text-primary hover:bg-gray-100 transition cursor-pointer"
                    title="10 tahun berikutnya"
                >
                    <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Years Grid -->
            <div data-year-grid class="grid grid-cols-3 gap-1.5"></div>

            <!-- Footer: Quick Actions -->
            <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px]">
                <button
                    type="button"
                    data-year-current-btn
                    class="text-primary hover:text-additional font-bold transition cursor-pointer hover:underline"
                >
                    Tahun Ini ({{ $currentYear }})
                </button>
                <button
                    type="button"
                    data-year-clear-btn
                    class="text-gray-400 hover:text-gray-600 font-medium transition cursor-pointer hover:underline"
                >
                    Hapus
                </button>
            </div>
        </div>
    @endif
</div>

@if($picker)
    @once
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function initYearPickers() {
                    document.querySelectorAll('[data-year-picker-container]').forEach(container => {
                        if (container.dataset.initialized === 'true') return;
                        container.dataset.initialized = 'true';

                        const input = container.querySelector('[data-year-input]');
                        const toggleBtn = container.querySelector('[data-year-picker-toggle]');
                        const popover = container.querySelector('[data-year-picker-popover]');
                        const rangeLabel = container.querySelector('[data-year-range-label]');
                        const grid = container.querySelector('[data-year-grid]');
                        const prevBtn = container.querySelector('[data-year-prev-decade]');
                        const nextBtn = container.querySelector('[data-year-next-decade]');
                        const currentBtn = container.querySelector('[data-year-current-btn]');
                        const clearBtn = container.querySelector('[data-year-clear-btn]');

                        if (!input || !popover || !grid) return;

                        const minYear = parseInt(container.getAttribute('data-min'), 10) || 1900;
                        const maxYear = parseInt(container.getAttribute('data-max'), 10) || 2100;
                        const currentSystemYear = new Date().getFullYear();

                        let displayedDecadeStart = Math.floor((parseInt(input.value, 10) || currentSystemYear) / 10) * 10;

                        function renderGrid() {
                            const selectedYear = parseInt(input.value, 10);
                            const startYear = displayedDecadeStart;
                            const endYear = startYear + 11; // 12 items (3 cols x 4 rows)

                            if (rangeLabel) {
                                rangeLabel.textContent = `${startYear} - ${endYear}`;
                            }

                            grid.innerHTML = '';

                            for (let y = startYear; y <= endYear; y++) {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.textContent = y;

                                const isSelected = selectedYear === y;
                                const isCurrent = currentSystemYear === y;
                                const isDisabled = y < minYear || y > maxYear;

                                let btnClasses = 'py-1.5 text-xs font-semibold rounded-lg transition-all text-center ';

                                if (isDisabled) {
                                    btnClasses += 'text-gray-300 cursor-not-allowed';
                                    btn.disabled = true;
                                } else if (isSelected) {
                                    btnClasses += 'bg-primary text-white font-bold shadow-xs';
                                } else if (isCurrent) {
                                    btnClasses += 'bg-gray-100 text-primary border border-primary/30 hover:bg-primary hover:text-white cursor-pointer';
                                } else {
                                    btnClasses += 'text-gray-700 hover:bg-gray-100 hover:text-primary cursor-pointer';
                                }

                                btn.className = btnClasses;

                                if (!isDisabled) {
                                    btn.addEventListener('click', function (e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        input.value = y;
                                        input.dispatchEvent(new Event('input', { bubbles: true }));
                                        input.dispatchEvent(new Event('change', { bubbles: true }));
                                        closePopover();
                                    });
                                }

                                grid.appendChild(btn);
                            }
                        }

                        function openPopover() {
                            const currentVal = parseInt(input.value, 10);
                            displayedDecadeStart = Math.floor((currentVal || currentSystemYear) / 10) * 10;
                            renderGrid();
                            popover.classList.remove('hidden');
                        }

                        function closePopover() {
                            popover.classList.add('hidden');
                        }

                        function togglePopover(e) {
                            if (e) {
                                e.preventDefault();
                                e.stopPropagation();
                            }
                            if (popover.classList.contains('hidden')) {
                                document.querySelectorAll('[data-year-picker-popover]').forEach(p => p.classList.add('hidden'));
                                openPopover();
                            } else {
                                closePopover();
                            }
                        }

                        // Strictly enforce 4 numeric digits only
                        input.addEventListener('keydown', function (e) {
                            if ([
                                'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
                                'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                                'Home', 'End'
                            ].includes(e.key) || e.ctrlKey || e.metaKey) {
                                return;
                            }

                            if (!/^[0-9]$/.test(e.key)) {
                                e.preventDefault();
                                return;
                            }

                            const selectedLength = (this.selectionEnd || 0) - (this.selectionStart || 0);
                            if (this.value.length >= 4 && selectedLength === 0) {
                                e.preventDefault();
                            }
                        });

                        input.addEventListener('input', function () {
                            let digits = this.value.replace(/\D/g, '').slice(0, 4);
                            if (this.value !== digits) {
                                this.value = digits;
                            }
                            if (digits.length === 4) {
                                const y = parseInt(digits, 10);
                                if (!isNaN(y)) {
                                    displayedDecadeStart = Math.floor(y / 10) * 10;
                                    renderGrid();
                                }
                            }
                        });

                        input.addEventListener('paste', function (e) {
                            e.preventDefault();
                            const pasteData = (e.clipboardData || window.clipboardData).getData('text') || '';
                            const digits = pasteData.replace(/\D/g, '').slice(0, 4);
                            if (digits) {
                                this.value = digits;
                                this.dispatchEvent(new Event('input', { bubbles: true }));
                                this.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });

                        // Show year options when input is clicked
                        input.addEventListener('click', function (e) {
                            e.stopPropagation();
                            if (popover.classList.contains('hidden')) {
                                document.querySelectorAll('[data-year-picker-popover]').forEach(p => p.classList.add('hidden'));
                                openPopover();
                            }
                        });

                        if (toggleBtn) {
                            toggleBtn.addEventListener('click', togglePopover);
                        }

                        if (prevBtn) {
                            prevBtn.addEventListener('click', function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                displayedDecadeStart -= 10;
                                renderGrid();
                            });
                        }

                        if (nextBtn) {
                            nextBtn.addEventListener('click', function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                displayedDecadeStart += 10;
                                renderGrid();
                            });
                        }

                        if (currentBtn) {
                            currentBtn.addEventListener('click', function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                input.value = currentSystemYear;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                                closePopover();
                            });
                        }

                        if (clearBtn) {
                            clearBtn.addEventListener('click', function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                input.value = '';
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                                closePopover();
                            });
                        }

                        // Close popover when clicking outside
                        document.addEventListener('click', function (e) {
                            if (!container.contains(e.target)) {
                                closePopover();
                            }
                        });

                        // Close on Escape key
                        document.addEventListener('keydown', function (e) {
                            if (e.key === 'Escape' && !popover.classList.contains('hidden')) {
                                closePopover();
                            }
                        });
                    });
                }

                initYearPickers();

                // Re-init if dynamic content added
                window.initYearPickers = initYearPickers;
            });
        </script>
    @endonce
@endif
