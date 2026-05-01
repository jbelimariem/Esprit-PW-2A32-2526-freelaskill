function exportToPDF() {
    const table = document.querySelector('.data-table');
    if (!table) {
        alert('Aucun tableau trouve a exporter.');
        return;
    }
    
    const pageTitle = document.querySelector('.admin-page-title');
    const filename = (pageTitle ? pageTitle.textContent.trim() : 'Export') + '_' + new Date().toLocaleDateString('fr-FR').replace(/\//g, '-');
    
    if (typeof html2pdf === 'undefined') {
        alert('Erreur: Bibliotheque PDF non chargee. Veuillez rafraichir la page.');
        return;
    }
    
    const clonedTable = table.cloneNode(true);
    
    // Nettoyer les classes pour eviter les conflits avec le theme sombre
    clonedTable.removeAttribute('class');
    clonedTable.querySelectorAll('*').forEach(el => {
        el.removeAttribute('class');
        if (el.tagName === 'IMG') {
            el.style.maxWidth = '60px';
            el.style.maxHeight = '60px';
            el.style.objectFit = 'cover';
            el.style.borderRadius = '4px';
        }
    });
    
    // Supprimer la derniere colonne (Actions)
    clonedTable.querySelectorAll('tr').forEach(row => {
        const cells = row.querySelectorAll('th, td');
        if (cells.length > 0) {
            cells[cells.length - 1].remove();
        }
    });
    
    const element = document.createElement('div');
    element.style.padding = '20px';
    element.style.backgroundColor = '#ffffff';
    element.style.color = '#000000';
    element.style.fontFamily = 'Arial, sans-serif';
    
    const title = document.createElement('h2');
    title.textContent = pageTitle ? pageTitle.textContent.trim() : 'Rapport d\'Export';
    title.style.textAlign = 'center';
    title.style.marginBottom = '20px';
    title.style.color = '#000000';
    
    element.appendChild(title);
    element.appendChild(clonedTable);
    
    const style = document.createElement('style');
    style.innerHTML = \
        * { color: #000000 !important; font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 20px; }
        th { background-color: #f0f0f0 !important; padding: 12px; text-align: left; border: 1px solid #cccccc; font-weight: bold; }
        td { padding: 10px; border: 1px solid #cccccc; vertical-align: middle; }
        tr:nth-child(even) { background-color: #fafafa !important; }
        div, span, p { background: transparent !important; margin: 0; padding: 0; }
        td div { display: flex; align-items: center; gap: 10px; }
    \;
    element.appendChild(style);
    
    const opt = {
        margin: 10,
        filename: filename + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
        jsPDF: { orientation: 'landscape', unit: 'mm', format: 'a4' }
    };
    
    html2pdf().set(opt).from(element).save();
}
