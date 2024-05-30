<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div class="container">
        <div class="signup">
            <h2>Sign Up</h2>
            <form action="signup.php" method="POST">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Sign Up</button>
            </form>
            <?php
            // Assume user already has an account initially
            $user_has_account = true;

            // Check if the user already has an account and display the login link
            if ($user_has_account) {
                echo "<p>Already have an account? <a href='login.php'>Login here</a>.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
