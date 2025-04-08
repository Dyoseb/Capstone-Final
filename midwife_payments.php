<?php
session_start();
include 'db.php';

// Check authentication
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'midwife') {
    header("Location: index.php");
    exit;
}

// Fetch all transactions with patient info
$sql = "SELECT t.*, m.patient_name, m.record_date 
        FROM transactions t
        JOIN medicalrecords m ON t.record_id = m.record_id
        ORDER BY t.transaction_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 60.9px;
            left: -250px;
            background-color: #8CB150;
            padding-top: 20px;
            overflow-y: auto;
            transition: left 0.3s ease;
            z-index: 1040;
        }
        .sidebar.open { left: 0; }
        .sidebar.closed { left: -250px; }
        .main-content {
            padding: 20px;
            margin-top: 56px;
            transition: margin-left 0.3s ease;
        }
        @media (min-width: 992px) {
            .sidebar { left: 0; }
            .sidebar.closed { left: -250px; }
            .main-content { margin-left: 250px; }
            .main-content.expanded { margin-left: 0; }
        }
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
        .badge-paid { background-color: #28a745; }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-partial { background-color: #fd7e14; }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <button class="toggle-btn" id="sidebarToggle">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand ms-2" href="#">
                <img src="Images/amorganda logo.png" alt="Logo" style="width: 35px; height: 35px; border-radius: 50%; margin-right: 10px;">
                Amorganda Lying-in Clinic
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="ms-2"><?= htmlspecialchars($_SESSION['username']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
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
                <a class="nav-link" href="midwife_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="midwife_appointment.php"><i class="fas fa-calendar-plus"></i> Appointments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="midwife_check-up-record.php"><i class="fas fa-clipboard-list"></i> Check-up Records</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="midwife_intrapartum-records.php"><i class="fa-solid fa-clipboard"></i> B2 Records</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="midwife_payments.php"><i class="fas fa-money-bill-wave"></i> Payments</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-money-bill-wave me-2"></i>Payment Transactions</h2>
            <a href="midwife_create_payment.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Payment
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Transaction ID</th>
                                <th>Patient Name</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): 
                                    $professional_fees = json_decode($row['professional_fees'], true);
                                    $total = $row['room_board'] + $row['drugs_medicine'] + 
                                             $row['delivery_room_fee'] + $row['supplies'];
                                ?>
                                <tr>
                                    <td>#<?= $row['transaction_id'] ?></td>
                                    <td><?= htmlspecialchars($row['patient_name']) ?></td>
                                    <td><?= date('M j, Y h:i A', strtotime($row['transaction_date'])) ?></td>
                                    <td>₱<?= number_format($total, 2) ?></td>
                                    <td>
                                        <span class="badge rounded-pill badge-<?= $row['payment_status'] ?>">
                                            <?= ucfirst($row['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="midwife_view_receipt.php?id=<?= $row['transaction_id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-receipt"></i> Receipt
                                        </a>
                                        <button class="btn btn-sm btn-outline-secondary edit-payment" 
                                                data-id="<?= $row['transaction_id'] ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-money-bill-wave fa-2x mb-3"></i>
                                        <p>No payment transactions found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Payment Modal -->
    <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body" id="editPaymentContent">
                    Loading payment details...
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebar').classList.toggle('closed');
            document.getElementById('mainContent').classList.toggle('expanded');
        });

        // Edit payment handler
        $(document).on('click', '.edit-payment', function() {
            const transactionId = $(this).data('id');
            $('#editPaymentContent').load('midwife_edit_payment.php?id=' + transactionId, function() {
                $('#editPaymentModal').modal('show');
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>