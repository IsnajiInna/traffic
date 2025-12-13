<?php
include 'config.php';
if (!isLoggedIn()) header("Location: login.php");
if (!isAdmin()) die("Access denied: Admins only.");

$message = "";

// ✅ Mark violation as Paid
if (isset($_POST['mark_paid'])) {
    $id = (int)$_POST['violation_id'];
    $sql = "UPDATE violations SET status='Paid' WHERE id=$id";
    if ($conn->query($sql)) {
        $message = "Payment marked as PAID!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// ✅ Mark violation as Unpaid
if (isset($_POST['mark_unpaid'])) {
    $id = (int)$_POST['violation_id'];
    $sql = "UPDATE violations SET status='Unpaid' WHERE id=$id";
    if ($conn->query($sql)) {
        $message = "Status changed back to UNPAID!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// ✅ Fetch ALL violations (paid or unpaid)
$sql = "SELECT v.*, u.full_name, u.license_plate, o.code, o.description 
        FROM violations v
        JOIN users u ON v.user_id = u.id 
        JOIN ordinances o ON v.ordinance_id = o.id
        ORDER BY v.date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Payments</title>
<link rel="stylesheet" href="styles.css">

<style>
.search-box { width: 300px; margin-bottom: 10px; }
.search-input { width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #aaa; }
.paid { color: green; font-weight: bold; }
.unpaid { color: red; font-weight: bold; }
</style>
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


<div class="main-content">
    <h1>Payment Management (Admin)</h1>

    <?php if ($message) echo "<p class='success'>$message</p>"; ?>

    <div class="search-box">
        <input type="text" id="searchInput" class="search-input" placeholder="Search name, plate, ordinance..." onkeyup="filterTable()">
    </div>

    <table id="paymentTable">
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>License Plate</th>
            <th>Ordinance</th>
            <th>Violation Type</th>
            <th>Fine</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>

        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= $row['full_name']; ?></td>
                    <td><?= $row['license_plate']; ?></td>
                    <td><?= $row['code']; ?></td>
                    <td><?= $row['violation_type']; ?></td>
                    <td>₱<?= number_format($row['fine_amount'],2); ?></td>
                    <td class="<?= $row['status']=='Paid' ? 'paid' : 'unpaid' ?>"><?= $row['status']; ?></td>
                    <td><?= $row['date']; ?></td>

                    <td>
                        <?php if ($row['status'] == 'Unpaid') { ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="violation_id" value="<?= $row['id']; ?>">
                                <button name="mark_paid" class="btn">Mark as Paid</button>
                            </form>
                        <?php } else { ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="violation_id" value="<?= $row['id']; ?>">
                                <button name="mark_unpaid" class="btn">Mark Unpaid</button>
                            </form>
                        <?php } ?>
                    </td>
                </tr>
        <?php } 
        } else {
            echo "<tr><td colspan='9'>No payment data found.</td></tr>";
        } ?>
    </table>
</div>

<script>
// ✅ FRONTEND SEARCH FILTER
function filterTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("#paymentTable tr");

    rows.forEach((row, i) => {
        if (i === 0) return; // Skip header
        row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none";
    });
}
</script>

</body>
</html>
