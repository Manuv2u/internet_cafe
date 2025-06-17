<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['computer_name'];
    $ip = $_POST['ip_address'];
    $status = $_POST['status'];

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $message = "❌ Invalid IP address format.";
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM computers WHERE ip_address = ?");
        $checkStmt->bind_param("s", $ip);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = "❌ Computer with this IP address already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO computers (computer_name, ip_address, status) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $ip, $status);
            $message = $stmt->execute() ? "✅ Computer added successfully!" : "❌ Error adding computer.";
        }

        $checkStmt->close();
    }
}

$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Computer</title>
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

<div class="navbar">
    <div class="logo">
        <img src="https://cdn-icons-png.flaticon.com/512/888/888879.png" alt="Logo">
        <strong>Internet Cafe Shop</strong>
    </div>

    <a href="dashboard.php" class="<?php echo $current == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-home"></i> Dashboard
    </a>

    <div class="dropdown">
        <a href="#" class="<?php echo in_array($current, ['add_computer.php', 'manage_computers.php']) ? 'active' : ''; ?>">
            <i class="fas fa-desktop"></i> Computer
        </a>
        <div class="dropdown-content">
            <a href="add_computer.php">Add Computer</a>
            <a href="manage_computers.php">Manage Computers</a>
        </div>
    </div>

    <div class="dropdown">
        <a href="#" class="<?php echo in_array($current, ['add_user.php', 'manage_user.php']) ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> User
        </a>
        <div class="dropdown-content">
            <a href="add_user.php">Add User</a>
            <a href="manage_user.php">Manage Users</a>
        </div>
    </div>

    <a href="booking.php" class="<?php echo $current == 'booking.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-window-maximize"></i> Bookings
    </a>

    <a href="search_user.php" class="<?php echo $current == 'search_user.php' ? 'active' : ''; ?>">
        <i class="fas fa-search"></i> Search
    </a>

    <a href="generate_report.php" class="<?php echo $current == 'generate_report.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-line"></i> Reports
    </a>

    <a href="logout.php" class="<?php echo $current == 'logout.php' ? 'logout-active' : ''; ?>" style="margin-left:auto;">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<div class="form-container">
    <h2>Add New Computer</h2>

    <?php if (isset($message)): ?>
        <div class="message <?php echo str_starts_with($message, '❌') ? 'error' : ''; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateForm()">
        <label for="computer_name">Computer Name:</label>
        <input type="text" id="computer_name" name="computer_name" required>

        <label for="ip_address">IP Address:</label>
        <input type="text" id="ip_address" name="ip_address" placeholder="e.g. 192.168.1.10" required>

        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="available">Available</option>
            <option value="in use">In Use</option>
        </select>

        <button type="submit">Add Computer</button>
    </form>
</div>

<script>
function validateForm() {
    const ip = document.getElementById('ip_address').value;
    const ipParts = ip.split('.');
    if (ipParts.length !== 4) {
        alert('Invalid IP address format.');
        return false;
    }
    for (let part of ipParts) {
        const num = Number(part);
        if (isNaN(num) || num < 0 || num > 255) {
            alert('Each part of the IP address must be between 0 and 255.');
            return false;
        }
    }
    return true;
}
</script>

</body>
</html>
