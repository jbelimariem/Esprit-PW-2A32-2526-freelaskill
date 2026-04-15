<?php
require_once __DIR__ . '/../Models/Rule.php';

$ruleModel = new Rule();
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
        $errors[] = 'Le titre est requis.';
    } elseif (strlen($titre) > 255) {
        $errors[] = 'Le titre ne peut pas dépasser 255 caractères.';
    }

    if (empty($description)) {
        $errors[] = 'La description est requise.';
    } elseif (strlen($description) > 1000) {
        $errors[] = 'La description ne peut pas dépasser 1000 caractères.';
    }

    // Validation du type (optionnel mais limité)
    if (!empty($type) && strlen($type) > 100) {
        $errors[] = 'Le type ne peut pas dépasser 100 caractères.';
    }

    // Validation de la valeur (optionnel)
    if (!empty($valeur) && strlen($valeur) > 500) {
        $errors[] = 'La valeur ne peut pas dépasser 500 caractères.';
    }

    // Validation du statut
    $validStatuts = ['actif', 'inactif'];
    if (!empty($statut) && !in_array($statut, $validStatuts)) {
        $errors[] = 'Le statut doit être "actif" ou "inactif".';
    }

    // Validation de l'ID contrat (optionnel, doit être numérique si fourni)
    if (!empty($id_contrat) && !is_numeric($id_contrat)) {
        $errors[] = 'L\'ID contrat doit être un nombre.';
    }

    // Vérification des doublons pour le titre (si création)
    if (empty($_POST['id_rule']) && !empty($titre)) {
        $existingRules = $ruleModel->all();
        foreach ($existingRules as $rule) {
            if (strtolower($rule['titre']) === strtolower($titre)) {
                $errors[] = 'Une règle avec ce titre existe déjà.';
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
            'id_contrat' => $id_contrat
        ];

        if (!empty($_POST['id_rule'])) {
            $editedId = intval($_POST['id_rule']);
            if ($ruleModel->update($editedId, $data)) {
                $successMessage = 'Règle mise à jour avec succès.';
            } else {
                $errors[] = 'Impossible de mettre à jour la règle.';
            }
        } else {
            if ($ruleModel->create($data)) {
                $successMessage = 'Règle ajoutée avec succès.';
            } else {
                $errors[] = 'Impossible d’ajouter la règle.';
            }
        }
    }
}

if ($action === 'toggle' && $id !== null) {
    $rule = $ruleModel->get($id);
    if ($rule) {
        $newStatus = $rule['statut'] === 'actif' ? 'inactif' : 'actif';
        if ($ruleModel->update($id, ['statut' => $newStatus])) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?success=toggle');
            exit;
        } else {
            $errors[] = 'Impossible de changer le statut de la règle.';
        }
    } else {
        $errors[] = 'Règle introuvable.';
    }
}

if ($action === 'edit' && $id !== null) {
    $currentRule = $ruleModel->get($id);
    if (!$currentRule) {
        $errors[] = 'Règle introuvable.';
        $action = 'list'; // Revenir à la liste si la règle n'existe pas
    }
}

$rules = $ruleModel->all();
