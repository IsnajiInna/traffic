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
    if ($conn->query($sql)) $message = "Ordinance added.";
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $code = mysqli_real_escape_string($conn, $_POST['code']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $fine_amount = $_POST['fine_amount'];
    $sql = "UPDATE ordinances SET code='$code', description='$description', fine_amount=$fine_amount WHERE id=$id";
    if ($conn->query($sql)) $message = "Ordinance updated.";
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>City Ordinance Traffic Violation System</h1>
    </header>
    <div class="container">
        <h2>Manage Ordinances</h2>
        <div class="dashboard-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="violations.php">Violations</a>
            <a href="search.php">Search</a>
            <a href="reports.php">Reports</a>
            <a href="payments.php">Payments</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
        <?php if ($message) echo "<p class='success'>$message</p>"; ?>

        <h3>Add New Ordinance</h3>
        <form method="POST">
            <label for="code">Code:</label>
            <input type="text" id="code" name="code" required>
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>
            <label for="fine_amount">Fine Amount (₱):</label>
            <input type="number" step="" id="fine_amount" name="fine_amount" required>
            <input type="submit" name="add" value="Add Ordinance">
        </form>

        <h3>Ordinance List</h3>
        <table>
            <tr><th>Code</th><th>Description</th><th>Fine Amount</th><th>Actions</th></tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['code']; ?></td>
                    <td><?php echo $row['description']; ?></td>
                    <td>₱<?php echo number_format($row['fine_amount'], 2); ?></td>
                    <td>
                        <button onclick="editRow(<?php echo $row['id']; ?>, '<?php echo addslashes($row['code']); ?>', '<?php echo addslashes($row['description']); ?>', <?php echo $row['fine_amount']; ?>)">Edit</button>
                        <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete?')"><button class="delete">Delete</button></a>
                    </td>
                </tr>
            <?php } ?>
        </table>

      
        <div id="editForm" style="display:none;">
            <h3>Edit Ordinance</h3>
            <form method="POST">
                <input type="hidden" id="edit_id" name="id">
                <label for="edit_code">Code:</label>
                <input type="text" id="edit_code" name="code" required>
                <label for="edit_description">Description:</label>
                <textarea id="edit_description" name="description" required></textarea>
                <label for="edit_fine_amount">Fine Amount (₱):</label>
                <input type="number" step="0.01" id="edit_fine_amount" name="fine_amount" required>
                <input type="submit" name="update" value="Update">
                <button type="button" onclick="cancelEdit()">Cancel</button>
            </form>
        </div>
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