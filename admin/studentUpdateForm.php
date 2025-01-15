<?php
include '../database/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM student WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $student = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    $query = "UPDATE student SET first_name = '$first_name', middle_name = '$middle_name', last_name = '$last_name', email = '$email' WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo "Student updated successfully.";
        header("Location: student.php");
    } else {
        echo "Error updating student: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
</head>

<body>
    <form method="post">
        <h2>Edit Student</h2>
        <input type="hidden" name="id" value="<?= $student['id']; ?>">
        <label>First Name:</label><br>
        <input type="text" name="first_name" value="<?= $student['first_name']; ?>" required><br>
        <label>Middle Name:</label><br>
        <input type="text" name="middle_name" value="<?= $student['middle_name']; ?>"><br>
        <label>Last Name:</label><br>
        <input type="text" name="last_name" value="<?= $student['last_name']; ?>" required><br>
        <label>Email:</label><br>
        <input type="email" name="email" value="<?= $student['email']; ?>" required><br>
        <button type="submit">Update</button>
    </form>
</body>

</html><?php
        include '../database/db.php';

        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $query = "SELECT * FROM student WHERE id = $id";
            $result = mysqli_query($conn, $query);
            $student = mysqli_fetch_assoc($result);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $first_name = $_POST['first_name'];
            $middle_name = $_POST['middle_name'];
            $last_name = $_POST['last_name'];
            $email = $_POST['email'];

            $query = "UPDATE student SET first_name = '$first_name', middle_name = '$middle_name', last_name = '$last_name', email = '$email' WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                echo "Student updated successfully.";
                header("Location: student.php");
            } else {
                echo "Error updating student: " . mysqli_error($conn);
            }
        }
        ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
</head>

<body>
    <form method="post">
        <h2>Edit Student</h2>
        <input type="hidden" name="id" value="<?= $student['id']; ?>">
        <label>First Name:</label><br>
        <input type="text" name="first_name" value="<?= $student['first_name']; ?>" required><br>
        <label>Middle Name:</label><br>
        <input type="text" name="middle_name" value="<?= $student['middle_name']; ?>"><br>
        <label>Last Name:</label><br>
        <input type="text" name="last_name" value="<?= $student['last_name']; ?>" required><br>
        <label>Email:</label><br>
        <input type="email" name="email" value="<?= $student['email']; ?>" required><br>
        <button type="submit">Update</button>
    </form>
</body>

</html>