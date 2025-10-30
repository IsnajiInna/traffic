<?php
include 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$sql_violations = "SELECT v.*, o.code, o.description FROM violations v 
                   JOIN ordinances o ON v.ordinance_id = o.id 
                   WHERE v.user_id = $user_id ORDER BY v.date DESC";
$result_violations = $conn->query($sql_violations);


if (isAdmin()) {
    $total_violations = $conn->query("SELECT COUNT(*) as count FROM violations")->fetch_assoc()['count'];
    $unpaid = $conn->query("SELECT COUNT(*) as count FROM violations WHERE status='Unpaid'")->fetch_assoc()['count'];
    $paid = $total_violations - $unpaid;
    $total_fines = $conn->query("SELECT SUM(fine_amount) as total FROM violations")->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
   <div class="main-content">
        <h1>Welcome, <?php echo $_SESSION['full_name']; ?> (<?php echo $role; ?>)</h1>
        <a href="logout.php" class="logout">Logout</a>
        
        <div class="sidebar">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="violations.php">Violations</a>
            <?php if (isAdmin()) { ?>
                <a href="ordinances.php">Ordinances</a>
                <a href="search.php">Search</a>
                <a href="reports.php">Reports</a>
            <?php } ?>
            <a href="payments_admin.php">Payments</a>
        </div>

        <?php if (isAdmin()) { ?>
            <h2>Admin Dashboard</h2>
            <p>Total Violations: <?php echo $total_violations; ?></p>
            <p>Unpaid Fines: <?php echo $unpaid; ?> (Total: ₱<?php echo number_format($total_fines, 2); ?>)</p>
            <p>Paid Fines: <?php echo $paid; ?></p>
        <?php } else { ?>
            <h2>Citizen Dashboard</h2>
            <h3>Your Violation History</h3>
            <table>
                <tr><th>Date</th><th>Type</th><th>Ordinance</th><th>Fine</th><th>Status</th></tr>
                <?php while ($row = $result_violations->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo $row['violation_type']; ?></td>
                        <td><?php echo $row['code'] . ': ' . $row['description']; ?></td>
                        <td>₱<?php echo number_format($row['fine_amount'], 2); ?></td>
                        <td><?php echo $row['status']; ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>
</body>
</html>

