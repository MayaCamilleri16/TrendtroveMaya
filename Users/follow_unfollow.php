<?php
session_start();
include('db_connection.php');
include('db_functions.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$action = $_POST['action'];
$user_id = (int)$_POST['user_id'];
$current_user_id = $_SESSION['user_id'];

if ($action === 'follow') {
    $stmt = $conn->prepare("INSERT INTO followers (user_id, follower_user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $current_user_id);
    $stmt->execute();
    
    $notification_content = "User with ID {$current_user_id} followed you.";
    createNotification($user_id, 'follow', $notification_content, 0);

} elseif ($action === 'unfollow') {
    $stmt = $conn->prepare("DELETE FROM followers WHERE user_id = ? AND follower_user_id = ?");
    $stmt->bind_param("ii", $user_id, $current_user_id);
    $stmt->execute();
}

header("Location: view_profile.php?user_id={$user_id}");
exit();
?>
