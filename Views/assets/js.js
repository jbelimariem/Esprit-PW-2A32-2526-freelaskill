const CART_STORAGE_KEY = 'freelaSkillCart';
const THEME_STORAGE_KEY = 'freelaSkillTheme';

// ==========================================
// Dark Mode / Light Mode Toggle
// ==========================================
function initTheme() {
    const savedTheme = localStorage.getItem(THEME_STORAGE_KEY) || 'dark';
    applyTheme(savedTheme);
}

function applyTheme(theme) {
    const html = document.documentElement;
    const body = document.body;
    
    if (theme === 'light') {
        html.style.colorScheme = 'light';
        html.style.filter = 'invert(0)';
        body.classList.add('light-mode');
        body.classList.remove('dark-mode');
        // Appliquer les variables CSS directement
        html.style.setProperty('--bg-dark', '#f9fafb');
        html.style.setProperty('--text-light', '#1f2937');
        html.style.setProperty('--bg-card', 'rgba(0, 0, 0, 0.03)');
        html.style.setProperty('--border', 'rgba(0, 0, 0, 0.08)');
    } else {
        html.style.colorScheme = 'dark';
        html.style.filter = 'invert(0)';
        body.classList.add('dark-mode');
        body.classList.remove('light-mode');
        // Réinitialiser les variables CSS
        html.style.setProperty('--bg-dark', '#020617');
        html.style.setProperty('--text-light', '#cbd5e1');
        html.style.setProperty('--bg-card', 'rgba(255, 255, 255, 0.03)');
        html.style.setProperty('--border', 'rgba(255, 255, 255, 0.08)');
    }
    localStorage.setItem(THEME_STORAGE_KEY, theme);
    updateThemeIcon();
}

function toggleTheme() {
    const currentTheme = localStorage.getItem(THEME_STORAGE_KEY) || 'dark';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    applyTheme(newTheme);
}

function updateThemeIcon() {
    const themeBtn = document.querySelector('.theme-toggle-btn');
    if (!themeBtn) return;
    const currentTheme = localStorage.getItem(THEME_STORAGE_KEY) || 'dark';
    const icon = themeBtn.querySelector('i');
    if (currentTheme === 'light') {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    } else {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
    }
}

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    const themeBtn = document.querySelector('.theme-toggle-btn');
    if (themeBtn) {
        themeBtn.addEventListener('click', toggleTheme);
    }
});

// ==========================================
// Cart Management
// ==========================================
function getCart() {
    try {
        return JSON.parse(localStorage.getItem(CART_STORAGE_KEY)) || [];
    } catch (error) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    updateCartCount();
}

function updateCartCount() {
    const cart  = getCart();
    const total = cart.reduce((sum, item) => sum + item.quantity, 0);
    const amount= cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    // Navbar cart badges. Some notification badges also use .cart-count,
    // so only update badges attached to panier links.
    const countBadges = document.querySelectorAll('a[href*="panier.php"] .cart-count, [data-cart-count]');
    countBadges.forEach(count => {
        count.textContent = total;
        count.style.display = total > 0 ? 'flex' : 'none';
    });

    // Sidebar stats on panier.php
    const sqty = document.getElementById('sidebar-qty');
    const stot = document.getElementById('sidebar-total');
    const sinf = document.getElementById('sidebar-cart-info');
    if (sqty)  sqty.textContent  = total;
    if (stot)  stot.textContent  = amount.toLocaleString('fr-FR');
    if (sinf)  sinf.textContent  = total + ' article' + (total > 1 ? 's' : '');
}

