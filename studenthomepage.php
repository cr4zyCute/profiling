<?php
session_start();
include './database/db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT profile_image FROM student WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($profile_image);
$stmt->fetch();
$stmt->close();
// Fetch posts, including profile_image
$sql = "SELECT posts.post_id, posts.content AS post_content, posts.media_path, posts.created_at AS post_date, 
        student.first_name, student.last_name, student.profile_image,
        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.post_id) AS like_count
        FROM posts
        JOIN student ON posts.student_id = student.id
        ORDER BY posts.created_at DESC";
$posts_result = $conn->query($sql);

// Handle new post creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_post'])) {
    $content = isset($_POST['content']) && trim($_POST['content']) !== '' ? trim($_POST['content']) : null;
    $student_id = $_SESSION['student_id'];
    $media_path = null;

    // Handle file upload
    if (isset($_FILES['media']) && $_FILES['media']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        $media_name = basename($_FILES['media']['name']);
        $media_path = $upload_dir . uniqid() . "_" . $media_name;

        if (!move_uploaded_file($_FILES['media']['tmp_name'], $media_path)) {
            echo "Failed to upload media.";
            exit();
        }
    }

    // Ensure at least one of content or media is provided
    if (empty($content) && empty($media_path)) {
        echo "You must provide text or media to create a post.";
        exit();
    }

    // Insert into the database
    $stmt = $conn->prepare("INSERT INTO posts (student_id, content, media_path) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $student_id, $content, $media_path);
    $stmt->execute();
    $stmt->close();
    header("Location: studenthomepage.php");
    exit();
}

// Handle new comment or reply
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_comment'])) {
    $post_id = intval($_POST['post_id']);
    $parent_comment_id = isset($_POST['parent_comment_id']) ? intval($_POST['parent_comment_id']) : null;
    $student_id = $_SESSION['student_id'];
    $comment_content = trim($_POST['comment_content']);

    if (!empty($comment_content)) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, student_id, content, parent_comment_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $post_id, $student_id, $comment_content, $parent_comment_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Comment content cannot be empty.";
    }

    // Refresh the page to display the new comment
    header("Location: studenthomepage.php");
    exit();
}

