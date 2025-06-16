<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['computer_name'];
    $ip = $_POST['ip_address'];
    $status = $_POST['status'];

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $message = "❌ Invalid IP address format.";
    } else {
        // Check if IP already exists
        $checkStmt = $conn->prepare("SELECT id FROM computers WHERE ip_address = ?");
        $checkStmt->bind_param("s", $ip);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = "❌ Computer with this IP address already exists.";
        } else {
            // Proceed to insert
            $stmt = $conn->prepare("INSERT INTO computers (computer_name, ip_address, status) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $ip, $status);
            $message = $stmt->execute() ? "✅ Computer added successfully!" : "❌ Error adding computer.";
        }

        $checkStmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Computer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #fbc2eb, #a6c1ee);
            margin: 0;
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 14px 30px;
            display: flex;
            align-items: center;
            gap: 25px;
            flex-wrap: wrap;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .navbar a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .navbar a:hover {
            background-color: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
            border-radius: 8px;
            min-width: 160px;
            z-index: 1;
        }

        .dropdown-content a {
            padding: 10px;
            display: block;
            color: #333;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            margin: 50px auto;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }

        input[type="text"], select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
            font-size: 14px;
        }

        button {
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            border: none;
            padding: 14px;
            border-radius: 10px;
            width: 100%;
            color: white;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
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
    </style>
</head>
<body>

<div class="navbar">
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://cdn-icons-png.flaticon.com/512/888/888879.png" alt="Logo" style="width: 30px;">
        <strong style="color: #007bff; font-size: 18px;">Internet Cafe Shop</strong>
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
            alert('Each part of IP address must be between 0 and 255.');
            return false;
        }
    }
    return true;
}
</script>

</body>
</html>
