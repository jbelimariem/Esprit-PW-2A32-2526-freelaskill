<?php
/**
 * Model/conversation.php
 * Attributs + Getters + Setters UNIQUEMENT
 */
class Conversation {

    private int     $id_conversation;
    private string  $date_creation;
    private string  $statut;
    private ?string $titre;
    private int     $id_user1;
    private int     $id_user2;

    public function __construct(
        int     $id_conversation = 0,
        string  $date_creation   = '',
        string  $statut          = 'active',
        ?string $titre           = null,
        int     $id_user1        = 0,
        int     $id_user2        = 0
    ) {
        $this->id_conversation = $id_conversation;
        $this->date_creation   = $date_creation;
        $this->statut          = $statut;
        $this->titre           = $titre;
        $this->id_user1        = $id_user1;
        $this->id_user2        = $id_user2;
    }

    public function getIdConversation(): int     { return $this->id_conversation; }
    public function getDateCreation():   string  { return $this->date_creation;   }
    public function getStatut():         string  { return $this->statut;          }
    public function getTitre():          ?string { return $this->titre;           }
    public function getIdUser1():        int     { return $this->id_user1;        }
    public function getIdUser2():        int     { return $this->id_user2;        }

    public function setIdConversation(int $id):      void { $this->id_conversation = $id;     }
    public function setDateCreation(string $date):   void { $this->date_creation   = $date;   }
    public function setStatut(string $statut):       void { $this->statut          = $statut; }
    public function setTitre(?string $titre):        void { $this->titre           = $titre;  }
    public function setIdUser1(int $id):             void { $this->id_user1        = $id;     }
    public function setIdUser2(int $id):             void { $this->id_user2        = $id;     }
}
?>