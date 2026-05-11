// FreelaSkill — Admin Backoffice Shared JS
// Même clé localStorage que le frontoffice pour synchroniser le thème
const THEME_KEY = 'freelaSkillTheme';

function applyTheme(theme) {
    const body = document.body;
    const icon  = document.getElementById('theme-icon');
    const label = document.getElementById('theme-label');

    if (theme === 'light') {
        document.documentElement.style.colorScheme = 'light';
        body.classList.add('light-mode');
        body.classList.remove('dark-mode');
        if (icon)  icon.className  = 'fa-solid fa-sun';
        if (label) label.textContent = 'Mode sombre';
    } else {
        document.documentElement.style.colorScheme = 'dark';
        body.classList.remove('light-mode');
        body.classList.add('dark-mode');
        if (icon)  icon.className  = 'fa-solid fa-moon';
        if (label) label.textContent = 'Mode clair';
    }
    localStorage.setItem(THEME_KEY, theme);
}

function toggleTheme() {
    applyTheme(localStorage.getItem(THEME_KEY) === 'light' ? 'dark' : 'light');
}

// Appliquer le thème sauvegardé dès le chargement
document.addEventListener('DOMContentLoaded', () => {
    applyTheme(localStorage.getItem(THEME_KEY) || 'dark');
});
