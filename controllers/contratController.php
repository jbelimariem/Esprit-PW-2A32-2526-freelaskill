<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/Contrat.php';
require_once __DIR__ . '/../Models/BadWordsService.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Models/NotificationRepository.php';
require_once __DIR__ . '/../Models/ContratVersion.php';
require_once __DIR__ . '/../Models/ContratVersionRepository.php';

// Buffer output pour permettre les redirections même si du HTML a été envoyé
if (!ob_get_level()) ob_start();

// createRule is defined in ruleController.php — include it if not already loaded
if (!function_exists('createRule')) {
    require_once __DIR__ . '/../Models/Rule.php';
    function createRule(array $data) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare(
            'INSERT INTO rules (titre, description, type, valeur, date_creation, statut, titre_contrat)
             VALUES (:titre, :description, :type, :valeur, NOW(), :statut, :titre_contrat)'
        );
        return $stmt->execute([
            'titre'         => $data['titre'],
            'description'   => $data['description'],
            'type'          => $data['type']          ?? 'Général',
            'valeur'        => $data['valeur']         ?? '',
            'statut'        => $data['statut']         ?? 'actif',
            'titre_contrat' => $data['titre_contrat']  ?? null,
        ]);
    }
}

// --- Fonctions de base de données ---

function getAllContrats($search = '', $sortBy = 'date_creation', $order = 'DESC') {
    $pdo = config::getConnexion();
    
    // Sécurisation des colonnes de tri
    $allowedSortColumns = ['titre', 'budget', 'delai', 'statut', 'date_creation'];
    $allowedOrders = ['ASC', 'DESC'];
    
    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'date_creation';
    }
    if (!in_array(strtoupper($order), $allowedOrders)) {
        $order = 'DESC';
    }

    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM contrat WHERE titre LIKE :search OR description LIKE :search ORDER BY $sortBy $order");
        $stmt->execute(['search' => '%' . $search . '%']);
    } else {
        $stmt = $pdo->query("SELECT * FROM contrat ORDER BY $sortBy $order");
    }
    
    return $stmt->fetchAll();
}

function getContratStatistics() {
    $pdo = config::getConnexion();
    
    $stats = [
        'total' => 0,
        'total_budget' => 0,
        'avg_budget' => 0,
        'max_budget' => 0,
        'min_budget' => 0,
        'by_status' => [
            'brouillon' => 0,
            'en_attente' => 0,
            'actif' => 0,
            'termine' => 0,
            'annule' => 0,
            'archive' => 0
        ]
    ];

    // Total contrats
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM contrat');
    $stats['total'] = $stmt->fetch()['total'];

    // Stats financières (Total, Moyen, Max, Min)
    $stmt = $pdo->query('SELECT SUM(budget) as total_budget, AVG(budget) as avg_budget, MAX(budget) as max_budget, MIN(budget) as min_budget FROM contrat');
    $fin = $stmt->fetch();
    $stats['total_budget'] = $fin['total_budget'] ?? 0;
    $stats['avg_budget'] = $fin['avg_budget'] ?? 0;
    $stats['max_budget'] = $fin['max_budget'] ?? 0;
    $stats['min_budget'] = $fin['min_budget'] ?? 0;

    // Répartition par statut
    $stmt = $pdo->query('SELECT statut, COUNT(*) as count FROM contrat GROUP BY statut');
    $statusData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Fusionner avec les clés par défaut pour s'assurer que toutes les clés existent
    foreach ($statusData as $st => $count) {
        $stats['by_status'][$st] = $count;
    }

    return $stats;
}

function getContratById(int $id) {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare('SELECT * FROM contrat WHERE id_contrat = :id');
    $stmt->execute(['id' => $id]);
    $contrat = $stmt->fetch();
    return $contrat === false ? null : $contrat;
}

