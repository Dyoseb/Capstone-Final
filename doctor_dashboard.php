<?php

include 'codes/includes/function.php'; 
$fetchdata = new queries();

// Get appointments
$sql = $fetchdata->get_appointments();
$appointments = [];
while ($row = mysqli_fetch_assoc($sql)) { 
    $appointments[] = $row;
}

$sql = $fetchdata->get_medical_records();
$medical_records = [];
$bp = [];
$fhr = [];
$weight = [];

while($row = mysqli_fetch_assoc($sql)){
    $medical_records[] = $row;
    $bp = $row['vs_bp'];
    $fhr = $row['vs_fhr'];
    $weight = $row['vs_wht'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { width: 250px; height: 100vh; position: fixed; background: #007bff; color: white; padding: 20px; }
        .content { margin-left: 260px; padding: 20px; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4>Doctor Dashboard</h4>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="#" class="nav-link text-white">Dashboard</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white">Appointments</a></li>
            <li class="nav-item"><a href="#" class="nav-link text-white">Patient Records</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link text-white">Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <h2>Welcome, Dr. <?php echo htmlspecialchars($_SESSION['username'] ?? 'Unknown'); ?>!</h2>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">Maternal Health Trends</div>
                    <div class="card-body">
                        <canvas id="healthChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">Upcoming Appointments</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($appointments as $row) { ?>
                                <li class="list-group-item">
                                    <?php echo htmlspecialchars($row['full_name']) . ' - ' . htmlspecialchars($row['visit_date']) . ' (' . htmlspecialchars($row['visit_time']) . ')'; ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">Recent Patient Visits</div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Date</th>
                                    <th>Diagnosis</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medical_records as $row) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['record_date']); ?></td>
                                        <td><?php echo htmlspecialchars($row['past_history']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var ctx = document.getElementById('healthChart').getContext('2d');
        var healthChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates ?? []); ?>,
                datasets: [{
                    label: 'Blood Pressure',
                    data: <?php echo json_encode($bp ?? []); ?>,
                    borderColor: 'red',
                    fill: false
                }, {
                    label: 'Weight (kg)',
                    data: <?php echo json_encode($weight ?? []); ?>,
                    borderColor: 'blue',
                    fill: false
                }, {
                    label: 'Fetal Heart Rate',
                    data: <?php echo json_encode($fhr ?? []); ?>,
                    borderColor: 'green',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
