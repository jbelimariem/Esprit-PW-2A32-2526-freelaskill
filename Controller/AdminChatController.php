<?php
// Controller/AdminChatController.php

require_once __DIR__ . '/../Model/conversation.php';
require_once __DIR__ . '/../Model/message.php';

class AdminChatController {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    private function jsonResponse(array $data): void {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function dashboard() {
        // Toutes les conversations
        $stmt = $this->pdo->prepare("
            SELECT c.*,
                   (SELECT COUNT(*) FROM messages m
                    WHERE m.id_conversation = c.id_conversation
                      AND m.statut != 'deleted') AS total_messages
            FROM conversations c
            ORDER BY c.date_creation DESC
        ");
        $stmt->execute();
        $allConversations = $stmt->fetchAll();

        // Messages signalés
        $stmt = $this->pdo->prepare("
            SELECT m.*, c.id_user1, c.id_user2
            FROM messages m
            JOIN conversations c ON c.id_conversation = m.id_conversation
            WHERE m.statut = 'flagged'
            ORDER BY m.date_envoi DESC
        ");
        $stmt->execute();
        $flaggedMessages = $stmt->fetchAll();

        // Comptage pour le donut chart
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM messages WHERE statut = 'normal'");
        $stmt->execute();
        $countNormal = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM messages WHERE statut = 'deleted'");
        $stmt->execute();
        $countDeleted = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM messages WHERE statut = 'flagged'");
        $stmt->execute();
        $countFlagged = (int)$stmt->fetchColumn();

        // RECHERCHE
        $search = trim($_GET['search'] ?? '');
        if ($search !== '') {
            $mot = strtolower($search);
            $allConversations = array_values(array_filter($allConversations, function($conv) use ($mot) {
                $data = json_decode($conv['titre'] ?? '', true);
                $nom  = strtolower($data['nom'] ?? '');
                return str_contains(strtolower($conv['titre'] ?? ''), $mot) || str_contains($nom, $mot);
            }));
            $flaggedMessages = array_values(array_filter($flaggedMessages, function($msg) use ($mot) {
                return str_contains(strtolower($msg['contenu']), $mot);
            }));
        }

        // TRI
        $tri = $_GET['tri'] ?? 'date_desc';
        usort($allConversations, function($a, $b) use ($tri) {
            if ($tri === 'messages') return (int)$b['total_messages'] - (int)$a['total_messages'];
            $diff = strtotime($a['date_creation']) - strtotime($b['date_creation']);
            return $tri === 'date_asc' ? $diff : -$diff;
        });

        // STATISTIQUES GLOBALES (sans filtre)
        $stmt = $this->pdo->prepare("
            SELECT c.*,
                   (SELECT COUNT(*) FROM messages m
                    WHERE m.id_conversation = c.id_conversation
                      AND m.statut != 'deleted') AS total_messages
            FROM conversations c
        ");
        $stmt->execute();
        $allConvForStats = $stmt->fetchAll();

        $activeCount   = count(array_filter($allConvForStats, fn($c) => $c['statut'] === 'active'));
        $totalMessages = (int)array_sum(array_column($allConvForStats, 'total_messages'));

        $convPlusActive = null;
        if (!empty($allConvForStats)) {
            usort($allConvForStats, fn($a, $b) => (int)$b['total_messages'] - (int)$a['total_messages']);
            $convPlusActive = $allConvForStats[0];
        }

        $stats = [
            'total_conversations'  => count($allConvForStats),
            'active_conversations' => $activeCount,
            'total_messages'       => $totalMessages,
            'flagged_messages'     => count($flaggedMessages),
            'conv_plus_active'     => $convPlusActive,
            'msg_normal'           => $countNormal,
            'msg_deleted'          => $countDeleted,
            'msg_flagged'          => $countFlagged,
        ];

        include __DIR__ . '/../Views/Backoffice/dashboard.php';
    }

    // ─────────────────────────────────────────────────────────────────────
    // EXPORT PDF — génère un HTML complet et le rend téléchargeable
    // ─────────────────────────────────────────────────────────────────────
    public function exportPdf() {
        // Récupérer toutes les données
        $stmt = $this->pdo->prepare("
            SELECT c.*,
                   (SELECT COUNT(*) FROM messages m
                    WHERE m.id_conversation = c.id_conversation) AS total_messages,
                   (SELECT COUNT(*) FROM messages m
                    WHERE m.id_conversation = c.id_conversation AND m.statut = 'deleted') AS deleted_messages,
                   (SELECT COUNT(*) FROM messages m
                    WHERE m.id_conversation = c.id_conversation AND m.statut = 'flagged') AS flagged_messages_count
            FROM conversations c
            ORDER BY c.date_creation DESC
        ");
        $stmt->execute();
        $allConversations = $stmt->fetchAll();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM messages WHERE statut = 'normal'");
        $stmt->execute();
        $countNormal = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM messages WHERE statut = 'deleted'");
        $stmt->execute();
        $countDeleted = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM messages WHERE statut = 'flagged'");
        $stmt->execute();
        $countFlagged = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("
            SELECT m.*, c.id_user1, c.id_user2
            FROM messages m
            JOIN conversations c ON c.id_conversation = m.id_conversation
            WHERE m.statut = 'flagged'
            ORDER BY m.date_envoi DESC
        ");
        $stmt->execute();
        $flaggedMessages = $stmt->fetchAll();

        $totalConvs    = count($allConversations);
        $activeConvs   = count(array_filter($allConversations, fn($c) => $c['statut'] === 'active'));
        $totalMessages = $countNormal + $countDeleted + $countFlagged;
        $dateExport    = date('d/m/Y à H:i');

        // Construire le HTML du rapport
        ob_start();
        include __DIR__ . '/../Views/Backoffice/pdf_report.php';
        $html = ob_get_clean();

        // Envoyer comme page HTML imprimable (le navigateur gère l'impression/PDF)
        if (ob_get_level()) ob_clean();
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────
    // ADMIN ACTIONS
    // ─────────────────────────────────────────────────────────────────────

    public function renameConversation() {
        $id_conversation = (int)($_POST['id_conversation'] ?? 0);
        $titre           = trim($_POST['titre'] ?? '');
        if (empty($titre)) $this->jsonResponse(['error' => 'Titre requis']);
        $stmt = $this->pdo->prepare("UPDATE conversations SET titre = :titre WHERE id_conversation = :id");
        $this->jsonResponse(['success' => $stmt->execute([':titre' => $titre, ':id' => $id_conversation])]);
    }

    public function deleteConversation() {
        $id   = (int)($_POST['id_conversation'] ?? 0);
        $stmt = $this->pdo->prepare("DELETE FROM conversations WHERE id_conversation = :id");
        $this->jsonResponse(['success' => $stmt->execute([':id' => $id])]);
    }

    public function archiveConversation() {
        $id   = (int)($_POST['id_conversation'] ?? 0);
        $stmt = $this->pdo->prepare("UPDATE conversations SET statut = 'archived' WHERE id_conversation = :id");
        $this->jsonResponse(['success' => $stmt->execute([':id' => $id])]);
    }

    public function deleteMessage() {
        $id   = (int)($_POST['id_message'] ?? 0);
        $stmt = $this->pdo->prepare("DELETE FROM messages WHERE id_message = :id");
        $this->jsonResponse(['success' => $stmt->execute([':id' => $id])]);
    }

    public function ignoreFlag() {
        $id   = (int)($_POST['id_message'] ?? 0);
        $stmt = $this->pdo->prepare("UPDATE messages SET statut = 'normal' WHERE id_message = :id");
        $this->jsonResponse(['success' => $stmt->execute([':id' => $id])]);
    }
}
?>