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





if(!hash_equals($_SESSION["token"] ?? "" , $_POST["token"] ?? "")){
    echo json_encode(["success" => false, "message" =>"Invalid token."]);
    exit;
}







$stmt = $pdo->prepare("SELECT id, name FROM tags  WHERE user_id = ? ORDER BY name");
$stmt->execute([$_SESSION["user_id"]]);
$tags = $stmt->fetchAll();


echo json_encode(["success" => true, "tags" => $tags]);



