<?php
include 'config.php';
// Check if user is logged in, redirect if not
if (!isLoggedIn()) header("Location: login.php");

$user_id = $_SESSION['user_id'];
$message = '';
$receipt = null;

// --- Handle Payment Submission ---
if (isset($_POST['pay'])) {
    $id = (int)$_POST['violation_id']; 
    
    // Process payment and update status
    // WARNING: For a real application, you MUST use **Prepared Statements** to prevent SQL Injection, 
    // instead of directly inserting variables into the query string.
    $update_sql = "UPDATE violations SET status='Paid' WHERE id=$id AND user_id=$user_id";
    if ($conn->query($update_sql)) {
        $message = "Payment successful!";
        
        // Fetch receipt details
        $receipt_sql = "SELECT * FROM violations WHERE id=$id AND user_id=$user_id";
        $receipt = $conn->query($receipt_sql)->fetch_assoc();
    } else {
        $message = "Error processing payment: " . $conn->error;
    }
}

// --- Fetch Outstanding Fines (Unpaid Violations) for the current user ---
// If a payment was just successful, the newly paid violation will no longer appear here.
$sql = "SELECT v.*, o.code FROM violations v 
        JOIN ordinances o ON v.ordinance_id = o.id 
        WHERE v.user_id = $user_id AND v.status = 'Unpaid'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payments</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Payment System</h1>
        <a href="dashboard.php">Back to Dashboard</a>
        
        <?php if ($receipt) { // Display receipt after successful payment ?>
            <div class="receipt">
                <h2>Payment Receipt</h2>
                <p class="success"><strong><?php echo $message; ?></strong></p>
                <p>Violation ID: <?php echo $receipt['id']; ?></p>
                <p>Type: <?php echo $receipt['violation_type']; ?></p>
                <p>Fine Paid: ₱<?php echo number_format($receipt['fine_amount'], 2); ?></p>
                <p>Date Paid: <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
            <hr>
        <?php } ?>
        
        <?php if ($result && $result->num_rows > 0) { ?>
            <h2>Outstanding Fines</h2>
            <table>
                <tr><th>ID</th><th>Type</th><th>Ordinance</th><th>Fine</th><th>Actions</th></tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['violation_type']; ?></td>
                        <td><?php echo $row['code']; ?></td>
                        <td>₱<?php echo number_format($row['fine_amount'], 2); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="violation_id" value="<?php echo $row['id']; ?>">
                                <input type="submit" name="pay" value="Pay Now" class="button">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else if (!$receipt) { // Only show this if no violations are found AND no receipt is being shown ?>
            <p class="success">You have no outstanding fines.</p>
        <?php } ?>
    </div>
</body>
</html>