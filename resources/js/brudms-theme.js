/**
 * BruDMS light / dark theme (customer preferences + localStorage).
 */
const STORAGE_KEY = 'brudms-appearance';

function normalizeTheme(theme) {
    return theme === 'light' ? 'light' : 'dark';
}

function preferServerTheme() {
    return document.querySelector('meta[name="brudms-theme-prefer-server"]')?.getAttribute('content') === '1';
}

function serverDefaultTheme() {
    const meta = document.querySelector('meta[name="brudms-theme-default"]');
    const value = meta?.getAttribute('content') || 'light';

    return value === 'light' ? 'light' : 'dark';
}

export function applyBrudmsTheme(theme) {
    if (typeof window.__brudmsApplyTheme === 'function') {
        return window.__brudmsApplyTheme(normalizeTheme(theme));
    }

    const value = normalizeTheme(theme);
    const root = document.documentElement;

    root.setAttribute('data-theme', value);

    if (value === 'dark') {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }

    try {
        localStorage.setItem(STORAGE_KEY, value);
    } catch (e) {
        /* private mode */
    }

    if (document.body) {
        const bg = value === 'light' ? '#e8f6fd' : '#1a1d2b';
        const fg = value === 'light' ? '#1a3d52' : '#f1f5f9';
        document.body.style.setProperty('background-color', bg, 'important');
        document.body.style.setProperty('color', fg, 'important');
    }

    window.dispatchEvent(
        new CustomEvent('brudms-theme-change', { detail: { theme: value } })
    );

    return value;
}

export function getStoredBrudmsTheme() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored === 'light' || stored === 'dark') {
            return stored;
        }
    } catch (e) {
        /* ignore */
    }

    return null;
}

export function initBrudmsTheme(serverDefault) {
    const fallback = serverDefault === 'light' ? 'light' : 'dark';

    if (preferServerTheme()) {
        return applyBrudmsTheme(serverDefaultTheme());
    }

    const stored = getStoredBrudmsTheme();

    return applyBrudmsTheme(stored || fallback);
}

window.BrudmsTheme = {
    storageKey: STORAGE_KEY,
    apply: applyBrudmsTheme,
    init: initBrudmsTheme,
    getStored: getStoredBrudmsTheme,
};

function bootTheme() {
    initBrudmsTheme(serverDefaultTheme());
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootTheme);
} else {
    bootTheme();
}

document.addEventListener('livewire:navigated', bootTheme);
window.addEventListener('brudms-theme-change', () => {});
