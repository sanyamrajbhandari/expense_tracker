<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    require "../config/db.php";

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $name = trim($_POST['name']?? "");
        $email = trim($_POST['email']??"");
        $password = trim($_POST['password']??"");
        $confirmPassword = trim($_POST['confirmPassword']??"");
        $errorMessage = "";
        $successMessage = "";

        //Checking for empty values
        if(empty($name)||empty($email)||empty($password)||empty($confirmPassword)){
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
    <title>Signup</title>
</head>
<body>

    <h2>Signup for your account</h2>

    <form method="POST">
        <div>
            <label>Name:</label><br>
            <input type="text" name="name" placeholder="Please enter your name" required >
        </div>

        <br>

        <div>
            <label>Email</label><br>
            <input type="email" name="email" placeholder="Please enter your email" required>
        </div>

        <br>

        <div>
            <label>Password</label><br>
            <input type="password" name="password" placeholder="Please enter your password" required>
        </div>

        <br>
        <div>
            <label>Confirm password</label><br>
            <input type="password" name="confirmPassword" placeholder="Please re-enter your password" required>
        </div>

        <br>

        <p style="color:red"><?= $errorMessage??""?></p>
        <p style="color:green"><?= $successMessage??""?></p>

        <button type="submit">Sign up</button>
    </form>

</body>
</html>