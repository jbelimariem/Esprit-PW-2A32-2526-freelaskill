<?php
/**
 * EscrowService — Système de paiement sécurisé (séquestre)
 * Simule un escrow : blocage du budget jusqu'à validation du travail.
 *
 * Workflow :
 *   en_attente → bloque (client dépose) → libere (client valide)
 *                                       → rembourse (litige/annulation)
 */
class EscrowService {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ── Récupérer le statut paiement d'un contrat ─────────────────────
    public function getStatutPaiement(int $idContrat): string {
        try {
            $stmt = $this->pdo->prepare('SELECT statut_paiement FROM contrat WHERE id_contrat = :id');
            $stmt->execute(['id' => $idContrat]);
            $row = $stmt->fetch();
            return $row ? $row['statut_paiement'] : 'en_attente';
        } catch (PDOException $e) {
            return 'en_attente'; // colonne pas encore créée
        }
    }

    // ── Récupérer toutes les transactions d'un contrat ────────────────
    public function getTransactions(int $idContrat): array {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM escrow_transactions WHERE id_contrat = :id ORDER BY date_action DESC'
            );
            $stmt->execute(['id' => $idContrat]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // ── Récupérer toutes les transactions (backoffice) ────────────────
    public function getAllTransactions(): array {
        try {
            $stmt = $this->pdo->query(
                'SELECT t.*, c.titre as titre_contrat, c.budget
                 FROM escrow_transactions t
                 JOIN contrat c ON t.id_contrat = c.id_contrat
                 ORDER BY t.date_action DESC'
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // ── Statistiques escrow ───────────────────────────────────────────
    public function getStats(): array {
        $default = [
            'total' => 0, 'en_attente' => 0, 'bloque' => 0,
            'libere' => 0, 'rembourse' => 0,
            'montant_bloque' => 0, 'montant_libere' => 0,
        ];
        try {
            $stmt = $this->pdo->query(
                "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN statut_paiement = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                    SUM(CASE WHEN statut_paiement = 'bloque'     THEN 1 ELSE 0 END) as bloque,
                    SUM(CASE WHEN statut_paiement = 'libere'     THEN 1 ELSE 0 END) as libere,
                    SUM(CASE WHEN statut_paiement = 'rembourse'  THEN 1 ELSE 0 END) as rembourse,
                    SUM(CASE WHEN statut_paiement = 'bloque' THEN budget ELSE 0 END) as montant_bloque,
                    SUM(CASE WHEN statut_paiement = 'libere' THEN budget ELSE 0 END) as montant_libere
                 FROM contrat"
            );
            $row = $stmt->fetch();
            return $row ?: $default;
        } catch (PDOException $e) {
            // La colonne statut_paiement n'existe pas encore → migration requise
            return $default;
        }
    }

    // ── ACTION : Client dépose le paiement (en_attente → bloque) ─────
    public function deposerPaiement(int $idContrat, string $effectuePar = 'client'): array {
        try {
            $statut = $this->getStatutPaiement($idContrat);
            if ($statut !== 'en_attente') {
                return ['success' => false, 'message' => 'Le paiement a déjà été déposé ou traité.'];
            }
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                "UPDATE contrat SET statut_paiement = 'bloque', statut = 'actif' WHERE id_contrat = :id"
            );
            $stmt->execute(['id' => $idContrat]);
            $this->logTransaction($idContrat, 'depot', 'en_attente', 'bloque', 'Paiement déposé et bloqué en séquestre', $effectuePar);
            $this->createEscrowNotification($idContrat, '💰 Paiement bloqué en séquestre — le travail peut commencer.');
            $this->pdo->commit();
            return ['success' => true, 'message' => 'Paiement bloqué en séquestre. Le freelancer peut commencer le travail.'];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()];
        }
    }

    // ── ACTION : Client valide le travail (bloque → libere) ──────────
    public function libererPaiement(int $idContrat, string $effectuePar = 'client'): array {
        try {
            $statut = $this->getStatutPaiement($idContrat);
            if ($statut !== 'bloque') {
                return ['success' => false, 'message' => 'Le paiement n\'est pas en séquestre.'];
            }
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                "UPDATE contrat SET statut_paiement = 'libere', statut = 'termine' WHERE id_contrat = :id"
            );
            $stmt->execute(['id' => $idContrat]);
            $this->logTransaction($idContrat, 'liberation', 'bloque', 'libere', 'Travail validé — paiement libéré au freelancer', $effectuePar);
            $this->createEscrowNotification($idContrat, '✅ Travail validé — paiement libéré au freelancer.');
            $this->pdo->commit();
            return ['success' => true, 'message' => 'Paiement libéré au freelancer. Contrat terminé.'];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()];
        }
    }