function displayComments($post_id, $parent_comment_id = null, $conn, $level = 0)
{
    $sql = "SELECT comments.comment_id, comments.content AS comment_content, comments.created_at AS comment_date, 
            student.first_name, student.last_name, comments.parent_comment_id 
            FROM comments 
            JOIN student ON comments.student_id = student.id 
            WHERE comments.post_id = ? AND comments.parent_comment_id " . ($parent_comment_id ? "= ?" : "IS NULL") . " 
            ORDER BY comments.created_at ASC";

    $stmt = $conn->prepare($sql);
    if ($parent_comment_id) {
        $stmt->bind_param("ii", $post_id, $parent_comment_id);
    } else {
        $stmt->bind_param("i", $post_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($comment = $result->fetch_assoc()) {
        echo "<div class='comment' style='margin-left: " . ($level * 20) . "px;'>";
        echo "<strong>{$comment['first_name']} {$comment['last_name']}</strong>: ";
        echo htmlspecialchars($comment['comment_content']);
        echo "<small> ({$comment['comment_date']})</small>";

        // If this is a reply, show the parent comment indicator
        if ($parent_comment_id) {
            echo "<div class='reply-indicator'><em>Replying to comment ID: {$parent_comment_id}</em></div>";
        }

        echo "<form method='post' class='reply-form' style='margin-top: 10px;'>
                <input type='hidden' name='post_id' value='{$post_id}'>
                <input type='hidden' name='parent_comment_id' value='{$comment['comment_id']}'>
                <textarea name='comment_content' placeholder='Write a reply...' required></textarea><br>
                <button type='submit' name='new_comment'>Reply</button>
              </form>";

        echo "</div>";

        // Recursive call to display replies of this comment
        displayComments($post_id, $comment['comment_id'], $conn, $level + 1);
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="./css/homepage.css">

    <script>
        function toggleComments(postId) {
            var commentsSection = document.getElementById('comments-' + postId);
            var button = document.getElementById('toggle-comments-btn-' + postId);

            if (commentsSection.style.display === "none") {
                commentsSection.style.display = "block";
                button.textContent = "Hide Comments";
            } else {
                commentsSection.style.display = "none";
                button.textContent = "Show Comments";
            }
        }
    </script>
</head>

<body>
    <header class="header">

        <nav>
            <a href="#home">Home</a>
            <a href="#annoucement">Announcements</a>
        </nav>
        <div class="profile">
            <div class="dropdown">
                <a href="#">
                    <img src="<?= htmlspecialchars(string: './' . $profile_image); ?>" alt="Profile Image" class="profile-img">
                </a>
                <div class="dropdown-content">
                    <a href="student.php">View Profile</a>
                    <a href="includes/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>


    <h1>Whats on Your Mind ?</h1>
    <form method="post" enctype="multipart/form-data">
        <textarea name="content" placeholder="What's on your mind?"></textarea><br>
        <label for="media">Upload Media:</label>
        <input type="file" name="media" id="media"><br>
        <button type="submit" name="new_post">Post</button>
    </form>
    <hr>
    <div class="annoucement" id="annoucement">

        <?php
        // Fetch announcements
        $announcement_sql = "SELECT content, created_at FROM announcements ORDER BY created_at DESC";
        $announcement_result = $conn->query($announcement_sql);

        if ($announcement_result->num_rows > 0): ?>
            <h2>Announcements</h2>
            <div class="announcements-section">
                <?php while ($announcement = $announcement_result->fetch_assoc()): ?>
                    <div class="announcement">
                        <p><?= htmlspecialchars($announcement['content']); ?></p>
                        <small>Posted on: <?= htmlspecialchars($announcement['created_at']); ?></small>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No announcements available.</p>
        <?php endif; ?>

    </div>
    <h2 id="home">Posts</h2>

    <?php while ($post = $posts_result->fetch_assoc()): ?>
        <div class="post">
            <div class="profile-section">
                <img style="width: 45px; height:45px; border-radius: 50%; " src="<?= htmlspecialchars($post['profile_image']); ?>"
                    alt="Profile Image"
                    class="profile-image">
                <h3><?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></h3>
            </div>
            <?php if (!empty($post['post_content'])): ?>
                <p><?php echo htmlspecialchars($post['post_content']); ?></p>
            <?php endif; ?>
            <?php if (!empty($post['media_path'])): ?>
                <img src="<?php echo $post['media_path']; ?>" alt="Media">
            <?php endif; ?>
            <small>Posted on: <?php echo $post['post_date']; ?></small><br>
            <a href="like.php?post_id=<?php echo $post['post_id']; ?>">Like</a> (<?php echo $post['like_count']; ?>)


            <!-- Show/Hide comments button -->
            <button id="toggle-comments-btn-<?php echo $post['post_id']; ?>" onclick="toggleComments(<?php echo $post['post_id']; ?>)"> Comments</button>

            <!-- Comments section -->
            <div id="comments-<?php echo $post['post_id']; ?>" class="comments-section">
                <?php displayComments($post['post_id'], null, $conn); ?>
                <form method="post" style="margin-top: 10px;">
                    <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                    <textarea name="comment_content" placeholder="Write a comment..." required></textarea><br>
                    <button type="submit" name="new_comment">Comment</button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdown = document.querySelector('.dropdown');
            const dropdownContent = document.querySelector('.dropdown-content');

            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownContent.style.display =
                    dropdownContent.style.display === 'block' ? 'none' : 'block';
            });

            document.addEventListener('click', function() {
                dropdownContent.style.display = 'none';
            });
        });
    </script>

</body>

</html>