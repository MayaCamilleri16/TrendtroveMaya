<?php
session_start();
include('db_connection.php');
include('db_functions.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$current_user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE users_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$is_following = false;

$follow_stmt = $conn->prepare("SELECT * FROM followers WHERE user_id = ? AND follower_user_id = ?");
$follow_stmt->bind_param("ii", $user_id, $current_user_id);
$follow_stmt->execute();
$follow_result = $follow_stmt->get_result();
if ($follow_result->num_rows > 0) {
    $is_following = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['name']); ?>'s Profile</title>
    <link rel="stylesheet" href="style/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
        <p><?php echo htmlspecialchars($user['bio']); ?></p>
        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="img-thumbnail">
        <form action="follow_unfollow.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <?php if ($is_following): ?>
                <button type="submit" name="action" value="unfollow" class="btn btn-danger">Unfollow</button>
            <?php else: ?>
                <button type="submit" name="action" value="follow" class="btn btn-primary">Follow</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
