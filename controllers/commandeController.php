<?php
// controllers/commandeController.php

require_once __DIR__ . '/../Models/commande.php';
require_once __DIR__ . '/config.php';

class CommandeController {

    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // -------------------------------------------------------
    // Base de données : CRUD
    // -------------------------------------------------------
    public function getAllData() {

        $sql = "SELECT * FROM commande ORDER BY idCommande DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByIdData($id) {

        $sql = "SELECT * FROM commande WHERE idCommande = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUser($user_id) {
        $sql = "SELECT * FROM commande WHERE user_id = ? ORDER BY idCommande DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUserPaginated($user_id, $limit, $offset) {
        $sql = "SELECT * FROM commande WHERE user_id = ? ORDER BY idCommande DESC LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($sql);
        // On force les types pour LIMIT et OFFSET
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCountByUser($user_id) {
        $sql = "SELECT COUNT(*) FROM commande WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function createData($data) {
        $sql = "INSERT INTO commande 
                (user_id, date_commande, statut, adresse_livraison, mode_paiement, mode_livraison, montant_total) 
                VALUES (?, CURDATE(), ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['user_id'],
            'en_attente',
            $data['adresse_livraison'],
            $data['mode_paiement'] ?? 'Sur place',
            $data['mode_livraison'] ?? 'Standard',
            $data['montant_total']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateStatutData($id, $statut) {
        $sql = "UPDATE commande SET statut=? WHERE idCommande=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut, $id]);
    }

    public function updateDetailsData($id, $adresse, $mode_paiement, $montant_total = null) {
        if ($montant_total !== null) {
            $sql = "UPDATE commande SET adresse_livraison=?, mode_paiement=?, montant_total=? WHERE idCommande=?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$adresse, $mode_paiement, $montant_total, $id]);
        } else {
            $sql = "UPDATE commande SET adresse_livraison=?, mode_paiement=? WHERE idCommande=?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$adresse, $mode_paiement, $id]);
        }
    }

    public function deleteData($id) {
        try {
            $this->pdo->beginTransaction();
            
            // 1. Supprimer les produits liés
            $sql1 = "DELETE FROM commande_produit WHERE idCommande = ?";
            $stmt1 = $this->pdo->prepare($sql1);
            $stmt1->execute([$id]);
 
            // 2. Supprimer la commande
            $sql2 = "DELETE FROM commande WHERE idCommande = ?";
            $stmt2 = $this->pdo->prepare($sql2);
            $stmt2->execute([$id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return false;
        }
    }

}
