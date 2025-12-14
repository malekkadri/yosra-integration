<?php
include '../../controller/CommentC.php';
$cc = new CommentC();

include '../../controller/RespondC.php';
$rc = new RespondC();

$id = $_GET['id'];

$cc-> deleteComment($id);
$rc-> deleteResCom($id);

header('Location: index.php');
?>