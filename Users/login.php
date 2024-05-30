<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div class="container">
        <div class="login">
            <h2>Login</h2>
            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <?php
            // Assume user has no account initially
            $user_has_no_account = true;

            // Check if the user doesn't have an account and display the sign-up link
            if ($user_has_no_account) {
                echo "<p>Don't have an account? <a href='signup.php'>Sign up here</a>.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
