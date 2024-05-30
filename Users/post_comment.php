<?php
session_start();
include('db_connection.php');
include('db_functions.php');

if (!isset($_SESSION['user_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pin_id = $_POST['pin_id'];
$comment = $_POST['comment'];

// Insert the comment into the database
$stmt = $conn->prepare("INSERT INTO comments (user_id, pin_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $pin_id, $comment);

if ($stmt->execute()) {
    // Fetch the owner of the pin to send the notification to
    $pin_owner_stmt = $conn->prepare("SELECT user_id FROM pins WHERE pin_id = ?");
    $pin_owner_stmt->bind_param("i", $pin_id);
    $pin_owner_stmt->execute();
    $pin_owner_result = $pin_owner_stmt->get_result();
    $pin_owner = $pin_owner_result->fetch_assoc();

    // Create notification for the pin owner
    $notification_content = "User with ID {$user_id} commented on your pin.";
    createNotification($pin_owner['user_id'], 'comment', $notification_content, 0);

    header("Location: view_pin.php?pin_id=$pin_id");
    exit();
} else {
    echo "Error posting comment: " . $conn->error;
}
?>
