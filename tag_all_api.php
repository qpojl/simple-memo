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

$memo_id = $_POST["memo_id"] ?? "";
$name = $_POST["name"] ?? "";



if(!hash_equals($_SESSION["token"] ?? "" , $_POST["token"] ?? "")){
    
    echo json_encode(["success" => false, "message" =>"Invalid token."]);
    
    exit;
}

if ($name === "") {
    echo json_encode(["success" => false, "message" => "Empty tag name"]);
    exit;
}


$stmt = $pdo->prepare("SELECT id FROM memos WHERE id = ? AND user_id = ?");
$stmt->execute([$memo_id,$_SESSION["user_id"]]);
if(!$stmt->fetch()){
    echo json_encode(["success" => false, "message" => "Not found"]);
    exit;
}


$stmt = $pdo->prepare("SELECT id FROM tags WHERE user_id = ? AND name = ?");
$stmt->execute([$_SESSION["user_id"],$name]);
$tag = $stmt->fetch();

if($tag){
    $tag_id = $tag["id"];
}else{
    $stmt = $pdo->prepare("INSERT INTO tags (user_id,name) VALUES (?,?)");
    $stmt->execute([$_SESSION["user_id"],$name]);
    $tag_id = $pdo->lastInsertId();
}

$stmt = $pdo->prepare("INSERT IGNORE INTO memo_tags (memo_id, tag_id) VALUES (?,?)");
$stmt->execute([$memo_id, $tag_id]);

echo json_encode(["success" => true, "tag_id" => $tag_id, "name" => $name]);



