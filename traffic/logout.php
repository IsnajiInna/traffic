<?php
if (!isset($_GET['confirm'])) {
    echo "<script>
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php?confirm=1';
            } else {
                window.history.back();
            }
          </script>";
    exit();
}

session_start();
session_destroy();
header("Location: login.php");
exit();
?>
