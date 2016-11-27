<?php
// get correct file path
require_once("session.php");
require_once("class.user.php");

$database = new Database();
$db = $database->dbConnection();
$fileName = $_GET['name'];
$stmt_1=$db->prepare("UPDATE images SET shared=1 WHERE img_name=:img_name");
$stmt_1->bindparam(":img_name", $fileName);
// remove file if it exists
$stmt_1->execute();
header('Location:index.php');
?>