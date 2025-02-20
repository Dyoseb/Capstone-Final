<?php 
    session_start();

    include 'db.php';

    include 'appointment.php';

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['patientName'])) {
        $full_name = $_POST['patientName'];
        $contact_number = $_POST['contactNumber'];
        $address = $_POST['patientAddress'];
        $visit_date = $_POST['selectedDate'];
        $visit_time = $_POST['timeSlot'];
    
        // Convert date format from MM/DD/YYYY to YYYY-MM-DD
        $visit_date = date("Y-m-d", strtotime($visit_date));
    
        // Insert into database
        $sql = "INSERT INTO appointments (full_name, contact_number, address, visit_date, visit_time, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $full_name, $contact_number, $address, $visit_date, $visit_time);
    
        if ($stmt->execute()) {
            echo json_encode(["success" => "Appointment booked successfully"]);
        } else {
            echo json_encode(["error" => "Failed to book appointment"]);
        }
    
        $stmt->close();
        $conn->close();
        exit;
    }

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

    // **Set Philippine Time Zone**
    date_default_timezone_set('Asia/Manila');

    // Get current month and year
    $month = isset($_GET['month']) ? (int) $_GET['month'] : date('m');
    $year = isset($_GET['year']) ? (int) $_GET['year'] : date('Y');

    if (isset($_GET['action'])) {
        if ($_GET['action'] === "prev") {
            $month--;
            if ($month < 1) {
                $month = 12;
                $year--;
            }
        } elseif ($_GET['action'] === "next") {
            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }
        }
    }

    // Generate first day of the month and total days
    $first_day_of_month = strtotime("$year-$month-01");
    $start_day = date('w', $first_day_of_month);
    $days_in_month = date('t', $first_day_of_month);
    $month_name = date('F', $first_day_of_month);

    // Get current date in Philippine time
    $current_date = date("m/d/Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Midwife Dashboard</title>
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

        /* Calendar styles */
        .calendar td {
            text-align: center;
            height: 60px;
            vertical-align: middle;
        }

        /* Make calendar fit on smaller screens */
        .table-responsive { 
            overflow-x: auto; 
        }

        /* Reduce button size for mobile */
        .calendar button {
            font-size: 10px; 
            padding: 4px 6px;
        }

        /* Reduce font size for smaller screens */
        @media (max-width: 768px) {
            .calendar th, .calendar td {
                font-size: 10px;
                padding: 5px;
            }

            .calendar button {
                font-size: 8px;
                padding: 3px 5px;
            }
        }

        .slot-label {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-top: 5px;
        }

        .calendar-div {
            background-color: white; /* White background */
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2); /* Soft shadow */
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
        <h2 class="text-center">Appointment Scheduling</h2>

    <div class="d-flex justify-content-between">
        <a href="?month=<?= $month ?>&year=<?= $year ?>&action=prev" class="btn btn-primary">Previous</a>
        <h3><?= $month_name . ' ' . $year ?></h3>
        <a href="?month=<?= $month ?>&year=<?= $year ?>&action=next" class="btn btn-primary">Next</a>
    </div>
    
    <div class="calendar-div">
        <table class="table table-bordered calendar mt-3">
            <thead>
                <tr style="background-color: rgba(0, 100, 0, 0.8);">
                    <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $day = 1;
                $cell_count = 0;
                $current_date = date("Y-m-d"); // Get today's date in YYYY-MM-DD format

                echo "<tr>";

                // Add empty cells for alignment
                for ($i = 0; $i < $start_day; $i++) {
                    echo "<td></td>";
                    $cell_count++;
                }

                // Generate calendar days
                for ($day = 1; $day <= $days_in_month; $day++) {
                    $date_str = sprintf("%04d-%02d-%02d", $year, $month, $day); // Format YYYY-MM-DD

                    // Fetch the count of booked slots for the date
                    $sql = "SELECT COUNT(*) as booked_count FROM appointments WHERE visit_date = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $date_str);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $booked_slots_count = $row['booked_count'];

                    // Define total slots per day
                    $total_slots_per_day = 15;
                    $available_slots = $total_slots_per_day - $booked_slots_count;

                    echo "<td><div><strong>$day</strong></div>";

                    // If the date is in the past, disable the button
                    if ($date_str < $current_date) {
                        echo "<button class='btn btn-secondary mt-1 btn-sm' disabled>Unavailable</button>";
                    } else {
                        echo "<button class='btn btn-success mt-1 btn-sm openModal' data-date='$date_str'>Available</button>";
                        echo "<div class='slot-label'>Slots: $available_slots</div>"; // Show available slots
                    }

                    echo "</td>";

                    $cell_count++;

                    // Create a new row every 7 columns (weeks)
                    if ($cell_count % 7 == 0) {
                        echo "</tr><tr>";
                    }
                }

                // Fill remaining cells to complete the last row
                while ($cell_count % 7 != 0) {
                    echo "<td></td>";
                    $cell_count++;
                }

                echo "</tr>";
            ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalLabel">Select Appointment Time</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="appointmentForm">
                        <input type="hidden" id="selectedDate" name="selectedDate">
                        <p><strong>Selected Date:</strong> <span id="displayDate"></span></p>

                        <!-- Full Name Input Field -->
                        <div class="mb-3">
                            <label for="patientName" class="form-label">Full Name</label>
                            <input type="text" id="patientName" name="patientName" class="form-control" required>
                        </div>

                        <!-- Contact Number Input Field -->
                        <div class="mb-3">
                            <label for="contactNumber" class="form-label">Contact Number</label>
                            <input type="text" id="contactNumber" name="contactNumber" class="form-control" required pattern="^\d{10,11}$" maxlength="11" placeholder="Enter 10 or 11-digit phone number" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                        </div>

                        <!-- Address Input Field -->
                        <div class="mb-3">
                            <label for="patientAddress" class="form-label">Address</label>
                            <input type="text" id="patientAddress" name="patientAddress" class="form-control" required>
                        </div>

                        <!-- Time Slot Selection -->
                        <div class="mb-3">
                            <label for="timeSlot" class="form-label">Choose a Time Slot:</label>
                            <select id="timeSlot" name="timeSlot" class="form-select" required>
                                <option value="07:00 AM - 08:00 AM">07:00 AM - 08:00 AM</option>
                                <option value="08:00 AM - 09:00 AM">08:00 AM - 09:00 AM</option>
                                <option value="09:00 AM - 10:00 AM">09:00 AM - 10:00 AM</option>
                                <option value="10:00 AM - 11:00 AM">10:00 AM - 11:00 AM</option>
                                <option value="11:00 AM - 12:00 PM">11:00 AM - 12:00 PM</option>
                                <option value="01:00 PM - 02:00 PM">01:00 PM - 02:00 PM</option>
                                <option value="02:00 PM - 03:00 PM">02:00 PM - 03:00 PM</option>
                                <option value="03:00 PM - 04:00 PM">03:00 PM - 04:00 PM</option>
                                <option value="04:00 PM - 05:00 PM">04:00 PM - 05:00 PM</option>
                                <option value="05:00 PM - 06:00 PM">05:00 PM - 06:00 PM</option>
                                <option value="06:00 PM - 07:00 PM">06:00 PM - 07:00 PM</option>
                                <option value="07:00 PM - 08:00 PM">07:00 PM - 08:00 PM</option>
                                <option value="08:00 PM - 09:00 PM">08:00 PM - 09:00 PM</option>
                                <option value="09:00 PM - 10:00 PM">09:00 PM - 10:00 PM</option>
                                <option value="10:00 PM - 11:00 PM">10:00 PM - 11:00 PM</option>
                            </select>
                        </div>

                        <!-- Confirm Button -->
                        <button type="submit" class="btn btn-primary">Confirm Appointment</button>
                    </form>
                </div>
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

        // document.addEventListener("DOMContentLoaded", function () {
        //     document.querySelectorAll(".openModal").forEach(button => {
        //         button.addEventListener("click", function () {
        //             let selectedDate = this.getAttribute("data-date");
        //             let formattedDate = new Date(selectedDate);
        //             let formatted_date = (formattedDate.getMonth() + 1).toString().padStart(2, '0') + '/' + formattedDate.getDate().toString().padStart(2, '0') + '/' + formattedDate.getFullYear();
        //             document.getElementById("selectedDate").value = formatted_date;
        //             document.getElementById("displayDate").innerText = formatted_date;
        //             let modal = new bootstrap.Modal(document.getElementById("appointmentModal"));
        //             modal.show();
        //         });
        //     });

        //     document.getElementById("appointmentForm").addEventListener("submit", function (event) {
        //         event.preventDefault();
        //         alert("Appointment scheduled for " + document.getElementById("selectedDate").value + " at " + document.getElementById("timeSlot").value);
        //         let modal = bootstrap.Modal.getInstance(document.getElementById("appointmentModal"));
        //         modal.hide();
        //     });
        // });

        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".openModal").forEach(button => {
                button.addEventListener("click", function () {
                    let selectedDate = this.getAttribute("data-date");
                    document.getElementById("selectedDate").value = selectedDate;
                    document.getElementById("displayDate").innerText = selectedDate;

                    fetchBookedSlots(selectedDate);

                    let modal = new bootstrap.Modal(document.getElementById("appointmentModal"));
                    modal.show();
                });
            });

            function fetchBookedSlots(selectedDate) {
                let timeDropdown = document.getElementById("timeSlot");

                fetch(`midwife_appointment.php?fetchBookedSlots=true&visit_date=${selectedDate}`)
                    .then(response => response.json())
                    .then(bookedSlots => {
                        const allSlots = [
                            "07:00 AM - 08:00 AM", "08:00 AM - 09:00 AM", "09:00 AM - 10:00 AM",
                            "10:00 AM - 11:00 AM", "11:00 AM - 12:00 PM", "01:00 PM - 02:00 PM",
                            "02:00 PM - 03:00 PM", "03:00 PM - 04:00 PM", "04:00 PM - 05:00 PM",
                            "05:00 PM - 06:00 PM", "06:00 PM - 07:00 PM", "07:00 PM - 08:00 PM",
                            "08:00 PM - 09:00 PM", "09:00 PM - 10:00 PM", "10:00 PM - 11:00 PM"
                        ];

                        // Clear previous options
                        timeDropdown.innerHTML = "";

                        // Add only available slots
                        allSlots.forEach(slot => {
                            if (!bookedSlots.includes(slot)) {
                                let option = document.createElement("option");
                                option.value = slot;
                                option.textContent = slot;
                                timeDropdown.appendChild(option);
                            }
                        });

                        // If no available slots, show message
                        if (timeDropdown.options.length === 0) {
                            let option = document.createElement("option");
                            option.textContent = "No available slots for this date";
                            option.disabled = true;
                            timeDropdown.appendChild(option);
                        }
                    })
                    .catch(error => console.error("Error fetching booked slots:", error));
            }
        });
   
        document.getElementById("appointmentForm").addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent default form submission

            let formData = new FormData(this);

            fetch(window.location.href, { // Send request to the same file
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Successfully Added",
                        text: "Your appointment has been booked!",
                        showConfirmButton: false,
                        timer: 2000 // Auto close in 2 seconds
                    }).then(() => {
                        location.reload(); // Refresh the page to update the calendar
                    });

                    // Reset the form fields
                    document.getElementById("appointmentForm").reset();

                    // Close the modal properly
                    let modalElement = document.getElementById("appointmentModal");
                    let modalInstance = bootstrap.Modal.getInstance(modalElement);
                    modalInstance.hide();

                    // Ensure modal backdrop is removed
                    document.body.classList.remove("modal-open");
                    let backdrop = document.querySelector(".modal-backdrop");
                    if (backdrop) {
                        backdrop.remove();
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Booking Failed",
                        text: data.error
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Something went wrong! Please try again."
                });
                console.error("Error:", error);
            });
        });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
