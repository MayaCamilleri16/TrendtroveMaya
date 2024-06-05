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

// Fetch pin views count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM analytics WHERE interaction_type = 'view' AND associated_id = ?");
$stmt->bind_param("i", $pin_id);
$stmt->execute();
$pin_views_count = $stmt->get_result()->fetch_assoc()['count'];

// Fetch messages for the logged in user
$messages_stmt = $conn->prepare("SELECT m.*, u1.name as sender_name, u2.name as receiver_name FROM messages m 
                                JOIN users u1 ON m.sender_id = u1.users_id 
                                JOIN users u2 ON m.receiver_id = u2.users_id 
                                WHERE m.sender_id = ? OR m.receiver_id = ? 
                                ORDER BY m.timestamp DESC");
$messages_stmt->bind_param("ii", $user_id, $user_id);
$messages_stmt->execute();
$messages_result = $messages_stmt->get_result();
$messages = $messages_result->fetch_all(MYSQLI_ASSOC);
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
        .suggestions {
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-height: 200px;
            overflow-y: auto;
            width: 300px;
            display: none;
        }
        .suggestions ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .suggestions ul li {
            padding: 10px;
            cursor: pointer;
        }
        .suggestions ul li:hover {
            background-color: #f5f5f5;
        }
        .inbox-panel {
            position: fixed;
            right: 0;
            top: 60px;
            width: 320px;
            max-height: 80vh;
            overflow-y: auto;
            background: white;
            border-left: 1px solid #ddd;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
        }
        .inbox-panel.active {
            display: block;
        }
        .inbox-panel h2 {
            padding: 10px;
            margin: 0;
            border-bottom: 1px solid #ddd;
            background: #f5f5f5;
        }
        .inbox-content {
            padding: 10px;
        }
        .inbox-content ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .inbox-content ul li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .inbox-content ul li:last-child {
            border-bottom: none;
        }
        .inbox-content ul li:hover {
            background-color: #f5f5f5;
            cursor: pointer;
        }
        .inbox-content .message-form {
            display: flex;
            margin-top: 10px;
        }
        .inbox-content .message-form input[type="text"] {
            flex-grow: 1;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .inbox-content .message-form button {
            margin-left: 10px;
            padding: 5px 10px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .chat-messages {
            list-style-type: none;
            padding: 0;
            margin: 0;
            max-height: 400px;
            overflow-y: auto;
        }
        .chat-messages li {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .chat-messages li small {
            display: block;
            color: #888;
        }
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <a class="navbar-brand" href="index.php">
        <div class="logo-container rounded-circle overflow-hidden">
            <img src="assets/logo.png" alt="Trendtrove Logo" class="img-fluid">
        </div>
    </a>
    <div class="ml-auto">
        <a href="index.php">
            <h1 class="title">Home Feed</h1>
        </a>
    </div>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Season Collection
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="winter.php">Winter</a>
                    <a class="dropdown-item" href="autumn.php">Autumn</a>
                    <a class="dropdown-item" href="spring.php">Spring</a>
                    <a class="dropdown-item" href="summer.php">Summer</a>
                </div>
            </li>
        </ul>
    </div>
    <div class="header-icons">
        <form class="form-inline mr-2" action="search.php" method="GET" id="searchForm">
            <input class="form-control mr-sm-2" type="search" placeholder="Search users or seasons" aria-label="Search" name="query" id="searchInput" required>
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
            <div id="suggestions" class="suggestions"></div>
        </form>
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
        <a href="#" class="nav-link" id="chatIcon">
            <img src="assets/messages.png" alt="Messages" class="icon">
        </a>
        <div id="chatPanel" class="inbox-panel">
            <h2>Inbox</h2>
            <div class="inbox-content">
                <ul id="messageList">
                    <?php foreach ($messages as $message): ?>
                        <li onclick="openChat(<?php echo $message['sender_id'] === $user_id ? $message['receiver_id'] : $message['sender_id']; ?>)">
                            <strong><?php echo htmlspecialchars($message['sender_id'] === $user_id ? $message['receiver_name'] : $message['sender_name']); ?>:</strong>
                            <br><?php echo htmlspecialchars($message['content']); ?>
                            <br><small><?php echo htmlspecialchars($message['timestamp']); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div id="chatContainer" style="display: none;">
                    <h3 id="chatUserName"></h3>
                    <ul id="chatMessages" class="chat-messages"></ul>
                    <form id="chatForm" class="message-form">
                        <input type="hidden" name="receiver_id" id="chatReceiverId">
                        <input type="text" name="content" placeholder="Your message" required>
                        <button type="submit">Send</button>
                    </form>
                </div>
            </div>
        </div>
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
        <p><?php echo $pin_views_count . ' views'; ?></p> <!-- Pin views displayed here -->

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
        const chatIcon = document.getElementById('chatIcon');
        const chatPanel = document.getElementById('chatPanel');
        const boardSelect = document.querySelector('select[name="board_id"]');
        const newBoardFields = document.getElementById('new-board-fields');

        notificationIcon.addEventListener('click', function (event) {
            event.preventDefault();
            if (notificationPanel.style.display === 'none' || notificationPanel.style.display === '') {
                notificationPanel.style.display = 'block';
                chatPanel.style.display = 'none';
            } else {
                notificationPanel.style.display = 'none';
            }
        });

        chatIcon.addEventListener('click', function (event) {
            event.preventDefault();
            if (chatPanel.style.display === 'none' || chatPanel.style.display === '') {
                chatPanel.style.display = 'block';
                notificationPanel.style.display = 'none';
            } else {
                chatPanel.style.display = 'none';
            }
        });

        // Close the notification and chat panels if clicked outside
        document.addEventListener('click', function (event) {
            if (!notificationIcon.contains(event.target) && !notificationPanel.contains(event.target)) {
                notificationPanel.style.display = 'none';
            }
            if (!chatIcon.contains(event.target) && !chatPanel.contains(event.target)) {
                chatPanel.style.display = 'none';
            }
        });

        boardSelect.addEventListener('change', function () {
            if (this.value === 'new') {
                newBoardFields.style.display = 'block';
            } else {
                newBoardFields.style.display = 'none';
            }
        });

        document.getElementById('chatForm').addEventListener('submit', function (event) {
            event.preventDefault();
            sendChatMessage();
        });

        // Fetch messages 
        fetchMessages();
        setInterval(fetchMessages, 5000);
    });

    function openChat(userId) {
        document.getElementById('chatContainer').style.display = 'block';
        document.getElementById('chatReceiverId').value = userId;
        const userName = document.querySelector(`#messageList li[onclick="openChat(${userId})"] strong`).innerText;
        document.getElementById('chatUserName').innerText = userName;
        fetchChatMessages(userId);
    }

    function sendChatMessage() {
        const form = document.getElementById('chatForm');
        const formData = new FormData(form);

        fetch('send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchChatMessages(document.getElementById('chatReceiverId').value);
                form.reset();
            } else {
                alert('Error sending message');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function fetchMessages() {
        fetch('fetch_messages.php')
        .then(response => response.json())
        .then(data => {
            const messageList = document.getElementById('messageList');
            messageList.innerHTML = '';
            data.messages.forEach(message => {
                const li = document.createElement('li');
                li.innerHTML = `<strong>${message.sender_id === <?php echo $user_id; ?> ? message.receiver_name : message.sender_name}:</strong><br>${message.content}<br><small>${message.timestamp}</small>`;
                li.setAttribute('onclick', `openChat(${message.sender_id === <?php echo $user_id; ?> ? message.receiver_id : message.sender_id})`);
                messageList.appendChild(li);
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function fetchChatMessages(userId) {
        fetch('fetch_chat_messages.php?user_id=' + userId)
        .then(response => response.json())
        .then(data => {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.innerHTML = '';
            data.messages.forEach(message => {
                const li = document.createElement('li');
                li.innerHTML = `<strong>${message.sender_id === <?php echo $user_id; ?> ? 'You' : message.sender_name}:</strong><br>${message.content}<br><small>${message.timestamp}</small>`;
                chatMessages.appendChild(li);
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>
</body>
</html>
