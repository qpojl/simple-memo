<?php
session_start();

if (!hash_equals($_SESSION["token"] ?? "", $_POST["token"] ?? "")) {
    exit("Unauthorized access.");
}

$_SESSION = [];
session_destroy();
header("location:signin_form.php");
exit;
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>Sign out</title>

        <link rel="stylesheet" href="style.css">


    </head>