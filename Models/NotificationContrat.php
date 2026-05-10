<?php
/**
 * Modèle OOP pour les notifications de changement de statut.
 * Respecte les principes OOP : encapsulation, getters/setters.
 */
class NotificationContrat
{
    private ?int    $id_notification;
    private int     $id_contrat;
    private string  $titre_contrat;
    private string  $ancien_statut;
    private string  $nouveau_statut;
    private string  $message;
    private bool    $lu;
    private string  $date_creation;

    // Icônes et couleurs par statut
    public const STATUS_CONFIG = [
        'brouillon'  => ['icon' => 'fa-file',          'color' => '#94A3B8', 'label' => 'Brouillon'],
        'en_attente' => ['icon' => 'fa-clock',          'color' => '#FBBF24', 'label' => 'En attente'],
        'actif'      => ['icon' => 'fa-circle-check',   'color' => '#60A5FA', 'label' => 'Actif'],
        'termine'    => ['icon' => 'fa-check-double',   'color' => '#34D399', 'label' => 'Terminé'],
        'annule'     => ['icon' => 'fa-circle-xmark',   'color' => '#F87171', 'label' => 'Annulé'],
        'archive'    => ['icon' => 'fa-box-archive',    'color' => '#9CA3AF', 'label' => 'Archivé'],
    ];

    public function __construct(
        int    $id_contrat,
        string $titre_contrat,
        string $ancien_statut,
        string $nouveau_statut,
        string $message = '',
        bool   $lu = false,
        string $date_creation = '',
        ?int   $id_notification = null
    ) {
        $this->id_contrat     = $id_contrat;
        $this->titre_contrat  = $titre_contrat;
        $this->ancien_statut  = $ancien_statut;
        $this->nouveau_statut = $nouveau_statut;
        $this->message        = $message ?: $this->buildMessage();
        $this->lu             = $lu;
        $this->date_creation  = $date_creation ?: date('Y-m-d H:i:s');
        $this->id_notification = $id_notification;
    }

    private function buildMessage(): string
    {
        $ancienLabel  = self::STATUS_CONFIG[$this->ancien_statut]['label']  ?? $this->ancien_statut;
        $nouveauLabel = self::STATUS_CONFIG[$this->nouveau_statut]['label'] ?? $this->nouveau_statut;
        return "Le contrat \"{$this->titre_contrat}\" est passé de \"{$ancienLabel}\" à \"{$nouveauLabel}\".";
    }

    // ── Getters ──────────────────────────────────────────────────────
    public function getIdNotification(): ?int  { return $this->id_notification; }
    public function getIdContrat(): int         { return $this->id_contrat; }
    public function getTitreContrat(): string   { return $this->titre_contrat; }
    public function getAncienStatut(): string   { return $this->ancien_statut; }
    public function getNouveauStatut(): string  { return $this->nouveau_statut; }
    public function getMessage(): string        { return $this->message; }
    public function isLu(): bool                { return $this->lu; }
    public function getDateCreation(): string   { return $this->date_creation; }

    // ── Setters ──────────────────────────────────────────────────────
    public function setLu(bool $lu): void { $this->lu = $lu; }

    public function getIconNouveauStatut(): string
    {
        return self::STATUS_CONFIG[$this->nouveau_statut]['icon'] ?? 'fa-bell';
    }

    public function getColorNouveauStatut(): string
    {
        return self::STATUS_CONFIG[$this->nouveau_statut]['color'] ?? '#94A3B8';
    }

    public function toArray(): array
    {
        return [
            'id_notification' => $this->id_notification,
            'id_contrat'      => $this->id_contrat,
            'titre_contrat'   => $this->titre_contrat,
            'ancien_statut'   => $this->ancien_statut,
            'nouveau_statut'  => $this->nouveau_statut,
            'message'         => $this->message,
            'lu'              => $this->lu ? 1 : 0,
            'date_creation'   => $this->date_creation,
        ];
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int)$row['id_contrat'],
            $row['titre_contrat'],
            $row['ancien_statut'],
            $row['nouveau_statut'],
            $row['message'],
            (bool)$row['lu'],
            $row['date_creation'],
            isset($row['id_notification']) ? (int)$row['id_notification'] : null
        );
    }
}
