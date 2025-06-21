<?php
require 'db_connect.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    } elseif (!preg_match("/^[A-Za-z]+$/", $username)) {
        $message = "<div class='alert alert-danger'>Username must contain only alphabets.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@gmail.com')) {
        $message = "<div class='alert alert-danger'>Only valid @gmail.com addresses are allowed.</div>";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "<div class='alert alert-danger'>Email already registered.</div>";
        } else {
            $insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $username, $email, $hashedPassword);

            if ($insert->execute()) {
                $message = "<div class='alert alert-success'>Registration successful. <a href='login.php'>Login here</a>.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Registration failed. Please try again later.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Internet Cafe Shop</title>
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
        .register-container {
            width: 400px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .input-group {
            margin-bottom: 15px;
            position: relative;
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
        .alert {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .alert-danger {
            background: #fee2e2;
            color: #b91c1c;
        }
        .alert-success {
            background: #dcfce7;
            color: #15803d;
        }
        .error-msg {
            color: #dc2626;
            font-size: 13px;
            margin-top: 4px;
            padding-left: 40px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
        }
        button:disabled {
            background: #a5b4fc;
        }
        .register-footer {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }
        .register-footer a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="register-container">
    <h2>Create Your Account</h2>
    <?php echo $message; ?>
    <form method="POST" action="" onsubmit="return validateForm()">
        <div class="input-group">
            <i class="fa fa-user"></i>
            <input type="text" id="username" name="username" placeholder="Username" required>
            <div class="error-msg" id="usernameError"></div>
        </div>
        <div class="input-group">
            <i class="fa fa-envelope"></i>
            <input type="email" id="email" name="email" placeholder="Email (must be @gmail.com)" required>
            <div class="error-msg" id="emailError"></div>
        </div>
        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <div class="error-msg" id="passwordError"></div>
        </div>
        <button type="submit" id="registerBtn" disabled>Register</button>
    </form>
    <div class="register-footer">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

<script>
    const usernameInput = document.getElementById("username");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const registerBtn = document.getElementById("registerBtn");

    const usernameError = document.getElementById("usernameError");
    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");

    function validateForm() {
        const username = usernameInput.value.trim();
        const email = emailInput.value.trim();
        const password = passwordInput.value;

        let isValid = true;

        // Username validation
        if (!/^[A-Za-z]+$/.test(username)) {
            usernameError.textContent = "Username must contain only letters.";
            isValid = false;
        } else {
            usernameError.textContent = "";
        }

        // Email validation
        if (!/^[^\s@]+@gmail\.com$/.test(email)) {
            emailError.textContent = "Only valid @gmail.com addresses allowed.";
            isValid = false;
        } else {
            emailError.textContent = "";
        }

        // Password validation
        if (
            password.length < 8 || password.length > 10 ||
            !/[A-Z]/.test(password) ||
            !/[a-z]/.test(password) ||
            !/[0-9]/.test(password) ||
            !/[!@#$%^&*(),.?":{}|<>]/.test(password)
        ) {
            passwordError.textContent = "Password must be 8–10 characters long, include uppercase, lowercase, number, and special character.";
            isValid = false;
        } else {
            passwordError.textContent = "";
        }

        registerBtn.disabled = !isValid;
        return isValid;
    }

    usernameInput.addEventListener("input", validateForm);
    emailInput.addEventListener("input", validateForm);
    passwordInput.addEventListener("input", validateForm);
</script>
</body>
</html>