    // ── ACTION : Remboursement (litige/annulation) ────────────────────
    public function rembourserPaiement(int $idContrat, string $commentaire = '', string $effectuePar = 'admin'): array {
        try {
            $statut = $this->getStatutPaiement($idContrat);
            if ($statut !== 'bloque') {
                return ['success' => false, 'message' => 'Impossible de rembourser — paiement non bloqué.'];
            }
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                "UPDATE contrat SET statut_paiement = 'rembourse', statut = 'annule' WHERE id_contrat = :id"
            );
            $stmt->execute(['id' => $idContrat]);
            $this->logTransaction($idContrat, 'remboursement', 'bloque', 'rembourse', $commentaire ?: 'Remboursement suite à litige', $effectuePar);
            $this->createEscrowNotification($idContrat, '🔄 Paiement remboursé au client suite à litige.');
            $this->pdo->commit();
            return ['success' => true, 'message' => 'Paiement remboursé au client.'];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()];
        }
    }

    // ── Enregistrer une transaction ───────────────────────────────────
    private function logTransaction(int $idContrat, string $type, string $avant, string $apres, string $commentaire, string $effectuePar): void {
        try {
            $stmt = $this->pdo->prepare('SELECT budget FROM contrat WHERE id_contrat = :id');
            $stmt->execute(['id' => $idContrat]);
            $montant = $stmt->fetch()['budget'] ?? 0;

            $stmt = $this->pdo->prepare(
                'INSERT INTO escrow_transactions (id_contrat, montant, type_action, statut_avant, statut_apres, commentaire, effectue_par)
                 VALUES (:id_contrat, :montant, :type_action, :statut_avant, :statut_apres, :commentaire, :effectue_par)'
            );
            $stmt->execute([
                'id_contrat'   => $idContrat,
                'montant'      => $montant,
                'type_action'  => $type,
                'statut_avant' => $avant,
                'statut_apres' => $apres,
                'commentaire'  => $commentaire,
                'effectue_par' => $effectuePar,
            ]);
        } catch (PDOException $e) {
            // Silencieux — la table n'existe peut-être pas encore
        }
    }

    // ── Créer une notification escrow ─────────────────────────────────
    private function createEscrowNotification(int $idContrat, string $message): void {
        try {
            $stmt = $this->pdo->prepare('SELECT titre, statut FROM contrat WHERE id_contrat = :id');
            $stmt->execute(['id' => $idContrat]);
            $c = $stmt->fetch();
            if (!$c) return;

            $stmt = $this->pdo->prepare(
                'INSERT INTO notifications (id_contrat, titre_contrat, ancien_statut, nouveau_statut, message)
                 VALUES (:id_contrat, :titre_contrat, :ancien_statut, :nouveau_statut, :message)'
            );
            $stmt->execute([
                'id_contrat'    => $idContrat,
                'titre_contrat' => $c['titre'],
                'ancien_statut' => $c['statut'],
                'nouveau_statut'=> $c['statut'],
                'message'       => $message,
            ]);
        } catch (Exception $e) {
            // Silencieux — la notification n'est pas critique
        }
    }

    // ── Labels et couleurs pour l'affichage ───────────────────────────
    public static function getLabel(string $statut): string {
        return match($statut) {
            'en_attente' => '⏳ En attente',
            'bloque'     => '🔒 Bloqué',
            'libere'     => '✅ Libéré',
            'rembourse'  => '🔄 Remboursé',
            default      => $statut,
        };
    }

    public static function getColor(string $statut): array {
        return match($statut) {
            'en_attente' => ['bg' => 'rgba(245,158,11,0.15)',  'color' => '#FBBF24'],
            'bloque'     => ['bg' => 'rgba(37,99,235,0.15)',   'color' => '#60A5FA'],
            'libere'     => ['bg' => 'rgba(16,185,129,0.15)',  'color' => '#34D399'],
            'rembourse'  => ['bg' => 'rgba(239,68,68,0.15)',   'color' => '#F87171'],
            default      => ['bg' => 'rgba(100,116,139,0.15)', 'color' => '#94A3B8'],
        };
    }
}
