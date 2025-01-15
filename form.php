<?php
include './database/db.php';

// Function to render the student form fields
function renderStudentFormFields($status = '', $year_level = '', $section = '')
{
?>
    <label for="status">Status</label>
    <select id="status" name="status" required>
        <option value="Regular" <?php echo $status === 'Regular' ? 'selected' : ''; ?>>Regular</option>
        <option value="Irregular" <?php echo $status === 'Irregular' ? 'selected' : ''; ?>>Irregular</option>
    </select>

    <label for="year_level">Year Level</label>
    <input type="text" id="year_level" name="year_level" value="<?php echo htmlspecialchars($year_level); ?>" placeholder="e.g., 1st Year">

    <label for="section">Section</label>
    <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($section); ?>" placeholder="e.g., Section A">
<?php
}

// Function to render and manage form fields
function renderFormFields($conn)
{
    // Fetch fields from the database
    $sql = "SELECT id, field_name FROM form_fields";
    $result = $conn->query($sql);
    $fields = $result->fetch_all(MYSQLI_ASSOC);
?>
    <h3>Manage Form Fields</h3>
    <div id="fieldList">
        <?php foreach ($fields as $field) { ?>
            <div id="field-<?php echo $field['id']; ?>">
                <form method="POST" action="update_field.php">
                    <input type="hidden" name="id" value="<?php echo $field['id']; ?>">
                    <input type="text" name="field_name" value="<?php echo htmlspecialchars($field['field_name']); ?>">
                    <button type="submit">Update</button>
                    <button type="button" onclick="deleteField(<?php echo $field['id']; ?>)">Delete</button>
                </form>
            </div>
        <?php } ?>
    </div>

    <h3>Add New Field</h3>
    <form method="POST" action="add_field.php">
        <input type="text" name="field_name" placeholder="Field Name" required>
        <button type="submit">Add Field</button>
    </form>

    <script>
        function deleteField(fieldId) {
            if (confirm('Are you sure you want to delete this field?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete_field.php';
                form.innerHTML = `<input type="hidden" name="id" value="${fieldId}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
<?php
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information Form</title>
</head>

<body>
    <form method="POST" action="save_student.php">
        <h2>Student Information</h2>
        <?php renderStudentFormFields(); // Render the basic student form fields 
        ?>
        <button type="submit">Submit</button>
    </form>

    <?php renderFormFields($conn); // Render the field management section 
    ?>
</body>

</html>