<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';
require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/UserController.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class PasswordResetController {

    private $pdo;
    private $mailConfig;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->pdo        = config::getConnexion();
        $this->mailConfig = require __DIR__ . '/../email_config.php';
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

                    // Send real email via PHPMailer
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
    // Send email via PHPMailer + Gmail SMTP
    // ──────────────────────────────────────────
    private function sendResetEmail($email, $prenom, $code) {
        $cfg = $this->mailConfig;

        try {
            $mail = new PHPMailer(true);

            // SMTP config
            $mail->isSMTP();
            $mail->Host       = $cfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['username'];
            $mail->Password   = $cfg['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $cfg['port'];
            $mail->CharSet    = 'UTF-8';

            // From / To
            $mail->setFrom($cfg['from_email'], $cfg['from_name']);
            $mail->addAddress($email, $prenom);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'FreelaSkill – Code de réinitialisation';
            $mail->Body    = $this->buildEmailHTML($prenom, $code);
            $mail->AltBody = "Bonjour $prenom,\n\nVotre code de vérification FreelaSkill : $code\n\nValable 15 minutes.";

            $mail->send();
            return ['ok' => true, 'error' => ''];

        } catch (Exception $e) {
            return ['ok' => false, 'error' => $mail->ErrorInfo];
        }
    }

    // ──────────────────────────────────────────
    // Beautiful HTML email template
    // ──────────────────────────────────────────
    private function buildEmailHTML($prenom, $code) {
        $digits = implode('', array_map(fn($c) => "
            <td style=\"width:48px;height:56px;text-align:center;vertical-align:middle;
                        background:#1e293b;border:2px solid #334155;border-radius:10px;
                        font-size:28px;font-weight:700;font-family:monospace;color:#ffffff;\">
                $c
            </td>
            <td style=\"width:8px;\"></td>
        ", str_split($code)));

        return "
        <!DOCTYPE html>
        <html>
        <body style=\"margin:0;padding:0;background:#0f172a;font-family:'Segoe UI',Arial,sans-serif;\">
        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
          <tr>
            <td align=\"center\" style=\"padding:40px 20px;\">
              <table width=\"520\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#1e293b;border-radius:20px;border:1px solid #334155;\">

                <!-- Header -->
                <tr>
                  <td align=\"center\" style=\"padding:36px 40px 24px;\">
                    <div style=\"font-size:22px;font-weight:700;color:#ffffff;\">
                      <span style=\"color:#ef4444;\">■</span> Freela<span style=\"color:#3b82f6;\">Skill</span>
                    </div>
                  </td>
                </tr>

                <!-- Body -->
                <tr>
                  <td style=\"padding:0 40px 32px;\">
                    <p style=\"font-size:16px;color:#94a3b8;margin:0 0 8px;\">Bonjour <strong style=\"color:#ffffff;\">$prenom</strong>,</p>
                    <p style=\"font-size:15px;color:#94a3b8;margin:0 0 28px;line-height:1.6;\">
                      Vous avez demandé la réinitialisation de votre mot de passe.<br>
                      Utilisez ce code pour confirmer votre identité :
                    </p>

                    <!-- Code -->
                    <table cellpadding=\"0\" cellspacing=\"0\" style=\"margin:0 auto 28px;\">
                      <tr>$digits</tr>
                    </table>

                    <div style=\"background:#0f172a;border:1px solid #334155;border-radius:12px;padding:14px 20px;text-align:center;\">
                      <span style=\"font-size:13px;color:#64748b;\">
                        ⏱ Ce code expire dans <strong style=\"color:#f59e0b;\">15 minutes</strong>
                      </span>
                    </div>

                    <p style=\"font-size:13px;color:#475569;margin:24px 0 0;line-height:1.6;\">
                      Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.<br>
                      Votre mot de passe ne sera pas modifié.
                    </p>
                  </td>
                </tr>

                <!-- Footer -->
                <tr>
                  <td style=\"border-top:1px solid #1e3a5f;padding:20px 40px;text-align:center;\">
                    <p style=\"font-size:12px;color:#334155;margin:0;\">© 2025 FreelaSkill — Tous droits réservés</p>
                  </td>
                </tr>

              </table>
            </td>
          </tr>
        </table>
        </body>
        </html>
        ";
    }
}
