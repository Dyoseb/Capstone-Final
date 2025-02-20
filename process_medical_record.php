<?php
session_start();
include 'db.php';

// Sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_name = sanitizeInput($_POST["patient_name"]);
    $address = sanitizeInput($_POST["address"]);
    
    // Ensure record_date exists
    $record_date = isset($_POST["record_date"]) ? sanitizeInput($_POST["record_date"]) : date("Y-m-d");

    // Ensure age is set
    $age = isset($_POST["age_size"]) ? sanitizeInput($_POST["age_size"]) : NULL;

    $lmp = !empty($_POST["lmp"]) ? sanitizeInput($_POST["lmp"]) : NULL;
    $edd = !empty($_POST["edd"]) ? sanitizeInput($_POST["edd"]) : NULL;
    $aog = sanitizeInput($_POST["aog"]);
    $gravida = isset($_POST["gravida"]) ? (int)$_POST["gravida"] : NULL;
    $para = isset($_POST["para"]) ? (int)$_POST["para"] : NULL;
    $abortus = isset($_POST["abortus"]) ? (int)$_POST["abortus"] : NULL;
    $tetanus_toxoid = sanitizeInput($_POST["tetanus_toxoid"]);
    $past_history = isset($_POST["past_history"]) ? implode(", ", $_POST["past_history"]) : NULL;
    $vs_aog = sanitizeInput($_POST["vs_aog"]);
    $vs_bp = sanitizeInput($_POST["vs_bp"]);
    $vs_pr = sanitizeInput($_POST["vs_pr"]);
    $vs_rr = sanitizeInput($_POST["vs_rr"]);
    $vs_fht = sanitizeInput($_POST["vs_fht"]);
    $vs_fhr = sanitizeInput($_POST["vs_fhr"]);
    $vs_wht = sanitizeInput($_POST["vs_wht"]);
    $vs_ie = sanitizeInput($_POST["vs_ie"]);
    $remarks = sanitizeInput($_POST["remarks"]);

    // **Fetch appointment_id from appointments table using patient_name**
    $stmt = $conn->prepare("SELECT id FROM appointments WHERE full_name = ? LIMIT 1");
    $stmt->bind_param("s", $patient_name);
    $stmt->execute();
    $stmt->bind_result($appointment_id);

    if (!$stmt->fetch()) {
        echo "<script>
                alert('Error: No appointment found for this patient.');
                window.history.back();
              </script>";
        exit;
    }
    $stmt->close();

    // **Insert data into medicalrecords table**
    $stmt = $conn->prepare("INSERT INTO medicalrecords 
        (appointment_id, patient_name, address, record_date, age, lmp, edd, aog, gravida, para, abortus, tetanus_toxoid, past_history, 
         vs_aog, vs_bp, vs_pr, vs_rr, vs_fht, vs_fhr, vs_wht, vs_ie, remarks) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // **Make sure bind_param matches placeholders**
    $stmt->bind_param("isssisssiiisssssssssss", 
        $appointment_id, $patient_name, $address, $record_date, $age, $lmp, $edd, $aog, 
        $gravida, $para, $abortus, $tetanus_toxoid, $past_history,
        $vs_aog, $vs_bp, $vs_pr, $vs_rr, $vs_fht, $vs_fhr, $vs_wht, 
        $vs_ie, $remarks
    );

    if ($stmt->execute()) {
        echo "<script>
                alert('Medical record added successfully!');
                window.location.href = 'midwife_check-up-form.php';
              </script>";
    } else {
        echo "<script>
                alert('Error saving medical record!');
                window.history.back();
              </script>";
    }

    $stmt->close();
    $conn->close();
}
?>
