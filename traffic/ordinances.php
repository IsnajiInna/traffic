<?php
include 'config.php';
if (!isLoggedIn()) header("Location: login.php");
if (!isAdmin()) die("Access denied for non-admins.");

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $code = mysqli_real_escape_string($conn, $_POST['code']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $fine_amount = $_POST['fine_amount'];

    $sql = "INSERT INTO ordinances (code, description, fine_amount) VALUES ('$code', '$description', $fine_amount)";
    if ($conn->query($sql)) $message = "✅ Ordinance added successfully.";
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $code = mysqli_real_escape_string($conn, $_POST['code']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $fine_amount = $_POST['fine_amount'];
    $sql = "UPDATE ordinances SET code='$code', description='$description', fine_amount=$fine_amount WHERE id=$id";
    if ($conn->query($sql)) $message = "✅ Ordinance updated.";
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM ordinances WHERE id=$id");
    $message = "Ordinance deleted.";
}

$result = $conn->query("SELECT * FROM ordinances ORDER BY code");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ordinances</title>
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


    <main class="content ordinance-content">
        <h1>Manage Ordinances</h1>

        <?php if ($message): ?>
            <p class="alert success"><?= $message; ?></p>
        <?php endif; ?>

        <div class="card">
            <h2>Add New Ordinance</h2>
            <form method="POST" class="form-grid">
                <label>Code:</label>
                <input type="text" name="code" required>

                <label>Description:</label>
                <textarea name="description" required></textarea>

                <label>Fine Amount (₱):</label>
                <input type="number" name="fine_amount" step="0.01" required>

                <button type="submit" name="add" class="btn btn-add">Add Ordinance</button>
            </form>
        </div>

        <div class="card">
            <h2>Ordinance List</h2>
            <table class="ordinance-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Fine Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['code']; ?></td>
                        <td><?= $row['description']; ?></td>
                        <td>₱<?= number_format($row['fine_amount'], 2); ?></td>
                        <td>
                            <button class="btn btn-edit"
                                onclick="editRow(<?= $row['id']; ?>, '<?= addslashes($row['code']); ?>', '<?= addslashes($row['description']); ?>', <?= $row['fine_amount']; ?>)">
                                Edit
                            </button>

                            <a href="?delete=<?= $row['id']; ?>" onclick="return confirm('Delete?')">
                                <button class="btn btn-delete">Delete</button>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- EDIT FORM -->
        <div class="card" id="editForm" style="display:none;">
            <h2>Edit Ordinance</h2>
            <form method="POST" class="form-grid">
                <input type="hidden" id="edit_id" name="id">

                <label>Code:</label>
                <input type="text" id="edit_code" name="code" required>

                <label>Description:</label>
                <textarea id="edit_description" name="description" required></textarea>

                <label>Fine Amount (₱):</label>
                <input type="number" step="0.01" id="edit_fine_amount" name="fine_amount" required>

                <button type="submit" name="update" class="btn btn-edit">Update</button>
                <button type="button" class="btn btn-cancel" onclick="cancelEdit()">Cancel</button>
            </form>
        </div>

    </main>
</div>

<script>
function editRow(id, code, desc, amount) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_code').value = code;
    document.getElementById('edit_description').value = desc;
    document.getElementById('edit_fine_amount').value = amount;
    document.getElementById('editForm').style.display = 'block';
}
function cancelEdit() {
    document.getElementById('editForm').style.display = 'none';
}
</script>

</body>
</html>