function createContrat(array $data) {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare(
        'INSERT INTO contrat (titre, description, budget, delai, statut, date_creation, freelance_info,
         job_offer_id, job_application_id, signature_client, signature_freelance)
         VALUES (:titre, :description, :budget, :delai, :statut, NOW(), :freelance_info,
         :job_offer_id, :job_application_id, :signature_client, :signature_freelance)'
    );
    $success = $stmt->execute([
        'titre'              => $data['titre'],
        'description'        => $data['description'],
        'budget'             => $data['budget'],
        'delai'              => $data['delai'],
        'statut'             => $data['statut'],
        'freelance_info'     => $data['freelance_info'],
        'job_offer_id'       => $data['job_offer_id']       ?? null,
        'job_application_id' => $data['job_application_id'] ?? null,
        'signature_client'   => $data['signature_client'],
        'signature_freelance'=> $data['signature_freelance']
    ]);
    if ($success) {
        $newId = $pdo->lastInsertId();
        // ── Email : notification création contrat ──────────────────
        try {
            require_once __DIR__ . '/../Models/EmailService.php';
            $emailSvc = new EmailService($pdo);
            $contrat  = getContratById((int)$newId);
            if ($contrat) {
                // Récupérer l'email du freelancer depuis job_applications si disponible
                $freelancerEmail = '';
                $freelancerName  = 'Freelancer';
                if (!empty($data['job_application_id'])) {
                    $stmtApp = $pdo->prepare('SELECT email, name FROM job_applications WHERE id = :id');
                    $stmtApp->execute(['id' => $data['job_application_id']]);
                    $app = $stmtApp->fetch();
                    if ($app) {
                        $freelancerEmail = $app['email'];
                        $freelancerName  = $app['name'];
                    }
                }
                // Fallback : extraire depuis freelance_info
                if (!$freelancerEmail) {
                    preg_match('/[\w.+-]+@[\w-]+\.[a-z]{2,}/i', $data['freelance_info'] ?? '', $m);
                    $freelancerEmail = $m[0] ?? 'demo@freelaskill.com';
                    $freelancerName  = trim(preg_split('/[-–|,]/', $data['freelance_info'] ?? 'Freelancer')[0]);
                }
                $emailSvc->notifyContratCreated($contrat, $freelancerEmail, $freelancerName);
            }
        } catch (Exception $e) { /* silencieux */ }
        return $newId;
    }
    return false;
}

function updateContrat(int $id, array $data) {
    $pdo = config::getConnexion();

    // ── 1. Récupérer l'ancienne version avant modification ────────────
    $old = getContratById($id);

    if ($old) {
        // Sauvegarder un snapshot (version historique)
        $versionRepo  = new ContratVersionRepository($pdo);
        $nextVersion  = $versionRepo->getNextVersionNumber($id);
        $modifiePar   = $_SESSION['user_role'] ?? 'admin';
        $snapshot     = ContratVersion::fromContrat($old, $nextVersion, $modifiePar);
        $versionRepo->save($snapshot);

        // ── 2. Créer une notification si le statut a changé ──────────
        $newStatut = $data['statut'] ?? $old['statut'];
        if (isset($data['statut']) && $data['statut'] !== $old['statut']) {
            $notifRepo = new NotificationRepository($pdo);
            $notif     = new NotificationContrat(
                $id,
                $old['titre'],
                $old['statut'],
                $newStatut
            );
            $notifRepo->create($notif);

            // ── Email : notification changement de statut ──────────
            try {
                require_once __DIR__ . '/../Models/EmailService.php';
                $emailSvc = new EmailService($pdo);
                preg_match('/[\w.+-]+@[\w-]+\.[a-z]{2,}/i', $old['freelance_info'] ?? '', $m);
                $toEmail = $m[0] ?? 'demo@freelaskill.com';
                $toName  = trim(preg_split('/[-–|,]/', $old['freelance_info'] ?? 'Freelancer')[0]);
                $emailSvc->notifyStatutChange($old, $old['statut'], $newStatut, $toEmail, $toName);
            } catch (Exception $e) { /* silencieux */ }
        }
    }

    // ── 3. Effectuer la mise à jour ───────────────────────────────────
    $stmt = $pdo->prepare(
        'UPDATE contrat SET titre = :titre, description = :description, budget = :budget,
         delai = :delai, statut = :statut, freelance_info = :freelance_info,
         job_offer_id = :job_offer_id, job_application_id = :job_application_id,
         signature_client = :signature_client, signature_freelance = :signature_freelance
         WHERE id_contrat = :id'
    );
    return $stmt->execute([
        'titre'               => $data['titre'],
        'description'         => $data['description'],
        'budget'              => $data['budget'],
        'delai'               => $data['delai'],
        'statut'              => $data['statut'],
        'freelance_info'      => $data['freelance_info'],
        'job_offer_id'        => $data['job_offer_id']       ?? null,
        'job_application_id'  => $data['job_application_id'] ?? null,
        'signature_client'    => $data['signature_client'],
        'signature_freelance' => $data['signature_freelance'],
        'id'                  => $id,
    ]);
}

