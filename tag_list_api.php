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




if(!hash_equals($_SESSION["token"] ?? "" , $_POST["token"] ?? "")){
    
    echo json_encode(["success" => false, "message" =>"Invalid token."]);
    
    exit;
}




$stmt = $pdo->prepare("SELECT id FROM memos WHERE id = ? AND user_id = ?");
$stmt->execute([$memo_id,$_SESSION["user_id"]]);
if(!$stmt->fetch()){
    echo json_encode(["success" => false, "message" => "Not found"]);
    exit;
}//メモの所有者を確認する



$stmt = $pdo->prepare("
    SELECT tags.id, tags.name
    FROM memo_tags
    JOIN tags ON memo_tags.tag_id = tags.id
    WHERE memo_tags.memo_id = ?
    ");
$stmt->execute([$memo_id]);
$tags = $stmt->fetchAll();



echo json_encode(["success" => true, "tags" => $tags]);



