<?php
class Post {
    private $id_User;
    private $author;
    private $message;
    private $time;
    private $image; // New property for image path
    private $status;

    public function __construct($id_User, $author, $message, $time, $image, $status) {
        $this->id_User = $id_User;
        $this->author = $author;
        $this->message = $message;
        $this->time = $time;
        $this->image = $image;
        $this->status = $status;
    }

    public function getId_User() {
        return $this->id_User;
    }

    public function setId_User($id_User) {
        $this->id_User = $id_User;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }

    public function getMessagePost() {
        return $this->message;
    }

    public function setMessagePost($message) {
        $this->message = $message;
    }

    public function getTime() {
        return $this->time;
    }

    public function setTime($time) {
        $this->time = $time;
    }

    public function getImage() {
        return $this->image;
    }

    public function setImage($image) {
        $this->image = $image;
    }
    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
}

?>
