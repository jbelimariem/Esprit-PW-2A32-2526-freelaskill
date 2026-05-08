<?php
/**
 * Model/GitRepository.php
 * Attributs + Getters + Setters UNIQUEMENT (MVC)
 */
class GitRepository {

    private int    $id_repo;
    private int    $id_conversation;
    private string $nom;
    private string $description;
    private string $date_creation;
    private string $branche_active;

    public function __construct(
        int    $id_repo          = 0,
        int    $id_conversation  = 0,
        string $nom              = 'main',
        string $description      = '',
        string $date_creation    = '',
        string $branche_active   = 'main'
    ) {
        $this->id_repo         = $id_repo;
        $this->id_conversation = $id_conversation;
        $this->nom             = $nom;
        $this->description     = $description;
        $this->date_creation   = $date_creation;
        $this->branche_active  = $branche_active;
    }

    public function getIdRepo():         int    { return $this->id_repo;         }
    public function getIdConversation(): int    { return $this->id_conversation; }
    public function getNom():            string { return $this->nom;             }
    public function getDescription():   string { return $this->description;      }
    public function getDateCreation():  string { return $this->date_creation;    }
    public function getBrancheActive(): string { return $this->branche_active;   }

    public function setIdRepo(int $id):            void { $this->id_repo         = $id;    }
    public function setIdConversation(int $id):    void { $this->id_conversation = $id;    }
    public function setNom(string $nom):           void { $this->nom             = $nom;   }
    public function setDescription(string $desc):  void { $this->description     = $desc;  }
    public function setDateCreation(string $date): void { $this->date_creation   = $date;  }
    public function setBrancheActive(string $b):   void { $this->branche_active  = $b;     }
}
?>
