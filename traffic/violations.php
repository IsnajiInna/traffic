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

// --- HANDLE ADDING VIOLATIONS ---
if ((isset($_POST['add']) || isset($_POST['add_multiple'])) && isAdmin()) {
    $user_id_target = intval($_POST['user_id']);
    if ($user_id_target <= 0) {
        $message = "Error: No user selected.";
    } else {
        $added = 0;
        if (isset($_POST['add_multiple'])) {
            $ordinance_ids = $_POST['ordinance_id'];
            $violation_types = $_POST['violation_type'];
            $dates = $_POST['date'];
            $officers = $_POST['officer_name'];
            $fines = $_POST['fine_amount'];

            for ($i=0; $i<count($violation_types); $i++) {
                $ord_id = intval($ordinance_ids[$i]);
                $v_type = mysqli_real_escape_string($conn, $violation_types[$i]);
                $v_date = $dates[$i];
                $officer = mysqli_real_escape_string($conn, $officers[$i]);
                $fine = floatval($fines[$i]);

                $sql = "INSERT INTO violations (user_id, ordinance_id, violation_type, date, officer_name, fine_amount, status)
                        VALUES ($user_id_target, $ord_id, '$v_type', '$v_date', '$officer', $fine, 'Unpaid')";
                if ($conn->query($sql)) {
                    $added++;
                    $msg = "A new violation has been added: $v_type on $v_date. Fine: ₱$fine.";
                    $conn->query("INSERT INTO notifications (user_id, message, role, is_read, created_at)
                                  VALUES ($user_id_target, '" . $conn->real_escape_string($msg) . "', 'user', 0, NOW())");
                }
            }
            $message = "$added violation(s) added successfully.";
        } else {
            $violation_type = mysqli_real_escape_string($conn, $_POST['violation_type']);
            $ordinance_id = intval($_POST['ordinance_id']);
            $date = $_POST['date'];
            $officer_name = mysqli_real_escape_string($conn, $_POST['officer_name']);
            $fine_amount = floatval($_POST['fine_amount']);

            $sql = "INSERT INTO violations (user_id, ordinance_id, violation_type, date, officer_name, fine_amount, status)
                    VALUES ($user_id_target, $ordinance_id, '$violation_type', '$date', '$officer_name', $fine_amount, 'Unpaid')";
            if ($conn->query($sql)) {
                $message = "Violation added successfully.";
                $msg = "A new violation has been added: $violation_type on $date. Fine: ₱$fine_amount.";
                $conn->query("INSERT INTO notifications (user_id, message, role, is_read, created_at)
                              VALUES ($user_id_target, '" . $conn->real_escape_string($msg) . "', 'user', 0, NOW())");
            } else {
                $message = "Error: " . $conn->error;
            }
        }
    }
}

// --- UPDATE STATUS ---
if (isset($_POST['update']) && isAdmin()) {
    $id = intval($_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $sql = "UPDATE violations SET status='$status' WHERE id=$id";
    $conn->query($sql) ? $message = "Violation updated." : $message = "Error: " . $conn->error;
}

// --- DELETE VIOLATION ---
if (isset($_GET['delete']) && isAdmin()) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM violations WHERE id=$id");
    $message = "Violation deleted.";
}

// --- FETCH VIOLATIONS ---
$where = isAdmin() ? "" : "WHERE v.user_id=$user_id";
$sql = "SELECT v.*, u.full_name, u.license_plate, o.code, o.description
        FROM violations v
        JOIN users u ON v.user_id=u.id
        JOIN ordinances o ON v.ordinance_id=o.id
        $where ORDER BY v.date DESC";
$result = $conn->query($sql);

// --- FETCH USERS & ORDINANCES ---
if (isAdmin()) {
    $users_result = $conn->query("SELECT id, full_name, email FROM users WHERE role='user'");
    $ordinances_result = $conn->query("SELECT * FROM ordinances");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Violations</title>
<link rel="stylesheet" href="styles.css">
<style>
.search-box { margin-bottom: 15px; width: 300px; }
.search-input { width: 100%; padding: 8px; border: 1px solid #aaa; border-radius: 5px; }
.badge { background-color: red; color: white; padding: 2px 6px; border-radius: 50%; font-size: 12px; margin-left: 5px; }
</style>
</head>
<body>
<div class="sidebar">
<h2>Admin Panel</h2>
<a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>">Dashboard</a>
<a href="violations.php" class="<?= basename($_SERVER['PHP_SELF'])=='violations.php'?'active':'' ?>">Violations</a>
<a href="ordinances.php" class="<?= basename($_SERVER['PHP_SELF'])=='ordinances.php'?'active':'' ?>">Ordinances</a>
<a href="notification.php" class="<?= basename($_SERVER['PHP_SELF'])=='notification.php'?'active':'' ?>">Notification<span class="badge"></span></a>
<a href="search.php" class="<?= basename($_SERVER['PHP_SELF'])=='search.php'?'active':'' ?>">Search</a>
<a href="reports.php" class="<?= basename($_SERVER['PHP_SELF'])=='reports.php'?'active':'' ?>">Reports</a>
<a href="payments_admin.php" class="<?= basename($_SERVER['PHP_SELF'])=='payments_admin.php'?'active':'' ?>">Payments</a>
<a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
</div>

<div class="main-content">
<h1><?= isAdmin() ? 'Manage Violations' : 'Your Violations'; ?></h1>
<?php if(isset($message)) echo "<p class='success'>$message</p>"; ?>

<?php if(isAdmin()): ?>
<h2>Add Violations</h2>
<form method="POST" id="multiViolationForm">
<label>User:</label>
<input type="text" id="userSearch" placeholder="Type user name or license plate" required>
<input type="hidden" name="user_id" id="user_id">
<div id="userResults"></div>

<h3>Violation List</h3>
<table id="violationRows">
<tr>
<th>Ordinance</th><th>Type</th><th>Date</th><th>Officer</th><th>Fine</th><th></th>
</tr>
<tr>
<td><select name="ordinance_id[]">
<?php
$ordinances_result->data_seek(0);
while($o=$ordinances_result->fetch_assoc()){
    echo "<option value='{$o['id']}'>{$o['code']} - {$o['description']}</option>";
}
?>
</select></td>
<td><input type="text" name="violation_type[]" required></td>
<td><input type="date" name="date[]" required></td>
<td><input type="text" name="officer_name[]" required></td>
<td><input type="number" step="0.01" name="fine_amount[]" required></td>
<td><button type="button" onclick="removeRow(this)">X</button></td>
</tr>
</table>
<button type="button" onclick="addRow()">+ Add More</button><br><br>
<button name="add_multiple" class="btn">Submit Violations</button>
</form>
<?php endif; ?>

<h2>Violation Records</h2>
<div class="search-box"><input type="text" class="search-input" placeholder="Search..." onkeyup="filterTable()"></div>
<table id="violationTable">
<tr>
<th>ID</th><th>Name</th><th>Plate</th><th>Ordinance</th><th>Type</th><th>Date</th><th>Fine</th><th>Status</th>
<?php if(isAdmin()) echo "<th>Actions</th>"; ?>
</tr>
<?php
if($result && $result->num_rows>0){
while($row=$result->fetch_assoc()){
    echo "<tr>
    <td>{$row['id']}</td>
    <td>{$row['full_name']}</td>
    <td>{$row['license_plate']}</td>
    <td>{$row['code']}</td>
    <td>{$row['violation_type']}</td>
    <td>{$row['date']}</td>
    <td>₱".number_format($row['fine_amount'],2)."</td>
    <td>{$row['status']}</td>";
    if(isAdmin()){
        echo "<td><a href='?edit={$row['id']}'>Edit</a> | <a href='?delete={$row['id']}' onclick=\"return confirm('Delete?')\">Delete</a></td>";
    }
    echo "</tr>";
}
}else{
    echo "<tr><td colspan='9'>No Data</td></tr>";
}
?>
</table>
</div>

<script>
// Add/remove rows
function addRow(){
    const table=document.getElementById('violationRows');
    const row=table.insertRow(-1);
    row.innerHTML=`<td><select name="ordinance_id[]">
<?php $ordinances_result->data_seek(0); while($o=$ordinances_result->fetch_assoc()){ echo "<option value='{$o['id']}'>{$o['code']} - {$o['description']}</option>"; } ?>
</select></td>
<td><input type="text" name="violation_type[]" required></td>
<td><input type="date" name="date[]" required></td>
<td><input type="text" name="officer_name[]" required></td>
<td><input type="number" step="0.01" name="fine_amount[]" required></td>
<td><button type="button" onclick="removeRow(this)">X</button></td>`;
}
function removeRow(btn){btn.parentNode.parentNode.remove();}

// Live search for user
document.getElementById('userSearch').addEventListener('keyup', function(){
    let text=this.value;
    if(text.length<2){document.getElementById('userResults').style.display="none"; return;}
    fetch('search_user.php?q='+text).then(res=>res.json()).then(data=>{
        let box=document.getElementById('userResults'); box.innerHTML="";
        if(data.length===0){box.style.display="none"; return;}
        data.forEach(user=>{
            let div=document.createElement('div');
            div.innerText=user.full_name+' ('+user.license_plate+')';
            div.style.padding='8px'; div.style.cursor='pointer';
            div.onclick=()=>{document.getElementById('userSearch').value=user.full_name; document.getElementById('user_id').value=user.id; box.style.display='none';};
            box.appendChild(div);
        });
        box.style.display='block';
    });
});

// Validate form
document.getElementById("multiViolationForm").addEventListener("submit",function(e){
    if(document.getElementById('user_id').value===""){
        alert("Please pick a user from search results."); e.preventDefault();
    }
});

// Fetch notification badge
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
