<?php  
    session_start();
    include 'db.php';

    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        header("Location: index.php");
        exit;
    }

    if ($_SESSION['role'] !== 'midwife') {
        header("Location: unauthorized.php");
        exit;
    }

    $username = $_SESSION['username'];
    $email = $_SESSION['email'];

    // Get selected filters
    $selected_month = isset($_GET['month']) ? $_GET['month'] : "";
    $search_name = isset($_GET['search']) ? trim($_GET['search']) : "";

    // Get current year dynamically
    $current_year = date("Y");

    // Fetch all months dynamically
    $all_months = [];
    for ($m = 1; $m <= 12; $m++) {
        $monthValue = date("$current_year-m", strtotime("$current_year-$m-01"));
        $all_months[$monthValue] = date("F", strtotime($monthValue . "-01"));
    }

    // Fetch records from medicalrecords table with contact number from appointments table
    $sql = "SELECT m.patient_name, m.address, m.age, m.record_date, 
                IFNULL(a.contact_number, 'N/A') AS contact_number 
            FROM medicalrecords m
            LEFT JOIN appointments a ON m.appointment_id = a.id";

    if (!empty($selected_month)) {
        $sql .= " WHERE DATE_FORMAT(m.record_date, '%Y-%m') = '$selected_month'";
    }

    if (!empty($search_name)) {
        $sql .= empty($selected_month) ? " WHERE" : " AND";
        $sql .= " LOWER(m.patient_name) LIKE LOWER('%$search_name%')";
    }

    $sql .= " ORDER BY m.record_date DESC";
    $result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

        .filters-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            max-width: 450px; /* Limits the width */
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-responsive {
            max-height: 500px; 
            overflow-y: auto;
            overflow-x: auto;
        }

        thead tr {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
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
        <h2 class="mb-4"> Check-Up Records</h2>

        <!-- Filters (Left-aligned, same structure you provided) -->
        <div class="filters-container">
            <form method="GET" class="d-flex gap-2">
                <!-- Month Filter -->
                <select name="month" class="form-select" style="width: 150px;" onchange="this.form.submit()">
                    <option value="">All Months</option>
                    <?php foreach ($all_months as $monthValue => $monthFormatted) {
                        echo "<option value='$monthValue' " . ($selected_month == $monthValue ? 'selected' : '') . ">$monthFormatted</option>";
                    } ?>
                </select>

                <!-- Search Bar -->
                <input type="text" name="search" class="form-control" placeholder="Search by Name" style="width: 250px;" value="<?= htmlspecialchars($search_name) ?>">
                <button type="submit" class="btn btn-primary"> Search</button>

                <!-- Reset Button -->
                <a href="midwife_check-up-record.php" class="btn btn-secondary"> Reset</a>
            </form>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Contact Number</th>
                            <th>Age</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['patient_name']) ?></td>
                                    <td><?= htmlspecialchars($row['address']) ?></td>
                                    <td><?= htmlspecialchars($row['contact_number']) ?></td>
                                    <td><?= htmlspecialchars($row['age'] ?: 'N/A') ?></td>
                                    <td><?= date("F j, Y", strtotime($row['record_date'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <i class="fas fa-folder-open"></i> No records found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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
<?php $conn->close(); ?>
