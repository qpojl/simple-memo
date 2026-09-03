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

$filter = $_GET["filter"] ?? "";
$q = $_GET["q"] ?? "";
$tagId = $_GET["tag"] ?? "";

$sql = "SELECT * FROM memos WHERE user_id = ?";
$params = [$_SESSION["user_id"]];

if ($filter === "favorite") {
    $sql .= " AND favorite = 1";
}

if ($q !== "") {
    $sql .= " AND (title LIKE ? OR body LIKE ?)";
    $params[]= "%" . $q . "%";
    $params[]= "%" . $q .  "%";

}



if ($tagId !== "") {
    $sql .= " AND id IN (SELECT memo_id FROM memo_tags WHERE tag_id = ?)";
    $params[] = $tagId;
}


$sql .= " ORDER BY created DESC, id DESC";





$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$memos = $stmt->fetchAll();







?>


<!DOCTYPE html>
<html lang="ja">
    <head>
     <meta charset="UTF-8">
     <title>memos</title>
    <link rel="stylesheet" href="style.css">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    </head>
    
    <body>

        

        <div class="header">
            <h1><a href="memos.php" class="brand">memos</a></h1>

            <button type="button" id="palette-open" class="search-trigger">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <span>Search memos...</span>
            </button>
            <div class="header-actions">
                

                <form method="post" action="signout.php">
                    <input type="hidden" name="token" value="<?php echo h(csrf_token());?>">
                    <input type="submit" value="Sign out" class="btn-plain">
                </form>
            </div>
        </div>

        <div class="space"></div>

        <div class="palette" id="palette">
            <div class="palette-content">
                <input type="text" id="palette-input" placeholder="Search memos..." value="<?php echo h($q); ?>">
                <div class="palette-tags" id="palette-tags"></div>
            </div>
        </div>

        <div class="modal" id="tag-modal">
            <div class="modal-content tag-modal-content">
                <div class="modal-header">
                    <span class="tag-modal-title">Tags</span>
                    <button id="tag-modal-close" class="btn-close" aria-label="Close">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/>
                            <path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>
            
                <input type="hidden" id="tag-modal-memo-id">
                <input type="hidden" id="tag-token" value="<?php echo h(csrf_token()); ?>">

                <div class="tag-list" id="tag-list"></div>

                <div class="tag-create">
                    <input type="text" id="tag-new-name" placeholder="New tag name">
                    <button type="button" id="tag-create-btn" class="add-btn">Create</button>
                </div>
            </div>
        </div>

        <div class="modal" id="modal">
            <div class="modal-content">
                <div class="modal-header">

                    <button type="button" id="modal-tag-btn" class="btn-tag" aria-label="Tags">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/>
                            <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>
                        </svg>
                    </button>
                    
                    <button id="modal-close" class="btn-close" aria-label="Close">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/>
                            <path d="m6 6 12 12"/>
                        </svg>

                    </button>
                </div>
                
                <input type="hidden" id="modal-id">
                <input type="hidden" id="modal-token" value="<?php echo h(csrf_token()); ?>">
                <input type="text" id="modal-title" placeholder="Title">
                <textarea id="modal-body" placeholder="Write something..."></textarea>
                
                <div class="modal-footer">
                    <span id="modal-status"></span>
                    
                
                </div>
            </div>
        </div>
        

        <div class="create-box">
            <input type="hidden" id="create-token" value="<?php echo h(csrf_token()); ?>">
            <input type="text" id="create-title" placeholder="Title">
            <div class="create-body">
                <div class="create-row">
                    <div class="body-wrap">
                        <textarea id="create-body" ></textarea>
                        <div class="fake-placeholder" id="create-placeholder">Write something...</div>
                    </div>
                    <button id="create-save" class="add-btn">Add</button>
                </div>
            </div>
        </div>
        
        <div class="memo-list">
            <?php foreach($memos as $memo) : ?>
                <div class="memo-card" data-id="<?php echo h ($memo["id"]); ?>"
                    
                    data-title="<?php echo h($memo["title"]); ?>"
                    data-body="<?php echo h($memo["body"]); ?>">
                    <div class="memo-card-inner">
                        <h3><?php echo h($memo["title"]); ?></h3>
                        <p class="memo-body"><?php echo h($memo["body"]); ?></p>
                        <div class="memo-actions">
                            
                            
                                
                            <form method="post">
                                <input type="hidden" name="token" value="<?php echo h(csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo h($memo["id"]); ?>">
                                
                                <button type="button" class="btn-favorite" data-id="<?php echo h($memo["id"]); ?>" aria-label="Bookmark">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="<?php echo $memo["favorite"] ? "currentColor" : "none"; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                                        </svg>
                                </button> 


                                <button type="button" class="btn-tag" data-id="<?php echo h($memo["id"]); ?>" aria-label="Tags">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/>
                                        <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>
                                    </svg>
                                </button>


                                <button type="submit" value="Delete" class="btn-delete" aria-label="Delete">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>   
                            </form>
                            
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>

        <script>
           
           const msnry = new Masonry(".memo-list", {
            itemSelector: ".memo-card" ,
            columnWidth: ".memo-card",
            gutter: 16,
            fitWidth: true,
           });

            document.querySelector(".memo-list").addEventListener("click",function(e) {
               if (e.target.closest(".memo-actions")) return;
            
            
                const card = e.target.closest(".memo-card");
                if (!card) return;

                document.querySelector("#modal-id").value = card.dataset.id;
                document.querySelector("#modal-title").value = card.dataset.title;
                document.querySelector("#modal-body").value = card.dataset.body;
                
                document.querySelector("#modal").classList.add("show");
                    
            });
            


            

            document.querySelector("#modal-close").addEventListener("click",function() {
                document.querySelector("#modal").classList.remove("show");
            })

            

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

                        msnry.layout();

                        const status = document.querySelector("#modal-status");
                        status.textContent = "Saved";
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
                    document.querySelector("#modal").classList.remove("show");
                }
            });

            document.addEventListener("keydown",function(e){
                if (e.key === "Escape"){
                    document.querySelector("#modal").classList.remove("show");
                    document.querySelector("#palette").classList.remove("show");
                    document.querySelector("#tag-modal").classList.remove("show");
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
                        card.dataset.body = json.body;

                        card.innerHTML = `
                             <div class="memo-card-inner">
                                <h3></h3>
                                <p class="memo-body"></p>
                                <div class="memo-actions">
                                    <form method="post">
                                        <input type="hidden" name="token" value="${document.querySelector("#create-token").value}">
                                        <input type="hidden" name="id" value="${json.id}">
                                        <button type="button" class="btn-favorite" data-id="${json.id}" aria-label="Bookmark">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                                            </svg> 
                                        </button>

                                        <button type="button" class="btn-tag" data-id="${json.id}" aria-label="Tags">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/>
                                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>
                                            </svg>
                                        </button>

                                        <button type="submit" class="btn-delete" aria-label="Delete">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        `;
                        card.querySelector("h3").textContent = title;
                        card.querySelector(".memo-body").textContent = json.body;

                        document.querySelector(".memo-list").prepend(card);
                        msnry.prepended(card);

                        document.querySelector("#create-title").value = "";
                        document.querySelector("#create-body").value = "";
                        ph.style.display = "";
                        document.querySelector("#create-body").style.height = "auto";
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
                        msnry.layout();
                    }
                })
            })

            document.querySelector(".memo-list").addEventListener("click", function(e) {
                const btn = e.target.closest(".btn-favorite");
                if (!btn) return;

                const data = new FormData();
                data.append("id" , btn.dataset.id);
                data.append("token", document.querySelector("#create-token").value);

                fetch("favorite_api.php", {
                    method: "POST",
                    body: data
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(json){
                    if (json.success) {
                        const svg = btn.querySelector("svg");
                        svg.setAttribute("fill", json.favorite == 1 ? "currentColor" : "none");

                        if (json.favorite == 0 && new URLSearchParams(location.search).get("filter") === "favorite") {
                            btn.closest(".memo-card").remove();
                            msnry.layout();
                        }   

                    }
                });

            });    

            document.querySelector("#palette-open").addEventListener("click", function() {
                document.querySelector("#palette").classList.add("show");
                loadPaletteTags();
                setTimeout(function(){
                    document.querySelector("#palette-input").focus();
                }, 50);
            });

            function loadPaletteTags() {
                const data = new FormData();
                data.append("token", document.querySelector("#tag-token").value);

                fetch("tag_all_api.php", { method: "POST", body: data })
                .then(function(res) {
                    return res.json();
                })
                .then(function(json) {
                    const box = document.querySelector("#palette-tags");
                    box.innerHTML = "";

                    const all = document.createElement("button");
                    all.type = "button";
                    all.className = "palette-tag";
                    all.textContent = "All";
                    all.addEventListener("click", function() {
                        location.href = "memos.php";
                    });
                    box.appendChild(all);

                    const bm = document.createElement("button");
                    bm.type = "button";
                    bm.className = "palette-tag palette-bookmark";
                    bm.textContent = "Bookmarks";
                    bm.addEventListener("click", function() {
                        location.href = "memos.php?filter=favorite";
                    });
                    box.appendChild(bm);

                    json.tags.forEach(function(tag) {
                        const btn = document.createElement("button");
                        btn.type = "button";
                        btn.className = "palette-tag";
                        btn.textContent = "#" + tag.name;
                        btn.addEventListener("click", function() {
                            location.href = "memos.php?tag=" + tag.id;
                        });
                        box.appendChild(btn);
                    });
                });
            }

            document.querySelector("#palette").addEventListener("click", function(e) {
                if (e.target === this) {
                    document.querySelector("#palette").classList.remove("show");
                }
            });
            
            document.querySelector("#palette-input").addEventListener("keydown" , function(e) {
                if (e.key === "Enter") {
                    location.href = "memos.php?q=" + encodeURIComponent(this.value);
                }
            });

            const input = document.querySelector("#palette-input");
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);

            
            document.querySelector("#tag-modal-close").addEventListener("click", function() {
                document.querySelector("#tag-modal").classList.remove("show");
            });

            document.querySelector("#tag-modal").addEventListener("click", function(e) {
            if (e.target === this) {
                document.querySelector("#tag-modal").classList.remove("show");
                }
            });




            function openTagModal(memoId) {
                document.querySelector("#tag-modal-memo-id").value = memoId;
                document.querySelector("#tag-modal").classList.add("show");
                loadTags(memoId);
            }

            function loadTags(memoId) {
                const token = document.querySelector("#tag-token").value;

                const allData = new FormData();
                allData.append("token", token);

                const listData = new FormData();
                listData.append("memo_id", memoId);
                listData.append("token", token);

                Promise.all([
                    fetch("tag_all_api.php", { method: "POST", body: allData }).then(function(r) { return r.json(); }),
                    fetch("tag_list_api.php", { method: "POST", body: listData }).then(function(r) { return r.json(); })
                ]).then(function(results) {
                    const allTags = results[0].tags;
                    const memoTags = results[1].tags;

                    const checkedIds = memoTags.map(function(t) {
                        return String(t.id);
                    });

                    const box = document.querySelector("#tag-list");
                    box.innerHTML = "";

                    allTags.forEach(function(tag) {
                        const label = document.createElement("label");
                        label.className = "tag-item";

                        const cb = document.createElement("input");
                        cb.type = "checkbox";
                        cb.value = tag.id;
                        cb.checked = checkedIds.includes(String(tag.id));

                        const span = document.createElement("span");
                        span.textContent = tag.name;

                        const del = document.createElement("button");
                        del.type = "button";
                        del.className = "tag-delete";
                        del.textContent = "×";
                        del.dataset.tagId = tag.id;


                        label.appendChild(cb);
                        label.appendChild(span);
                        label.appendChild(del);
                        box.appendChild(label);
                    });
                });
            }
            
            



            document.querySelector("#tag-list").addEventListener("change", function(e) {
                const cb = e.target;
                if (cb.type !== "checkbox") return;

                const memoId = document.querySelector("#tag-modal-memo-id").value;
                const token = document.querySelector("#tag-token").value;
                const tagId = cb.value;

                if (cb.checked) {
                    const name = cb.nextElementSibling.textContent;

                    const data = new FormData();
                    data.append("memo_id", memoId);
                    data.append("name", name);
                    data.append("token", token);

                    fetch("tag_add_api.php", { method: "POST", body: data });
                } else {
                    const data = new FormData();
                    data.append("memo_id", memoId);
                    data.append("tag_id", tagId);
                    data.append("token", token);

                    fetch("tag_remove_api.php", { method: "POST", body: data });
                }
            });





            document.querySelector("#tag-create-btn").addEventListener("click", function() {
                const input = document.querySelector("#tag-new-name");
                const name = input.value.trim();
                if (name === "") return;

                const memoId = document.querySelector("#tag-modal-memo-id").value;
                const token = document.querySelector("#tag-token").value;

                const data = new FormData();
                data.append("memo_id", memoId);
                data.append("name", name);
                data.append("token", token);

                fetch("tag_add_api.php", { method: "POST", body: data })
                .then(function(res) {
                    return res.json();
                })
                .then(function(json) {
                    if (json.success) {
                        input.value = "";
                        loadTags(memoId);
                    }
                });
            });


            document.querySelector(".memo-list").addEventListener("click", function(e) {
                const btn = e.target.closest(".btn-tag");
                if (!btn) return;

                openTagModal(btn.dataset.id);
            });


            document.querySelector("#modal-tag-btn").addEventListener("click", function() {
                openTagModal(document.querySelector("#modal-id").value);
            });


            const phMessages = ["Write something...", "Add tags with #"];
                let phIndex = 0;
                const ph = document.querySelector("#create-placeholder");

                setInterval(function() {
                    if (document.querySelector("#create-body").value !== "") return;

                    ph.style.opacity = "0";

                    setTimeout(function() {
                        phIndex = (phIndex + 1) % phMessages.length;
                        ph.textContent = phMessages[phIndex]; 
                        ph.style.opacity = "1";
                    }, 300);
                }, 3000);

                document.querySelector("#create-body").addEventListener("input", function() {
                    ph.style.display = this.value === "" ? "" : "none";
                });


                document.querySelector(".create-box").addEventListener("click", function(e) {
                    if (e.target.closest("button")) return;
                    if (e.target.closest("input")) return;
                    if (e.target.closest("textarea")) return;
                    document.querySelector("#create-body").focus();
                });

                document.querySelector("#create-body").addEventListener("input",function(){
                    document.querySelector("#create-body").style.height = "auto";
                    document.querySelector("#create-body").style.height = this.scrollHeight + "px";
                });

            document.querySelector("#tag-list").addEventListener("click", function(e) {
                const del = e.target.closest(".tag-delete");
                if (!del) return;

                e.preventDefault();

                const data = new FormData();
                data.append("tag_id", del.dataset.tagId);
                data.append("token", document.querySelector("#tag-token").value);

                fetch("tag_delete_api.php", { method: "POST", body: data })
                .then(function(res) {
                    return res.json();
                })
                .then(function(json) {
                    if (json.success) {
                        loadTags(document.querySelector("#tag-modal-memo-id").value);
                    }
                });
            });

            document.querySelector("#modal .modal-content").addEventListener("click", function(e) {
                if (e.target.closest("button, input, textarea, a")) return;
                document.querySelector("#modal-body").focus();
            });
                
            const mb = document.querySelector("#modal-body");
            mb.focus();
            mb.setSelectionRange(mb.value.length, mb.value.length);

            document.querySelector("#create-body").addEventListener("keydown", function(e) {
                if (e.key !== "Enter") return;
                if (e.shiftKey) return;
                if (e.isComposing) return;

                e.preventDefault();
                document.querySelector("#create-save").click();
            });
           
        </script>
    </body>
  
</html>

