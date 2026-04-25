<?php
// controllers/ProfileController.php

require_once __DIR__ . '/UserController.php';

class ProfileController extends UserController {

    public function handleProfileActions(&$user, &$success) {
        $errors = [];
        $action = $_POST['action'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $action === '') {
            return [
                'action' => '',
                'errors' => [],
            ];
        }

        $userId = $user->getId();

        if ($action === 'update') {
            $updatedUser = new User(
                $_POST['nom'] ?? '',
                $_POST['prenom'] ?? '',
                $_POST['email'] ?? '',
                '',
                $user->getRole(),
                $_POST['bio'] ?? '',
                $user->getAvatar(),
                $user->getStatus()
            );
            $updatedUser->setId($userId);

            $errors = $this->validateProfileUser($updatedUser);

            if (empty($errors) && $updatedUser->getEmail() !== $user->getEmail() && $this->emailExists($updatedUser->getEmail())) {
                $this->addFieldError($errors, 'email', 'Cet email est deja utilise.');
            }

            if (empty($errors)) {
                $this->update($updatedUser);
                $_SESSION['user_nom'] = $updatedUser->getNom();
                $user = $this->getById($userId);
                $success = true;
            }
        } elseif ($action === 'avatar') {
            $errors = $this->handleAvatarUpload($user, $success);
        } elseif ($action === 'password') {
            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');
            $errors = $this->validatePasswordChange($newPassword, $confirmPassword);

            if (empty($errors)) {
                $this->updatePassword($userId, $newPassword);
                $_SESSION['pwd_changed_time'] = time();
                $success = true;
            }
        } elseif ($action === 'links') {
            if ($user->getRole() === 'freelancer') {
                $github = trim($_POST['github_url'] ?? '');
                $linkedin = trim($_POST['linkedin_url'] ?? '');

                if ($github !== '' && !filter_var($github, FILTER_VALIDATE_URL)) {
                    $this->addFieldError($errors, 'github_url', 'URL GitHub invalide.');
                }
                if ($linkedin !== '' && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
                    $this->addFieldError($errors, 'linkedin_url', 'URL LinkedIn invalide.');
                }

                if (empty($errors)) {
                    $linkUser = new User();
                    $linkUser->setId($userId);
                    $linkUser->setGithubUrl($github);
                    $linkUser->setLinkedinUrl($linkedin);
                    $this->updateLinks($linkUser);

                    $user = $this->getById($userId);
                    $success = true;
                }
            }
        } elseif ($action === 'files') {
            if ($user->getRole() === 'freelancer') {
                $errors = $this->handleDocumentsUpload($user, $success);
            }
        } elseif ($action === 'delete_link') {
            if ($user->getRole() === 'freelancer') {
                $field = $_POST['field'] ?? '';

                if (in_array($field, ['github_url', 'linkedin_url'], true)) {
                    $this->clearLink($userId, $field);
                    $user = $this->getById($userId);
                    $success = true;
                }
            }
        } elseif ($action === 'delete_file') {
            if ($user->getRole() === 'freelancer') {
                $field = $_POST['field'] ?? '';

                if (in_array($field, ['cv_url', 'portfolio_url'], true)) {
                    $existingFile = $field === 'cv_url' ? $user->getCvUrl() : $user->getPortfolioUrl();

                    if (!empty($existingFile)) {
                        $absolutePath = $this->projectPath($existingFile);
                        if (is_file($absolutePath)) {
                            @unlink($absolutePath);
                        }
                    }

                    $this->clearFile($userId, $field);
                    $user = $this->getById($userId);
                    $success = true;
                }
            }
        }

        return [
            'action' => $action,
            'errors' => $errors,
        ];
    }

    public function handleOnboardingLinks(&$user) {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $errors;
        }

        $userId = $user->getId();
        $github = trim($_POST['github_url'] ?? '');
        $linkedin = trim($_POST['linkedin_url'] ?? '');

        if ($github !== '' && !filter_var($github, FILTER_VALIDATE_URL)) {
            $this->addFieldError($errors, 'github_url', "Le lien GitHub n'est pas une URL valide.");
        }
        if ($linkedin !== '' && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
            $this->addFieldError($errors, 'linkedin_url', "Le lien LinkedIn n'est pas une URL valide.");
        }

        $files = $this->processDocumentUploads($user);
        $errors = $this->mergeErrors($errors, $files['errors']);

        if (!empty($errors)) {
            return $errors;
        }

        $linkUser = new User();
        $linkUser->setId($userId);
        $linkUser->setGithubUrl($github);
        $linkUser->setLinkedinUrl($linkedin);
        $this->updateLinks($linkUser);

        if ($files['cv_path'] !== null || $files['portfolio_path'] !== null) {
            $fileUser = new User();
            $fileUser->setId($userId);
            $fileUser->setCvUrl($files['cv_path']);
            $fileUser->setPortfolioUrl($files['portfolio_path']);
            $this->updateFiles($fileUser);
        }

        header('Location: profile.php?links=saved');
        exit;
    }
}
