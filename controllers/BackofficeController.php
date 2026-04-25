<?php
// controllers/BackofficeController.php

require_once __DIR__ . '/UserController.php';

class BackofficeController extends UserController {

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
