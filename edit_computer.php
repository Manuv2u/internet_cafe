<?php
require 'db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('❌ Invalid computer ID.');
}

$id = intval($_GET['id']);
$message = "";

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM computers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$computer = $result->fetch_assoc();

if (!$computer) {
    die('❌ Computer not found.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['computer_name'];
    $ip = $_POST['ip_address'];
    $status = $_POST['status'];

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $message = "❌ Invalid IP address format.";
    } else {
        $update = $conn->prepare("UPDATE computers SET computer_name = ?, ip_address = ?, status = ? WHERE id = ?");
        $update->bind_param("sssi", $name, $ip, $status, $id);
        $message = $update->execute() ? "✅ Computer updated successfully!" : "❌ Error updating computer.";
        // Refresh data after update
        $stmt = $conn->prepare("SELECT * FROM computers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $computer = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Computer</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f2f7fc ;
            margin: 0;
            min-height: 100vh;
        }

        /* Navbar */
       
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
        
        /* Form Container */
       .form-container {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    padding: 35px 40px;
    border-radius: 20px;
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.4);
    width: 100%;
    max-width: 500px;
    margin: 60px auto;
    border: 1px solid rgba(200, 200, 200, 0.3);
}


        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #007bff ;
            font-weight: 600;
            font-size: 26px;
        }

        label {
            font-size: 16px;
            font-weight: 500;
            color: #444;
            display: block;
            margin-bottom: 5px;
        }

         input[type="text"],
select {
	width:100%;
	margin-bottom:25px;
    background: #fdfdfd;
    border: 1px solid #ccc;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.3s ease-in-out;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

input:focus,
select:focus {
    border-color: #74ebd5;
    box-shadow: 0 0 10px rgba(116, 235, 213, 0.6);
    background: #fff;
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
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(116, 235, 213, 0.4);
}

button:hover {
    background: #0056b3 ;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(116, 235, 213, 0.5);
}


        .message {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 20px;
            color: green;
        }

        .error {
            color: red;
        }

        a.back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #555;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a.back-link:hover {
            color: #007bff;
        }
    </style>
</head>
<body>

<!-- Navbar (same as add_computer.php) -->

<div class="navbar">
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://cdn-icons-png.flaticon.com/512/888/888879.png" alt="Logo" style="width: 30px;">
        <strong>Internet Cafe Shop</strong>
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


<!-- Form -->
<div class="form-container">
    <h2>Edit Computer</h2>
    <?php
        if (!empty($message)) {
            echo "<div class='message " . (str_starts_with($message, '✅') ? '' : 'error') . "'>" . htmlspecialchars($message) . "</div>";
        }
    ?>
    <form method="POST">
        <label for="computer_name">Computer Name:</label>
        <input type="text" id="computer_name" name="computer_name" value="<?= htmlspecialchars($computer['computer_name']) ?>" required>

        <label for="ip_address">IP Address:</label>
        <input type="text" id="ip_address" name="ip_address" value="<?= htmlspecialchars($computer['ip_address']) ?>" required pattern="^(\d{1,3}\.){3}\d{1,3}$" title="Enter a valid IP address (e.g. 192.168.1.1)">

        <label for="status">Status:</label>
        <select id="status" name="status">
            <option value="available" <?= $computer['status'] == 'available' ? 'selected' : '' ?>>Available</option>
            <option value="in use" <?= $computer['status'] == 'in use' ? 'selected' : '' ?>>In Use</option>
        </select>

        <button type="submit">Update Computer</button>
    </form>
    <a class="back-link" href="manage_computers.php">← Back to Manage Computers</a>
</div>

</body>
</html>
