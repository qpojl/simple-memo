<?php
session_start();
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "POST only"]);
    exit;
}

require_once("config.php");

if(!isset($_SESSION["user_id"])){
    
    echo json_encode(["success" => false, "message" =>"Not logged in."]);
    
    exit;
}

$title = $_POST["title"] ?? "";
$body = $_POST["body"] ?? "";



if(!hash_equals($_SESSION["token"] ?? "" , $_POST["token"] ?? "")){
    
    echo json_encode(["success" => false, "message" =>"Invalid token."]);
    
    exit;
}







preg_match_all('/#([^\s#]+)/u', $body, $matches);
$tagNames = array_unique($matches[1]);

$body = trim(preg_replace('/#[^\s#]+/u', '', $body));

$stmt = $pdo->prepare("INSERT INTO memos (title, body, user_id) VALUES (?,?,?)");
$stmt->execute([$title, $body, $_SESSION["user_id"]]);
$memo_id = $pdo->lastInsertId();

foreach ($tagNames as $name) {
    $stmt = $pdo->prepare("SELECT id FROM tags WHERE user_id = ? AND name = ?");
    $stmt->execute([$_SESSION["user_id"], $name]);
    $tag = $stmt->fetch();

    if ($tag) {
        $tag_id = $tag["id"];
    
    }else{
        $stmt = $pdo->prepare("INSERT INTO tags (user_id, name) VALUES (?,?)");
        $stmt->execute([$_SESSION["user_id"], $name]);
        $tag_id = $pdo->lastInsertId(); 
    }

    $stmt = $pdo->prepare("INSERT IGNORE INTO memo_tags (memo_id, tag_id) VALUES (?,?)");
    $stmt->execute([$memo_id, $tag_id]);
}

echo json_encode(["success" => true, "id"=> $memo_id, "body" => $body]);












