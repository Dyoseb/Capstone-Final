<?php 
    session_start();
    
    include 'db.php';

    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        header("Location: index.php");
        exit;
    }

    if ($_SESSION['role'] !== 'utility') {
        header("Location: unauthorized.php");
        exit;
    }

    $username = $_SESSION['username'];
    $email = $_SESSION['email'];
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $startYear = isset($_GET['startYear']) ? (int)$_GET['startYear'] : null;
    $endYear = isset($_GET['endYear']) ? (int)$_GET['endYear'] : null;

    $sql = "SELECT r.product_id, p.sku, p.name, r.quantity, r.date_restocked 
            FROM restock r 
            JOIN products p ON r.product_id = p.product_id 
            WHERE 1=1";

    $params = [];
    $types = "";

    // Debugging: Check received GET values
    // echo "<pre>"; print_r($_GET); echo "</pre>";

    if (!empty($search)) {
        $sql .= " AND (p.sku LIKE ? OR p.name LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "ss";
    }

    if (!empty($startYear)) {
        $sql .= " AND YEAR(r.date_restocked) >= ?";
        $params[] = $startYear;
        $types .= "i";
    }

    if (!empty($endYear)) {
        $sql .= " AND YEAR(r.date_restocked) <= ?";
        $params[] = $endYear;
        $types .= "i";
    }

    $sql .= " ORDER BY r.date_restocked DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $restockedProducts = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $restockedProducts[] = $row;
        }
    }
    $stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utility Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert CDN -->
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

        /* Make the table responsive */
        .table-responsive {
            max-height: 550px; /* Set a fixed height for the table container */
            overflow-y: auto; /* Enable vertical scrolling */
            overflow-x: auto; /* Enable horizontal scrolling if needed */
            width: 100%;
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

        <h2 class="text-center">Restocked Products</h2>

        <form method="GET" class="mb-3">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search SKU/Name:</label>
                    <input type="text" id="search" name="search" class="form-control" 
                            value="<?= htmlspecialchars($search) ?>" 
                            placeholder="Type to search...">
                </div>
                <div class="col-md-3">
                    <label for="startYear" class="form-label">Start Year:</label>
                    <input type="number" id="startYear" name="startYear" class="form-control" 
                            value="<?= $startYear ? $startYear : '' ?>" 
                            placeholder="YYYY" min="2000" max="2100">
                </div>
                <div class="col-md-3">
                    <label for="endYear" class="form-label">End Year:</label>
                    <input type="number" id="endYear" name="endYear" class="form-control" 
                            value="<?= $endYear ? $endYear : '' ?>" 
                            placeholder="YYYY" min="2000" max="2100">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="restocked_products.php" class="btn btn-secondary ms-2">Reset</a>
                </div>
            </div>
        </form>

        <div class="p-4 bg-white rounded shadow">
            <div class="table-responsive">
                <table class="table table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Date Restocked</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($restockedProducts)) {
                            foreach ($restockedProducts as $product) {
                                echo "<tr>
                                        <td>{$product['sku']}</td>
                                        <td>{$product['name']}</td>
                                        <td>{$product['quantity']}</td>
                                        <td>{$product['date_restocked']}</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>No restocked products found.</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#searchInput, #startYear, #endYear').on('input change', function () {
                let search = $('#searchInput').val().toLowerCase();
                let startYear = parseInt($('#startYear').val());
                let endYear = parseInt($('#endYear').val());
                
                $('#restockedTable tr').each(function () {
                    let sku = $(this).find('td:nth-child(1)').text().toLowerCase();
                    let name = $(this).find('td:nth-child(2)').text().toLowerCase();
                    let restockDate = $(this).find('td:nth-child(4)').text().trim();

                    // Ensure restockDate is in a valid format before extracting the year
                    let restockYear = restockDate ? new Date(restockDate).getFullYear() : null;

                    // Check search filter
                    let matchesSearch = sku.includes(search) || name.includes(search);
                    
                    // Check year filter
                    let matchesYear = (!startYear || (restockYear && restockYear >= startYear)) &&
                                    (!endYear || (restockYear && restockYear <= endYear));

                    if (matchesSearch && matchesYear) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });

    </script>

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