<?php
require_once __DIR__ . '/../config.php';

class Rule
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM rules ORDER BY date_creation DESC');
        return $stmt->fetchAll();
    }

    public function get(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM rules WHERE id_rule = :id');
        $stmt->execute(['id' => $id]);
        $rule = $stmt->fetch();
        return $rule === false ? null : $rule;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO rules (titre, description, type, valeur, date_creation, statut, id_contrat) VALUES (:titre, :description, :type, :valeur, NOW(), :statut, :id_contrat)'
        );
        return $stmt->execute([
            'titre' => $data['titre'],
            'description' => $data['description'],
            'type' => $data['type'],
            'valeur' => $data['valeur'],
            'statut' => $data['statut'],
            'id_contrat' => $data['id_contrat']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE rules SET titre = :titre, description = :description, type = :type, valeur = :valeur, statut = :statut, id_contrat = :id_contrat WHERE id_rule = :id'
        );
        return $stmt->execute([
            'titre' => $data['titre'],
            'description' => $data['description'],
            'type' => $data['type'],
            'valeur' => $data['valeur'],
            'statut' => $data['statut'],
            'id_contrat' => $data['id_contrat'],
            'id' => $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM rules WHERE id_rule = :id');
        return $stmt->execute(['id' => $id]);
    }
}
