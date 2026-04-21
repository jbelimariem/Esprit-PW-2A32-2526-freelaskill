<?php
// Models/User.php

class User implements ArrayAccess, JsonSerializable
{
    private $id = null;
    private $nom = '';
    private $prenom = '';
    private $email = '';
    private $password = '';
    private $role = 'client';
    private $bio = '';
    private $avatar = '';
    private $status = 'active';
    private $created_at = null;
    private $github_url = '';
    private $linkedin_url = '';
    private $cv_url = null;
    private $portfolio_url = null;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data)
    {
        $mapping = [
            'id' => 'setId',
            'nom' => 'setNom',
            'prenom' => 'setPrenom',
            'email' => 'setEmail',
            'password' => 'setPassword',
            'role' => 'setRole',
            'bio' => 'setBio',
            'avatar' => 'setAvatar',
            'status' => 'setStatus',
            'created_at' => 'setCreatedAt',
            'github_url' => 'setGithubUrl',
            'linkedin_url' => 'setLinkedinUrl',
            'cv_url' => 'setCvUrl',
            'portfolio_url' => 'setPortfolioUrl',
        ];

        foreach ($mapping as $field => $setter) {
            if (array_key_exists($field, $data)) {
                $this->$setter($data[$field]);
            }
        }

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id === null ? null : (int) $id;
        return $this;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($nom)
    {
        $this->nom = trim((string) $nom);
        return $this;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($prenom)
    {
        $this->prenom = trim((string) $prenom);
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = trim((string) $email);
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = (string) $password;
        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $value = trim((string) $role);
        $this->role = $value !== '' ? $value : 'client';
        return $this;
    }

    public function getBio()
    {
        return $this->bio;
    }

    public function setBio($bio)
    {
        $this->bio = trim((string) $bio);
        return $this;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function setAvatar($avatar)
    {
        $this->avatar = trim((string) $avatar);
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $value = trim((string) $status);
        $this->status = $value !== '' ? $value : 'active';
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
        return $this;
    }

    public function getGithubUrl()
    {
        return $this->github_url;
    }

    public function setGithubUrl($githubUrl)
    {
        $this->github_url = trim((string) $githubUrl);
        return $this;
    }

    public function getLinkedinUrl()
    {
        return $this->linkedin_url;
    }

    public function setLinkedinUrl($linkedinUrl)
    {
        $this->linkedin_url = trim((string) $linkedinUrl);
        return $this;
    }

    public function getCvUrl()
    {
        return $this->cv_url;
    }

    public function setCvUrl($cvUrl)
    {
        $this->cv_url = ($cvUrl === '' ? null : $cvUrl);
        return $this;
    }

    public function getPortfolioUrl()
    {
        return $this->portfolio_url;
    }

    public function setPortfolioUrl($portfolioUrl)
    {
        $this->portfolio_url = ($portfolioUrl === '' ? null : $portfolioUrl);
        return $this;
    }

    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset)
    {
        return property_exists($this, $offset) ? $this->$offset : null;
    }

    public function offsetSet($offset, $value)
    {
        if (property_exists($this, $offset)) {
            $this->$offset = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset)) {
            $this->$offset = null;
        }
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'role' => $this->role,
            'bio' => $this->bio,
            'avatar' => $this->avatar,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'github_url' => $this->github_url,
            'linkedin_url' => $this->linkedin_url,
            'cv_url' => $this->cv_url,
            'portfolio_url' => $this->portfolio_url,
        ];
    }
}
