<?php
require_once __DIR__ . '/../../controllers/produitController.php';
require_once __DIR__ . '/../../controllers/Category_prodController.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';

$notifController = new NotificationController();
$unreadCount = $notifController->getUnreadCount(1); // Default user

$produitController = new ProduitController();
$categoryController = new Category_prodController();

$selectedCategoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

if ($selectedCategoryId) {
    $produits = $produitController->getAllFrontData($selectedCategoryId);
} else {
    $produits = $produitController->getAllFrontData();
}

$categories         = $categoryController->getAllData();

$categoryCounts = [];
foreach ($produits as $p) {
    $categoryCounts[$p['category_id']] = ($categoryCounts[$p['category_id']] ?? 0) + 1;
}
$categoryNames = [];
foreach ($categories as $cat) {
    $categoryNames[$cat['idCategory']] = $cat['nom'];
}
$totalProduitCount   = count($produits);
$totalCategoryCount  = count($categories);

// Pagination
$itemsPerPage = 12;
$currentPage  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalPages   = ceil($totalProduitCount / $itemsPerPage);
$currentPage  = min($currentPage, $totalPages > 0 ? $totalPages : 1);
$startIndex   = ($currentPage - 1) * $itemsPerPage;
$produitsPagines = array_slice($produits, $startIndex, $itemsPerPage);
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace — FreelaSkill</title>
    <meta name="description" content="Parcourez notre marketplace FreelaSkill — équipements tech, licences logiciels, accessoires créatifs.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css?v=6">
<style>
/* • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • 
   ARIA AI ADVISOR — Widget Styles
• • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • • */
#aria-fab {
    position: fixed; bottom: 32px; right: 32px; z-index: 99999;
    width: 58px; height: 58px; border-radius: 50%; border: none; cursor: pointer;
    background: var(--tech-blue);
    box-shadow: 0 4px 20px rgba(59,130,246,.5);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: #fff; transition: transform .25s ease;
    animation: aria-pulse 2.5s infinite;
}
#aria-fab:hover { transform: scale(1.1); }
#aria-fab .aria-badge {
    position: absolute; top: -4px; right: -4px;
    background: #10b981; color: #fff; border-radius: 50%;
    width: 20px; height: 20px; font-size: 9px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid #0f172a; letter-spacing: 0;
}
@keyframes aria-pulse {
    0%,100% { box-shadow: 0 4px 20px rgba(59,130,246,.5); }
    60%     { box-shadow: 0 4px 30px rgba(59,130,246,.0); }
}

/* Widget panel */
#aria-panel {
    position: fixed; bottom: 110px; right: 32px; z-index: 99998;
    width: 420px; max-height: 620px;
    background: #0f172a;
    border: 1px solid rgba(59,130,246,.2);
    border-radius: 24px;
    box-shadow: 0 32px 80px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,.04);
    display: flex; flex-direction: column; overflow: hidden;
    transform: translateY(20px) scale(.96); opacity: 0;
    pointer-events: none;
    transition: all .3s cubic-bezier(.34,1.56,.64,1);
}
#aria-panel.open {
    transform: translateY(0) scale(1); opacity: 1; pointer-events: all;
}