function getCardProductData(card) {
    const id = card.dataset.id || '';
    const ownerId = card.dataset.ownerId || '';
    const title = card.querySelector('.card-title')?.textContent.trim() || '';
    const priceText = card.querySelector('.price-main')?.textContent.trim() || '0';
    const price = parseInt(priceText.replace(/[^\d]/g, ''), 10) || 0;
    const category = card.querySelector('.card-category')?.textContent.trim() || '';
    
    const icon = card.querySelector('.card-image > span')?.textContent.trim() || '';
    
    const imgEl = card.querySelector('.card-image img');
    const imageSrc = imgEl ? imgEl.getAttribute('src') : '';

    const badge = card.querySelector('.card-badge')?.textContent.trim() || '';
    return { id, ownerId, title, price, category, icon, imageSrc, badge };
}

function addToCart(product) {
    const cart = getCart();
    const existing = cart.find(item => (item.id && product.id) ? item.id === product.id : item.title === product.title);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({ ...product, quantity: 1 });
    }
    saveCart(cart);
}

function getCartItemIcon(item) {
    const icon = String(item.icon || '').trim();
    return icon.toLowerCase().includes('comparer') ? '' : icon;
}

function renderCartPage() {
    if (!document.body.classList.contains('cart-page')) return;

    const cart = getCart();
    const itemsContainer = document.querySelector('#cart-items');
    const emptyState = document.querySelector('#cart-empty');
    const summaryCount = document.querySelector('#cart-qty');
    const subtotalEl = document.querySelector('#cart-subtotal');
    const totalEl = document.querySelector('#cart-total');
    const checkoutCard = document.querySelector('.checkout-card');

    if (!itemsContainer || !summaryCount || !subtotalEl || !totalEl || !checkoutCard) return;

    const totalQuantity = cart.reduce((sum, item) => sum + item.quantity, 0);
    const subtotal = cart.reduce((sum, item) => sum + item.quantity * item.price, 0);

    summaryCount.textContent = totalQuantity;
    subtotalEl.textContent = `${subtotal.toLocaleString('fr-FR')} DT`;
    totalEl.textContent = `${subtotal.toLocaleString('fr-FR')} DT`;

    if (cart.length === 0) {
        itemsContainer.innerHTML = '';
        emptyState.style.display = 'block';
        checkoutCard.style.display = 'none';
        return;
    }

    emptyState.style.display = 'none';
    checkoutCard.style.display = 'flex';

    itemsContainer.innerHTML = cart.map((item, index) => `
        <div class="cart-item">
            <div class="cart-item-left">
                ${item.imageSrc ? 
                    `<img src="${item.imageSrc}" alt="${item.title}" style="width: 65px; height: 65px; object-fit: cover; border-radius: 0.5rem;">` : 
                    `<div class="item-icon">${getCartItemIcon(item) || '<i class="fa-solid fa-box"></i>'}</div>`
                }
                <div>
                    <div class="cart-item-name">${item.title}</div>
                    <div class="cart-item-meta">${item.category}${item.badge ? ' · ' + item.badge : ''}</div>
                </div>
            </div>
            <div class="cart-item-right">
                <div class="cart-item-price">${item.price.toLocaleString('fr-FR')} DT</div>
                <div class="cart-item-qty">Quantité : ${item.quantity}</div>
                <button type="button" class="remove-item" data-index="${index}">Supprimer</button>
            </div>
        </div>
    `).join('');
}

function removeCartItem(index) {
    const cart = getCart();
    cart.splice(index, 1);
    saveCart(cart);
    renderCartPage();
}

function clearCart() {
    localStorage.removeItem(CART_STORAGE_KEY);
    updateCartCount();
    renderCartPage();
}

updateCartCount();
renderCartPage();

// Toggle wishlist
const wishlistButtons = document.querySelectorAll('.wishlist-btn');
wishlistButtons.forEach(btn => {
    btn.addEventListener('click', e => {
        e.stopPropagation();
        btn.classList.toggle('active');
        const icon = btn.querySelector('i');
        icon.className = btn.classList.contains('active') ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
    });
});

// View toggle
const viewButtons = document.querySelectorAll('.view-btn');
viewButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        viewButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});

