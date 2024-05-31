<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$query = isset($_GET['query']) ? $_GET['query'] : '';

if ($query) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE CONCAT('%', ?, '%')");
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="style/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>
        <?php if (!empty($users)): ?>
            <ul>
                <?php foreach ($users as $user): ?>
                    <li>
                        <a href="view_profile.php?user_id=<?php echo htmlspecialchars($user['users_id']); ?>">
                            <?php echo htmlspecialchars($user['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
