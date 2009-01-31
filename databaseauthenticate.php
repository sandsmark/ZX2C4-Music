<?php
require_once("settings.php");
$dbpassword = isset($_POST["dbpassword"]) ? $_POST["dbpassword"] : $_GET["dbpassword"];
if($dbpassword != DATABASE_PASSWORD)
{
    $databaseLogin = true;
    header("HTTP/1.1 403 Incorrect Username and/or Password");
    require_once("login.php");
    exit;
}
?>