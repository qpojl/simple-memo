<?php

session_start();

require_once("config.php");

if(!isset($_SESSION["user_id"])){
    header("location:signin_form.php");
    exit;


    }



$id = $_POST["id"] ?? "";

if($_SERVER["REQUEST_METHOD"] === "POST"){

    if(!hash_equals($_SESSION["token"] ?? "",$_POST["token"] ?? "")){
        exit("Unauthorized access.");
    }


    $stmt = $pdo->prepare("DELETE FROM memos WHERE id = ? AND user_id = ?");
    $stmt->execute([$id,$_SESSION["user_id"]]);

    header("location:memos.php");
    exit;

}

$stmt = $pdo->prepare("SELECT * FROM memos WHERE user_id = ?");
$stmt ->execute([$_SESSION["user_id"]]);
$memos = $stmt->fetchAll();


?>


<!DOCTYPE html>
<html lang="ja">
    <head>
     <meta charset="UTF-8">
     <title>memos</title>
    <link rel="stylesheet" href="style.css">

    </head>
    <script>
document.addEventListener("click", function(e) {
    document.querySelectorAll("details[open]").forEach(function(d) {
        if (!d.contains(e.target)) {
            d.removeAttribute("open");
        }
    });
});
</script>
    <body>

        <div class="header">
            <h1>My memos</h1>
            <div class="header-actions">
                <a href="creatememo.php" class="btn">Create memo</a>
                <form method="post" action="signout.php">
                    <input type="hidden" name="token" value="<?php echo h(csrf_token());?>">
                    <input type="submit" value="Sign out" class="btn-plain">
                </form>
            </div>
        </div>
            
     <div class="memo-list">
            <?php foreach($memos as $memo) : ?>
                <div class="memo-card">
                    <h3><?php echo h($memo["title"]); ?></h3>
                    <p class="memo-body"><?php echo h($memo["body"]); ?></p>
                    <div class="memo-actions">
                        <a href = "editmemo.php?id=<?php echo h($memo["id"]); ?>">Edit</a>
                        <details>
                            <summary>⋮</summary>
                            <form method="post">
                                <input type="hidden" name="token" value="<?php echo h(csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo h($memo["id"]); ?>">
                                <input type="submit" value="Delete" class="btn-delete">
                            </form>
                        </details>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </body>

            

       
       

       
</html>

