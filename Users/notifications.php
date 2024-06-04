<?php
session_start();
include('db_connection.php');
include('db_functions.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch notifications for the logged-in user
$user_id = $_SESSION['user_id'];
$notifications = readNotifications($user_id);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="style/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Notifications</h2>
        <?php if (!empty($notifications)): ?>
            <ul>
                <?php foreach ($notifications as $notification): ?>
                    <li>
                        <?php echo htmlspecialchars($notification['user_name']); ?>: <?php echo htmlspecialchars($notification['content']); ?>
                        <small><?php echo htmlspecialchars($notification['timestamp']); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No notifications.</p>
        <?php endif; ?>
    </div>
</body>
</html>
