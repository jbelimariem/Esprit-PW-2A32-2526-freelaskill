<?php
// controllers/AuthController.php

require_once __DIR__ . '/UserController.php';

class AuthController extends UserController {

    private $maxAttempts = 5;
    private $lockoutSeconds = 900; // 15 minutes

    private function startSessionIfNeeded() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function redirectAuthenticatedUser() {
        if (!empty($_SESSION['user_id'])) {
            header('Location: profile.php');
            exit;
        }
    }

    public function executeLoginPage() {
        $this->startSessionIfNeeded();
        $this->redirectAuthenticatedUser();

        $errors = $this->handleLogin();
        $fieldError = function ($field) use ($errors) {
            return $errors[$field] ?? '';
        };

        include __DIR__ . '/../views/frontoffice/login.view.php';
    }

    public function executeRegisterPage() {
        $this->startSessionIfNeeded();
        $this->redirectAuthenticatedUser();

        $errors = $this->handleRegister();
        $fieldError = function ($field) use ($errors) {
            return $errors[$field] ?? '';
        };
        $data = $this->buildRegisterFormData();

        include __DIR__ . '/../views/frontoffice/register.view.php';
    }

    private function buildRegisterFormData() {
        $data = [];

        if (isset($_GET['role']) && in_array($_GET['role'], ['freelancer', 'client'], true)) {
            $data['role'] = $_GET['role'];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data['nom'] = $_POST['nom'] ?? '';
            $data['prenom'] = $_POST['prenom'] ?? '';
            $data['email'] = $_POST['email'] ?? '';
            $data['role'] = $_POST['role'] ?? '';
            $data['bio'] = $_POST['bio'] ?? '';
        }

        return $data;
    }

    // -------------------------------------------------------
    // Rate limiting helpers (session-based per IP)
    // -------------------------------------------------------
    private function getRateLimitKey() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return 'login_attempts_' . md5($ip);
    }

    public function isLockedOut() {
        $key = $this->getRateLimitKey();
        $data = $_SESSION[$key] ?? null;

        if (!$data) {
            return false;
        }

        if ($data['attempts'] >= $this->maxAttempts) {
            $elapsed = time() - $data['last_attempt'];
            if ($elapsed < $this->lockoutSeconds) {
                return true;
            }
            // Lockout expired — reset
            unset($_SESSION[$key]);
        }

        return false;
    }

    public function getLockoutSecondsRemaining() {
        $key = $this->getRateLimitKey();
        $data = $_SESSION[$key] ?? null;

        if (!$data) {
            return 0;
        }

        $elapsed = time() - $data['last_attempt'];
        $remaining = $this->lockoutSeconds - $elapsed;
        return max(0, $remaining);
    }

    public function getAttemptCount() {
        $key = $this->getRateLimitKey();
        $data = $_SESSION[$key] ?? null;
        return $data ? $data['attempts'] : 0;
    }

    private function recordFailedAttempt() {
        $key = $this->getRateLimitKey();
        $data = $_SESSION[$key] ?? ['attempts' => 0, 'last_attempt' => 0];
        $data['attempts']++;
        $data['last_attempt'] = time();
        $_SESSION[$key] = $data;
    }

    private function resetAttempts() {
        $key = $this->getRateLimitKey();
        unset($_SESSION[$key]);
    }

    // -------------------------------------------------------
    // Login
    // -------------------------------------------------------
    public function handleLogin() {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $errors;
        }

        // Check lockout FIRST
        if ($this->isLockedOut()) {
            $remaining = $this->getLockoutSecondsRemaining();
            $minutes = ceil($remaining / 60);
            return ['_global' => "Trop de tentatives. Compte bloque.", '_locked' => $remaining];
        }

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $isGoogle = !empty($_POST['google_login']);

        if ($email === '') {
            $this->addFieldError($errors, 'email', "L'email est requis.");
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFieldError($errors, 'email', 'Email invalide.');
        }
        
        if (!$isGoogle && $password === '') {
            $this->addFieldError($errors, 'password', 'Le mot de passe est requis.');
        }

        if (!empty($errors)) {
            return $errors;
        }

        if ($isGoogle) {
            $user = $this->getByEmail($email);
            if (!$user) {
                return ['_global' => "Ce compte Google n'est pas inscrit."];
            }
        } else {
            $user = $this->login($email, $password);

            if (!$user) {
                $this->recordFailedAttempt();
                $remaining = $this->maxAttempts - $this->getAttemptCount();

                if ($this->isLockedOut()) {
                    return ['_global' => "Trop de tentatives. Compte bloque.", '_locked' => $this->lockoutSeconds];
                }

                $warn = $remaining <= 2 ? " ({$remaining} tentative(s) restante(s))" : '';
                return ['_global' => 'Email ou mot de passe incorrect.' . $warn];
            }
        }

        if ($user->getStatus() === 'banned') {
            $this->recordFailedAttempt();
            return ['_global' => "Votre compte a ete suspendu. Contactez l'administrateur."];
        }

        if ($user->getStatus() === 'rejected') {
            return ['_global' => "Votre demande d'inscription a ete refusee par l'administrateur.", '_rejected' => true];
        }

        if ($user->getStatus() === 'pending') {
            return ['_global' => "Votre compte est en attente de validation par un administrateur.", '_pending' => true];
        }

        // Success — reset attempts
        $this->resetAttempts();

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_nom'] = $user->getNom();
        $_SESSION['user_prenom'] = $user->getPrenom();
        $_SESSION['user_role'] = $user->getRole();

        if ($user->getRole() === 'freelancer' && $user->getGithubUrl() === '' && $user->getLinkedinUrl() === '') {
            header('Location: onboarding_links.php');
        } else {
            header('Location: profile.php');
        }

        exit;
    }

    // -------------------------------------------------------
    // Register
    // -------------------------------------------------------
    public function handleRegister() {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $errors;
        }

        $user = new User(
            $_POST['nom'] ?? '',
            $_POST['prenom'] ?? '',
            $_POST['email'] ?? '',
            $_POST['password'] ?? '',
            $_POST['role'] ?? '',
            $_POST['bio'] ?? '',
            '',
            'pending'
        );

        $confirmPassword = trim($_POST['confirm_password'] ?? '');
        $errors = $this->validateRegistrationUser($user, $confirmPassword);

        if (empty($errors) && $this->emailExists($user->getEmail())) {
            $this->addFieldError($errors, 'email', 'Cet email est deja utilise.');
        }

        if (!empty($errors)) {
            return $errors;
        }

        $this->create($user);

        // Account is pending — do NOT create a session yet.
        // Redirect to the waiting page so user knows to wait for admin approval.
        $_SESSION['pending_email'] = $user->getEmail();
        $_SESSION['pending_name']  = $user->getPrenom();
        header('Location: pending.php');
        exit;
    }
}

