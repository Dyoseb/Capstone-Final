<?php  
    
    include 'db.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // Load PHPMailer
    require 'vendor/autoload.php';

    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        header('Content-Type: application/json');

        $user_email = $_POST['user_email'];     // User's Gmail
        $user_password = $_POST['user_password']; // User's App Password
        $contact_number = $_POST['contact_number']; // Contact Number
        $full_name = $_POST['patientName'];
        $address = $_POST['address'];
        $visit_date = $_POST['visit_date']; // Visit Date
        $visit_time = $_POST['visit_time']; // Visit Time
        $message = $_POST['message']; // Message

        // Fixed recipient email (clinic's email)
        $recipient_email = "galangJoseph97@gmail.com";

        // Check if fields are empty
        if (empty($user_email) || empty($user_password) || empty($visit_date) || empty($visit_time) || empty($contact_number) || empty($full_name) || empty($address)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required!']);
            exit;
        }

        // Connect to MySQL Database
        $conn = new mysqli($host, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
        }

        // Check if the selected time slot is already booked
        $checkSlot = $conn->prepare("SELECT * FROM appointments WHERE visit_date = ? AND visit_time = ?");
        $checkSlot->bind_param("ss", $visit_date, $visit_time);
        $checkSlot->execute();
        $result = $checkSlot->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Selected time slot is already booked!']);
            exit;
        }

        // Save Appointment to Database
        $stmt = $conn->prepare("INSERT INTO appointments (full_name, address, contact_number, visit_date, visit_time) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $address, $contact_number, $visit_date, $visit_time);

        if ($stmt->execute()) {
            $appointment_saved = true;
        } else {
            echo json_encode(['success' => false, 'message' => 'Error saving appointment: ' . $stmt->error]);
            exit;
        }

        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $user_email;    // User's Gmail
            $mail->Password   = $user_password; // User's App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Email Recipients
            $mail->setFrom($user_email, 'User'); // User's Gmail
            $mail->addAddress($recipient_email); // Fixed Recipient Email (Clinic)
            $mail->addReplyTo($user_email, "User Inquiry"); // Allows clinic to reply to sender

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = 'New Appointment Request';
            $mail->Body    = "
                <p><strong>From:</strong> $user_email</p>
                <p><strong>Full Name:</strong> $full_name</p>
                <p><strong>Addres:</strong> $address</p>
                <p><strong>Contact Number:</strong> $contact_number</p>
                <p><strong>Preferred Visit Date:</strong> $visit_date</p>
                <p><strong>Preferred Visit Time:</strong> $visit_time</p>
                <p><strong>Message:</strong> " . nl2br(htmlspecialchars($message)) . "</p>";

            // Send Email
            $mail->send();
            echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
        }
        exit;
    }

    // Fetch booked time slots
    if (isset($_GET['fetchBookedSlots'])) {
        header('Content-Type: application/json');

        $conn = new mysqli($host, $username, $password, $dbname);

        if ($conn->connect_error) {
            echo json_encode(['error' => 'Database connection failed!']);
            exit;
        }

        $visit_date = $_GET['visit_date']; // Date selected by user

        $sql = "SELECT visit_time FROM appointments WHERE visit_date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $visit_date);
        $stmt->execute();
        $result = $stmt->get_result();

        $booked_slots = [];
        while ($row = $result->fetch_assoc()) {
            $booked_slots[] = $row['visit_time'];
        }

        echo json_encode($booked_slots);
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maternity Lying-in Clinic</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif; 
        }
        .hero-section { 
            background: url('https://source.unsplash.com/1600x900/?maternity,hospital') center/cover; 
            height: 90vh; 
            color: white; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            text-align: center; 
        }

        .hero-section h1 { 
            font-size: 3rem; 
        }
        .hero-section p { 
            font-size: 1.2rem; 
        }
        .services { 
            padding: 50px 0; 
        }
        .service-box { 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
        }
        .contact-section { 
            padding: 50px 0; 
            background: #f8f9fa; 
        }
        .map-container { 
            height: 400px; 
        }

        footer { 
            background: #343a40; 
            color: white; 
            padding: 15px 0; 
            text-align: center; 
        }

        /* Eye icon styling */
        .input-group {
            position: relative;
        }
        .input-group .form-control {
            padding-right: 40px; /* Ensure space for the icon */
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand ms-2" href="#"><img src="Images\amorganda logo.png" alt="Logo" style="width: 35px; height: 35px; border-radius: 50%; margin-right: 10px;">Maternity Lying-in Clinic</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="#map">Location</a></li>
                    <li class="nav-item"><a class="nav-link" href="#privacyPolicy">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Welcome to Maternity Lying-in Clinic</h1>
            <p>Providing compassionate maternity care for mothers and babies.</p>
            <a href="#contact" class="btn btn-primary btn-lg">Book an Appointment</a>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services text-center" id="services">
        <div class="container">
            <h2>Our Services</h2>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="service-box bg-light p-4">
                        <i class="fas fa-baby fa-3x mb-3 text-primary"></i>
                        <h4>Pre-natal Care</h4>
                        <p>Comprehensive check-ups to ensure a healthy pregnancy.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-box bg-light p-4">
                        <i class="fas fa-procedures fa-3x mb-3 text-success"></i>
                        <h4>Delivery Services</h4>
                        <p>Safe and comfortable delivery with professional care.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-box bg-light p-4">
                        <i class="fas fa-heartbeat fa-3x mb-3 text-danger"></i>
                        <h4>Post-natal Care</h4>
                        <p>Follow-up care for mothers and newborns.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Contact Section -->
    <section class="contact-section" id="contact">
        <div class="container">
            <h2 class="text-center">Book an Appointment</h2>
            <div class="row mt-4">
                <div class="col-md-6">
                    <form id="contactForm">
                        <div class="mb-3">
                            <label class="form-label">Your Gmail</label>
                            <input type="email" id="user_email" name="user_email" class="form-control" required>
                            <div class="invalid-feedback">Please enter a valid Gmail address.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your App Password</label>
                            <div class="input-group">
                                <input type="password" id="user_password" name="user_password" class="form-control" required>
                                <span class="input-group-text">
                                    <i class="fas fa-eye" id="togglePassword" style="cursor: pointer;"></i>
                                </span>
                            </div>
                        </div>
                        <!-- Full Name Input -->
                        <div class="mb-3">
                            <label for="patientName" class="form-label">Full Name</label>
                            <input type="text" id="patientName" name="patientName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" id="address" name="address" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" class="form-control" required maxlength="11"
                            pattern="^\d{10,}$">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Preferred Visit Date</label>
                            <input type="date" id="visit_date" name="visit_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Preferred Visit Time</label>
                            <select id="visit_time" name="visit_time" class="form-select" required>
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
                        <div class="mb-3">
                            <label class="form-label">Message (Purpose)</label>
                            <textarea id="message" name="message" class="form-control" rows="4"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <h4>Clinic Address</h4>
                    <p>180 Lot 7i Phase, Blk, 2 Mabuhay City Main Rd, Subdivision, Cabuyao, Laguna</p>
                    <h4>Phone</h4>
                    <p>0917-846-5817 / 049-540-2051</p>
                    <h4>Email</h4>
                    <p>amorgandaslyinginclinic@gmail.com</p>
                    <h4>Facebook</h4>
                    <p>Amorganda’s Lying in Clinic</p>
                    <h4>24 Hours Open</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- Google Map -->
    <section id="map">
        <div class="container">
            <h2 class="text-center">Find Us on Google Maps</h2>
            <div class="map-container mt-4">
                <iframe width="100%" height="400" frameborder="0" style="border:0"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3867.277160406384!2d121.15524437509934!3d14.237047486207354!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bd63c2e2a4afe7%3A0xcb6d523d5c6fa81a!2sAmorganda%E2%80%99s%20Lying-in%20Clinic!5e0!3m2!1sen!2sph!4v1738246229775!5m2!1sen!2sph">
                </iframe>
            </div>
        </div>
    </section>

    <!-- Privacy Policy Section -->
    <section id="privacyPolicy" class="container my-5">
        <h3 class="text-center">Privacy Policy</h3>
        <div class="accordion" id="privacyAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrivacy">
                        View Privacy Policy
                    </button>
                </h2>
                <div id="collapsePrivacy" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <p>Welcome to Maternity Lying-in Clinic. We are committed to protecting your privacy...</p>
                        <h4>1. APPOINTMENT FORM</h4>
                        <ul>
                            <p>
                                Information submitted through the Appointment form on our site is sent to our email. We adhere to the  Data Privacy Act of 2012 and you can find more information about this here: 
                                <a href="https://privacy.gov.ph/data-privacy-act/" target="_blank">Republic Act 10173 – Data Privacy Act of 2012.</a>
                            </p>
                        </ul>
                        <h4>2. GOOGLE ANALYTICS</h4>
                        <ul>
                            <p>
                                We use Google Analytics on our site for anonymous reporting of site usage. So, no personalized data is stored. If you would like to opt-out of Google Analytics monitoring your behavior on our website please use this link: 
                                    <a href="https://tools.google.com/dlpage/gaoptout/" target="_blank">Google Analytics Opt-out.</a>
                            </p>
                        </ul>
                        <h4>3. COOKIES</h4>
                        <ul>
                            <p>This site uses cookies – small text files that are placed on your machine to help the site provide a better user experience. In general, cookies are used to retain user preferences, store information for things like shopping carts, and provide anonymized tracking data to third party applications like Google Analytics. Cookies generally exist to make your browsing experience better. However, you may prefer to disable cookies on this site and on others. The most effective way to do this is to disable cookies in your browser. We suggest consulting the help section of your browser.</p>
                        </ul>
                        <h4>4. EMBEDDED CONTENT</h4>
                        <ul>
                            <li><strong>Facebook</strong>: The Facebook Page plugin is used to display our Facebook timeline on our site. Facebook has its own cookie and privacy policies over which we have no control. There is no installation of cookies from Facebook, and your IP is not sent to a Facebook server until you consent to it. See their privacy policy here: 
                            <a href="https://www.facebook.com/privacy/policy" target="_blank">Facebook Privacy Policy</a>.
                            </li>
                        </ul>
                        <ul>
                            <li><strong>Google and Email Security: </strong>Our website integrates **Gmail SMTP** to send emails. Google’s privacy policies apply when using Gmail services. You can review **Google's Privacy Policy** here:  
                                <a href="https://policies.google.com/privacy" target="_blank">Google Privacy Policy</a>
                            </li>
                        </ul>
                        <ul>
                            <li><strong>User Control & Opt-out: </strong>If you do not wish to communicate via Gmail, you may contact us through **phone or social media**. You can request to have your **email conversation deleted** from our records.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <div class="social-icons mt-2 pb-3">
                <!-- Facebook Icon -->
                <a href="https://www.facebook.com/LgbtSpotte/" target="_blank" class="text-white mx-2">
                    <i class="fab fa-facebook fa-2x"></i>
                </a>
                <!-- Email Icon -->
                <a href="amorgandaslyinginclinic@gmail.com" class="text-white mx-2">
                    <i class="fas fa-envelope fa-2x"></i>
                </a>
            </div>
            <p>&copy; 2025 Maternity Lying-in Clinic | All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Cookie Consent Banner -->
    <div id="cookie-banner" style="display: none; position: fixed; bottom: 0; width: 100%; background: rgba(255, 0, 0, 0.7); color: white; text-align: center; padding: 10px; z-index: 1000; display: flex; justify-content: center; align-items: center;">
        <p style="margin: 0; font-size: 14px; display: inline;">
            We use cookies to ensure that we give you the best experience on our website. If you continue to use this site, we will assume that you are happy with it.
        </p>
        <button id="accept-cookies" style="background: blue; color: white; border: none; padding: 5px 15px; margin-left: 10px; cursor: pointer; border-radius: 5px; font-size: 14px;">
            OK
        </button>
        <button id="close-banner" style="background: red; color: white; border: none; padding: 5px 15px; cursor: pointer; margin-left: 15px; font-size: 14px; border-radius: 5px;">
            ✖
        </button>
    </div>

    <!-- JavaScript to Handle Cookie Consent -->
    <script>
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                let date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + value + expires + "; path=/"; // <---- Add "path=/"
        }

        function getCookie(name) {
            let match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
            return match ? match[2] : null;
        }

        // If cookie exists, hide the banner
        if (getCookie("cookie_consent") === "accepted") {
            document.getElementById("cookie-banner").style.display = "none";
        }

        function checkCookieConsent() {
            if (!getCookie("cookie_consent")) {
                document.getElementById("cookie-banner").style.display = "flex";
            }
        }

        document.getElementById("accept-cookies").addEventListener("click", function() {
            document.cookie = "cookie_consent=accepted; expires=Fri, 31 Dec 9999 23:59:59 GMT; path=/";
            document.getElementById("cookie-banner").style.display = "none";
        });

        document.getElementById("close-banner").addEventListener("click", function() {
            document.getElementById("cookie-banner").style.display = "none";
        });

        window.onload = checkCookieConsent;
    </script>

    <script>
        document.getElementById("contactForm").addEventListener("submit", function(event) {
            event.preventDefault();
            let formData = new FormData(this);

            fetch("", { // Submitting to the same file
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    document.getElementById("contactForm").reset();
                }
            })
            .catch(error => alert("An error occurred: " + error));
        });

        // Prevent past dates
        document.addEventListener("DOMContentLoaded", function() {
            let today = new Date().toISOString().split("T")[0];
            document.getElementById("visit_date").setAttribute("min", today);
        });

        // Prevent past times
        document.getElementById("visit_time").addEventListener("input", function() {
            let selectedDate = document.getElementById("visit_date").value;
            let currentTime = new Date();
            let selectedTime = new Date(selectedDate + "T" + this.value);

            if (selectedTime < currentTime) {
                alert("Cannot select past time!");
                this.value = "";
            }
        });

        // Toggle password visibility
        document.getElementById("togglePassword").addEventListener("click", function() {
            let passwordField = document.getElementById("user_password");

            // Toggle input field type
            if (passwordField.type === "password") {
                passwordField.type = "text";
                this.classList.remove("fa-eye");
                this.classList.add("fa-eye-slash"); // Change to eye-slash
            } else {
                passwordField.type = "password";
                this.classList.remove("fa-eye-slash");
                this.classList.add("fa-eye"); // Change back to eye
            }
        });

        document.getElementById("contact_number").addEventListener("input", function (e) {
            this.value = this.value.replace(/[^0-9]/g, ''); // Remove any non-numeric characters, including special chars
        });

        document.getElementById("visit_date").addEventListener("change", function () {
            let selectedDate = this.value;
            let timeDropdown = document.getElementById("visit_time");

            fetch(`appointment.php?fetchBookedSlots=true&visit_date=${selectedDate}`)
                .then(response => response.json())
                .then(bookedSlots => {
                    // Get all possible time slots
                    const allSlots = [
                        "07:00 AM - 08:00 AM", "08:00 AM - 09:00 AM", "09:00 AM - 10:00 AM",
                        "10:00 AM - 11:00 AM", "11:00 AM - 12:00 PM", "01:00 PM - 02:00 PM",
                        "02:00 PM - 03:00 PM", "03:00 PM - 04:00 PM", "04:00 PM - 05:00 PM",
                        "05:00 PM - 06:00 PM", "06:00 PM - 07:00 PM", "07:00 PM - 08:00 PM",
                        "08:00 PM - 09:00 PM", "09:00 PM - 10:00 PM", "10:00 PM - 11:00 PM"
                    ];

                    // Clear existing options
                    timeDropdown.innerHTML = "";

                    // Add only available time slots
                    allSlots.forEach(slot => {
                        if (!bookedSlots.includes(slot)) {
                            let option = document.createElement("option");
                            option.value = slot;
                            option.textContent = slot;
                            timeDropdown.appendChild(option);
                        }
                    });

                    // Check if all slots are booked
                    if (timeDropdown.options.length === 0) {
                        let option = document.createElement("option");
                        option.textContent = "No available slots for this date";
                        option.disabled = true;
                        timeDropdown.appendChild(option);
                    }
                })
                .catch(error => console.error("Error fetching booked slots:", error));
        });

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
