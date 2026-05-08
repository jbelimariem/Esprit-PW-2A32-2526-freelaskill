<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['user'])) {
    $_SESSION['user_id'] = (int)$_GET['user'];
    $_SESSION['role'] = ($_SESSION['user_id'] == 1) ? 'admin' : 'user';
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
}

// ── Handle POST directly here (no separate fetch needed) ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    header('Content-Type: application/json');

    $id_user1 = (int)$_SESSION['user_id'];
    $id_user2 = (int)$_POST['user_id'];

    if ($id_user2 <= 0) {
        echo json_encode(['error' => 'ID utilisateur invalide']);
        exit;
    }

    if ($id_user1 === $id_user2) {
        echo json_encode(['error' => 'Vous ne pouvez pas créer une conversation avec vous-même']);
        exit;
    }

    try {
        $pdo = config::getConnexion();

        // Check if conversation already exists
        $stmt = $pdo->prepare("
            SELECT id_conversation FROM conversations
            WHERE (id_user1 = :u1 AND id_user2 = :u2)
               OR (id_user1 = :u2 AND id_user2 = :u1)
        ");
        $stmt->execute([':u1' => $id_user1, ':u2' => $id_user2]);
        $existing = $stmt->fetch();

        if ($existing) {
            echo json_encode(['success' => true, 'id_conversation' => $existing['id_conversation']]);
            exit;
        }

        // Create new conversation
        $stmt = $pdo->prepare("
            INSERT INTO conversations (id_user1, id_user2, statut, date_creation)
            VALUES (:u1, :u2, 'active', NOW())
        ");
        $ok = $stmt->execute([':u1' => $id_user1, ':u2' => $id_user2]);

        if ($ok) {
            $newId = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'id_conversation' => $newId]);
        } else {
            $err = $stmt->errorInfo();
            echo json_encode(['error' => 'Echec INSERT: ' . ($err[2] ?? 'inconnu')]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'PDO: ' . $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle conversation - FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0a0a;
            color: white;
            padding: 2rem;
        }
        .container { max-width: 500px; margin: 0 auto; }
        .back-btn {
            color: #2563eb; text-decoration: none;
            margin-bottom: 2rem; display: inline-block;
        }
        h1 { margin-bottom: .5rem; font-size: 1.8rem; }
        .session-info {
            background: rgba(37,99,235,.1);
            border: 1px solid rgba(37,99,235,.25);
            border-radius: 8px;
            padding: .5rem 1rem;
            font-size: .8rem;
            color: #93c5fd;
            margin-bottom: 1.5rem;
        }
        .search-box {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.25rem;
        }
        .search-box input {
            width: 100%; background: transparent;
            border: none; outline: none;
            color: white; font-size: 1rem; padding: 0.5rem;
        }
        .search-box input::placeholder { color: #666; }
        .btn-start {
            background: #2563eb; color: white; border: none;
            padding: 0.8rem 1rem; border-radius: 12px;
            cursor: pointer; font-size: 1rem; font-weight: 500;
            width: 100%; transition: all 0.2s;
        }
        .btn-start:hover { background: #1d4ed8; }
        .btn-start:disabled { background: #2a2a2a; cursor: not-allowed; }
        .alert {
            border-radius: 8px; padding: 1rem;
            margin-bottom: 1rem; text-align: center; font-size: .9rem;
        }
        .alert-error   { background: rgba(231,0,19,.1);  border: 1px solid rgba(231,0,19,.3);  color: #e70013; }
        .alert-success { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3); color: #22c55e; }
        .info-text { text-align: center; color: #666; margin-top: 1rem; font-size: 0.8rem; }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php?page=conversations" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>

    <h1><i class="fa-solid fa-plus"></i> Nouvelle conversation</h1>
    <div class="session-info">
        <i class="fa-solid fa-user"></i>
        Connecté : <strong>Utilisateur #<?= (int)$_SESSION['user_id'] ?></strong>
    </div>

    <div class="search-box">
        <input type="number" id="userIdInput"
               placeholder="Entrez l'ID de l'utilisateur (ex: 2)" min="1">
    </div>

    <div id="alert" class="alert" style="display:none"></div>

    <button class="btn-start" id="startBtn" onclick="startConversation()">
        <i class="fa-regular fa-message"></i> Démarrer la discussion
    </button>

    <div class="info-text">
        <i class="fa-regular fa-circle-info"></i>
        Entrez l'ID de l'utilisateur avec qui vous voulez discuter
    </div>
</div>

<script>
function showAlert(msg, type) {
    const el = document.getElementById('alert');
    el.className = 'alert alert-' + type;
    el.textContent = msg;
    el.style.display = 'block';
    if (type === 'error') setTimeout(() => el.style.display = 'none', 4000);
}

function startConversation() {
    const userId = parseInt(document.getElementById('userIdInput').value);
    if (!userId || userId <= 0) {
        showAlert('Veuillez entrer un ID utilisateur valide', 'error');
        return;
    }

    const btn = document.getElementById('startBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creation...';

    const form = new FormData();
    form.append('user_id', userId);

    // POST to the same file — no session sharing issues
    fetch('new_conversation.php', { method: 'POST', body: form })
        .then(r => r.text())
        .then(text => {
            console.log('Server response:', text);
            let data;
            try { data = JSON.parse(text); }
            catch(e) {
                showAlert('Reponse invalide: ' + text.substring(0, 150), 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-regular fa-message"></i> Demarrer la discussion';
                return;
            }

            if (data.success) {
                showAlert('Conversation creee ! Redirection...', 'success');
                setTimeout(() => {
                    window.location.href = 'index.php?page=chat&id=' + data.id_conversation;
                }, 700);
            } else {
                showAlert(data.error || 'Erreur inconnue', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-regular fa-message"></i> Demarrer la discussion';
            }
        })
        .catch(err => {
            showAlert('Erreur reseau: ' + err.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-regular fa-message"></i> Demarrer la discussion';
        });
}

document.getElementById('userIdInput').addEventListener('keypress', e => {
    if (e.key === 'Enter') startConversation();
});
</script>
</body>
</html>