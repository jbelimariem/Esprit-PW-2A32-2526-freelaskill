<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/SmtpClient.php';

class MailController {
    private $user;
    private $pass;
    private $lastError = '';
    private $lastLogs = [];

    public function __construct() {
        $this->user = GMAIL_USER;
        $this->pass = GMAIL_PASS;
    }

    private function normalizeItem($item) {
        return [
            'nom' => $item['nom'] ?? $item['title'] ?? 'Produit',
            'quantite' => $item['quantite'] ?? $item['quantity'] ?? 1,
            'prix' => $item['prix'] ?? $item['price'] ?? 0,
        ];
    }

    public function sendOrderConfirmation($toEmail, $orderId, $amount, $items) {
        $subject = "Confirmation de commande #$orderId - FreelaSkill";

        $itemsHtml = '';
        foreach ($items as $item) {
            $item = $this->normalizeItem($item);
            $itemsHtml .= "<tr>
                <td style='padding:10px; border-bottom:1px solid #eee;'>" . htmlspecialchars($item['nom']) . " (x" . (int)$item['quantite'] . ")</td>
                <td style='padding:10px; border-bottom:1px solid #eee; text-align:right;'>" . number_format((float)$item['prix'], 2, '.', ' ') . " DT</td>
            </tr>";
        }

        $htmlContent = "
        <div style='font-family:Arial, sans-serif; max-width:600px; margin:0 auto; background:#fff; border:1px solid #eee; border-radius:10px; overflow:hidden;'>
            <div style='background:#020617; padding:30px; text-align:center; color:#fff;'>
                <h1 style='margin:0; font-size:24px;'>Freela<span style='color:#3b82f6;'>Skill</span></h1>
                <p style='opacity:0.8;'>Votre commande a ete validee.</p>
            </div>
            <div style='padding:30px;'>
                <h2 style='color:#020617;'>Merci pour votre commande !</h2>
                <p>Bonjour,</p>
                <p>Votre commande <strong>#$orderId</strong> est confirmee et en cours de preparation.</p>
                <table style='width:100%; border-collapse:collapse; margin-top:20px;'>
                    <thead>
                        <tr style='background:#f8fafc;'>
                            <th style='padding:10px; text-align:left;'>Article</th>
                            <th style='padding:10px; text-align:right;'>Prix</th>
                        </tr>
                    </thead>
                    <tbody>$itemsHtml</tbody>
                    <tfoot>
                        <tr>
                            <td style='padding:20px 10px; font-weight:bold;'>Total</td>
                            <td style='padding:20px 10px; font-weight:bold; text-align:right; color:#10b981; font-size:18px;'>" . number_format((float)$amount, 2, '.', ' ') . " DT</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style='background:#f8fafc; padding:20px; text-align:center; font-size:11px; color:#94a3b8;'>
                Ceci est un mail automatique, merci de ne pas y repondre.
            </div>
        </div>";

        $smtp = new SmtpClient($this->user, $this->pass);
        $sent = $smtp->send($toEmail, 'FreelaSkill Marketplace', $subject, $htmlContent);

        $this->lastError = $smtp->getLastError();
        $this->lastLogs = $smtp->getLogs();

        return $sent;
    }

    public function sendSellerOrderNotification($toEmail, $orderId, $amount, $items) {
        $subject = "Nouvelle vente #$orderId - FreelaSkill";

        $itemsHtml = '';
        foreach ($items as $item) {
            $item = $this->normalizeItem($item);
            $lineTotal = (float)$item['prix'] * (int)$item['quantite'];
            $itemsHtml .= "<tr>
                <td style='padding:10px; border-bottom:1px solid #eee;'>" . htmlspecialchars($item['nom']) . " (x" . (int)$item['quantite'] . ")</td>
                <td style='padding:10px; border-bottom:1px solid #eee; text-align:right;'>" . number_format($lineTotal, 2, '.', ' ') . " DT</td>
            </tr>";
        }

        $htmlContent = "
        <div style='font-family:Arial, sans-serif; max-width:600px; margin:0 auto; background:#fff; border:1px solid #eee; border-radius:10px; overflow:hidden;'>
            <div style='background:#020617; padding:30px; text-align:center; color:#fff;'>
                <h1 style='margin:0; font-size:24px;'>Freela<span style='color:#3b82f6;'>Skill</span></h1>
                <p style='opacity:0.8;'>Vous avez recu une nouvelle vente.</p>
            </div>
            <div style='padding:30px;'>
                <h2 style='color:#020617;'>Nouvelle commande #$orderId</h2>
                <p>Bonjour,</p>
                <p>Un acheteur vient de commander un ou plusieurs de vos produits.</p>
                <table style='width:100%; border-collapse:collapse; margin-top:20px;'>
                    <thead>
                        <tr style='background:#f8fafc;'>
                            <th style='padding:10px; text-align:left;'>Article vendu</th>
                            <th style='padding:10px; text-align:right;'>Montant</th>
                        </tr>
                    </thead>
                    <tbody>$itemsHtml</tbody>
                    <tfoot>
                        <tr>
                            <td style='padding:20px 10px; font-weight:bold;'>Total commande</td>
                            <td style='padding:20px 10px; font-weight:bold; text-align:right; color:#10b981; font-size:18px;'>" . number_format((float)$amount, 2, '.', ' ') . " DT</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style='background:#f8fafc; padding:20px; text-align:center; font-size:11px; color:#94a3b8;'>
                Ceci est un mail automatique, merci de ne pas y repondre.
            </div>
        </div>";

        $smtp = new SmtpClient($this->user, $this->pass);
        $sent = $smtp->send($toEmail, 'FreelaSkill Marketplace', $subject, $htmlContent);

        $this->lastError = $smtp->getLastError();
        $this->lastLogs = $smtp->getLogs();

        return $sent;
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function getLastLogs() {
        return $this->lastLogs;
    }
}
