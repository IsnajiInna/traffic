<?php
include 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}
$unread_count = 0;
if (isAdmin()) {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM notifications WHERE role='admin' AND is_read=0");
    if ($res) {
        $unread_count = $res->fetch_assoc()['cnt'];
    }
}
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch unread notifications for admin
$notif_count = 0;
if (isAdmin()) {
    $notif_count = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE role='admin' AND is_read=0")->fetch_assoc()['count'];
}
?>

<div class="sidebar">
    <h2><?= isAdmin() ? "Admin Panel" : "Dashboard" ?></h2>

    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="violations.php" class="<?= basename($_SERVER['PHP_SELF']) == 'violations.php' ? 'active' : '' ?>">Violations</a>
    <a href="ordinances.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ordinances.php' ? 'active' : '' ?>">Ordinances</a>
    <a href="notification.php" class="<?= basename($_SERVER['PHP_SELF']) == 'notification.php' ? 'active' : '' ?>">
        Notifications
        <?php if ($notif_count > 0) { ?>
            <span class="badge"><?= $notif_count ?></span>
        <?php } ?>
    </a>
    <a href="search.php" class="<?= basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : '' ?>">Search</a>
    <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">Reports</a>
    <a href="payments_admin.php" class="<?= basename($_SERVER['PHP_SELF']) == 'payments_admin.php' ? 'active' : '' ?>">Payments</a>

    <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');" class="logout-btn">Logout</a>
</div>

<style>
.badge {
    background: red;
    color: white;
    padding: 2px 6px;
    border-radius: 50%;
    font-size: 12px;
    vertical-align: top;
    margin-left: 5px;
}
</style>