// Filter options
let currentCategoryFilter = 'all';
let currentSearchTerm = '';
let currentMinPrice = 0;
let currentMaxPrice = 1000000;
let currentAvailability = 'all'; 
let visualSearchAllowedIds = null; 
let visualSearchProductIds = null;

function normalizeFilterValue(value) {
    return value.toString().trim().toLowerCase();
}

function clearFormErrors(form) {
    form.querySelectorAll('.field-error').forEach(error => error.remove());
}

function addFormError(input, message) {
    const error = document.createElement('p');
    error.className = 'field-error';
    error.style.color = '#f87171';
    error.style.fontSize = '0.9rem';
    error.style.margin = '0.35rem 0 0';
    error.textContent = message;

    if (input && (input.id === 'product-image' || input.id === 'image')) {
        const imageErrorContainer = document.getElementById('image-error-container');
        if (imageErrorContainer) {
            imageErrorContainer.textContent = message;
            return;
        }
    }

    const container = input.closest('div') || input.parentElement;
    if (container) {
        container.appendChild(error);
    }
}

function validateProductForm(form) {
    clearFormErrors(form);

    const title = form.querySelector('#title');
    const category = form.querySelector('#category');
    const price = form.querySelector('#price');
    const stock = form.querySelector('#stock');
    const description = form.querySelector('#description');
    const availability = form.querySelector('#availability');
    const imageInput = form.querySelector('#product-image, #image');
    let valid = true;

    if (title && title.value.trim().length < 5) {
        addFormError(title, 'Le titre doit contenir au moins 5 caractères.');
        valid = false;
    }

    if (category && category.value.trim() === '') {
        addFormError(category, 'Choisissez une catégorie.');
        valid = false;
    }

    if (price) {
        const parsedPrice = Number(price.value);
        if (price.value.trim() === '' || Number.isNaN(parsedPrice) || parsedPrice <= 0) {
            addFormError(price, 'Entrez un prix valide supérieur à 0.');
            valid = false;
        }
    }

    if (stock) {
        const parsedStock = Number(stock.value);
        if (stock.value.trim() === '' || Number.isNaN(parsedStock) || parsedStock < 0 || !Number.isInteger(parsedStock)) {
            addFormError(stock, 'Entrez un stock valide de 0 ou plus.');
            valid = false;
        }
    }

    if (description && description.value.trim().length < 20) {
        addFormError(description, 'La description doit contenir au moins 20 caractères.');
        valid = false;
    }

    if (availability && availability.value.trim() === '') {
        addFormError(availability, 'Sélectionnez une disponibilité.');
        valid = false;
    }

    if (imageInput && imageInput.files.length > 0) {
        const file = imageInput.files[0];
        const allowedTypes = ['image/png', 'image/jpeg', 'image/webp'];
        const maxSize = 5 * 1024 * 1024;

        if (!allowedTypes.includes(file.type)) {
            addFormError(imageInput, 'Le fichier doit être une image PNG, JPG ou WEBP.');
            valid = false;
        }
        if (file.size > maxSize) {
            addFormError(imageInput, 'La taille de l’image doit être inférieure à 5 Mo.');
            valid = false;
        }
    }

    return valid;
}

