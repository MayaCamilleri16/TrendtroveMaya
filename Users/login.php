<?php
session_start(); 
include('db_connection.php');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user from the database based on email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if user exists
    if ($result->num_rows == 1) {
        // User exists, verify password
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Password is correct, allow login
            $_SESSION['user_id'] = $user['users_id'];
            header("Location: index.php"); // Redirect to homepage 
            exit();
        } else {
            // Incorrect password
            $error_message = "Incorrect email or password";
        }
    } else {
        // User does not exist
        $error_message = "Incorrect email or password";
    }
}
?>

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
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <?php
            if (isset($error_message)) {
                echo "<p>$error_message</p>";
            }
            ?>
            <p>Don't have an account? <a href='signup.php'>Sign up here</a>.</p>
        </div>
    </div>
</body>
</html>
