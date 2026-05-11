<?php
ob_start();

// ══════════════════════════════════════════════════════════════
// MESSAGERIE — Point d'entrée intégré dans FreelaSkill
// Fichier à placer à la racine du projet :
// C:\xampp\htdocs\freelaskill\messagerie_index.php
// ══════════════════════════════════════════════════════════════

// 1. Charger la session FreelaSkill (déjà démarrée par les autres pages)
require_once __DIR__ . '/controllers/session.php';

// 2. Charger la config DB de FreelaSkill (même base : freelaskill)
require_once __DIR__ . '/config.php';

// 3. Sécurité : si pas connecté → page de login
if (!isLoggedIn()) {
    header('Location: /freelaskill/Views/Frontoffice/login.php');
    exit;
}

// 4. Adapter le rôle FreelaSkill → rôle Messagerie
// FreelaSkill : 'admin', 'freelancer', 'client'
// Messagerie  : 'admin', 'user'
$freelaskill_role    = $_SESSION['user_role'] ?? 'user';
$_SESSION['role']    = ($freelaskill_role === 'admin') ? 'admin' : 'user';

// 5. Routage
$page   = $_GET['page']   ?? 'conversations';
$action = $_GET['action'] ?? 'index';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($page) {

    case 'conversations':
        require_once __DIR__ . '/controllers/ChatController.php';
        $controller = new ChatController();
        $controller->conversations();
        break;

    case 'chat':
        require_once __DIR__ . '/controllers/ChatController.php';
        $controller = new ChatController();

        if      ($action === 'send'             && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->sendMessage(); }
        elseif  ($action === 'delete'           && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->deleteMessage(); }
        elseif  ($action === 'edit'             && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->editMessage(); }
        elseif  ($action === 'report'           && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->reportMessage(); }
        elseif  ($action === 'translate'        && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->translateMessage(); }
        elseif  ($action === 'unread-counts'    && $_SERVER['REQUEST_METHOD'] === 'GET')  { $controller->getUnreadCounts(); }
        elseif  ($action === 'upload-file'      && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->uploadFile(); }
        elseif  ($action === 'ephemeral'        && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->sendEphemeralMessage(); }
        elseif  ($action === 'create'           && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->createConversation(); }
        elseif  ($action === 'rename'           && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->renameConversation(); }
        elseif  ($action === 'delete-conv-user' && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->deleteConversationUser(); }
        elseif  ($action === 'searchUser'       && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->searchUser(); }
        elseif  ($action === 'create-groupe'    && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->createGroupe(); }
        elseif  ($action === 'inviter-membre'   && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->inviterMembre(); }
        elseif  ($action === 'quitter-groupe'   && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->quitterGroupe(); }
        elseif  ($id) { $controller->show($id); }
        else {
            header('Location: messagerie_index.php?page=conversations');
            exit;
        }
        break;

    case 'git':
        require_once __DIR__ . '/controllers/ChatController.php';
        $controller = new ChatController();
        $id_conv = isset($_GET['id_conversation']) ? (int)$_GET['id_conversation'] : null;
        
        if ($action === 'commit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->commitMessage($id_conv);
        } elseif ($id_conv) {
            $controller->git($id_conv);
        } else {
            header('Location: messagerie_index.php?page=conversations');
            exit;
        }
        break;

    case 'admin':
    // Seul l'admin peut accéder au dashboard admin messagerie
    if (!isset($_SESSION['admin_id'])) {
        header('Location: messagerie_index.php?page=conversations');
        exit;
    }

        require_once __DIR__ . '/controllers/AdminChatController.php';
        $controller = new AdminChatController();

        if      ($action === 'archive'      && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->archiveConversation(); }
        elseif  ($action === 'delete-conv'  && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->deleteConversation(); }
        elseif  ($action === 'delete-msg'   && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->deleteMessage(); }
        elseif  ($action === 'ignore-flag'  && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->ignoreFlag(); }
        elseif  ($action === 'rename-conv'  && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->renameConversation(); }
        elseif  ($action === 'export-pdf')  { $controller->exportPdf(); }
        else    { $controller->dashboard(); }
        break;

    default:
        header('Location: messagerie_index.php?page=conversations');
        exit;
}
?>
