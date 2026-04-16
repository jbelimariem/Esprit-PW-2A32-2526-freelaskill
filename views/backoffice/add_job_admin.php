<?php
// views/backoffice/add_job_admin.php — Admin: Ajouter une offre
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/JobOffer.php';

$errors  = [];
$data    = ['titre'=>'','description'=>'','competences'=>'','budget'=>'','delai'=>'','statut'=>'pending'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titre'       => trim($_POST['titre']       ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'competences' => trim($_POST['competences'] ?? ''),
        'budget'      => trim($_POST['budget']       ?? ''),
        'delai'       => trim($_POST['delai']        ?? ''),
        'statut'      => trim($_POST['statut']       ?? 'pending'),
        'client_id'   => 1
    ];

    if (empty($data['titre']))                    { $errors['titre']       = "Le titre est obligatoire."; }
    elseif (strlen($data['titre']) < 5)           { $errors['titre']       = "Min. 5 caractères."; }
    elseif (strlen($data['titre']) > 255)         { $errors['titre']       = "Max. 255 caractères."; }
    if (empty($data['description']))              { $errors['description'] = "La description est obligatoire."; }
    elseif (strlen($data['description']) < 20)    { $errors['description'] = "Min. 20 caractères."; }
    if (empty($data['competences']))              { $errors['competences'] = "Champ obligatoire."; }
    if (empty($data['budget']) || !is_numeric($data['budget'])) { $errors['budget'] = "Budget numérique requis."; }
    elseif ((float)$data['budget'] <= 0)          { $errors['budget']      = "Budget > 0 requis."; }
    if (empty($data['delai']))                    { $errors['delai']       = "Champ obligatoire."; }

    if (empty($errors)) {
        $model    = new JobOffer();
        $insertId = $model->create($data);
        $model->updateStatut($insertId, $data['statut']);
        header('Location: dashboard.php?success=added'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une mission | Admin</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <!-- Theme CSS -->
    <link rel="stylesheet" href="admin.css?v=<?= time() ?>">
</head>
<body class="page-anim">
    
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>

    <div class="admin-layout">
        
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <div class="logo">
                <i class="fa-solid fa-shapes"></i>
                Freela<span>Skill</span>
            </div>
            
            <nav class="admin-nav">
                <div style="margin: 0.5rem 0 0.5rem 1rem; font-size: 0.7rem; text-transform: uppercase; color: #475569; font-weight: 700; letter-spacing: 1px;">Menu Principal</div>
                <a href="dashboard.php" class="admin-nav-item">
                    <i class="fa-solid fa-briefcase"></i> Gestion des Missions
                </a>

                <div style="margin: 1.5rem 0 0.5rem 1rem; font-size: 0.7rem; text-transform: uppercase; color: #475569; font-weight: 700; letter-spacing: 1px;">Actions</div>
                <a href="add_job_admin.php" class="admin-nav-item active">
                    <i class="fa-solid fa-plus-circle"></i> Ajouter une Offre
                </a>

            </nav>
        </aside>

        <!-- MAIN AREA -->
        <main class="admin-main">
            <!-- TOPBAR -->
            <header class="admin-topbar">
                <div style="color: var(--text-muted); font-size: 0.9rem;">
                    Back-office / Missions / <span style="color: white;">Ajouter</span>
                </div>
                <div class="admin-top-actions">
                    <div class="admin-icon-btn"><i class="fa-regular fa-bell"></i></div>
                    <div class="nav-avatar">AH</div>
                </div>
            </header>

            <!-- CONTENT -->
            <div class="admin-content" style="max-width: 900px;">
                <div class="admin-header-row">
                    <div>
                        <h1 class="admin-page-title">Créer une <span>Nouvelle Mission</span></h1>
                        <p style="color: var(--text-muted); margin-top: 0.5rem;">Remplissez les détails ci-dessous pour publier une offre.</p>
                    </div>
                </div>

                <div class="glass-card">
                    <form action="add_job_admin.php" method="POST" id="add-form">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <!-- Titre -->
                            <div style="grid-column: span 2;">
                                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-light); font-size: 0.85rem;">Titre de la mission</label>
                                <input type="text" name="titre" value="<?= htmlspecialchars($data['titre']) ?>" 
                                       style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem; color: white;" 
                                       placeholder="Ex: Développeur React Expert">
                                <?php if(isset($errors['titre'])): ?><small style="color: var(--tunisian-red);"><?= $errors['titre'] ?></small><?php endif; ?>
                            </div>

                            <!-- Budget -->
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-light); font-size: 0.85rem;">Budget (DT)</label>
                                <input type="text" name="budget" value="<?= htmlspecialchars($data['budget']) ?>" 
                                       style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem; color: white;">
                                <?php if(isset($errors['budget'])): ?><small style="color: var(--tunisian-red);"><?= $errors['budget'] ?></small><?php endif; ?>
                            </div>

                            <!-- Délai -->
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-light); font-size: 0.85rem;">Délai estimé</label>
                                <select name="delai" style="width: 100%; background: rgba(2,6,23,0.9); border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem; color: white;">
                                    <option value="">Sélectionnez</option>
                                    <?php foreach (["1 semaine","2 semaines","1 mois","2 mois","3 mois","6 mois"] as $d): ?>
                                        <option value="<?= $d ?>" <?= $data['delai']===$d?'selected':'' ?>><?= $d ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if(isset($errors['delai'])): ?><small style="color: var(--tunisian-red);"><?= $errors['delai'] ?></small><?php endif; ?>
                            </div>

                            <!-- Compétences -->
                            <div style="grid-column: span 2;">
                                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-light); font-size: 0.85rem;">Compétences (virgules)</label>
                                <input type="text" name="competences" value="<?= htmlspecialchars($data['competences']) ?>" 
                                       style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem; color: white;" 
                                       placeholder="PHP, MySQL, Design...">
                                <?php if(isset($errors['competences'])): ?><small style="color: var(--tunisian-red);"><?= $errors['competences'] ?></small><?php endif; ?>
                            </div>

                            <!-- Description -->
                            <div style="grid-column: span 2;">
                                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-light); font-size: 0.85rem;">Description</label>
                                <textarea name="description" rows="5" 
                                          style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem; color: white;"><?= htmlspecialchars($data['description']) ?></textarea>
                                <?php if(isset($errors['description'])): ?><small style="color: var(--tunisian-red);"><?= $errors['description'] ?></small><?php endif; ?>
                            </div>

                            <!-- Statut -->
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-light); font-size: 0.85rem;">Statut initial</label>
                                <select name="statut" style="width: 100%; background: rgba(2,6,23,0.9); border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem; color: white;">
                                    <option value="pending" <?= $data['statut']==='pending'?'selected':'' ?>>En attente</option>
                                    <option value="approved" <?= $data['statut']==='approved'?'selected':'' ?>>Approuvée</option>
                                    <option value="rejected" <?= $data['statut']==='rejected'?'selected':'' ?>>Rejetée</option>
                                </select>
                            </div>
                        </div>

                        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">Publier l'offre</button>
                            <a href="dashboard.php" class="btn btn-outline">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
