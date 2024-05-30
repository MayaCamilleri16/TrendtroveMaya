<?php
session_start();
include('db_connection.php');

// Check if pin_id is provided
if (!isset($_GET['pin_id'])) {
    header("Location: account.php");
    exit();
}

$pin_id = $_GET['pin_id'];

// Fetch pin details from the database
$stmt = $conn->prepare("SELECT p.*, u.name, u.profile_picture FROM pin p JOIN users u ON p.user_id = u.users_id WHERE p.pin_id = ?");
$stmt->bind_param("i", $pin_id);
$stmt->execute();
$result = $stmt->get_result();
$pin = $result->fetch_assoc();

if (!$pin) {
    echo "Pin not found";
    exit();
}

// Fetch comments for the pin
$comments_stmt = $conn->prepare("SELECT c.*, u.name, u.profile_picture FROM comments c JOIN users u ON c.user_id = u.users_id WHERE c.pin_id = ?");
$comments_stmt->bind_param("i", $pin_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
$comments = $comments_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Pin</title>
    <link rel="stylesheet" href="style/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="pin-details">
            <img src="<?php echo htmlspecialchars($pin['image_url']); ?>" alt="Pin Image" class="pin-image">
            <div class="pin-info">
                <h2><?php echo htmlspecialchars($pin['description']); ?></h2>
                <p><?php echo htmlspecialchars($pin['tags']); ?></p>
                <div class="user-info">
                    <img src="<?php echo htmlspecialchars($pin['profile_picture']); ?>" alt="User Profile Picture" class="user-profile-picture">
                    <p><?php echo htmlspecialchars($pin['name']); ?></p>
                </div>
                <p><strong>Note to self:</strong> What do you want to remember about this Pin?</p>
                <textarea placeholder="Add note"></textarea>
            </div>
        </div>
        <div class="comments-section">
            <h3>Comments</h3>
            <!-- Display comments -->
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-user-info">
                        <img src="<?php echo htmlspecialchars($comment['profile_picture']); ?>" alt="User Profile Picture" class="comment-user-profile-picture">
                        <p><?php echo htmlspecialchars($comment['name']); ?></p>
                    </div>
                    <p><?php echo htmlspecialchars($comment['content']); ?></p>
                    <p class="timestamp"><?php echo htmlspecialchars($comment['timestamp']); ?></p>
                </div>
            <?php endforeach; ?>
            <!-- Add a comment form -->
            <form action="add_comment.php" method="post">
                <input type="hidden" name="pin_id" value="<?php echo $pin_id; ?>">
                <textarea name="comment" placeholder="Add a comment" required></textarea>
                <button type="submit">Post</button>
            </form>
        </div>
    </div>
</body>
</html>
