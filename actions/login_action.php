<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /event-system/login.php');
    exit();
}

$email       = trim($_POST['email']    ?? '');
$password    =      $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

if (empty($email) || empty($password)) {
    header('Location: /event-system/login.php?error=empty');
    exit();
}

$conn = getConnection();
$stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user || !password_verify($password, $user['password'])) {
    header('Location: /event-system/login.php?error=invalid');
    exit();
}

// Set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['name']    = $user['name'];
$_SESSION['email']   = $user['email'];
$_SESSION['role']    = $user['role'];

// Remember me 
if ($remember_me) {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    setcookie('remember_email', $user['email'], time() + (30 * 24 * 60 * 60), '/', '', $secure, true);
} else {
    setcookie('remember_email', '', time() - 3600, '/');
}

if ($user['role'] === 'admin') {
    header('Location: /event-system/admin/dashboard.php');
} else {
    header('Location: /event-system/dashboard.php');
}
exit();