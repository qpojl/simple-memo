<?php
$dsn = "mysql:host=localhost;dbname=データベース名;charset=utf8mb4";
$db_user = "ユーザー名";
$db_pass = "パスワード";

$pdo = new PDO($dsn, $db_user, $db_pass);

function h($str){
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}