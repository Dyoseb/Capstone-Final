<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'midwife') {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Invalid transaction ID");
}

$transactionId = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT t.*, m.patient_name, m.address, m.age 
                       FROM transactions t
                       JOIN medicalrecords m ON t.record_id = m.record_id
                       WHERE t.transaction_id = ?");
$stmt->bind_param("i", $transactionId);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    die("Transaction not found");
}

$professional_fees = json_decode($transaction['professional_fees'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt #<?= $transactionId ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #8CB150;
            padding-bottom: 10px;
        }
        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            color: #8CB150;
            margin-bottom: 5px;
        }
        .clinic-address {
            font-size: 14px;
            color: #666;
        }
        .receipt-title {
            text-align: center;
            font-size: 20px;
            margin: 20px 0;
            font-weight: bold;
        }
        .patient-info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total-row {
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
            .receipt-container {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <div class="clinic-name">Amorganda Lying-in Clinic</div>
            <div class="clinic-address">123 Clinic Street, Barangay Health, City</div>
            <div class="clinic-address">Contact: 0912-345-6789 | TIN: 123-456-789-000</div>
        </div>
        
        <div class="receipt-title">OFFICIAL RECEIPT</div>
        
        <div class="patient-info">
            <div><strong>Patient:</strong> <?= htmlspecialchars($transaction['patient_name']) ?></div>
            <div><strong>Address:</strong> <?= htmlspecialchars($transaction['address']) ?></div>
            <div><strong>Age:</strong> <?= $transaction['age'] ?></div>
            <div><strong>Transaction #:</strong> <?= $transactionId ?></div>
            <div><strong>Date:</strong> <?= date('F j, Y h:i A', strtotime($transaction['transaction_date'])) ?></div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount (₱)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" style="font-weight: bold; background-color: #f8f9fa;">Hospital Charges</td>
                </tr>
                <tr>
                    <td>Room and Board</td>
                    <td><?= number_format($transaction['room_board'], 2) ?></td>
                </tr>
                <tr>
                    <td>Drugs and Medicine</td>
                    <td><?= number_format($transaction['drugs_medicine'], 2) ?></td>
                </tr>
                <tr>
                    <td>Delivery Room Fee</td>
                    <td><?= number_format($transaction['delivery_room_fee'], 2) ?></td>
                </tr>
                <tr>
                    <td>Medical Supplies</td>
                    <td><?= number_format($transaction['supplies'], 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td>Subtotal (Hospital Charges)</td>
                    <td><?= number_format($transaction['room_board'] + $transaction['drugs_medicine'] + 
                                   $transaction['delivery_room_fee'] + $transaction['supplies'], 2) ?></td>
                </tr>
                
                <tr>
                    <td colspan="2" style="font-weight: bold; background-color: #f8f9fa;">Professional Fees</td>
                </tr>
                <?php foreach ($professional_fees as $fee): ?>
                <tr>
                    <td><?= htmlspecialchars($fee['name']) ?></td>
                    <td><?= htmlspecialchars($fee['service']) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td>Subtotal (Professional Fees)</td>
                    <td><?= number_format(array_sum(array_column($professional_fees, 'amount')), 2) ?></td>
                </tr>
                
                <tr style="border-top: 2px solid #000;">
                    <td><strong>TOTAL AMOUNT DUE</strong></td>
                    <td><strong>₱<?= number_format($transaction['total_amount'], 2) ?></strong></td>
                </tr>
                <tr>
                    <td>Payment Status</td>
                    <td style="text-transform: capitalize;"><?= $transaction['payment_status'] ?></td>
                </tr>
                <tr>
                    <td>Amount Paid</td>
                    <td>₱<?= number_format($transaction['amount_paid'], 2) ?></td>
                </tr>
                <?php if ($transaction['payment_status'] === 'partial'): ?>
                <tr>
                    <td>Balance Due</td>
                    <td>₱<?= number_format($transaction['total_amount'] - $transaction['amount_paid'], 2) ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="footer">
            <p>Thank you for choosing our clinic!</p>
            <p>This receipt is computer generated and does not require a signature.</p>
            <p>For any concerns, please contact our clinic within 7 days from issuance.</p>
        </div>
        
        <div class="text-center mt-3 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <a href="midwife_payments.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Payments
            </a>
        </div>
    </div>
</body>
</html>