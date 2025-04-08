<?php
session_start();
include 'db.php';

// Check authentication
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'midwife') {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Handle POST request for updating payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Prepare professional fees array
    $professional_fees = [];
    foreach ($_POST['professional_name'] as $index => $name) {
        $professional_fees[] = [
            'name' => $name,
            'service' => $_POST['service_type'][$index],
            'amount' => (float)$_POST['professional_amount'][$index]
        ];
    }

    // Calculate total amount
    $total_amount = (float)$_POST['room_board'] + (float)$_POST['drugs_medicine'] + 
                    (float)$_POST['delivery_room_fee'] + (float)$_POST['supplies'];
    foreach ($professional_fees as $fee) {
        $total_amount += $fee['amount'];
    }

    // Update database
    $stmt = $conn->prepare("UPDATE transactions SET
        room_board = ?,
        drugs_medicine = ?,
        delivery_room_fee = ?,
        supplies = ?,
        professional_fees = ?,
        total_amount = ?,
        payment_status = ?,
        amount_paid = ?,
        note = ?
    WHERE transaction_id = ?");

    $stmt->bind_param(
        "ddddssdssi",
        $_POST['room_board'],
        $_POST['drugs_medicine'],
        $_POST['delivery_room_fee'],
        $_POST['supplies'],
        json_encode($professional_fees),
        $total_amount,
        $_POST['payment_status'],
        $_POST['amount_paid'],
        $_POST['note'],
        $_POST['transaction_id']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    exit;
}

// Handle GET request for displaying edit form
if (!isset($_GET['id'])) {
    die("Transaction ID not provided");
}

$transactionId = (int)$_GET['id'];
$transaction = $conn->query("SELECT * FROM transactions WHERE transaction_id = $transactionId")->fetch_assoc();
if (!$transaction) {
    die("Transaction not found");
}

$professional_fees = json_decode($transaction['professional_fees'], true);
?>

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title">Edit Payment #<?= $transactionId ?></h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="editPaymentForm" method="POST">
        <input type="hidden" name="transaction_id" value="<?= $transactionId ?>">
        
        <div class="border p-3 mb-4 rounded">
            <h5 class="mb-3"><i class="fas fa-hospital me-2"></i>Hospital Charges</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Room & Board</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" name="room_board" 
                               value="<?= $transaction['room_board'] ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Drugs & Medicine</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" name="drugs_medicine" 
                               value="<?= $transaction['drugs_medicine'] ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Delivery Room</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" name="delivery_room_fee" 
                               value="<?= $transaction['delivery_room_fee'] ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Supplies</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" name="supplies" 
                               value="<?= $transaction['supplies'] ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="border p-3 mb-4 rounded">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-user-md me-2"></i>Professional Fees</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addProfessionalEdit">
                    <i class="fas fa-plus me-1"></i> Add Professional
                </button>
            </div>
            
            <div id="professionalFeesContainerEdit">
                <?php foreach ($professional_fees as $index => $fee): ?>
                <div class="row g-3 mb-3 professional-fee-row">
                    <div class="col-md-4">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="professional_name[]" 
                               value="<?= htmlspecialchars($fee['name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Professional Fee/s</label>
                        <input type="text" class="form-control" name="professional_fees[]" 
                                placeholder="Professional Fee" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger remove-professional" 
                                <?= count($professional_fees) <= 1 ? 'disabled' : '' ?>>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="border p-3 mb-4 rounded bg-light">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Payment Status</label>
                        <select class="form-select" name="payment_status" required>
                            <option value="pending" <?= $transaction['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="partial" <?= $transaction['payment_status'] === 'partial' ? 'selected' : '' ?>>Partial Payment</option>
                            <option value="paid" <?= $transaction['payment_status'] === 'paid' ? 'selected' : '' ?>>Fully Paid</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Amount Paid</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="amount_paid" 
                                   value="<?= $transaction['amount_paid'] ?>" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea class="form-control" name="note" rows="2"><?= htmlspecialchars($transaction['note']) ?></textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<script>
    // Add professional fee row
    $('#addProfessionalEdit').click(function() {
        const newRow = `
            <div class="row g-3 mb-3 professional-fee-row">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="professional_name[]" 
                           placeholder="Dr. Juan Dela Cruz" required>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="service_type[]" 
                           placeholder="Delivery Fee" required>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" name="professional_amount[]" 
                               min="0" step="0.01" value="2000.00" required>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger remove-professional">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>`;
        $('#professionalFeesContainerEdit').append(newRow);
    });

    // Remove professional fee row
    $(document).on('click', '.remove-professional', function() {
        if ($('.professional-fee-row').length > 1) {
            $(this).closest('.professional-fee-row').remove();
        }
    });

    // Form submission
    $('#editPaymentForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: window.location.href, // Submit to same URL
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editPaymentModal').modal('hide');
                    // Refresh the page or update the table row
                    window.location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Update failed'));
                }
            },
            error: function(xhr) {
                alert('Error updating payment: ' + xhr.responseText);
            }
        });
    });
</script>