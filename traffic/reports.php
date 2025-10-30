<?php
include 'config.php';
if (!isLoggedIn() || !isAdmin()) header("Location: login.php");

if (isset($_GET['export'])) {
    $report_type = $_GET['export'];  
    $sql = "SELECT * FROM violations"; 
    $result = $conn->query($sql);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'User ID', 'Ordinance ID', 'Type', 'Date', 'Officer', 'Fine', 'Status']);
    while ($row = $result->fetch_assoc()) {
       fputcsv($output, [$row['id'], $row['user_id'], $row['ordinance_id'], $row['violation_type'], $row['date'], $row['officer_name'], '₱' . number_format($row['fine_amount'], 2), $row['status']]);
    }
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Generate Reports</h1>
        <a href="dashboard.php">Back to Dashboard</a>
        
        <h2>Report Options</h2>
        <a href="?export=monthly" class="button">Export Monthly Report (CSV)</a>
        <a href="?export=annual" class="button">Export Annual Report (CSV)</a>
        
        <h2>Preview (Last 10 Violations)</h2>
        <table>
            <tr><th>ID</th><th>Type</th><th>Date</th><th>Fine</th><th>Status</th></tr>
            <?php
            $result = $conn->query("SELECT * FROM violations ORDER BY date DESC LIMIT 10");
            while ($row = $result->fetch_assoc()) { ?>
                <tr><td><?php echo $row['id']; ?></td><td><?php echo $row['violation_type']; ?></td><td><?php echo $row['date']; ?></td><td>₱<?php echo number_format($row['fine_amount'], 2); ?></td><td><?php echo $row['status']; ?></td></tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>