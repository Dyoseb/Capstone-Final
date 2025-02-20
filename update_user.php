<?php
session_start();
include 'db.php'; // Ensure this correctly connects to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the data from the AJAX request
    $id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE tbl_users SET username=?, email=?, role=?, status=? WHERE user_id=?");
    $stmt->bind_param("ssssi", $username, $email, $role, $status, $id);

    if ($stmt->execute()) {
        echo "success"; // This will be used for AJAX response
    } else {
        echo "Error updating user: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
