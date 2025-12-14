<?php
include '../../controller/RespondC.php';
$rc = new RespondC();

$id = $_GET['id'];
$rc-> deleteRespond($id);
header('Location: index.php');
?>