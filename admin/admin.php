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
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="#" onclick="loadSection('dashboard')">Dashboard</a>
        <a href="#" onclick="loadSection('studentList')">Student List</a>
        <a href="#" onclick="loadSection('report')">Generate Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <?php
    $firstYearQuery = "SELECT COUNT(*) AS first_year FROM student WHERE year_level = '1st year' OR year_level = 'First Year'";
    $secondYearQuery = "SELECT COUNT(*) AS second_year FROM student WHERE year_level = '2nd year' OR year_level = 'Second Year'";
    $thirdYearQuery = "SELECT COUNT(*) AS third_year FROM student WHERE year_level = '3rd year' OR year_level = 'Third Year'";
    $fourthYearQuery = "SELECT COUNT(*) AS fourth_year FROM student WHERE year_level = '4th year' OR year_level = 'Fourth Year'";

    $totalStudentsQuery = "SELECT COUNT(*) AS total_students FROM student";
    $firstYearResult = mysqli_query($conn, $firstYearQuery);
    $secondYearResult = mysqli_query($conn, $secondYearQuery);
    $thirdYearResult = mysqli_query($conn, $thirdYearQuery);
    $fourthYearResult = mysqli_query($conn, $fourthYearQuery);
    $totalStudentsResult = mysqli_query($conn, $totalStudentsQuery);

    $firstYearCount = mysqli_fetch_assoc($firstYearResult)['first_year'];
    $secondYearCount = mysqli_fetch_assoc($secondYearResult)['second_year'];
    $thirdYearCount = mysqli_fetch_assoc($thirdYearResult)['third_year'];
    $fourthYearCount = mysqli_fetch_assoc($fourthYearResult)['fourth_year'];

    $totalStudents = mysqli_fetch_assoc($totalStudentsResult)['total_students'];
    ?>
    <div class="main-content">
        <div id="dashboardSection" class="section">
            <h1>Welcome to the Dashboard</h1>
            <p>Use the sidebar to navigate through the sections.</p>

            <div class="boxes">
                <div class="box">
                    <h3>Total Students</h3>
                    <p><?= $totalStudents; ?></p>
                </div>
                <div class="box">
                    <h3>1st Year Students</h3>
                    <p><?= $firstYearCount; ?></p>
                </div>
                <div class="box">
                    <h3>2nd Year Students</h3>
                    <p><?= $secondYearCount; ?></p>
                </div>
                <div class="box">
                    <h3>3rd Year Students</h3>
                    <p><?= $thirdYearCount; ?></p>
                </div>
                <div class="box">
                    <h3>4th Year Students</h3>
                    <p><?= $fourthYearCount; ?></p>
                </div>
            </div>
        </div>

        <div id="studentListSection" class="section" style="display:none;">
            <h2>Registered Students</h2>
            <a href="../studentRegistration.php">
                <button>Add a Student</button>
            </a>

            <!-- Live Search Input -->
            <input type="text" id="searchInput" placeholder="Search by first name, middle name, last name, or email" onkeyup="liveSearch()" />

            <table id="studentTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Profile Image</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr onclick="redirectToUpdate(<?= $row['id']; ?>)">
                            <td><?= $row['id']; ?></td>
                            <td>
                                <?php if (!empty($row['profile_image'])): ?>
                                    <img src="<?= htmlspecialchars('../' . $row['profile_image']); ?>" alt="Profile Image" width="50" height="50">
                                <?php else: ?>
                                    <img src="default-profile.png" alt="Default Profile" width="50" height="50">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['first_name']); ?></td>
                            <td><?= htmlspecialchars($row['middle_name']); ?></td>
                            <td><?= htmlspecialchars($row['last_name']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div id="reportSection" class="section" style="display:none;">
            <h2>Student Report</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Profile Image</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Year Level</th>
                        <th>Section</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $studentQuery = "SELECT * FROM student";
                    $studentResult = mysqli_query($conn, $studentQuery);

                    if ($studentResult && mysqli_num_rows($studentResult) > 0) {
                        while ($row = mysqli_fetch_assoc($studentResult)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>";
                            if (!empty($row['profile_image'])) {
                                echo "<img src='../" . htmlspecialchars($row['profile_image']) . "' alt='Profile Image' width='50' height='50'>";
                            } else {
                                echo "<img src='default-profile.png' alt='Default Profile' width='50' height='50'>";
                            }
                            echo "</td>";
                            echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['middle_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['year_level']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['section']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>No data found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function liveSearch() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#studentTable tbody tr');
            rows.forEach(row => {
                const cells = row.getElementsByTagName('td');
                const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' ');
                row.style.display = rowText.includes(query) ? '' : 'none';
            });
        }

        function loadSection(section) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(sec => (sec.style.display = 'none'));
            document.getElementById(section + 'Section').style.display = 'block';
        }

        function redirectToUpdate(studentId) {
            window.location.href = `studentUpdateForm.php?id=${studentId}`;
        }
    </script>
</body>

</html>