<?php
/**
 * Modèle OOP pour les versions d'un contrat (historique des modifications).
 */
class ContratVersion
{
    private ?int    $id_version;
    private int     $id_contrat;
    private int     $version_number;
    private string  $titre;
    private string  $description;
    private float   $budget;
    private int     $delai;
    private string  $statut;
    private string  $freelance_info;
    private string  $modifie_par;
    private string  $date_version;

    public function __construct(
        int    $id_contrat,
        int    $version_number,
        string $titre,
        string $description,
        float  $budget,
        int    $delai,
        string $statut,
        string $freelance_info = '',
        string $modifie_par = 'admin',
        string $date_version = '',
        ?int   $id_version = null
    ) {
        $this->id_contrat     = $id_contrat;
        $this->version_number = $version_number;
        $this->titre          = $titre;
        $this->description    = $description;
        $this->budget         = $budget;
        $this->delai          = $delai;
        $this->statut         = $statut;
        $this->freelance_info = $freelance_info;
        $this->modifie_par    = $modifie_par;
        $this->date_version   = $date_version ?: date('Y-m-d H:i:s');
        $this->id_version     = $id_version;
    }

    // ── Getters ──────────────────────────────────────────────────────
    public function getIdVersion(): ?int      { return $this->id_version; }
    public function getIdContrat(): int        { return $this->id_contrat; }
    public function getVersionNumber(): int    { return $this->version_number; }
    public function getTitre(): string         { return $this->titre; }
    public function getDescription(): string   { return $this->description; }
    public function getBudget(): float         { return $this->budget; }
    public function getDelai(): int            { return $this->delai; }
    public function getStatut(): string        { return $this->statut; }
    public function getFreelanceInfo(): string { return $this->freelance_info; }
    public function getModifiePar(): string    { return $this->modifie_par; }
    public function getDateVersion(): string   { return $this->date_version; }

    /**
     * Compare deux versions et retourne les champs modifiés.
     * @return array ['field' => ['old' => ..., 'new' => ...]]
     */
    public function diff(ContratVersion $other): array
    {
        $changes = [];
        $fields  = [
            'titre'         => 'Titre',
            'description'   => 'Description',
            'budget'        => 'Budget',
            'delai'         => 'Délai',
            'statut'        => 'Statut',
            'freelance_info'=> 'Freelancer',
        ];

        foreach ($fields as $field => $label) {
            $oldVal = (string)$this->$field;
            $newVal = (string)$other->$field;
            if ($oldVal !== $newVal) {
                $changes[$field] = [
                    'label' => $label,
                    'old'   => $oldVal,
                    'new'   => $newVal,
                ];
            }
        }

        return $changes;
    }

    public function toArray(): array
    {
        return [
            'id_contrat'     => $this->id_contrat,
            'version_number' => $this->version_number,
            'titre'          => $this->titre,
            'description'    => $this->description,
            'budget'         => $this->budget,
            'delai'          => $this->delai,
            'statut'         => $this->statut,
            'freelance_info' => $this->freelance_info,
            'modifie_par'    => $this->modifie_par,
            'date_version'   => $this->date_version,
        ];
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int)$row['id_contrat'],
            (int)$row['version_number'],
            $row['titre'],
            $row['description'] ?? '',
            (float)$row['budget'],
            (int)$row['delai'],
            $row['statut'],
            $row['freelance_info'] ?? '',
            $row['modifie_par']    ?? 'admin',
            $row['date_version'],
            isset($row['id_version']) ? (int)$row['id_version'] : null
        );
    }

    public static function fromContrat(array $contrat, int $versionNumber, string $modifiePar = 'admin'): self
    {
        return new self(
            (int)$contrat['id_contrat'],
            $versionNumber,
            $contrat['titre'],
            $contrat['description'] ?? '',
            (float)$contrat['budget'],
            (int)$contrat['delai'],
            $contrat['statut'],
            $contrat['freelance_info'] ?? '',
            $modifiePar
        );
    }
}
