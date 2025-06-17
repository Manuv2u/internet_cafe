<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>

  <!-- Bootstrap & FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body {
      background: #f4f6f9;
      font-family: 'Segoe UI', sans-serif;
    }

    .navbar {
      background-color: #2563eb;
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #fff;
      font-weight: bold;
      font-size: 20px;
    }

    .navbar-brand img {
      width: 40px;
      height: 40px;
    }

    .sidebar {
      min-height: 100vh;
      background-color: #1e293b;
      padding-top: 60px;
    }

    .sidebar .nav-link {
      color: #cbd5e1;
      padding: 15px;
      transition: 0.3s;
    }

    .sidebar .nav-link:hover, .sidebar .nav-link.active {
      background-color: #334155;
      color: #fff;
    }

    .sidebar i {
      margin-right: 10px;
    }

    .content {
      margin-left: 250px;
      padding: 30px;
    }

    .card-stats {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      padding: 20px;
      display: flex;
      align-items: center;
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .card-stats:hover {
      transform: translateY(-5px);
    }

    .card-stats .icon {
      font-size: 2.5rem;
      color: #2563eb;
      margin-right: 20px;
    }

    /* Dropdown fix */
    .dropdown-menu {
      background-color: #1e293b;
      border: none;
    }

    .dropdown-menu .dropdown-item {
      color: #ffffff;
      background-color: #1e293b;
    }

    .dropdown-menu .dropdown-item:hover {
      background-color: #2563eb;
      color: #ffffff;
    }

    @media (max-width: 768px) {
      .content {
        margin-left: 0;
        padding: 20px;
      }

      .sidebar {
        display: none;
      }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar fixed-top navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      <img src="https://cdn-icons-png.flaticon.com/512/888/888879.png" alt="Logo">
      Internet Café
    </a>
    <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</nav>

<!-- Sidebar -->
<div class="d-flex">
  <div class="sidebar position-fixed d-none d-md-block" style="width: 250px;">
    <ul class="nav flex-column">
      <li><a href="#" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>

      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#"><i class="fas fa-laptop"></i> Computer</a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="add_computer.php">Add Computer</a></li>
          <li><a class="dropdown-item" href="manage_computers.php">Manage Computers</a></li>
        </ul>
      </li>

      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#"><i class="fas fa-users"></i> User</a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="add_user.php">Add User</a></li>
          <li><a class="dropdown-item" href="manage_user.php">Manage User</a></li>
          <li><a class="dropdown-item" href="old_user.php">Old User</a></li>
        </ul>
      </li>

      <li><a href="Booking.php" class="nav-link"><i class="fa-solid fa-window-maximize"></i> Booking</a></li>
      <li><a href="search_user.php" class="nav-link"><i class="fas fa-search"></i> Search</a></li>
      <li><a href="generate_report.php" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></li>
    </ul>
  </div>

  <!-- Main content -->
  <div class="content w-100">
    <div class="mb-4">
      <h2>Welcome to the Dashboard</h2>
      <p class="text-muted">Overview of Internet Café system</p>
    </div>

    <div class="row g-4">
      <div class="col-md-6">
        <div class="card-stats" onclick="location.href='manage_user.php'">
          <div class="icon"><i class="fas fa-users"></i></div>
          <div>
            <h5>Total Users</h5>
            <p class="mb-0">Click to manage users</p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card-stats" onclick="location.href='manage_computers.php'">
          <div class="icon"><i class="fas fa-desktop"></i></div>
          <div>
            <h5>Total Computers</h5>
            <p class="mb-0">Click to manage computers</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

</body>
</html>
