<?php
// controllers/ProduitController.php

require_once __DIR__ . '/../Models/Produit.php';
require_once __DIR__ . '/../controllers/Category_prodController.php';
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ProduitController {

    private $pdo;
    private $categoryController;

    public function __construct() {
        $this->pdo = config::getConnexion();
        $this->categoryController = new Category_prodController();
    }

    public function uploadImageToCloudinary(array $file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Fichier invalide ou téléchargement interrompu.');
        }

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes, true)) {
            throw new Exception('Le fichier doit être une image PNG, JPG ou WEBP.');
        }

        $cloudinaryUrl = defined('CLOUDINARY_URL') ? CLOUDINARY_URL : getenv('CLOUDINARY_URL');
        if (empty($cloudinaryUrl)) {
            throw new Exception('Configuration Cloudinary manquante.');
        }

        $parsed = parse_url($cloudinaryUrl);
        if (empty($parsed['host']) || empty($parsed['user']) || empty($parsed['pass'])) {
            throw new Exception('URL Cloudinary invalide.');
        }

        $cloudName = $parsed['host'];
        $apiKey = $parsed['user'];
        $apiSecret = $parsed['pass'];
        $url = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

        $postData = [
            'file' => curl_file_create($file['tmp_name'], $file['type'], $file['name']),
            'timestamp' => time(),
            'folder' => 'freelaskill'
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_USERPWD, "$apiKey:$apiSecret");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception('Échec Cloudinary : ' . $error);
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $data = json_decode($response, true);
        if ($httpCode !== 200 || empty($data['secure_url'])) {
            $message = $data['error']['message'] ?? 'Réponse Cloudinary inattendue.';
            throw new Exception('Cloudinary : ' . $message);
        }

        return $data['secure_url'];
    }

    // -------------------------------------------------------
    // Base de données : CRUD
    // -------------------------------------------------------
    public function getAllData() {
        $sql = "SELECT * FROM produit";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllFrontData($category_id = null, $excludeUserId = null) {
        $excludeUserId = $excludeUserId !== null ? (int)$excludeUserId : null;
        if ($category_id) {
            $sql = "SELECT * FROM produit WHERE LOWER(TRIM(statut)) = 'disponible' AND category_id = ?";
            $params = [$category_id];
            if ($excludeUserId) {
                $sql .= " AND (user_id IS NULL OR user_id <> ?)";
                $params[] = $excludeUserId;
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            $sql = "SELECT * FROM produit WHERE LOWER(TRIM(statut)) = 'disponible'";
            if ($excludeUserId) {
                $sql .= " AND (user_id IS NULL OR user_id <> ?)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$excludeUserId]);
            } else {
                $stmt = $this->pdo->query($sql);
            }
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retourne les produits de l'admin
    public function getAllAdminData($admin_id) {
        try {
            $sql = "SELECT * FROM produit WHERE user_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$admin_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Repli s'il n'y a pas de colonne user_id
            $sql = "SELECT * FROM produit";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function getByIdData($id) {
        $sql = "SELECT * FROM produit WHERE idProduit = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCategoryData($category_id, $statut = null) {
        if ($statut) {
            $sql = "SELECT * FROM produit WHERE category_id = ? AND LOWER(TRIM(statut)) = LOWER(TRIM(?))";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$category_id, $statut]);
        } else {
            $sql = "SELECT * FROM produit WHERE category_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$category_id]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByPriceData($min, $max, $statut = null) {
        if ($statut) {
            $sql = "SELECT * FROM produit WHERE (prix BETWEEN ? AND ?) AND LOWER(TRIM(statut)) = LOWER(TRIM(?))";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$min, $max, $statut]);
        } else {
            $sql = "SELECT * FROM produit WHERE prix BETWEEN ? AND ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$min, $max]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByStatutData($statut) {
        $sql = "SELECT * FROM produit WHERE LOWER(TRIM(statut)) = LOWER(TRIM(?))";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([trim(strtolower($statut))]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Alias english version
    public function getByStatus($status) {
        return $this->getByStatutData($status);
    }

    public function normalizeDisponibilite($value) {
        $value = trim((string) $value);
        $values = [
            'disponible' => 'Disponible maintenant',
            'disponible maintenant' => 'Disponible maintenant',
            'deux_semaines' => 'Dans 2 semaines',
            'dans 2 semaines' => 'Dans 2 semaines',
            'un_mois' => 'Dans 1 mois',
            'dans 1 mois' => 'Dans 1 mois',
            'non_disponible' => 'Non disponible',
            'non disponible' => 'Non disponible',
        ];

        $key = strtolower($value);
        return $values[$key] ?? 'Disponible maintenant';
    }

    public function createData($data) {
        $disponibilite = $this->normalizeDisponibilite($data['disponibilite'] ?? 'Disponible maintenant');
        try {
            if (isset($data['user_id'])) {
                $sql = "INSERT INTO produit 
                        (category_id, nom, description, prix, stock, image, statut, disponibilite, user_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $data['category_id'],
                    $data['nom'],
                    $data['description'],
                    $data['prix'],
                    $data['stock'],
                    $data['image'],
                    isset($data['statut']) ? trim(strtolower($data['statut'])) : 'pending',
                    $disponibilite,

                    $data['user_id']
                ]);
            } else {
                throw new PDOException('No user_id provided');
            }
        } catch (PDOException $e) {
            $sql = "INSERT INTO produit 
                    (category_id, nom, description, prix, stock, image, statut, disponibilite) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['category_id'],
                $data['nom'],
                $data['description'],
                $data['prix'],
                $data['stock'],
                $data['image'],
                isset($data['statut']) ? trim(strtolower($data['statut'])) : 'pending',
                $disponibilite

            ]);
        }
        return $this->pdo->lastInsertId();
    }

    public function updateData($id, $data) {
        $sql = "UPDATE produit 
                SET nom=?, description=?, prix=?, stock=?, image=?, category_id=?, statut=?, disponibilite=? 

                WHERE idProduit=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['description'],
            $data['prix'],
            $data['stock'],
            $data['image'],
            $data['category_id'],
            isset($data['statut']) ? trim(strtolower($data['statut'])) : null,
            $this->normalizeDisponibilite($data['disponibilite'] ?? 'Disponible maintenant'),

            $id
        ]);
    }

    public function updateStatutData($id, $statut) {
        $sql = "UPDATE produit SET statut = ? WHERE idProduit = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([trim(strtolower($statut)), $id]);
    }

    public function deleteData($id, $user_id = null) {
        try {
            $this->pdo->beginTransaction();

            // Remove dependent order rows first to satisfy foreign key constraints
            $sql = "DELETE FROM commande_produit WHERE idProduit = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);

            $sql = "DELETE FROM produit WHERE idProduit = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);

            $this->pdo->commit();
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function updateStockData($id, $quantite) {
        $sql = "UPDATE produit SET stock = stock - ? WHERE idProduit = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$quantite, $id]);
    }

    // -------------------------------------------------------
    // Frontoffice
    // -------------------------------------------------------
    public function index() {
        $produits    = $this->getAllData();
        $categories  = $this->categoryController->getAllData();

        include __DIR__ . '/../Views/Frontoffice/home.php';
    }

    public function filterByCategory($category_id) {
        $produits   = $this->getByCategoryData($category_id, 'disponible');
        $categories = $this->categoryController->getAllData();

        include __DIR__ . '/../Views/Frontoffice/home.php';
    }

    public function filterByPrice($min, $max) {
        $produits   = $this->getByPriceData($min, $max, 'disponible');
        $categories = $this->categoryController->getAllData();

        include __DIR__ . '/../Views/Frontoffice/home.php';
    }

    public function show($id) {
        $produit = $this->getByIdData($id);
        if (!$produit) {
            header('Location: home.php');
            exit;
        }
        include __DIR__ . '/../Views/Frontoffice/detailproduit.php';
    }

    public function showForm() {
        $categories = $this->categoryController->getAllData();

        include __DIR__ . '/../Views/Frontoffice/vendreproduit.php';
    }

    public function create() {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imagePath = '';
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                try {
                    $imagePath = $this->uploadImageToCloudinary($_FILES['image']);
                } catch (Exception $e) {
                    $errors[] = 'Erreur lors de l\'upload sur Cloudinary : ' . $e->getMessage();
                }
            } elseif (!empty($_FILES['image']['name'])) {
                $errors[] = 'Erreur lors de l\'upload de l\'image.';
            }

            $price = (int) $_POST['price'];
            if ($price <= 0) {
                $errors[] = 'Entrez un prix valide supérieur à 0.';
            }

            if (empty(trim($_POST['title'] ?? ''))) {
                $errors[] = 'Le titre est obligatoire.';
            }

            if (empty(trim($_POST['description'] ?? ''))) {
                $errors[] = 'La description est obligatoire.';
            }

            if (empty($_POST['category'])) {
                $errors[] = 'Choisissez une catégorie.';
            }

            if (empty($errors)) {
                $data = [
                    'nom'           => $_POST['title'],
                    'description'   => $_POST['description'],
                    'prix'          => $price,
                    'category_id'   => $_POST['category'],
                    'statut'        => 'pending',
                    'disponibilite' => $_POST['disponibilite'] ?? 'Disponible maintenant',
                    'stock'         => max(0, (int)($_POST['stock'] ?? 0)),
                    'image'         => $imagePath,
                    'user_id'       => $_SESSION['user_id'] ?? ($_SESSION['admin_id'] ?? 1)

                ];
                $new_id = $this->createData($data);
                header('Location: home.php');
                exit;
            }
        }
        return $errors;
    }

    public function createAdmin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imagePath = '';
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                try {
                    $imagePath = $this->uploadImageToCloudinary($_FILES['image']);
                } catch (Exception $e) {
                    $imagePath = '';
                }
            }

            $price = max(1, (int) $_POST['price']);
            $stock = max(0, (int) ($_POST['stock'] ?? 0));
            $data = [
                'nom'           => $_POST['title'],
                'description'   => $_POST['description'],
                'prix'          => $price,
                'category_id'   => $_POST['category'],
                'statut'        => 'disponible',
                'disponibilite' => $_POST['disponibilite'] ?? 'Disponible maintenant',
                'stock'         => $stock,

                'image'       => $imagePath,
                'user_id'     => $_SESSION['admin_id'] ?? ($_SESSION['user_id'] ?? 1)
            ];
            $new_id = $this->createData($data);
            header('Location: produits.php');
            exit;
        }
    }
    
    // Fonction utilitaire pour enregistrer automatiquement l'ajout comme un achat
    private function enregistrerAchat($produit_id, $data) {
        require_once __DIR__ . '/commandeController.php';
        require_once __DIR__ . '/CommandeProduitController.php';
        $cmdCtrl = new CommandeController();
        $cmdProdCtrl = new CommandeProduitController();
        
        try {
            $cmd_id = $cmdCtrl->createData([
                'user_id' => $data['user_id'] ?? 1,
                'adresse_livraison' => 'Auto-ajout Produit',
                'montant_total' => $data['prix']
            ]);
            
            $cmdProdCtrl->createData([
                'idCommande' => $cmd_id,
                'idProduit' => $produit_id,
                'quantite' => 1,
                'prix_unitaire' => $data['prix']
            ]);
        } catch (PDOException $e) {
            // Ignorer si échec de liaison
        }
    }

    public function delete($id) {
        $this->deleteData($id, $_SESSION['admin_id']);
        header('Location: produits.php');
        exit;
    }

}
