<?php
require 'db_connect.php';

$user = null;
$message = "";
$selected_mode = "available";

// Load available computers
$computers = $conn->query("SELECT computer_name, id FROM computers WHERE status = 'available'");

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM user WHERE name LIKE ? OR mobile_number LIKE ?");
    $searchTerm = "%$search%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

if(isset($_GET['computer_id'])){
    $computer_id = $_GET['computer_id'];
    $stmt = $conn -> prepare("SELECT computer_name, computer_name, id FROM computers WHERE id = ? ");
    $stmt->bind_param('i',$computer_id);
    $stmt->execute();
    $computer = $stmt->get_result()->fetch_assoc();
}
$all_bookings = [];
$test = "1";


if (isset($_GET['modeSelector']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $selected_mode = $_GET['modeSelector'];
    $search = $_GET['user'];
    $stmt = $conn->prepare("SELECT * FROM user WHERE name LIKE ? OR mobile_number LIKE ?");
    $searchTerm = "%$search%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $all_bookings = [];
    $bookings_result = $conn->query("
        SELECT b.id, u.name as user_name, c.computer_name, b.start_session,c.id as cid
        FROM bookings b
        JOIN user u ON b.user_id = u.id 
        JOIN computers c ON (b.computer_name = c.computer_name ) AND c.status = 'in use' AND b.amount_billed = 0
        ORDER BY b.start_session ASC
    ");
    if ($bookings_result) {
        while ($row = $bookings_result->fetch_assoc()) {
            $all_bookings[] = $row;
        }
    } else {
        $message = "Error fetching bookings: " . $conn->error;
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $computer_id = $_POST['computer_id'];
    $start_session = $_POST['start_session'];
    //$end_session = $_POST['end_session'];

    $start = new DateTime($start_session);
    $end = new DateTime($start_session);
    $now = new DateTime();

    // if ($end <= $start) {
    //     $test = "3";
    //     $message = "Error: End session must be after start session.";
    // } else {
        $test = "4";
        $interval = $start->diff($end);
        $duration = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

        $rate_per_minute = 1.50;
        $amount_billed = $duration * $rate_per_minute;

        $stmt = $conn->prepare("SELECT computer_name FROM computers WHERE id = ?");
        $stmt->bind_param("i", $computer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $computer = $result->fetch_assoc();
        $computer_name = $computer['computer_name'];
        $amount_billed = 0;
        $duration = 0;

        $stmt = $conn->prepare("INSERT INTO bookings (user_id, computer_name, start_session, end_session, duration, amount_billed) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssid", $user_id, $computer_name, $start_session, $start_session, $duration, $amount_billed);

        if ($stmt->execute()) {
                $test = "6";
                $conn->query("UPDATE computers SET status = 'in use' WHERE id = $computer_id");
                //  $conn->query("UPDATE user SET status = 'in use' WHERE id = $user_id");
                $message = "Booking session started";
        }
   // }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Session</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
       <style>
	   *{
		box-sizing : border-box;
	}
      body {
            font-family: 'Poppins', sans-serif;
            background: #f2f7fc ;
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
       .container {
    max-width: 600px;
    margin: 90px auto 0 auto; /* ⬅️ Top margin added here */
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

h2 {
    text-align: center;
    color:  #007bff ;
    margin-bottom: 30px;
}

form {
    margin-top: 20px;
}

input, select, button {
    width: 100%;
    padding: 14px;
    margin-top: 12px;
    font-size: 16px;
    border-radius: 10px;
    border: 1px solid #ccc;
    transition: 0.3s;
}

input:focus, select:focus {
    border-color: #f8b500;
    box-shadow: 0 0 5px rgba(248, 181, 0, 0.5);
    outline: none;
}

 button {
            background: #007bff;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        button:hover {
             background: #0056b3; ;
        }

.info, .success {
    text-align: center;
    margin-top: 20px;
    font-weight: bold;
    padding: 10px;
    border-radius: 8px;
}

.success {
    background-color: #d4edda;
    color: #155724;
}

.info {
   background-color: #e6e6fa;
    color: #4b0082;
}

label {
    display: block;
    margin-top: 10px;
    font-weight: 600;
    color: #333;
}

/* Session Form Styling */
#add_session_form form {
    margin-top: 30px;
    padding: 20px;
    background-color: #f4f8ff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

#add_session_form label {
    margin-top: 10px;
    display: block;
    font-weight: 600;
    color: #333;
}

#add_session_form select,
#add_session_form input {
    width: 100%;
    margin-top: 8px;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    transition: border 0.3s, box-shadow 0.3s;
}

#add_session_form input:focus,
#add_session_form select:focus {
    border-color: #74ebd5;
    box-shadow: 0 0 5px rgba(116, 235, 213, 0.5);
    outline: none;
}

#add_session_form button {
    margin-top: 20px;
    background: linear-gradient(to right, #74ebd5, #ACB6E5);
    color: white;
    border: none;
    font-weight: bold;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
}

#add_session_form button:hover {
    background: linear-gradient(to right, #ACB6E5, #74ebd5);
}

/* Booking Table Section */
#test {
    margin-top: 40px;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

#test label {
    display: block;
    font-size: 20px;
    font-weight: bold;
    color: #6a0dad;
    margin-bottom: 20px;
}

select#modeSelector {
    margin-top: 10px;
}
#booking_table {
    width: 100%;
    margin-top: 30px;
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    border-radius: 10px;
    overflow: hidden;
}

#booking_table thead {
    background-color: #6a0dad; /* Deep Slate Blue (Contrast) */
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#booking_table th, #booking_table td {
    padding: 14px 18px;
    border-bottom: 1px solid #f1f1f1;
    text-align: left;
    font-size: 15px;
}

