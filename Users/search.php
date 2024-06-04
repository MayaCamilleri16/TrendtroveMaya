<?php
session_start();
include('db_connection.php');
include('db_functions.php');

$query = isset($_GET['query']) ? $_GET['query'] : '';

if ($query) {
    // Search for users
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE CONCAT('%', ?, '%') LIMIT 1");
    $user_stmt->bind_param("s", $query);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();

    if ($user) {
        header("Location: view_profile.php?user_id=" . $user['users_id']);
        exit();
    }

    // If no user found, search for season collections
    $season_stmt = $conn->prepare("SELECT * FROM SeasonCollection WHERE season LIKE CONCAT('%', ?, '%') LIMIT 1");
    $season_stmt->bind_param("s", $query);
    $season_stmt->execute();
    $season_result = $season_stmt->get_result();
    $season = $season_result->fetch_assoc();

    if ($season) {
        switch (strtolower($season['season'])) {
            case 'spring collection':
                header("Location: spring.php");
                break;
            case 'summer collection':
                header("Location: summer.php");
                break;
            case 'winter collection':
                header("Location: winter.php");
                break;
            case 'autumn collection':
                header("Location: autumn.php");
                break;
            default:
                echo "Season collection not found.";
        }
        exit();
    }

    // If no user or season found, show a message or handle it appropriately
    echo "No results found.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <style>
        .suggestions {
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-height: 200px;
            overflow-y: auto;
            width: 300px;
            display: none;
        }
        .suggestions ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .suggestions ul li {
            padding: 10px;
            cursor: pointer;
        }
        .suggestions ul li:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <form class="form-inline mr-2" action="search.php" method="GET" id="searchForm">
        <input class="form-control mr-sm-2" type="search" placeholder="Search users or seasons" aria-label="Search" name="query" id="searchInput" required>
        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        <div id="suggestions" class="suggestions"></div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const suggestionsBox = document.getElementById('suggestions');
            const searchForm = document.getElementById('searchForm');

            searchInput.addEventListener('input', function() {
                const query = searchInput.value;
                if (query.length > 2) {
                    fetch(`search_suggestions.php?query=${query}`)
                        .then(response => response.json())
                        .then(data => {
                            let suggestionsList = '';

                            if (data.users.length > 0) {
                                suggestionsList += '<li><strong>Users</strong></li>';
                                suggestionsList += data.users.map(user => `<li data-id="${user.users_id}" data-type="user">${user.name}</li>`).join('');
                            }

                            if (data.seasons.length > 0) {
                                suggestionsList += '<li><strong>Seasons</strong></li>';
                                suggestionsList += data.seasons.map(season => `<li data-id="${season.collection_id}" data-type="season">${season.season}</li>`).join('');
                            }

                            if (suggestionsList) {
                                suggestionsBox.innerHTML = `<ul>${suggestionsList}</ul>`;
                                suggestionsBox.style.display = 'block';
                            } else {
                                suggestionsBox.innerHTML = '';
                                suggestionsBox.style.display = 'none';
                            }
                        });
                } else {
                    suggestionsBox.innerHTML = '';
                    suggestionsBox.style.display = 'none';
                }
            });

            suggestionsBox.addEventListener('click', function(event) {
                if (event.target.tagName === 'LI' && event.target.dataset.type) {
                    const id = event.target.getAttribute('data-id');
                    const type = event.target.getAttribute('data-type');
                    if (type === 'user') {
                        window.location.href = `view_profile.php?user_id=${id}`;
                    } else if (type === 'season') {
                        window.location.href = `view_season.php?season_id=${id}`;
                    }
                }
            });

            document.addEventListener('click', function(event) {
                if (!searchForm.contains(event.target)) {
                    suggestionsBox.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
