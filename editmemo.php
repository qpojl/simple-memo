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
     <title>editmemo</title>
     <link rel="stylesheet" href="style.css">
    </head>

    <body class="editor-page">
        <div class="editor-header">
            <a href="memos.php">←</a>
            <span id="status"></span>
        </div>

        <form method="post" action="editmemo.php" class="editor">
            <input type="hidden" name="token" value="<?php echo h(csrf_token());?>">
            <input type="hidden" name="id" value="<?php echo h($memo["id"]);?>">
            <input type="text" name="title" class="editor-title" placeholder="Title" value="<?php echo h($memo["title"]);?>">
            <textarea name="body" class="editor-body" placeholder="Write something..." cols="30" rows="10"><?php echo h($memo["body"]);?></textarea>
            <button type="submit">Save</button>
        </form>
        
    </body>

</html>