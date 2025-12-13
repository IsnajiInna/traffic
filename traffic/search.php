<?php
include 'config.php';
if (!isLoggedIn()) header("Location: login.php");

$search_query = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';
$sql = "SELECT v.*, u.full_name, u.license_plate, o.code FROM violations v 
        JOIN users u ON v.user_id = u.id 
        JOIN ordinances o ON v.ordinance_id = o.id 
        WHERE u.full_name LIKE '%$search_query%' OR u.license_plate LIKE '%$search_query%' OR o.code LIKE '%$search_query%'";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Violations</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="sidebar">
    <h2>Admin Panel</h2>

   <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="violations.php" class="<?= basename($_SERVER['PHP_SELF']) == 'violations.php' ? 'active' : '' ?>">Violations</a>
    <a href="ordinances.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ordinances.php' ? 'active' : '' ?>">Ordinances</a>
    <a href="notification.php" class="<?= basename($_SERVER['PHP_SELF']) == 'notification.php' ? 'active' : '' ?>">Notification</a>
    <a href="search.php" class="<?= basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : '' ?>">Search</a>
    <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">Reports</a>
    <a href="payments_admin.php" class="<?= basename($_SERVER['PHP_SELF']) == 'payments_admin.php' ? 'active' : '' ?>">Payments</a>

    <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');" class="logout-btn">Logout</a>
</div>

    <div class="container">
        <h1>Search Violations</h1>
        <a href="dashboard.php">Back to Dashboard</a>
        
        <form method="GET">
            <label for="query">Search by Driver Name, Plate, or Ordinance:</label>
            <input type="text" id="query" name="query" value="<?php echo $search_query; ?>">
            <input type="submit" value="Search">
        </form>

        <h2>Search Results</h2>
        <table>
            <tr><th>ID</th><th>Driver Name</th><th>Plate</th><th>Ordinance</th><th>Date</th><th>Fine</th><th>Status</th></tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['full_name']; ?></td>
                    <td><?php echo $row['license_plate']; ?></td>
                    <td><?php echo $row['code']; ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td>₱<?php echo number_format($row['fine_amount'], 2); ?></td>
                    <td><?php echo $row['status']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
