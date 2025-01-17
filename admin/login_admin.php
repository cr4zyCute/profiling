<?php
session_start();
include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        die('Username and password are required.');
    }

    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $stored_password);
        $stmt->fetch();

        if ($password === $stored_password) {
            // Set session variable for admin
            $_SESSION['admin_id'] = $id;
            header('Location: admin.php');
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "Admin not found.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            /* user-select: none; */
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        html,
        body {
            height: 100%;
        }

        body {
            display: grid;
            place-items: center;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4, #fbc2eb, #a18cd1);
            background-size: 400% 400%;
            animation: gradientAnimation 10s ease infinite;
            text-align: center;
            margin: 0;
            height: 100vh;
        }

        @keyframes gradientAnimation {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .content {
            width: 500px;
            padding: 40px 30px;
            background: #dde1e7;
            border-radius: 10px;
            box-shadow: -3px -3px 7px #ffffff73,
                2px 2px 5px rgba(94, 104, 121, 0.288);
        }

        .content .text {
            font-size: 33px;
            font-weight: 600;
            margin-bottom: 35px;
            color: #595959;
        }

        .field {
            height: 50px;
            width: 100%;
            display: flex;
            position: relative;
        }

        .field:nth-child(2) {
            margin-top: 20px;
        }

        .field input {
            height: 100%;
            width: 100%;
            padding-left: 45px;
            outline: none;
            border: none;
            font-size: 18px;
            background: #dde1e7;
            color: #595959;
            border-radius: 25px;
            box-shadow: inset 2px 2px 5px #BABECC,
                inset -5px -5px 10px #ffffff73;
        }

        .field input:focus {
            box-shadow: inset 1px 1px 2px #BABECC,
                inset -1px -1px 2px #ffffff73;
        }

        .field span {
            position: absolute;
            color: #595959;
            width: 50px;
            line-height: 50px;
        }

        .field label {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 45px;
            pointer-events: none;
            color: #666666;
        }

        .field input:valid~label {
            opacity: 0;
        }

        .forgot-pass {
            text-align: left;
            margin: 10px 0 10px 5px;
        }

        .forgot-pass a {
            font-size: 16px;
            color: #3498db;
            text-decoration: none;
        }

        .forgot-pass:hover a {
            text-decoration: underline;
        }

        button {
            margin: 15px 0;
            width: 100%;
            height: 50px;
            font-size: 18px;
            line-height: 50px;
            font-weight: 600;
            background: #dde1e7;
            border-radius: 25px;
            border: none;
            outline: none;
            cursor: pointer;
            color: #595959;
            box-shadow: 2px 2px 5px #BABECC,
                -5px -5px 10px #ffffff73;
        }

        button:focus {
            color: #3498db;
            box-shadow: inset 2px 2px 5px #BABECC,
                inset -5px -5px 10px #ffffff73;
        }

        .sign-up {
            margin: 10px 0;
            color: #595959;
            font-size: 16px;
        }

        .sign-up a {
            color: #3498db;
            text-decoration: none;
        }

        .sign-up a:hover {
            text-decoration: underline;
        }
    </style>
    </style>
    <title>Admin Login</title>
</head>

<body>
    <div class="content">
        <div class="text">Welcome Admin</div>
        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="field">
                <span class="fas fa-user"></span>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="field">
                <span class="fas fa-lock"></span>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="forgot-pass">
                <a href="#">Forgot Password?</a>
            </div>
            <button type="submit" name="login">Log in</button>
        </form>
        <div class="sign-up">
            <p>Don't have an account? <a href="#">Sign up</a></p>
        </div>

    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.display = 'none';
            }, 3000);
        }
    });
</script>

</html>