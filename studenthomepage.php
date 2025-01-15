<?php
session_start();
include './database/db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch posts
$sql = "SELECT posts.post_id, posts.content AS post_content, posts.media_path, posts.created_at AS post_date, 
        student.first_name, student.last_name,
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
    <!-- <link rel="stylesheet" href="./css/homepage.css"> -->
    <title>Homepage</title>
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
    <h1>Welcome to the Student Homepage</h1>
    <form method="post" enctype="multipart/form-data">
        <textarea name="content" placeholder="What's on your mind?"></textarea><br>
        <label for="media">Upload Media:</label>
        <input type="file" name="media" id="media"><br>
        <button type="submit" name="new_post">Post</button>
    </form>
    <hr>
    <h2>Posts</h2>
    <?php while ($post = $posts_result->fetch_assoc()): ?>
        <div>
            <h3><?php echo $post['first_name'] . ' ' . $post['last_name']; ?></h3>
            <?php if (!empty($post['post_content'])): ?>
                <p><?php echo htmlspecialchars($post['post_content']); ?></p>
            <?php endif; ?>
            <?php if (!empty($post['media_path'])): ?>
                <img src="<?php echo $post['media_path']; ?>" alt="Media" style="max-width: 100%; height: auto;">
            <?php endif; ?>
            <small>Posted on: <?php echo $post['post_date']; ?></small><br>
            <a href="like.php?post_id=<?php echo $post['post_id']; ?>">Like</a> (<?php echo $post['like_count']; ?>)
            <form method="post" style="margin-top: 10px;">
                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                <textarea name="comment_content" placeholder="Write a comment..." required></textarea><br>
                <button type="submit" name="new_comment">Comment</button>
            </form>

            <!-- Show/Hide comments button -->
            <button id="toggle-comments-btn-<?php echo $post['post_id']; ?>" onclick="toggleComments(<?php echo $post['post_id']; ?>)">Show Comments</button>

            <!-- Comments section -->
            <div id="comments-<?php echo $post['post_id']; ?>" style="margin-left: 20px; display: none;">
                <?php displayComments($post['post_id'], null, $conn); ?>
            </div>
        </div>
        <hr>
    <?php endwhile; ?>
</body>

</html>