function setupImagePreview() {
    const imageInput = document.getElementById('product-image') || document.getElementById('image');
    if (!imageInput) return;

    const imageDropzone = document.getElementById('image-dropzone');
    const imagePreview = document.getElementById('image-preview');
    const imagePrompt = document.getElementById('image-prompt');

    const updatePreview = (file) => {
        clearFormErrors(imageInput.closest('form') || imageInput.parentElement);

        if (!file) {
            if (imagePreview) imagePreview.style.display = 'none';
            if (imagePrompt) imagePrompt.textContent = 'Glissez vos images ici ou cliquez pour parcourir';
            return;
        }

        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            if (imagePrompt) imagePrompt.textContent = 'Fichier non valide. Choisissez une image PNG, JPG ou WEBP.';
            addFormError(imageInput, 'Le fichier doit être une image PNG, JPG ou WEBP.');
            if (imagePreview) imagePreview.style.display = 'none';
            return;
        }

        if (imagePreview) {
            const reader = new FileReader();
            reader.onload = (event) => {
                imagePreview.src = event.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        if (imagePrompt) {
            imagePrompt.textContent = file.name;
        }
    };

    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];
        updatePreview(file);
    });

    if (!imageDropzone) return;

    imageDropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        imageDropzone.style.borderColor = 'rgba(59,130,246,0.8)';
        imageDropzone.style.background = 'rgba(59,130,246,0.06)';
    });

    imageDropzone.addEventListener('dragleave', () => {
        imageDropzone.style.borderColor = 'rgba(59,130,246,0.3)';
        imageDropzone.style.background = 'rgba(59,130,246,0.02)';
    });

    imageDropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        imageDropzone.style.borderColor = 'rgba(59,130,246,0.3)';
        imageDropzone.style.background = 'rgba(59,130,246,0.02)';
        const file = event.dataTransfer.files[0];
        if (file) {
            imageInput.files = event.dataTransfer.files;
            imageInput.dispatchEvent(new Event('change'));
        }
    });
}

function bindProductForms() {
    const productForms = [
        document.getElementById('sell-form'),
        document.getElementById('admin-product-form')
    ].filter(Boolean);

    productForms.forEach(form => {
        form.addEventListener('submit', (event) => {
            if (!validateProductForm(form)) {
                event.preventDefault();
            }
        });
    });
}

function initializeProductFormHandlers() {
    setupImagePreview();
    bindProductForms();
}

function initializeFilters() {
    const activeCategory = document.querySelector('.filter-section .filter-option.active[data-filter]');
    if (activeCategory) {
        currentCategoryFilter = normalizeFilterValue(activeCategory.dataset.filter || 'all');
    }

    const availabilitySection = Array.from(document.querySelectorAll('.filter-section')).find(section =>
        normalizeFilterValue(section.querySelector('.filter-title')?.textContent || '') === 'disponibilité'
    );

    if (availabilitySection) {
        const activeOption = availabilitySection.querySelector('.filter-option.active');
        if (activeOption) {
            currentAvailability = activeOption.dataset.filter ? normalizeFilterValue(activeOption.dataset.filter) : 'all';
        }
    }
}

