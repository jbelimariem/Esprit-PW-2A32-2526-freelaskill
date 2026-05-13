<?php
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div style="padding: 0 0.5rem; margin-bottom: 2rem;">
        <div class="logo">
            <i class="fa-solid fa-shapes" style="color: #3b82f6;"></i>
            Freela<span>Skill</span>
        </div>
        <p style="font-size: 0.75rem; color: #475569; margin-top: 0.5rem; letter-spacing: 1px;">Admin Control v1.0</p>
    </div>
    
    <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/users_dashboard.php" class="nav-item <?php echo $currentFile === 'users_dashboard.view.php' || $currentFile === 'users_dashboard.php' ? 'active' : ''; ?>" style="text-decoration:none;"><i class="fa-solid fa-users-viewfinder"></i> Gestion Users</a>
    <div class="nav-item"><i class="fa-solid fa-network-wired"></i> Flux de Missions</div>
    
    <?php $isMarketplaceActive = in_array($currentFile, ['dashboard.php', 'produits.php', 'mes_achats.php', 'pending_products.php', 'ajouter_produit.php', 'liste_categories.php', 'ajouter_categorie.php', 'liste_commandes.php']); ?>
    <div class="nav-item-wrapper <?php echo $isMarketplaceActive ? 'open' : ''; ?>">
        <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/dashboard.php" class="nav-item <?php echo $isMarketplaceActive ? 'active' : ''; ?>" style="text-decoration:none;">
            <i class="fa-solid fa-store"></i> Marketplace
            <i class="fa-solid fa-chevron-right" style="margin-left:auto; font-size:0.7rem; opacity:0.5;"></i>
        </a>
        <div class="submenu">
            <div class="submenu-title">Marketplace Admin</div>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/dashboard.php" class="submenu-item">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/produits.php" class="submenu-item">
                <i class="fa-solid fa-box"></i> Gestion Produits
            </a>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/mes_achats.php" class="submenu-item">
                <i class="fa-solid fa-user-tag"></i> Mes produits admin
            </a>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/pending_products.php" class="submenu-item">
                <i class="fa-solid fa-clock"></i> Validation Produits
            </a>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/ajouter_produit.php" class="submenu-item">
                <i class="fa-solid fa-plus"></i> Ajouter Produit
            </a>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/liste_categories.php" class="submenu-item">
                <i class="fa-solid fa-list"></i> Liste Catégories
            </a>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/ajouter_categorie.php" class="submenu-item">
                <i class="fa-solid fa-folder-plus"></i> Ajouter Catégorie
            </a>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/liste_commandes.php" class="submenu-item">
                <i class="fa-solid fa-cart-shopping"></i> Commandes
            </a>
        </div>
    </div>

    <?php $isContratsActive = (strpos($currentFile, 'contrat') !== false || strpos($currentFile, 'rules') !== false); ?>
    <div class="nav-item-wrapper <?php echo $isContratsActive ? 'open' : ''; ?>">
        <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/admin_contrat_list.php" class="nav-item <?php echo $isContratsActive ? 'active' : ''; ?>" style="text-decoration:none;">
            <i class="fa-solid fa-file-contract"></i> Contrats
            <i class="fa-solid fa-chevron-right" style="margin-left:auto; font-size:0.7rem; opacity:0.5;"></i>
        </a>
        <div class="submenu">
            <div class="submenu-title">Gestion Légale</div>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/admin_contrat_list.php" class="submenu-item <?php echo $currentFile === 'admin_contrat_list.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-list-check"></i> Liste des contrats
            </a>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/admin_contrat_form.php" class="submenu-item <?php echo $currentFile === 'admin_contrat_form.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-file-signature"></i> Nouveau contrat
            </a>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/admin_rules_list.php" class="submenu-item <?php echo $currentFile === 'admin_rules_list.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-gavel"></i> Liste des règles
            </a>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Backoffice/admin_rules_form.php" class="submenu-item <?php echo $currentFile === 'admin_rules_form.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-plus-circle"></i> Nouvelle règle
            </a>
        </div>
    </div>
    <div class="nav-item"><i class="fa-solid fa-shield-halved"></i> Securite</div>
    <a href="/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin" class="nav-item <?php echo $currentFile === 'messagerie_index.php' ? 'active' : ''; ?>" style="text-decoration:none;">
        <i class="fa-solid fa-comments"></i> Messagerie
    </a>

    <div style="margin-top: auto; padding-top: 2rem;">
        <a href="/Esprit-PW-2A32-2526-TalentBridge-job/Views/Frontoffice/home.php" class="btn btn-outline"
           style="width:100%;font-size:.85rem;padding:.75rem;border-radius:999px;display:flex;align-items:center;justify-content:center;gap:.5rem; color: #ef4444; border-color: rgba(239,68,68,0.2);text-decoration:none;">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Retour au Hub
        </a>
    </div>
</aside>
