<?php
    session_start();

    include 'db.php';

    // Prevent logged-in users from accessing login page
    if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
        $role = $_SESSION['role'];

        // Redirect based on user role
        if ($role === "admin") {
            header("Location: admin_dashboard.php");
            exit;
        } elseif ($role === "doctor") {
            header("Location: doctor_dashboard.php");
            exit;
        } elseif ($role === "midwife") {
            header("Location: midwife_dashboard.php");
            exit;
        } else {
            header("Location: utility_dashboard.php");
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input_email = $_POST['email'];
        $input_password = $_POST['password'];

        // Prevent SQL Injection
        $stmt = $conn->prepare("SELECT * FROM tbl_Users WHERE email = ?");
        $stmt->bind_param("s", $input_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Check lock_until
            $current_time = date("Y-m-d H:i:s");
            if ($user['lock_until'] && strtotime($user['lock_until']) > strtotime($current_time)) {
                $error = "Your account is locked. Please try again after " . date("h:i A", strtotime($user['lock_until'])) . ".";
            } else {
                // Reset failed attempts if lock period has passed
                if ($user['lock_until'] && strtotime($user['lock_until']) <= strtotime($current_time)) {
                    $stmt = $conn->prepare("UPDATE tbl_Users SET failed_attempts = 0, lock_until = NULL WHERE email = ?");
                    $stmt->bind_param("s", $input_email);
                    $stmt->execute();
                }

                if ($user['status'] !== 'Active') {
                    $error = "Your account is inactive. Please contact the administrator.";
                } elseif ($input_password === $user['password']) {
                    // Reset failed attempts on successful login
                    $stmt = $conn->prepare("UPDATE tbl_Users SET failed_attempts = 0, lock_until = NULL WHERE email = ?");
                    $stmt->bind_param("s", $input_email);
                    $stmt->execute();

                    // Set session variables and redirect
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    if ($user['role'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } elseif ($user['role'] === 'doctor') {
                        header("Location: doctor_dashboard.php");
                    } elseif ($user['role'] === 'midwife') {
                        header("Location: midwife_dashboard.php");
                    } else {
                        header("Location: utility_dashboard.php");
                    }
                    exit;
                } else {
                    // Increment failed attempts
                    $failed_attempts = $user['failed_attempts'] + 1;
                    $lock_until = null;

                    if ($failed_attempts >= 5) {
                        $lock_until = date("Y-m-d H:i:s", strtotime("+15 minutes"));
                        $error = "Too many failed attempts. Your account is locked for 15 minutes.";
                    } else {
                        $error = "Invalid password. You have " . (5 - $failed_attempts) . " attempt(s) remaining.";
                    }

                    // Update failed attempts and lock_until in the database
                    $stmt = $conn->prepare("UPDATE tbl_Users SET failed_attempts = ?, lock_until = ? WHERE email = ?");
                    $stmt->bind_param("iss", $failed_attempts, $lock_until, $input_email);
                    $stmt->execute();
                }
            }
        } else {
            $error = "Invalid email.";
        }

        $stmt->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maternity Clinic Login</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f9;
        }

        .card {
            border-radius: 8px;
        }

        h2 {
            color: #5a5a5a;
        }

        .password-toggle {
            position: relative;
            display: flex;
        }

        .password-toggle .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .password-toggle .input-group-text {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card p-4 shadow-sm" style="width: 100%; max-width: 400px;">
            <h2 class="text-center mb-4">Maternity Clinic Login</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form id="login-form" method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <div class="input-group password-toggle">
                        <input type="password" id="password" name="password" class="form-control" required>
                        <span class="input-group-text" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                <button type="submit" id="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS and Popper (required for some components like dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;
            
            // Basic validation
            if (!email || !password) {
                alert("Please fill in all fields.");
                e.preventDefault();
            }
        });

        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', () => {
            // Toggle password visibility
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Toggle the eye icon
            togglePassword.querySelector('i').classList.toggle('fa-eye');
            togglePassword.querySelector('i').classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
