<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../views/frontoffice/EmailApiService.php';
require_once __DIR__ . '/UserController.php';

class PasswordResetController {

    private $pdo;
    private $mailConfig;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->pdo        = config::getConnexion();
        $this->mailConfig = require __DIR__ . '/email_config.php';
        $this->ensureTable();
    }

    private function ensureTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS password_resets (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                email      VARCHAR(255) NOT NULL,
                code       VARCHAR(6)   NOT NULL,
                expires_at DATETIME     NOT NULL,
                used       TINYINT(1)   DEFAULT 0,
                created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email)
            )
        ");
    }

    // ──────────────────────────────────────────
    // Step 1: Generate code and send real email
    // ──────────────────────────────────────────
    public function handleForgotPassword() {
        $error   = '';
        $success = false;
        $mailErr = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Veuillez entrer une adresse email valide.";
            } else {
                $uc   = new UserController();
                $user = $uc->getByEmail($email);

                if ($user) {
                    $code    = sprintf('%06d', random_int(0, 999999));
                    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                    // Delete old codes
                    $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                    $stmt->execute([$email]);

                    // Save new code
                    $stmt = $this->pdo->prepare(
                        "INSERT INTO password_resets (email, code, expires_at) VALUES (?, ?, ?)"
                    );
                    $stmt->execute([$email, $code, $expires]);

                    // Send real email via the configured email API
                    $sent = $this->sendResetEmail($email, $user->getPrenom(), $code);
                    if (!$sent['ok']) {
                        $mailErr = $sent['error'];
                    }
                }

                $success = true;
                $_SESSION['reset_email']    = $email;
                $_SESSION['reset_mailerr']  = $mailErr;
            }
        }

        return [
            'error'    => $error,
            'success'  => $success,
            'mailErr'  => $_SESSION['reset_mailerr'] ?? '',
        ];
    }

    // ──────────────────────────────────────────
    // Step 2: Verify the code
    // ──────────────────────────────────────────
    public function handleVerifyCode() {
        $email = $_SESSION['reset_email'] ?? '';
        $error = '';
        $valid = false;

        if (empty($email)) {
            header('Location: forgot_password.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim($_POST['code'] ?? '');

            if (empty($code)) {
                $error = "Veuillez entrer le code reçu par email.";
            } else {
                $stmt = $this->pdo->prepare(
                    "SELECT * FROM password_resets
                     WHERE email = ? AND code = ? AND used = 0 AND expires_at > NOW()
                     LIMIT 1"
                );
                $stmt->execute([$email, $code]);
                $row = $stmt->fetch();

                if ($row) {
                    $_SESSION['reset_verified'] = true;
                    unset($_SESSION['reset_mailerr']);
                    $valid = true;
                } else {
                    $error = "Code incorrect ou expiré. Veuillez réessayer.";
                }
            }
        }

        return ['error' => $error, 'valid' => $valid, 'email' => $email];
    }

    // ──────────────────────────────────────────
    // Step 3: Save new password
    // ──────────────────────────────────────────
    public function handleResetPassword() {
        $email    = $_SESSION['reset_email']    ?? '';
        $verified = $_SESSION['reset_verified'] ?? false;
        $error    = '';
        $success  = false;

        if (empty($email) || !$verified) {
            header('Location: forgot_password.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password        = $_POST['password']         ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (strlen($password) < 8) {
                $error = "Le mot de passe doit contenir au moins 8 caractères.";
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $error = "Le mot de passe doit contenir au moins une lettre majuscule.";
            } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
                $error = "Le mot de passe doit contenir au moins un caractère spécial.";
            } elseif ($password !== $confirmPassword) {
                $error = "Les mots de passe ne correspondent pas.";
            } else {
                $uc   = new UserController();
                $user = $uc->getByEmail($email);

                if ($user) {
                    $uc->updatePassword($user->getId(), $password);

                    $stmt = $this->pdo->prepare(
                        "UPDATE password_resets SET used = 1 WHERE email = ?"
                    );
                    $stmt->execute([$email]);

                    unset($_SESSION['reset_email'], $_SESSION['reset_verified']);
                    $success = true;

                    // Automatically log in the user if their account is active
                    if ($user->getStatus() === 'active') {
                        $_SESSION['user_id']     = $user->getId();
                        $_SESSION['user_nom']    = $user->getNom();
                        $_SESSION['user_prenom'] = $user->getPrenom();
                        $_SESSION['user_role']   = $user->getRole();
                        
                        // We pass onboarding link to the view so the button knows where to go
                        $nextPage = ($user->getRole() === 'freelancer' && $user->getGithubUrl() === '' && $user->getLinkedinUrl() === '') 
                                    ? 'onboarding_links.php' : 'profile.php';
                    } else {
                        // If pending/banned/rejected, send them to the block page
                        $_SESSION['google_blocked'] = $user->getStatus();
                        $nextPage = 'google_blocked.php';
                    }
                } else {
                    $error = "Compte introuvable.";
                }
            }
        }

        return ['error' => $error, 'success' => $success, 'email' => $email, 'nextPage' => $nextPage ?? 'login.php'];
    }

    // ──────────────────────────────────────────
    // Send email via configured HTTP email API
    // ──────────────────────────────────────────
    private function sendResetEmail($email, $prenom, $code) {
        $emailApi = new EmailApiService($this->mailConfig);
        return $emailApi->sendPasswordReset($email, $prenom, $code);
    }
}
