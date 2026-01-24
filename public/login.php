<?php
// login.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

    <h2>Login</h2>

    <form action="login.php" method="POST">
        <div>
            <label>Name</label><br>
            <input type="text" name="name">
        </div>

        <br>

        <div>
            <label>Email</label><br>
            <input type="email" name="email">
        </div>

        <br>

        <div>
            <label>Password</label><br>
            <input type="password" name="password">
        </div>

        <br>

        <button type="submit">Login</button>
    </form>

    <p>
        Don't have an account?
        <a href="signup.php">Sign up here</a>
    </p>

</body>
</html>