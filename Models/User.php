<?php
// Models/User.php

class User {
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $password;
    private $role;
    private $bio;
    private $avatar;
    private $status;
    private $created_at;
    private $github_url;
    private $linkedin_url;
    private $cv_url;
    private $portfolio_url;
    private $face_descriptor;

    public function __construct($nom = '', $prenom = '', $email = '', $password = '', $role = 'client', $bio = '', $avatar = '', $status = 'active', $github_url = '', $linkedin_url = '', $cv_url = null, $portfolio_url = null) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->bio = $bio;
        $this->avatar = $avatar;
        $this->status = $status;
        $this->github_url = $github_url;
        $this->linkedin_url = $linkedin_url;
        $this->cv_url = $cv_url;
        $this->portfolio_url = $portfolio_url;
    }

    // --- Getters & Setters ---

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getNom() { return $this->nom; }
    public function setNom($nom) { $this->nom = $nom; }

    public function getPrenom() { return $this->prenom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getPassword() { return $this->password; }
    public function setPassword($password) { $this->password = $password; }

    public function getRole() { return $this->role; }
    public function setRole($role) { $this->role = $role; }

    public function getBio() { return $this->bio; }
    public function setBio($bio) { $this->bio = $bio; }

    public function getAvatar() { return $this->avatar; }
    public function setAvatar($avatar) { $this->avatar = $avatar; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }

    public function getGithubUrl() { return $this->github_url; }
    public function setGithubUrl($github_url) { $this->github_url = $github_url; }

    public function getLinkedinUrl() { return $this->linkedin_url; }
    public function setLinkedinUrl($linkedin_url) { $this->linkedin_url = $linkedin_url; }

    public function getCvUrl() { return $this->cv_url; }
    public function setCvUrl($cv_url) { $this->cv_url = $cv_url; }

    public function getPortfolioUrl() { return $this->portfolio_url; }
    public function setPortfolioUrl($portfolio_url) { $this->portfolio_url = $portfolio_url; }

    public function getFaceDescriptor() { return $this->face_descriptor; }
    public function setFaceDescriptor($face_descriptor) { $this->face_descriptor = $face_descriptor; }

}
