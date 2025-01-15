<?php
include '../database/db.php';

// Handle form submission for adding announcements
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_announcement'])) {
    $announcement_text = trim($_POST['announcement_text']);

    if (!empty($announcement_text)) {
        $stmt = $conn->prepare("INSERT INTO announcements (content) VALUES (?)");
        $stmt->bind_param("s", $announcement_text);

        if ($stmt->execute()) {
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit(); // Ensure no further code is executed
        } else {
            echo "<p style='color: red;'>Error posting announcement: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        echo "<p style='color: red;'>Announcement text is required.</p>";
    }
}

// Handle announcement deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_announcement'])) {
    $announcement_id = intval($_POST['announcement_id']);

    if ($announcement_id > 0) {
        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->bind_param("i", $announcement_id);

        if ($stmt->execute()) {
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit(); // Ensure no further code is executed
        } else {
            echo "<p style='color: red;'>Error deleting announcement: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Announcements</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Sidebar Styles */


        /* Main Content */
        .main-content {
            margin-left: 270px;
            padding: 30px;
            background-color: #fff;
            min-height: 100vh;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 20px;
        }

        .main-content h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #224abe;
        }

        /* Form Styles */
        form {
            background: #f7f9fc;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }

        form label {
            font-size: 14px;
            font-weight: bold;
            color: #555;
        }

        form textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top: 8px;
            font-size: 14px;
            color: #333;
            resize: none;
        }

        form button {
            background: #224abe;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        form button:hover {
            background: #4e73df;
        }

        /* Announcement Display */
        .announcement {
            max-width: 600px;
            margin: 20px auto;
            background: #f7f7f7;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #224abe;
        }

        .announcement p {
            font-size: 16px;
            line-height: 1.5;
            margin: 0;
            color: #333;
        }

        .announcement small {
            color: #666;
            display: block;
            margin-top: 10px;
            font-size: 12px;
        }

        /* Buttons */
        .button-delete {
            display: inline-block;
            background: #e74a3b;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .button-delete:hover {
            background: #c0392b;
        }

        .back {
            position: absolute;
            top: 20px;
            /* Distance from the top of the page */
            left: 20px;
            /* Distance from the left of the page */
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .back:hover {
            background: linear-gradient(135deg, #5c0bb5, #1e60cf);
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(34, 74, 190, 0.4);
        }

        a {
            text-decoration: none;
            /* Remove underline from the link */
        }
    </style>
</head>

<body>

    <a href="admin.php">
        <button class="back">Back</button>
    </a>

    <center>
        <h1>Make an Announcement</h1>
    </center>

    <form method="post">
        <label for="announcement_text">Announcement Text:</label>
        <textarea name="announcement_text" id="announcement_text" rows="4" required></textarea><br>
        <button type="submit" name="submit_announcement">Post Announcement</button>
    </form>
    <center>
        <h2>All Announcements</h2>
    </center>
    <?php
    // Fetch all announcements
    $sql = "SELECT id, content, created_at FROM announcements ORDER BY created_at DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($announcement = $result->fetch_assoc()) {
            echo "<div class='announcement'>";
            echo "<p>" . htmlspecialchars($announcement['content']) . "</p>";
            echo "<small>Posted on: " . htmlspecialchars($announcement['created_at']) . "</small>";

            // Delete form
            echo "<form method='post' class='delete-form'>";
            echo "<input type='hidden' name='announcement_id' value='" . htmlspecialchars($announcement['id']) . "'>";
            echo "<button type='submit' name='delete_announcement'>Delete</button>";
            echo "</form>";

            echo "</div>";
        }
    } else {
        echo "<p>No announcements available.</p>";
    }
    ?>
    </div>
</body>

</html>