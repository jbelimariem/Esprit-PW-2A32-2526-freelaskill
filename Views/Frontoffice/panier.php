<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=6">
    <style>
        .address-block { margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem; }
        .address-block label { font-size: 0.78rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        .address-input {
            width: 100%; padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.05); border: 1.5px solid rgba(255,255,255,0.12);
            border-radius: 0.75rem; color: #e2e8f0;
            font-family: 'Space Grotesk', sans-serif; font-size: 0.9rem;
            transition: border-color .25s, box-shadow .25s; resize: none; outline: none; box-sizing: border-box;
        }
        .address-input::placeholder { color: #475569; }
        .address-input:focus  { border-color: rgba(59,130,246,.6); box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
        .address-input.error  { border-color: rgba(239,68,68,.6);  box-shadow: 0 0 0 3px rgba(239,68,68,.1); }
        .address-error        { font-size: .78rem; color: #f87171; display: none; }
        .address-error.show   { display: block; }

        /* Toast */
        #order-toast {
            position: fixed; bottom: 2rem; right: 2rem; z-index: 9999;
            display: none; flex-direction: column; gap: .4rem; max-width: 360px;
            background: rgba(15,17,24,.96); backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.1); border-radius: 1.25rem;
            padding: 1.25rem 1.5rem; box-shadow: 0 20px 60px rgba(0,0,0,.5);
            animation: slideInToast .4s cubic-bezier(.34,1.56,.64,1) forwards;
        }
        #order-toast.show    { display: flex; }
        #order-toast.success { border-color: rgba(16,185,129,.3); }
        #order-toast.error   { border-color: rgba(239,68,68,.3); }
        .toast-icon  { font-size: 1.5rem; margin-bottom: .2rem; }
        .toast-title { font-weight: 700; font-size: 1rem; color: white; }
        .toast-sub   { font-size: .85rem; color: #94a3b8; line-height: 1.5; }
        @keyframes slideInToast { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        /* Spinner sur le bouton */
        #checkout-btn { position: relative; overflow: hidden; }
        #checkout-btn .btn-spinner {
            display: none; width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.3); border-top-color: white;
            border-radius: 50%; animation: spin .6s linear infinite; margin-right: .5rem;
        }
        #checkout-btn.loading .btn-spinner { display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="page-anim cart-page">

<!-- NAVBAR -->
<nav>
    <div class="logo">
        <i class="fa-solid fa-shapes"></i>
        Freela<span>Skill</span>
    </div>
    <ul class="nav-links">
        <li><a href="#">Accueil</a></li>
        <li><a href="#">Missions</a></li>
        <li><a href="home.php">Marketplace</a></li>
        <li><a href="#">Freelancers</a></li>
    </ul>
    <div class="nav-right">
        <a href="home.php" class="cart-btn">
            <i class="fa-solid fa-arrow-left"></i> Boutique
        </a>
        <div class="nav-avatar">AH</div>
    </div>
</nav>

<!-- MARKETPLACE LAYOUT — même structure que home.php -->
<div class="marketplace-layout">

    <!-- ── SIDEBAR ── -->
    <aside class="mkt-sidebar">

        <!-- Card 1 : Résumé panier -->
        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-cart-shopping"></i></div>
                <div class="mkt-profile-name">Mon Panier</div>
                <div class="mkt-profile-sub" id="sidebar-cart-info">0 article(s)</div>
            </div>
            <div class="mkt-profile-stats">
                <div class="mkt-stat">
                    <div class="mkt-stat-val" id="sidebar-qty">0</div>
                    <div class="mkt-stat-label">Articles</div>
                </div>
                <div class="mkt-stat">
                    <div class="mkt-stat-val" id="sidebar-total">0</div>
                    <div class="mkt-stat-label">Total DT</div>
                </div>
            </div>
        </div>

        <!-- Card 2 : Navigation + Infos -->
        <div class="mkt-sidebar-card">

            <!-- Navigation -->
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="home.php" class="nav-item">
                    <i class="fa-solid fa-store"></i> Tout parcourir
                </a>
                <a href="panier.php" class="nav-item active">
                    <i class="fa-solid fa-cart-shopping"></i> Mon panier
                </a>
                <a href="mes_ventes.php" class="nav-item">
                    <i class="fa-solid fa-tag"></i> Mes ventes
                </a>
                <a href="vendreproduit.php" class="nav-item">
                    <i class="fa-solid fa-plus-circle"></i> Vendre un produit
                </a>
            </div>

            <!-- Infos livraison -->
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Informations</div>
                <div class="filter-option active">
                    <span><i class="fa-solid fa-truck" style="color:#3b82f6;margin-right:.4rem;"></i>Livraison Tunisie</span>
                </div>
                <div class="filter-option">
                    <span><i class="fa-solid fa-shield-halved" style="color:#10b981;margin-right:.4rem;"></i>Paiement sécurisé</span>
                </div>
                <div class="filter-option">
                    <span><i class="fa-solid fa-rotate-left" style="color:#a855f7;margin-right:.4rem;"></i>Retour sous 14 jours</span>
                </div>
            </div>

        </div>

    </aside>

    <!-- ── MAIN PANEL ── -->
    <div class="mkt-main">

        <!-- HERO compact -->
        <section class="hero-banner" style="padding:2rem 2rem 1.5rem;">
            <div class="hero-glow"></div>
            <div class="hero-glow-2"></div>
            <div class="hero-content" style="margin: 0 auto; text-align: center; display: flex; flex-direction: column; align-items: center;">
                <div class="hero-tag"><i class="fa-solid fa-cart-shopping"></i> Mon panier</div>
                <h1 class="hero-title" style="font-size:2rem;">Vos articles sélectionnés</h1>
                <p class="hero-sub" style="font-size:.9rem;margin-bottom:0;">Vérifiez votre commande et confirmez votre achat.</p>
            </div>
        </section>

        <div class="products-toolbar">
            <p class="result-count"><strong id="cart-qty">0</strong> article(s) dans le panier</p>
            <div class="toolbar-right">
                <button class="view-btn active" title="Panier"><i class="fa-solid fa-cart-shopping"></i></button>
            </div>
        </div>

        <div class="cart-content">
            <div class="cart-items" id="cart-items"></div>

            <!-- CHECKOUT CARD -->
            <aside class="checkout-card">
                <div class="checkout-title">Résumé de commande</div>
                <div class="checkout-row"><span>Sous-total</span><span id="cart-subtotal">0 DT</span></div>
                <div class="checkout-row"><span>Livraison</span><span style="color:#10b981;">Gratuite</span></div>
                <div class="checkout-row total"><span>Total</span><span id="cart-total">0 DT</span></div>

                <div class="address-block">
                    <label for="adresse-livraison"><i class="fa-solid fa-location-dot"></i> Adresse de livraison</label>
                    <textarea id="adresse-livraison" class="address-input" rows="2" placeholder="Ex: 12 Rue de Carthage, Tunis 1001"></textarea>
                    <span class="address-error" id="adresse-error">Veuillez saisir votre adresse de livraison.</span>
                </div>

                <button id="checkout-btn" class="btn-cart" style="margin-top:.75rem;">
                    <span class="btn-spinner"></span>
                    <i class="fa-solid fa-check" id="checkout-icon"></i>
                    <span id="checkout-label">Valider la commande</span>
                </button>
                <a href="home.php" class="cart-btn" style="margin-top:.5rem;">Continuer mes achats</a>
            </aside>
        </div>

        <!-- Empty state -->
        <div class="cart-empty" id="cart-empty">
            <h2>Votre panier est vide</h2>
            <p>Ajoutez des produits depuis la Marketplace puis revenez ici.</p>
            <a href="home.php" class="btn btn-primary" style="margin-top:1.5rem;display:inline-flex;">
                <i class="fa-solid fa-store"></i> Retour à la boutique
            </a>
        </div>

    </div><!-- /mkt-main -->
</div><!-- /marketplace-layout -->

<!-- TOAST -->
<div id="order-toast">
    <span class="toast-icon"  id="toast-icon">✅</span>
    <span class="toast-title" id="toast-title">Commande validée !</span>
    <span class="toast-sub"   id="toast-sub"></span>
</div>

<script src="../assets/js.js?v=4"></script>
<script>
(function () {
    function showToast(success, title, sub) {
        var t = document.getElementById('order-toast');
        document.getElementById('toast-icon').textContent  = success ? '✅' : '❌';
        document.getElementById('toast-title').textContent = title;
        document.getElementById('toast-sub').textContent   = sub;
        t.className = 'show ' + (success ? 'success' : 'error');
        setTimeout(function () { t.className = ''; }, 6000);
    }

    function getApiUrl() {
        var parts = window.location.pathname.split('/');
        var idx   = parts.indexOf('Esprit-PW-2A32-2526-TalentBridge');
        return idx !== -1
            ? parts.slice(0, idx + 1).join('/') + '/api/create_order.php'
            : '../../api/create_order.php';
    }

    var btn        = document.getElementById('checkout-btn');
    var adresseEl  = document.getElementById('adresse-livraison');
    var adresseErr = document.getElementById('adresse-error');
    var labelEl    = document.getElementById('checkout-label');
    var iconEl     = document.getElementById('checkout-icon');

    if (!btn) return;

    btn.addEventListener('click', function () {
        var cart = getCart();
        if (!cart || cart.length === 0) {
            showToast(false, 'Panier vide', 'Ajoutez au moins un produit avant de valider.');
            return;
        }
        var adresse = adresseEl.value.trim();
        if (!adresse) {
            adresseEl.classList.add('error');
            adresseErr.classList.add('show');
            adresseEl.focus();
            return;
        }
        adresseEl.classList.remove('error');
        adresseErr.classList.remove('show');

        btn.disabled = true;
        btn.classList.add('loading');
        iconEl.style.display = 'none';
        labelEl.textContent  = 'Envoi en cours…';

        fetch(getApiUrl(), {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ cart: cart, adresse: adresse })
        })
        .then(function (res) {
            if (!res.ok) return res.text().then(function (t) { throw new Error('HTTP ' + res.status + ' : ' + t); });
            return res.json();
        })
        .then(function (data) {
            btn.disabled = false;
            btn.classList.remove('loading');
            iconEl.style.display = '';
            labelEl.textContent  = 'Valider la commande';
            if (data.success) {
                clearCart();
                showToast(true, 'Commande #' + data.order_id + ' créée !',
                    'Montant : ' + Number(data.montant).toLocaleString('fr-FR') + ' DT — Merci !');
                setTimeout(function () { window.location.href = 'home.php'; }, 2500);
            } else {
                showToast(false, 'Erreur', data.error || 'Impossible de créer la commande.');
            }
        })
        .catch(function (err) {
            btn.disabled = false;
            btn.classList.remove('loading');
            iconEl.style.display = '';
            labelEl.textContent  = 'Valider la commande';
            showToast(false, 'Erreur réseau', err.message);
        });
    });

    adresseEl.addEventListener('input', function () {
        if (adresseEl.value.trim()) {
            adresseEl.classList.remove('error');
            adresseErr.classList.remove('show');
        }
    });
})();
</script>
</body>
</html>