<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$query = isset($_GET['query']) ? $_GET['query'] : '';

if ($query) {
    // Search for users
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE CONCAT('%', ?, '%')");
    $user_stmt->bind_param("s", $query);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $users = $user_result->fetch_all(MYSQLI_ASSOC);

    // Search for seasons
    $season_stmt = $conn->prepare("SELECT * FROM pins WHERE description LIKE CONCAT('%', ?, '%') OR tags LIKE CONCAT('%', ?, '%')");
    $season_stmt->bind_param("ss", $query, $query);
    $season_stmt->execute();
    $season_result = $season_stmt->get_result();
    $seasons = $season_result->fetch_all(MYSQLI_ASSOC);
} else {
    $users = [];
    $seasons = [];
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
            <h3>Users</h3>
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

        <?php if (!empty($seasons)): ?>
            <h3>Seasons</h3>
            <ul>
                <?php foreach ($seasons as $season): ?>
                    <li>
                        <a href="view_pin.php?pin_id=<?php echo htmlspecialchars($season['pin_id']); ?>">
                            <?php echo htmlspecialchars($season['description']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No season pins found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
