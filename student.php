<?php
session_start();
include './database/db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    die('You are not logged in.');
}

// Retrieve the student ID from the session if not passed via GET
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
} else {
    $id = $_SESSION['student_id'];
}

// Fetch the student data from the database
$query = "SELECT first_name, middle_name, last_name, profile_image, email, status, year_level, section FROM student WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($first_name, $middle_name, $last_name, $profile_image, $email, $status, $year_level, $section);
if (!$stmt->fetch()) {
    die('Student not found.');
}
$stmt->close();

// Fetch additional dynamic fields (form fields added by admin)
$formFields = [];
$stmt = $conn->prepare("SELECT id, field_name FROM form_fields");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $formFields[] = $row;
}
$stmt->close();

// Fetch values for these dynamic fields for the logged-in student
$additionalFields = [];
foreach ($formFields as $field) {
    $stmt = $conn->prepare("SELECT field_value FROM student_additional_fields WHERE student_id = ? AND field_name = ?");
    $stmt->bind_param("is", $id, $field['field_name']);
    $stmt->execute();
    $stmt->bind_result($field_value);
    if ($stmt->fetch()) {
        $additionalFields[$field['field_name']] = $field_value;
    } else {
        $additionalFields[$field['field_name']] = ''; // If no value exists, set as empty
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="./css/profile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    </lin>

</head>

<body>
    <div class="profile-container">
        <div class="sidebar">
            <?php if ($profile_image): ?>
                <img src="<?= htmlspecialchars('./' . $profile_image); ?>" alt="Profile Image" style="width:150px;height:150px;">
            <?php else: ?>
                <p>No profile image available.</p>
            <?php endif; ?>
            <h3> <?php echo htmlspecialchars($first_name . " " . $middle_name . " " . $last_name); ?></h3>
            <p><i class="bi bi-mortarboard-fill"></i>Student</p>
            <p>ID: <?= htmlspecialchars($id); ?></p>
            <button class="edit-profile-btn" onclick="openPopup()">Form</button>
        </div>

        <div class="main-content">
            <div class="header">
                <div class="logo"><img style="width: 45px;" src="./pictures/bsitlogo.png" alt=""></div>
                <a href="studenthomepage.php">
                    <div class="home-icon"><i class="bi bi-house-door-fill"></i></div>
                </a>
                <div class="profile-dropdown">
                    <div class="profile-icon">
                        <img src="<?= htmlspecialchars(string: './' . $profile_image); ?>" alt="Profile Image" class="profile-img">
                    </div>
                    <div class="dropdown-menu">
                        <a href="./studentProfileUpdate.php">Update Profile</a>
                        <a href="includes/logout.php">Log Out</a>
                    </div>
                </div>
            </div>


            <div class="info-section">
                <div class="info-box">
                    <h4>Information</h4>
                    <p>First Name: Nikki</p>
                    <p>Middle Name: Sixx</p>
                    <p>Last Name: Acosta</p>

                    <p>Section: <?php echo htmlspecialchars($section); ?></p>
                    <p>Year Leve: <?php echo htmlspecialchars($year_level); ?></p>
                    <p>Student Status: <?php echo htmlspecialchars($status); ?></p>
                    <h4>Credentials</h4>
                    <p>Email: <?php echo htmlspecialchars($email); ?></p>
                </div>

                <div class="info-box">
                    <h4>Other Information</h4>
                    <?php foreach ($formFields as $field): ?>
                        <label style="color: white;" for="<?php echo htmlspecialchars($field['field_name']); ?>">
                            <?php echo htmlspecialchars(str_replace('_', ' ', $field['field_name'])); ?>
                        </label>
                        <span style="color: white;" id="<?php echo htmlspecialchars($field['field_name']); ?>">
                            <?php echo htmlspecialchars($additionalFields[$field['field_name']]); ?><br>
                        </span>

                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="popup-overlay" id="popupOverlay">
        <div class="popup-content">
            <button class="close-popup" onclick="closePopup()">&times;</button>
            <h3>Form</h3>
            <form method="POST" action="update_student.php" class="fillup-form">
                <!-- Status -->
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Regular" <?php echo $status === 'Regular' ? 'selected' : ''; ?>>Regular</option>
                        <option value="Irregular" <?php echo $status === 'Irregular' ? 'selected' : ''; ?>>Irregular</option>
                    </select>
                </div>

                <!-- Year Level -->
                <div class="form-group">
                    <label for="year_level">Year Level</label>
                    <input type="text" id="year_level" name="year_level"
                        value="<?php echo htmlspecialchars($year_level); ?>" placeholder="e.g., first year">
                </div>
                <!-- Section -->
                <div class="form-group">
                    <label for="section">Section</label>
                    <input type="text" id="section" name="section"
                        value="<?php echo htmlspecialchars($section); ?>" placeholder="e.g., Section A">
                </div>

                <!-- Dynamic Fields (e.g., father's name, mother's name, etc.) -->
                <?php foreach ($formFields as $field): ?>
                    <div class="form-group">
                        <label for="<?php echo htmlspecialchars($field['field_name']); ?>">
                            <?php echo htmlspecialchars(str_replace('_', ' ', $field['field_name'])); ?>
                        </label>
                        <input type="text" id="<?php echo htmlspecialchars($field['field_name']); ?>"
                            name="<?php echo htmlspecialchars($field['field_name']); ?>"
                            value="<?php echo htmlspecialchars($additionalFields[$field['field_name']]); ?>">
                    </div>
                <?php endforeach; ?>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Form submission validation
        document.querySelector('form').addEventListener('submit', function(event) {
            const yearLevel = document.getElementById('year_level').value.trim().toLowerCase();

            // Valid year levels
            const validYearLevels = ['first year', 'second year', 'third year', 'fourth year'];

            if (!validYearLevels.includes(yearLevel)) {
                alert('Please enter a valid year level (e.g., first year, second year, third year, or fourth year).');
                event.preventDefault(); // Prevent form submission
            }
        });
    </script>
    <script>
        function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            const menu = document.getElementById('dropdownMenu');
            if (!event.target.closest('.profile-dropdown')) {
                menu.style.display = 'none';
            }
        };

        function openPopup() {
            document.getElementById('popupOverlay').style.display = 'flex';
        }

        function closePopup() {
            document.getElementById('popupOverlay').style.display = 'none';
        }
    </script>

    <div class="yourpost">
        <?php
        include './database/db.php';

        if (!isset($_SESSION['student_id'])) {
            header("Location: login.php");
            exit();
        }

        $student_id = $_SESSION['student_id'];

        // Fetch posts
        $sql = "SELECT posts.post_id, posts.content AS post_content, posts.media_path, posts.created_at AS post_date, 
        student.first_name, student.last_name,
        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.post_id) AS like_count
        FROM posts
        JOIN student ON posts.student_id = student.id
        WHERE posts.student_id = ?
        ORDER BY posts.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $posts_result = $stmt->get_result();

        // Handle new post creation
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_post'])) {
            $content = isset($_POST['content']) && trim($_POST['content']) !== '' ? trim($_POST['content']) : null;
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

            // Ensure content or media is provided
            if (empty($content) && empty($media_path)) {
                echo "You must provide text or media to create a post.";
                exit();
            }

            // Insert post into database
            $stmt = $conn->prepare("INSERT INTO posts (student_id, content, media_path) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $student_id, $content, $media_path);
            $stmt->execute();
            $stmt->close();

            header("Location: studenthomepage.php");
            exit();
        }
        ?>

        <style>

        </style>
        <script>
            function toggleComments(postId) {
                const commentsSection = document.getElementById(`comments-${postId}`);
                const button = document.getElementById(`toggle-btn-${postId}`);
                if (commentsSection.style.display === "none") {
                    commentsSection.style.display = "block";
                    button.textContent = "Hide Comments";
                } else {
                    commentsSection.style.display = "none";
                    button.textContent = "Show Comments";
                }
            }
        </script>


        <div class="container">
            <h1>My Posts</h1>

            <hr>
            <?php while ($post = $posts_result->fetch_assoc()): ?>
                <div class="post-container">
                    <form method="post" action="delete_post.php" style="margin-top: 10px;">
                        <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                        <button type="submit" name="delete_post" onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</button>
                    </form>

                    <div class="post-header">
                        <?php if ($profile_image): ?>
                            <img src="<?= htmlspecialchars('./' . $profile_image); ?>" alt="Profile Image" style="width:50px;height:50px; border-radius: 50%;">
                        <?php else: ?>
                            <p>No profile image available.</p>
                        <?php endif; ?>
                        <strong><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></strong>
                        <small>(<?php echo htmlspecialchars($post['post_date']); ?>)</small>
                    </div>
                    <div class="post-content">
                        <?php if (!empty($post['post_content'])): ?>
                            <p><?php echo htmlspecialchars($post['post_content']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($post['media_path'])): ?>
                            <div class="post-media">
                                <img src="<?php echo htmlspecialchars($post['media_path']); ?>" alt="Media">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="like.php?post_id=<?php echo $post['post_id']; ?>">Like</a>
                        (<?php echo $post['like_count']; ?>)
                    </div>
                    <button id="toggle-btn-<?php echo $post['post_id']; ?>" onclick="toggleComments(<?php echo $post['post_id']; ?>)">Show Comments</button>
                    <div id="comments-<?php echo $post['post_id']; ?>" class="comments-section" style="display: none;">
                        <form method="post">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                            <textarea name="comment_content" placeholder="Add a comment..." required></textarea><br>
                            <button type="submit" name="new_comment">Comment</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>




</body>

</html>