const THEME_STORAGE_KEY = 'freelaskill-theme';

function getStoredTheme() {
    try {
        const value = localStorage.getItem(THEME_STORAGE_KEY);
        return value === 'light' || value === 'dark' ? value : 'dark';
    } catch (error) {
        return 'dark';
    }
}

function applyTheme(theme) {
    const resolvedTheme = theme === 'light' ? 'light' : 'dark';
    const nextTheme = resolvedTheme === 'dark' ? 'light' : 'dark';

    document.documentElement.dataset.theme = resolvedTheme;
    document.documentElement.style.colorScheme = resolvedTheme;

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const icon = button.querySelector('[data-theme-icon]');
        const label = button.querySelector('[data-theme-label]');

        button.setAttribute('aria-pressed', resolvedTheme === 'light' ? 'true' : 'false');
        button.setAttribute('aria-label', nextTheme === 'light' ? 'Passer en mode jour' : 'Passer en mode nuit');
        button.setAttribute('title', nextTheme === 'light' ? 'Passer en mode jour' : 'Passer en mode nuit');

        if (icon) {
            icon.className = nextTheme === 'light' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        }

        if (label) {
            label.textContent = nextTheme === 'light' ? 'Jour' : 'Nuit';
        }
    });
}

function toggleTheme() {
    const nextTheme = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';

    try {
        localStorage.setItem(THEME_STORAGE_KEY, nextTheme);
    } catch (error) {
        // Ignore localStorage failures and keep the UI working.
    }

    applyTheme(nextTheme);
}

document.addEventListener('DOMContentLoaded', () => {
    applyTheme(getStoredTheme());

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', toggleTheme);
    });
});
