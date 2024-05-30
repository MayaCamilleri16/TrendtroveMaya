<?php
session_start();
include('db_connection.php');

function readNotifications($user_id) {
    global $conn;
    
    // statement for selecting notifications for a specific user
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from the database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE users_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch followers and following counts
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM followers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$followers_count = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM followers WHERE follower_user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$following_count = $stmt->get_result()->fetch_assoc()['count'];

// Fetch user's pins
$pins_stmt = $conn->prepare("SELECT * FROM pin WHERE user_id = ?");
$pins_stmt->bind_param("i", $user_id);
$pins_stmt->execute();
$pins_result = $pins_stmt->get_result();
$pins = $pins_result->fetch_all(MYSQLI_ASSOC);

// Fetch user's boards
$boards_stmt = $conn->prepare("SELECT * FROM boards WHERE user_id = ?");
$boards_stmt->bind_param("i", $user_id);
$boards_stmt->execute();
$boards_result = $boards_stmt->get_result();
$boards = $boards_result->fetch_all(MYSQLI_ASSOC);

// Fetch notifications for the logged-in user
$notifications = readNotifications($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <link rel="stylesheet" href="style/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .card img {
            width: 100%;
            height: auto;
        }
        .card p {
            padding: 10px;
        }
        .notification-panel {
            display: none;
            position: fixed;
            right: 0;
            top: 60px;
            width: 300px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            border-left: 1px solid #ddd;
            padding: 15px;
            overflow-y: auto;
        }
        .notification-content ul {
            list-style-type: none;
            padding: 0;
        }
        .notification-content ul li {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .notification-content ul li small {
            display: block;
            color: #888;
        }
        .notifications-container {
            margin-top: 20px;
        }
        .notifications-container ul {
            list-style-type: none;
            padding: 0;
        }
        .notifications-container ul li {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .notifications-container ul li small {
            display: block;
            color: #888;
        }
    </style>
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
    <div class="profile-container">
        <div class="cover-photo">
            <img src="<?php echo htmlspecialchars($user['cover_photo']); ?>" alt="Cover Photo" id="cover-photo">
            <form action="upload_cover_photo.php" method="post" enctype="multipart/form-data">
                <label for="change-cover-photo" class="change-cover-photo-label">Change Cover Photo</label>
                <input type="file" id="change-cover-photo" name="cover_photo" class="change-cover-photo-input">
                <button type="submit">Upload</button>
            </form>
        </div>
        <div class="profile-photo">
            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Photo" id="profile-photo">
            <form action="upload_profile_photo.php" method="post" enctype="multipart/form-data">
                <label for="change-profile-photo" class="change-profile-photo-label">Change Profile Photo</label>
                <input type="file" id="change-profile-photo" name="profile_photo" class="change-profile-photo-input">
                <button type="submit">Upload</button>
            </form>
        </div>
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['name']); ?></h1>
            <p><?php echo htmlspecialchars($user['bio']); ?></p>
            <p><?php echo $followers_count . ' followers · ' . $following_count . ' following'; ?></p>
        </div>
        <div class="profile-actions">
            <a href="editprofile.php" class="btn btn-edit-profile">Edit Profile</a>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
        <div class="profile-tabs">
            <button class="tab-btn active" onclick="openTab('created')">Created</button>
            <button class="tab-btn" onclick="openTab('saved')">Saved</button>
        </div>
        <div class="profile-content" id="created">
            <div class="grid-container">
                <?php
                // Display user's created pins
                foreach ($pins as $pin) {
                    echo "<div class='card'>
                            <a href='view_pin.php?pin_id={$pin['pin_id']}'>
                                <img src='{$pin['image_url']}' alt=''>
                                <p>{$pin['description']}</p>
                            </a>
                          </div>";
                }
                ?>
            </div>
        </div>
        <div class="profile-content" id="saved" style="display:none;">
            <div class="grid-container">
                <?php
                // Display user's saved boards
                foreach ($boards as $board) {
                    echo "<div class='card'>
                            <h3>{$board['name']}</h3>
                            <p>{$board['description']}</p>
                          </div>";
                }
                ?>
            </div>
        </div>

        <!-- Notifications section -->
        <div class="notifications-container">
            <h2>Notifications</h2>
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

    <!-- Pin posting form -->
    <div class="pin-posting-form">
        <h2>Post a New Pin</h2>
        <form action="pin.php" method="post" enctype="multipart/form-data">
            <input type="text" name="description" placeholder="Description" required>
            <input type="text" name="tags" placeholder="Tags">
            <select name="season_id" required>
                <option value="">Select Season</option>
                <option value="1">Winter</option>
                <option value="2">Autumn</option>
                <option value="3">Spring</option>
                <option value="4">Summer</option>
            </select>
            <input type="file" name="image" required>
            <button type="submit">Post</button>
        </form>
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
