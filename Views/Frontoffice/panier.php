<?php
require_once __DIR__ . '/../../controllers/NotificationController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$user = null;
$hasAvatar = false;
$avatarUrl = '';
$initials = '';

if (!empty($_SESSION['user_id'])) {
    $userController = new UserController();
    $user = $userController->getById((int)$_SESSION['user_id']);
    if ($user) {
        $initials = strtoupper(mb_substr($user->getPrenom(), 0, 1) . mb_substr($user->getNom(), 0, 1));
        $avatar = trim((string)$user->getAvatar());
        if ($avatar !== '') {
            if (strpos($avatar, 'http') === 0) {
                $avatarUrl = $avatar;
            } else {
                $avatarUrl = '../../' . ltrim(str_replace('\\', '/', $avatar), '/');
            }
            $hasAvatar = true;
        }
    }
}

$notifController = new NotificationController();
$unreadCount = !empty($_SESSION['user_id']) ? $notifController->getUnreadCount((int)$_SESSION['user_id']) : 0;
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier — FreelaSkill</title>
    <script src="../assets/theme-init.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=6">
    <script src="../assets/theme.js" defer></script>
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
    <!-- Stripe JS SDK -->
    <script src="https://js.stripe.com/v3/"></script>
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
        <?php if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'freelancer'): ?>
        <li><a href="#">Missions</a></li>
        <?php endif; ?>
        <li><a href="home.php" class="active">Marketplace</a></li>
        <?php if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'client'): ?>
        <li><a href="#">Freelancers</a></li>
        <?php endif; ?>
        <li><a href="profile.php">Mon Profil</a></li>
    </ul>
    <div class="nav-right">
        <button type="button" class="theme-toggle" data-theme-toggle>
            <i class="fa-solid fa-sun" data-theme-icon></i>
            <span data-theme-label>Jour</span>
        </button>
        
        <a href="notifications.php" class="cart-btn" style="position: relative; margin-right: 10px; color: var(--text-muted);">
            <i class="fa-solid fa-bell"></i>
            <?php if($unreadCount > 0): ?>
                <span class="cart-count" style="position:absolute;top:-6px;right:-6px;background:#3b82f6;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <a href="panier.php" class="cart-btn" style="position: relative; margin-right: 15px; color: var(--text-muted);">
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="cart-count" style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);">0</span>
        </a>

        <?php if ($user): ?>
            <div class="nav-avatar<?php echo $hasAvatar ? ' has-image' : ''; ?>" title="<?php echo htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()); ?>">
                <?php if ($hasAvatar): ?>
                    <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Photo de profil" class="nav-avatar-image">
                <?php else: ?>
                    <?php echo $initials; ?>
                <?php endif; ?>
            </div>
            <a href="logout.php" class="btn btn-outline" style="font-size:0.82rem; padding:0.45rem 1rem; margin-left: 10px;" title="Déconnexion">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary" style="font-size:0.82rem; padding:0.45rem 1rem;">
                Connexion
            </a>
        <?php endif; ?>
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
                <a href="mes_commandes.php" class="nav-item">
                    <i class="fa-solid fa-receipt"></i> Mes commandes
                </a>
                <a href="notifications.php" class="nav-item">
                    <i class="fa-solid fa-bell"></i> Notifications
                    <?php if($unreadCount > 0): ?>
                        <span style="background:#ef4444; color:white; border-radius:50%; width:18px; height:18px; font-size:10px; display:flex; align-items:center; justify-content:center; margin-left:auto;"><?= $unreadCount ?></span>
                    <?php endif; ?>
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

              


                <div class="address-block" style="margin-top: 1.5rem;">
                    <label><i class="fa-solid fa-credit-card"></i> Mode de paiement</label>
                    <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                        <label style="flex: 1; cursor: pointer;">
                            <input type="radio" name="mode_paiement" value="Sur place" checked style="display: none;">
                            <div class="payment-opt active" data-val="Sur place" style="padding: 0.75rem; border: 1.5px solid rgba(255,255,255,0.1); border-radius: 0.75rem; text-align: center; transition: 0.3s; background: rgba(255,255,255,0.03);">
                                <i class="fa-solid fa-hand-holding-dollar"></i><br><span style="font-size: 0.75rem;">Sur place</span>
                            </div>
                        </label>
                        <label style="flex: 1; cursor: pointer;">
                            <input type="radio" name="mode_paiement" value="En ligne" style="display: none;">
                            <div class="payment-opt" data-val="En ligne" style="padding: 0.75rem; border: 1.5px solid rgba(255,255,255,0.1); border-radius: 0.75rem; text-align: center; transition: 0.3s; background: rgba(255,255,255,0.03);">
                                <i class="fa-solid fa-globe"></i><br><span style="font-size: 0.75rem;">En ligne</span>
                            </div>
                        </label>
                    </div>
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

