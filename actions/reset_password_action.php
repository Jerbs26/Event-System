<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /event-system/reset_password.php');
    exit();
}

if (empty($_SESSION['reset_email'])) {
    setFlash('error', 'Session expired. Please request a new reset code.');
    header('Location: /event-system/forgot_password.php');
    exit();
}

$email = $_SESSION['reset_email'];
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Collect OTP digits
$digits = '';
for ($i = 1; $i <= 6; $i++) {
    $digits .= preg_replace('/\D/', '', $_POST['otp_' . $i] ?? '');
}
$enteredOtp = substr($digits, 0, 6);

// Validate inputs
$errors = [];
if (strlen($enteredOtp) !== 6) $errors[] = 'Please enter the complete 6-digit code.';
if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: /event-system/reset_password.php');
    exit();
}

$conn = getConnection();

// Fetch reset record
$stmt = $conn->prepare("SELECT id, otp_code, expires_at, attempts FROM password_resets WHERE email = ? ORDER BY created_at DESC LIMIT 1");
if (!$stmt) {
    error_log('reset_password_action prepare (fetch): ' . $conn->error);
    $conn->close();
    setFlash('error', 'Something went wrong. Please try again.');
    header('Location: /event-system/reset_password.php');
    exit();
}
$stmt->bind_param('s', $email);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$record) {
    $conn->close();
    setFlash('error', 'No reset request found. Please try again.');
    unset($_SESSION['reset_email']);
    header('Location: /event-system/forgot_password.php');
    exit();
}

// Check expiry
if (strtotime($record['expires_at']) < time()) {
    $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $del->bind_param('s', $email);
    $del->execute();
    $del->close();
    $conn->close();
    setFlash('error', 'Your reset code has expired. Please request a new one.');
    unset($_SESSION['reset_email']);
    header('Location: /event-system/forgot_password.php');
    exit();
}

// Check max attempts
if ($record['attempts'] >= 5) {
    $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $del->bind_param('s', $email);
    $del->execute();
    $del->close();
    $conn->close();
    setFlash('error', 'Too many incorrect attempts. Please request a new reset code.');
    unset($_SESSION['reset_email']);
    header('Location: /event-system/forgot_password.php');
    exit();
}

// Verify OTP
if (!hash_equals($record['otp_code'], $enteredOtp)) {
    $upd = $conn->prepare("UPDATE password_resets SET attempts = attempts + 1 WHERE id = ?");
    $upd->bind_param('i', $record['id']);
    $upd->execute();
    $upd->close();
    $conn->close();
    $remaining = 5 - ($record['attempts'] + 1);
    setFlash('error', "Incorrect code. {$remaining} attempt(s) remaining.");
    header('Location: /event-system/reset_password.php');
    exit();
}

// OTP correct — update password
$hashed = password_hash($password, PASSWORD_DEFAULT);
$upd = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
if (!$upd) {
    error_log('reset_password_action prepare (update users): ' . $conn->error);
    $conn->close();
    setFlash('error', 'Could not update password. Please try again.');
    header('Location: /event-system/reset_password.php');
    exit();
}
$upd->bind_param('ss', $hashed, $email);
$upd->execute();
$upd->close();

// Clean up reset record
$del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$del->bind_param('s', $email);
$del->execute();
$del->close();
$conn->close();

unset($_SESSION['reset_email']);
setFlash('success', 'Password reset successfully! Please sign in with your new password.');
header('Location: /event-system/login.php');
exit();