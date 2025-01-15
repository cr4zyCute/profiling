<?php
include '../database/db.php';

// Fetch student data
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "SELECT * FROM student WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $student = mysqli_fetch_assoc($result);
    if (!$student) {
        die('Student not found.');
    }
}

// Handle student update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $imagePath = '../uploads/' . basename($_FILES['profile_image']['name']);

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Ensure the directory exists
        }
        $imagePath = 'uploads/' . basename($_FILES['profile_image']['name']);
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], '../' . $imagePath)) {
            echo "File uploaded successfully: " . $imagePath;
        } else {
            echo "File upload failed.";
            exit;
        }
    } else {
        echo "No new file uploaded. Using existing image: " . $student['profile_image'];
        $imagePath = $student['profile_image']; // Retain the existing image
    }

    // Update student record
    $stmt = $conn->prepare("UPDATE student SET first_name = ?, middle_name = ?, last_name = ?, email = ?, profile_image = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $first_name, $middle_name, $last_name, $email, $imagePath, $id);

    if ($stmt->execute()) {
        echo "Profile updated successfully. Image path: " . $imagePath;
        exit;
    } else {
        echo "Error updating student: " . $stmt->error;
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <style>
        /* Add styling as needed */
    </style>
</head>

<body>

    <form method="post" enctype="multipart/form-data">
        <h2>Edit Student</h2>
        <input type="hidden" name="id" value="<?= $student['id']; ?>">
        <label>First Name:</label><br>
        <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name']); ?>" required><br>
        <label>Middle Name:</label><br>
        <input type="text" name="middle_name" value="<?= htmlspecialchars($student['middle_name']); ?>"><br>
        <label>Last Name:</label><br>
        <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name']); ?>" required><br>
        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($student['email']); ?>" required><br>

        <!-- Profile Image Upload -->
        <label>Profile Image:</label><br>
        <?php if (!empty($student['profile_image'])): ?>
            <img src="<?= htmlspecialchars('../' . $student['profile_image']); ?>" alt="Profile Image" style="width:150px;height:150px;">
        <?php endif; ?>
        <input type="file" name="profile_image" accept="image/*"><br>

        <button type="submit">Update</button>
    </form>

    <!-- Print Button -->
    <button onclick="window.print()">Print Profile</button>

    <!-- Delete Button (Redirect to deleteStudent.php) -->
    <a href="deleteStudent.php?id=<?= $student['id']; ?>" onclick="return confirm('Are you sure you want to delete this student?')">
        <button type="button">Delete Student</button>
    </a>

</body>

</html>