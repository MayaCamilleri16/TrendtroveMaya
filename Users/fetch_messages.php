<?php
session_start();
include('db_connection.php');
include('db_functions.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM messages WHERE sender_id = ? OR receiver_id = ? ORDER BY timestamp DESC");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['success' => true, 'messages' => $messages]);

$stmt->close();
$conn->close();
?>
