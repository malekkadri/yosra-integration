<?php
include '../../controller/CommentC.php';
$author = $_GET['author'];
$message = $_GET['message'];
$time = $_GET['currentTime'];
$id = $_GET['id'];
$id_Post = $_GET['id_post'];

$cc = new CommentC();
$c = new Comment($id_Post, $author, $message,$time);
$cc->modifyComment($c,$id);
header('Location: index.php');
?>
