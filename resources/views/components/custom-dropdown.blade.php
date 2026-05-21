@props([
    'id',
    'name' => null,
    'value' => '',
    'options' => [],
    'ariaLabel' => null,
])

@php
    $options = is_array($options) ? $options : [];
    $value = (string) ($value ?? '');
    $selectedLabel = $options[$value] ?? (count($options) ? (string) reset($options) : '');
@endphp

<div
    class="custom-dropdown"
    data-custom-dropdown
    style="position: relative; flex-shrink: 0; -webkit-tap-highlight-color: transparent;"
    {{ $attributes->except(['id', 'name', 'value', 'options', 'ariaLabel']) }}
>
    <button
        type="button"
        class="custom-dropdown-trigger press-btn"
        aria-haspopup="listbox"
        aria-expanded="false"
        @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
        style="margin: 0; display: flex; align-items: center; gap: 6px; padding: 5px 24px 5px 9px; border-radius: 8px; border: 0.7px solid rgba(255,255,255,0.21); background: rgba(66, 106, 120, 0.22); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); color: rgba(255,255,255,0.9); font-family: Poppins, sans-serif; font-size: 10px; font-weight: 600; cursor: pointer; line-height: 1.2; min-height: 28px; max-width: 42vw; touch-action: manipulation; user-select: none; -webkit-user-select: none;"
    >
        <span class="custom-dropdown-label" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $selectedLabel }}</span>
        <span class="custom-dropdown-chevron" aria-hidden="true" style="position: absolute; right: 9px; top: 50%; transform: translateY(-50%); flex-shrink: 0; color: rgba(255,255,255,0.45); font-size: 7px; line-height: 1; transition: transform 0.15s ease; pointer-events: none;">▼</span>
    </button>

    <ul
        class="custom-dropdown-menu"
        role="listbox"
        hidden
        style="position: absolute; top: calc(100% + 4px); right: 0; z-index: 50; min-width: 100%; margin: 0; padding: 4px 5px 6px; list-style: none; border-radius: 9px; border: 0.7px solid rgba(255, 255, 255, 0.18); background: rgba(97, 107, 110, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); overflow: hidden;"
    >
        @foreach($options as $optValue => $optLabel)
            <li role="presentation">
                <button
                    type="button"
                    role="option"
                    class="custom-dropdown-option press-btn"
                    data-value="{{ $optValue }}"
                    aria-selected="{{ (string) $optValue === $value ? 'true' : 'false' }}"
                    style="display: block; width: 100%; text-align: left; margin: 0; padding: 6px 8px; border: none; border-radius: 6px; background: {{ (string) $optValue === $value ? 'rgba(4, 188, 255, 0.18)' : 'transparent' }}; color: rgba(255,255,255,0.88); font-family: Poppins, sans-serif; font-size: 10px; font-weight: 500; cursor: pointer; line-height: 1.2; white-space: nowrap; touch-action: manipulation;"
                >
                    {{ $optLabel }}
                </button>
            </li>
        @endforeach
    </ul>

    <input
        type="hidden"
        id="{{ $id }}"
        @if($name) name="{{ $name }}" @endif
        value="{{ $value }}"
        tabindex="-1"
        aria-hidden="true"
    />
</div>

@once
    @push('styles')
        <style>
            .custom-dropdown-trigger { position: relative; }
            .custom-dropdown-menu[hidden] { display: none !important; }
            .custom-dropdown-menu {
                overflow: hidden;
                overscroll-behavior: none;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }
            .custom-dropdown-menu::-webkit-scrollbar { display: none; width: 0; height: 0; }
            .custom-dropdown.is-open .custom-dropdown-chevron { transform: translateY(-50%) rotate(180deg); }
            .custom-dropdown-option + .custom-dropdown-option {
                margin-top: 3px;
            }
            .custom-dropdown-option + .custom-dropdown-option::before {
                content: '';
                position: absolute;
                top: -2px;
                left: 5px;
                right: 5px;
                border-top: 0.7px solid rgba(255, 255, 255, 0.18);
            }
            .custom-dropdown-option {
                position: relative;
            }
            .custom-dropdown-option:hover,
            .custom-dropdown-option:focus,
            .custom-dropdown-option:active {
                background: transparent !important;
                outline: none;
            }
            .custom-dropdown-option[aria-selected="true"] {
                background: rgba(4, 188, 255, 0.18) !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                if (window.__customDropdownInit) return;
                window.__customDropdownInit = true;

                function closeDropdown(root) {
                    if (!root) return;
                    var menu = root.querySelector('.custom-dropdown-menu');
                    var trigger = root.querySelector('.custom-dropdown-trigger');
                    root.classList.remove('is-open');
                    if (menu) menu.hidden = true;
                    if (trigger) trigger.setAttribute('aria-expanded', 'false');
                }

                function openDropdown(root) {
                    document.querySelectorAll('[data-custom-dropdown].is-open').forEach(function (other) {
                        if (other !== root) closeDropdown(other);
                    });
                    var menu = root.querySelector('.custom-dropdown-menu');
                    var trigger = root.querySelector('.custom-dropdown-trigger');
                    root.classList.add('is-open');
                    if (menu) menu.hidden = false;
                    if (trigger) trigger.setAttribute('aria-expanded', 'true');
                }

                function setValue(root, value, label) {
                    var input = root.querySelector('input[type="hidden"]');
                    var labelEl = root.querySelector('.custom-dropdown-label');
                    if (input) {
                        input.value = value;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if (labelEl && label) labelEl.textContent = label;
                    root.querySelectorAll('.custom-dropdown-option').forEach(function (btn) {
                        var selected = btn.getAttribute('data-value') === value;
                        btn.setAttribute('aria-selected', selected ? 'true' : 'false');
                        btn.style.background = selected ? 'rgba(4, 188, 255, 0.18)' : 'transparent';
                    });
                }

                document.addEventListener('click', function (e) {
                    var trigger = e.target.closest('.custom-dropdown-trigger');
                    var option = e.target.closest('.custom-dropdown-option');
                    var root = (trigger || option || e.target).closest('[data-custom-dropdown]');

                    if (option && root) {
                        e.preventDefault();
                        e.stopPropagation();
                        setValue(root, option.getAttribute('data-value'), option.textContent.trim());
                        closeDropdown(root);
                        return;
                    }

                    if (trigger && root) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (root.classList.contains('is-open')) {
                            closeDropdown(root);
                        } else {
                            openDropdown(root);
                        }
                        return;
                    }

                    document.querySelectorAll('[data-custom-dropdown].is-open').forEach(closeDropdown);
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        document.querySelectorAll('[data-custom-dropdown].is-open').forEach(closeDropdown);
                    }
                });

                document.addEventListener('wheel', function (e) {
                    if (e.target.closest('.custom-dropdown-menu')) {
                        e.preventDefault();
                    }
                }, { passive: false });
            })();
        </script>
    @endpush
@endonce
