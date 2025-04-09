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

        <link rel="stylesheet" href="styles\midwife_create.css">
    </head>
    <body>
        <!-- <div id="professional_name1" value=<?php echo $_POST['professional_name']; ?> hidden></div> -->
        <!-- Navigation Bar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
            <div class="container-fluid">
                <button class="toggle-btn" id="sidebarToggle">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <!-- Admin Dashboard brand -->
                <a class="navbar-brand ms-2" href="#">
                    <img src="Images/amorganda logo.png" alt="Logo" style="width: 35px; height: 35px; border-radius: 50%; margin-right: 10px;">
                    Amorganda Lying-in Clinic
                </a>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="ms-2"><?= htmlspecialchars($username); ?></span>
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
                        <li><a class="nav-link" href="midwife_intrapartum-form.php"><i class="fa-solid fa-file-circle-plus"></i> B2 Record</a></li>
                        <li><a class="nav-link" href="midwife_intrapartum-records.php"><i class="fa-solid fa-clipboard"></i> Saved B2 Records</a></li>
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
                                    <input type="datetime-local" class="form-control" name="transaction_date" id="transaction_date" value="<?= isset($_POST['transaction_date']) ? $_POST['transaction_date'] : date('Y-m-d\TH:i') ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Clinic Charges -->
                        <div class="border p-3 mb-4 rounded">
                            <h5 class="mb-3"><i class="fas fa-hospital me-2"></i> Clinic Charges</h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Room & Board</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control hospital-charge" name="room_board" id="room_board" min="0" step="0.01" value="<?= '1500.00' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Drugs & Medicine</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control hospital-charge" name="drugs_medicine" id="drugs_medicine" min="0" step="0.01" value="<?=  '0.00' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Delivery Room</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control hospital-charge" name="delivery_room_fee" id="delivery_room_fee" min="0" step="0.01" value=" '3000.00' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Supplies</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control hospital-charge" name="supplies" id="supplies" min="0" step="0.01" value="<?= '500.00' ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Fees -->
                        <div class="border p-3 mb-4 rounded">
                            <h5 class="mb-3"><i class="fas fa-user-md me-2"></i> Professional Fees</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Doctor's Name</label>
                                    <input type="text" class="form-control" name="professional_name" id="professional_name" placeholder="Dr. Juan Dela Cruz" value="<?= '' ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fee Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" name="professional_fees" id="professional_fees" min="0" step="0.01" value="<?=  '500.00' ?>" required>
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
                                        <input type="text" name="total_amount" id="total_amount" hidden>
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
                                        <select class="form-select" name="payment_status" id="payment_status" required>
                                            <option value="pending" >Pending</option>
                                            <option value="partial" >Partial Payment</option>
                                            <option value="paid" >Fully Paid</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Amount Paid</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" name="amount_paid" id="amount_paid" min="0" step="0.01" value="<?= $_POST['amount_paid'] ?? '0.00' ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="note" id="note" rows="2" placeholder="Any additional notes..."><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="reset" class="btn btn-secondary">Reset</button>
                            <button type="button" id="dataSubmit" class="btn btn-primary">
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
        </script>

        <!-- Intrapartum Submenu Toggle Script -->
        <script>
        $(document).on('click', '#dataSubmit', function() {
            var isSubmitting = true;

            const recordID = document.getElementById('record_id').value;
            const transactionDate = document.getElementById('transaction_date').value;
            const roomBoard = document.getElementById('room_board').value;
            const drugsMedicine = document.getElementById('drugs_medicine').value;
            const deliveryRoomFee = document.getElementById('delivery_room_fee').value;
            const supplies = document.getElementById('supplies').value;
            const professionalName = document.getElementById('professional_name').value;
            const professionalFees = document.getElementById('professional_fees').value;
            const totalAmount = document.getElementById('total_amount').value;
            const paymentStatus = document.getElementById('payment_status').value;
            const amountPaid = document.getElementById('amount_paid').value;
            const note = document.getElementById('note').value;

            console.log("Form Data:", {
                recordID, transactionDate, roomBoard, drugsMedicine, deliveryRoomFee, supplies, 
                professionalName, professionalFees, totalAmount, paymentStatus, amountPaid, note
            });

            const fd = new FormData();
            fd.append("pick", "1");
            fd.append("record_id", recordID);
            fd.append("transaction_date", transactionDate);
            fd.append("room_board", roomBoard);
            fd.append("drugs_medicine", drugsMedicine);
            fd.append("delivery_room_fee", deliveryRoomFee);
            fd.append("supplies", supplies);
            fd.append("professional_fees", professionalFees);
            fd.append("professional_name", professionalName);
            fd.append("total_amount", totalAmount);
            fd.append("payment_status", paymentStatus);
            fd.append("amount_paid", amountPaid);
            fd.append("note", note);

            $.ajax({
                url: "codes/includes/admin_control.php",
                data: fd,
                processData: false,
                contentType: false,
                type: "POST",
                success: function(result) {
                    if ($.trim(result) !== "0") {
                        console.log("Success: Transaction inserted!");
                        // Optionally redirect after success
                        // setTimeout(() => (window.location.href = "trainingList"), 2000);
                    } else {
                        console.log("Error: Failed to insert transaction");
                        // Optionally re-enable button here
                        $("#AQApproved").prop("disabled", false);
                    }
                    isSubmitting = false;
                },
                error: function() {
                    console.log("Error: An error occurred during the AJAX request");
                    isSubmitting = false;
                }
            });
        });

            document.addEventListener("DOMContentLoaded", function () {
                let toggleButton = document.getElementById("intrapartumToggle");
                let submenu = document.getElementById("intrapartumSubmenu");
                let arrowIcon = toggleButton.querySelector(".arrow-icon");

                // Initialize submenu height to 0
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

        <!-- Patient Search Functionality -->
        <!-- <script>
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
        </script> -->

        <!-- Calculate Total Amount -->
        <script>
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
                calculateTotal();
            });
        </script>
    </body>

    </html>
    <?php $conn->close(); ?>