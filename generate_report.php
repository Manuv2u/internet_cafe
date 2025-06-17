<?php
require 'db_connect.php';

$total_billed = 0;
$message = '';
$errors = [];

if (isset($_GET['refresh'])) {
    $start_date = null;
    $end_date = null;
} else {
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
}

if ($start_date && $end_date) {
    if (strtotime($start_date) > strtotime($end_date)) {
        $errors[] = "From date cannot be after To date.";
    } else {
        $sql = "SELECT 
                    u.name AS user_name,
                    u.mobile_number,
                    u.email,
                    b.computer_name,
                    b.start_session,
                    b.end_session,
                    b.duration,
                    b.amount_billed
                FROM bookings b
                JOIN user u ON b.user_id = u.id
                -- JOIN computers c ON b.computer_name = c.id
                WHERE DATE(b.start_session) BETWEEN ? AND ?
                ORDER BY b.start_session DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $message = "No users were booked during the selected period.";
        }
    }
}

if (!isset($result)) {
    $sql = "SELECT 
                u.name AS user_name,
                u.mobile_number,
                u.email,
                b.computer_name,
                b.start_session,
                b.end_session,
                b.duration,
                b.amount_billed
            FROM bookings b
            JOIN user u ON b.user_id = u.id
            -- JOIN computers c ON b.computer_name = c.id
            ORDER BY b.start_session DESC";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
	* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
body {
    font-family: 'Poppins', sans-serif;
    background:  #f2f7fc;
    color: #333;
}

main {
    padding: 20px;
    margin-top: 20px;
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


/* Heading */
h2 {
    text-align: center;
    font-size: 24px;
    margin-bottom: 30px;
    color: #007bff;
}

/* Form Controls */
form {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

input[type="date"],
button {
    padding: 10px 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 15px;
    outline: none;
}

button {
    background-color: #007bff;
    color: white;
    font-weight: 500;
    border: none;
    transition: 0.3s ease;
    cursor: pointer;
}

button.refresh-btn {
    background-color: #28a745;
}

button:hover {
    opacity: 0.92;
}

/* Table Styles */
table {
    width: 95%;
    margin: auto;
    border-collapse: collapse;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

th {
    background-color: #007bff;
    color: white;
    font-weight: 600;
    padding: 14px;
    text-align: center;
}

td {
    padding: 12px;
    text-align: center;
    font-size: 15px;
    border-bottom: 1px solid #eee;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #eaf3ff;
}

/* Totals */
.total {
    text-align: center;
    font-weight: bold;
    margin-top: 20px;
    font-size: 18px;
    color: #333;
}

/* PDF Button */
.pdf-button {
    text-align: center;
    margin-top: 25px;
}

.pdf-button button {
    background-color: #007bff ;
    color: white;
    font-weight: bold;
    padding: 10px 18px;
    border-radius: 8px;
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

<main>
<h2> Report</h2>

<form method="GET" action="">
    <label>From:</label>
    <input type="date" name="start_date" value="<?= htmlspecialchars($start_date ?? '') ?>">
    <label>To:</label>
    <input type="date" name="end_date" value="<?= htmlspecialchars($end_date ?? '') ?>">
    <button type="submit">Search</button>
    <button type="submit" name="refresh" class="refresh-btn">Refresh</button>
</form>
<?php if (!empty($errors)) : ?>
    <div style="color: red; text-align:center; margin-bottom:15px;">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php elseif (!empty($message)) : ?>
    <div style="color: darkorange; text-align:center; margin-bottom:15px;">
        <p><?= htmlspecialchars($message) ?></p>
    </div>
<?php endif; ?>


<table>
    <thead>
        <tr>
            <th>User Name</th>
            <th>Mobile</th>
            <th>Email</th>
            <th>Computer</th>
            <th>Start Session</th>
            <th>End Session</th>
            <th>Duration (min)</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { 
            $total_billed += $row['amount_billed'];
        ?>
        <tr>
            <td><?= htmlspecialchars($row['user_name']) ?></td>
            <td><?= htmlspecialchars($row['mobile_number']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['computer_name']) ?></td>
            <td><?= htmlspecialchars($row['start_session']) ?></td>
            <td><?= htmlspecialchars($row['end_session']) ?></td>
            <td><?= htmlspecialchars($row['duration']) ?></td>
            <td><?= number_format($row['amount_billed'], 2) ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<div class="total">
    Total Amount Billed: <?= number_format($total_billed, 2) ?>
</div>

<div class="pdf-button">
    <button onclick="generatePDF()">Download PDF</button>
</div>

<script>
    async function generatePDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'pt', 'a4');

        doc.setFontSize(18);
        doc.text("Booking Report", 40, 40);

        const headers = [["User Name", "Mobile", "Email", "Computer", "Start", "End", "Duration", "Amount"]];
        const rows = [];

        document.querySelectorAll("table tbody tr").forEach(row => {
            const rowData = [];
            row.querySelectorAll("td").forEach(cell => {
                rowData.push(cell.textContent.trim());
            });
            rows.push(rowData);
        });

        doc.autoTable({
            head: headers,
            body: rows,
            startY: 60,
            theme: 'grid',
            headStyles: {
                fillColor: [255, 255, 255],
                textColor: 0,
                lineColor: [0, 0, 0],
                lineWidth: 0.1,
                fontStyle: 'bold',
            },
            styles: {
                fontSize: 10,
                cellPadding: 5,
                lineColor: [0, 0, 0],
                lineWidth: 0.1,
            },
        });

        doc.save("booking_report.pdf");
    }
</script>
</main>
</body>
</html>