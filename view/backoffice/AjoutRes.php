<?php

include '../../controller/RespondC.php';
$id_Post = $_GET['id_post'];
$id_Com = $_GET['id_com'];
$author = $_GET['author'];
$message = $_GET['message'];  
$time = $_GET['currentTime'];
$rc = new RespondC();
$r = new Respond($id_Post,$id_Com, $author, $message,$time);
$rc-> addRespond($r);
header('Location: index.php');

?>