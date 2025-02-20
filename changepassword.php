<?php
    session_start();
    include 'db.php';

    // Restrict access to logged-in users
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        header("Location: index.php");
        exit;
    }

    // Get user details from session
    $username = $_SESSION['username'];
    $email = $_SESSION['email'];

    // Handle password change
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Fetch the current password from database
        $stmt = $conn->prepare("SELECT password FROM tbl_Users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($stored_password);
        $stmt->fetch();
        $stmt->close();

        // Verify the current password (you should use `password_hash()` for security)
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "All fields are required!";
        } elseif ($current_password !== $stored_password) {
            $error = "Current password is incorrect.";
        } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $new_password)) {
            $error = "Password must be at least 8 characters long and include at least one letter and one number.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } else {
            // Update new password in database
            $stmt = $conn->prepare("UPDATE tbl_Users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $new_password, $email);
            if ($stmt->execute()) {
                $success = "Password updated successfully!";
            } else {
                $error = "Error updating password.";
            }
            $stmt->close();
        }
    }
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

        .container {
            margin-top: 80px;
            max-width: 500px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 38px;
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

    <!-- Sidebar Navigation for Admin -->
    <?php if ($_SESSION['role'] === 'admin') : ?>
        <div class="sidebar" id="sidebar-admin">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_users.php">
                        <i class="fa-solid fa-users"></i> Manage Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Appointments.php">
                        <i class="fas fa-calendar-check"></i> Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="inventory.php">
                        <i class="fas fa-box"></i> Inventory
                    </a>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Sidebar Navigation for Midwife -->
    <?php if ($_SESSION['role'] === 'midwife') : ?>
        <div class="sidebar" id="sidebar-midwife">
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
                    <a class="nav-link" href="checkup_form.php">
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
    <?php endif; ?>

    <!-- Sidebar For Utility -->
    <?php if ($_SESSION['role'] === 'utility') : ?>
        <div class="sidebar" id="sidebar">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="utility_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="utility_inventory.php">
                        <i class="fa-solid fa-boxes"></i> Inventory
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="utility_restock-log.php">
                        <i class="fa-solid fa-clipboard-list"></i> Restock Log
                    </a>
                </li>
            </ul>
        </div>
    <?php endif; ?>


    <div class="container">
        <h3 class="text-center">Change Password</h3>
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
        <?php if (isset($success)) { echo "<div class='alert alert-success'>$success</div>"; } ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($username); ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled>
            </div>
            <div class="mb-3 position-relative">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-control" name="current_password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword(this, 'current_password')"></i>
            </div>
            <div class="mb-3 position-relative">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" name="new_password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword(this, 'new_password')"></i>
            </div>
            <div class="mb-3 position-relative">
                <label class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword(this, 'confirm_password')"></i>
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Password</button>
        </form>
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

        function togglePassword(icon, field) {
            let input = document.getElementsByName(field)[0];
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>
