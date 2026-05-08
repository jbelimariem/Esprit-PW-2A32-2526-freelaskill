<?php
/**
 * Model/GitCommit.php
 * Attributs + Getters + Setters UNIQUEMENT (MVC)
 */
class GitCommit {

    private int    $id_commit;
    private int    $id_conversation;
    private string $branche;
    private string $message;
    private string $auteur;
    private int    $id_user;
    private string $date_commit;
    private string $hash;
    private string $snapshot;   // JSON snapshot des messages à ce moment

    public function __construct(
        int    $id_commit        = 0,
        int    $id_conversation  = 0,
        string $branche          = 'main',
        string $message          = '',
        string $auteur           = '',
        int    $id_user          = 0,
        string $date_commit      = '',
        string $hash             = '',
        string $snapshot         = '[]'
    ) {
        $this->id_commit        = $id_commit;
        $this->id_conversation  = $id_conversation;
        $this->branche          = $branche;
        $this->message          = $message;
        $this->auteur           = $auteur;
        $this->id_user          = $id_user;
        $this->date_commit      = $date_commit;
        $this->hash             = $hash;
        $this->snapshot         = $snapshot;
    }

    public function getIdCommit():        int    { return $this->id_commit;       }
    public function getIdConversation():  int    { return $this->id_conversation; }
    public function getBranche():         string { return $this->branche;         }
    public function getMessage():         string { return $this->message;         }
    public function getAuteur():          string { return $this->auteur;          }
    public function getIdUser():          int    { return $this->id_user;         }
    public function getDateCommit():      string { return $this->date_commit;     }
    public function getHash():            string { return $this->hash;            }
    public function getSnapshot():        string { return $this->snapshot;        }

    public function setIdCommit(int $id):          void { $this->id_commit       = $id;  }
    public function setIdConversation(int $id):    void { $this->id_conversation = $id;  }
    public function setBranche(string $b):         void { $this->branche         = $b;   }
    public function setMessage(string $msg):       void { $this->message         = $msg; }
    public function setAuteur(string $a):          void { $this->auteur          = $a;   }
    public function setIdUser(int $id):            void { $this->id_user         = $id;  }
    public function setDateCommit(string $date):   void { $this->date_commit     = $date;}
    public function setHash(string $hash):         void { $this->hash            = $hash;}
    public function setSnapshot(string $snap):     void { $this->snapshot        = $snap;}
}
?>