/* Header */
.aria-header {
    background: rgba(59,130,246,.06);
    padding: 18px 20px; display: flex; align-items: center; gap: 14px;
    border-bottom: 1px solid rgba(255,255,255,.07);
    position: relative;
}
.aria-avatar {
    width: 46px; height: 46px; border-radius: 50%;
    background: var(--tech-blue);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
    box-shadow: 0 0 16px rgba(59,130,246,.4);
}
@keyframes aria-glow {
    0%,100% { box-shadow: 0 0 12px rgba(59,130,246,.4); }
    50%     { box-shadow: 0 0 24px rgba(59,130,246,.7); }
}
.aria-header-info .aria-name { font-weight: 700; font-size: 1rem; color: #f1f5f9; }
.aria-header-info .aria-status {
    font-size: .75rem; color: #10b981;
    display: flex; align-items: center; gap: 5px;
}
.aria-status-dot {
    width: 7px; height: 7px; border-radius: 50%; background: #10b981;
    animation: blink 1.5s infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
.aria-close-btn {
    margin-left: auto; background: rgba(255,255,255,.07); border: none;
    color: #94a3b8; width: 32px; height: 32px; border-radius: 50%;
    cursor: pointer; font-size: 1rem; display: flex;
    align-items: center; justify-content: center; transition: all .2s;
}
.aria-close-btn:hover { background: rgba(239,68,68,.2); color: #ef4444; }

/* Quick suggestions */
.aria-suggestions {
    display: flex; gap: 8px; flex-wrap: wrap;
    padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,.06);
}
.aria-chip {
    background: rgba(59,130,246,.1); border: 1px solid rgba(59,130,246,.25);
    color: var(--tech-blue); font-size: .72rem; font-weight: 500;
    padding: 5px 12px; border-radius: 20px; cursor: pointer;
    transition: all .2s; white-space: nowrap;
}
.aria-chip:hover {
    background: rgba(59,130,246,.2); color: #fff;
    border-color: rgba(59,130,246,.5); transform: translateY(-1px);
}

/* Messages */
.aria-messages {
    flex: 1; overflow-y: auto; padding: 16px;
    display: flex; flex-direction: column; gap: 14px;
    scrollbar-width: thin; scrollbar-color: rgba(59,130,246,.25) transparent;
}
.aria-messages::-webkit-scrollbar { width: 4px; }
.aria-messages::-webkit-scrollbar-thumb {
    background: rgba(59,130,246,.3); border-radius: 4px;
}

.msg-bubble {
    max-width: 88%; animation: msgIn .3s ease;
}
@keyframes msgIn { from { opacity:0; transform: translateY(8px); } }
.msg-bubble.user { align-self: flex-end; }
.msg-bubble.aria  { align-self: flex-start; }
.user-msg { align-self: flex-end; }
.aria-msg { align-self: flex-start; }

.msg-content {
    padding: 11px 15px; border-radius: 16px;
    font-size: .875rem; line-height: 1.55; color: #e2e8f0;
}
.msg-bubble.user .msg-content, .user-msg .msg-content {
    background: var(--tech-blue);
    border-radius: 16px 16px 4px 16px;
}
.msg-bubble.aria .msg-content, .aria-msg .msg-content {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px 16px 16px 4px;
}
.msg-time { font-size: .68rem; color: #475569; margin-top: 4px; text-align: right; }
.msg-bubble.aria .msg-time, .aria-msg .msg-time { text-align: left; }
.user-msg .msg-time { text-align: right; }

/* Product cards in chat */
.aria-product-cards {
    display: flex; flex-direction: column; gap: 10px; margin-top: 10px;
}
.aria-prod-card {
    background: rgba(59,130,246,.07);
    border: 1px solid rgba(59,130,246,.18); border-radius: 12px;
    padding: 10px 12px; display: flex; gap: 12px; align-items: center;
    cursor: pointer; transition: all .2s; text-decoration: none;
}
.aria-prod-card:hover {
    border-color: rgba(59,130,246,.4); background: rgba(59,130,246,.14);
    transform: translateX(3px);
}
.aria-prod-img {
    width: 46px; height: 46px; border-radius: 8px; flex-shrink: 0;
    background: rgba(59,130,246,.12);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; overflow: hidden;
}
.aria-prod-img img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; }
.aria-prod-info { flex: 1; min-width: 0; }
.aria-prod-name { font-weight: 600; font-size: .85rem; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.aria-prod-cat  { font-size: .72rem; color: var(--text-muted); margin-top: 2px; }
.aria-prod-price { font-weight: 700; font-size: .9rem; color: var(--tech-blue); white-space: nowrap; }

/* Typing indicator */
.aria-typing {
    display: flex; gap: 5px; align-items: center; padding: 12px 15px;
    background: rgba(255,255,255,.07); border-radius: 16px 16px 16px 4px;
    width: fit-content;
}
.aria-typing span {
    width: 7px; height: 7px; border-radius: 50%; background: var(--tech-blue);
    animation: typing-bounce .9s infinite ease-in-out;
}
.aria-typing span:nth-child(2) { animation-delay: .15s; }
.aria-typing span:nth-child(3) { animation-delay: .3s; }
@keyframes typing-bounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-8px)} }

/* Input area */
.aria-input-area {
    padding: 14px 16px; border-top: 1px solid rgba(255,255,255,.07);
    display: flex; gap: 10px; align-items: flex-end;
}
.aria-input {
    flex: 1; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
    border-radius: 14px; padding: 10px 14px; color: #f1f5f9;
    font-size: .875rem; font-family: inherit; resize: none;
    min-height: 42px; max-height: 100px; outline: none; transition: border-color .2s;
    line-height: 1.4;
}
.aria-input::placeholder { color: #475569; }
.aria-input:focus { border-color: rgba(99,102,241,.5); }
.aria-send-btn {
    width: 42px; height: 42px; border-radius: 12px; border: none;
    background: var(--tech-blue); color: #fff;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0; transition: transform .2s, opacity .2s;
}
.aria-send-btn:hover { transform: scale(1.08); }
.aria-send-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* Intro message card */
.aria-intro { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 8px 0; }
.aria-intro-icon { font-size: 2.5rem; margin-bottom: 8px; }
.aria-intro-title { font-weight: 700; font-size: 1rem; color: #f1f5f9; margin-bottom: 6px; }
.aria-intro-sub { font-size: .8rem; color: #64748b; line-height: 1.5; }

@media (max-width: 480px) {
    #aria-panel { width: calc(100vw - 24px); right: 12px; bottom: 90px; }
    #aria-fab { bottom: 20px; right: 20px; }
}

.card-image { position: relative; overflow: hidden; }
.card-compare-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(2, 6, 23, 0.1), rgba(2, 6, 23, 0.72));
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 4;
}
.product-card:hover .card-compare-overlay {
    opacity: 1;
}
.card-compare-btn {
    background: rgba(15, 23, 42, 0.9);
    color: white;
    border: 1px solid rgba(255,255,255,0.22);
    padding: 9px 16px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.82rem;
    cursor: pointer;
    transform: translateY(10px);
    transition: all 0.3s ease;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.35);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.product-card:hover .card-compare-btn {
    transform: translateY(0);
}
.card-compare-btn:hover {
    background: var(--tech-blue);
    border-color: rgba(59,130,246,0.8);
    transform: translateY(0) scale(1.03);
}
.product-card.battle-selected { 
    border-color: var(--tech-blue) !important; 
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.3) !important; 
}
.product-card.battle-selected .card-compare-btn {
    background: rgba(16, 185, 129, 0.95);
    border-color: rgba(16, 185, 129, 0.95);
}
.product-card.battle-selected .card-compare-overlay {
    opacity: 1;
}

