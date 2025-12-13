<?php
include 'config.php';
include 'sidebar.php';
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

// ======================
// Notifications Functions
// ======================
function getUnreadNotifications($conn, $user_id) {
    $sql = "SELECT * FROM notifications WHERE user_id = $user_id AND is_read = 0 ORDER BY created_at DESC";
    return $conn->query($sql);
}

function markNotificationRead($conn, $notif_id) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $notif_id");
}

// Mark notification as read if clicked
if (isset($_GET['read'])) {
    $notif_id = intval($_GET['read']);
    markNotificationRead($conn, $notif_id);
    header("Location: dashboard.php");
    exit();
}

// Get unread notifications
$notifications = getUnreadNotifications($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Admin Panel</h2>

    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="violations.php" class="<?= basename($_SERVER['PHP_SELF']) == 'violations.php' ? 'active' : '' ?>">Violations</a>
    <a href="ordinances.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ordinances.php' ? 'active' : '' ?>">Ordinances</a>
<?php
// Get unread notification count
$notif_count = 0;
if (!isAdmin()) { // Only for citizens; remove condition if needed for all
    $res_count = $conn->query("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = $user_id AND is_read = 0");
    if ($res_count) {
        $notif_count = $res_count->fetch_assoc()['cnt'];
    }
}
?>
<a href="notification.php" class="<?= basename($_SERVER['PHP_SELF']) == 'notification.php' ? 'active' : '' ?>">
    Notification
    <?php if ($unread_count > 0): ?>
        <span class="notif-badge"><?= $unread_count ?></span>
    <?php endif; ?>
</a>
    <a href="search.php" class="<?= basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : '' ?>">Search</a>
    <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">Reports</a>
    <a href="payments_admin.php" class="<?= basename($_SERVER['PHP_SELF']) == 'payments_admin.php' ? 'active' : '' ?>">Payments</a>

    <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');" class="logout-btn">Logout</a>
</div>

<!-- MAIN DASHBOARD CONTENT -->
<div class="main-content">
    <h1>Welcome, <?= $_SESSION['full_name']; ?> (<?= $role; ?>)</h1>

    <!-- Notifications -->
    <?php if ($notifications && $notifications->num_rows > 0): ?>
        <div class="notifications">
            <h3>Unread Notifications</h3>
            <ul>
                <?php while ($n = $notifications->fetch_assoc()): ?>
                    <li>
                        <?= $n['message']; ?> 
                        <a href="dashboard.php?read=<?= $n['id']; ?>" class="mark-read-btn">Mark as read</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isAdmin()) { ?>
        <h2>Admin Dashboard Overview</h2>

        <div class="dashboard-nav">
            <a href="violations.php">Manage Violations</a>
            <a href="ordinances.php">Manage Ordinances</a>
            <a href="reports.php">View Reports</a>
            <a href="payments_admin.php">View Payments</a>
        </div>

        <div class="card">
            <h3>Total Violations</h3>
            <p><?= $total_violations; ?> records</p>
        </div>

        <div class="card">
            <h3>Unpaid Violations</h3>
            <p><?= $unpaid; ?> (Total: ₱<?= number_format($total_fines, 2); ?>)</p>
        </div>

        <div class="card">
            <h3>Paid Violations</h3>
            <p><?= $paid; ?></p>
        </div>

    <?php } else { ?>
        <h2>Citizen Dashboard</h2>

        <h3>Your Violation History</h3>

        <table>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Ordinance</th>
                <th>Fine</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $result_violations->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['date']; ?></td>
                    <td><?= $row['violation_type']; ?></td>
                    <td><?= $row['code'] . ': ' . $row['description']; ?></td>
                    <td>₱<?= number_format($row['fine_amount'], 2); ?></td>
                    <td class="<?= $row['status'] == 'Paid' ? 'status-paid' : 'status-unpaid'; ?>">
                        <?= $row['status']; ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>

</body>
</html>
