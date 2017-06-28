<?php
session_start();
header('Content-type: text/plain');
$fileName="";
$uid=$_SESSION["uid"];
$downType=($_GET["q"]=="css")?"css":"html";
$fileName="cssout$uid.$downType";
$content=($downType=="css")? $_SESSION["cAdStyle"]:$_SESSION["cDocument"];
header('Content-Disposition: attachment; filename="'.$fileName.'"');
print($content);
?>