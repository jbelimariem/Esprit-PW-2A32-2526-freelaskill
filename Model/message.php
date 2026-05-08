<?php
/**
 * Model/message.php
 * Attributs + Getters + Setters UNIQUEMENT
 */
class Message {

    private int    $id_message;
    private string $contenu;
    private string $date_envoi;
    private string $statut;
    private int    $id_conversation;
    private int    $id_expediteur;
    private string $contenu_traduit;
    private string $langue_source;

    public function __construct(
        int    $id_message      = 0,
        string $contenu         = '',
        string $date_envoi      = '',
        string $statut          = 'normal',
        int    $id_conversation = 0,
        int    $id_expediteur   = 0,
        string $contenu_traduit = '',
        string $langue_source   = ''
    ) {
        $this->id_message      = $id_message;
        $this->contenu         = $contenu;
        $this->date_envoi      = $date_envoi;
        $this->statut          = $statut;
        $this->id_conversation = $id_conversation;
        $this->id_expediteur   = $id_expediteur;
        $this->contenu_traduit = $contenu_traduit;
        $this->langue_source   = $langue_source;
    }

    public function getIdMessage():      int    { return $this->id_message;      }
    public function getContenu():        string { return $this->contenu;         }
    public function getDateEnvoi():      string { return $this->date_envoi;      }
    public function getStatut():         string { return $this->statut;          }
    public function getIdConversation(): int    { return $this->id_conversation; }
    public function getIdExpediteur():   int    { return $this->id_expediteur;   }
    public function getContenuTraduit(): string { return $this->contenu_traduit; }
    public function getLangueSource():   string { return $this->langue_source;   }

    public function setIdMessage(int $id):       void { $this->id_message      = $id;      }
    public function setContenu(string $contenu): void { $this->contenu         = $contenu; }
    public function setDateEnvoi(string $date):  void { $this->date_envoi      = $date;    }
    public function setStatut(string $statut):   void { $this->statut          = $statut;  }
    public function setIdConversation(int $id):  void { $this->id_conversation = $id;      }
    public function setIdExpediteur(int $id):    void { $this->id_expediteur   = $id;      }
    public function setContenuTraduit(string $contenu): void { $this->contenu_traduit = $contenu; }
    public function setLangueSource(string $langue):   void { $this->langue_source   = $langue; }
}
?>