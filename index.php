<?php
session_start();
include './database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM student WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $stored_password);
        $stmt->fetch();

        // Check if the password matches (without hashing)
        if ($password === $stored_password) {
            $_SESSION['student_id'] = $id;
            header("Location: student.php");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No student found with this email.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./css/index.css">
</head>


<body>

    <div class="wrapper">
        <nav class="nav">
            <div class="nav-logo">
                <img src="./pictures/bsitlogo.png" alt="Logo" class="logo-img">
                <p>BSIT</p>
            </div>
            <div class="nav-menu" id="navMenu">
                <ul>
                    <li><a href="#" class="link active">Home</a></li>
                    <li><a href="#about" class="link">About</a></li>

                </ul>
            </div>
            <div class="nav-button">
                <a href=" StudentRegistration.php">
                    <button class="btn" id="registerBtn">Register</button>
                </a>
                <!-- <button class="btn" id="registerBtn" onclick="register()">Sign Up</button> -->
            </div>
            <div class="nav-menu-btn">
                <i class="bx bx-menu" onclick="myMenuFunction()"></i>
            </div>
        </nav>
        <form action="" method="post">
            <div class="form-box">
                <div class="login-container" id="login">
                    <div class="top">
                        <!-- <span>Don't have an account? <a href="studentRegistrationForm.php" onclick="register()">Sign Up</a></span> -->
                        <header class="logo">
                            <img src="./pictures/bsitlogo.png" alt="bsit_logo">
                        </header>
                    </div>
                    <?php if (!empty($error_message)) : ?>
                        <p id="errorMessage" style="color: red;"><?= $error_message; ?></p>
                    <?php endif; ?>

                    <div class="input-box">
                        <input type="email" class="input-field" name="email" placeholder="Enter your email" required>
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                    <div class="input-box">
                        <input type="password" class="input-field" name="password" placeholder="Enter your Password" required>
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div class="input-box">
                        <button type="submit" name="login" class="submit" class="button">Login Now</button>
                    </div>
                </div>
        </form>

    </div>

    <section class="cards" id="about">
        <div class="card">
            <img src="/college_bsit.jpg" alt="Card Image">
            <h3>Service 1</h3>
            <p>Learn about our amazing service that helps you achieve your goals.</p>
        </div>
        <div class="card">
            <img src="./images/itblackman.jpg" alt="Card Image">
            <h3>Service 2</h3>
            <p>Discover how we can make a difference in your daily life.</p>
        </div>
        <div class="card">
            <img src="./images/nega.jpg" alt="Card Image">
            <h3>Service 3</h3>
            <p>Experience the best support and quality in the industry.</p>
        </div>
    </section>

    <footer class="simple-footer">
        <p>&copy; 2025 Your Website | <a href="#">Privacy Policy</a> | <a href="#">Terms of Use</a></p>
        <div class="footer-social">

        </div>
    </footer>

    <script>
        const popup = document.getElementById('loginPopup');
        const overlay = document.getElementById('popupOverlay');
        const closeBtn = document.querySelector('.close'); // Select the close button

        function openPopup() {
            popup.classList.add('active');
            overlay.classList.add('active');
        }

        function closePopup() {
            popup.classList.remove('active');
            overlay.classList.remove('active');
        }

        // Keep popup open if there's an error message
        <?php if ($show_popup): ?>
            openPopup();
        <?php endif; ?>

        // Add an event listener to the close button to close the popup
        closeBtn.addEventListener('click', closePopup);

        overlay.addEventListener('click', closePopup); // Also close popup when clicking on the overlay
        document.addEventListener('DOMContentLoaded', reveal);

        document.addEventListener("DOMContentLoaded", function() {
            const errorMessage = document.getElementById("error-message");

            if (errorMessage) {
                // Set a timeout to hide the error message after 5 seconds
                setTimeout(() => {
                    errorMessage.style.display = "none";
                }, 3000);
            }
        });
    </script>

</body>

</html>