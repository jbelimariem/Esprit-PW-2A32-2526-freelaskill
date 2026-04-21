<?php
// controllers/BackofficeController.php

require_once __DIR__ . '/UserController.php';

class BackofficeController extends UserController
{
    public function handleAdminDashboard()
    {
        $data = ['errors' => [], 'success' => '', 'form' => ''];
        $userModel = $this->userModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['_action'] ?? '';

            if ($action === 'create') {
                $data['form'] = 'create';
                $newUser = (new User())
                    ->setNom($_POST['nom'] ?? '')
                    ->setPrenom($_POST['prenom'] ?? '')
                    ->setEmail($_POST['email'] ?? '')
                    ->setPassword($_POST['password'] ?? '')
                    ->setRole($_POST['role'] ?? '')
                    ->setBio($_POST['bio'] ?? '')
                    ->setAvatar('')
                    ->setStatus('active');

                $data['errors'] = $this->validateAdminCreate($newUser);

                if (empty($data['errors']) && $userModel->emailExists($newUser->getEmail())) {
                    $this->addFieldError($data['errors'], 'email', 'Email deja utilise.');
                }

                if (empty($data['errors'])) {
                    $userModel->create($newUser);
                    header('Location: users_dashboard.php?msg=created');
                    exit;
                }
            } elseif ($action === 'update') {
                $data['form'] = 'update';
                $updatedUser = (new User())
                    ->setId((int) ($_POST['edit_id'] ?? 0))
                    ->setNom($_POST['nom'] ?? '')
                    ->setPrenom($_POST['prenom'] ?? '')
                    ->setEmail($_POST['email'] ?? '')
                    ->setBio($_POST['bio'] ?? '')
                    ->setRole($_POST['role'] ?? '')
                    ->setStatus($_POST['status'] ?? '');

                $data['errors'] = $this->validateAdminUpdate($updatedUser);

                $existing = $userModel->getById($updatedUser->getId());
                if (
                    empty($data['errors']) &&
                    $existing &&
                    $updatedUser->getEmail() !== $existing->getEmail() &&
                    $userModel->emailExists($updatedUser->getEmail())
                ) {
                    $this->addFieldError($data['errors'], 'email', 'Email deja utilise par un autre compte.');
                }

                if (empty($data['errors'])) {
                    $userModel->updateFull($updatedUser);
                    header('Location: users_dashboard.php?msg=updated');
                    exit;
                }
            }
        }

        if (!empty($_GET['action']) && !empty($_GET['id'])) {
            $targetId = (int) $_GET['id'];

            if ($_GET['action'] === 'ban') {
                $userModel->updateStatus($targetId, 'banned');
            }
            if ($_GET['action'] === 'activate') {
                $userModel->updateStatus($targetId, 'active');
            }
            if ($_GET['action'] === 'delete') {
                $userModel->delete($targetId);
            }

            header('Location: users_dashboard.php?msg=' . $_GET['action']);
            exit;
        }

        return $data;
    }
}
