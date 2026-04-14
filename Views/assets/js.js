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
    const badge = card.querySelector('.card-badge')?.textContent.trim() || '';
    return { title, price, category, icon, badge };
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
                <div class="item-icon">${item.icon || '🛒'}</div>
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
const filterSections = document.querySelectorAll('.filter-section');
filterSections.forEach(section => {
    section.querySelectorAll('.filter-option').forEach(opt => {
        opt.addEventListener('click', () => {
            section.querySelectorAll('.filter-option').forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
        });
    });
});

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

