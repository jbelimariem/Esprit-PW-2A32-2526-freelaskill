<?php
/**
 * EmailService — Système de notifications par email (SMTP)
 * Utilise PHPMailer avec Gmail SMTP
 * 5 événements : création, escrow, validation, alerte délai, changement statut
 */

// Charger PHPMailer
$phpMailerPath = __DIR__ . '/PHPMailer/';
if (file_exists($phpMailerPath . 'PHPMailer.php')) {
    require_once $phpMailerPath . 'Exception.php';
    require_once $phpMailerPath . 'PHPMailer.php';
    require_once $phpMailerPath . 'SMTP.php';
    define('PHPMAILER_AVAILABLE', true);
} else {
    define('PHPMAILER_AVAILABLE', false);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {

    // ── Configuration SMTP ────────────────────────────────────────────
    // Modifiez ces valeurs avec votre compte Gmail
    private string $smtpHost     = 'smtp.gmail.com';
    private int    $smtpPort     = 587;
    private string $smtpUser     = 'aafrahawat@gmail.com';
    private string $smtpPassword = 'iqmxgiajwhdkxbqd';
    private string $fromName     = 'FreelaSkill';
    private string $fromEmail    = 'aafrahawat@gmail.com';

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->loadConfig();
    }

    // ── Charger la config depuis la DB ────────────────────────────────
    private function loadConfig(): void {
        try {
            $stmt = $this->pdo->query("SELECT cle, valeur FROM email_config");
            if ($stmt) {
                foreach ($stmt->fetchAll() as $row) {
                    match($row['cle']) {
                        'smtp_user'     => $this->smtpUser     = $row['valeur'],
                        'smtp_password' => $this->smtpPassword = $row['valeur'],
                        'from_name'     => $this->fromName     = $row['valeur'],
                        default         => null
                    };
                }
                $this->fromEmail = $this->smtpUser;
            }
        } catch (PDOException $e) {
            // Table pas encore créée — utiliser les valeurs par défaut
        }
    }

    // ════════════════════════════════════════════════════════════════
    // ÉVÉNEMENT 1 : Création de contrat
    // ════════════════════════════════════════════════════════════════
    public function notifyContratCreated(array $contrat, string $toEmail, string $toName = 'Freelancer'): bool {
        $subject = "📄 Nouveau contrat disponible — {$contrat['titre']}";
        $body    = $this->templateContratCreated($contrat, $toName);
        return $this->send($toEmail, $toName, $subject, $body, 'contrat_created', $contrat['id_contrat']);
    }

    // ════════════════════════════════════════════════════════════════
    // ÉVÉNEMENT 2 : Paiement bloqué (Escrow)
    // ════════════════════════════════════════════════════════════════
    public function notifyEscrowBloque(array $contrat, string $toEmail, string $toName = 'Freelancer'): bool {
        $subject = "🔒 Paiement sécurisé — Vous pouvez commencer le travail";
        $body    = $this->templateEscrowBloque($contrat, $toName);
        return $this->send($toEmail, $toName, $subject, $body, 'escrow_bloque', $contrat['id_contrat']);
    }

    // ════════════════════════════════════════════════════════════════
    // ÉVÉNEMENT 3 : Travail validé — paiement libéré
    // ════════════════════════════════════════════════════════════════
    public function notifyPaiementLibere(array $contrat, string $toEmail, string $toName = 'Freelancer'): bool {
        $subject = "✅ Paiement libéré — Travail validé par le client";
        $body    = $this->templatePaiementLibere($contrat, $toName);
        return $this->send($toEmail, $toName, $subject, $body, 'paiement_libere', $contrat['id_contrat']);
    }

    // ════════════════════════════════════════════════════════════════
    // ÉVÉNEMENT 4 : Alerte délai (⏱️ feature avancée)
    // ════════════════════════════════════════════════════════════════
    public function notifyAlerteDelai(array $contrat, int $joursRestants, string $toEmail, string $toName): bool {
        $urgence = $joursRestants <= 2 ? '🚨 URGENT' : '⚠️ Attention';
        $subject = "$urgence — Le contrat \"{$contrat['titre']}\" expire dans $joursRestants jour(s)";
        $body    = $this->templateAlerteDelai($contrat, $joursRestants, $toName);
        return $this->send($toEmail, $toName, $subject, $body, 'alerte_delai', $contrat['id_contrat']);
    }

    // ════════════════════════════════════════════════════════════════
    // ÉVÉNEMENT 5 : Changement de statut
    // ════════════════════════════════════════════════════════════════
    public function notifyStatutChange(array $contrat, string $ancienStatut, string $nouveauStatut, string $toEmail, string $toName): bool {
        $subject = "🔄 Statut modifié — {$contrat['titre']}";
        $body    = $this->templateStatutChange($contrat, $ancienStatut, $nouveauStatut, $toName);
        return $this->send($toEmail, $toName, $subject, $body, 'statut_change', $contrat['id_contrat']);
    }

    // ════════════════════════════════════════════════════════════════
    // Vérifier les alertes délai pour tous les contrats actifs
    // ════════════════════════════════════════════════════════════════
    public function checkAndSendDelaiAlerts(): array {
        $sent = [];
        try {
            // Contrats actifs dont le délai approche (3, 2, 1 jours)
            $stmt = $this->pdo->query(
                "SELECT * FROM contrat
                 WHERE statut = 'actif'
                 AND delai > 0
                 AND date_creation IS NOT NULL"
            );
            $contrats = $stmt->fetchAll();

            foreach ($contrats as $c) {
                $dateCreation  = new DateTime($c['date_creation']);
                $dateExpiry    = (clone $dateCreation)->modify('+' . intval($c['delai']) . ' days');
                $today         = new DateTime();
                $diff          = $today->diff($dateExpiry);
                $joursRestants = (int)$diff->format('%r%a'); // négatif si expiré

                // Alerter à J-3, J-2, J-1
                if ($joursRestants >= 0 && $joursRestants <= 3) {
                    // Vérifier si alerte déjà envoyée aujourd'hui
                    if (!$this->alerteDejaEnvoyee($c['id_contrat'], 'alerte_delai', $joursRestants)) {
                        // Email simulé (freelancer_info contient le nom)
                        $freelancerEmail = $this->extractEmail($c['freelance_info'] ?? '');
                        $freelancerName  = $this->extractName($c['freelance_info'] ?? 'Freelancer');

                        if ($freelancerEmail) {
                            $this->notifyAlerteDelai($c, $joursRestants, $freelancerEmail, $freelancerName);
                            $sent[] = "Alerte J-{$joursRestants} envoyée pour : {$c['titre']}";
                        } else {
                            // Pas d'email réel — log quand même
                            $this->logEmail($c['id_contrat'], 'alerte_delai', 'demo@freelaskill.com',
                                "⚠️ Alerte délai J-{$joursRestants} — {$c['titre']}", true);
                            $sent[] = "Alerte J-{$joursRestants} loggée pour : {$c['titre']}";
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            // Silencieux
        }
        return $sent;
    }

    // ════════════════════════════════════════════════════════════════
    // Envoi SMTP
    // ════════════════════════════════════════════════════════════════
    private function send(string $toEmail, string $toName, string $subject, string $body, string $type, int $idContrat): bool {
        $success = false;

        if (PHPMAILER_AVAILABLE && !empty($this->smtpPassword) && $this->smtpPassword !== 'your_app_password_here') {
            // Envoi réel via SMTP
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $this->smtpHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $this->smtpUser;
                $mail->Password   = $this->smtpPassword;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $this->smtpPort;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom($this->fromEmail, $this->fromName);
                $mail->addAddress($toEmail, $toName);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $body));

                $mail->send();
                $success = true;
            } catch (Exception $e) {
                $success = false;
            }
        } else {
            // Mode simulation — log sans envoi réel
            $success = true; // simulé
        }

        // Toujours logger
        $this->logEmail($idContrat, $type, $toEmail, $subject, $success);
        return $success;
    }

    // ════════════════════════════════════════════════════════════════
    // Logger les emails envoyés
    // ════════════════════════════════════════════════════════════════
    private function logEmail(int $idContrat, string $type, string $toEmail, string $subject, bool $success): void {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO email_logs (id_contrat, type_email, to_email, subject, statut, date_envoi)
                 VALUES (:id_contrat, :type_email, :to_email, :subject, :statut, NOW())"
            );
            $stmt->execute([
                'id_contrat' => $idContrat,
                'type_email' => $type,
                'to_email'   => $toEmail,
                'subject'    => $subject,
                'statut'     => $success ? 'envoye' : 'echec',
            ]);
        } catch (PDOException $e) {
            // Table pas encore créée
        }
    }

    private function alerteDejaEnvoyee(int $idContrat, string $type, int $joursRestants): bool {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM email_logs
                 WHERE id_contrat = :id AND type_email = :type
                 AND DATE(date_envoi) = CURDATE()
                 AND subject LIKE :jours"
            );
            $stmt->execute([
                'id'    => $idContrat,
                'type'  => $type,
                'jours' => "%J-{$joursRestants}%",
            ]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // ── Récupérer tous les logs ───────────────────────────────────────
    public function getLogs(int $limit = 50): array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT l.*, c.titre as titre_contrat
                 FROM email_logs l
                 LEFT JOIN contrat c ON l.id_contrat = c.id_contrat
                 ORDER BY l.date_envoi DESC
                 LIMIT :limit"
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getStats(): array {
        try {
            $stmt = $this->pdo->query(
                "SELECT
                    COUNT(*) as total,
                    SUM(statut = 'envoye') as envoyes,
                    SUM(statut = 'echec')  as echecs,
                    COUNT(DISTINCT type_email) as types
                 FROM email_logs"
            );
            return $stmt->fetch() ?: ['total'=>0,'envoyes'=>0,'echecs'=>0,'types'=>0];
        } catch (PDOException $e) {
            return ['total'=>0,'envoyes'=>0,'echecs'=>0,'types'=>0];
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────
    private function extractEmail(string $info): string {
        preg_match('/[\w.+-]+@[\w-]+\.[a-z]{2,}/i', $info, $m);
        return $m[0] ?? '';
    }

    private function extractName(string $info): string {
        $parts = preg_split('/[-–|,]/', $info);
        return trim($parts[0] ?? $info);
    }

    public function isConfigured(): bool {
        return !empty($this->smtpPassword) && $this->smtpPassword !== 'your_app_password_here';
    }

    public function getSmtpUser(): string { return $this->smtpUser; }

    // ════════════════════════════════════════════════════════════════
    // TEMPLATES HTML
    // ════════════════════════════════════════════════════════════════

    private function baseTemplate(string $content, string $accentColor = '#2563EB'): string {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8">
        <style>
            body { margin:0; padding:0; background:#f1f5f9; font-family: Arial, sans-serif; }
            .wrapper { max-width:600px; margin:0 auto; padding:20px; }
            .card { background:white; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); }
            .header { background:linear-gradient(135deg,' . $accentColor . ', #1D4ED8); padding:32px 28px; text-align:center; }
            .header h1 { color:white; margin:0; font-size:22px; font-weight:700; }
            .header p { color:rgba(255,255,255,0.8); margin:8px 0 0; font-size:14px; }
            .body { padding:28px; }
            .info-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f1f5f9; font-size:14px; }
            .info-label { color:#64748b; font-weight:600; }
            .info-value { color:#1e293b; font-weight:500; }
            .badge { display:inline-block; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:700; }
            .btn { display:inline-block; padding:12px 28px; background:' . $accentColor . '; color:white; text-decoration:none; border-radius:999px; font-weight:700; font-size:14px; margin-top:20px; }
            .footer { background:#f8fafc; padding:16px 28px; text-align:center; font-size:12px; color:#94a3b8; border-top:1px solid #e2e8f0; }
            .alert-box { background:#FEF3C7; border:1px solid #F59E0B; border-radius:10px; padding:14px 18px; margin:16px 0; }
            .success-box { background:#D1FAE5; border:1px solid #10B981; border-radius:10px; padding:14px 18px; margin:16px 0; }
        </style></head><body>
        <div class="wrapper">
            <div style="text-align:center;padding:16px 0;">
                <span style="font-family:Arial;font-size:20px;font-weight:900;color:#1e293b;">Freela<span style="color:' . $accentColor . '">Skill</span></span>
            </div>
            <div class="card">' . $content . '</div>
            <div style="text-align:center;padding:16px;font-size:11px;color:#94a3b8;">
                © ' . date('Y') . ' FreelaSkill — Plateforme de gestion de contrats freelance<br>
                Cet email a été envoyé automatiquement, merci de ne pas y répondre.
            </div>
        </div></body></html>';
    }

    private function templateContratCreated(array $c, string $name): string {
        $budget = number_format($c['budget'], 2, ',', ' ');
        $content = '
        <div class="header">
            <h1>📄 Nouveau contrat disponible</h1>
            <p>Un client vous a proposé un contrat</p>
        </div>
        <div class="body">
            <p style="color:#1e293b;font-size:15px;">Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
            <p style="color:#64748b;font-size:14px;line-height:1.6;">Un nouveau contrat vous a été assigné sur la plateforme FreelaSkill. Voici les détails :</p>
            <div style="background:#f8fafc;border-radius:10px;padding:16px;margin:16px 0;">
                <div class="info-row"><span class="info-label">Titre</span><span class="info-value">' . htmlspecialchars($c['titre']) . '</span></div>
                <div class="info-row"><span class="info-label">Budget</span><span class="info-value" style="color:#2563EB;font-weight:700;">' . $budget . ' DT</span></div>
                <div class="info-row"><span class="info-label">Délai</span><span class="info-value">' . intval($c['delai']) . ' jours</span></div>
                <div class="info-row"><span class="info-label">Statut</span><span class="badge" style="background:#EFF6FF;color:#2563EB;">Brouillon</span></div>
            </div>
            <p style="color:#64748b;font-size:13px;">Connectez-vous à votre espace pour consulter les détails et signer le contrat.</p>
            <center><a href="#" class="btn">Voir le contrat</a></center>
        </div>
        <div class="footer">FreelaSkill · Notification automatique · Contrat #' . $c['id_contrat'] . '</div>';
        return $this->baseTemplate($content, '#2563EB');
    }

    private function templateEscrowBloque(array $c, string $name): string {
        $budget = number_format($c['budget'], 2, ',', ' ');
        $content = '
        <div class="header" style="background:linear-gradient(135deg,#059669,#10B981);">
            <h1>🔒 Paiement sécurisé en séquestre</h1>
            <p>Le client a déposé le paiement — vous pouvez commencer</p>
        </div>
        <div class="body">
            <p style="color:#1e293b;font-size:15px;">Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
            <div class="success-box">
                <strong style="color:#065F46;">✅ Bonne nouvelle !</strong>
                <p style="margin:6px 0 0;color:#065F46;font-size:13px;">Le client a sécurisé le paiement de <strong>' . $budget . ' DT</strong> en séquestre. Vous pouvez commencer le travail en toute confiance.</p>
            </div>
            <div style="background:#f8fafc;border-radius:10px;padding:16px;margin:16px 0;">
                <div class="info-row"><span class="info-label">Contrat</span><span class="info-value">' . htmlspecialchars($c['titre']) . '</span></div>
                <div class="info-row"><span class="info-label">Montant bloqué</span><span class="info-value" style="color:#10B981;font-weight:700;">' . $budget . ' DT</span></div>
                <div class="info-row"><span class="info-label">Délai</span><span class="info-value">' . intval($c['delai']) . ' jours</span></div>
            </div>
            <p style="color:#64748b;font-size:13px;">Le paiement sera libéré automatiquement après validation de votre travail par le client.</p>
            <center><a href="#" class="btn" style="background:#10B981;">Accéder au contrat</a></center>
        </div>
        <div class="footer">FreelaSkill · Escrow sécurisé · Contrat #' . $c['id_contrat'] . '</div>';
        return $this->baseTemplate($content, '#10B981');
    }

    private function templatePaiementLibere(array $c, string $name): string {
        $budget = number_format($c['budget'], 2, ',', ' ');
        $content = '
        <div class="header" style="background:linear-gradient(135deg,#7C3AED,#6D28D9);">
            <h1>🎉 Paiement libéré !</h1>
            <p>Votre travail a été validé par le client</p>
        </div>
        <div class="body">
            <p style="color:#1e293b;font-size:15px;">Félicitations <strong>' . htmlspecialchars($name) . '</strong> !</p>
            <div class="success-box" style="background:#EDE9FE;border-color:#7C3AED;">
                <strong style="color:#4C1D95;">✅ Travail validé — Paiement libéré</strong>
                <p style="margin:6px 0 0;color:#4C1D95;font-size:13px;">Le client a validé votre travail. Le montant de <strong>' . $budget . ' DT</strong> a été libéré.</p>
            </div>
            <div style="background:#f8fafc;border-radius:10px;padding:16px;margin:16px 0;">
                <div class="info-row"><span class="info-label">Contrat</span><span class="info-value">' . htmlspecialchars($c['titre']) . '</span></div>
                <div class="info-row"><span class="info-label">Montant reçu</span><span class="info-value" style="color:#7C3AED;font-weight:700;">' . $budget . ' DT</span></div>
                <div class="info-row"><span class="info-label">Statut</span><span class="badge" style="background:#EDE9FE;color:#7C3AED;">Terminé</span></div>
            </div>
            <center><a href="#" class="btn" style="background:#7C3AED;">Voir le récapitulatif</a></center>
        </div>
        <div class="footer">FreelaSkill · Contrat terminé · #' . $c['id_contrat'] . '</div>';
        return $this->baseTemplate($content, '#7C3AED');
    }

    private function templateAlerteDelai(array $c, int $jours, string $name): string {
        $budget  = number_format($c['budget'], 2, ',', ' ');
        $urgence = $jours <= 1 ? '#EF4444' : ($jours <= 2 ? '#F59E0B' : '#F97316');
        $icon    = $jours <= 1 ? '🚨' : '⚠️';
        $content = '
        <div class="header" style="background:linear-gradient(135deg,' . $urgence . ',#DC2626);">
            <h1>' . $icon . ' Alerte délai — ' . $jours . ' jour(s) restant(s)</h1>
            <p>Le contrat expire bientôt</p>
        </div>
        <div class="body">
            <p style="color:#1e293b;font-size:15px;">Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
            <div class="alert-box">
                <strong style="color:#92400E;">' . $icon . ' Attention !</strong>
                <p style="margin:6px 0 0;color:#92400E;font-size:13px;">Le contrat <strong>"' . htmlspecialchars($c['titre']) . '"</strong> expire dans <strong>' . $jours . ' jour(s)</strong>. Assurez-vous de livrer le travail à temps.</p>
            </div>
            <div style="background:#f8fafc;border-radius:10px;padding:16px;margin:16px 0;">
                <div class="info-row"><span class="info-label">Contrat</span><span class="info-value">' . htmlspecialchars($c['titre']) . '</span></div>
                <div class="info-row"><span class="info-label">Budget</span><span class="info-value">' . $budget . ' DT</span></div>
                <div class="info-row"><span class="info-label">Jours restants</span><span class="info-value" style="color:' . $urgence . ';font-weight:700;">' . $jours . ' jour(s)</span></div>
                <div class="info-row"><span class="info-label">Délai total</span><span class="info-value">' . intval($c['delai']) . ' jours</span></div>
            </div>
            <center><a href="#" class="btn" style="background:' . $urgence . ';">Voir le contrat</a></center>
        </div>
        <div class="footer">FreelaSkill · Alerte automatique · Contrat #' . $c['id_contrat'] . '</div>';
        return $this->baseTemplate($content, $urgence);
    }

    private function templateStatutChange(array $c, string $ancien, string $nouveau, string $name): string {
        $statusColors = [
            'brouillon'  => '#94A3B8', 'en_attente' => '#F59E0B',
            'actif'      => '#2563EB', 'termine'    => '#10B981',
            'annule'     => '#EF4444', 'archive'    => '#6B7280',
        ];
        $newColor = $statusColors[$nouveau] ?? '#2563EB';
        $content = '
        <div class="header">
            <h1>🔄 Statut du contrat modifié</h1>
            <p>' . htmlspecialchars($c['titre']) . '</p>
        </div>
        <div class="body">
            <p style="color:#1e293b;font-size:15px;">Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
            <p style="color:#64748b;font-size:14px;">Le statut de votre contrat a été mis à jour :</p>
            <div style="display:flex;align-items:center;justify-content:center;gap:16px;margin:20px 0;padding:16px;background:#f8fafc;border-radius:10px;">
                <span class="badge" style="background:#f1f5f9;color:#64748b;font-size:14px;">' . strtoupper($ancien) . '</span>
                <span style="color:#94a3b8;font-size:20px;">→</span>
                <span class="badge" style="background:' . $newColor . '22;color:' . $newColor . ';font-size:14px;">' . strtoupper($nouveau) . '</span>
            </div>
            <div style="background:#f8fafc;border-radius:10px;padding:16px;margin:16px 0;">
                <div class="info-row"><span class="info-label">Contrat</span><span class="info-value">' . htmlspecialchars($c['titre']) . '</span></div>
                <div class="info-row"><span class="info-label">Budget</span><span class="info-value">' . number_format($c['budget'], 2, ',', ' ') . ' DT</span></div>
                <div class="info-row"><span class="info-label">Nouveau statut</span><span class="badge" style="background:' . $newColor . '22;color:' . $newColor . ';">' . strtoupper($nouveau) . '</span></div>
            </div>
            <center><a href="#" class="btn">Voir le contrat</a></center>
        </div>
        <div class="footer">FreelaSkill · Notification automatique · Contrat #' . $c['id_contrat'] . '</div>';
        return $this->baseTemplate($content, $newColor);
    }
}
