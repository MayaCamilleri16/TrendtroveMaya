<?php
session_start();
include('db_connection.php');
include('db_functions.php'); 

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch winter pins from the database
$pins_stmt = $conn->prepare("SELECT * FROM pins WHERE season_id = 3"); 
$pins_stmt->execute();
$pins_result = $pins_stmt->get_result();
$pins = $pins_result->fetch_all(MYSQLI_ASSOC);

// Fetch notifications for the logged in user
$user_id = $_SESSION['user_id'];
$notifications = readNotifications($user_id);

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
    <title>Trendtrove - Winter Collection</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="style/style.css">

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
            <h1 class="title">Winter Collection</h1>
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

<!-- Main area -->
<main id="content">
    <!-- Winter Collection section -->
    <h2 class="title" style="text-align: center; margin-top: 20px;">Winter Collection</h2>

    <!-- Container -->
    <div class="grid-container">
        <?php
        // Display fetched winter pins
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
</main>

<!-- Footer -->
<footer class="text-center mt-4">
    <p>&copy; 2024 Trendtrove</p>
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
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
