<?php
include('db_connection.php');

// users
function createUser($name, $email, $password, $profile_picture, $bio) {
    global $conn;

    if (empty($profile_picture)) {
        $profile_picture = '';
    }
    if (empty($bio)) {
        $bio = '';
    }

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, profile_picture, bio) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $profile_picture, $bio);

    return $stmt->execute();
}

function readUser($user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

function updateUser($user_id, $name, $email, $password, $profile_picture, $bio) {
    global $conn;

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, profile_picture = ?, bio = ? WHERE user_id = ?");
    $stmt->bind_param("sssssi", $name, $email, $password, $profile_picture, $bio, $user_id);

    return $stmt->execute();
}

function deleteUser($user_id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    return $stmt->execute();
}

// pins
function createPin($user_id, $image_url, $description, $tags, $season_id) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO pins (user_id, image_url, description, tags, season_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $user_id, $image_url, $description, $tags, $season_id);
    
    return $stmt->execute();
}

function readPin($pin_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM pins WHERE pin_id = ?");
    $stmt->bind_param("i", $pin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

function updatePin($pin_id, $user_id, $image_url, $description, $tags, $season_id) {
    global $conn;

    $stmt = $conn->prepare("UPDATE pins SET user_id = ?, image_url = ?, description = ?, tags = ?, season_id = ? WHERE pin_id = ?");
    $stmt->bind_param("isssii", $user_id, $image_url, $description, $tags, $season_id, $pin_id);
    
    return $stmt->execute();
}

function deletePin($pin_id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM pins WHERE pin_id = ?");
    $stmt->bind_param("i", $pin_id);
    
    return $stmt->execute();
}

// comments
function createComment($user_id, $pin_id, $content) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO comments (user_id, pin_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $pin_id, $content);

    if ($stmt->execute()) {
        $pin_owner_stmt = $conn->prepare("SELECT user_id FROM pins WHERE pin_id = ?");
        $pin_owner_stmt->bind_param("i", $pin_id);
        $pin_owner_stmt->execute();
        $pin_owner_result = $pin_owner_stmt->get_result();
        $pin_owner = $pin_owner_result->fetch_assoc();

        $notification_content = "User with ID {$user_id} commented on your pin.";
        createNotification($pin_owner['user_id'], 'comment', $notification_content, 0);

        return true;
    } else {
        return false;
    }
}

function readComment($comment_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM comments WHERE comment_id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

function updateComment($comment_id, $content) {
    global $conn;

    $stmt = $conn->prepare("UPDATE comments SET content = ? WHERE comment_id = ?");
    $stmt->bind_param("si", $content, $comment_id);

    return $stmt->execute();
}

function deleteComment($comment_id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
    $stmt->bind_param("i", $comment_id);

    return $stmt->execute();
}

// season collection
function createSeasonCollection($season, $description) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO season_collection (season, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $season, $description);
    
    return $stmt->execute();
}

function readSeasonCollection($collection_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM season_collection WHERE collection_id = ?");
    $stmt->bind_param("i", $collection_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

function updateSeasonCollection($collection_id, $season, $description) {
    global $conn;

    $stmt = $conn->prepare("UPDATE season_collection SET season = ?, description = ? WHERE collection_id = ?");
    $stmt->bind_param("ssi", $season, $description, $collection_id);

    return $stmt->execute();
}

function deleteSeasonCollection($collection_id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM season_collection WHERE collection_id = ?");
    $stmt->bind_param("i", $collection_id);

    return $stmt->execute();
}

// notifications
function createNotification($user_id, $type, $content, $read) {
    global $conn;
    $timestamp = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, content, timestamp, `read`) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die('Error preparing the statement: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("isssi", $user_id, $type, $content, $timestamp, $read);

    return $stmt->execute();
}

function readNotifications($user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateNotification($notification_id, $read) {
    global $conn;

    $stmt = $conn->prepare("UPDATE notifications SET read = ? WHERE notification_id = ?");
    $stmt->bind_param("ii", $read, $notification_id);

    return $stmt->execute();
}

function deleteNotification($notification_id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ?");
    $stmt->bind_param("i", $notification_id);

    return $stmt->execute();
}

// messages
function createMessage($sender_id, $receiver_id, $content) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $content);

    return $stmt->execute();
}

function readMessages($user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM messages WHERE sender_id = ? OR receiver_id = ?");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateMessage($message_id, $content) {
    global $conn;

    $stmt = $conn->prepare("UPDATE messages SET content = ? WHERE message_id = ?");
    $stmt->bind_param("si", $content, $message_id);

    return $stmt->execute();
}

function deleteMessage($message_id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM messages WHERE message_id = ?");
    $stmt->bind_param("i", $message_id);

    return $stmt->execute();
}

// boards
function createBoard($user_id, $name, $description) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO boards (user_id, name, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $name, $description);

    return $stmt->execute();
}

function readBoards($user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM boards WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateBoard($board_id, $name, $description) {
    global $conn;

    $stmt = $conn->prepare("UPDATE boards SET name = ?, description = ? WHERE board_id = ?");
    $stmt->bind_param("ssi", $name, $description, $board_id);

    return $stmt->execute();
}

function deleteBoard($board_id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM boards WHERE board_id = ?");
    $stmt->bind_param("i", $board_id);

    return $stmt->execute();
}

// analytics
function createAnalytics($user_id, $interaction_type, $timestamp, $associated_id) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO analytics (user_id, interaction_type, timestamp, associated_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $user_id, $interaction_type, $timestamp, $associated_id);

    return $stmt->execute();
}

function readAnalytics($user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM analytics WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateAnalytics($analytic_id, $user_id, $interaction_type, $timestamp, $associated_id) {
    global $conn;

    $stmt = $conn->prepare("UPDATE analytics SET user_id = ?, interaction_type = ?, timestamp = ?, associated_id = ? WHERE analytic_id = ?");
    $stmt->bind_param("issii", $user_id, $interaction_type, $timestamp, $associated_id, $analytic_id);

    return $stmt->execute();
}

function deleteAnalytics($analytic_id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM analytics WHERE analytic_id = ?");
    $stmt->bind_param("i", $analytic_id);

    return $stmt->execute();
}
?>