function deleteContrat(int $id) {
    $pdo = config::getConnexion();
    // Libérer les règles associées avant la suppression (on efface le titre_contrat correspondant)
    $contrat = getContratById($id);
    if ($contrat) {
        $stmt = $pdo->prepare('UPDATE rules SET titre_contrat = NULL WHERE titre_contrat = :titre');
        $stmt->execute(['titre' => $contrat['titre']]);
    }

    $stmt = $pdo->prepare('DELETE FROM contrat WHERE id_contrat = :id');
    return $stmt->execute(['id' => $id]);
}

function assignRulesToContrat($contratId, $ruleIds) {
    if (empty($ruleIds) || !is_array($ruleIds)) return;
    $pdo = config::getConnexion();
    $contrat = getContratById($contratId);
    if (!$contrat) return;
    
    // Associer les règles sélectionnées au titre du contrat
    $inQuery = implode(',', array_fill(0, count($ruleIds), '?'));
    $stmt = $pdo->prepare("UPDATE rules SET titre_contrat = ? WHERE id_rule IN ($inQuery)");
    $params = array_merge([$contrat['titre']], $ruleIds);
    $stmt->execute($params);
}

function saveSuggestedRules(array $data) {
    if (!empty($_POST['suggested_rules_json'])) {
        $suggestedRules = json_decode($_POST['suggested_rules_json'], true);
        if (is_array($suggestedRules)) {
            $contratTitre = $data['titre'];
            foreach ($suggestedRules as $sr) {
                if (!empty($sr['titre']) && !empty($sr['description'])) {
                    createRule([
                        'titre'         => htmlspecialchars($sr['titre'],       ENT_QUOTES, 'UTF-8'),
                        'description'   => htmlspecialchars($sr['description'], ENT_QUOTES, 'UTF-8'),
                        'type'          => htmlspecialchars($sr['type']   ?? 'Général', ENT_QUOTES, 'UTF-8'),
                        'valeur'        => htmlspecialchars($sr['valeur'] ?? '',         ENT_QUOTES, 'UTF-8'),
                        'statut'        => 'actif',
                        'titre_contrat' => $contratTitre,
                    ]);
                }
            }
        }
    }
}

function getAvailableRulesForContrat($contratId = null) {
    $pdo = config::getConnexion();
    $stmt = $pdo->query('SELECT * FROM rules ORDER BY date_creation DESC');
    return $stmt->fetchAll();
}

// ── Récupérer les offres de job approuvées ────────────────────────────
function getApprovedJobOffers(): array {
    $pdo = config::getConnexion();
    try {
        $stmt = $pdo->query(
            "SELECT id, titre, budget, delai, competences
             FROM job_offer WHERE statut = 'approved'
             ORDER BY date_creation DESC"
        );
        return $stmt->fetchAll();
    } catch (PDOException $e) { return []; }
}

