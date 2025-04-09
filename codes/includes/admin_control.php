<?php
require_once 'function.php'; 
$fetchdata = new queries();

$pick = urldecode($_POST['pick']);
echo "Pick: " . $pick . "<br>"; // Debugging line

if($pick == 1 || $pick == "1" || $pick == '1') {
    // Debugging each value
    $recordID = htmlentities(htmlspecialchars(urldecode($_POST['record_id'])));
    $transactionDate = htmlentities(htmlspecialchars(urldecode($_POST['transaction_date'])));
    $roomBoard = htmlentities(htmlspecialchars(urldecode($_POST['room_board'])));
    $drugsMedicine = htmlentities(htmlspecialchars(urldecode($_POST['drugs_medicine'])));
    $deliveryRoomFee = htmlentities(htmlspecialchars(urldecode($_POST['delivery_room_fee'])));
    $supplies = htmlentities(htmlspecialchars(urldecode($_POST['supplies'])));
    $professionalFees = htmlentities(htmlspecialchars(urldecode($_POST['professional_fees'])));
    $professionalName = htmlentities(htmlspecialchars(urldecode($_POST['professional_name'])));
    $totalAmount = htmlentities(htmlspecialchars(urldecode($_POST['total_amount'])));
    $paymentStatus = htmlentities(htmlspecialchars(urldecode($_POST['payment_status'])));
    $amountPaid = htmlentities(htmlspecialchars(urldecode($_POST['amount_paid'])));
    $note = htmlentities(htmlspecialchars(urldecode($_POST['note'])));

    // Debugging output of POST data
    echo "recordID: $recordID<br>";
    echo "transactionDate: $transactionDate<br>";
    echo "roomBoard: $roomBoard<br>";
    echo "drugsMedicine: $drugsMedicine<br>";
    echo "deliveryRoomFee: $deliveryRoomFee<br>";
    echo "supplies: $supplies<br>";
    echo "professionalFees: $professionalFees<br>";
    echo "professionalName: $professionalName<br>";
    echo "totalAmount: $totalAmount<br>";
    echo "paymentStatus: $paymentStatus<br>";
    echo "amountPaid: $amountPaid<br>";
    echo "note: $note<br>";

    // Call the query to insert data
    $query = $fetchdata->midwife_payment($transactionDate, $roomBoard, $drugsMedicine, $deliveryRoomFee, $supplies, $professionalFees, $professionalName, $totalAmount, $paymentStatus, $amountPaid, $note);

    if ($query) {
        echo '1';  // Success response
    } else {
        // If the query fails, show error message and display mysqli error
        echo '0<br>';
        echo 'MySQL Error: ' . mysqli_error($fetchdata->dbh); // Debugging error
    }
} else {
    echo 'Invalid pick value';
}
?>
