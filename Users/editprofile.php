<?php
session_start();
include('db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from the database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE users_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $bio = $_POST['bio'];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $hashed_password = $user['password'];
    }

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, bio = ? WHERE users_id = ?");
    $stmt->bind_param("ssssi", $name, $email, $hashed_password, $bio, $user_id);

    if ($stmt->execute()) {
        echo "Profile updated successfully";
        header("Location: account.php");
        exit();
    } else {
        echo "Error updating profile: " . $conn->error;
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $profile_photo = 'uploads/' . basename($_FILES['profile_photo']['name']);
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], $profile_photo);

        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE users_id = ?");
        $stmt->bind_param("si", $profile_photo, $user_id);
        $stmt->execute();
    }

    // Handle cover photo upload
    if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
        $cover_photo = 'uploads/' . basename($_FILES['cover_photo']['name']);
        move_uploaded_file($_FILES['cover_photo']['tmp_name'], $cover_photo);

        $stmt = $conn->prepare("UPDATE users SET cover_photo = ? WHERE users_id = ?");
        $stmt->bind_param("si", $cover_photo, $user_id);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="edit-profile">
            <h2>Edit Profile</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <input type="password" name="password" placeholder="New Password (leave blank to keep current password)">
                <textarea name="bio" placeholder="Your bio..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                
                <label for="change-profile-photo" class="change-photo-label">Change Profile Photo</label>
                <input type="file" id="change-profile-photo" name="profile_photo" class="change-photo-input">
                
                <label for="change-cover-photo" class="change-photo-label">Change Cover Photo</label>
                <input type="file" id="change-cover-photo" name="cover_photo" class="change-photo-input">
                
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>
