<?php

    session_start();

    require_once("config.php");


    if(!isset($_SESSION["user_id"])){
        header("location:signin_form.php");
        exit;
    }


    
    $title = $_POST["title"] ?? "";
    $body = $_POST["body"] ?? "";
    $id =$_POST["id"] ?? $_GET["id"] ?? "";

    if($_SERVER["REQUEST_METHOD"] === "POST"){

        if(!hash_equals($_SESSION["token"] ?? "",$_POST["token"] ?? "")){
            exit("Unauthorized access.");
        }
        
        if($title === ""){
            $title = "No title";
        }

        $stmt = $pdo->prepare("UPDATE memos SET title = ?, body = ? WHERE user_id = ? AND id = ?");
        $stmt->execute([$title,$body,$_SESSION["user_id"],$id]);

        header("location:memos.php");
        exit;

    }


    $stmt = $pdo->prepare("SELECT * FROM memos WHERE id = ? AND user_id = ?");
    $stmt->execute([$id,$_SESSION["user_id"]]);
    $memo = $stmt->fetch();

    if (!$memo){
        header("location:memos.php");
        exit;
    }



?>




<!DOCTYPE html>
<html lang="ja">
    <head>
     <meta charset="UTF-8">

     <link rel="stylesheet" href="style.css">

    </head>

    <body>

        <form method="post" action="editmemo.php">
        <input type="hidden" name="token" value="<?php echo h(csrf_token());?>">
        <input type="hidden" name="id" value="<?php echo h($memo["id"]);?>">
        <br>
        <input type="text" name="title" value="<?php echo h($memo["title"]);?>">
        <br>
        <textarea name="body" cols="30" rows="10"><?php echo h($memo["body"]);?></textarea>
        <br>
        <input type="submit" value="Save">

        <br>
        <br>
        

        </form>

<a href="memos.php">Back</a>
    
    </body>
</html>