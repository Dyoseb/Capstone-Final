<?php 
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit;
}

// Restrict access to only Midwives
if ($_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php"); // Redirect to an unauthorized access page
    exit;
}

$username = $_SESSION['username'];
$email = $_SESSION['email'];

// Handle Add User Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $name = $_POST['username'];  // tbl_users uses "username"
    $email = $_POST['email'];
    $password = $_POST['password']; // Ensure password is received
    $role = $_POST['role'];
    $status = "Active";  // Set default account status

    // Insert into tbl_users with correct columns
    $stmt = $conn->prepare("INSERT INTO tbl_users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $role, $status);

    if ($stmt->execute()) {
        echo "<script>alert('User added successfully!'); window.location.href='manage_users.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
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
            font-size: 20px; /* Adjust as needed */
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

        /* start of toggle for password */

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

        /* Wrapper for password field and eye icon */
        .password-container {
            display: flex;
            align-items: center;
            width: 100%;
            border: 1px solid #ced4da; /* Match input border */
            border-radius: 5px; /* Rounded corners */
            background-color: white;
        }

        /* Password input field */
        .password-field {
            flex: 1; /* Take up remaining space */
            border: none;
            outline: none;
            padding: 8px;
            font-size: 16px;
        }

        /* Style for the eye icon */
        .toggle-password {
            cursor: pointer;
            color: #6c757d;
            font-size: 18px;
            padding: 5px;
            border-left: 1px solid #ced4da; /* Separator */
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px; /* Fixed width for the icon container */
            height: 100%;
        }

        /* Hover effect */
        .toggle-password:hover {
            color: #333;
        }

        /* Prevent text selection on icon */
        .toggle-password i {
            pointer-events: none;
        }

        .password-toggle {
            width: 35px;  /* Adjust width */
            height: 37.5px; /* Adjust height */
            font-size: 14px; /* Adjust icon size */
            padding: 5px; /* Reduce padding */
        }
        /* end of toggle for password */

        /* for management label */
        .fixed-heading {
            position: sticky;
            top: 70px; /* Adjust based on navbar height */
            background-color: white;
            z-index: 1000; /* Ensures it stays above content */
            padding: 10px 10px;
            display: inline-block;
            border-radius: 8px; /* Optional: Adds rounded corners */
            min-width: 250px; /* Adjust this value to control width */
            max-width: 400px; /* Optional: Restrict max width */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); /* Soft shadow effect */
        }

        /* Make the table responsive */
        .table-responsive {
            max-height: 550px; /* Set a fixed height for the table container */
            overflow-y: auto; /* Enable vertical scrolling */
            overflow-x: auto; /* Enable horizontal scrolling if needed */
            width: 100%;
        } 

        /* make the header row sticky */
        .table thead tr {
            position: sticky;
            top: 0;
            background-color: white; /* Ensure it stays visible */
            z-index: 10;
            box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.1); /* Optional shadow */
        }

        /* larger font for navbar brand */
        .navbar-brand {
            font-size: 20px; /* Adjust size as needed */
            font-weight: bold; /* Makes it stand out */
        }
        /* end of it */

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

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="container mt-5">
            <h2 class="text-center fixed-heading">User Management</h2>
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fa-solid fa-user-plus"></i> Add User</button>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="roleFilter" class="form-label">Filter by Role:</label>
                    <select id="roleFilter" class="form-select">
                        <option value="">All Roles</option>
                        <option value="Admin">Admin</option>
                        <option value="Utility">Utility</option>
                        <option value="Midwife">Midwife</option>
                        <option value="Doctor">Doctor</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="statusFilter" class="form-label">Filter by Status:</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="searchInput" class="form-label">Search Name/Email:</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Type to search...">
                </div>
            </div>
            <div class="p-4 bg-white rounded shadow">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="userTable">
                            <?php
                            $result = $conn->query("SELECT * FROM tbl_users");
                            while ($row = $result->fetch_assoc()) {
                                // Sanitize data to prevent HTML & JS injection issues
                                $userId = $row['user_id'];
                                $username = htmlspecialchars($row['username'], ENT_QUOTES);
                                $email = htmlspecialchars($row['email'], ENT_QUOTES);
                                $role = htmlspecialchars($row['role'], ENT_QUOTES);
                                $status = htmlspecialchars($row['status'], ENT_QUOTES);

                                echo "<tr id='user{$userId}'>
                                        <td>{$userId}</td>
                                        <td>{$username}</td>
                                        <td>{$email}</td>
                                        <td>{$role}</td>
                                        <td>{$status}</td>
                                        <td>
                                            <button class='btn btn-warning btn-sm' 
                                                onclick='editUser({$userId}, \"{$username}\", \"{$email}\", \"{$role}\", \"{$status}\")'>
                                                <i class='fas fa-edit'></i> Edit
                                            </button>
                                        </td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editUserForm">
                            <input type="hidden" id="editUserId" name="user_id">

                            <label for="editUsername" class="form-label">Username:</label>
                            <input type="text" class="form-control" id="editUsername" name="username" required>

                            <label for="editEmail" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>

                            <label for="editRole" class="form-label">Role:</label>
                            <select id="editRole" class="form-select mb-2" name="role" required>
                                <option value="" selected disabled>-- Select Role --</option>
                                <option value="Admin">Admin</option>
                                <option value="Utility">Utility</option>
                                <option value="Midwife">Midwife</option>
                                <option value="Doctor">Doctor</option>
                            </select>

                            <label for="editStatus" class="form-label">Status:</label>
                            <select id="editStatus" class="form-select mb-2" name="status" required>
                                <option value="" selected disabled>-- Select Status --</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="updateUser()">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add User Modal -->
        <div class="modal fade" id="addUserModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Add User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
                            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>

                            <select name="role" class="form-select mb-2" required>
                                <option value="" selected disabled>-- Select Role --</option>
                                <option value="Admin">Admin</option>
                                <option value="Utility">Utility</option>
                                <option value="Midwife">Midwife</option>
                                <option value="Doctor">Doctor</option>
                            </select>

                            <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control mb-2"
                                placeholder="Password" required
                                pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$"
                                title="Password must be at least 8 characters long and include at least one letter and one number.">
                                <button class="btn btn-outline-secondary password-toggle" type="button" onclick="togglePassword()">
                                    <i id="toggleIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="add_user" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="manageUser.js"></script>

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

        function togglePasswordVisibility() {
            let passwordInput = document.getElementById("password");
            let icon = document.querySelector(".toggle-password i");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        function togglePassword() {
            let password = document.getElementById("password");
            let icon = document.getElementById("toggleIcon");
            password.type = password.type === "password" ? "text" : "password";
            icon.classList.toggle("fa-eye-slash");
        }
    </script>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        
        function editUser(id, username, email, role, status) {
            document.getElementById("editUserId").value = id;
            document.getElementById("editUsername").value = username;
            document.getElementById("editEmail").value = email;

            // Set the role dropdown properly
            let roleDropdown = document.getElementById("editRole");
            for (let i = 0; i < roleDropdown.options.length; i++) {
                if (roleDropdown.options[i].value === role) {
                    roleDropdown.options[i].selected = true;
                    break;
                }
            }

            // Set the status dropdown properly
            let statusDropdown = document.getElementById("editStatus");
            for (let i = 0; i < statusDropdown.options.length; i++) {
                if (statusDropdown.options[i].value === status) {
                    statusDropdown.options[i].selected = true;
                    break;
                }
            }

            // Show the modal
            var editModal = new bootstrap.Modal(document.getElementById("editUserModal"));
            editModal.show();
        }


        function updateUser() {
            let formData = new FormData(document.getElementById("editUserForm"));

            fetch("update_user.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes("success")) {
                    Swal.fire({
                        icon: "success",
                        title: "Updated!",
                        text: "User details updated successfully!",
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK"
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Something went wrong!",
                        confirmButtonColor: "#d33",
                        confirmButtonText: "OK"
                    });
                }
            })
            .catch(error => {
                console.error("Error updating user:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Something went wrong!",
                    confirmButtonColor: "#d33",
                    confirmButtonText: "OK"
                });
            });
        }

    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const roleFilter = document.getElementById("roleFilter");
        const statusFilter = document.getElementById("statusFilter");
        const searchInput = document.getElementById("searchInput");
        const userTable = document.getElementById("userTable");
        const rows = userTable.getElementsByTagName("tr");

        function filterTable() {
            const roleValue = roleFilter.value.toLowerCase();
            const statusValue = statusFilter.value.toLowerCase();
            const searchValue = searchInput.value.toLowerCase();

            for (let i = 0; i < rows.length; i++) {
                let cols = rows[i].getElementsByTagName("td");
                if (cols.length > 0) {
                    let name = cols[1].textContent.toLowerCase();
                    let email = cols[2].textContent.toLowerCase();
                    let role = cols[3].textContent.toLowerCase();
                    let status = cols[4].textContent.toLowerCase();

                    let roleMatch = roleValue === "" || role === roleValue;
                    let statusMatch = statusValue === "" || status === statusValue;
                    let searchMatch = searchValue === "" || name.includes(searchValue) || email.includes(searchValue);

                    if (roleMatch && statusMatch && searchMatch) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }

        roleFilter.addEventListener("change", filterTable);
        statusFilter.addEventListener("change", filterTable);
        searchInput.addEventListener("keyup", filterTable);
    });
    </script>

</body>
</html>
