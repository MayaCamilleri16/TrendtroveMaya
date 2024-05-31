<?php
session_start();
include('db_connection.php');

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

// Fetch user's pins
$pins_stmt = $conn->prepare("SELECT * FROM pins WHERE user_id = ?");
$pins_stmt->bind_param("i", $user_id);
$pins_stmt->execute();
$pins_result = $pins_stmt->get_result();
$pins = $pins_result->fetch_all(MYSQLI_ASSOC);

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
    <style>
        .profile-container {
            margin-top: 20px;
        }
        .cover-photo {
            position: relative;
            width: 100%;
            height: 200px;
            background-size: cover;
            background-position: center;
            margin-bottom: 20px;
        }
        .profile-photo img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin-top: -75px;
            border: 5px solid #fff;
        }
        .profile-info {
            text-align: center;
        }
        .profile-actions {
            text-align: center;
            margin-top: 20px;
        }
        .profile-tabs {
            text-align: center;
            margin-top: 20px;
        }
        .profile-tabs .tab-btn {
            background: none;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
        }
        .profile-tabs .tab-btn.active {
            color: #007bff;
        }
        .profile-content {
            display: none;
        }
        .profile-content.active {
            display: block;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .card img {
            width: 100%;
            height: auto;
        }
        .card p {
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container profile-container">
        <div class="cover-photo" style="background-image: url('<?php echo htmlspecialchars($user['cover_photo']); ?>');"></div>
        <div class="profile-photo text-center">
            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Photo">
        </div>
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['name']); ?></h1>
            <p><?php echo htmlspecialchars($user['bio']); ?></p>
        </div>
        <div class="profile-actions">
            <form action="follow_unfollow.php" method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <?php if ($is_following): ?>
                    <button type="submit" name="action" value="unfollow" class="btn btn-danger">Unfollow</button>
                <?php else: ?>
                    <button type="submit" name="action" value="follow" class="btn btn-primary">Follow</button>
                <?php endif; ?>
            </form>
        </div>
        <div class="profile-tabs">
            <button class="tab-btn active" onclick="openTab('created')">Created</button>
            <button class="tab-btn" onclick="openTab('saved')">Saved</button>
        </div>
        <div class="profile-content active" id="created">
            <div class="grid-container">
                <?php
                // Display user's created pins
                foreach ($pins as $pin) {
                    echo "<div class='card'>
                            <a href='view_pin.php?pin_id={$pin['pin_id']}'>
                                <img src='{$pin['image_url']}' alt=''>
                                <p>{$pin['description']}</p>
                            </a>
                          </div>";
                }
                ?>
            </div>
        </div>
        <div class="profile-content" id="saved">
            <div class="grid-container">
                <!-- Display user's saved pins here -->
            </div>
        </div>
    </div>
    <script>
        function openTab(tabName) {
            var i;
            var x = document.getElementsByClassName("profile-content");
            var tabButtons = document.getElementsByClassName("tab-btn");
            for (i = 0; i < x.length; i++) {
                x[i].classList.remove("active");
            }
            for (i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove("active");
            }
            document.getElementById(tabName).classList.add("active");
            event.target.classList.add("active");
        }

        document.addEventListener('DOMContentLoaded', function () {
            openTab('created');
        });
    </script>
</body>
</html>