#booking_table tbody tr:hover {
    background-color: #c8ade2;
    cursor: pointer;
    transition: background-color 0.2s ease-in-out;
}
#booking_table thead th:first-child {
    border-top-left-radius: 10px;
}
#booking_table thead th:last-child {
    border-top-right-radius: 10px;
}

#booking_table tbody tr:last-child td:first-child {
    border-bottom-left-radius: 10px;
}
#booking_table tbody tr:last-child td:last-child {
    border-bottom-right-radius: 10px;
}



    </style>
</head>
<body>
    <!-- Navbar -->

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


<div class="container">
    <h2>Book User Session</h2>

    <?php if ($message): ?>
        <div class="<?= strpos($message, 'Error') !== false ? 'info' : 'success' ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="GET">
        <input type="text" name="search" placeholder="Enter name or mobile number" required>
        <button type="submit">Search User</button>
    </form>

    <?php if ($user): ?>
        <div class="info">
            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile_number']) ?></p>
        </div>

          <form action="" method="GET">
            <label for="appModeSelector" class="block text-lg font-medium text-gray-700 mb-2">
                Select Mode:
            </label>
            <input type="hidden" name="user" value="<?= $search ?>">
            <select id="modeSelector" name="modeSelector" onchange="this.form.submit()">
                <option value="available" <?php echo ($selected_mode === 'available') ? 'selected' : ''; ?>>Book Session</option>
                <option value="in_use" <?php echo ($selected_mode === 'in_use') ? 'selected' : ''; ?>>View All Bookings</option>
            </select>
        </form>

        <div id="add_session_form">
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

            <label>Computer Name:</label>
            <select name="computer_id" required>
                <option value="">Select Computer</option>
                <?php
                $computers = $conn->query("SELECT id, computer_name FROM computers WHERE status = 'available'");
                while ($row = $computers->fetch_assoc()) {
                    echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['computer_name']) . '</option>';
                }
                ?>
            </select>

            <label>Start Session:</label>
            <input type="datetime-local" name="start_session" id="start_session" required onpaste="return false;">

            <!-- <label>End Session:</label>
            <input type="datetime-local" name="end_session" id="end_session" required onpaste="return false;"> -->

            <button type="submit">Start Session</button>
        </form>
        </div>

        <div id="test">
            <label>TEST</label>
            <table id="booking_table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User Name</th>
                        <th>Computer Name</th>
                        <th>Start Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_bookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['id']) ?></td>
                            <td><?= htmlspecialchars($booking['user_name']) ?></td>
                            <td><?= htmlspecialchars($booking['computer_name']) ?></td>
                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($booking['start_session']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    
    <?php elseif (isset($_GET['search'])): ?>
        <p class="info">No user found matching your search.</p>
    <?php endif; ?>
</div>

<script>
window.onload = function () {
    const opt = document.getElementById('modeSelector');
    const urlParams = new URLSearchParams(window.location.search);
    const myParam = urlParams.get('modeSelector');
    if(myParam === 'available'){
        document.getElementById('add_session_form').style.display = 'block'
        document.getElementById('test').style.display = 'none'
    }
    else if(myParam === 'in_use'){
        document.getElementById('add_session_form').style.display = 'none'
        document.getElementById('test').style.display = 'block'
    }
    else{
        document.getElementById('add_session_form').style.display = 'block'
        document.getElementById('test').style.display = 'none'
    }

    const now = new Date();
    now.setSeconds(0, 0);

    const iso = now.toLocaleString('sv').replace(' ', 'T').slice(0, 16); 

    const startInput = document.getElementById('start_session');
    //const endInput = document.getElementById('end_session');

    startInput.min = iso;
    //endInput.min = iso;
    startInput.value = iso;

    // Adjust endInput min when start changes
    // startInput.addEventListener('change', function () {
    //     endInput.min = this.value;
    //     if (endInput.value < this.value) {
    //         endInput.value = this.value;
    //     }
    // });

    // Disable paste
    document.querySelectorAll('input[type="datetime-local"]').forEach(input => {
        input.addEventListener('paste', e => e.preventDefault());
    });

    const table = document.getElementById("booking_table")
    if(table){
        const rows = table.getElementsByTagName('tr');
        if(rows){
            Array.from(rows).forEach((row,index)=>{
                row.addEventListener('click', () => {
                        var host =   "http://" + window.location.host;
                        var user = <?php echo json_encode($user); ?>;
                        var bookings = <?php echo json_encode($all_bookings); ?>;
                        console.log(bookings)
                        console.log(user)
                        const cells = row.getElementsByTagName('td');
                        console.log(cells[0]);
                        console.log(cells[1]);

                        const booking_id = cells[0].innerHTML;
                        console.log(booking_id);
                        console.log(index);
                        let index1 = index
                        let selected_booking = bookings[index1 - 1]
                        console.log(selected_booking);
                        console.log(host)

                        let url = new URL(host+"/internet_cafe/edit_booking.php")
                        url.searchParams.append('user',user['name'])
                        url.searchParams.append('computer_id',selected_booking['cid'])
                        url.searchParams.append('booking_id',booking_id)

                        host = host + '/p/edit_booking.php?user='+user['name']+"&computer_id="+selected_booking['cid']
                        console.log(url.toString())
                        window.location.href = url.toString()

                        const content2 = cells[1].innerHTML;
                        console.log(content2);
                     });
            })
        }
    }

};
</script>

</body>
</html>