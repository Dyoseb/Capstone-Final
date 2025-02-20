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

    // Handle AJAX request for patient suggestions
    if (isset($_GET['query'])) {
        $query = "%" . $_GET['query'] . "%"; // Allow partial matches
        $stmt = $conn->prepare("SELECT full_name, address FROM appointments WHERE full_name LIKE ? LIMIT 5");
        $stmt->bind_param("s", $query);
        $stmt->execute();
        $result = $stmt->get_result();

        $suggestions = [];
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = ["name" => $row['full_name'], "address" => $row['address']];
        }

        echo json_encode($suggestions);
        $stmt->close();
        $conn->close();
        exit; // Stop execution after responding to AJAX
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Midwife Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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

        .div-checkUp {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .div-checkUp {
            overflow-y: auto;
            overflow-x: auto;
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
        <h2 class="text-start">Check Up</h2> 
        <div class="div-checkUp">
            <form action="process_medical_record.php" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <!-- Name Field with Dynamic Placeholder -->
                        <label class="form-label">Name:</label>
                        <div class="position-relative">
                            <input type="text" name="patient_name" id="patientName" class="form-control" autocomplete="off" required onkeyup="fetchPatientSuggestions()" onblur="clearPlaceholder()">
                            <span id="suggestedName" class="position-absolute text-muted" style="top: 50%; left: 13px; transform: translateY(-50%); pointer-events: none;"></span>
                            <ul id="suggestionsList" class="list-group position-absolute mt-1 w-100"></ul>
                        </div>
                        <small class="text-danger" id="nameError"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date:</label>
                        <input type="text" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                    </div>
                    <div class="col-md-6 mt-2">
                        <label class="form-label">Address:</label>
                        <input type="text" name="address" class="form-control" id="patientAddress" required>
                    </div>
                    <div class="col-md-6 mt-2">
                        <label class="form-label">Age:</label>
                        <input type="text" name="age_size" class="form-control" required oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                    </div>
                </div>

                <!-- LMP, EDD, AOG -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">LMP:</label>
                        <input type="date" name="lmp" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">EDD:</label>
                        <input type="date" name="edd" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">AOG:</label>
                        <input type="text" name="aog" class="form-control" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                    </div>
                </div>

                <!-- Gravida, Para, Abortus -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">G:</label>
                        <input type="number" name="gravida" class="form-control" min="0" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">P:</label>
                        <input type="number" name="para" class="form-control" min="0" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">A:</label>
                        <input type="number" name="abortus" class="form-control" min="0" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                    </div>
                </div>

                <!-- Tetanus Toxoid -->
                <div class="mb-3">
                    <label class="form-label">Tetanus Toxoid:</label>
                    <select name="tetanus_toxoid" class="form-select">
                        <option value="">Select...</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>

                <!-- Past History -->
                <h4 class="mt-4">Past History</h4>
                <div class="row">
                    <?php
                    $past_history_options = [
                        "Allergy", "German Measles", "Liver Disease", "Thyroid Dysfunction",
                        "Asthma", "Gyne Disease", "Mental Disorder", "Tuberculosis",
                        "Blood Dyscrasia", "Heart Disease", "Neoplasm", "UTI",
                        "Chicken Pox", "Hyperthyroidism", "Nephritis", "Venereal Disease",
                        "Diabetes", "Operations"
                    ];
                    foreach ($past_history_options as $option) {
                        echo "
                        <div class='col-md-4'>
                            <input type='checkbox' name='past_history[]' value='$option'> 
                            <label>$option</label>
                        </div>";
                    }
                    ?>
                </div>

                <!-- Vital Signs -->
                <h4 class="mt-4">Vital Signs</h4>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">AOG:</label>
                        <input type="text" name="vs_aog" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">BP:</label>
                        <input type="text" name="vs_bp" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">PR:</label>
                        <input type="text" name="vs_pr" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">RR:</label>
                        <input type="text" name="vs_rr" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">FHT:</label>
                        <input type="text" name="vs_fht" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FHR:</label>
                        <input type="text" name="vs_fhr" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">WHT:</label>
                        <input type="text" name="vs_wht" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">IE:</label>
                        <input type="text" name="vs_ie" class="form-control">
                    </div>
                </div>

                <!-- Remarks -->
                <div class="mb-3">
                    <label class="form-label">Remarks:</label>
                    <textarea name="remarks" class="form-control"></textarea>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
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

        document.addEventListener("DOMContentLoaded", function() {
            let searchInput = document.querySelector("input[name='search']");

            searchInput.addEventListener("input", function() {
                this.value = this.value.replace(/[^A-Za-z. ]/g, ""); // Removes numbers & special chars
            });
        });

        function fetchPatientDetails() {
            let name = document.getElementById("patientName").value;

            if (name.length < 3) {
                document.getElementById("patientAddress").value = "";
                document.getElementById("nameError").innerText = "Type at least 3 letters.";
                return;
            }

            fetch("fetch_patient.php?name=" + encodeURIComponent(name))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById("patientAddress").value = data.address;
                        document.getElementById("nameError").innerText = "";
                    } else {
                        document.getElementById("patientAddress").value = "";
                        document.getElementById("nameError").innerText = "Patient not found in appointments.";
                    }
                })
                .catch(error => console.error("Error fetching patient details:", error));
            }
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
       function fetchPatientSuggestions() {
            let input = document.getElementById("patientName");
            let query = input.value.trim();
            let placeholder = document.getElementById("suggestedName");
            let suggestionsList = document.getElementById("suggestionsList");

            if (query.length < 2) { 
                suggestionsList.innerHTML = "";
                placeholder.innerText = "";
                return;
            }

            fetch(`<?php echo $_SERVER['PHP_SELF']; ?>?query=${query}`) // Fetch from the same file
                .then(response => response.json())
                .then(data => {
                    suggestionsList.innerHTML = "";
                    placeholder.innerText = "";

                    if (data.length > 0) {
                        // Set the first suggestion as a light-colored placeholder
                        placeholder.innerText = data[0].name;

                        data.forEach(patient => {
                            let li = document.createElement("li");
                            li.classList.add("list-group-item", "list-group-item-action");
                            li.textContent = patient.name;
                            li.onclick = function () {
                                input.value = patient.name;
                                document.getElementById("patientAddress").value = patient.address;
                                suggestionsList.innerHTML = "";
                                placeholder.innerText = "";
                            };
                            suggestionsList.appendChild(li);
                        });
                    }
                })
                .catch(error => console.error("Error fetching names:", error));
        }

        // Clears placeholder when user clicks outside input field
        function clearPlaceholder() {
            document.getElementById("suggestedName").innerText = "";
        }

        // Hide suggestions when clicking outside
        document.addEventListener("click", function (event) {
            if (!event.target.closest("#patientName")) {
                document.getElementById("suggestionsList").innerHTML = "";
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>

