<?php
session_start();
require 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } elseif (!preg_match("/^[A-Za-z]+$/", $username)) {
        $error = "Username must contain only alphabets (A–Z or a–z).";
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $login_time = date('Y-m-d H:i:s');
            $insert = $conn->prepare("INSERT INTO sessions (user_id, login_time) VALUES (?, ?)");
            $insert->bind_param("is", $user['id'], $login_time);
            $insert->execute();
            $_SESSION['session_id'] = $conn->insert_id;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Internet Cafe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('register.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-wrapper {
            width: 400px;
            padding: 40px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            margin-top: 68px;
        }

        h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .logo {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }

        .logo img {
            height: 60px;
        }

        .input-group {
            position: relative;
            margin-bottom: 15px;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: #999;
        }

        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        .input-group input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .error-message {
            color: red;
            font-size: 13px;
            margin: 4px 0 12px 5px;
            text-align: left;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .button-group button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
        }

        #loginBtn {
            background: #2563eb;
            color: white;
        }

        #loginBtn:hover {
            background: #1e40af;
        }

        .register-btn {
            background: #10b981;
            color: white;
        }

        .register-btn:hover {
            background: #059669;
        }

        header {
            width: 100%;
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        header .logo-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        header .logo-header img {
            width: 40px;
            height: 40px;
        }

        header .logo-header h1 {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }

        header nav a {
            margin-left: 12px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            background: #2563eb;
            padding: 8px 12px;
            border-radius: 6px;
        }

        header nav a:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>
<header>
    <div class="logo-header">
        <img src="https://cdn-icons-png.flaticon.com/512/888/888879.png" alt="System Logo">
        <h1>Internet Cafe Shop</h1>
    </div>
    <nav>
        <a href="landing.html" title="Home"><i class="fa fa-home"></i> Home</a>
    </nav>
</header>
<div class="main-wrapper">
    <div class="logo">
        <img src="https://cdn-icons-png.flaticon.com/512/888/888879.png" alt="Logo">
    </div>
    <h2>Login to Internet Cafe</h2>

    <?php if (!empty($error)) echo "<div class='error-message'>" . htmlspecialchars($error) . "</div>"; ?>

    <form method="POST" action="">
        <div class="input-group">
            <i class="fa fa-user"></i>
            <input type="text" id="username" name="username" placeholder="Username" required>
        </div>
        <div id="usernameError" class="error-message"></div>

        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="Password" required>
        </div>
        <div id="passwordError" class="error-message"></div>

        <div class="button-group">
            <button type="submit" id="loginBtn">Login</button>
            <a href="register.php"><button type="button" class="register-btn">Register</button></a>
        </div>
    </form>
</div>

<script>
    const usernameInput = document.getElementById("username");
    const passwordInput = document.getElementById("password");
    const loginBtn = document.getElementById("loginBtn");

    const usernameError = document.getElementById("usernameError");
    const passwordError = document.getElementById("passwordError");

    function validateInputs() {
        const username = usernameInput.value.trim();
        const password = passwordInput.value;
        let isValid = true;

        if (!/^[A-Za-z]+$/.test(username)) {
            usernameError.textContent = "Only alphabets are allowed in username.";
            isValid = false;
        } else {
            usernameError.textContent = "";
        }

        if (password.length < 8) {
            passwordError.textContent = "Password must be at least 8 characters.";
            isValid = false;
        } else {
            passwordError.textContent = "";
        }

        loginBtn.disabled = !isValid;
    }

    usernameInput.addEventListener("input", validateInputs);
    passwordInput.addEventListener("input", validateInputs);
</script>
</body>
</html>
