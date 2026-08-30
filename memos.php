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

        <div class="space"></div>

        <div class="modal" id="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <a id="modal-expand" href="">Open in full page</a>
                    <button id="modal-close" class="btn-plain">Close</button>
                </div>
                
                <input type="hidden" id="modal-id">
                <input type="hidden" id="modal-token" value="<?php echo h(csrf_token()); ?>">
                <input type="text" id="modal-title" placeholder="Title">
                <textarea id="modal-body" placeholder="Write something..."></textarea>
                
                <div class="modal-footer">
                    <span id="modal-status"></span>
                    <div class="modal-buttons">
                    </div>
                </div>
            </div>
        </div>

        <div class="create-box">
            <input type="hidden" id="create-token" value="<?php echo h(csrf_token()); ?>">
            <input type="text" id="create-title" placeholder="Title">
            <textarea id="create-body" placeholder="Write something..."></textarea>
            <div class="create-footer">
                <button id="create-save" class="add-btn">Add</button>
            </div>
        </div>
        
        <div class="memo-list">
            <?php foreach($memos as $memo) : ?>
                <div class="memo-card" data-id="<?php echo h ($memo["id"]); ?>"
                    data-title="<?php echo h($memo["title"]); ?>"
                    data-body="<?php echo h($memo["body"]); ?>">
                    <h3><?php echo h($memo["title"]); ?></h3>
                    <p class="memo-body"><?php echo h($memo["body"]); ?></p>
                    <div class="memo-actions">
                        
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


        <script>
           

            document.querySelectorAll(".memo-card").forEach(function(card) {
                card.addEventListener("click",function(e){
                    document.querySelector("#modal-id").value = card.dataset.id;
                    document.querySelector("#modal-title").value = card.dataset.title;
                    document.querySelector("#modal-body").value = card.dataset.body;
                    document.querySelector("#modal-expand").href = "editmemo.php?id=" + card.dataset.id;
                    document.querySelector("#modal").style.display = "flex";
                    
                });
            });

            document.querySelectorAll(".memo-actions details").forEach(function(details) {

                details.addEventListener("click", function(e) {
                    e.stopPropagation();
                })
            });   

            

            document.querySelector("#modal-close").addEventListener("click",function() {
                document.querySelector("#modal").style.display = "none";
            })

            document.addEventListener("click", function(e) {
                document.querySelectorAll("details[open]").forEach(function(d) {
                    if (!d.contains(e.target)) {
                        d.removeAttribute("open");
                    }
                });
            });

            function autoSave() {
                const data = new FormData();
                data.append("id",document.querySelector("#modal-id").value);
                data.append("title",document.querySelector("#modal-title").value);
                data.append("body",document.querySelector("#modal-body").value);
                data.append("token",document.querySelector("#modal-token").value);                   
                fetch("api.php",{
                    method:"POST",
                    body: data
                })
                .then(function(res){
                    return res.json();
                })
                .then(function(json){
                    if (json.success) {
                        const id = document.querySelector("#modal-id").value;
                        const card = document.querySelector('.memo-card[data-id="' + id + '"]');

                        const newTitle = document.querySelector("#modal-title").value;
                        const newBody = document.querySelector("#modal-body").value;

                        card.querySelector("h3").textContent = newTitle;
                        card.querySelector(".memo-body").textContent = newBody;

                        card.dataset.title = newTitle;
                        card.dataset.body = newBody;

                        const status = document.querySelector("#modal-status");
                        status.textContent = "✓ Saved";
                        status.classList.add("show");

                        setTimeout(function(){
                            status.classList.remove("show");

                        },2000);


                        

                    }
                });
                
            }
            
            let saveTimer = null;
            document.querySelector("#modal-title").addEventListener("input",function(){
                document.querySelector("#modal-status").classList.remove("show");
                clearTimeout(saveTimer);
                saveTimer = setTimeout(autoSave,1000);
            });

            document.querySelector("#modal-body").addEventListener("input",function(){
                document.querySelector("#modal-status").classList.remove("show");
                clearTimeout(saveTimer);
                saveTimer = setTimeout(autoSave, 1000);
            });
            
            document.querySelector("#modal").addEventListener("click",function(e){
                if (e.target === this){
                    document.querySelector("#modal").style.display = "none";
                }
            });

            document.addEventListener("keydown",function(e){
                if (e.key === "Escape"){
                    document.querySelector("#modal").style.display = "none";
                }
            });

            
            
            

        </script>
    </body>
  
</html>

