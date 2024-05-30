<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['user_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$content = $_POST['content'];
$timestamp = date('Y-m-d H:i:s');

// Validate form data
if (empty($receiver_id) || empty($content)) {
    echo "All fields are required.";
    exit();
}

// Insert the new message into the database
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, timestamp) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $user_id, $receiver_id, $content, $timestamp);

if ($stmt->execute()) {
    // Redirect to the account page 
    header("Location: account.php");
    exit();
} else {
    echo "Error sending message: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
