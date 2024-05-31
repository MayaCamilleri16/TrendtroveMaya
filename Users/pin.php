<?php
session_start();
include('db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $description = $_POST['description'];
    $tags = $_POST['tags'];
    $season_id = isset($_POST['season_id']) ? $_POST['season_id'] : 0;

    // Handle file upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_url = $target_file;

        // Insert pin into database
        $stmt = $conn->prepare("INSERT INTO pins (user_id, image_url, description, tags, season_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $user_id, $image_url, $description, $tags, $season_id);

        if ($stmt->execute()) {
            echo "Pin posted successfully";
            header("Location: account.php");
            exit();
        } else {
            echo "Error posting pin: " . $conn->error;
        }
    } else {
        echo "Error uploading image.";
    }
}
?>
