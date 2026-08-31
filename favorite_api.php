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


$id =$_POST["id"] ??  "";



if(!hash_equals($_SESSION["token"] ?? "" , $_POST["token"] ?? "")){
    
    echo json_encode(["success" => false, "message" =>"Invalid token."]);
    
    exit;
}



$stmt = $pdo->prepare("UPDATE memos SET favorite = 1 - favorite WHERE id = ? AND user_id = ? ");
$stmt->execute([$id , $_SESSION["user_id"]]);

$stmt = $pdo->prepare("SELECT favorite FROM memos WHERE id = ? AND user_id = ?");
$stmt ->execute([$id, $_SESSION["user_id"]]);
$memo = $stmt->fetch();

echo json_encode(["success" => true, "favorite" => $memo["favorite"]]);



