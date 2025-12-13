<?php
include 'config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['count'=>0]); exit(); }

$user_id = $_SESSION['user_id'];

if (isAdmin()) {
    $sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE role='admin' AND is_read=0";
} else {
    $sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id=$user_id AND role='user' AND is_read=0";
}

$result = $conn->query($sql);
$row = $result->fetch_assoc();
echo json_encode(['count'=>intval($row['unread_count'])]);
?>