<script src="../assets/js.js?v=6"></script>
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

    function getStripeApiUrl() {
        var parts = window.location.pathname.split('/');
        var idx   = parts.indexOf('Esprit-PW-2A32-2526-TalentBridge');
        return idx !== -1
            ? parts.slice(0, idx + 1).join('/') + '/api/create_stripe_session.php'
            : '../../api/create_stripe_session.php';
    }

    // Initialisation Stripe
    var stripe = Stripe('pk_test_your_public_key'); // Elle sera remplacée par la constante PHP ci-dessous
    <?php require_once __DIR__ . '/../../controllers/config.php'; ?>
    stripe = Stripe('<?= STRIPE_PUBLIC_KEY ?>');

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

        var modePaiement = document.querySelector('input[name="mode_paiement"]:checked').value;
        var modeLivraison = "Standard";

        // SI PAIEMENT EN LIGNE -> REDIRECTION STRIPE
        if (modePaiement === 'En ligne') {
            var total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            // On mappe les données pour que le backend reçoive les bons noms de clés
            var formattedItems = cart.map(item => ({
                idProduit: item.id,
                nom: item.title,
                prix: item.price,
                quantite: item.quantity,
                image: item.imageSrc
            }));

            fetch(getStripeApiUrl(), {
                method : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body   : JSON.stringify({ 
                    items: formattedItems,
                    adresse: adresse,
                    total: total,
                    user_id: 1 
                })
            })
            .then(res => res.json())
            .then(session => {
                if (session.error) throw new Error(session.error);
                return stripe.redirectToCheckout({ sessionId: session.id });
            })
            .catch(err => {
                btn.disabled = false;
                btn.classList.remove('loading');
                iconEl.style.display = '';
                labelEl.textContent  = 'Valider la commande';
                showToast(false, 'Erreur Stripe', err.message);
            });
            return;
        }

        // SI PAIEMENT SUR PLACE -> LOGIQUE HABITUELLE
        fetch(getApiUrl(), {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ cart: cart, adresse: adresse, mode_paiement: modePaiement, mode_livraison: modeLivraison })
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
                var mailInfo = data.mail_sent
                    ? ' Email envoye.'
                    : ' Email non envoye : ' + (data.mail_error || 'configuration SMTP a verifier.');
                showToast(true, 'Commande #' + data.order_id + ' créée !',
                    'Montant : ' + Number(data.montant).toLocaleString('fr-FR') + ' DT. ' + mailInfo);
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

    // Toggle visual for payment options
    document.querySelectorAll('input[name="mode_paiement"]').forEach(function(input) {
        input.addEventListener('change', function() {
            document.querySelectorAll('.payment-opt').forEach(function(opt) {
                opt.classList.remove('active');
                opt.style.borderColor = 'rgba(255,255,255,0.1)';
                opt.style.background = 'rgba(255,255,255,0.03)';
            });
            var div = this.nextElementSibling;
            div.classList.add('active');
            div.style.borderColor = '#3b82f6';
            div.style.background = 'rgba(59,130,246,0.1)';
        });
    });
    // Init first one payment
    var activeOpt = document.querySelector('input[name="mode_paiement"]:checked').nextElementSibling;
    activeOpt.style.borderColor = '#3b82f6';
    activeOpt.style.background = 'rgba(59,130,246,0.1)';

})();
</script>
</body>
</html>
