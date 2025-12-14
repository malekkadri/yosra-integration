<?php
include '../../controller/RespondC.php';
$author = $_GET['author'];
$message = $_GET['message'];
$time = $_GET['currentTime'];
$id = $_GET['id'];
$id_Com = $_GET['id_com'];
$id_Post = $_GET['id_post'];

$rc = new RespondC();
$r = new Respond($id_Post, $id_Com, $author, $message, $time);
$rc->modifyRespond($r,$id);

header('Location: index.php');
?>
