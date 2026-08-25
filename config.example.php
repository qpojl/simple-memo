<?php

function csrf_token(){
    if (!isset($_SESSION["token"])){
        $_SESSION["token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["token"];
    
}

$dsn = "mysql:host=localhost;dbname=データベース名;charset=utf8mb4";
$db_user = "ユーザー名";
$db_pass = "パスワード";

$pdo = new PDO($dsn, $db_user, $db_pass);

function h($str){
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}