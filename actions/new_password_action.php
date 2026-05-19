<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /event-system/new_password.php');
    exit();
}

// Must have a verified OTP session
if (empty($_SESSION['otp_verified']) || empty($_SESSION['reset_email'])) {
    setFlash('error', 'Session expired. Please start over.');
    header('Location: /event-system/forgot_password.php');
    exit();
}

$email           = $_SESSION['reset_email'];
$password        = $_POST['password']         ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validate
$errors = [];
if (strlen($password) < 6)          $errors[] = 'Password must be at least 6 characters.';
if ($password !== $confirmPassword)  $errors[] = 'Passwords do not match.';

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: /event-system/new_password.php');
    exit();
}

$conn = getConnection();

// Update password
$hashed = password_hash($password, PASSWORD_DEFAULT);
$upd = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
if (!$upd) {
    error_log('new_password_action prepare (update): ' . $conn->error);
    $conn->close();
    setFlash('error', 'Could not update password. Please try again.');
    header('Location: /event-system/new_password.php');
    exit();
}
$upd->bind_param('ss', $hashed, $email);
$upd->execute();
$upd->close();

// Clean up reset record
$del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
if ($del) {
    $del->bind_param('s', $email);
    $del->execute();
    $del->close();
}
$conn->close();

// Clear reset session data
unset($_SESSION['reset_email'], $_SESSION['otp_verified']);

setFlash('success', 'Password reset successfully! Please sign in with your new password.');
header('Location: /event-system/login.php');
exit();