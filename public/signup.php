<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    require "../config/db.php";
    require_once "../includes/security.php";
    $errorMessage = "";
    $successMessage = "";

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $name = trim($_POST['name']?? "");
        $email = trim($_POST['email']??"");
        $password = trim($_POST['password']??"");
        $confirmPassword = trim($_POST['confirmPassword']??"");
        // Checking CSRF
        if (!validate_csrf_token($_POST['csrf_token'] ?? "")) {
            $errorMessage = "CSRF token validation failed";
        }

        //Checking for empty values
        if($errorMessage == "" && (empty($name)||empty($email)||empty($password)||empty($confirmPassword))){
            $errorMessage = "Please fill all of the fields for registration";
        }

        //Checking email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = "Invalid email address";
        }

        //Password validation

        //Password length validation

        if(strlen($password) <8){
            $errorMessage = "The password length must be atleast 8 characters";
        }
        $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/";
        if (!preg_match($pattern, $password)) {
            $errorMessage = "Invalid password! Make sure your password has astleast 
            - One capital letter,
            - One small letter,
            - One number
            - One special character";
        }

        // Confirm password 
        if($confirmPassword!=$password){
            $errorMessage = "The passwords don't match";
        }

        //to check if email has already been used for registration
         try{
            $checkEmail = "SELECT email FROM users WHERE email = ?";
            $stmt = $conn->prepare($checkEmail);
            $stmt->execute([$email]);
            $result = $stmt->fetch();

            if($result){
                $errorMessage = "Email has already been registered";
            }
         }catch(Exception $e){
            echo "An error occured: ". $e->getMessage();
         }

        if ($errorMessage == "") {
            try {
                // in order to perform multiple actions and execute them at once, we start transaction
                $conn->beginTransaction();

                // Inserting user into users table
                $sql = "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $name,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT)
                ]);

                // getting newly created user's id
                $userId = $conn->lastInsertId();

                // inserting default wallet(Cash) into wallets table by using user's ID
                $walletSql = "INSERT INTO wallets (user_id, name, balance) VALUES (?, ?, ?)";
                $walletStmt = $conn->prepare($walletSql);
                $walletStmt->execute([$userId,'Cash',0]);

                // Commit transaction
                $conn->commit();

                $successMessage = "Registration Successful!
                <a href='login.php'> Login </a>";

            } catch (Exception $e) {
                // Rollback if anything fails
                $conn->rollBack();
                $errorMessage = "An error occurred in registration: " . $e->getMessage();
            }
}


    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | PaisaKhai</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Create Account</h2>
            <p class="auth-subtitle">Join us and start tracking</p>

            <?php if ($errorMessage !== ""): ?>
                <div class="error-msg"><?= e($errorMessage) ?></div>
            <?php endif; ?>

            <?php if ($successMessage !== ""): ?>
                <div class="success-msg"><?= $successMessage ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="John Doe" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="john@example.com" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Min. 8 characters" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirmPassword" placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="btn-auth">Sign Up</button>
            </form>

            <div class="auth-footer">
                Already have an account? 
                <a href="login.php">Log in</a>
            </div>
        </div>
    </div>
</body>
</html>