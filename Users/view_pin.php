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
$pin_stmt = $conn->prepare("SELECT pins.*, users.name, users.profile_picture FROM pins JOIN users ON pins.user_id = users.users_id WHERE pin_id = ?");
if ($pin_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$pin_stmt->bind_param("i", $pin_id);
$pin_stmt->execute();
$pin_result = $pin_stmt->get_result();
$pin = $pin_result->fetch_assoc();

// Fetch comments for the pin
$comments_stmt = $conn->prepare("SELECT comments.*, users.name, users.profile_picture FROM comments JOIN users ON comments.user_id = users.users_id WHERE pin_id = ? ORDER BY timestamp DESC");
if ($comments_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$comments_stmt->bind_param("i", $pin_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
$comments = $comments_result->fetch_all(MYSQLI_ASSOC);

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['content'])) {
    $comment_content = $_POST['content'];
    $comment_user_id = $_SESSION['user_id'];
    
    if (createComment($comment_user_id, $pin_id, $comment_content)) {
        header("Location: view_pin.php?pin_id=" . $pin_id);
        exit();
    } else {
        echo "Error posting comment.";
    }
}

// Handle saving pin to board
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['board_id'])) {
    $board_id = $_POST['board_id'];
    $user_id = $_SESSION['user_id'];

    if ($board_id == 'new') {
        $board_name = $_POST['new_board_name'];
        $board_desc = $_POST['new_board_desc'];

        $stmt = $conn->prepare("INSERT INTO boards (user_id, name, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $board_name, $board_desc);
        $stmt->execute();
        $board_id = $stmt->insert_id;
    }

    $stmt = $conn->prepare("INSERT INTO board_pins (board_id, pin_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $board_id, $pin_id);
    if ($stmt->execute()) {
        header("Location: view_pin.php?pin_id=" . $pin_id);
        exit();
    } else {
        echo "Error saving pin to board.";
    }
}

// Fetch user's boards
$user_id = $_SESSION['user_id'];
$boards_stmt = $conn->prepare("SELECT * FROM boards WHERE user_id = ?");
$boards_stmt->bind_param("i", $user_id);
$boards_stmt->execute();
$boards_result = $boards_stmt->get_result();
$boards = $boards_result->fetch_all(MYSQLI_ASSOC);

// Fetch recommended pins
$recommended_stmt = $conn->prepare("SELECT pins.*, users.name, users.profile_picture FROM pins JOIN users ON pins.user_id = users.users_id WHERE pins.pin_id != ? ORDER BY RAND() LIMIT 5");
if ($recommended_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$recommended_stmt->bind_param("i", $pin_id);
$recommended_stmt->execute();
$recommended_result = $recommended_stmt->get_result();
$recommended_pins = $recommended_result->fetch_all(MYSQLI_ASSOC);

// Fetch notifications for the logged in user
$notifications = readNotifications($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Pin</title>
    <link rel="stylesheet" href="style/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .pin-container {
            display: flex;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .pin-image {
            flex: 1 1 50%;
            padding: 10px;
        }
        .pin-details {
            flex: 1 1 50%;
            padding: 10px;
        }
        .pin-image img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }
        .comments-section {
            margin-top: 20px;
        }
        .comments-section h3 {
            margin-bottom: 10px;
        }
        .comment {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
        }
        .comment img {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }
        .comment p {
            margin: 0;
        }
        .comment small {
            display: block;
            color: #888;
        }
        .recommended-section {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .recommended-pin {
            flex: 1 1 19%;
            margin-bottom: 15px;
        }
        .recommended-pin img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }
        .profile-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            display: inline-block;
            margin-right: 10px;
        }
        .profile-picture img {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
<!-- Header using Bootstrap Navbar -->
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

<div class="container mt-4 pin-container">
    <div class="pin-image">
        <img src="<?php echo htmlspecialchars($pin['image_url']); ?>" alt="">
    </div>
    <div class="pin-details">
        <h2><?php echo htmlspecialchars($pin['description']); ?></h2>
        <p><?php echo htmlspecialchars($pin['tags']); ?></p>
        <div class="d-flex align-items-center mb-3">
            <div class="profile-picture">
                <img src="<?php echo htmlspecialchars($pin['profile_picture']); ?>" alt="Profile Picture">
            </div>
            <a href="profile.php?user_id=<?php echo htmlspecialchars($pin['user_id']); ?>"><?php echo htmlspecialchars($pin['name']); ?></a>
        </div>

        <!-- Save to Board Form -->
        <form action="view_pin.php?pin_id=<?php echo $pin_id; ?>" method="post">
            <div class="form-group">
                <label for="board_id">Save to Board:</label>
                <select name="board_id" class="form-control" required>
                    <option value="">Select a board</option>
                    <?php foreach ($boards as $board): ?>
                        <option value="<?php echo $board['board_id']; ?>"><?php echo htmlspecialchars($board['name']); ?></option>
                    <?php endforeach; ?>
                    <option value="new">Create new board</option>
                </select>
            </div>
            <div id="new-board-fields" style="display: none;">
                <div class="form-group">
                    <label for="new_board_name">New Board Name:</label>
                    <input type="text" name="new_board_name" class="form-control">
                </div>
                <div class="form-group">
                    <label for="new_board_desc">New Board Description:</label>
                    <input type="text" name="new_board_desc" class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-danger mb-3">Save</button>
        </form>

        <div class="comments-section">
            <h3>Comments</h3>
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment mb-3">
                        <div class="profile-picture">
                            <img src="<?php echo htmlspecialchars($comment['profile_picture']); ?>" alt="Profile Picture">
                        </div>
                        <div>
                            <p><strong><?php echo htmlspecialchars($comment['name']); ?>:</strong> <?php echo htmlspecialchars($comment['content']); ?></p>
                            <small><?php echo htmlspecialchars($comment['timestamp']); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php endif; ?>
            
            <form action="view_pin.php?pin_id=<?php echo $pin_id; ?>" method="post">
                <div class="form-group">
                    <textarea name="content" class="form-control" placeholder="Add a comment" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post</button>
            </form>
        </div>
    </div>
</div>
<!-- Recommended Pins Section -->
<div class="container mt-4 recommended-section">
    <h3>Recommended Pins</h3>
    <div class="row">
        <?php foreach ($recommended_pins as $recommended_pin): ?>
            <div class="col-md-4 recommended-pin">
                <a href="view_pin.php?pin_id=<?php echo htmlspecialchars($recommended_pin['pin_id']); ?>">
                    <img src="<?php echo htmlspecialchars($recommended_pin['image_url']); ?>" alt="">
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationPanel = document.getElementById('notificationPanel');
        const boardSelect = document.querySelector('select[name="board_id"]');
        const newBoardFields = document.getElementById('new-board-fields');

        notificationIcon.addEventListener('click', function (event) {
            event.preventDefault();
            if (notificationPanel.style.display === 'none' || notificationPanel.style.display === '') {
                notificationPanel.style.display = 'block';
            } else {
                notificationPanel.style.display = 'none';
            }
        });

        document.addEventListener('click', function (event) {
            if (!notificationIcon.contains(event.target) && !notificationPanel.contains(event.target)) {
                notificationPanel.style.display = 'none';
            }
        });

        boardSelect.addEventListener('change', function () {
            if (this.value === 'new') {
                newBoardFields.style.display = 'block';
            } else {
                newBoardFields.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>
