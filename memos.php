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

$stmt = $pdo->prepare("SELECT * FROM memos WHERE user_id = ? ORDER BY created DESC, id DESC");
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
           

            document.querySelector(".memo-list").addEventListener("click",function(e) {
                if (document.querySelector("details[open]")) return;
                if (e.target.closest("details")) return;
            
            
                const card = e.target.closest(".memo-card");
                if (!card) return;

                document.querySelector("#modal-id").value = card.dataset.id;
                document.querySelector("#modal-title").value = card.dataset.title;
                document.querySelector("#modal-body").value = card.dataset.body;
                
                document.querySelector("#modal").style.display = "flex";
                    
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

            document.querySelector("#create-save").addEventListener("click",function(){
                const data = new FormData();
                data.append("title",document.querySelector("#create-title").value);
                data.append("body",document.querySelector("#create-body").value);
                data.append("token",document.querySelector("#create-token").value);

                fetch("create_api.php", {
                    method:"POST",
                    body:data
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(json) {
                    if (json.success) {
                        
                       
                    
                
            

                        const title = document.querySelector("#create-title").value;
                        const body = document.querySelector("#create-body").value;

                        const card = document.createElement("div");
                        card.className = "memo-card";
                        card.dataset.id = json.id;
                        card.dataset.title = title;
                        card.dataset.body = body;

                        card.innerHTML =
                            '<h3></h3>' +
                            '<p class="memo-body"></p>' +
                            '<div class="memo-actions">' +
                                '<details>' +
                                    '<summary>⋮</summary>' +
                                    '<form method="post">' +
                                        '<input type="hidden" name="token" value="'+document.querySelector("#create-token").value +'">' +
                                        '<input type="hidden" name="id" value="' + json.id + '">' +
                                        '<input type="submit" value="Delete" class="btn-delete">' +
                                    '</form>' +
                                '</details>' +
                            '</div>';

                        card.querySelector("h3").textContent = title;
                        card.querySelector(".memo-body").textContent = body;

                        document.querySelector(".memo-list").prepend(card);

                        document.querySelector("#create-title").value = "";
                        document.querySelector("#create-body").value = "";
                    }
                });
            });
            
            document.querySelector(".memo-list").addEventListener("submit", function(e) {
                e.preventDefault();

                const form = e.target;
                const card = form.closest(".memo-card");

                const data = new FormData(form);

                fetch("delete_api.php", {
                    method: "POST",
                    body: data
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(json) {
                    if (json.success) {
                        card.remove();
                    }
                })
            })

        </script>
    </body>
  
</html>

