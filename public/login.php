<?php
require "../config/db.php";

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $name = trim($_POST['name']?? "");
        $email = trim($_POST['email']??"");
        $password = trim($_POST['password']??"");
        $errorMessage = "";

        //Checking for empty values
        if(empty($email)||empty($password)){
            $errorMessage = "Please fill all of the fields for registration";
        }

        //Checking email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = "Invalid email address";
        }

        //Password length validation

        if(strlen($password) <8){
            $errorMessage = "The password length must be atleast 8 characters";
        }

        if($errorMessage == ""){
            try{
            $checkSql = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($checkSql);
            $stmt->execute([$email]);
            $result = $stmt->fetch();
            
            if($result){
                if(password_verify($password,$result['password_hash'])){
                    $_SESSION['user_id'] = $result['id'];
                    $_SESSION['user_name'] = $result['name'];
                    header("Location: dashboard.php");
                    exit;
                }else{
                    $errorMessage = "Incorrect Password! Please try again!";
                }
            }else{
                $errorMessage = "Email hasn't been registered";
            }
            }catch(Exception $e){
                echo "An error occured: " . $e->getMessage();
            }

        }

    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

    <h2>Login</h2>

    <form method="POST">

        <div>
            <label>Email</label><br>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <br>

        <div>
            <label>Password</label><br>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <br>

        <button type="submit">Login</button>
    </form>

    <p>
        Don't have an account?
        <a href="signup.php">Sign up here</a>
    </p>

    <p style="color:red"><?= $errorMessage??""?></p>

</body>
</html>