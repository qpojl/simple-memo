<?php
session_start();

require_once("config.php");

$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";


$err_msg = [];

if($_SERVER["REQUEST_METHOD"] === "POST"){

        if ($email === ""){
            $err_msg["n_email"]="Required";
        }

        if ($password === ""){
            $err_msg["n_password"]="Required";
        }

        if(preg_match ('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/',$email) === 0){
            $err_msg["email"] = "Invalid email address";
        }

        if(preg_match('/^(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}$/i',$password) === 0){
            $err_msg["password"] = "Invalid password";
        }

        if(empty($err_msg)){
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if($user){
                $exist_err_msg["exist"] = "Account already exists";

            }else{

               $password = password_hash($password,PASSWORD_DEFAULT);

               $stmt = $pdo->prepare("INSERT INTO users (email,password) VALUES (?,?)");
               $stmt->execute([$email,$password]);

               header("location:signin_form.php");
               exit;


            }


            
        }


    }

    

?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>Sign up</title>

    </head>

    <body>
    <h3>Sign up for SimpleMemo</h3>
    <div style="color:red;"><?php echo h($exist_err_msg["exist"] ??  "") ;?> </div>
    <form method="post">
    <p>Email</p>
    <input type="text" name="email" placeholder="Email" value="<?php echo h($email ?? ""); ?>"><br>
    <div style="color:red;"><?php echo h($err_msg["n_email"] ?? $err_msg["email"] ?? "" );?> </div>
    <br>
    <p>Password</p>
    <input type="password" name="password" placeholder="Password"><br>
    <p>Password should be at least 8 characters including a number and a lowercase letter.</p>
    <div style="color: red;"><?php echo h($err_msg["n_password"] ?? $err_msg["password"] ?? "");?> </div>
    <br>
    <input type="submit" value= "Create an account">
    

    </form>
    <br>
    <br>
    <p>Already have an account? <a href="signin_form.php">Sign in</a></p>

    </body>
</html>