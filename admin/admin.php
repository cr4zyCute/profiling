<?php
include '../database/db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_student'])) {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    // Insert only the static fields into the database
    $stmt = $conn->prepare("INSERT INTO student (first_name, middle_name, last_name, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $first_name, $middle_name, $last_name, $email);

    if ($stmt->execute()) {
        echo "Student added successfully.<br>";

        // Display additional fields (Status, Year Level, Section) and any custom fields
        echo "<h3>Submitted Details:</h3>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($_POST['status']) . "</p>";
        echo "<p><strong>Year Level:</strong> " . htmlspecialchars($_POST['year_level']) . "</p>";
        echo "<p><strong>Section:</strong> " . htmlspecialchars($_POST['section']) . "</p>";

        // Process custom fields
        if (!empty($_POST['customFieldName']) && !empty($_POST['customFieldValue'])) {
            foreach ($_POST['customFieldName'] as $index => $fieldName) {
                $fieldValue = $_POST['customFieldValue'][$index];
                echo "<p><strong>" . htmlspecialchars($fieldName) . ":</strong> " . htmlspecialchars($fieldValue) . "</p>";
            }
        }
    } else {
        echo "Error adding student: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all students
$query = "SELECT * FROM student";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        // Add custom fields dynamically
        function addField() {
            const container = document.getElementById('customFields');
            const fieldDiv = document.createElement('div');
            fieldDiv.innerHTML = `
                <input type="text" name="customFieldName[]" placeholder="Field Name" required>
                <input type="text" name="customFieldValue[]" placeholder="Field Value" required>
                <button type="button" onclick="this.parentNode.remove()">Remove</button>
            `;
            container.appendChild(fieldDiv);
        }
    </script>
</head>

<body>
    <h1>Admin Panel</h1>
    <h2>Registered Students</h2>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Last Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= htmlspecialchars($row['first_name']); ?></td>
                    <td><?= htmlspecialchars($row['middle_name']); ?></td>
                    <td><?= htmlspecialchars($row['last_name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<h2>Form</h2>
    <form method="POST">
        <label>First Name:</label><br>
        <input type="text" name="first_name" required><br>

        <label>Middle Name:</label><br>
        <input type="text" name="middle_name"><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" required><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br>

        <label>Status:</label><br>
        <select name="status" required>
            <option value="Regular">Regular</option>
            <option value="Irregular">Irregular</option>
        </select><br>

        <label>Year Level:</label><br>
        <input type="text" name="year_level" placeholder="e.g., First Year, Second Year" required><br>

        <label>Section:</label><br>
        <input type="text" name="section" placeholder="e.g., A, B, C" required><br>

        <h3>Additional Fields</h3>
        <div id="customFields"></div>
        <button type="button" onclick="addField()">Add Field</button><br><br>

        <button type="submit" name="submit_student">Submit</button>
    </form>
</body>

</html>