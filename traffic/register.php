<?php
include 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];  
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $license_plate = mysqli_real_escape_string($conn, $_POST['license_plate']);

    $check_sql = "SELECT id FROM users WHERE email='$email'";
    $check_result = $conn->query($check_sql);
    if ($check_result->num_rows > 0) {
        $error = "Email already registered.";
    } else {
        $sql = "INSERT INTO users (email, password, full_name, license_plate, role) VALUES ('$email', '$password', '$full_name', '$license_plate', 'user')";
        if ($conn->query($sql)) {
            $message = "Registration successful! <a href='login.php'>Login here</a>";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Traffic Violation System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>City Ordinance Traffic Violation System</h1>
    </header>
    <div class="container">
        <h2>User Registration</h2>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <?php if ($message) echo "<p class='success'>$message</p>"; ?>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>
            <label for="license_plate">License Plate (optional):</label>
            <input type="text" id="license_plate" name="license_plate">
            <input type="submit" value="Register">
        </form>
        <p>Already registered? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
