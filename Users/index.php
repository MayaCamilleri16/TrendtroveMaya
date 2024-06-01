<?php
session_start();
include('db_connection.php');
include('db_functions.php'); 

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch pins from the database
$pins_stmt = $conn->prepare("SELECT * FROM pins");
$pins_stmt->execute();
$pins_result = $pins_stmt->get_result();
$pins = $pins_result->fetch_all(MYSQLI_ASSOC);

// Fetch notifications for the logged in user
$user_id = $_SESSION['user_id'];
$notifications = readNotifications($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trendtrove</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="style/style.css">
    <!-- Masonry library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/4.2.2/masonry.pkgd.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
<a class="navbar-brand" href="index.php">
        <div class="logo-container rounded-circle overflow-hidden">
            <img src="assets/logo.png" alt="Trendtrove Logo" class="img-fluid">
        </div>
    </a>
    <div class="ml-auto">
        <h1 class="title">Home Feed</h1>
    </div>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="#">Create</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Season Collection
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="#">Winter</a>
                    <a class="dropdown-item" href="#">Autumn</a>
                    <a class="dropdown-item" href="#">Spring</a>
                    <a class="dropdown-item" href="#">Summer</a>
                </div>
            </li>
        </ul>
    </div>
    <div class="header-icons">
        <a href="search.html" class="nav-link">
            <img src="assets/search.png" alt="Search" class="icon">
        </a>
        <a href="#" class="nav-link" id="notificationIcon">
            <img src="assets/notification.png" alt="Notifications" class="icon">
        </a>
        <div id="notificationPanel" class="notification-panel">
            <h2>Notifications</h2>
            <div class="notification-content">
                <?php if (!empty($notifications)): ?>
                    <ul>
                        <?php foreach ($notifications as $notification): ?>
                            <li>
                                <?php echo htmlspecialchars($notification['content']); ?>
                                <small><?php echo htmlspecialchars($notification['timestamp']); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No notifications.</p>
                <?php endif; ?>
            </div>
        </div>
        <a href="messages.html" class="nav-link">
            <img src="assets/messages.png" alt="Messages" class="icon">
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="account.php" class="nav-link">
                <img src="assets/account.png" alt="Account" class="icon">
            </a>
            <a href="logout.php" class="nav-link">
                Logout
            </a>
        <?php else: ?>
            <a href="login.php" class="nav-link">
                <img src="assets/account.png" alt="Account" class="icon">
            </a>
        <?php endif; ?>
    </div>
</nav>
    <!-- Main area -->
    <main id="content">
        <!-- for you -->
        <h2 class="title" style="text-align: center; margin-top: 20px;">For You</h2>

        <!-- container -->
        <div class="grid-container">
            <?php
            // Display fetched pins
            foreach ($pins as $pin) {
                echo "<div class='card'>
                        <img src='{$pin['image_url']}' alt=''>
                        <p>{$pin['description']}</p>
                      </div>";
            }
            ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center mt-4">
        <p>&copy; 2024 Trendtrove</p>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const notificationIcon = document.getElementById('notificationIcon');
            const notificationPanel = document.getElementById('notificationPanel');

            notificationIcon.addEventListener('click', function (event) {
                event.preventDefault(); // Prevent the default anchor click behavior
                if (notificationPanel.style.display === 'none' || notificationPanel.style.display === '') {
                    notificationPanel.style.display = 'block';
                } else {
                    notificationPanel.style.display = 'none';
                }
            });

            // Close the notification panel if clicked outside
            document.addEventListener('click', function (event) {
                if (!notificationIcon.contains(event.target) && !notificationPanel.contains(event.target)) {
                    notificationPanel.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