// ── Récupérer les candidatures approuvées pour une offre ─────────────
function getApprovedApplicationsForOffer(int $jobOfferId): array {
    $pdo = config::getConnexion();
    try {
        $stmt = $pdo->prepare(
            "SELECT ja.id, ja.name, ja.email, ja.job_title, ja.cv_path,
                    u.id as user_id, u.nom, u.prenom, u.avatar
             FROM job_applications ja
             LEFT JOIN users u ON ja.user_id = u.id
             WHERE ja.job_id = :job_id AND ja.status = 'approved'
             ORDER BY ja.created_at DESC"
        );
        $stmt->execute(['job_id' => $jobOfferId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) { return []; }
}

// ── Récupérer toutes les candidatures approuvées ──────────────────────
function getAllApprovedApplications(): array {
    $pdo = config::getConnexion();
    try {
        $stmt = $pdo->query(
            "SELECT ja.id, ja.name, ja.email, ja.job_title, ja.cv_path,
                    ja.job_id, jo.titre as job_titre, jo.budget as job_budget,
                    u.id as user_id, u.nom, u.prenom, u.avatar
             FROM job_applications ja
             LEFT JOIN job_offer jo ON ja.job_id = jo.id
             LEFT JOIN users u ON ja.user_id = u.id
             WHERE ja.status = 'approved'
             ORDER BY ja.created_at DESC"
        );
        return $stmt->fetchAll();
    } catch (PDOException $e) { return []; }
}

// ── Récupérer contrat avec infos complètes (jointures) ───────────────
function getContratWithDetails(int $id): ?array {
    $pdo = config::getConnexion();
    try {
        $stmt = $pdo->prepare(
            "SELECT c.*,
                    jo.titre as job_offer_titre, jo.competences as job_competences,
                    ja.name as freelancer_name, ja.email as freelancer_email,
                    ja.cv_path as freelancer_cv, ja.job_title as freelancer_poste,
                    u.nom as user_nom, u.prenom as user_prenom, u.avatar as user_avatar
             FROM contrat c
             LEFT JOIN job_offer jo ON c.job_offer_id = jo.id
             LEFT JOIN job_applications ja ON c.job_application_id = ja.id
             LEFT JOIN users u ON ja.user_id = u.id
             WHERE c.id_contrat = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (PDOException $e) { return null; }
}


// --- Logique de Contrôleur ---

$errors = [];
$successMessage = null;
$action = $_REQUEST['action'] ?? 'list';
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
$currentContrat = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = filter_var(trim($_POST['titre'] ?? ''), FILTER_SANITIZE_STRING);
    $description = filter_var(trim($_POST['description'] ?? ''), FILTER_SANITIZE_STRING);
    $budget = filter_var(trim($_POST['budget'] ?? ''), FILTER_SANITIZE_STRING);
    $delai = filter_var(trim($_POST['delai'] ?? ''), FILTER_SANITIZE_STRING);
    $statut = filter_var(trim($_POST['statut'] ?? ''), FILTER_SANITIZE_STRING);
    $freelance_info = filter_var(trim($_POST['freelance_info'] ?? ''), FILTER_SANITIZE_STRING);
    $signature_client = filter_var(trim($_POST['signature_client'] ?? ''), FILTER_SANITIZE_STRING);
    $signature_freelance = filter_var(trim($_POST['signature_freelance'] ?? ''), FILTER_SANITIZE_STRING);
    $selected_rules = $_POST['selected_rules'] ?? [];
    $job_offer_id       = !empty($_POST['job_offer_id'])       ? intval($_POST['job_offer_id'])       : null;
    $job_application_id = !empty($_POST['job_application_id']) ? intval($_POST['job_application_id']) : null;
    
    if (empty($statut)) {
        $statut = 'brouillon'; // Statut par défaut
    }

    // Validations
    if (empty($titre)) {
        $errors['titre'] = 'Le titre est obligatoire.';
    } elseif (strlen($titre) > 255) {
        $errors['titre'] = 'Le titre ne peut pas dépasser 255 caractères.';
    }

    if (empty($description)) {
        $errors['description'] = 'La description est obligatoire.';
    }

    if (empty($budget) || !is_numeric($budget) || floatval($budget) <= 0) {
        $errors['budget'] = 'Le budget doit être un nombre strictement positif.';
    }

    if (empty($delai) || !is_numeric($delai) || intval($delai) <= 0) {
        $errors['delai'] = 'Le délai doit être un nombre entier strictement positif (en jours).';
    }

    $validStatuts = ['brouillon', 'en_attente', 'actif', 'termine', 'annule', 'archive'];
    if (!in_array($statut, $validStatuts)) {
        $errors['statut'] = 'Statut invalide.';
    }

    if (empty($freelance_info)) {
        $errors['freelance_info'] = 'Les informations du freelancer sont obligatoires.';
    }

    // Signature : optionnelle à la création, obligatoire seulement en modification
    // L'autre partie peut signer via la page de détails
    $userRole = $_SESSION['user_role'] ?? 'client';
    // Pas de validation de signature obligatoire — les deux parties signent quand elles veulent
    if (empty($errors)) {
        $badWords = new BadWordsService();
        $bwResult = $badWords->checkFields([
            'titre'          => $titre,
            'description'    => $description,
            'freelance_info' => $freelance_info,
        ]);
        if (!$bwResult['is_clean']) {
            foreach ($bwResult['errors'] as $field => $msg) {
                $errors[$field] = $msg;
            }
        }
    }

    // Vérification de l'unicité du titre — seulement si pas d'autres erreurs
    if (empty($errors) && !empty($titre)) {
        $existingContrats = getAllContrats();
        $currentId = !empty($_POST['id_contrat']) ? intval($_POST['id_contrat']) : null;
        foreach ($existingContrats as $c) {
            if ($currentId && $c['id_contrat'] == $currentId) continue;
            if (strtolower(trim($c['titre'])) === strtolower(trim($titre))) {
                $errors['titre'] = 'Un contrat avec ce titre existe déjà.';
                break;
            }
        }
    }

    if (empty($errors)) {
        $data = [
            'titre'              => htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'),
            'description'        => htmlspecialchars($description, ENT_QUOTES, 'UTF-8'),
            'budget'             => floatval($budget),
            'delai'              => intval($delai),
            'statut'             => $statut,
            'freelance_info'     => htmlspecialchars($freelance_info, ENT_QUOTES, 'UTF-8'),
            'job_offer_id'       => $job_offer_id,
            'job_application_id' => $job_application_id,
            'signature_client'   => htmlspecialchars($signature_client, ENT_QUOTES, 'UTF-8'),
            'signature_freelance'=> htmlspecialchars($signature_freelance, ENT_QUOTES, 'UTF-8')
        ];

        if (!empty($_POST['id_contrat'])) {
            $editedId = intval($_POST['id_contrat']);
            if (updateContrat($editedId, $data)) {
                assignRulesToContrat($editedId, $selected_rules);
                saveSuggestedRules($data); // Sauvegarder les règles IA en édition aussi

                if (!empty($_POST['redirect_to'])) {
                    $redirectUrl = $_POST['redirect_to'] . '?success=update';
                    if (!headers_sent()) {
                        header('Location: ' . $redirectUrl);
                        exit;
                    } else {
                        echo '<script>window.location.href="' . htmlspecialchars($redirectUrl, ENT_QUOTES) . '";</script>';
                        exit;
                    }
                }
                $successMessage = 'Contrat mis à jour avec succès.';
            } else {
                $errors['general'] = 'Impossible de mettre à jour le contrat.';
            }
        } else {
            $newId = createContrat($data);
            if ($newId) {
                assignRulesToContrat($newId, $selected_rules);
                saveSuggestedRules($data); // Sauvegarder les règles IA en création

                if (!empty($_POST['redirect_to'])) {
                    $redirectUrl = $_POST['redirect_to'] . '?success=create';
                    if (!headers_sent()) {
                        header('Location: ' . $redirectUrl);
                        exit;
                    } else {
                        // Fallback JS redirect si headers déjà envoyés
                        echo '<script>window.location.href="' . htmlspecialchars($redirectUrl, ENT_QUOTES) . '";</script>';
                        exit;
                    }
                }
                $successMessage = 'Contrat ajouté avec succès.';
            } else {
                $errors['general'] = 'Impossible de créer le contrat.';
            }
        }
    }
}

if ($action === 'delete' && $id !== null) {
    if (deleteContrat($id)) {
        $redirectUrl = strtok($_SERVER['REQUEST_URI'], '?') . '?success=delete';
        if (!headers_sent()) { header('Location: ' . $redirectUrl); exit; }
        else { echo '<script>window.location.href="' . htmlspecialchars($redirectUrl, ENT_QUOTES) . '";</script>'; exit; }
    } else {
        $errors['general'] = 'Impossible de supprimer ce contrat.';
    }
}

// ── Changement rapide de statut (workflow) ────────────────────────────
if ($action === 'change_status' && $id !== null) {
    $newStatus      = $_POST['new_status'] ?? '';
    $validStatuts   = ['brouillon', 'en_attente', 'actif', 'termine', 'annule', 'archive'];
    $currentContrat = getContratById($id);

    if ($currentContrat && in_array($newStatus, $validStatuts)) {
        $data = array_merge($currentContrat, ['statut' => $newStatus]);
        if (updateContrat($id, $data)) {
            $redirectUrl = strtok($_SERVER['REQUEST_URI'], '?') . '?success=status_changed';
            if (!headers_sent()) { header('Location: ' . $redirectUrl); exit; }
            else { echo '<script>window.location.href="' . htmlspecialchars($redirectUrl, ENT_QUOTES) . '";</script>'; exit; }
        }
    }
    $redirectUrl = strtok($_SERVER['REQUEST_URI'], '?') . '?error=status_failed';
    if (!headers_sent()) { header('Location: ' . $redirectUrl); exit; }
    else { echo '<script>window.location.href="' . htmlspecialchars($redirectUrl, ENT_QUOTES) . '";</script>'; exit; }
}

if ($action === 'archive' && $id !== null) {
    $currentContrat = getContratById($id);
    if ($currentContrat) {
        $data = [
            'titre' => $currentContrat['titre'],
            'description' => $currentContrat['description'],
            'budget' => $currentContrat['budget'],
            'delai' => $currentContrat['delai'],
            'statut' => 'archive'
        ];
        if (updateContrat($id, $data)) {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?success=archive');
            exit;
        } else {
            $errors['general'] = 'Impossible d\'archiver ce contrat.';
        }
    }
}

if ($action === 'verify' && $id !== null) {
    $currentContrat = getContratById($id);
    if ($currentContrat) {
        // Logique de vérification de conformité basique
        // Vérifie si le budget et le délai respectent des critères minimaux
        if ($currentContrat['budget'] > 0 && $currentContrat['delai'] > 0 && strlen($currentContrat['description']) > 10) {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?success=verify_ok');
        } else {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?error=verify_fail');
        }
        exit;
    }
}

if ($action === 'edit' && $id !== null) {
    $currentContrat = getContratById($id);
    if (!$currentContrat) {
        $errors['general'] = 'Contrat introuvable.';
        $action = 'list';
    }
}

// Les valeurs de tri et recherche récupérées du GET pour l'affichage de la liste
$searchQuery = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'date_creation';
$order = $_GET['order'] ?? 'DESC';

$contrats = getAllContrats($searchQuery, $sortBy, $order);
