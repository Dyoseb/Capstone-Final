<?php  
include 'db.php'; // Include your database connection file

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
