<?php 
    session_start();
    include 'db.php';

    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        header("Location: index.php"); // Redirect to login page if not logged in
        exit;
    }

    // Restrict access to only Midwives
    if ($_SESSION['role'] !== 'utility') {
        header("Location: unauthorized.php"); // Redirect to an unauthorized access page
        exit;
    }

    $username = $_SESSION['username'];
    $email = $_SESSION['email'];

    // Ensure the database connection is available
    if (!isset($conn)) {
        die("Database connection error. Please check db.php.");
    }

    // Fetch product quantities
    $productQuery = "SELECT name, quantity FROM products ORDER BY name";
    $productResult = $conn->query($productQuery);

    if (!$productResult) {
        die("Error fetching products: " . $conn->error);
    }

    $products = [];
    $quantities = [];

    while ($row = $productResult->fetch_assoc()) {
        $products[] = $row['name'];
        $quantities[] = $row['quantity'];
    }

    // Fetch restock trends
    $restockQuery = "SELECT DATE(date_restocked) as restock_date, SUM(quantity) as total_quantity 
                     FROM restock 
                     GROUP BY DATE(date_restocked) 
                     ORDER BY date_restocked";
    $restockResult = $conn->query($restockQuery);

    if (!$restockResult) {
        die("Error fetching restock data: " . $conn->error);
    }

    $dates = [];
    $restockQuantities = [];

    while ($row = $restockResult->fetch_assoc()) {
        $dates[] = $row['restock_date'];
        $restockQuantities[] = $row['total_quantity'];
    }

     // Fetch Low Stock (Threshold <10)
     $lowStockQuery = "SELECT name, quantity FROM products WHERE quantity > 0 AND quantity < 10 ORDER BY quantity ASC";
     $lowStockResult = $conn->query($lowStockQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utility Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="row">
            <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5>Current Inventory Levels</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="inventoryChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Line Chart (Restock Trends) -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5>Restock Trends Over Time</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="restockChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Low Stock Table -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5>⚠️ Low Stock (Below 10)</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $lowStockResult->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo $row['quantity']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <?php if ($lowStockResult->num_rows == 0) { echo "<p class='text-center'>✅ No low stock items.</p>"; } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inventory Bar Chart
        var ctx1 = document.getElementById('inventoryChart').getContext('2d');
        var inventoryChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($products); ?>,
                datasets: [{
                    label: 'Quantity Available',
                    data: <?php echo json_encode($quantities); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Restock Line Chart
        var ctx2 = document.getElementById('restockChart').getContext('2d');
        var restockChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Restocked Quantity',
                    data: <?php echo json_encode($restockQuantities); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
    </script>
     
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
