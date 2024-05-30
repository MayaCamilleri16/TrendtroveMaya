<?php
include('db_connection.php');

// users
function createUser($name, $email, $password, $profile_picture, $bio) {
    global $conn;
    
    //for inserting a new user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, profile_picture, bio) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $profile_picture, $bio);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function readUser($user_id) {
    global $conn;
    
    //  statement for selecting a user
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function updateUser($user_id, $name, $email, $password, $profile_picture, $bio) {
    global $conn;
    
    //  statement for updating a user
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, profile_picture = ?, bio = ? WHERE user_id = ?");
    $stmt->bind_param("sssssi", $name, $email, $password, $profile_picture, $bio, $user_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function deleteUser($user_id) {
    global $conn;
    
    // Prepared statement for deleting a user
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

//  pins
function createPin($user_id, $image_url, $description, $tags) {
    global $conn;

    // statement for inserting a new pin
    $stmt = $conn->prepare("INSERT INTO pins (user_id, image_url, description, tags) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", user_id, image_url, description, tags);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}
function readPin($pin_id) {
    global $conn;

    // statement for selecting a pin
    $stmt = $conn->prepare("SELECT * FROM pins WHERE pin_id = ?");
    $stmt->bind_param("i", $pin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc(); 
}


function updatePin($pin_id, $user_id, $image_url, $description, $tags) {
    global $conn;

    // statement for updating a pin
    $stmt = $conn->prepare("UPDATE pins SET user_id = ?, image_url = ?, description = ?, tags = ? WHERE pin_id = ?");
    $stmt->bind_param("isssi", user_id, image_url, description, tags, pin_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function deletePin($pin_id) {
    global $conn;

    //  statement for deleting a pin
    $stmt = $conn->prepare("DELETE FROM pins WHERE pin_id = ?");

    $stmt->bind_param("i", pin_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}



// CRUD operations for comments
function createComment($user_id, $pin_id, $content) {
    global $conn;
    
    //  statement for inserting a new comment
    $stmt = $conn->prepare("INSERT INTO comments (user_id, pin_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, pin_id, content);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function readComment($comment_id) {
    global $conn;
    
    //  statement for selecting a comment
    $stmt = $conn->prepare("SELECT * FROM comments WHERE comment_id = ?");
    $stmt->bind_param("i", comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function updateComment($comment_id,$content) {
    global $conn;
    
    //  statement for updating a comment
    $stmt = $conn->prepare("UPDATE comments SET content = ? WHERE comment_id = ?");
    $stmt->bind_param("si", content, comment_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function deleteComment($comment_id) {
    global $conn;
    
    // statement for deleting a comment
    $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
    $stmt->bind_param("i", comment_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

//  user preference
function createUserPreference($user_id, $preference, $value) {
    global $conn;
    
    // statement for inserting a new user preference
    $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, preference, value) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $preference, $value);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Read a user preference
function readUserPreference($preference_id) {
    global $conn;
    
    //statement for selecting a user preference
    $stmt = $conn->prepare("SELECT * FROM user_preferences WHERE preference_id = ?");
    $stmt->bind_param("i", $preference_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Update a user preference
function updateUserPreference($preference_id, $preference, $value) {
    global $conn;
    
    // statement for updating a user preference
    $stmt = $conn->prepare("UPDATE user_preferences SET preference = ?, value = ? WHERE preference_id = ?");
    $stmt->bind_param("ssi", $preference, $value, $preference_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Delete a user preference
function deleteUserPreference($preference_id) {
    global $conn;
    
    // Prepared statement for deleting a user preference
    $stmt = $conn->prepare("DELETE FROM user_preferences WHERE preference_id = ?");
    $stmt->bind_param("i", $preference_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}
//season collection
function createSeasonCollection($season, $description) {
    global $conn;
    
    // statement for inserting a new season collection
    $stmt = $conn->prepare("INSERT INTO season_collection (season, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $season, description);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Read a season collection
function readSeasonCollection($collection_id) {
    global $conn;
    
    //  statement for selecting a season collection
    $stmt = $conn->prepare("SELECT * FROM season_collection WHERE collection_id = ?");
    $stmt->bind_param("i", $collection_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Update a season collection
function updateSeasonCollection($collection_id, $season, $description) {
    global $conn;
    
    //statement for updating a season collection
    $stmt = $conn->prepare("UPDATE season_collection SET season = ?, description = ? WHERE collection_id = ?");
    $stmt->bind_param("ssi", $season, $description, $collection_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Delete a season collection
function deleteSeasonCollection($collection_id) {
    global $conn;
    
    // statement for deleting a season collection
    $stmt = $conn->prepare("DELETE FROM season_collection WHERE collection_id = ?");
    $stmt->bind_param("i", $collection_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}
// Create a new search history entry
function createSearchHistory($user_id, $search_query, $timestamp) {
    global $conn;
    
    // statement for inserting a new search history entry
    $stmt = $conn->prepare("INSERT INTO search_history (user_id, search_query, timestamp) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $search_query, $timestamp);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Read search history entries by user_id
function readSearchHistory($user_id) {
    global $conn;
    
    // statement for selecting search history entries for a specific user
    $stmt = $conn->prepare("SELECT * FROM search_history WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Update a search history entry
function updateSearchHistory($history_id, $user_id, $search_query, $timestamp) {
    global $conn;
    
    // statement for updating a search history entry
    $stmt = $conn->prepare("UPDATE search_history SET user_id = ?, search_query = ?, timestamp = ? WHERE history_id = ?");
    $stmt->bind_param("issi", $user_id, $search_query, $timestamp, $history_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Delete a search history entry
function deleteSearchHistory($history_id) {
    global $conn;
    
    // statement for deleting a search history entry
    $stmt = $conn->prepare("DELETE FROM search_history WHERE history_id = ?");
    $stmt->bind_param("i", $history_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}
// Create a new notification
function createNotification($user_id, $type, $content, $timestamp, $read) {
    global $conn;
    
    //statement for inserting a new notification
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, content, timestamp, read) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $user_id, $type, $content, $timestamp, $read);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Read notifications for a specific user
function readNotifications($user_id) {
    global $conn;
    
    // statement for selecting notifications for a specific user
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Update a notification (e.g. mark as read)
function updateNotification($notification_id, $read) {
    global $conn;
    
    // statement for updating a notification's read status
    $stmt = $conn->prepare("UPDATE notifications SET read = ? WHERE notification_id = ?");
    $stmt->bind_param("ii", $read, $notification_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Delete a notification
function deleteNotification($notification_id) {
    global $conn;
    
    //statement for deleting a notification
    $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ?");
    $stmt->bind_param("i", $notification_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Create a new message
function createMessage($sender_id, $receiver_id, $content, $timestamp) {
    global $conn;
    
    //statement for inserting a new message
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, timestamp) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $content, $timestamp);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Read messages for a specific user (either as sender or receiver)
function readMessages($user_id) {
    global $conn;
    
    //  statement for selecting messages for a specific user (either as sender or receiver)
    $stmt = $conn->prepare("SELECT * FROM messages WHERE sender_id = ? OR receiver_id = ?");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Update a message 
function updateMessage($message_id, $content) {
    global $conn;
    
    //  statement for updating a message's content
    $stmt = $conn->prepare("UPDATE messages SET content = ? WHERE message_id = ?");
    $stmt->bind_param("si", $content, $message_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Delete a message
function deleteMessage($message_id) {
    global $conn;
    
    //statement for deleting a message
    $stmt = $conn->prepare("DELETE FROM messages WHERE message_id = ?");
    $stmt->bind_param("i", $message_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}
// Create a new board
function createBoard($user_id, $name, $description) {
    global $conn;
    
    // statement for inserting a new board
    $stmt = $conn->prepare("INSERT INTO boards (user_id, name, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, name, $description);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Read boards for a specific user
function readBoards($user_id) {
    global $conn;
    
    //statement for selecting boards for a specific user
    $stmt = $conn->prepare("SELECT * FROM boards WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Update a board
function updateBoard($board_id, $name, $description) {
    global $conn;
    
    // statement for updating a board
    $stmt = $conn->prepare("UPDATE boards SET name = ?, description = ? WHERE board_id = ?");
    $stmt->bind_param("ssi", name, description, board_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Delete a board
function deleteBoard($board_id) {
    global $conn;
    
    //statement for deleting a board
    $stmt = $conn->prepare("DELETE FROM boards WHERE board_id = ?");
    $stmt->bind_param("i", board_id);
if ($stmt->execute()) {
    // Return true if successful
    return true;
} else {
    // Return false otherwise
    return false;
}

}

// analytics entry
function createAnalytics($user_id, $interaction_type, $timestamp, $associated_id) {
    global $conn;
    
    // statement for inserting a new analytics entry
    $stmt = $conn->prepare("INSERT INTO analytics (user_id, interaction_type, timestamp, associated_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iss", $user_id, interaction_type, $timestamp, $associated_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Read analytics data
function readAnalytics($user_id) {
    global $conn;
    
    //statement for selecting analytics data for a specific user
    $stmt = $conn->prepare("SELECT * FROM analytics WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Update an analytics entry
function updateAnalytics($analytic_id, $user_id, $interaction_type, $timestamp, $associated_id) {
    global $conn;
    
    // statement for updating an analytics entry
    $stmt = $conn->prepare("UPDATE analytics SET user_id = ?, interaction_type = ?, timestamp = ?, associated_id = ? WHERE analytic_id = ?");
    $stmt->bind_param("issii", $user_id, interaction_type, $timestamp, associated_id, analytic_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Delete an analytics entry
function deleteAnalytics($analytic_id) {
    global $conn;
    
    // statement for deleting an analytics entry
    $stmt = $conn->prepare("DELETE FROM analytics WHERE analytic_id = ?");
    $stmt->bind_param("i", analytic_id);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}
?>