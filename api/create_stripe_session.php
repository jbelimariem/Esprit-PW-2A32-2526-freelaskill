<?php
header('Content-Type: application/json');

// Désactiver l'affichage des erreurs HTML pour ne pas casser le JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
<<<<<<< HEAD
    require_once __DIR__ . '/../controllers/config.php';
=======
    require_once __DIR__ . '/../config.php';
>>>>>>> e50c4cf (Mise a jour locale avant synchronisation)
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    // Récupération des données du panier
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);

    if (empty($input['items'])) {
        throw new Exception('Le panier est vide ou malformé.');
    }

    $pdo = config::getConnexion();
    foreach ($input['items'] as $item) {
        $idProduit = (int)($item['idProduit'] ?? 0);
        $quantite = max(1, (int)($item['quantite'] ?? 1));
        if ($idProduit <= 0) {
            throw new Exception('Produit invalide dans le panier.');
        }
        $stmt = $pdo->prepare("SELECT nom, stock, disponibilite FROM produit WHERE idProduit = ?");
        $stmt->execute([$idProduit]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product || ($product['disponibilite'] ?? '') === 'Non disponible' || (int)$product['stock'] < $quantite) {
            $name = $product['nom'] ?? ('Produit #' . $idProduit);
            throw new Exception($name . ' est indisponible ou en stock insuffisant.');
        }
    }

    // Sauvegarde en session pour la page de confirmation
    $_SESSION['pending_order'] = [
        'items'   => $input['items'],
        'cart'    => $input['items'],
        'adresse' => $input['adresse'] ?? 'Non spécifiée',
        'total'   => (float)($input['total'] ?? 0),
        'user_id' => $input['user_id'] ?? 1
    ];

    $line_items = [];
    foreach ($input['items'] as $item) {
        // Validation basique
        $nom = !empty($item['nom']) ? $item['nom'] : 'Produit sans nom';
        $prix = !empty($item['prix']) ? (float)$item['prix'] : 0.0;
        $quantite = !empty($item['quantite']) ? (int)$item['quantite'] : 1;
        $image = !empty($item['image']) ? $item['image'] : 'https://via.placeholder.com/150';

        $line_items[] = [
            'price_data' => [
                'currency' => 'eur', // Note: Stripe ne supporte pas TND en direct, on utilise EUR pour le test
                'product_data' => [
                    'name' => $nom,
                    'images' => [$image],
                ],
                'unit_amount' => (int)($prix * 100), // En centimes
            ],
            'quantity' => $quantite,
        ];
    }

    $data = [
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => 'http://localhost/Esprit-PW-2A32-2526-TalentBridge/Views/Frontoffice/confirmation.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/Esprit-PW-2A32-2526-TalentBridge/Views/Frontoffice/panier.php',
    ];

    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        throw new Exception('CURL Error: ' . $err);
    }

    // On renvoie directement la réponse de Stripe si c'est un succès
    if ($httpCode >= 200 && $httpCode < 300) {
        echo $response;
    } else {
        // Sinon on décode pour renvoyer l'erreur Stripe proprement
        $stripeError = json_decode($response, true);
        $msg = isset($stripeError['error']['message']) ? $stripeError['error']['message'] : 'Erreur Stripe inconnue';
        throw new Exception($msg);
    }

} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
