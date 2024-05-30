<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['user_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pin_id = $_POST['pin_id'];
$comment = $_POST['comment'];

$stmt = $conn->prepare("INSERT INTO comments (user_id, pin_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $pin_id, $comment);

if ($stmt->execute()) {
    header("Location: view_pin.php?pin_id=$pin_id");
    exit();
} else {
    echo "Error posting comment: " . $conn->error;
}
?>
