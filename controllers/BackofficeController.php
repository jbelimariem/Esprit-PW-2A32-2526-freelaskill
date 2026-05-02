<?php
// controllers/BackofficeController.php

require_once __DIR__ . '/UserController.php';

class BackofficeController extends UserController {

    public function executeUsersDashboardPage() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $dashboardData = $this->handleAdminDashboard();
        $errors = $dashboardData['errors'];
        $activeModal = $dashboardData['form'] ?? '';
        $createErrors = $activeModal === 'create' ? $errors : [];
        $editErrors = $activeModal === 'update' ? $errors : [];
        $fieldError = function ($bag, $field) {
            return $bag[$field] ?? '';
        };
        $createValues = $activeModal === 'create' ? $_POST : [];
        $editValues = $activeModal === 'update' ? $_POST : [];

        $flash = $this->resolveFlashMessage($_GET['msg'] ?? '');

        $search = trim($_GET['search'] ?? '');
        $role = trim($_GET['role'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $activeFilters = $this->buildActiveFilters($search, $role, $status);
        $users = ($search !== '' || $role !== '' || $status !== '')
            ? $this->filter($search, $role, $status)
            : $this->getAll();

        $totalUsers = $this->countAll();
        $totalFreelancers = $this->countByRole('freelancer');
        $totalClients = $this->countByRole('client');
        $totalBanned = $this->countByStatus('banned');
        $totalActive = $this->countByStatus('active');
        $totalPending = $this->countByStatus('pending');
        $pctFreelancer = $totalUsers > 0 ? round(($totalFreelancers / $totalUsers) * 100) : 0;
        $pctClient = $totalUsers > 0 ? round(($totalClients / $totalUsers) * 100) : 0;
        $pctBanned = $totalUsers > 0 ? round(($totalBanned / $totalUsers) * 100) : 0;
        $pctActive = $totalUsers > 0 ? round(($totalActive / $totalUsers) * 100) : 0;
        $pendingUsers = $this->filter('', '', 'pending');

        $editUser = null;
        if (!empty($_GET['edit'])) {
            $editUser = $this->getById((int) $_GET['edit']);
        }

        if ($activeModal === 'update') {
            $editUser = (object) [
                'id' => (int) ($editValues['edit_id'] ?? 0),
                'prenom' => $editValues['prenom'] ?? '',
                'nom' => $editValues['nom'] ?? '',
                'email' => $editValues['email'] ?? '',
                'bio' => $editValues['bio'] ?? '',
                'role' => $editValues['role'] ?? 'freelancer',
                'status' => $editValues['status'] ?? 'active',
            ];
        }

        $chartLabels = [];
        $chartData = [];
        foreach (($dashboardData['chart_data'] ?? []) as $date => $count) {
            $chartLabels[] = date('d/m', strtotime($date));
            $chartData[] = $count;
        }

        include __DIR__ . '/../views/backoffice/users_dashboard.view.php';
    }

    private function resolveFlashMessage($messageKey) {
        $flashMap = [
            'created'  => ['Utilisateur cree', 'Le compte a ete ajoute avec succes.', 'success', 'fa-circle-check'],
            'updated'  => ['Profil mis a jour', 'Les informations du compte ont bien ete enregistrees.', 'success', 'fa-pen-to-square'],
            'ban'      => ['Compte suspendu', "L'utilisateur ne peut plus acceder a la plateforme pour le moment.", 'warning', 'fa-ban'],
            'activate' => ['Compte reactive', "L'utilisateur peut a nouveau acceder a son espace.", 'success', 'fa-bolt'],
            'delete'   => ['Compte supprime', 'Le compte a ete retire definitivement.', 'error', 'fa-trash-can'],
            'reject'   => ['Inscription refusee', "L'utilisateur a ete notifie du refus lors de sa prochaine connexion.", 'error', 'fa-xmark'],
        ];

        return $flashMap[$messageKey] ?? null;
    }

    private function buildActiveFilters($search, $role, $status) {
        $activeFilters = [];

        if ($search !== '') {
            $activeFilters[] = 'Recherche: ' . $search;
        }

        if ($role !== '') {
            $activeFilters[] = 'Role: ' . ucfirst($role);
        }

        if ($status !== '') {
            $statusLabels = [
                'active' => 'Actif',
                'banned' => 'Suspendu',
                'pending' => 'En attente',
                'rejected' => 'Refuse',
            ];
            $activeFilters[] = 'Statut: ' . ($statusLabels[$status] ?? $status);
        }

        return $activeFilters;
    }

    public function handleAdminDashboard() {
        $data = ['errors' => [], 'success' => '', 'form' => '', 'chart_data' => []];

        // Fetch registration stats for the area chart
        $data['chart_data'] = $this->getRegistrationStats(14);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['_action'] ?? '';

            if ($action === 'create') {
                $data['form'] = 'create';
                $newUser = new User(
                    $_POST['nom'] ?? '',
                    $_POST['prenom'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['password'] ?? '',
                    $_POST['role'] ?? '',
                    $_POST['bio'] ?? '',
                    '',
                    'active'
                );

                $data['errors'] = $this->validateAdminCreate($newUser);

                if (empty($data['errors']) && $this->emailExists($newUser->getEmail())) {
                    $this->addFieldError($data['errors'], 'email', 'Email deja utilise.');
                }

                if (empty($data['errors'])) {
                    $this->create($newUser);
                    header('Location: users_dashboard.php?msg=created');
                    exit;
                }
            } elseif ($action === 'update') {
                $data['form'] = 'update';
                $updatedUser = new User(
                    $_POST['nom'] ?? '',
                    $_POST['prenom'] ?? '',
                    $_POST['email'] ?? '',
                    '',
                    $_POST['role'] ?? '',
                    $_POST['bio'] ?? '',
                    '',
                    $_POST['status'] ?? ''
                );
                $updatedUser->setId((int) ($_POST['edit_id'] ?? 0));

                $data['errors'] = $this->validateAdminUpdate($updatedUser);

                $existing = $this->getById($updatedUser->getId());
                if (
                    empty($data['errors']) &&
                    $existing &&
                    $updatedUser->getEmail() !== $existing->getEmail() &&
                    $this->emailExists($updatedUser->getEmail())
                ) {
                    $this->addFieldError($data['errors'], 'email', 'Email deja utilise par un autre compte.');
                }

                if (empty($data['errors'])) {
                    $this->updateFull($updatedUser);
                    header('Location: users_dashboard.php?msg=updated');
                    exit;
                }
            }
        }

        if (!empty($_GET['action']) && !empty($_GET['id'])) {
            $targetId = (int) $_GET['id'];

            if ($_GET['action'] === 'ban') {
                $this->updateStatus($targetId, 'banned');
            }
            if ($_GET['action'] === 'activate') {
                $this->updateStatus($targetId, 'active');
            }
            if ($_GET['action'] === 'reject') {
                // Mark as rejected so the user sees a clear message on login
                $this->updateStatus($targetId, 'rejected');
            }
            if ($_GET['action'] === 'delete') {
                $this->delete($targetId);
            }

            header('Location: users_dashboard.php?msg=' . $_GET['action']);
            exit;
        }

        return $data;
    }

    public function getRegistrationStats($days = 14) {
        $sql = "SELECT DATE(created_at) as reg_date, COUNT(*) as total 
                FROM users 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(created_at) 
                ORDER BY DATE(created_at) ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$days]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fill in missing days with 0
        $stats = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stats[$date] = 0;
        }
        foreach ($results as $row) {
            $date = $row['reg_date'];
            if (isset($stats[$date])) {
                $stats[$date] = (int)$row['total'];
            }
        }
        return $stats;
    }
}
