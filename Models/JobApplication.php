<?php
// Models/JobApplication.php — Modèle Entité avec Getters/Setters pour les candidatures

require_once __DIR__ . '/../config.php';

class JobApplication {
    private $id;
    private $job_id;
    private $name;
    private $job_title;
    private $status;
    private $created_at;

    private $pdo;

    public function __construct($data = []) {
        $this->pdo = config::getConnexion();
        if (!empty($data)) {
            $this->id         = $data['id']         ?? null;
            $this->job_id     = $data['job_id']     ?? null;
            $this->name       = $data['name']       ?? '';
            $this->job_title  = $data['job_title']  ?? '';
            $this->status     = $data['status']     ?? 'pending';
            $this->created_at = $data['created_at'] ?? null;
        }
    }

    // --- GETTERS ---
    public function getId()        { return $this->id; }
    public function getJobId()     { return $this->job_id; }
    public function getName()      { return $this->name; }
    public function getJobTitle()  { return $this->job_title; }
    public function getStatus()    { return $this->status; }
    public function getCreatedAt() { return $this->created_at; }

}
