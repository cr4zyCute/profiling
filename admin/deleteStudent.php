<?php
include '../database/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM student WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        echo "Student deleted successfully.";
        header("Location: student.php");
    } else {
        echo "Error deleting student: " . mysqli_error($conn);
    }
}
