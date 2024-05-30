<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trendtrove</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="style/style.css">
    <!-- Masonry library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/4.2.2/masonry.pkgd.min.js"></script>
</head>
<body>
    <!-- Header using Bootstrap Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="#">
            <div class="logo-container rounded-circle overflow-hidden">
                <img src="assets/logo.png" alt="Trendtrove Logo" class="img-fluid">
            </div>
        </a>
        <div class="ml-auto">
            <h1 class="title">Home Feed</h1>
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#">Create</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Analytics
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="#">Overview</a>
                        <a class="dropdown-item" href="#">Audience Insights</a>
                        <a class="dropdown-item" href="#">Conversion Insights</a>
                        <a class="dropdown-item" href="#">Trends</a>
                    </div>
                </li>
            </ul>
        </div>
        <div class="header-icons">
            <img src="assets/search.png" alt="Search" class="icon">
            <img src="assets/notification.png" alt="Notifications" class="icon">
            <img src="assets/messages.png" alt="Messages" class="icon">
            <img src="assets/account.png" alt="Account" class="icon">
        </div>
    </nav>


    <!-- Main area -->
    <main id="content">

    <!-- for you -->
    <h2 class="title" style="text-align: center; margin-top: 20px;">For You</h2>

    <!-- container -->
    <div class="grid-container">
        <?php
        $cards = [
            ["image" => "https://i.pinimg.com/736x/9a/1d/4e/9a1d4e46d5bf7855f41690a8cad751fe.jpg", "description" => "Description 1"],
            ["image" => "https://i.pinimg.com/564x/7e/8b/cb/7e8bcbf8b5a3750440f7bc4735054cc4.jpg", "description" => "Description 2"],
            ["image" => "https://i.pinimg.com/564x/3c/a3/aa/3ca3aa2bc1c4918e8d5c4aaef566735a.jpg", "description" => "Description 3"],
            ["image" => "https://i.pinimg.com/736x/88/35/ed/8835ed750c4c23da2409a05cedf64b61.jpg", "description" => "Description 1"],
            ["image" => "https://i.pinimg.com/564x/14/77/7b/14777b4787ebefeba694c7a9539bd3bf.jpg", "description" => "Description 2"],
            ["image" => "https://i.pinimg.com/564x/12/88/ba/1288ba7ff3c35080afe90dccf47fe0ac.jpg", "description" => "Description 3"],
            ["image" => "https://i.pinimg.com/564x/65/6d/a0/656da0cdcc75b64ede64773e8079d50b.jpg", "description" => "Description 1"],
            ["image" => "https://i.pinimg.com/736x/93/60/3c/93603c4168f40853ecac042d8b224e52.jpg", "description" => "Description 2"],
            ["image" => "https://i.pinimg.com/736x/47/fe/83/47fe83eeb1b4ac5ca5a64df23188bde7.jpg", "description" => "Description 3"],
            ["image" => "https://i.pinimg.com/736x/7e/cd/c7/7ecdc7e4abcc1bdfd50661006e3d0831.jpg", "description" => "Description 1"],
            ["image" => "https://i.pinimg.com/736x/1c/85/70/1c857011b57f556b67bfe37f9b78db1f.jpg", "description" => "Description 2"],
            ["image" => "https://i.pinimg.com/736x/9e/8e/6c/9e8e6c5f7c126ca0b0d5b49708b63551.jpg", "description" => "Description 3"],
        ];

        foreach ($cards as $card) {
            echo "<div class='card'>
                    <img src='{$card['image']}' alt=''>
                    <p>{$card['description']}</p>
                  </div>";
        }
        ?>
    </div>
</main>
      

     <!-- Footer -->
     <footer class="text-center mt-4">
        <p>&copy; 2024 Trendtrove</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
