<?php
require_once("settings.php");
session_start();
$username = isset($_POST["username"]) ? $_POST["username"] : $_GET["username"];
$password = isset($_POST["password"]) ? $_POST["password"] : $_GET["password"];
if($username == USER_USERNAME && $password == USER_PASSWORD) //very basic, but only for now
{
    $_SESSION["loggedin"] = true;
}
elseif($username != "" && $password != "")
{
    $wrongPassword = true;
}
if($_SESSION["loggedin"] !== true)
{
    session_write_close();
    header("HTTP/1.1 403 Incorrect Username and/or Password");
    require_once("login.php");
    exit;
}
session_write_close();
?>
