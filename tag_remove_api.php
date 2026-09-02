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
$tag_id = $_POST["tag_id"] ?? "";



if(!hash_equals($_SESSION["token"] ?? "" , $_POST["token"] ?? "")){
    
    echo json_encode(["success" => false, "message" =>"Invalid token."]);
    
    exit;
}



$stmt = $pdo->prepare("SELECT id FROM memos WHERE id = ? AND user_id = ?");
$stmt->execute([$memo_id,$_SESSION["user_id"]]);
if(!$stmt->fetch()){
    echo json_encode(["success" => false, "message" => "Not found"]);
    exit;
}




$stmt = $pdo->prepare("DELETE FROM memo_tags WHERE memo_id = ? AND tag_id = ?");
$stmt->execute([$memo_id, $tag_id]);

echo json_encode(["success" => true]);



