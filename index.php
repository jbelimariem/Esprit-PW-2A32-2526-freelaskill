<?php
ob_start();

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

$page   = $_GET['page']   ?? 'conversations';
$action = $_GET['action'] ?? 'index';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($page) {

    case 'conversations':
        require_once 'Controller/ChatController.php';
        $controller = new ChatController();
        $controller->conversations();
        break;

    case 'chat':
    require_once 'Controller/ChatController.php';
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
        // ── Nouvelles routes groupes ──
        elseif  ($action === 'create-groupe'    && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->createGroupe(); }
        elseif  ($action === 'inviter-membre'   && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->inviterMembre(); }
        elseif  ($action === 'quitter-groupe'   && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->quitterGroupe(); }
        elseif  ($id) { $controller->show($id); }
        else {
            header('Location: index.php?page=conversations');
            exit;
        }
        break;

    case 'git':
        require_once 'Controller/GitController.php';
        $controller = new GitController();

        if      ($action === 'commit'          && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->commit(); }
        elseif  ($action === 'restore'         && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->restore(); }
        elseif  ($action === 'create-branche'  && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->createBranche(); }
        elseif  ($action === 'delete-branche'  && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->deleteBranche(); }
        elseif  ($action === 'list-commits'    && $_SERVER['REQUEST_METHOD'] === 'GET')  { $controller->listCommits(); }
        elseif  ($action === 'list-branches'   && $_SERVER['REQUEST_METHOD'] === 'GET')  { $controller->listBranches(); }
        elseif  ($action === 'show-commit'     && $_SERVER['REQUEST_METHOD'] === 'GET')  { $controller->showCommit(); }
        else    { $controller->panel(); }
        break;

    case 'admin':
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            die('Accès réservé aux administrateurs');
        }

        require_once 'Controller/AdminChatController.php';
        $controller = new AdminChatController();

        if      ($action === 'archive'      && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->archiveConversation(); }
        elseif  ($action === 'delete-conv'  && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->deleteConversation(); }
        elseif  ($action === 'delete-msg'   && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->deleteMessage(); }
        elseif  ($action === 'ignore-flag'  && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->ignoreFlag(); }
        elseif  ($action === 'rename-conv'  && $_SERVER['REQUEST_METHOD'] === 'POST') { $controller->renameConversation(); }
        // ── Nouvelle route export PDF ──
        elseif  ($action === 'export-pdf') { $controller->exportPdf(); }
        else    { $controller->dashboard(); }
        break;

    default:
        header('Location: index.php?page=conversations');
        exit;
}
?>