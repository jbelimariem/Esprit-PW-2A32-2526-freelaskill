const CART_STORAGE_KEY = 'freelaSkillCart';

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
    const count = document.querySelector('.cart-count');
    if (!count) return;
    const total = getCart().reduce((sum, item) => sum + item.quantity, 0);
    count.textContent = total;
}

function getCardProductData(card) {
    const title = card.querySelector('.card-title')?.textContent.trim() || '';
    const priceText = card.querySelector('.price-main')?.textContent.trim() || '0';
    const price = parseInt(priceText.replace(/[^\d]/g, ''), 10) || 0;
    const category = card.querySelector('.card-category')?.textContent.trim() || '';
    
    const imageText = card.querySelector('.card-image')?.innerText.trim() || '';
    const icon = imageText.split('\n')[0].trim();
    
    const imgEl = card.querySelector('.card-image img');
    const imageSrc = imgEl ? imgEl.getAttribute('src') : '';

    const badge = card.querySelector('.card-badge')?.textContent.trim() || '';
    return { title, price, category, icon, imageSrc, badge };
}

function addToCart(product) {
    const cart = getCart();
    const existing = cart.find(item => item.title === product.title);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({ ...product, quantity: 1 });
    }
    saveCart(cart);
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
                    `<div class="item-icon">${item.icon || '🛍️'}</div>`
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
let currentMaxPrice = 3000;
let currentAvailability = 'all'; // 'all', 'en stock', 'stock faible'

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
        const activeOption = availabilitySection.querySelector('.filter-option.active span');
        const activeText = normalizeFilterValue(activeOption?.textContent || '');
        if (activeText.includes('faible')) {
            currentAvailability = 'stock faible';
        } else if (activeText.includes('tous')) {
            currentAvailability = 'all';
        } else {
            currentAvailability = 'en stock';
        }
    }
}

function applyFilters() {
    const grid = document.querySelector('.products-grid');
    if (!grid) return;
    
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
        const priceText = card.querySelector('.price-main')?.textContent.trim() || '0';
        const price = parseInt(priceText.replace(/[^\d]/g, ''), 10) || 0;
        
        const stockInfo = normalizeFilterValue(card.querySelector('.stock-info')?.textContent || '');
        let stockState = 'en stock';
        if (stockInfo.includes('rupture')) stockState = 'rupture';
        else if (stockInfo.includes('faible')) stockState = 'faible';
        
        const matchesCategory = currentCategoryFilter === 'all' || category === currentCategoryFilter;
        const matchesSearch = currentSearchTerm === '' || title.includes(currentSearchTerm);
        const matchesPrice = price >= currentMinPrice && price <= currentMaxPrice;
        
        let matchesStock = true;
        if (currentAvailability === 'en stock') matchesStock = stockState === 'en stock' || stockState === 'faible';
        else if (currentAvailability === 'stock faible') matchesStock = stockState === 'faible';
        
        if (matchesCategory && matchesSearch && matchesPrice && matchesStock) {
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
                    if (optText.includes('faible')) currentAvailability = 'stock faible';
                    else if (optText.includes('tous')) currentAvailability = 'all';
                    else currentAvailability = 'en stock';
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
        currentMaxPrice = parseInt(priceMaxInput.value) || 3000;
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
        currentSearchTerm = e.target.value.toLowerCase().trim();
        applyFilters();
    });
    
    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
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

const checkoutButton = document.querySelector('#checkout-btn');
if (checkoutButton) {
    checkoutButton.addEventListener('click', e => {
        e.preventDefault();
        if (getCart().length === 0) return;
        clearCart();
        alert('Merci ! Votre commande a bien été prise en compte.');
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

