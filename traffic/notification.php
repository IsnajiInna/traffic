<?php
include 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// --- HANDLE MARK AS READ ---
if (isset($_GET['read'])) {
    $notif_id = intval($_GET['read']);
    if (isAdmin()) {
        $conn->query("UPDATE notifications SET is_read=1 WHERE id=$notif_id");
    } else {
        $conn->query("UPDATE notifications SET is_read=1 WHERE id=$notif_id AND user_id=$user_id");
    }
    header("Location: notification.php");
    exit();
}

// --- HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $notif_id = intval($_GET['delete']);
    if (isAdmin()) {
        $conn->query("DELETE FROM notifications WHERE id=$notif_id");
    } else {
        $conn->query("DELETE FROM notifications WHERE id=$notif_id AND user_id=$user_id");
    }
    header("Location: notification.php");
    exit();
}

// --- FETCH NOTIFICATIONS ---
if (isAdmin()) {
    // Admin sees all notifications
    $sql = "SELECT n.*, u.full_name FROM notifications n
            LEFT JOIN users u ON n.user_id=u.id
            ORDER BY n.created_at DESC";
} else {
    // User sees their own notifications
    $sql = "SELECT * FROM notifications WHERE user_id=$user_id ORDER BY created_at DESC";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Notifications</title>
<link rel="stylesheet" href="styles.css">
<style>
table { border-collapse: collapse; width: 100%; }
th, td { padding: 8px; border: 1px solid #ddd; }
.status-unread { font-weight: bold; background-color: #f9f9f9; }
.badge { background-color: red; color: white; padding: 2px 6px; border-radius: 50%; font-size: 12px; margin-left: 5px; }
</style>
</head>
<body>

<div class="sidebar">
<h2>Admin Panel</h2>
<a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
<a href="violations.php" class="<?= basename($_SERVER['PHP_SELF']) == 'violations.php' ? 'active' : '' ?>">Violations</a>
<a href="ordinances.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ordinances.php' ? 'active' : '' ?>">Ordinances</a>
<a href="notification.php" class="<?= basename($_SERVER['PHP_SELF']) == 'notification.php' ? 'active' : '' ?>">Notification<span class="badge"></span></a>
<a href="search.php" class="<?= basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : '' ?>">Search</a>
<a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">Reports</a>
<a href="payments_admin.php" class="<?= basename($_SERVER['PHP_SELF']) == 'payments_admin.php' ? 'active' : '' ?>">Payments</a>
<a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
</div>

<div class="main-content">
<h1>Notifications</h1>

<table>
<tr>
<th>Date</th>
<th>Message</th>
<?php if(isAdmin()) echo "<th>User</th>"; ?>
<th>Status</th>
<th>Action</th>
</tr>

<?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr class="<?= $row['is_read'] ? '' : 'status-unread'; ?>">
            <td><?= $row['created_at']; ?></td>
            <td><?= htmlspecialchars($row['message']); ?></td>
            <?php if(isAdmin()): ?>
                <td><?= isset($row['full_name']) ? $row['full_name'] : 'N/A'; ?></td>
            <?php endif; ?>
            <td><?= $row['is_read'] ? 'Read' : 'Unread'; ?></td>
            <td>
                <?php if (!$row['is_read']): ?>
                    <a href="?read=<?= $row['id']; ?>">Mark Read</a> |
                <?php endif; ?>
                <a href="?delete=<?= $row['id']; ?>" onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="<?= isAdmin() ? 5 : 4 ?>">No notifications.</td></tr>
<?php endif; ?>
</table>
</div>

<script>
// Fetch notification badge for sidebar
function fetchNotifications(){
    fetch('fetch_notifications.php')
    .then(res=>res.json())
    .then(data=>{
        let badge=document.querySelector('a[href="notification.php"] .badge');
        if(data.count>0){badge.textContent=data.count; badge.style.display='inline-block';}
        else{badge.style.display='none';}
    });
}
setInterval(fetchNotifications,5000);
fetchNotifications();
</script>
</body>
</html>
