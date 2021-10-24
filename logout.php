<?
session_start();

$_SESSION["logged"] = 0;

session_destroy();

header("Location: /login.php");
?>