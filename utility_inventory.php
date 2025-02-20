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
    
    $alertMessage = "";

    // ------------------------ UPDATE PRODUCT ------------------------
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
        if (isset($_POST['id']) && isset($_POST['sku']) && isset($_POST['name']) && isset($_POST['quantity']) && isset($_POST['status']) && isset($_POST['price']) && isset($_POST['expiration'])) {
            $id = $_POST['id'];
            $sku = $_POST['sku'];
            $name = $_POST['name'];
            $quantity = $_POST['quantity'];
            $status = $_POST['status'];
            $price = $_POST['price'];
            $expiration = $_POST['expiration'];

            // Check if SKU already exists in another record
            $checkStmt = $conn->prepare("SELECT product_id FROM products WHERE sku = ? AND product_id != ?");
            $checkStmt->bind_param("si", $sku, $id);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                // SKU already exists, show error
                echo "<script>Swal.fire('Error!', 'SKU already exists for another product. Please choose a different SKU.', 'error');</script>";
            } else {
                // SKU is unique, proceed with update
                $stmt = $conn->prepare("UPDATE products SET sku=?, name=?, quantity=?, price=?, expiration=? WHERE product_id=?");
                $stmt->bind_param("ssiisi", $sku, $name, $quantity, $price, $expiration, $id);

                if ($stmt->execute()) {
                    echo "<script>Swal.fire('Success!', 'Product updated successfully!', 'success').then(() => { window.location.href='utility_inventory.php'; });</script>";
                } else {
                    echo "<script>Swal.fire('Error!', 'Error updating product: " . $stmt->error . "', 'error');</script>";
                }
                $stmt->close();
            }
            $checkStmt->close();
        } else {
            echo "<script>Swal.fire('Error!', 'Invalid input. Please fill all fields.', 'error');</script>";
        }
    }

    // ------------------------ ARCHIVE PRODUCT ------------------------
    if (isset($_GET['archive_id'])) {
        $id = $_GET['archive_id'];
        $stmt = $conn->prepare("UPDATE products SET status='Archived' WHERE product_id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Product archived successfully!'); window.location.href='utility_inventory.php';</script>";
        } else {
            echo "<script>alert('Error archiving product: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }

    // ------------------------ AJAX SEARCH & FILTER ------------------------
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["fetch"])) {
        $search = isset($_POST["search"]) ? trim($_POST["search"]) : '';
        $status = isset($_POST["status"]) ? trim($_POST["status"]) : '';
    
        $sql = "SELECT * FROM products WHERE 1=1";
    
        if (!empty($search)) {
            $sql .= " AND (sku LIKE ? OR name LIKE ?)";
            $search = "%$search%";
        }
        
        if (!empty($status)) {
            $sql .= " AND status = ?";
        }
    
        $stmt = $conn->prepare($sql);
    
        if (!empty($search) && !empty($status)) {
            $stmt->bind_param("sss", $search, $search, $status);
        } elseif (!empty($search)) {
            $stmt->bind_param("ss", $search, $search);
        } elseif (!empty($status)) {
            $stmt->bind_param("s", $status);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['sku']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['quantity']}</td>
                        <td>{$row['status']}</td>
                        <td>{$row['price']}</td>
                        <td>{$row['expiration']}</td>
                        <td>
                            <button class='btn btn-warning btn-sm edit-btn' 
                                data-id='{$row['product_id']}' data-name='{$row['name']}'
                                data-sku='{$row['sku']}' data-quantity='{$row['quantity']}' 
                                data-status='{$row['status']}' data-price='{$row['price']}' 
                                data-expiration='{$row['expiration']}'>
                                Edit
                            </button>
                            <button class='btn btn-primary btn-sm use-btn' 
                                data-id='{$row['product_id']}' data-name='{$row['name']}'>
                                Use
                             </button>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='5' class='text-center'>No products found.</td></tr>";
        }
        exit;
    }

    // Fetch patients from medicalrecords table
    $patients = [];
    $patientQuery = "SELECT record_id, patient_name FROM medicalrecords";
    $result = $conn->query($patientQuery);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $patients[] = $row;
        }
    }
    
    // ------------------------ USE PRODUCT ------------------------
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['use_product'])) {
        if (isset($_POST['id'], $_POST['patient_id'], $_POST['date_used'], $_POST['quantity_used'], $_POST['usage_notes'])) {
            $id = $_POST['id'];
            $patient_id = $_POST['patient_id'];
            $date_used = $_POST['date_used'];
            $quantity_used = $_POST['quantity_used'];
            $usage_notes = $_POST['usage_notes'];

            // Get current product quantity
            $stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($current_quantity);
            $stmt->fetch();
            $stmt->close();

            if ($current_quantity === null) {
                echo "<script>Swal.fire('Error!', 'Product not found.', 'error');</script>";
            } elseif ($current_quantity < $quantity_used) {
                echo "<script>Swal.fire('Error!', 'Insufficient stock!', 'error');</script>";
            } else {
                // Insert into patient_used_products table
                $stmt = $conn->prepare("INSERT INTO patient_used_products (patient_id, product_id, date_used, quantity_used, usage_notes) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisis", $patient_id, $id, $date_used, $quantity_used, $usage_notes);

                if ($stmt->execute()) {
                    // Deduct the quantity from products table
                    $new_quantity = $current_quantity - $quantity_used;
                    $stmt = $conn->prepare("UPDATE products SET quantity = ? WHERE product_id = ?");
                    $stmt->bind_param("ii", $new_quantity, $id);
                    $stmt->execute();
                    $stmt->close();

                    echo "<script>Swal.fire('Success!', 'Product usage recorded and stock updated!', 'success').then(() => { window.location.href='utility_inventory.php'; });</script>";
                } else {
                    echo "<script>Swal.fire('Error!', 'Error recording usage.', 'error');</script>";
                }
            }
        } else {
            echo "<script>Swal.fire('Error!', 'Invalid input. Please fill all fields.', 'error');</script>";
        }
    }

    // ------------------------ RESTOCK PRODUCT ------------------------
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['restock_product'])) {
        if (isset($_POST['product_id'], $_POST['restock_quantity'], $_POST['date_restocked'])) {
            $product_id = $_POST['product_id'];
            $restock_quantity = $_POST['restock_quantity'];
            $date_restocked = $_POST['date_restocked'];

            // Insert restock record into the restock table
            $stmt = $conn->prepare("INSERT INTO restock (product_id, quantity, date_restocked) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $product_id, $restock_quantity, $date_restocked);

            if ($stmt->execute()) {
                // Update the quantity in the products table
                $updateStmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE product_id = ?");
                $updateStmt->bind_param("ii", $restock_quantity, $product_id);
                $updateStmt->execute();
                $updateStmt->close();

                echo "<script>Swal.fire('Success!', 'Product restocked successfully!', 'success').then(() => { window.location.href='utility_inventory.php'; });</script>";
            } else {
                echo "<script>Swal.fire('Error!', 'Error updating product stock.', 'error');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>Swal.fire('Error!', 'Invalid input. Please select a product and enter a quantity.', 'error');</script>";
        }
    }

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

        <h2 class="text-center mb-4">Products Inventory</h2>
        
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="searchInput" class="form-label">Search SKU/Name:</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Type to search...">
            </div>

            <div class="col-md-4">
                <label for="statusFilter" class="form-label">Filter by Status:</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Archived">Archived</option>
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">Add New Product</button>
                <button class="btn btn-info ms-2" data-bs-toggle="modal" data-bs-target="#restockProductModal" style="color: white;">Restock</button>
            </div>
        </div>
        
        <div class="p-4 bg-white rounded shadow">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Expiration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productTable">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="text" name="price" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expiration Date</label>
                            <input type="date" name="expiration" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Product Modal  -->
    <div class="modal fade" id="editProductModal" tabindex="-1"> 
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        
                        <div class="mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" id="edit-sku" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="edit-name" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" id="edit-quantity" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="text" name="price" id="edit-price" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Expiration Date</label>
                            <input type="date" name="expiration" id="edit-expiration" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit-status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Archived">Archived</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="submit" name="update_product" class="btn btn-warning">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Use Product Modal -->
    <div class="modal fade" id="useProductModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Use Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="use-product-id">

                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" id="use-product-name" class="form-control" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Patient</label>
                            <select name="patient_id" class="form-select" required>
                                <option value="" selected disabled>-- Select Patient --</option>
                                <?php foreach ($patients as $patient) {
                                    echo "<option value='{$patient['record_id']}'>{$patient['patient_name']}</option>";
                                } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date Used</label>
                            <input type="date" name="date_used" id="use-date" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Quantity Used</label>
                            <input type="number" name="quantity_used" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Usage Notes</label>
                            <textarea name="usage_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="use_product" class="btn btn-primary">Confirm Use</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Restock Product Modal -->
    <div class="modal fade" id="restockProductModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Restock Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Product</label>
                            <select name="product_id" class="form-select" required>
                                <option value="" selected disabled>-- Select Product --</option>
                                <?php 
                                    $query = "SELECT product_id, name FROM products";
                                    $result = $conn->query($query);
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['product_id']}'>{$row['name']}</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="restock_quantity" class="form-control" required>
                        </div>
                        <!-- Hidden Date Input (Auto-filled with current date) -->
                        <input type="hidden" name="date_restocked" id="restock-date">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="restock_product" class="btn btn-info">Confirm Restock</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // edit 
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".edit-btn").forEach(button => {
                button.addEventListener("click", function() {
                    document.getElementById("edit-id").value = this.getAttribute("data-id");
                    document.getElementById("edit-sku").value = this.getAttribute("data-sku");
                    document.getElementById("edit-name").value = this.getAttribute("data-name");
                    document.getElementById("edit-quantity").value = this.getAttribute("data-quantity");
                    document.getElementById("edit-status").value = this.getAttribute("data-status");
                    document.getElementById("edit-price").value = this.getAttribute("data-price");
                    document.getElementById("edit-expiration").value = this.getAttribute("data-expiration");  // Fetch status
                });
            });
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            fetchProducts(); // Fetch products on page load

            function fetchProducts() {
                let search = $("#searchInput").val().trim();
                let status = $("#statusFilter").val().trim();

                $.ajax({
                    url: window.location.href,
                    method: "POST",
                    data: { search: search, status: status, fetch: true },
                    success: function (response) {
                        $("#productTable").html(response);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", error);
                    }
                });
            }

            // Attach event listener to search & filter inputs
            $("#searchInput, #statusFilter").on("input change", function () {
                fetchProducts();
            });

            // Use event delegation for dynamically added buttons
            $(document).on("click", ".edit-btn", function () {
                $("#edit-id").val($(this).data("id"));
                $("#edit-sku").val($(this).data("sku"));
                $("#edit-name").val($(this).data("name"));
                $("#edit-quantity").val($(this).data("quantity"));
                $("#edit-status").val($(this).data("status"));
                $("#edit-price").val($(this).data("price"));
                $("#edit-expiration").val($(this).data("expiration"));

                $("#editProductModal").modal("show");
            });

            $(document).on("click", ".use-btn", function () {
                $("#use-product-id").val($(this).data("id"));
                $("#use-product-name").val($(this).data("name"));
                $("#useProductModal").modal("show");
            });
        });

        $(document).on("click", ".use-btn", function () {
            $("#use-product-id").val($(this).data("id"));
            $("#use-product-name").val($(this).data("name"));

            // Get today's date in YYYY-MM-DD format
            let today = new Date().toISOString().split('T')[0];
            $("#use-date").val(today);

            $("#useProductModal").modal("show");
        });

        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("restock-date").value = new Date().toISOString().split('T')[0];
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
