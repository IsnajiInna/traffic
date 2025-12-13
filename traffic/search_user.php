<?php
include 'config.php';

$q = $_GET['q'];
$q = mysqli_real_escape_string($conn, $q);

$sql = "SELECT id, full_name, license_plate FROM users 
        WHERE role='user' AND (full_name LIKE '%$q%' OR license_plate LIKE '%$q%') LIMIT 10";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
