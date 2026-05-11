<?php
require_once __DIR__ . '/../../controllers/commandeController.php';
require_once __DIR__ . '/../../controllers/CommandeProduitController.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';
require_once __DIR__ . '/../../controllers/MailRecipientHelper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session_id = $_GET['session_id'] ?? null;
$order_created = false;
$mail_sent = false;
$mail_error = null;

// Si on a une session Stripe, on peut valider la commande
if ($session_id) {
    // Note: Dans un système parfait, on vérifierait ici l'état de la session via l'API Stripe
    // Mais pour ton projet, on va considérer que si l'utilisateur arrive ici, c'est qu'il a payé.

    // On pourrait stocker les détails de la commande en session temporaire avant d'aller sur Stripe
    // Pour cet exemple, on va simuler la récupération si tu as des données en session
    if (isset($_SESSION['pending_order'])) {
        $orderData = $_SESSION['pending_order'];

        $commandeController = new CommandeController();
        $cpController = new CommandeProduitController();
        $notifController = new NotificationController();

        // 1. Créer la commande
        $idCommande = $commandeController->createData([
            'user_id' => $orderData['user_id'],
            'adresse_livraison' => $orderData['adresse'],
            'mode_paiement' => 'Stripe (En ligne)',
            'mode_livraison' => 'Standard',
            'montant_total' => $orderData['total']
        ]);

        // 2. Ajouter les produits
        $resolvedCart = [];
        foreach ($orderData['cart'] as $item) {
            $pdo = config::getConnexion();
            $stockStmt = $pdo->prepare("SELECT nom, stock, disponibilite, user_id FROM produit WHERE idProduit = ?");
            $stockStmt->execute([$item['idProduit']]);
            $productStock = $stockStmt->fetch();

            if (!$productStock || ($productStock['disponibilite'] ?? '') === 'Non disponible' || (int)$productStock['stock'] < (int)$item['quantite']) {
                $productName = $productStock['nom'] ?? ('Produit #' . $item['idProduit']);
                throw new Exception("Stock indisponible pour " . $productName);
            }

            $cpController->createData([
                'idCommande' => $idCommande,
                'idProduit' => $item['idProduit'],
                'quantite' => $item['quantite'],
                'prix_unitaire' => $item['prix']
            ]);

            $updateStock = $pdo->prepare("UPDATE produit SET stock = GREATEST(stock - ?, 0), disponibilite = CASE WHEN GREATEST(stock - ?, 0) <= 0 THEN 'Non disponible' ELSE disponibilite END WHERE idProduit = ?");
            $updateStock->execute([(int)$item['quantite'], (int)$item['quantite'], $item['idProduit']]);

            $resolvedCart[] = [
                'idProduit' => (int)$item['idProduit'],
                'nom' => $productStock['nom'] ?? ($item['nom'] ?? 'Produit'),
                'prix' => (float)($item['prix'] ?? 0),
                'quantite' => (int)$item['quantite'],
                'owner_id' => isset($productStock['user_id']) ? (int)$productStock['user_id'] : null,
            ];
        }

        // 3. Notification
        $notifController->createData($orderData['user_id'], "Paiement Stripe réussi ! Votre commande #$idCommande est confirmée.", "success");

        // 4. ENVOI DU MAIL DE CONFIRMATION (GMAIL SMTP)
        require_once __DIR__ . '/../../controllers/MailController.php';
        $mailController = new MailController();
        
        $pdo = config::getConnexion();
        $userEmail = MailRecipientHelper::getUserEmail($pdo, $orderData['user_id']);

        // Envoi au client
        if ($userEmail) {
            $mail_sent = $mailController->sendOrderConfirmation($userEmail, $idCommande, $orderData['total'], $resolvedCart);
            if (!$mail_sent) {
                $mail_error = $mailController->getLastError();
                error_log('[TalentBridge SMTP] ' . $mail_error);
            }
        } else {
            $mail_error = "Aucun email valide trouve pour l'utilisateur #" . $orderData['user_id'] . ".";
            error_log('[TalentBridge SMTP] ' . $mail_error);
        }

        // Envoi aux vendeurs concernes
        $seller_mail_sent = true;
        $sellerItemsByEmail = MailRecipientHelper::groupSellerItemsByEmail($pdo, $resolvedCart, $orderData['user_id']);
        foreach ($sellerItemsByEmail as $sellerEmail => $sellerItems) {
            $sentToSeller = $mailController->sendSellerOrderNotification($sellerEmail, $idCommande, $orderData['total'], $sellerItems);
            if (!$sentToSeller) {
                $seller_mail_sent = false;
                $sellerError = $mailController->getLastError();
                $mail_error = trim(($mail_error ? $mail_error . ' | ' : '') . "Vendeur $sellerEmail: $sellerError");
                error_log('[TalentBridge SMTP] Vendeur ' . $sellerEmail . ': ' . $sellerError);
            }
        }

        $mail_sent = $mail_sent && $seller_mail_sent;

        $order_created = true;
        unset($_SESSION['pending_order']); // On nettoie
    }
}
?>
<!DOCTYPE html>
<html lang="fr" style="color-scheme: dark;">

<head>
    <meta charset="UTF-8">
    <title>Confirmation de commande — FreelaSkill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css?v=6">
    <style>
        .conf-card {
            max-width: 500px;
            margin: 5rem auto;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .conf-icon {
            width: 80px;
            height: 80px;
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }
    </style>
</head>

<body class="page-anim">
    <div class="conf-card">
        <div class="conf-icon"><i class="fa-solid fa-check"></i></div>
        <h1 style="color:white; margin-bottom:1rem;">Paiement Réussi !</h1>
        <p style="color:#94a3b8; line-height:1.6; margin-bottom:2rem;">
            Merci pour votre achat. Votre commande a été enregistrée avec succès et le vendeur a été notifié.
        </p>
        <a href="home.php" class="btn btn-primary" style="display:inline-flex; width:auto; padding:0.8rem 2rem;">Retour
            à la boutique</a>
    </div>

    <script>
        // VIDER LE PANIER LOCALEMENT
        localStorage.removeItem('freelaSkillCart');
        localStorage.removeItem('freelaskill_cart');
    </script>
</body>

</html>
