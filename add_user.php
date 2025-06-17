<?php
require 'db_connect.php';

$errors = [];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name          = trim($_POST['name']);
    $address       = trim($_POST['address']);
    $mobile_number = trim($_POST['mobile_number']);
    $email         = trim($_POST['email']);

    if (!preg_match("/^[A-Z][a-z]*( [A-Z][a-z]*)*$/", $name)) {
        $errors[] = "Name must start with capital letters and contain only alphabets.";
    }

    if (!preg_match("/^[6-9][0-9]{9}$/", $mobile_number)) {
        $errors[] = "Mobile number must start with 6, 7, 8, or 9 and be 10 digits.";
    } else {
        $checkMobile = $conn->prepare("SELECT id FROM user WHERE mobile_number = ?");
        $checkMobile->bind_param("s", $mobile_number);
        $checkMobile->execute();
        $checkMobile->store_result();
        if ($checkMobile->num_rows > 0) {
            $errors[] = "Mobile number already exists.";
        }
        $checkMobile->close();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@gmail.com')) {
        $errors[] = "Email must be a valid Gmail address (e.g., yourname@gmail.com).";
    } else {
        $checkEmail = $conn->prepare("SELECT id FROM user WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();
        if ($checkEmail->num_rows > 0) {
            $errors[] = "Email already exists.";
        }
        $checkEmail->close();
    }

    if (strlen($address) < 10) {
        $errors[] = "Address must be at least 10 characters long.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO user (name, address, mobile_number, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $address, $mobile_number, $email);

        if ($stmt->execute()) {
            $message = "User added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
         * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f2f7fc;
        }

        .navbar {
            background-color: #007bff;
            color: white;
            padding: 16px 30px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            font-weight: 500;
            border-radius: 8px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .navbar a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.3);
            transform: scale(1.03);
        }

        .navbar a.active {
            background-color: rgba(0, 0, 0, 0.3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .navbar a.logout-active {
            background-color: #dc3545;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 42px;
            left: 0;
            background-color: #fff;
            min-width: 180px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
            border-radius: 8px;
            overflow: hidden;
            z-index: 99;
        }

        .dropdown-content a {
            display: block;
            padding: 10px 15px;
            color: #333;
            background-color: white;
        }

        .dropdown-content a:hover {
            background-color: #f0f0f0;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .form-container {
            background: white;
            max-width: 500px;
            margin: 60px auto;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #007bff;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
            font-size: 15px;
            transition: 0.3s;
        }

        input[type="text"]:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }
        input[type="email"],
textarea {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    margin-bottom: 20px;
    font-size: 15px;
    transition: 0.3s;
}

input[type="email"]:focus,
textarea:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
}

        button {
            background: #007bff;
            border: none;
            padding: 14px;
            border-radius: 10px;
            width: 100%;
            color: white;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #0056b3;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            color: green;
        }

        .error {
            color: red;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            width: 32px;
        }

        .logo strong {
            font-size: 18px;
            color: white;
        }
    </style>
</head>
<body>

<!-- Navbar -->

<div class="navbar">
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://cdn-icons-png.flaticon.com/512/888/888879.png" alt="Logo" style="width: 30px;">
        <strong >Internet Cafe Shop</strong>
    </div>

    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>

    <div class="dropdown">
        <a href="#"><i class="fas fa-desktop"></i> Computer</a>
        <div class="dropdown-content">
            <a href="add_computer.php">Add Computer</a>
            <a href="manage_computers.php">Manage Computers</a>
        </div>
    </div>

    <div class="dropdown">
        <a href="#"><i class="fas fa-users"></i> User</a>
        <div class="dropdown-content">
            <a href="add_user.php">Add User</a>
            <a href="manage_user.php">Manage Users</a>
        </div>
    </div>

    <a href="booking.php"><i class="fa-solid fa-window-maximize"></i> Bookings</a>
    <a href="search_user.php"><i class="fas fa-search"></i> Search</a>
    <a href="generate_report.php"><i class="fas fa-chart-line"></i> Reports</a>
    <a href="logout.php" style="margin-left:auto;"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>


<!-- Main Form -->
<div class="container">
    <div class="form-container">
        <?php
            if (!empty($errors)) {
                echo "<ul style='color:red;'>";
                foreach ($errors as $err) {
                    echo "<li>" . htmlspecialchars($err) . "</li>";
                }
                echo "</ul>";
            } elseif (!empty($message)) {
                echo "<p>$message</p>";
            }
        ?>

        <h2>Add New User</h2>
        <form method="POST" novalidate>
            <label>Name:</label>
            <input type="text" name="name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required onblur="capitalizeName(this)">
            <div id="name-error" class="field-error"></div>

            <label>Address:</label>
            <textarea name="address" rows="3" required><?= isset($address) ? htmlspecialchars($address) : '' ?></textarea>
            <div id="address-error" class="field-error"></div>

            <label>Mobile Number:</label>
            <input type="text" name="mobile_number" value="<?= isset($mobile_number) ? htmlspecialchars($mobile_number) : '' ?>" required>
            <div id="mobile-error" class="field-error"></div>

            <label>Email:</label>
            <input type="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
            <div id="email-error" class="field-error"></div>

            <button type="submit">Add User</button>
        </form>
    </div>


</div>

<!-- JavaScript -->
<script>
document.querySelector("form").addEventListener("submit", function (e) {
    let isValid = true;

    const name = document.querySelector('input[name="name"]');
    const address = document.querySelector('textarea[name="address"]');
    const mobile = document.querySelector('input[name="mobile_number"]');
    const email = document.querySelector('input[name="email"]');

    const nameError = document.getElementById("name-error");
    const addressError = document.getElementById("address-error");
    const mobileError = document.getElementById("mobile-error");
    const emailError = document.getElementById("email-error");

    nameError.textContent = "";
    addressError.textContent = "";
    mobileError.textContent = "";
    emailError.textContent = "";

    const nameRegex = /^[A-Za-z ]+$/;
    if (!name.value.trim() || !nameRegex.test(name.value.trim())) {
        nameError.textContent = "Name must contain only letters and spaces.";
        isValid = false;
    }

    if (!address.value.trim() || address.value.trim().length < 10) {
        addressError.textContent = "Address must be at least 10 characters long.";
        isValid = false;
    }

    const mobileRegex = /^[6-9]\d{9}$/;
    if (!mobileRegex.test(mobile.value.trim())) {
        mobileError.textContent = "Mobile must be 10 digits and start with 6, 7, 8, or 9.";
        isValid = false;
    }

    const emailVal = email.value.trim();
    if (!emailVal.endsWith("@gmail.com") || emailVal.indexOf("@") <= 0) {
        emailError.textContent = "Email must be a valid Gmail address (e.g., yourname@gmail.com).";
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
    }
});

function capitalizeName(input) {
    input.value = input.value
        .toLowerCase()
        .split(' ')
        .filter(w => w.length > 0)
        .map(w => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ');
}
</script>

</body>
</html>
