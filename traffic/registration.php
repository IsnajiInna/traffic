<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $license_plate = mysqli_real_escape_string($conn, $_POST['license_plate']);

    $sql = "INSERT INTO users (email, password, full_name, license_plate, role) VALUES ('$email', '$password', '$full_name', '$license_plate', 'user')";
    if ($conn->query($sql)) {
        $message = "Registration successful! <a href='login.php'>Login here</a>";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>User Registration</h1>
        <?php if (isset($message)) echo "<p class='success'>$message</p>"; ?>
        <form method="POST">
            <label>Email:</label><input type="email" name="email" required>
            <label>Password:</label><input type="password" name="password" required>
            <label>Full Name:</label><input type="text" name="full_name" required>
            <label>License Plate (optional):</label><input type="text" name="license_plate">
            <input type="submit" value="Register">
        </form>
        <p>Already registered? <a href="login.php">Login</a></p>
    </div>
</body>
</html>