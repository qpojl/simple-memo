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
