<?php
// controllers/ProfileController.php

require_once __DIR__ . '/UserController.php';
require_once __DIR__ . '/../views/frontoffice/GroqService.php';

class ProfileController extends UserController {

    public function executeProfilePage() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        $user = $this->getById($userId);

        if (!$user) {
            session_destroy();
            header('Location: login.php');
            exit;
        }

        $success = false;
        $profileResult = $this->handleProfileActions($user, $success);
        $profileAction = $profileResult['action'] ?? '';
        $errors = $profileResult['errors'] ?? [];
        $aiBioGenerated = $profileResult['generated_bio'] ?? '';
        $aiBioNotice = $profileResult['notice'] ?? '';
        $updateErrors = $profileAction === 'update' ? $errors : [];
        $avatarErrors = $profileAction === 'avatar' ? $errors : [];
        $passwordErrors = $profileAction === 'password' ? $errors : [];
        $linksErrors = $profileAction === 'links' ? $errors : [];
        $filesErrors = $profileAction === 'files' ? $errors : [];
        $fieldError = function ($bag, $field) {
            return $bag[$field] ?? '';
        };

        $initials = strtoupper(mb_substr($user->getPrenom(), 0, 1) . mb_substr($user->getNom(), 0, 1));
        $roleBadge = $this->buildRoleBadge($user);
        $statusBadge = $this->buildStatusBadge($user);
        $memberSince = date('d/m/Y', strtotime($user->getCreatedAt()));
        $avatarUrl = $this->buildAvatarWebPath($user->getAvatar() ?? '');
        $hasAvatar = $avatarUrl !== '';
        $activeProfileTab = $this->resolveActiveProfileTab($profileAction);
        $profileFormValues = [
            'nom' => $_POST['nom'] ?? $user->getNom(),
            'prenom' => $_POST['prenom'] ?? $user->getPrenom(),
            'email' => $_POST['email'] ?? $user->getEmail(),
            'bio' => $_POST['bio'] ?? ($user->getBio() ?? ''),
            'github_url' => $_POST['github_url'] ?? ($user->getGithubUrl() ?? ''),
            'linkedin_url' => $_POST['linkedin_url'] ?? ($user->getLinkedinUrl() ?? ''),
        ];
        $profileSocialLinks = [
            'github_url' => $user->getGithubUrl(),
            'linkedin_url' => $user->getLinkedinUrl(),
        ];
        $profileDocuments = [
            'cv_url' => $user->getCvUrl(),
            'portfolio_url' => $user->getPortfolioUrl(),
        ];
        $passwordChangedTime = $_SESSION['pwd_changed_time'] ?? null;

        include __DIR__ . '/../views/frontoffice/profile.view.php';
    }

    private function buildAvatarWebPath($avatar) {
        $avatar = trim((string) $avatar);
        if ($avatar === '') {
            return '';
        }

        $relativePath = ltrim(str_replace('\\', '/', $avatar), '/');
        $absolutePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        return is_file($absolutePath) ? '../../' . $relativePath : '';
    }

    private function buildRoleBadge(User $user) {
        if ($user->getRole() === 'freelancer') {
            return '<span class="badge badge-freelancer"><i class="fa-solid fa-laptop-code"></i> Freelancer</span>';
        }

        return '<span class="badge badge-client"><i class="fa-solid fa-building"></i> Client</span>';
    }

    private function buildStatusBadge(User $user) {
        if ($user->getStatus() === 'active') {
            return '<span class="badge badge-active"><i class="fa-solid fa-circle-check"></i> Actif</span>';
        }

        if ($user->getStatus() === 'banned') {
            return '<span class="badge badge-banned"><i class="fa-solid fa-ban"></i> Suspendu</span>';
        }

        return '<span class="badge badge-pending"><i class="fa-solid fa-clock"></i> En attente</span>';
    }

    private function resolveActiveProfileTab($profileAction) {
        if ($profileAction === 'password') {
            return 'security';
        }

        if (in_array($profileAction, ['links', 'files'], true)) {
            return 'networks';
        }

        return 'info';
    }

    public function executeOnboardingLinksPage() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        if (($_SESSION['user_role'] ?? '') !== 'freelancer') {
            header('Location: profile.php');
            exit;
        }

        $user = $this->getById((int) $_SESSION['user_id']);
        if (!$user) {
            session_destroy();
            header('Location: login.php');
            exit;
        }

        if ((!empty($user->getGithubUrl()) || !empty($user->getLinkedinUrl())) && empty($_GET['force'])) {
            header('Location: profile.php');
            exit;
        }

        $errors = $this->handleOnboardingLinks($user);
        $fieldError = function ($field) use ($errors) {
            return $errors[$field] ?? '';
        };
        $onboardingValues = [
            'github_url' => $_POST['github_url'] ?? ($user->getGithubUrl() ?? ''),
            'linkedin_url' => $_POST['linkedin_url'] ?? ($user->getLinkedinUrl() ?? ''),
        ];

        include __DIR__ . '/../views/frontoffice/onboarding_links.view.php';
    }

    public function handleProfileActions(&$user, &$success) {
        $errors = [];
        $action = $_POST['action'] ?? '';
        $generatedBio = '';
        $notice = '';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $action === '') {
            return [
                'action' => '',
                'errors' => [],
                'generated_bio' => '',
                'notice' => '',
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
        } elseif ($action === 'generate_bio') {
            $draftUser = $this->buildProfileDraftUser($user);
            $groq = new GroqService();

            if (!$groq->isConfigured()) {
                $errors['_global'] = 'Groq n est pas configure. Ajoutez votre cle API dans controllers/config.local.php.';
            } else {
                try {
                    $generatedBio = $groq->generateProfileBio($draftUser, $_POST['bio'] ?? $user->getBio());
                    $_POST['bio'] = $generatedBio;
                    $notice = 'Bio generee par IA. Relisez-la puis cliquez sur Sauvegarder pour l enregistrer.';
                } catch (RuntimeException $e) {
                    $errors['_global'] = $e->getMessage();
                }
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
            'generated_bio' => $generatedBio,
            'notice' => $notice,
        ];
    }

    private function buildProfileDraftUser(User $user) {
        $draftUser = new User(
            $_POST['nom'] ?? $user->getNom(),
            $_POST['prenom'] ?? $user->getPrenom(),
            $_POST['email'] ?? $user->getEmail(),
            '',
            $user->getRole(),
            $_POST['bio'] ?? ($user->getBio() ?? ''),
            $user->getAvatar(),
            $user->getStatus(),
            $_POST['github_url'] ?? ($user->getGithubUrl() ?? ''),
            $_POST['linkedin_url'] ?? ($user->getLinkedinUrl() ?? ''),
            $user->getCvUrl(),
            $user->getPortfolioUrl()
        );
        $draftUser->setId($user->getId());
        $draftUser->setCreatedAt($user->getCreatedAt());
        $draftUser->setFaceDescriptor($user->getFaceDescriptor());

        return $draftUser;
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