#battle-bar {
    position: fixed; bottom: 18px; left: 50%;
    width: min(980px, calc(100vw - 32px));
    background: rgba(15, 23, 42, 0.96); backdrop-filter: blur(20px);
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 18px;
    padding: 12px 14px;
    display: grid;
    grid-template-columns: auto 1fr auto auto;
    align-items: center;
    gap: 14px;
    transform: translate(-50%, calc(100% + 32px));
    transition: transform 0.35s cubic-bezier(.22,1,.36,1), opacity .25s ease;
    opacity: 0;
    z-index: 10000;
    box-shadow: 0 24px 70px rgba(0,0,0,0.48), 0 0 0 1px rgba(59,130,246,0.08);
}
#battle-bar.active { transform: translate(-50%, 0); opacity: 1; }

.bbar-label {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #e2e8f0;
    font-size: .88rem;
    font-weight: 800;
    white-space: nowrap;
}
.bbar-label span {
    color: #94a3b8;
    font-size: .74rem;
    font-weight: 600;
    display: block;
    margin-top: 1px;
}
.bbar-slots {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    scrollbar-width: none;
}
.bbar-slots::-webkit-scrollbar { display: none; }
.bbar-slot {
    min-width: 148px;
    max-width: 190px;
    height: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 7px 10px 7px 7px;
    color: #fff;
    background: rgba(255,255,255,0.045);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
}
.bbar-slot.placeholder {
    border-style: dashed;
    color: #94a3b8;
}
.bbar-slot-img {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    background: rgba(59,130,246,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}
.bbar-slot-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.bbar-slot-name {
    min-width: 0;
    flex: 1;
    font-size: .78rem;
    font-weight: 700;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.bbar-remove,
.bbar-clear {
    border: 0;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.bbar-remove {
    width: 22px;
    height: 22px;
    border-radius: 7px;
    color: #94a3b8;
    background: rgba(255,255,255,0.06);
    flex-shrink: 0;
}
.bbar-remove:hover,
.bbar-clear:hover {
    color: #fff;
    background: rgba(239,68,68,0.75);
}
.bbar-vs {
    color: #64748b;
    font-size: .68rem;
    font-weight: 900;
    letter-spacing: .08em;
}

.start-battle-btn {
    background: var(--tech-blue);
    color: white; border: none; padding: 11px 18px; border-radius: 12px;
    font-weight: 800;
    cursor: pointer; box-shadow: 0 10px 28px rgba(59, 130, 246, 0.32);
    transition: all 0.3s; display: flex; align-items: center; gap: 10px;
    white-space: nowrap;
}
.start-battle-btn:hover:not(:disabled) { transform: translateY(-1px); filter: brightness(1.08); }
.start-battle-btn:disabled { opacity: 0.48; cursor: not-allowed; filter: grayscale(1); }
.bbar-clear {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    color: #94a3b8;
    background: rgba(255,255,255,0.06);
    font-size: .95rem;
}

/* Modal Battle */
#battle-modal {
    position: fixed; inset: 0; background: rgba(2,6,23,0.82);
    backdrop-filter: blur(10px);
    z-index: 10001; display: none; align-items: center; justify-content: center;
    padding: 20px;
}
.battle-container {
    background: #0f172a; width: 100%; max-width: 980px; max-height: 90vh;
    border-radius: 18px; border: 1px solid rgba(148, 163, 184, 0.18);
    overflow: hidden; display: flex; flex-direction: column;
    box-shadow: 0 32px 90px rgba(0,0,0,.55);
}
.battle-header {
    background: rgba(255,255,255,0.035);
    padding: 18px 22px;
    color: white;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.battle-title {
    display: flex;
    flex-direction: column;
    gap: 3px;
    font-weight: 800;
    font-size: 1.1rem;
}
.battle-title small {
    color: #94a3b8;
    font-size: .78rem;
    font-weight: 500;
}
.battle-close {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.08);
    color: #cbd5e1;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.battle-close:hover { color: #fff; background: rgba(239,68,68,.7); }
.battle-content { flex: 1; overflow-y: auto; padding: 26px; color: #e2e8f0; }
.battle-content table { width: 100%; border-collapse: collapse; margin: 20px 0; background: rgba(255,255,255,0.025); border-radius: 12px; overflow: hidden; }
.battle-content th, .battle-content td { padding: 13px 15px; border: 1px solid rgba(255,255,255,0.08); text-align: left; }
.battle-content th { background: rgba(59, 130, 246, 0.12); color: #bfdbfe; }

.verdict-box {
    background: rgba(16, 185, 129, 0.09);
    border: 1px solid rgba(16, 185, 129, 0.35); padding: 18px; border-radius: 12px; margin-top: 20px;
}

@media (max-width: 760px) {
    #battle-bar {
        grid-template-columns: 1fr auto;
    }
    .bbar-label {
        grid-column: 1 / -1;
    }
    .bbar-slots {
        grid-column: 1 / -1;
    }
    .start-battle-btn {
        justify-content: center;
    }
}
</style>
</head>
<body class="page-anim home-page">

<nav>
    <div class="logo">
        <i class="fa-solid fa-shapes"></i>
        Freela<span>Skill</span>
    </div>
    <ul class="nav-links">
        <li><a href="#">Accueil</a></li>
        <li><a href="#">Missions</a></li>
        <li><a href="#" class="active">Marketplace</a></li>
        <li><a href="#">Freelancers</a></li>
    </ul>
    <div class="nav-right">
        
        <div class="theme-toggle-btn" style="cursor: pointer; margin-right: 15px; font-size: 1.2rem; color: var(--text-muted); display: flex; align-items: center;" title="Basculer le thème">
            <i class="fa-regular fa-moon"></i>
        </div>
        <a href="notifications.php" class="cart-btn" style="position: relative; margin-right: 10px;">
            <i class="fa-solid fa-bell"></i>
            <?php if($unreadCount > 0): ?>
                <span class="cart-count" style="position:absolute;top:-6px;right:-6px;background:#3b82f6;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <a href="panier.php" class="cart-btn" style="position: relative;">
            <i class="fa-solid fa-bag-shopping"></i> Panier
            <span class="cart-count" style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:white;border-radius:50%;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:2px solid var(--bg-dark);">0</span>
        </a>
        <div class="nav-avatar">AH</div>
    </div>
</nav>

<div class="marketplace-layout">

    <aside class="mkt-sidebar">

        <div class="mkt-profile-card">
            <div class="mkt-profile-header">
                <div class="mkt-avatar"><i class="fa-solid fa-store"></i></div>
                <div class="mkt-profile-name">Marketplace</div>
                <div class="mkt-profile-sub">FreelaSkill Tunisia</div>
            </div>
            <div class="mkt-profile-stats">
                <div class="mkt-stat">
                    <div class="mkt-stat-val"><?= $totalProduitCount ?></div>
                    <div class="mkt-stat-label">Produits</div>
                </div>
                <div class="mkt-stat">
                    <div class="mkt-stat-val"><?= $totalCategoryCount ?></div>
                    <div class="mkt-stat-label">Catégories</div>
                </div>
            </div>
        </div>

        <div class="mkt-sidebar-card">

            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Navigation</div>
                <a href="home.php" class="nav-item active">
                    <i class="fa-solid fa-store"></i> Tout parcourir
                </a>
                <a href="panier.php" class="nav-item">
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

            <!-- Catégories -->
            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Catégorie</div>
                <a href="home.php" class="filter-option <?= !$selectedCategoryId ? 'active' : '' ?>" style="text-decoration:none; color:inherit;">
                    <span>Tous les produits</span>
                    <span class="filter-count"><?= count($produitController->getAllFrontData()) ?></span>
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="?category=<?= $category['idCategory'] ?>" class="filter-option <?= $selectedCategoryId == $category['idCategory'] ? 'active' : '' ?>" style="text-decoration:none; color:inherit;">
                        <span><?= htmlspecialchars($category['nom']) ?></span>
                        <span class="filter-count"><?= $categoryCounts[$category['idCategory']] ?? 0 ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="mkt-sidebar-section filter-section">
                <div class="mkt-nav-label filter-title">Disponibilité</div>
                <div class="filter-option active" data-filter="all">
                    <span>Tous</span>
                </div>
                <div class="filter-option" data-filter="Disponible maintenant">
                    <span style="display:flex;align-items:center;gap:0.5rem;"><i class="fa-solid fa-circle-check" style="color:#10b981;"></i> Disponible maintenant</span>
                </div>
                <div class="filter-option" data-filter="Dans 2 semaines">
                    <span style="display:flex;align-items:center;gap:0.5rem;"><i class="fa-solid fa-clock" style="color:#f59e0b;"></i> Dans 2 semaines</span>
                </div>
                <div class="filter-option" data-filter="Dans 1 mois">
                    <span style="display:flex;align-items:center;gap:0.5rem;"><i class="fa-solid fa-clock" style="color:#f59e0b;"></i> Dans 1 mois</span>
                </div>
                <div class="filter-option" data-filter="Non disponible">
                    <span style="display:flex;align-items:center;gap:0.5rem;"><i class="fa-solid fa-circle-xmark" style="color:#ef4444;"></i> Non disponible</span>
                </div>
            </div>

            <div class="mkt-sidebar-section">
                <div class="mkt-nav-label">Prix (DT)</div>
                <div class="price-range">
                    <div class="price-inputs">
                        <input class="price-input" type="number" placeholder="Min" value="0">
                        <span class="price-sep">—</span>
                        <input class="price-input" type="number" placeholder="Max" value="1000000">
                    </div>
                    <input type="range" min="0" max="1000000" value="1000000">
                </div>
            </div>

        </div>

    </aside>

    <div class="mkt-main">

        <section class="hero-banner" style="padding: 2rem 2rem 3rem;">
            <div class="hero-glow"></div>
            <div class="hero-glow-2"></div>
            <div class="hero-content" style="max-width:750px; margin: 0 auto; text-align: center; display: flex; flex-direction: column; align-items: center;">
                <div class="hero-tag"><i class="fa-solid fa-bolt"></i> Marketplace Tunisia</div>
                <h1 class="hero-title">Trouvez les outils<br>qu'il vous <span>faut</span></h1>
                <p class="hero-sub">Équipements tech, licences logiciels, accessoires créatifs — livrés partout en Tunisie.</p>
                <div class="search-container" style="justify-content: center; width: 100%;">
                    <div class="search-wrap" style="flex: 1; max-width: 500px; position: relative;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="main-search-input" placeholder="Rechercher un produit, une marque…" style="padding-right: 40px;">
                        <button id="visual-search-btn" title="Rechercher par image" style="position: absolute; right: 12px; background: transparent; border: none; color: #94a3b8; cursor: pointer; font-size: 1.1rem; transition: color 0.2s; z-index: 10;">
                            <i class="fa-solid fa-camera"></i>
                        </button>
                        <input type="file" id="visual-search-file" accept="image/*" style="display: none;">
                    </div>
                    <button class="btn-search" id="main-search-btn"><i class="fa-solid fa-search"></i> Rechercher</button>
                </div>
                <div class="action-row" style="display:flex; align-items:center; justify-content: center; gap:.75rem; margin-top:1.25rem; width: 100%;">
                    <span style="color:#475569; font-size:.82rem;">
                        Vous vendez ? <strong style="color:#94A3B8; font-weight:500;">Déposez votre annonce gratuitement</strong>
                    </span>
                    <a href="vendreproduit.php" style="display:inline-flex; align-items:center; gap:6px; background:transparent; color:#94A3B8; border:1px solid rgba(255,255,255,0.1); padding:8px 16px; border-radius:10px; font-size:.82rem; font-weight:500; white-space:nowrap; text-decoration:none; transition:background .2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='transparent'">
                        + Vendre un produit
                    </a>
                </div>
            </div>
        </section>

        <div class="products-toolbar">
            <p class="result-count"><strong><?= $totalProduitCount ?> produits</strong> trouvés</p>
            <div class="toolbar-right">
                <select class="sort-select">
                    <option>Trier : Pertinence</option>
                    <option>Prix croissant</option>
                    <option>Prix décroissant</option>
                    <option>Nouveautés</option>
                </select>
                <div class="view-toggle">
                    <button class="view-btn active" title="Grille"><i class="fa-solid fa-grip"></i></button>
                    <button class="view-btn" title="Liste"><i class="fa-solid fa-list"></i></button>
                </div>
            </div>
        </div>

        <div class="active-filters">
            <div class="chip">Tous les produits <button><i class="fa-solid fa-xmark"></i></button></div>
            <div class="chip">En stock <button><i class="fa-solid fa-xmark"></i></button></div>
        </div>

        <div class="products-grid">
            <?php if (empty($produits)): ?>
                <div class="product-card" style="opacity:.9;width:100%;text-align:center;padding:3rem 2rem;grid-column:1/-1;">
                    <div class="card-body">
                        <div class="card-title">Aucun produit pour le moment</div>
                        <p style="color:var(--text-muted);margin-top:1rem;">Ajoutez un produit depuis « Vendre un produit ».</p>
                        <a href="vendreproduit.php" class="btn btn-primary" style="margin-top:1.5rem;"><i class="fa-solid fa-plus"></i> Vendre un produit</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($produitsPagines as $produit):
                    $catName   = $categoryNames[$produit['category_id']] ?? 'Autre';
                    $dispoValue = $produit['disponibilite'] ?? 'Disponible maintenant';
                    $stockCount = (int)($produit['stock'] ?? 0);
                    
                    if ($stockCount <= 0) {
                        $stockClass = 'out-stock';
                        $stockText = 'Rupture de stock';
                    } elseif ($dispoValue === 'Disponible maintenant') {
                        $stockClass = 'in-stock';
                        $stockText = 'Dispo. maintenant';
                    } elseif ($dispoValue === 'Non disponible') {
                        $stockClass = 'out-stock';
                        $stockText = 'Non disponible';
                    } else {
                        $stockClass = 'low-stock';
                        $stockText = $dispoValue;
                    }
                    
                    $opStyle    = $dispoValue === 'Non disponible' ? 'opacity:0.6;' : '';
                    $priceStr   = number_format($produit['prix'], 0, ',', ' ');
                    $desc       = htmlspecialchars(mb_strimwidth($produit['description'], 0, 70, 'â€¦'));
                ?>
                    <div class="product-card" data-id="<?= $produit['idProduit'] ?>" data-dispo="<?= htmlspecialchars($dispoValue) ?>" style="<?= $opStyle ?>">
                        <div class="card-image">
                            <div class="card-compare-overlay">
                                <button class="card-compare-btn" onclick="toggleBattle(<?= $produit['idProduit'] ?>, '<?= addslashes($produit['nom']) ?>', '<?= $produit['image'] ?>', this.closest('.product-card'))">
                                    <i class="fa-solid fa-scale-balanced"></i> Comparer
                                </button>
                            </div>
                            <?php if (!empty($produit['image'])): ?>
                                <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:1rem 1rem 0 0;">
                            <?php else: ?>
                                <span style="font-size:3rem;">🛍️</span>
                            <?php endif; ?>
                            <?php if ($dispoValue === 'Dans 2 semaines' || $dispoValue === 'Dans 1 mois'): ?>
                                <span class="card-badge badge-popular" style="background:rgba(245, 158, 11, 0.9);">PRÉCOMMANDE</span>
                            <?php elseif ($dispoValue === 'Non disponible'): ?>
                                <span class="card-badge badge-out" style="background:rgba(239,68,68,0.9);">INDISPONIBLE</span>
                            <?php endif; ?>
                            <button class="wishlist-btn"><i class="fa-regular fa-heart"></i></button>
                        </div>
                        <div class="card-body">
                            <div class="card-category"><?= htmlspecialchars($catName) ?></div>
                            <div class="card-title"><?= htmlspecialchars($produit['nom']) ?></div>
                            <div class="card-rating" style="margin-bottom:.8rem;">
                                <span class="stars" style="color:#f59e0b;">★★★★★</span>
                                <span class="rating-text" style="opacity:.8;font-size:.9rem;"><?= $desc ?></span>
                            </div>
                            <div class="card-footer">
                                <div class="price-block">
                                    <span class="price-main"><?= $priceStr ?></span>
                                    <span class="price-currency">DT</span>
                                </div>
                                <div class="stock-info <?= $stockClass ?>"><span class="stock-dot"></span><?= $stockText ?></div>
                            </div>
                            <?php if ($dispoValue === 'Non disponible' || (int)($produit['stock'] ?? 0) <= 0): ?>
                                <button class="btn-cart" disabled><i class="fa-solid fa-ban"></i> Indisponible</button>
                            <?php else: ?>
                                <button class="btn-cart"><i class="fa-solid fa-cart-plus"></i> Ajouter au panier</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination" style="margin-top:3rem;">
            <a href="?page=<?= max(1,$currentPage-1) ?>" class="pag-btn <?= $currentPage<=1?'disabled':'' ?>" <?= $currentPage<=1?'onclick="return false;"':'' ?>>
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <?php
            $start = max(1, $currentPage-2);
            $end   = min($totalPages, $start+4);
            if ($start > 1): ?>
                <a href="?page=1" class="pag-btn">1</a>
                <?php if ($start > 2): ?><span class="pag-btn" style="pointer-events:none;">â€¦</span><?php endif; ?>
            <?php endif; ?>
            <?php for ($i=$start;$i<=$end;$i++): ?>
                <a href="?page=<?= $i ?>" class="pag-btn <?= $i===$currentPage?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages-1): ?><span class="pag-btn" style="pointer-events:none;">…</span><?php endif; ?>
                <a href="?page=<?= $totalPages ?>" class="pag-btn"><?= $totalPages ?></a>
            <?php endif; ?>
            <a href="?page=<?= min($totalPages,$currentPage+1) ?>" class="pag-btn <?= $currentPage>=$totalPages?'disabled':'' ?>" <?= $currentPage>=$totalPages?'onclick="return false;"':'' ?>>
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
        <?php endif; ?>

    </div>
</div>

<script src="../assets/js.js?v=7"></script>

<button id="aria-fab" onclick="toggleAria()" title="Conseiller IA">
    <i class="fa-solid fa-robot"></i>
    <span class="aria-badge">AI</span>
</button>

<div id="aria-panel">
    <div class="aria-header">
        <div class="aria-avatar"><i class="fa-solid fa-robot"></i></div>
        <div class="aria-header-info">
            <div class="aria-name">ARIA</div>
            <div class="aria-status">
                <div class="aria-status-dot"></div>
                Conseillère IA · En ligne
            </div>
        </div>
        <button class="aria-close-btn" onclick="toggleAria()" title="Fermer">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Quick suggestions -->
    <div class="aria-suggestions" id="aria-chips">
        <span class="aria-chip" onclick="sendChip(this)"><i class="fa-solid fa-wallet"></i> Budget 200 DT</span>
        <span class="aria-chip" onclick="sendChip(this)"><i class="fa-solid fa-laptop"></i> Meilleur laptop</span>
        <span class="aria-chip" onclick="sendChip(this)"><i class="fa-solid fa-gamepad"></i> Gaming</span>
        <span class="aria-chip" onclick="sendChip(this)"><i class="fa-solid fa-star"></i> Rapport qualité/prix</span>
    </div>

    <div class="aria-messages" id="aria-messages">
        <div class="msg-bubble aria-msg">
            <div class="msg-content">
                <div class="aria-intro">
                    <div class="aria-intro-icon"><i class="fa-solid fa-sparkles" style="color:var(--tech-blue)"></i></div>
                    <div class="aria-intro-title">Bonjour ! Je suis ARIA</div>
                    <div class="aria-intro-sub">
                        Votre conseillère shopping IA.<br>
                        Dites-moi votre <strong>budget</strong> et votre <strong>besoin</strong>,
                        je trouve le produit parfait pour vous.
                    </div>
                </div>
            </div>
            <div class="msg-time">Maintenant</div>
        </div>
    </div>
    <div class="aria-input-area">
        <textarea
            id="aria-input"
            class="aria-input"
            placeholder="Ex : J'ai 500 DT, cherche un laptop..."
            rows="1"
            onkeydown="handleAriaKey(event)"
            oninput="autoResize(this)"
        ></textarea>
        <button class="aria-send-btn" id="aria-send-btn" onclick="sendAriaMessage()"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
</div>

<div id="battle-bar">
    <div class="bbar-label">
        <i class="fa-solid fa-scale-balanced"></i>
        <div>Comparateur IA<span>Sélectionnez 2 à 4 produits</span></div>
    </div>
    <div class="bbar-slots">
        <!-- Slots générés en JS -->
    </div>
    <button class="start-battle-btn" id="bbar-launch" disabled onclick="startTheBattle()">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Lancer l'analyse
    </button>
    <button class="bbar-clear" onclick="clearBattle()" title="Vider la sélection"><i class="fa-solid fa-trash"></i></button>
</div>

<div id="battle-modal">
    <div class="battle-container">
        <div class="battle-header">
            <div class="battle-title">
                <span>Analyse comparative</span>
                <small>Classement, points forts et meilleur choix selon le besoin</small>
            </div>
            <button class="battle-close" onclick="closeBattleModal()" title="Fermer"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="battle-content" id="battle-result"></div>
    </div>
</div>

<script>
let battleSelection = [];

function toggleBattle(id, nom, img, cardEl) {
    const idx = battleSelection.findIndex(p => p.id === id);
    if (idx > -1) {
        battleSelection.splice(idx, 1);
        cardEl.classList.remove('battle-selected');
        const btn = cardEl.querySelector('.card-compare-btn');
        if(btn) btn.innerHTML = '<i class="fa-solid fa-scale-balanced"></i> Comparer';
    } else {
        if (battleSelection.length >= 4) {
            alert("Limite de 4 produits atteinte pour une analyse optimale !");
            return;
        }
        battleSelection.push({id, nom, img});
        cardEl.classList.add('battle-selected');
        const btn = cardEl.querySelector('.card-compare-btn');
        if(btn) btn.innerHTML = '<i class="fa-solid fa-check"></i> Ajouté';
    }
    updateBattleBar();
}

function updateBattleBar() {
    const bar = document.getElementById('battle-bar');
    const slotsContainer = document.querySelector('.bbar-slots');
    
    if (battleSelection.length > 0) bar.classList.add('active');
    else bar.classList.remove('active');

    let html = '';
    battleSelection.forEach((p, index) => {
        html += `
            <div class="bbar-slot">
                <div class="bbar-slot-img">
                    ${p.img ? `<img src="${p.img}" alt="">` : '<i class="fa-solid fa-box"></i>'}
                </div>
                <div class="bbar-slot-name">${p.nom}</div>
                <button class="bbar-remove" onclick="removeSpecific(${p.id})" title="Retirer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            ${index < battleSelection.length - 1 ? '<div class="bbar-vs">VS</div>' : ''}
        `;
    });

    if (battleSelection.length < 2) {
        html += `
            <div class="bbar-vs">VS</div>
            <div class="bbar-slot placeholder">
                <div class="bbar-slot-img"><i class="fa-solid fa-plus"></i></div>
                <div class="bbar-slot-name">Ajouter un produit</div>
            </div>
        `;
    }

    slotsContainer.innerHTML = html;
    
    document.getElementById('bbar-launch').disabled = (battleSelection.length < 2);
}

function removeSpecific(id) {
    const card = document.querySelector(`.product-card[data-id="${id}"]`);
    if (card) {
        toggleBattle(id, '', '', card);
        return;
    }

    battleSelection = battleSelection.filter(p => p.id !== id);
    updateBattleBar();
}

function clearBattle() {
    battleSelection = [];
    document.querySelectorAll('.product-card.battle-selected').forEach(c => {
        c.classList.remove('battle-selected');
        const btn = c.querySelector('.card-compare-btn');
        if(btn) btn.innerHTML = '<i class="fa-solid fa-scale-balanced"></i> Comparer';
    });
    updateBattleBar();
}

async function startTheBattle() {
    const modal = document.getElementById('battle-modal');
    const resultBox = document.getElementById('battle-result');
    modal.style.display = 'flex';
    resultBox.innerHTML = `<div style="text-align:center;padding:60px 20px;"><div class="aria-typing" style="margin:0 auto 20px;"><span></span><span></span><span></span></div><div style="color:var(--tech-blue);font-weight:700;font-size:1.1rem;">Analyse IA en cours...</div><div style="color:#64748b;margin-top:8px;font-size:.88rem;">Comparaison des prix, disponibilités et caractéristiques</div></div>`;
    try {
        const res = await fetch('../../api/ai_battle.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                ids: battleSelection.map(p => p.id)
            })
        });
        const data = await res.json();
        if (data.error) {
            resultBox.innerHTML = `<div style="padding:30px;text-align:center;color:#ef4444;">${data.error}</div>`;
        } else {
            resultBox.style.opacity = 0;
            resultBox.innerHTML = data.battle_report;
            setTimeout(() => { resultBox.style.transition='opacity .5s'; resultBox.style.opacity=1; }, 80);
        }
    } catch(e) {
        resultBox.innerHTML = `<div style="padding:30px;text-align:center;color:#ef4444;">âš ï¸ Erreur de connexion.</div>`;
    }
}

function closeBattleModal() {
    document.getElementById('battle-modal').style.display = 'none';
}

// Click on card to add to battle
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.product-card').forEach(card => {
        const compareBtn = card.querySelector('.card-compare-btn');
        if (compareBtn) {
            compareBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const id  = parseInt(card.dataset.id);
                const nom = card.querySelector('.card-title')?.textContent || '';
                const img = card.querySelector('.card-image img')?.src || '';
                return;
            });
        }
    });
});

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   ARIA AI ADVISOR
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function toggleAria() {
    const panel = document.getElementById('aria-panel');
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) setTimeout(() => document.getElementById('aria-input').focus(), 300);
}
function autoResize(el) { el.style.height='auto'; el.style.height=Math.min(el.scrollHeight,100)+'px'; }
function handleAriaKey(e) { if (e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendAriaMessage();} }
function sendChip(el) {
    document.getElementById('aria-input').value = el.textContent.replace(/^[^\w\d]+/,'').trim();
    document.getElementById('aria-chips').style.display='none';
    sendAriaMessage();
}
function getTime() { return new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'}); }
function escHtml(t) { return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function addMsg(role, html) {
    const box = document.getElementById('aria-messages');
    const div = document.createElement('div');
    div.className = `msg-bubble ${role}-msg`;
    div.innerHTML = `<div class="msg-content">${html}</div><div class="msg-time">${getTime()}${role==='aria'?' Â· ARIA':''}</div>`;
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
    return div;
}

function showTyping() {
    const box = document.getElementById('aria-messages');
    const div = document.createElement('div');
    div.className = 'msg-bubble aria-msg'; div.id='aria-typing-bubble';
    div.innerHTML = `<div class="aria-typing"><span></span><span></span><span></span></div>`;
    box.appendChild(div); box.scrollTop=box.scrollHeight;
}
function hideTyping() { document.getElementById('aria-typing-bubble')?.remove(); }

function buildProductCards(products) {
    if (!products?.length) return '';
    return '<div class="aria-product-cards">' + products.map(p => {
        const img = p.image ? `<img src="${escHtml(p.image)}" alt="${escHtml(p.nom)}">` : `<i class="fa-solid fa-box" style="font-size:1.4rem;color:var(--tech-blue)"></i>`;
        const price = parseFloat(p.prix).toLocaleString('fr-TN',{minimumFractionDigits:0});
        return `<a href="detailproduit.php?id=${p.idProduit}" class="aria-prod-card" target="_blank">
            <div class="aria-prod-img">${img}</div>
            <div class="aria-prod-info"><div class="aria-prod-name">${escHtml(p.nom)}</div><div class="aria-prod-cat">${escHtml(p.categorie||'Produit')}</div></div>
            <div class="aria-prod-price">${price} DT</div>
        </a>`;
    }).join('') + '</div>';
}

async function sendAriaMessage() {
    const inputEl = document.getElementById('aria-input');
    const sendBtn = document.getElementById('aria-send-btn');
    const question = inputEl.value.trim();
    if (!question) return;
    inputEl.value=''; inputEl.style.height='auto'; sendBtn.disabled=true;
    document.getElementById('aria-chips').style.display='none';
    addMsg('user', escHtml(question));
    showTyping();
    try {
        const res = await fetch('../../api/ai_advisor.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({question})
        });
        if (!res.ok) throw new Error('HTTP '+res.status);
        const data = await res.json();
        hideTyping();
        if (data.error) {
            addMsg('aria', `<span style="color:#ef4444">âš ï¸ ${escHtml(data.error)}</span>`);
        } else {
            let formatted = escHtml(data.reply)
                .replace(/\*\*(.*?)\*\*/g,'<strong style="color:var(--tech-blue)">$1</strong>')
                .replace(/\n/g,'<br>');
            addMsg('aria', formatted + buildProductCards(data.products||[]));
        }
    } catch(err) {
        hideTyping();
        addMsg('aria', `<span style="color:#ef4444">âš ï¸ Connexion impossible.</span>`);
    } finally { sendBtn.disabled=false; inputEl.focus(); }
}
</script>
</body>
</html>
