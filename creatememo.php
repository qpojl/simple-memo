<?php
session_start();

require_once("config.php");

if(!isset($_SESSION["user_id"])){
    header("location:signin_form.php");
    exit;
}



$title = $_POST["title"] ?? "";
$body = $_POST["body"] ?? "";

if($_SERVER["REQUEST_METHOD"] === "POST"){

    if($title === ""){
        $title = "No title";
    }

    $stmt = $pdo->prepare("INSERT INTO memos (title,body,user_id) VALUES (?,?,?)");
    $stmt->execute([$title,$body,$_SESSION["user_id"]]);

    header("location:memos.php");
    exit;

}

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
     <meta charset="UTF-8">
     <title>creatememo</title>
    </head>
    <body>

    <form method="post">

    <br>
    <input type="text" name="title">
    <br>
    <textarea name="body" cols="30" rows="10"></textarea>
    <br>
    <input type="submit" value="Save">

    </form>
    <br>

    <a href="memos.php">memos</a>







<?php





?>



</body>
</html>

