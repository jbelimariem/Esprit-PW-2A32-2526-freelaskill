<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/Contrat.php';

// --- Fonctions de base de données ---

function getAllContrats() {
    $pdo = config::getConnexion();
    $stmt = $pdo->query('SELECT * FROM contrat ORDER BY date_creation DESC');
    return $stmt->fetchAll();
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
        'INSERT INTO contrat (titre, description, budget, delai, statut, date_creation) VALUES (:titre, :description, :budget, :delai, :statut, NOW())'
    );
    return $stmt->execute([
        'titre' => $data['titre'],
        'description' => $data['description'],
        'budget' => $data['budget'],
        'delai' => $data['delai'],
        'statut' => $data['statut']
    ]);
}

function updateContrat(int $id, array $data) {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare(
        'UPDATE contrat SET titre = :titre, description = :description, budget = :budget, delai = :delai, statut = :statut WHERE id_contrat = :id'
    );
    return $stmt->execute([
        'titre' => $data['titre'],
        'description' => $data['description'],
        'budget' => $data['budget'],
        'delai' => $data['delai'],
        'statut' => $data['statut'],
        'id' => $id
    ]);
}

function deleteContrat(int $id) {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare('DELETE FROM contrat WHERE id_contrat = :id');
    return $stmt->execute(['id' => $id]);
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
    
    if (empty($statut)) {
        $statut = 'brouillon'; // Statut par défaut
    }

    // Validations
    if (empty($titre)) {
        $errors[] = 'Le titre est obligatoire.';
    } elseif (strlen($titre) > 255) {
        $errors[] = 'Le titre ne peut pas dépasser 255 caractères.';
    }

    if (empty($description)) {
        $errors[] = 'La description est obligatoire.';
    }

    if (empty($budget) || !is_numeric($budget) || floatval($budget) <= 0) {
        $errors[] = 'Le budget doit être un nombre strictement positif.';
    }

    if (empty($delai) || !is_numeric($delai) || intval($delai) <= 0) {
        $errors[] = 'Le délai doit être un nombre entier strictement positif (en jours).';
    }

    $validStatuts = ['brouillon', 'en_attente', 'actif', 'termine', 'annule', 'archive'];
    if (!in_array($statut, $validStatuts)) {
        $errors[] = 'Statut invalide.';
    }

    // Vérification de l'unicité du titre (Optionnel, mais bonne pratique)
    $existingContrats = getAllContrats();
    $currentId = !empty($_POST['id_contrat']) ? intval($_POST['id_contrat']) : null;
    
    foreach ($existingContrats as $c) {
        if ($currentId && $c['id_contrat'] == $currentId) continue;
        if (!empty($titre) && strtolower($c['titre']) === strtolower($titre)) {
            $errors[] = 'Un contrat avec ce titre existe déjà.';
        }
    }

    if (empty($errors)) {
        $data = [
            'titre' => htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'),
            'description' => htmlspecialchars($description, ENT_QUOTES, 'UTF-8'),
            'budget' => floatval($budget),
            'delai' => intval($delai),
            'statut' => $statut
        ];

        if (!empty($_POST['id_contrat'])) {
            $editedId = intval($_POST['id_contrat']);
            if (updateContrat($editedId, $data)) {
                if (!empty($_POST['redirect_to'])) {
                    header('Location: ' . $_POST['redirect_to'] . '?success=update');
                    exit;
                }
                $successMessage = 'Contrat mis à jour avec succès.';
            } else {
                $errors[] = 'Impossible de mettre à jour le contrat.';
            }
        } else {
            if (createContrat($data)) {
                if (!empty($_POST['redirect_to'])) {
                    header('Location: ' . $_POST['redirect_to'] . '?success=create');
                    exit;
                }
                $successMessage = 'Contrat ajouté avec succès.';
            } else {
                $errors[] = 'Impossible de créer le contrat.';
            }
        }
    }
}

if ($action === 'delete' && $id !== null) {
    if (deleteContrat($id)) {
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?success=delete');
        exit;
    } else {
        $errors[] = 'Impossible de supprimer ce contrat.';
    }
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
            $errors[] = 'Impossible d\'archiver ce contrat.';
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
        $errors[] = 'Contrat introuvable.';
        $action = 'list';
    }
}

$contrats = getAllContrats();
