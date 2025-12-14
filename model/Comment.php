<?php

class Comment

{
  private $id_Post;
  private $author;
  private $message;
  private $time;

public function __construct($id_Post, $author, $message, $time) {
    $this->id_Post = $id_Post;
    $this->author = $author;
    $this->message = $message;
    $this->time = $time;
}


    public function getId_Post()
  {
    return $this->id_Post;
  }

  public function setId_Post($id_Post)
  {
    $this->id_Post = $id_Post;
  }
  public function getAuthor()
  {
    return $this->author;
  }

  public function setAuthor($author)
  {
    $this->author = $author;
  }

  public function getMessageCom()
  {
    return $this->message;
  }

  public function setMessageCom($message)
  {
    $this->message = $message;
  }

  public function getTime()
  {
    return $this->time;
  }

  public function setTime($time)
  {
   $this->time = $time;
  }
}