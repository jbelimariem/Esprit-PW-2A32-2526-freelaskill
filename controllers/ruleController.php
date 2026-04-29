<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/Rule.php';

// --- Fonctions de base de données ---

function getAllRules() {
    $pdo = config::getConnexion();
    $stmt = $pdo->query('SELECT * FROM rules ORDER BY date_creation DESC');
    return $stmt->fetchAll();
}

function getRuleById(int $id) {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare('SELECT * FROM rules WHERE id_rule = :id');
    $stmt->execute(['id' => $id]);
    $rule = $stmt->fetch();
    return $rule === false ? null : $rule;
}

function createRule(array $data) {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare(
        'INSERT INTO rules (titre, description, type, valeur, date_creation, statut, id_contrat) VALUES (:titre, :description, :type, :valeur, NOW(), :statut, :id_contrat)'
    );
    return $stmt->execute([
        'titre' => $data['titre'],
        'description' => $data['description'],
        'type' => $data['type'],
        'valeur' => $data['valeur'],
        'statut' => $data['statut'],
        'id_contrat' => $data['id_contrat']
    ]);
}

function updateRule(int $id, array $data) {
    $pdo = config::getConnexion();
    // On met à jour seulement les champs fournis dans $data
    $fields = [];
    foreach ($data as $key => $value) {
        $fields[] = "$key = :$key";
    }
    $data['id'] = $id;
    $stmt = $pdo->prepare('UPDATE rules SET ' . implode(', ', $fields) . ' WHERE id_rule = :id');
    return $stmt->execute($data);
}

function deleteRule(int $id) {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare('DELETE FROM rules WHERE id_rule = :id');
    return $stmt->execute(['id' => $id]);
}

// --- Logique de Contrôleur ---
$errors = [];
$successMessage = null;
$action = $_REQUEST['action'] ?? 'list';
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
$currentRule = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitisation des entrées
    $type = filter_var(trim($_POST['type'] ?? ''), FILTER_SANITIZE_STRING);
    $titre = filter_var(trim($_POST['titre'] ?? ''), FILTER_SANITIZE_STRING);
    $description = filter_var(trim($_POST['description'] ?? ''), FILTER_SANITIZE_STRING);
    $valeur = filter_var(trim($_POST['valeur'] ?? ''), FILTER_SANITIZE_STRING);
    $statut = filter_var(trim($_POST['statut'] ?? ''), FILTER_SANITIZE_STRING);
    $id_contrat = filter_var(trim($_POST['id_contrat'] ?? ''), FILTER_SANITIZE_STRING);

    // Validation des champs requis
    if (empty($titre)) {
        $errors['titre'] = 'Le titre est requis.';
    } elseif (strlen($titre) > 255) {
        $errors['titre'] = 'Le titre ne peut pas dépasser 255 caractères.';
    }

    if (empty($description)) {
        $errors['description'] = 'La description est requise.';
    } elseif (strlen($description) > 1000) {
        $errors['description'] = 'La description ne peut pas dépasser 1000 caractères.';
    }

    // Validation du type (optionnel mais limité)
    if (!empty($type) && strlen($type) > 100) {
        $errors['type'] = 'Le type ne peut pas dépasser 100 caractères.';
    }

    // Validation de la valeur (optionnel)
    if (!empty($valeur) && strlen($valeur) > 500) {
        $errors['valeur'] = 'La valeur ne peut pas dépasser 500 caractères.';
    }

    // Validation du statut
    $validStatuts = ['actif', 'inactif'];
    if (!empty($statut) && !in_array($statut, $validStatuts)) {
        $errors['statut'] = 'Le statut doit être "actif" ou "inactif".';
    }

    // Validation de l'ID contrat (optionnel, doit être numérique si fourni)
    if (!empty($id_contrat) && !is_numeric($id_contrat)) {
        $errors['id_contrat'] = 'L\'ID contrat doit être un nombre.';
    }

    // Vérification des doublons pour le titre (si création)
    if (empty($_POST['id_rule']) && !empty($titre)) {
        $existingRules = getAllRules();
        foreach ($existingRules as $rule) {
            if (strtolower($rule['titre']) === strtolower($titre)) {
                $errors['titre'] = 'Une règle avec ce titre existe déjà.';
                break;
            }
        }
    }

    if (empty($errors)) {
        $data = [
            'titre' => htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'),
            'description' => htmlspecialchars($description, ENT_QUOTES, 'UTF-8'),
            'type' => htmlspecialchars($type, ENT_QUOTES, 'UTF-8'),
            'valeur' => htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8'),
            'statut' => $statut,
            'id_contrat' => $id_contrat === '' ? null : $id_contrat
        ];

        if (!empty($_POST['id_rule'])) {
            $editedId = intval($_POST['id_rule']);
            if (updateRule($editedId, $data)) {
                $successMessage = 'Règle mise à jour avec succès.';
            } else {
                $errors['general'] = 'Impossible de mettre à jour la règle.';
            }
        } else {
            if (createRule($data)) {
                $successMessage = 'Règle ajoutée avec succès.';
            } else {
                $errors['general'] = 'Impossible d’ajouter la règle.';
            }
        }
    }
}

if ($action === 'toggle' && $id !== null) {
    $rule = getRuleById($id);
    if ($rule) {
        $newStatus = $rule['statut'] === 'actif' ? 'inactif' : 'actif';
        if (updateRule($id, ['statut' => $newStatus])) {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?success=toggle');
            exit;
        } else {
            $errors['general'] = 'Impossible de changer le statut de la règle.';
        }
    } else {
        $errors['general'] = 'Règle introuvable.';
    }
}

if ($action === 'delete' && $id !== null) {
    if (deleteRule($id)) {
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?success=delete');
        exit;
    } else {
        $errors['general'] = 'Impossible de supprimer cette règle.';
    }
}

if ($action === 'edit' && $id !== null) {
    $currentRule = getRuleById($id);
    if (!$currentRule) {
        $errors['general'] = 'Règle introuvable.';
        $action = 'list'; // Revenir à la liste si la règle n'existe pas
    }
}

$rules = getAllRules();
