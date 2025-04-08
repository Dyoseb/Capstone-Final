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

        // Check authentication
        if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'midwife') {
            header("Location: index.php");
            exit;
        }

        // Handle patient search AJAX request
        if (isset($_POST['search_patients'])) {
            $search = $conn->real_escape_string($_POST['search_patients']);
            $query = "SELECT record_id, patient_name FROM medicalrecords 
                    WHERE patient_name LIKE '%$search%' 
                    ORDER BY record_date DESC LIMIT 10";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="patient-result" data-id="'.$row['record_id'].'">';
                    echo htmlspecialchars($row['patient_name']);
                    echo '</div>';
                }
            } else {
                echo '<div class="no-results">No patients found</div>';
            }
            exit; // Stop further execution for AJAX requests
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $total_amount = (float)$_POST['total_amount'];
            
            // Get single professional data (not arrays anymore)
            $professional_name = $_POST['professional_name'] ?? '';
            $professional_fees = $_POST['professional_fees'] ?? 0;

            // Insert into database
            $stmt = $conn->prepare("INSERT INTO transactions (
                record_id, transaction_date, room_board, drugs_medicine, 
                delivery_room_fee, supplies, professional_name, professional_fees, total_amount, 
                payment_status, amount_paid, note
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param(
                "isdddddsddss",
                $_POST['record_id'],
                $_POST['transaction_date'],
                $_POST['room_board'],
                $_POST['drugs_medicine'],
                $_POST['delivery_room_fee'],
                $_POST['supplies'],
                $professional_name,
                $professional_fees,
                $total_amount,
                $_POST['payment_status'],
                $_POST['amount_paid'],
                $_POST['note']
            );

            if ($stmt->execute()) {
                $transaction_id = $conn->insert_id;
                header("Location: midwife_view_receipt.php?id=$transaction_id");
                exit;
            } else {
                $error = "Error saving payment: " . $conn->error;
            }
        }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Midwife Dashboard</title>
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

            .card {
                box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            }
            .form-control, .form-select {
                border-radius: 0.35rem;
            }

            .submenu {
                transition: max-height 0.3s ease-in-out;
                overflow: hidden;
            }

            .submenu {
                max-height: 0px;
                overflow: hidden;
                transition: max-height 0.4s ease-in-out;
                padding-left: 20px;
            }

            .arrow-icon {
                transition: transform 0.3s ease-in-out;
            }

            .rotate {
                transform: rotate(180deg);
            }

            .professional-select {
                width: 100%;
            }

            .search-container {
                position: relative;
            }

            .search-results {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #ddd;
                border-top: none;
                border-radius: 0 0 4px 4px;
                max-height: 200px;
                overflow-y: auto;
                z-index: 1000;
                display: none;
            }

            .patient-result {
                padding: 8px 12px;
                cursor: pointer;
            }

            .patient-result:hover {
                background-color: #f8f9fa;
            }

            .no-results {
                padding: 8px 12px;
                color: #6c757d;
                font-style: italic;
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
                        <i class="fas fa-note-medical"></i> Check-up Form
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="midwife_check-up-record.php">
                        <i class="fas fa-clipboard-list"></i> Check-up Records
                    </a>
                </li>
                <!-- Intrapartum Record Parent Item -->
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-between" href="#" id="intrapartumToggle">
                        <span><i class="fa-solid fa-book-medical"></i> Intrapartum Record</span>
                        <i class="fas fa-chevron-down arrow-icon"></i>
                    </a>
                    <ul class="submenu" id="intrapartumSubmenu">
                        <li>
                            <a class="nav-link" href="midwife_intrapartum-form.php">
                                <i class="fa-solid fa-file-circle-plus"></i> B2 Record
                            </a>
                        </li>
                        <li>
                            <a class="nav-link" href="midwife_intrapartum-records.php">
                                <i class="fa-solid fa-clipboard"></i> Saved B2 Records
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="main-content" id="mainContent">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-money-bill-wave me-2"></i>Create New Payment</h2>
                <a href="midwife_payments.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Payments
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form id="paymentForm" method="POST">
                        <!-- Patient Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Search Patient</label>
                                    <div class="search-container">
                                        <input type="text" class="form-control" id="patientSearch" placeholder="Start typing patient name..." autocomplete="off">
                                        <input type="hidden" name="record_id" id="record_id" required>
                                        <div id="patientResults" class="search-results"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Transaction Date</label>
                                    <input type="datetime-local" class="form-control" name="transaction_date" 
                                        value="<?= isset($_POST['transaction_date']) ? $_POST['transaction_date'] : date('Y-m-d\TH:i') ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Hospital Charges -->
                        <div class="border p-3 mb-4 rounded">
                            <h5 class="mb-3"><i class="fas fa-hospital me-2"></i>Clinic Charges</h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Room & Board</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control hospital-charge" name="room_board" 
                                            min="0" step="0.01" value="<?= isset($_POST['room_board']) ? $_POST['room_board'] : '1500.00' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Drugs & Medicine</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control hospital-charge" name="drugs_medicine" 
                                            min="0" step="0.01" value="<?= isset($_POST['drugs_medicine']) ? $_POST['drugs_medicine'] : '0.00' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Delivery Room</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control hospital-charge" name="delivery_room_fee" 
                                            min="0" step="0.01" value="<?= isset($_POST['delivery_room_fee']) ? $_POST['delivery_room_fee'] : '3000.00' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Supplies</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control hospital-charge" name="supplies" 
                                            min="0" step="0.01" value="<?= isset($_POST['supplies']) ? $_POST['supplies'] : '500.00' ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Fees -->
                        <div class="border p-3 mb-4 rounded">
                            <h5 class="mb-3"><i class="fas fa-user-md me-2"></i>Professional Fees</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Doctor's Name</label>
                                    <input type="text" class="form-control" name="professional_name" 
                                        placeholder="Dr. Juan Dela Cruz" value="<?= isset($_POST['professional_name']) ? $_POST['professional_name'] : '' ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fee Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" name="professional_fees" 
                                            min="0" step="0.01" value="<?= isset($_POST['professional_fees']) ? $_POST['professional_fees'] : '500.00' ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Amount -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="text" class="form-control" id="total_amount_display" value="0.00" readonly>
                                        <input type="hidden" name="total_amount" id="total_amount">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="border p-3 mb-4 rounded bg-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Payment Status</label>
                                        <select class="form-select" name="payment_status" required>
                                            <option value="pending" <?= isset($_POST['payment_status']) && $_POST['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="partial" <?= isset($_POST['payment_status']) && $_POST['payment_status'] === 'partial' ? 'selected' : '' ?>>Partial Payment</option>
                                            <option value="paid" <?= isset($_POST['payment_status']) && $_POST['payment_status'] === 'paid' ? 'selected' : '' ?>>Fully Paid</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Amount Paid</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" name="amount_paid" 
                                                min="0" step="0.01" value="<?= isset($_POST['amount_paid']) ? $_POST['amount_paid'] : '0.00' ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="note" rows="2" 
                                        placeholder="Any additional notes..."><?= isset($_POST['note']) ? htmlspecialchars($_POST['note']) : '' ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="reset" class="btn btn-secondary">Reset</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
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

            document.addEventListener("DOMContentLoaded", function () {
                let toggleButton = document.getElementById("intrapartumToggle");
                let submenu = document.getElementById("intrapartumSubmenu");
                let arrowIcon = toggleButton.querySelector(".arrow-icon");

                // Initialize height to 0 so it smoothly opens
                submenu.style.maxHeight = "0px";
                submenu.style.overflow = "hidden";

                toggleButton.addEventListener("click", function (e) {
                    e.preventDefault();

                    if (submenu.style.maxHeight === "0px") {
                        submenu.style.maxHeight = submenu.scrollHeight + "px";
                        arrowIcon.classList.add("rotate");
                    } else {
                        submenu.style.maxHeight = "0px";
                        arrowIcon.classList.remove("rotate");
                    }
                });
            });
        </script>

    <script>
        // Patient search functionality
        $(document).ready(function() {
            $('#patientSearch').on('input', function() {
                const searchTerm = $(this).val().trim();
                if (searchTerm.length >= 2) {
                    $.ajax({
                        url: window.location.href,
                        method: 'POST',
                        data: { search_patients: searchTerm },
                        success: function(response) {
                            $('#patientResults').html(response).show();
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                        }
                    });
                } else {
                    $('#patientResults').hide();
                }
            });

            // Handle click on search result
            $(document).on('click', '.patient-result', function() {
                const recordId = $(this).data('id');
                const patientName = $(this).text();
                $('#patientSearch').val(patientName);
                $('#record_id').val(recordId);
                $('#patientResults').hide();
            });

            // Hide results when clicking elsewhere
            $(document).click(function(e) {
                if (!$(e.target).closest('.search-container').length) {
                    $('#patientResults').hide();
                }
            });
        });
    </script>

    
<script>

            // Function to calculate total amount
            function calculateTotal() {
                let total = 0;
                
                // Add hospital charges
                total += parseFloat($('[name="room_board"]').val()) || 0;
                total += parseFloat($('[name="drugs_medicine"]').val()) || 0;
                total += parseFloat($('[name="delivery_room_fee"]').val()) || 0;
                total += parseFloat($('[name="supplies"]').val()) || 0;
                
                // Add professional fees
                $('[name="professional_fees[]"]').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });
                
                // Update the display
                $('#total_amount_display').val(total.toFixed(2));
                $('#total_amount').val(total.toFixed(2));
            }

            // Calculate total when any input changes
            $(document).on('input', '[name="room_board"], [name="drugs_medicine"], [name="delivery_room_fee"], [name="supplies"], [name="professional_fees[]"]', calculateTotal);

            // Initialize on page load
            $(document).ready(function() {
                updateRemoveButtons();
                calculateTotal();
            });
        </script>
    </body>
    </html>
    <?php $conn->close(); ?>