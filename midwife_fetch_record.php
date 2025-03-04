<?php 
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM medicalrecords WHERE record_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        ?>

        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Name:</strong> <?= htmlspecialchars($row['patient_name']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Date:</strong> <?= htmlspecialchars($row['record_date']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Address:</strong> <?= htmlspecialchars($row['address']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Age:</strong> <?= htmlspecialchars($row['age']) ?></p>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-4">
                    <p><strong>LMP:</strong> <?= htmlspecialchars($row['lmp'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>EDD:</strong> <?= htmlspecialchars($row['edd'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>AOG:</strong> <?= htmlspecialchars($row['aog'] ?? 'N/A') ?></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <p><strong>G:</strong> <?= htmlspecialchars($row['gravida'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>P:</strong> <?= htmlspecialchars($row['para'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>A:</strong> <?= htmlspecialchars($row['abortus'] ?? 'N/A') ?></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <p><strong>Tetanus Toxoid:</strong> <?= htmlspecialchars($row['tetanus_toxoid'] ?? 'N/A') ?></p>
                </div>
            </div>

            <hr>

            <h5>Past History</h5>
            <p><?= htmlspecialchars($row['past_history'] ?? 'N/A') ?></p>

            <hr>

            <h5>Vital Signs</h5>
            <div class="row">
                <div class="col-md-3">
                    <p><strong>AOG:</strong> <?= !empty($row['vs_aog']) ? htmlspecialchars($row['vs_aog']) : 'N/A' ?></p>
                </div>
                <div class="col-md-3">
                    <p><strong>BP:</strong> <?= !empty($row['vs_bp']) ? htmlspecialchars($row['vs_bp']) : 'N/A' ?></p>
                </div>
                <div class="col-md-3">
                    <p><strong>PR:</strong> <?= !empty($row['vs_pr']) ? htmlspecialchars($row['vs_pr']) : 'N/A' ?></p>
                </div>
                <div class="col-md-3">
                    <p><strong>RR:</strong> <?= !empty($row['vs_rr']) ? htmlspecialchars($row['vs_rr']) : 'N/A' ?></p>
                </div>
                <div class="col-md-3">
                    <p><strong>FHT:</strong> <?= !empty($row['vs_fht']) ? htmlspecialchars($row['vs_fht']) : 'N/A' ?></p>
                </div>
                <div class="col-md-3">
                    <p><strong>FHR:</strong> <?= !empty($row['vs_fhr']) ? htmlspecialchars($row['vs_fhr']) : 'N/A' ?></p>
                </div>
                <div class="col-md-3">
                    <p><strong>WHT:</strong> <?= !empty($row['vs_wht']) ? htmlspecialchars($row['vs_wht']) : 'N/A' ?></p>
                </div>
                <div class="col-md-3">
                    <p><strong>IE:</strong> <?= !empty($row['vs_ie']) ? htmlspecialchars($row['vs_ie']) : 'N/A' ?></p>
                </div>
            </div>
            <hr>
            <p><strong>Remarks:</strong> <?= !empty($row['remarks']) ? htmlspecialchars($row['remarks']) : 'N/A' ?></p>
        </div>

        <?php
    } else {
        echo "<p>No records found.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
