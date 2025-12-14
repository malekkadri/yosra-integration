<?php
include '../../controller/PostC.php';
include '../../controller/CommentC.php';
include '../../controller/RespondC.php';

$pc = new PostC();
$cc = new CommentC();
$rc = new RespondC();

$id = $_GET['id'];

$pc-> deletePost($id);
$cc-> deleteComPost($id);
$rc-> deleteResComPost($id);
header('Location: index.php');
?>