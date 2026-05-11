<?php
// messagerie_get_messages.php
// Point d'entrée AJAX pour récupérer les messages en temps réel
// Placé à la racine du projet FreelaSkill

require_once __DIR__ . '/controllers/session.php';
require_once __DIR__ . '/config.php';

if (!isLoggedIn() || !isset($_GET['id'])) {
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

$id_conversation = (int)$_GET['id'];
$id_user         = (int)$_SESSION['user_id'];

$pdo = config::getConnexion();

// Récupérer la conversation
$stmt = $pdo->prepare("SELECT * FROM conversations WHERE id_conversation = :id");
$stmt->execute([':id' => $id_conversation]);
$conversation = $stmt->fetch();

if (!$conversation) {
    echo json_encode(['error' => 'Conversation introuvable']);
    exit;
}

// Vérifier accès : membre direct ou membre d'un groupe
$directAccess = ($conversation['id_user1'] == $id_user || $conversation['id_user2'] == $id_user);
$groupeAccess = false;
if (!$directAccess && !empty($conversation['titre'])) {
    $groupeData = json_decode($conversation['titre'], true);
    if (is_array($groupeData) && !empty($groupeData['groupe'])) {
        $groupeAccess = in_array($id_user, $groupeData['membres'] ?? []);
    }
}
if (!$directAccess && !$groupeAccess) {
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// 1. Récupérer les messages
$stmt = $pdo->prepare("
    SELECT m.*,
           GREATEST(0, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(m.date_envoi)) as seconds_ago
    FROM messages m
    WHERE m.id_conversation = :id AND m.statut != 'deleted'
    ORDER BY m.date_envoi ASC
");
$stmt->execute([':id' => $id_conversation]);
$messages = $stmt->fetchAll();

// 2. Envoyer la réponse
echo json_encode([
    'conversation' => $conversation,
    'messages'     => $messages
]);

// Flush avant suppression des éphémères
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    if (ob_get_level()) ob_end_flush();
    flush();
}

// 3. Supprimer les messages éphémères expirés
$stmt = $pdo->prepare("
    SELECT id_message, contenu, id_expediteur
    FROM messages
    WHERE id_conversation = :id
      AND statut != 'deleted'
      AND contenu LIKE :pattern
");
$stmt->execute([':id' => $id_conversation, ':pattern' => '%"ephemeral":true%']);
$toCheck = $stmt->fetchAll();

$toDelete = [];
foreach ($toCheck as $m) {
    $data = json_decode($m['contenu'], true);
    if (!is_array($data) || empty($data['ephemeral'])) continue;

    $exp       = $data['expiration'] ?? '';
    $createdAt = (int)($data['created_at'] ?? 0);
    $isSender  = ((int)$m['id_expediteur'] === $id_user);

    if ($exp === 'read' && !$isSender)                      { $toDelete[] = (int)$m['id_message']; }
    elseif ($exp === '10s' && (time() - $createdAt) >= 10)  { $toDelete[] = (int)$m['id_message']; }
    elseif ($exp === '1h'  && (time() - $createdAt) >= 3600){ $toDelete[] = (int)$m['id_message']; }
    elseif ($exp === '24h' && (time() - $createdAt) >= 86400){ $toDelete[] = (int)$m['id_message']; }
}

if (!empty($toDelete)) {
    $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
    $del = $pdo->prepare("UPDATE messages SET statut = 'deleted' WHERE id_message IN ($placeholders)");
    $del->execute($toDelete);
}
?>