function applyFilters() {
    const grid = document.querySelector('.products-grid');
    if (!grid) return;
    const isSellerPage = document.body.dataset.page === 'mes-ventes';
    
    // Sort logic
    const sortSelect = document.querySelector('.sort-select');
    const sortType = sortSelect ? normalizeFilterValue(sortSelect.value) : '';
    
    // Convert NodeList to Array to sort them
    const cardsArray = Array.from(grid.querySelectorAll('.product-card:not([style*="text-align: center"])'));
    
    // Sorting
    cardsArray.sort((a, b) => {
        const priceA = parseInt(a.querySelector('.price-main')?.textContent.replace(/[^\d]/g, '') || 0, 10);
        const priceB = parseInt(b.querySelector('.price-main')?.textContent.replace(/[^\d]/g, '') || 0, 10);

        if (sortType.includes('croissant') && !sortType.includes('décroissant')) return priceA - priceB;
        if (sortType.includes('décroissant')) return priceB - priceA;
        return 0; // Default order
    });

    // Re-append sorted
    cardsArray.forEach(card => grid.appendChild(card));

    // Filtering
    let count = 0;
    
    cardsArray.forEach(card => {
        const category = normalizeFilterValue(card.querySelector('.card-category')?.textContent || '');
        const title = normalizeFilterValue(card.querySelector('.card-title')?.textContent || '');
        const description = normalizeFilterValue(card.querySelector('.rating-text')?.textContent || '');
        const priceText = card.querySelector('.price-main')?.textContent.trim() || '0';
        const price = parseInt(priceText.replace(/[^\d]/g, ''), 10) || 0;
        
        const dispoText = normalizeFilterValue(card.dataset.dispo || 'disponible maintenant');
        
        const matchesCategory = currentCategoryFilter === 'all' || category === currentCategoryFilter;
        
        // Recherche multi-mots (plus flexible)
        let matchesSearch = true;
        if (currentSearchTerm !== '') {
            const searchWords = currentSearchTerm.split(' ');
            matchesSearch = searchWords.some(word => title.includes(word) || category.includes(word));
        }

        const matchesPrice = isSellerPage || (price >= currentMinPrice && price <= currentMaxPrice);
        
        let matchesStock = true;
        if (currentAvailability !== 'all') {
            matchesStock = dispoText === currentAvailability;
        }

        // Filtre spécifique Recherche Visuelle (si actif)
        let matchesVisual = true;
        if (typeof visualSearchAllowedIds !== 'undefined' && visualSearchAllowedIds !== null) {
            matchesVisual = visualSearchAllowedIds.includes(parseInt(card.dataset.id));
        }
        
        if (matchesCategory && matchesSearch && matchesPrice && matchesStock && matchesVisual) {
            card.style.display = '';
            count++;
        } else {
            card.style.display = 'none';
        }
    });

    const resultCount = document.querySelector('.result-count');
    if (resultCount && !document.body.classList.contains('cart-page')) {
        resultCount.innerHTML = `<strong>${count} produit${count > 1 ? 's' : ''}</strong> trouvés`;
    }
}

const filterSections = document.querySelectorAll('.filter-section');
filterSections.forEach(section => {
    const filterTitle = normalizeFilterValue(section.querySelector('.filter-title')?.textContent || '');
    
    if (filterTitle === 'catégorie' || filterTitle === 'disponibilité') {
        section.querySelectorAll('.filter-option').forEach(opt => {
            opt.addEventListener('click', () => {
                section.querySelectorAll('.filter-option').forEach(o => o.classList.remove('active'));
                opt.classList.add('active');
                
                const optText = normalizeFilterValue(opt.querySelector('span')?.textContent || '');
                
                if (filterTitle === 'catégorie') {
                    currentCategoryFilter = opt.dataset.filter ? normalizeFilterValue(opt.dataset.filter) : 'all';
                } else if (filterTitle === 'disponibilité') {
                    currentAvailability = opt.dataset.filter ? normalizeFilterValue(opt.dataset.filter) : 'all';
                }
                
                applyFilters();
            });
        });
    }
});

// Initialize active filters and counts on page load
initializeFilters();
applyFilters();

const sortSelect = document.querySelector('.sort-select');
if (sortSelect) {
    sortSelect.addEventListener('change', () => {
        applyFilters();
    });
}

// Price filter setup
const priceMinInput = document.querySelectorAll('.price-input')[0];
const priceMaxInput = document.querySelectorAll('.price-input')[1];
const priceRange = document.querySelector('input[type="range"]');

if (priceMinInput && priceMaxInput && priceRange) {
    const syncPrices = () => {
        currentMinPrice = parseInt(priceMinInput.value) || 0;
        currentMaxPrice = parseInt(priceMaxInput.value) || 1000000;
        applyFilters();
    };

    priceMinInput.addEventListener('input', syncPrices);
    priceMaxInput.addEventListener('input', syncPrices);
    
    priceRange.addEventListener('input', (e) => {
        priceMaxInput.value = e.target.value;
        currentMaxPrice = parseInt(e.target.value);
        applyFilters();
    });
}

const searchInput = document.getElementById('main-search-input');
const searchBtn = document.getElementById('main-search-btn');

if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        if (e.isTrusted) visualSearchProductIds = null;
        currentSearchTerm = e.target.value.toLowerCase().trim();
        applyFilters();
    });
    
    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            visualSearchProductIds = null;
            currentSearchTerm = searchInput.value.toLowerCase().trim();
            applyFilters();
        });
    }
}

