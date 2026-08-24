<?php

session_start();

require_once("config.php");

$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

$err_msg = [];

if($_SERVER["REQUEST_METHOD"] === "POST"){
    if ($email === ""){
        $err_msg["email"] = "Required";
    }

    if ($password === ""){
        $err_msg["password"] = "Required";
    }

    if (empty($err_msg)){
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");//「emailが〇〇の人の、全情報を取る」
        $stmt->execute([$email]);//引数$email(入力されたemailが入る)で実行。データベースを照合。
        $user = $stmt->fetch();//照合結果を$userに入れる。

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

    </head>
    <body>

        <h3>Sign in to SimpleMemo</h3>
        <div style="color:red;"><?php echo h($err_msg["invalid"] ?? "") ;?> </div>
        <form method="post" >
        <p>Email</p>  
        <input type="text" name="email" value="<?php echo h($email); ?>"><br>
        <div style="color:red;"><?php echo h($err_msg["email"] ?? "" );?> </div>
        
        <br>
        <p>Password</p>
        <input type="password" name="password" ><br>
        <div style="color:red;"><?php echo h($err_msg["password"] ?? "" );?> </div>
        
        <br>
        <input type="submit" value="Sign in">
        </form>

        <br>
        <br>

        <p>New to SimpleMemo? <a href="signup.php">Create an account</a></p>

        <?php


        ?>
        
    </body>
</html>

