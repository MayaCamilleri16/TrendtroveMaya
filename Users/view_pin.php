<?php
session_start();
include('db_connection.php');
include('db_functions.php'); 

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch pin details from the database
$pin_id = $_GET['pin_id'];
$pin_stmt = $conn->prepare("SELECT * FROM pins WHERE pin_id = ?");
$pin_stmt->bind_param("i", $pin_id);
$pin_stmt->execute();
$pin_result = $pin_stmt->get_result();
$pin = $pin_result->fetch_assoc();

// Fetch comments for the pin
$comments_stmt = $conn->prepare("SELECT comments.*, users.name FROM comments JOIN users ON comments.user_id = users.users_id WHERE pin_id = ? ORDER BY timestamp DESC");
$comments_stmt->bind_param("i", $pin_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
$comments = $comments_result->fetch_all(MYSQLI_ASSOC);

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comment_content = $_POST['content'];
    $comment_user_id = $_SESSION['user_id'];
    
    if (createComment($comment_user_id, $pin_id, $comment_content)) {
        header("Location: view_pin.php?pin_id=" . $pin_id);
        exit();
    } else {
        echo "Error posting comment.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Pin</title>
    <link rel="stylesheet" href="style/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<!-- Header using Bootstrap Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <a class="navbar-brand" href="#">
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

<body>
    <div class="pin-container">
        <img src="<?php echo htmlspecialchars($pin['image_url']); ?>" alt="">
        <h2><?php echo htmlspecialchars($pin['description']); ?></h2>
        <p><?php echo htmlspecialchars($pin['tags']); ?></p>
        <p>Posted by: <?php echo htmlspecialchars($pin['user_id']); ?></p>
        
        <div class="comments-section">
            <h3>Comments</h3>
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <p><strong><?php echo htmlspecialchars($comment['name']); ?>:</strong> <?php echo htmlspecialchars($comment['content']); ?></p>
                        <small><?php echo htmlspecialchars($comment['timestamp']); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php endif; ?>
            
            <form action="view_pin.php?pin_id=<?php echo $pin_id; ?>" method="post">
                <textarea name="content" placeholder="Add a comment" required></textarea>
                <button type="submit">Post</button>
            </form>
        </div>
    </div>
      <script>
        function openTab(tabName) {
            var i;
            var x = document.getElementsByClassName("profile-content");
            for (i = 0; i < x.length; i++) {
                x[i].style.display = "none";
            }
            document.getElementById(tabName).style.display = "block";
        }

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

            // Initialize the default tab
            openTab('created');
        });
    </script>
</body>
</html>
