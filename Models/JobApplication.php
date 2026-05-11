<?php
// Models/JobApplication.php — Modèle Entité avec Getters pour les candidatures

require_once __DIR__ . '/../config.php';

class JobApplication {
    private $id;
    private $job_id;
    private $name;
    private $email;
    private $job_title;
    private $status;
    private $created_at;
    private $freelancer_id;
    private $phone;
    private $cover_letter;
    private $cv_path;
    private $message;   // ancienne colonne (lettre de motivation)
    private $cv_link;   // ancienne colonne (lien CV)

    private $pdo;

    // Initialise les attributs à partir d'un tableau $data venant de la BDD
    public function __construct($data = []) {
        $this->pdo = config::getConnexion();
        if (!empty($data)) {
            $this->id            = $data['id']            ?? null;
            $this->job_id        = $data['job_id']        ?? null;
            $this->name          = $data['name']          ?? '';
            $this->email         = $data['email']         ?? '';
            $this->job_title     = $data['job_title']     ?? '';
            $this->status        = $data['status']        ?? 'pending';
            $this->created_at    = $data['created_at']    ?? null;
            $this->freelancer_id = $data['freelancer_id'] ?? null;
            $this->phone         = $data['phone']         ?? '';
            $this->cover_letter  = $data['cover_letter']  ?? '';
            $this->cv_path       = $data['cv_path']       ?? '';
            $this->message       = $data['message']       ?? '';
            $this->cv_link       = $data['cv_link']       ?? '';
        }
    }

    // --- GETTERS ---
    public function getId()           { return $this->id; }
    public function getJobId()        { return $this->job_id; }
    public function getName()         { return $this->name; }
    public function getEmail()        { return $this->email; }
    public function getJobTitle()     { return $this->job_title; }
    public function getStatus()       { return $this->status; }
    public function getCreatedAt()    { return $this->created_at; }
    public function getFreelancerId() { return $this->freelancer_id; }
    public function getPhone()        { return $this->phone; }
    public function getCoverLetter()  { return $this->cover_letter; }
    public function getCvPath()       { return $this->cv_path; }
    public function getMessage()      { return $this->message; }
    public function getCvLink()       { return $this->cv_link; }
}
