<?php
include('db.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Sanitize email input
    $password = $_POST['password'];

    // Check if user exists and password matches directly
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password); // Bind both email and plain password
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id);
        $stmt->fetch();

        // Start session and store user ID
        $_SESSION['user_id'] = $id;
        header("Location: dashboard.php"); // Redirect to dashboard
        exit();
    } else {
        echo "Invalid login details!";
    }

    $stmt->close();
    $conn->close();
}
?>