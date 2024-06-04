<?php
include('db_connection.php');

$query = isset($_GET['query']) ? $_GET['query'] : '';

$response = [
    'users' => [],
    'seasons' => []
];

if ($query) {
    // Search for users
    $user_stmt = $conn->prepare("SELECT users_id, name FROM users WHERE name LIKE CONCAT('%', ?, '%')");
    $user_stmt->bind_param("s", $query);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    while ($user = $user_result->fetch_assoc()) {
        $response['users'][] = $user;
    }

    // Search for season collections
    $season_stmt = $conn->prepare("SELECT collection_id, season FROM SeasonCollection WHERE season LIKE CONCAT('%', ?, '%')");
    $season_stmt->bind_param("s", $query);
    $season_stmt->execute();
    $season_result = $season_stmt->get_result();
    while ($season = $season_result->fetch_assoc()) {
        $response['seasons'][] = $season;
    }
}

echo json_encode($response);
?>
