<?php 
    session_start();

    include 'db.php';

    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        header("Location: index.php"); // Redirect to login page if not logged in
        exit;
    }

    // Restrict access to only Midwives
    if ($_SESSION['role'] !== 'midwife') {
        header("Location: unauthorized.php"); // Redirect to an unauthorized access page
        exit;
    }

    $username = $_SESSION['username'];
    $email = $_SESSION['email'];

    // ✅ Fetch Total Check-ups
    $total_checkups = $conn->query("SELECT COUNT(*) as total FROM medicalrecords")->fetch_assoc()['total'] ?? 0;

    // ✅ Fetch Total Appointments
    $total_appointments = $conn->query("SELECT COUNT(*) as total FROM appointments")->fetch_assoc()['total'] ?? 0;

    $completed_appointments = $conn->query("SELECT COUNT(DISTINCT appointment_id) as total FROM medicalrecords")->fetch_assoc()['total'];
    $missed_appointments = $total_appointments - $completed_appointments;


    // ✅ Fetch Monthly Checkups (Bar Chart)
    $monthly_checkups = [];
    $monthly_appointments = [];
    for ($m = 1; $m <= 12; $m++) {
        $month = str_pad($m, 2, "0", STR_PAD_LEFT);
        $year = date("Y");

        $checkup_query = "SELECT COUNT(*) as total FROM medicalrecords WHERE DATE_FORMAT(record_date, '%Y-%m') = '$year-$month'";
        $appointment_query = "SELECT COUNT(*) as total FROM appointments WHERE DATE_FORMAT(visit_date, '%Y-%m') = '$year-$month'";

        $monthly_checkups[] = $conn->query($checkup_query)->fetch_assoc()['total'] ?? 0;
        $monthly_appointments[] = $conn->query($appointment_query)->fetch_assoc()['total'] ?? 0;
    }

    // ✅ Fetch Age Group Distribution (Pie Chart)
    $age_groups = [
        "10-14" => "SELECT COUNT(*) as total FROM medicalrecords WHERE age BETWEEN 10 AND 14",
        "15-19" => "SELECT COUNT(*) as total FROM medicalrecords WHERE age BETWEEN 15 AND 19",
        "20-49" => "SELECT COUNT(*) as total FROM medicalrecords WHERE age BETWEEN 20 AND 49"
    ];

    $age_distribution = [];
    foreach ($age_groups as $group => $query) {
        $age_distribution[$group] = $conn->query($query)->fetch_assoc()['total'] ?? 0;
    }
    
    // Close connection
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        /* Sidebar styles */
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 60.9px; /* Navbar height */
            left: -250px; /* Initially hidden */
            background-color: #8CB150;
            padding-top: 20px;
            overflow-y: auto; /* Enable scrolling for smaller screens */
            transition: left 0.3s ease;
            z-index: 1040; /* Ensure it appears above main content */
        }

        .sidebar.open {
            left: 0;
        }

        .sidebar.closed {
            left: -250px; /* Allow hiding on toggle */
        }

        .sidebar .nav-link {
            color: #ffffff;
            font-size: 18px; /* Adjust as needed */
            font-weight: bold; /* Makes text stand out */
            padding: 12px 15px; /* Increase spacing */
        }

        .sidebar .nav-link:hover {
            background-color: #495057;
        }

        .main-content {
            padding: 20px;
            margin-top: 56px; /* Match navbar height */
            transition: margin-left 0.3s ease;
        }

        /* Responsive styles for large screens */
        @media (min-width: 992px) {
            .sidebar {
                left: 0; /* Sidebar always visible by default on large screens */
            }

            .sidebar.closed {
                left: -250px; /* Allow hiding on toggle */
            }

            .main-content {
                margin-left: 250px; /* Default margin for large screens */
            }

            .main-content.expanded {
                margin-left: 0; /* Adjust when sidebar is toggled */
            }
        }

        /* Responsive styles for small screens */
        @media (max-width: 991px) {
            .sidebar {
                width: 250px;
                height: calc(100vh - 56px); /* Adjust height to exclude navbar */
                left: -250px; /* Hidden by default */
            }

            .sidebar.open {
                left: 0;
            }

            .main-content {
                margin-left: 0; /* Reset margin for small screens */
            }
        }

        /* Toggle button styles */
        .toggle-btn {
            border: none;
            background: transparent;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .toggle-btn:focus {
            outline: none;
        }

        /* Larger font for navbar brand */
        .navbar-brand {
            font-size: 20px; /* Adjust size as needed */
            font-weight: bold; /* Makes it stand out */
        }

        .dashboard-container { margin-top: 80px; }
        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .stats-card h3 { 
            margin-bottom: 0; 
            font-size: 22px; 
            font-weight: bold; }

        .stats-card p { 
            font-size: 18px; 
            margin-bottom: 0; }
            
        .chart-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 400px;
            width: 100%;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .chart-box {
            width: 50%;
            padding: 10px;
        }
        canvas {
            width: 100% !important;
            height: 100% !important;
        }
        .dashboard-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <button class="toggle-btn" id="sidebarToggle">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Admin Dashboard brand -->
            <a class="navbar-brand ms-2" href="#"><img src="Images\amorganda logo.png" alt="Logo" style="width: 35px; height: 35px; border-radius: 50%; margin-right: 10px;">Amorganda Lying-in Clinic</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="ms-2"><?php echo htmlspecialchars($username); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end profile-dropdown" aria-labelledby="profileDropdown" style="position: absolute; right: 0; top: 50px; z-index: 1050; box-shadow: 0px 4px 6px rgba(0,0,0,0.1);">
                        <li><a class="dropdown-item" href="changepassword.php"><i class="fas fa-lock"></i> Change Password</a></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="midwife_dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="midwife_appointment.php">
                    <i class="fas fa-calendar-plus"></i> Appointments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="midwife_my-schedule.php">
                    <i class="fas fa-calendar-check"></i> My Schedule
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="midwife_check-up-form.php">
                    <i class="fas fa-notes-medical"></i> Check-up Form
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="midwife_check-up-record.php">
                    <i class="fas fa-clipboard-list"></i> Check-up Records
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <h2 class="text-center">📊 Clinic Dashboard</h2>
        
        <!-- Stats Cards -->
        <div class="row g-3">
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h5>Total Check-Ups</h5>
                    <h3><?= $total_checkups; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h5>Total Appointments</h5>
                    <h3><?= $total_appointments; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h5>Completed</h5>
                    <h3><?= $completed_appointments; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h5>Missed</h5>
                    <h3><?= $missed_appointments; ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="d-flex justify-content-center mt-3">
            <div class="chart-box">
                <div class="chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
            <div class="chart-box">
                <div class="chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 📊 Bar Chart Data
        var barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: 'Appointments',
                        data: <?= json_encode($monthly_appointments) ?>,
                        backgroundColor: 'rgba(52, 152, 219, 0.8)'
                    },
                    {
                        label: 'Check-Ups',
                        data: <?= json_encode($monthly_checkups) ?>,
                        backgroundColor: 'rgba(231, 76, 60, 0.8)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // 🥧 Pie Chart Data
        var pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['10-14', '15-19', '20-49'],
                datasets: [{
                    data: <?= json_encode(array_values($age_distribution)) ?>,
                    backgroundColor: ['#3498db', '#e74c3c', '#f1c40f', '#1abc9c']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            sidebar.classList.toggle('closed'); 
            mainContent.classList.toggle('expanded'); 
        });
    </script>
</body>
</html>
