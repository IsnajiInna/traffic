<?php
include 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if (!isAdmin() && $_SERVER['REQUEST_METHOD'] != 'GET') {
    die("Access denied: Only admins can modify violations.");
}

if (isset($_POST['add']) && isAdmin()) {
    $violation_type = mysqli_real_escape_string($conn, $_POST['violation_type']);
    $ordinance_id = $_POST['ordinance_id'];
    $date = $_POST['date'];
    $officer_name = mysqli_real_escape_string($conn, $_POST['officer_name']);
    $fine_amount = $_POST['fine_amount'];
    $user_id_target = $_POST['user_id'];

    $sql = "INSERT INTO violations (user_id, ordinance_id, violation_type, date, officer_name, fine_amount, status) 
            VALUES ($user_id_target, $ordinance_id, '$violation_type', '$date', '$officer_name', $fine_amount, 'Unpaid')";
    if ($conn->query($sql)) {
        $message = "Violation added successfully.";
    } else {
        $message = "Error: " . $conn->error;
    }
}

if (isset($_POST['update']) && isAdmin()) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $sql = "UPDATE violations SET status='$status' WHERE id=$id";
    if ($conn->query($sql)) {
        $message = "Violation updated successfully.";
    } else {
        $message = "Error: " . $conn->error;
    }
}

if (isset($_GET['delete']) && isAdmin()) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM violations WHERE id=$id");
    $message = "Violation deleted successfully.";
}

$where = isAdmin() ? "WHERE v.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)" : "WHERE v.user_id = $user_id";
$sql = "SELECT v.*, u.full_name, u.license_plate, o.code, o.description FROM violations v 
        JOIN users u ON v.user_id = u.id 
        JOIN ordinances o ON v.ordinance_id = o.id $where ORDER BY v.date DESC";
$result = $conn->query($sql);

if (isAdmin()) {
    $users_result = $conn->query("SELECT id, full_name, email FROM users WHERE role='user'");
    $ordinances_result = $conn->query("SELECT * FROM ordinances");
    if (!$ordinances_result || $ordinances_result->num_rows == 0) {
        $ordinance_error = "No ordinances available. Add some in ordinances.php first.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Violations</title>
<link rel="stylesheet" href="style.css">
<style>
.search-box {
    margin-bottom: 15px;
    width: 300px;
}
.search-input {
    width: 100%;
    padding: 8px;
    border: 1px solid #aaa;
    border-radius: 5px;
}
</style>
</head>
<body>

<div class="sidebar">
    <h2>City Dashboard</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="ordinances.php">Ordinances</a>
    <a href="payments.php">Payments</a>
    <a href="violations.php" class="active">Violations</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main-content">
    <h1><?php echo isAdmin() ? 'Manage Violations (Recent Only)' : 'Your Violations'; ?></h1>

    <?php if (isset($message)) echo "<p class='success'>$message</p>"; ?>
    <?php if (isset($ordinance_error)) echo "<p class='error'>$ordinance_error</p>"; ?>

    <?php if (isAdmin()) { ?>
    <h2>Add New Violation</h2>
    <form method="POST">
        <label for="user_id">User:</label>
        <select name="user_id" required>
            <?php while ($u = $users_result->fetch_assoc()) { ?>
                <option value="<?= $u['id']; ?>"><?= $u['full_name'].' ('.$u['email'].')'; ?></option>
            <?php } ?>
        </select>

        <label>Ordinance:</label>
        <select name="ordinance_id" required>
            <?php 
            $ordinances_result->data_seek(0);
            while ($o = $ordinances_result->fetch_assoc()) { ?>
                <option value="<?= $o['id']; ?>"><?= $o['code'].' - '.$o['description']; ?></option>
            <?php } ?>
        </select>

        <label>Violation Type:</label>
        <input type="text" name="violation_type" required>

        <label>Date:</label>
        <input type="date" name="date" required>

        <label>Officer:</label>
        <input type="text" name="officer_name" required>

        <label>Fine Amount:</label>
        <input type="number" step="0.01" name="fine_amount" required>

        <button name="add" class="btn">Add Violation</button>
    </form>
    <?php } ?>

    <h2>Violation Records</h2>

    <!-- ✅ SEARCH INPUT (Frontend only, no PHP change) -->
    <div class="search-box">
        <input type="text" class="search-input" id="searchInput" placeholder="Search name, plate, ordinance..." onkeyup="filterTable()">
    </div>

    <table id="violationTable">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>License Plate</th>
            <th>Ordinance</th>
            <th>Type</th>
            <th>Date</th>
            <th>Fine</th>
            <th>Status</th>
            <?php if (isAdmin()) echo "<th>Actions</th>"; ?>
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
                    <td><?= $row['date']; ?></td>
                    <td>₱<?= number_format($row['fine_amount'],2); ?></td>
                    <td><?= $row['status']; ?></td>
                    <?php if (isAdmin()) { ?>
                        <td>
                            <a href="?edit=<?= $row['id']; ?>">Edit</a> |
                            <a href="?delete=<?= $row['id']; ?>" onclick="return confirm('Delete violation?')">Delete</a>
                        </td>
                    <?php } ?>
                </tr>
        <?php } } else { echo "<tr><td colspan='9'>No Data</td></tr>"; } ?>
    </table>
</div>

<script>
// ✅ FRONT-END SEARCH (No PHP changes)
function filterTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("#violationTable tr");

    rows.forEach((row, i) => {
        if (i === 0) return; // skip header
        row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none";
    });
}
</script>

</body>
</html>