initializeProductFormHandlers();

// Add to cart
const addCartButtons = document.querySelectorAll('.product-card .btn-cart:not(:disabled)');
addCartButtons.forEach(btn => {
    btn.addEventListener('click', e => {
        e.stopPropagation();
        const card = btn.closest('.product-card');
        if (!card) return;
        addToCart(getCardProductData(card));

        btn.innerHTML = '<i class="fa-solid fa-check"></i> Ajouté !';
        btn.style.background = 'rgba(34,197,94,0.15)';
        btn.style.borderColor = 'rgba(34,197,94,0.3)';
        btn.style.color = 'var(--tech-green)';

        setTimeout(() => {
            btn.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Ajouter au panier';
            btn.style.background = '';
            btn.style.borderColor = '';
            btn.style.color = '';
        }, 1800);
    });
});

// Remove item from cart page
const cartItemsContainer = document.querySelector('#cart-items');
if (cartItemsContainer) {
    cartItemsContainer.addEventListener('click', e => {
        const btn = e.target.closest('.remove-item');
        if (!btn) return;
        const index = Number(btn.dataset.index);
        removeCartItem(index);
    });
}


// Open detail page when clicking a product card on the home page only
if (document.body.classList.contains('home-page')) {
    document.querySelectorAll('.product-card[data-id]').forEach(card => {
        card.addEventListener('click', e => {
            if (e.target.closest('.btn-cart') || e.target.closest('.wishlist-btn') || e.target.closest('button')) {
                return;
            }
            const productId = card.dataset.id;
            if (productId) {
                window.location.href = `detailproduit.php?id=${productId}`;
            }
        });
    });
}

// PDF Export function
function exportToPDF() {
    const table = document.querySelector('.data-table');
    if (!table) {
        alert('Aucun tableau trouvé à exporter.');
        return;
    }

    const pageTitle = document.querySelector('.admin-page-title');
    const filename = (pageTitle ? pageTitle.textContent.trim() : 'Export') + '_' + new Date().toLocaleDateString('fr-FR').replace(/\//g, '-');

    if (typeof html2pdf === 'undefined') {
        alert('Erreur: Bibliothèque PDF non chargée. Veuillez rafraîchir la page.');
        return;
    }

    const clonedTable = table.cloneNode(true);

    // Supprimer la dernière colonne (Actions)
    clonedTable.querySelectorAll('tr').forEach(row => {
        const cells = row.querySelectorAll('th, td');
        if (cells.length > 0) cells[cells.length - 1].remove();
    });

    // Forcer les styles inline sur chaque cellule pour écraser le thème
    clonedTable.querySelectorAll('th').forEach(th => {
        th.removeAttribute('class');
        th.style.cssText = 'background-color: #e0e0e0 !important; color: #000000 !important; padding: 10px; border: 1px solid #999; font-weight: bold; font-family: Arial, sans-serif; font-size: 12px;';
    });

    clonedTable.querySelectorAll('td').forEach((td, i) => {
        td.removeAttribute('class');
        td.style.cssText = 'background-color: #ffffff !important; color: #000000 !important; padding: 10px; border: 1px solid #999; font-family: Arial, sans-serif; font-size: 12px; vertical-align: middle;';
        // Lignes alternées
        const row = td.closest('tr');
        const rowIndex = row ? row.rowIndex : 0;
        if (rowIndex % 2 === 0) td.style.backgroundColor = '#f5f5f5 !important';

        td.querySelectorAll('*').forEach(el => {
            el.removeAttribute('class');
            el.style.color = '#000000';
            el.style.background = 'transparent';
            if (el.tagName === 'IMG') {
                el.style.cssText = 'max-width: 60px; max-height: 60px; object-fit: cover; border-radius: 4px;';
            }
        });
    });

    clonedTable.style.cssText = 'width: 100%; border-collapse: collapse;';
    clonedTable.removeAttribute('class');

    // Conteneur principal
    const element = document.createElement('div');
    element.style.cssText = 'padding: 20px; background-color: #ffffff; color: #000000; font-family: Arial, sans-serif;';

    const title = document.createElement('h2');
    title.textContent = pageTitle ? pageTitle.textContent.trim() : "Rapport d'Export";
    title.style.cssText = 'text-align: center; margin-bottom: 20px; color: #000000; font-family: Arial, sans-serif;';

    element.appendChild(title);
    element.appendChild(clonedTable);

    const opt = {
        margin: 10,
        filename: filename + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            ignoreElements: (el) => el.classList && el.classList.contains('no-export')
        },
        jsPDF: { orientation: 'landscape', unit: 'mm', format: 'a4' }
    };

    html2pdf().set(opt).from(element).save();
}

