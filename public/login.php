<?php
require "../config/db.php";
require_once "../includes/security.php";

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $name = trim($_POST['name']?? "");
        $email = trim($_POST['email']??"");
        $password = trim($_POST['password']??"");
        $errorMessage = "";

        // Checking CSRF
        if (!validate_csrf_token($_POST['csrf_token'] ?? "")) {
            $errorMessage = "CSRF token validation failed";
        }

        //Checking for empty values
        if($errorMessage == "" && (empty($email)||empty($password))){
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
                echo "An error occured: " . e($e->getMessage());
            }

        }

    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | PaisaKhai</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Welcome Back</h2>
            <p class="auth-subtitle">Log in to manage your expenses</p>

            <?php if ($errorMessage !== ""): ?>
                <div class="error-msg"><?= e($errorMessage) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="name@example.com" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn-auth">Login</button>
            </form>

            <div class="auth-footer">
                Don't have an account? 
                <a href="signup.php">Sign up here</a>
            </div>
        </div>
    </div>
</body>
</html>