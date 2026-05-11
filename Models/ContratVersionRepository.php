<?php
require_once __DIR__ . '/ContratVersion.php';

/**
 * Repository OOP pour les versions de contrat — accès PDO uniquement.
 */
class ContratVersionRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Sauvegarde un snapshot du contrat avant modification. */
    public function save(ContratVersion $version): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO contrat_versions
                 (id_contrat, version_number, titre, description, budget, delai, statut, freelance_info, modifie_par, date_version)
                 VALUES
                 (:id_contrat, :version_number, :titre, :description, :budget, :delai, :statut, :freelance_info, :modifie_par, NOW())'
            );
            return $stmt->execute([
                'id_contrat'     => $version->getIdContrat(),
                'version_number' => $version->getVersionNumber(),
                'titre'          => $version->getTitre(),
                'description'    => $version->getDescription(),
                'budget'         => $version->getBudget(),
                'delai'          => $version->getDelai(),
                'statut'         => $version->getStatut(),
                'freelance_info' => $version->getFreelanceInfo(),
                'modifie_par'    => $version->getModifiePar(),
            ]);
        } catch (PDOException $e) {
            // Table pas encore créée — ignorer silencieusement
            return false;
        }
    }

    /** Retourne toutes les versions d'un contrat, triées par numéro décroissant. */
    public function findByContrat(int $idContrat): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM contrat_versions WHERE id_contrat = :id ORDER BY version_number DESC'
            );
            $stmt->execute(['id' => $idContrat]);
            return array_map([ContratVersion::class, 'fromArray'], $stmt->fetchAll());
        } catch (PDOException $e) {
            // Table pas encore créée — retourner tableau vide
            return [];
        }
    }

    /** Retourne le prochain numéro de version pour un contrat. */
    public function getNextVersionNumber(int $idContrat): int
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT COALESCE(MAX(version_number), 0) + 1 FROM contrat_versions WHERE id_contrat = :id'
            );
            $stmt->execute(['id' => $idContrat]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 1;
        }
    }

    /** Retourne une version spécifique. */
    public function findById(int $idVersion): ?ContratVersion
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM contrat_versions WHERE id_version = :id');
            $stmt->execute(['id' => $idVersion]);
            $row = $stmt->fetch();
            return $row ? ContratVersion::fromArray($row) : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /** Retourne les deux dernières versions pour comparaison. */
    public function findLastTwo(int $idContrat): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM contrat_versions WHERE id_contrat = :id ORDER BY version_number DESC LIMIT 2'
            );
            $stmt->execute(['id' => $idContrat]);
            return array_map([ContratVersion::class, 'fromArray'], $stmt->fetchAll());
        } catch (PDOException $e) {
            return [];
        }
    }
}
