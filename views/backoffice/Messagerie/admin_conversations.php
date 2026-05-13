<?php
// Views/Backoffice/admin_conversations.php

if (!isset($allConversations) || !isset($flaggedMessages) || !isset($stats)) {
    die("Erreur: Données non disponibles");
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Messagerie - FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #060a18;
            --card-bg: rgba(15, 23, 42, 0.65);
            --border-color: rgba(255, 255, 255, 0.07);
            --text-muted: #8b9cb8;
            --tech-blue: #2563eb;
            --tunisian-red: #e70013;
            --tech-green: #22c55e;
            --radius-lg: 16px;
            --radius-full: 999px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background:
                radial-gradient(ellipse 70% 60% at 75% 30%, rgba(37,99,235,0.08) 0%, transparent 70%),
                radial-gradient(ellipse 55% 50% at 20% 20%, rgba(124,58,237,0.06) 0%, transparent 65%),
                #060a18;
            color: white;
            min-height: 100vh;
        }

        /* Logo 3 couleurs : Rouge, Blanc, Bleu */
        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .logo i {
            color: #e70013;
            font-size: 1.3rem;
        }
        
        .logo .logo-freela {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
        }
        
        .logo .logo-skill {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2563eb;
        }

        .navbar {
            background: rgba(8, 12, 28, 0.97);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-badge {
            background: rgba(37, 99, 235, 0.15);
            border: 1px solid rgba(37, 99, 235, 0.3);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            color: var(--tech-blue);
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--tech-blue);
        }

        .main-content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .stat-card:hover {
            border-color: rgba(37, 99, 235, 0.3);
            transform: translateY(-2px);
        }

        .stat-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-sub {
            font-size: 0.75rem;
            color: var(--tech-green);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 2rem 0 1.5rem;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-bar {
            display: flex;
            gap: 1rem;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.5rem 1rem;
            margin-bottom: 1rem;
        }

        .search-bar input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: white;
            font-size: 0.9rem;
        }

        .search-bar input::placeholder {
            color: var(--text-muted);
        }

        .search-bar button {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .filter-tab {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 0.4rem 1rem;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-tab:hover {
            background: rgba(37, 99, 235, 0.1);
            color: var(--tech-blue);
        }

        .filter-tab.active {
            background: var(--tech-blue);
            color: white;
            border-color: var(--tech-blue);
        }

        .admin-table {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        .admin-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th {
            text-align: left;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.8rem;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.85rem;
        }

        .admin-table tr:hover td {
            background: rgba(37, 99, 235, 0.05);
        }

        .badge {
            display: inline-flex;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-active {
            background: rgba(34, 197, 94, 0.1);
            color: var(--tech-green);
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .badge-archived {
            background: rgba(100, 116, 139, 0.1);
            color: #94a3b8;
            border: 1px solid rgba(100, 116, 139, 0.2);
        }

        .flagged-card {
            background: var(--card-bg);
            border: 1px solid rgba(231, 0, 19, 0.3);
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .flagged-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .flagged-user {
            font-weight: 600;
            color: var(--tunisian-red);
        }

        .flagged-date {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .flagged-content {
            background: rgba(0, 0, 0, 0.2);
            padding: 0.75rem;
            border-radius: 0.75rem;
            margin: 0.75rem 0;
            font-size: 0.85rem;
        }

        .flagged-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-sm {
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--tech-blue);
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-danger {
            background: var(--tunisian-red);
            color: white;
        }

        .btn-danger:hover {
            background: #c21a2e;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            cursor: pointer;
        }

        .btn-outline:hover {
            border-color: var(--tech-blue);
            color: var(--tech-blue);
        }

        .btn-icon {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.4rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .btn-icon:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--tech-blue);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=home" class="logo">
            <i class="fa-solid fa-shapes"></i>
            <span class="logo-freela">Freela</span>
            <span class="logo-skill">Skill</span>
        </a>
        <div class="admin-badge">
            <i class="fa-solid fa-shield-halved"></i> Panel Administrateur
        </div>
        <div class="nav-links">
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=conversations">Messagerie</a>
            <a href="/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin" class="active">Admin</a>
        </div>
    </div>
</nav>

<div class="main-content">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">Conversations totales</div>
            <div class="stat-value"><?= $stats['total_conversations'] ?></div>
            <div class="stat-sub"><?= $stats['active_conversations'] ?> actives</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Messages échangés</div>
            <div class="stat-value"><?= $stats['total_messages'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Messages signalés</div>
            <div class="stat-value" style="color: var(--tunisian-red);"><?= $stats['flagged_messages'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">À modérer</div>
            <div class="stat-value"><?= $stats['flagged_messages'] ?></div>
        </div>
    </div>

    <div class="search-bar">
        <i class="fa-solid fa-magnifying-glass" style="color: var(--text-muted);"></i>
        <input type="text" id="searchInput" placeholder="Rechercher par ID, client, freelance ou message...">
        <button id="clearSearchBtn" style="display: none;">
            <i class="fa-solid fa-times-circle"></i>
        </button>
    </div>

    <div class="filter-tabs">
        <button class="filter-tab active" data-filter="all">Toutes</button>
        <button class="filter-tab" data-filter="active">Actives</button>
        <button class="filter-tab" data-filter="archived">Archivées</button>
    </div>

    <div class="section-header">
        <h2 class="section-title">
            <i class="fa-solid fa-flag" style="color: var(--tunisian-red);"></i>
            Messages signalés
        </h2>
    </div>

    <?php if (empty($flaggedMessages)): ?>
        <div class="empty-state">
            <i class="fa-regular fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; color: var(--tech-green);"></i>
            <p>Aucun message signalé</p>
            <p style="font-size: 0.8rem;">Tous les messages respectent les règles</p>
        </div>
    <?php else: ?>
        <?php foreach ($flaggedMessages as $message): ?>
            <div class="flagged-card">
                <div class="flagged-header">
                    <span class="flagged-user">
                        <i class="fa-regular fa-user"></i> Utilisateur #<?= $message['id_expediteur'] ?? '?' ?>
                    </span>
                    <span class="flagged-date">
                        <i class="fa-regular fa-clock"></i> <?= date('d/m/Y H:i', strtotime($message['date_envoi'])) ?>
                    </span>
                </div>
                <div class="flagged-content">
                    <?= nl2br(htmlspecialchars($message['contenu'])) ?>
                </div>
                <div class="flagged-actions">
                    <button class="btn-sm btn-primary" onclick="ignoreMessage(<?= $message['id_message'] ?>)">
                        <i class="fa-regular fa-check-circle"></i> Ignorer
                    </button>
                    <button class="btn-sm btn-danger" onclick="deleteMessage(<?= $message['id_message'] ?>)">
                        <i class="fa-regular fa-trash-can"></i> Supprimer
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="section-header">
        <h2 class="section-title">
            <i class="fa-solid fa-comments"></i>
            Toutes les conversations
        </h2>
        <button class="btn-sm btn-outline" onclick="location.reload()">
            <i class="fa-solid fa-rotate-right"></i> Actualiser
        </button>
    </div>

    <div class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client / Freelance</th>
                    <th>Statut</th>
                    <th>Messages</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="conversationsTable">
                <?php foreach ($allConversations as $conv): ?>
                <tr data-conv-id="<?= $conv['id_conversation'] ?>">
                    <td>#<?= $conv['id_conversation'] ?></td>
                    <td><?= $conv['id_client'] ?> / <?= $conv['id_freelance'] ?></td>
                    <td>
                        <span class="badge <?= $conv['statut'] === 'active' ? 'badge-active' : 'badge-archived' ?>">
                            <?= $conv['statut'] === 'active' ? 'Active' : 'Archivée' ?>
                        </span>
                    </td>
                    <td><?= $conv['total_messages'] ?? 0 ?></td>
                    <td><?= date('d/m/Y', strtotime($conv['date_creation'])) ?></td>
                    <td>
                        <button class="btn-icon" onclick="viewConversation(<?= $conv['id_conversation'] ?>)" title="Voir">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                        <button class="btn-icon" onclick="archiveConversation(<?= $conv['id_conversation'] ?>)" title="Archiver">
                            <i class="fa-regular fa-folder"></i>
                        </button>
                        <button class="btn-icon" onclick="deleteConversation(<?= $conv['id_conversation'] ?>)" title="Supprimer">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const clearBtn = document.getElementById('clearSearchBtn');
const tableRows = document.querySelectorAll('#conversationsTable tr');
const filterTabs = document.querySelectorAll('.filter-tab');

function filterTable() {
    const searchTerm = searchInput.value.toLowerCase();
    const activeFilter = document.querySelector('.filter-tab.active')?.dataset.filter || 'all';
    
    tableRows.forEach(row => {
        const rowText = row.innerText.toLowerCase();
        const statusCell = row.querySelector('.badge');
        const status = statusCell ? statusCell.innerText.toLowerCase() : '';
        
        let matchesSearch = searchTerm === '' || rowText.includes(searchTerm);
        let matchesFilter = true;
        
        if (activeFilter === 'active') {
            matchesFilter = status === 'active';
        } else if (activeFilter === 'archived') {
            matchesFilter = status === 'archivée';
        }
        
        row.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
    });
}

searchInput.addEventListener('input', () => {
    filterTable();
    clearBtn.style.display = searchInput.value ? 'block' : 'none';
});

clearBtn.addEventListener('click', () => {
    searchInput.value = '';
    filterTable();
    clearBtn.style.display = 'none';
});

filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        filterTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        filterTable();
    });
});

function viewConversation(id) {
    window.open('/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=chat&id=' + id, '_blank');
}

function archiveConversation(id) {
    if (confirm('Archiver cette conversation ?')) {
        fetch('/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin&action=archive', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_conversation=' + id
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
    }
}

function deleteConversation(id) {
    if (confirm('Supprimer définitivement cette conversation ?')) {
        fetch('/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin&action=delete-conv', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_conversation=' + id
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
    }
}

function deleteMessage(id) {
    if (confirm('Supprimer ce message ?')) {
        fetch('/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin&action=delete-msg', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_message=' + id
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
    }
}

function ignoreMessage(id) {
    if (confirm('Ignorer ce signalement ?')) {
        fetch('/Esprit-PW-2A32-2526-TalentBridge-job/messagerie_index.php?page=admin&action=ignore-flag', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_message=' + id
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
    }
}
</script>

</body>
</html>