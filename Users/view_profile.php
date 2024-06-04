<?php
session_start();
include('db_connection.php');
include('db_functions.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$current_user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE users_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch user's pins
$pins_stmt = $conn->prepare("SELECT * FROM pins WHERE user_id = ?");
$pins_stmt->bind_param("i", $user_id);
$pins_stmt->execute();
$pins_result = $pins_stmt->get_result();
$pins = $pins_result->fetch_all(MYSQLI_ASSOC);

$is_following = false;
$follow_stmt = $conn->prepare("SELECT * FROM followers WHERE user_id = ? AND follower_user_id = ?");
$follow_stmt->bind_param("ii", $user_id, $current_user_id);
$follow_stmt->execute();
$follow_result = $follow_stmt->get_result();
if ($follow_result->num_rows > 0) {
    $is_following = true;
}

// Fetch notifications for the logged in user
$notifications = readNotifications($current_user_id);

// Fetch followers and following counts for the viewed user
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM followers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$followers_count = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM followers WHERE follower_user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$following_count = $stmt->get_result()->fetch_assoc()['count'];

// Fetch messages for the logged in user
$messages_stmt = $conn->prepare("SELECT m.*, u1.name as sender_name, u2.name as receiver_name FROM messages m 
                                JOIN users u1 ON m.sender_id = u1.users_id 
                                JOIN users u2 ON m.receiver_id = u2.users_id 
                                WHERE m.sender_id = ? OR m.receiver_id = ? 
                                ORDER BY m.timestamp DESC");
$messages_stmt->bind_param("ii", $current_user_id, $current_user_id);
$messages_stmt->execute();
$messages_result = $messages_stmt->get_result();
$messages = $messages_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['name']); ?>'s Profile</title>
    <link rel="stylesheet" href="style/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-container {
            margin-top: 10px;
        }
        .cover-photo {
            position: relative;
            width: 100%;
            height: 200px;
            background-size: cover;
            background-position: center;
            margin-bottom: 20px;
        }
        .profile-photo img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin-top: -8px;
        }
        .profile-info {
            text-align: center;
        }
        .profile-actions {
            text-align: center;
            margin-top: 20px;
        }
        .profile-tabs {
            text-align: center;
            margin-top: 20px;
        }
        .profile-tabs .tab-btn {
            background: none;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
        }
        .profile-tabs .tab-btn.active {
            color: #007bff;
        }
        .profile-content {
            display: none;
        }
        .profile-content.active {
            display: block;
        }
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
            <img src="assets/messages.png" alt="Chat" class="icon">
        </a>
        <div id="chatPanel" class="inbox-panel">
            <h2>Inbox</h2>
            <div class="inbox-content">
                <ul id="messageList">
                    <?php foreach ($messages as $message): ?>
                        <li onclick="openChat(<?php echo $message['sender_id'] === $current_user_id ? $message['receiver_id'] : $message['sender_id']; ?>)">
                            <strong><?php echo htmlspecialchars($message['sender_id'] === $current_user_id ? $message['receiver_name'] : $message['sender_name']); ?>:</strong>
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

<div class="profile-container">
    <div class="cover-photo" style="background-image: url('<?php echo htmlspecialchars($user['cover_photo']); ?>');"></div>
    <div class="profile-photo text-center">
        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Photo">
    </div>
    <div class="profile-info">
        <h1><?php echo htmlspecialchars($user['name']); ?></h1>
        <p><?php echo htmlspecialchars($user['bio']); ?></p>
        <p><?php echo $followers_count . ' followers · ' . $following_count . ' following'; ?></p>
    </div>
    <div class="profile-actions">
        <form action="follow_unfollow.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <?php if ($is_following): ?>
                <button type="submit" name="action" value="unfollow" class="btn btn-danger">Unfollow</button>
            <?php else: ?>
                <button type="submit" name="action" value="follow" class="btn btn-primary">Follow</button>
            <?php endif; ?>
        </form>
    </div>
    <div class="profile-tabs">
        <button class="tab-btn active" onclick="openTab('created')">Created</button>
        <button class="tab-btn" onclick="openTab('saved')">Saved</button>
    </div>
    <div class="profile-content active" id="created">
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
    <div class="profile-content" id="saved">
        <div class="grid-container">
            <!-- Display user's saved pins here -->
        </div>
    </div>
</div>
<script>
    function openTab(tabName) {
        var i;
        var x = document.getElementsByClassName("profile-content");
        var tabButtons = document.getElementsByClassName("tab-btn");
        for (i = 0; i < x.length; i++) {
            x[i].classList.remove("active");
        }
        for (i = 0; i < tabButtons.length; i++) {
            tabButtons[i].classList.remove("active");
        }
        document.getElementById(tabName).classList.add("active");
        event.target.classList.add("active");
    }

    document.addEventListener('DOMContentLoaded', function () {
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationPanel = document.getElementById('notificationPanel');
        const chatIcon = document.getElementById('chatIcon');
        const chatPanel = document.getElementById('chatPanel');

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

        document.getElementById('messageForm').addEventListener('submit', function (event) {
            event.preventDefault();
            sendMessage();
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

    function sendMessage() {
        const form = document.getElementById('messageForm');
        const formData = new FormData(form);

        fetch('send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchMessages();
                form.reset();
            } else {
                alert('Error sending message');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
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
                li.innerHTML = `<strong>${message.sender_id === <?php echo $current_user_id; ?> ? message.receiver_name : message.sender_name}:</strong><br>${message.content}<br><small>${message.timestamp}</small>`;
                li.setAttribute('onclick', `openChat(${message.sender_id === <?php echo $current_user_id; ?> ? message.receiver_id : message.sender_id})`);
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
                li.innerHTML = `<strong>${message.sender_id === <?php echo $current_user_id; ?> ? 'You' : message.sender_name}:</strong><br>${message.content}<br><small>${message.timestamp}</small>`;
                chatMessages.appendChild(li);
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        openTab('created');
    });
</script>
</body>
</html>
