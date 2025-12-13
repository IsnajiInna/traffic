<?php
include 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, password, role, full_name FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['password'] === $password) {
            session_start();
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['full_name'] = $row['full_name'];
            header("Location: dashboard.php");
            exit();
        }
    }
    $error = "Invalid email or password!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

   <style>
    body {
        margin: 0;
        height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        font-family: Arial, sans-serif;
        background: #f0f2f5;
    }

    header {
        position: absolute;
        top: 0;
        width: 100%;
        text-align: center;
        background: #2c3e50;
        color: #fff;
        padding: 15px 0;
        font-size: 24px;
        font-weight: bold;
    }

    .login-box {
        width: 350px;
        background: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
        text-align: center;
    }

    .login-box label {
        display: block;
        margin: 8px 0 4px;
        color: #333;
        text-align: left;
    }

    .login-box input[type="email"],
    .login-box input[type="password"] {
        width: 90%;
        padding: 10px;
        border: 1px solid #bbb;
        border-radius: 5px;
    }

    .login-box input[type="submit"] {
        width: 60%;
        margin-top: 12px;
        padding: 10px;
        background: #2c3e50;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
    }

    .login-box input[type="submit"]:hover {
        background: #1a242f;
    }

    .error {
        background: #ffdddd;
        color: #d00000;
        padding: 8px;
        margin-bottom: 10px;
        border-left: 4px solid #d00000;
        border-radius: 4px;
    }
</style>


</head>
<body>

<header>City Ordinance Traffic Violation System</header>

<div class="login-box">
    <h1>Login</h1>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <input type="submit" value="Login">
    </form>

    <p>New user? <a href="register.php">Register</a></p>
</div>

</body>
</html>
