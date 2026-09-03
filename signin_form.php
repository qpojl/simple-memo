<?php

session_start();

require_once("config.php");

$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

$err_msg = [];

if($_SERVER["REQUEST_METHOD"] === "POST"){

    if(!hash_equals($_SESSION["token"] ?? "",$_POST["token"] ?? "")){
        exit("Unauthorized access.");
    }

    if ($email === ""){
        $err_msg["email"] = "Required";
    }

    if ($password === ""){
        $err_msg["password"] = "Required";
    }

    if (empty($err_msg)){
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password , $user["password"])){
                

                session_regenerate_id(true);
                $_SESSION["user_id"] =$user["id"];

                header("Location: memos.php");
                exit;
                


            }else{
                $err_msg["invalid"] = "Invalid email or password";
                

                
                
            }

        


        
    }
    
}

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>Sign in</title>

        <link rel="stylesheet" href="style.css">

    </head>
    <body class="auth-page">

        <div class="card">

        <h3>Sign in to memos</h3>
        <div class="err_msg"><?php echo h($err_msg["invalid"] ?? "") ;?> </div>
        <form method="post" >
        <input type="hidden" name="token" value="<?php echo h(csrf_token());?>">
        <p>Email</p>  
        <input type="text" name="email" value="<?php echo h($email); ?>">
        <div class="err_msg"><?php echo h($err_msg["email"] ?? "" );?> </div>
        
        
        <p>Password</p>
        <input type="password" name="password" >
        <div class="err_msg"><?php echo h($err_msg["password"] ?? "" );?> </div>
        
        
        <input type="submit" value="Sign in">
        </form>

        
        

        <p>New to memos? <a href="signup.php">Create an account</a></p>

        


        </div>
        
    </body>
</html>

