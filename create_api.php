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



$stmt = $pdo->prepare("INSERT INTO memos (title,body,user_id) VALUES (?,?,?)");
$stmt->execute([$title,$body,$_SESSION["user_id"]]);





echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);