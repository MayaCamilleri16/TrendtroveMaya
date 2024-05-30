<?php
session_start();
include('db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_photo'])) {
    $file_tmp = $_FILES['profile_photo']['tmp_name'];
    $file_name = $_FILES['profile_photo']['name'];
    $file_path = "uploads/profile_photos/$file_name";

    if (move_uploaded_file($file_tmp, $file_path)) {
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE users_id = ?");
        $stmt->bind_param("si", $file_path, $user_id);
        if ($stmt->execute()) {
            echo "Profile photo updated successfully";
        } else {
            echo "Error updating profile photo: " . $conn->error;
        }
    } else {
        echo "Error uploading file";
    }
    header("Location: account.php");
    exit();
}
?>
