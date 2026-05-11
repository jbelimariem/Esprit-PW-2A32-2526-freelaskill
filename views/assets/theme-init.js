(() => {
    try {
        const value = localStorage.getItem('freelaskill-theme');
        const theme = value === 'light' || value === 'dark' ? value : 'dark';
        document.documentElement.dataset.theme = theme;
        document.documentElement.style.colorScheme = theme;
    } catch (error) {
        document.documentElement.dataset.theme = 'dark';
        document.documentElement.style.colorScheme = 'dark';
    }
})();
