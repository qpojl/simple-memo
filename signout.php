<?php
session_start();
$_SESSION = [];
session_destroy();
header("location:signin_form.php");
exit;
?>
