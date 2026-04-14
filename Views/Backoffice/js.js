document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', () => {
        document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
        item.classList.add('active');
    });
});

document.querySelectorAll('.js-delete-link').forEach(link => {
    link.addEventListener('click', event => {
        const confirmed = confirm('Voulez-vous vraiment supprimer cette catégorie ? Cette action est irréversible.');
        if (!confirmed) {
            event.preventDefault();
        }
    });
});
