<?php
include './database/db.php';

// Function to render the student form fields
function renderStudentFormFields($status = '', $year_level = '', $section = '')
{
?>
    <div class="form-row">
        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="Regular" <?php echo $status === 'Regular' ? 'selected' : ''; ?>>Regular</option>
            <option value="Irregular" <?php echo $status === 'Irregular' ? 'selected' : ''; ?>>Irregular</option>
        </select>
    </div>
    <div class="form-row">
        <label for="year_level">Year Level</label>
        <input type="text" id="year_level" name="year_level" value="<?php echo htmlspecialchars($year_level); ?>" placeholder="e.g., 1st Year">
    </div>
    <div class="form-row">
        <label for="section">Section</label>
        <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($section); ?>" placeholder="e.g., Section A">
    </div>
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
    <div class="field-management">
        <h3>Manage Form Fields</h3>
        <h3>Add New Field</h3>
        <form method="POST" action="add_field.php" class="add-field-form">
            <input type="text" name="field_name" placeholder="Field Name" required>
            <button style="background-color: #007bff;" type="submit">Add Field</button>
        </form>
        <div id="fieldList">
            <?php foreach ($fields as $field) { ?>
                <div class="field-item" id="field-<?php echo $field['id']; ?>">
                    <form method="POST" action="update_field.php">
                        <input type="hidden" name="id" value="<?php echo $field['id']; ?>">
                        <input type="text" name="field_name" value="<?php echo htmlspecialchars($field['field_name']); ?>">
                        <button class="update" type="submit">Update</button>
                        <button class="delete" type="button" onclick="deleteField(<?php echo $field['id']; ?>)">Delete</button>
                    </form>
                </div>
            <?php } ?>
        </div>


    </div>

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
    <link rel="stylesheet" href="style.css">
</head>
<style>
    /* General Layout */
    body {
        font-family: Arial, sans-serif;

        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .container {
        width: 100%;
        max-width: 1000px;



    }

    /* Header Section */
    .form-header {

        color: white;
        font-size: 20px;
        padding: 15px;
        text-align: center;
        position: relative;
    }

    .add-button {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background-color: #333;
        color: white;
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 50%;
        font-size: 20px;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .add-button:hover {
        background-color: #555;
    }

    /* Form Fields */
    .student-form .form-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .student-form .form-row label {
        flex: 1;
        font-size: 14px;
        font-weight: bold;
        margin-right: 10px;
        text-align: center;
    }

    .student-form .form-row input,
    .student-form .form-row select {
        flex: 2;
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    /* Divider */
    .divider {
        margin: 20px 0;
        border-top: 1px solid #ccc;
    }

    /* Fields Layout */
    .field-management #fieldList {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: space-between;
    }

    .field-management .field-item {
        width: calc(33.333% - 10px);
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #ececec;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .field-management .field-item input {
        width: 90%;
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .field-management button {
        padding: 5px 10px;
        font-size: 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin: 5px;
        color: white;
    }

    .field-management button.update {
        background-color: #007bff;
    }

    .field-management button.delete {
        background-color: #dc3545;
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

<body>

    <a href="././admin/admin.php">
        <button class="back">Back</button>
    </a>
    <div class="container">
        <div class="form-header">
            Form

        </div>
        <div class="form-content">
            <form method="POST" action="save_student.php" class="student-form">
                <h3>Fill Up</h3>
                <?php renderStudentFormFields(); ?>
                <div class="form-submit">
                    <button type="submit">Submit</button>
                </div>
            </form>
            <div class="divider"></div>
            <?php renderFormFields($conn); ?>
        </div>
    </div>
</body>

</html>