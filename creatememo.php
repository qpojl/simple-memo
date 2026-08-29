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

    if(!hash_equals($_SESSION["token"] ?? "",$_POST["token"] ?? "")){
        exit("Unauthorized access.");
    }

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
        <link rel="stylesheet" href="style.css">
    </head>

    <body class="editor-page">

        <div class="editor-header">
            <a href="memos.php">←</a>
            <span id="status"></span>
        </div>

    <form method="post" class="editor">
        <input type="hidden" name="token" value="<?php echo h(csrf_token()); ?>">
        <input type="text" name="title" class="editor-title" placeholder="Title">
        <textarea name="body" class="editor-body" placeholder="Start writing..." cols="30" rows="10"></textarea>
        <button type="submit" class="btn">Save</button>

    </form>
    

    







<?php





?>



</body>
</html>

