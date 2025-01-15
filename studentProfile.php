<?php
session_start();
include './database/db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch the user details
$stmt = $conn->prepare("SELECT first_name, middle_name, last_name, profile_image, email, status, year_level, section FROM student WHERE id = ?");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$stmt->bind_result($first_name, $middle_name, $last_name, $profile_image, $email, $status, $year_level, $section);
$stmt->fetch();
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
    $stmt->bind_param("is", $_SESSION['student_id'], $field['field_name']);
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
    <title>Edit Profile</title>
</head>

<body>
    <h2>Welcome, <?php echo htmlspecialchars($first_name . " " . $middle_name . " " . $last_name); ?></h2>

    <?php if ($profile_image): ?>
        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" style="width:150px;height:150px;"><br>
    <?php else: ?>
        <p>No profile image available.</p>
    <?php endif; ?>

    <h3>Edit Profile</h3>
    <form method="POST" action="update_student.php">
        <!-- Static fields -->
        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="Regular" <?php echo $status === 'Regular' ? 'selected' : ''; ?>>Regular</option>
            <option value="Irregular" <?php echo $status === 'Irregular' ? 'selected' : ''; ?>>Irregular</option>
        </select>

        <label for="year_level">Year Level</label>
        <input type="text" id="year_level" name="year_level" value="<?php echo htmlspecialchars($year_level); ?>" placeholder="e.g., 1st Year">

        <label for="section">Section</label>
        <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($section); ?>" placeholder="e.g., Section A">

        <!-- Dynamic Fields (Added by Admin in form.php) -->
        <?php foreach ($formFields as $field): ?>
            <label for="<?php echo htmlspecialchars($field['field_name']); ?>">
                <?php echo htmlspecialchars(str_replace('_', ' ', $field['field_name'])); ?>
            </label>
            <input type="text" id="<?php echo htmlspecialchars($field['field_name']); ?>"
                name="<?php echo htmlspecialchars($field['field_name']); ?>"
                value="<?php echo htmlspecialchars($additionalFields[$field['field_name']]); ?>">
        <?php endforeach; ?>


        <button type="submit">Update</button>
    </form>

    <a href="./includes/logout.php">Logout</a>
    <a href="studenthomepage.php">Home</a>
</body>

</html>