// ==========================================
// VISUAL SEARCH LOGIC (Search by Image)
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const visualSearchBtn = document.getElementById('visual-search-btn');
    const visualSearchFile = document.getElementById('visual-search-file');
    const searchInput = document.getElementById('main-search-input');

    if (!visualSearchBtn || !visualSearchFile) return;

    visualSearchBtn.addEventListener('click', (e) => {
        console.log("Camera button clicked");
        e.preventDefault();
        e.stopPropagation();
        visualSearchFile.click();
    });

    visualSearchFile.addEventListener('change', async () => {
        if (!visualSearchFile.files.length) return;

        const file = visualSearchFile.files[0];
        const formData = new FormData();
        formData.append('image', file);

        // UI Loading state
        const originalBtnIcon = visualSearchBtn.innerHTML;
        visualSearchBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        visualSearchBtn.disabled = true;

        try {
            const response = await fetch('../../api/ai_visual_search.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                console.log("Visual Search Description:", data.description);
                // Mettre à jour l'input de recherche avec la description de l'IA
                if (searchInput) {
                    searchInput.value = data.description;
                    currentSearchTerm = data.description.toLowerCase();
                    
                    // Stocker les IDs trouvés par l'IA pour un filtrage précis
                    if (data.products && data.products.length > 0) {
                        visualSearchAllowedIds = data.products.map(p => parseInt(p.idProduit));
                    } else {
                        visualSearchAllowedIds = []; // Aucun résultat
                    }

                    applyFilters();
                    
                    // Scroll vers les résultats
                    const grid = document.querySelector('.products-grid');
                    if (grid) grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    
                    // Feedback visuel
                    const resultCount = document.querySelector('.result-count');
                    if (resultCount) {
                        resultCount.innerHTML += ` <span id="visual-search-label" style="color: #3b82f6; font-size: 0.8rem; margin-left: 10px; background: rgba(59,130,246,0.1); padding: 4px 10px; border-radius: 20px;">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Image analysée : "${data.description}"
                            <button onclick="resetVisualSearch()" style="background:none; border:none; color:#ef4444; margin-left:5px; cursor:pointer;"><i class="fa-solid fa-times"></i></button>
                        </span>`;
                    }
                }
            } else {
                alert("Erreur de recherche visuelle : " + (data.error || "Inconnu"));
            }
        } catch (error) {
            console.error("Visual Search Error:", error);
            alert("Erreur lors de la communication avec l'IA.");
        } finally {
            visualSearchBtn.innerHTML = originalBtnIcon;
            visualSearchBtn.disabled = false;
            visualSearchFile.value = ''; // Reset file input
        }
    });
});

// Fonction pour réinitialiser la recherche visuelle
window.resetVisualSearch = function() {
    visualSearchAllowedIds = null;
    const searchInput = document.getElementById('main-search-input');
    if (searchInput) searchInput.value = '';
    currentSearchTerm = '';
    
    const label = document.getElementById('visual-search-label');
    if (label) label.remove();
    
    applyFilters();
};
