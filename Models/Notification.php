<?php
// Models/Notification.php

class Notification {
    private $idNotification;
    private $user_id;
    private $message;
    private $date_notif;
    private $is_read;
    private $type;

    public function __construct($user_id = null, $message = '', $type = 'info') {
        $this->user_id = $user_id;
        $this->message = $message;
        $this->type = $type;
    }

    // Getters and Setters
    public function getIdNotification() { return $this->idNotification; }
    public function setIdNotification($id) { $this->idNotification = $id; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }

    public function getMessage() { return $this->message; }
    public function setMessage($msg) { $this->message = $msg; }

    public function getDateNotif() { return $this->date_notif; }
    public function setDateNotif($date) { $this->date_notif = $date; }

    public function getIsRead() { return $this->is_read; }
    public function setIsRead($val) { $this->is_read = $val; }

    public function getType() { return $this->type; }
    public function setType($type) { $this->type = $type; }
}
