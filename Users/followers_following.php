<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Followers & Following</title>
    <link rel="stylesheet" href="style/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
<div class="container">
    <h2>Followers & Following</h2>

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#followersModal">View Followers</button>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#followingModal">View Following</button>

    <!-- Followers Modal -->
    <div class="modal fade" id="followersModal" tabindex="-1" role="dialog" aria-labelledby="followersModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="followersModalLabel">Followers</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="user-list" id="followersList">
                        <?php foreach ($followers as $follower): ?>
                            <li>
                                <div class="user-info">
                                    <img src="<?php echo htmlspecialchars($follower['profile_picture']); ?>" alt="Profile Picture">
                                    <span><?php echo htmlspecialchars($follower['name']); ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Following Modal -->
    <div class="modal fade" id="followingModal" tabindex="-1" role="dialog" aria-labelledby="followingModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="followingModalLabel">Following</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="user-list" id="followingList">
                        <?php foreach ($following as $follow): ?>
                            <li>
                                <div class="user-info">
                                    <img src="<?php echo htmlspecialchars($follow['profile_picture']); ?>" alt="Profile Picture">
                                    <span><?php echo htmlspecialchars($follow['name']); ?></span>
                                </div>
                                <button class="btn btn-danger unfollow-btn" data-user-id="<?php echo $follow['users_id']; ?>">Unfollow</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('.unfollow-btn').click(function() {
            var userId = $(this).data('user-id');
            var button = $(this);

            $.ajax({
                type: 'POST',
                url: 'follow_unfollow.php',
                data: {user_id: userId, action: 'unfollow'},
                success: function(response) {
                    if(response.success) {
                        button.closest('li').remove();
                    } else {
                        alert('Error unfollowing user.');
                    }
                }
            });
        });
    });
</script>
</body>
</html>
