<?php
session_start();
include('db_connection.php');
include('db_functions.php');

if (!isset($_SESSION['user_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$message_content = $_POST['message_content'];
$pin_id = $_POST['pin_id'];
$timestamp = date('Y-m-d H:i:s');

// Validate form data
if (empty($receiver_id) || empty($message_content) || empty($pin_id)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Insert the new message into the database
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, timestamp) VALUES (?, ?, ?, ?)");
$content = "Shared a pin: <a href='view_pin.php?pin_id={$pin_id}'>View Pin</a><br>" . $message_content;
$stmt->bind_param("iiss", $user_id, $receiver_id, $content, $timestamp);

if ($stmt->execute()) {
    // Create a notification for the receiver
    $notification_content = "You have received a new message from user ID {$user_id}.";
    createNotification($receiver_id, 'message', $notification_content, 0);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error sending message: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
