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
$id =$_POST["id"] ??  "";



if(!hash_equals($_SESSION["token"] ?? "" , $_POST["token"] ?? "")){
    
    echo json_encode(["success" => false, "message" =>"Invalid token."]);
    
    exit;
}



$stmt = $pdo->prepare("UPDATE memos SET title = ?,body = ?  WHERE id = ? AND user_id = ?");
$stmt->execute([$title,$body,$id,$_SESSION["user_id"]]);





echo json_encode(["success" => true]);