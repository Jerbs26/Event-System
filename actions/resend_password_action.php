<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /event-system/reset_password.php');
    exit();
}

if (empty($_SESSION['reset_email'])) {
    setFlash('error', 'Session expired. Please request a new reset code.');
    header('Location: /event-system/forgot_password.php');
    exit();
}

// Email comes from session only — never from user input
$email = $_SESSION['reset_email'];

$conn = getConnection();

// Verify user actually exists and get name for email greeting
$stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    error_log('resend_reset_action prepare (users): ' . $conn->error);
    $conn->close();
    setFlash('error', 'Something went wrong. Please try again.');
    header('Location: /event-system/reset_password.php');
    exit();
}
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $conn->close();
    setFlash('error', 'Account not found. Please register first.');
    unset($_SESSION['reset_email']);
    header('Location: /event-system/forgot_password.php');
    exit();
}

$rate = $conn->prepare("SELECT created_at FROM password_resets WHERE email = ? ORDER BY created_at DESC LIMIT 1");
if (!$rate) {
    error_log('resend_reset_action prepare (rate): ' . $conn->error);
    $conn->close();
    setFlash('error', 'Something went wrong. Please try again.');
    header('Location: /event-system/reset_password.php');
    exit();
}
$rate->bind_param('s', $email);
$rate->execute();
$existing = $rate->get_result()->fetch_assoc();
$rate->close();

if ($existing && (time() - strtotime($existing['created_at'])) < 60) {
    $conn->close();
    setFlash('error', 'Please wait at least 60 seconds before requesting a new code.');
    header('Location: /event-system/reset_password.php');
    exit();
}

// Delete old tokens
$del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
if (!$del) {
    error_log('resend_reset_action prepare (delete): ' . $conn->error);
    $conn->close();
    setFlash('error', 'Something went wrong. Please try again.');
    header('Location: /event-system/reset_password.php');
    exit();
}
$del->bind_param('s', $email);
$del->execute();
$del->close();

// Generate new OTP
$otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Insert — column is otp_code (matches reset_password_action.php)
$ins = $conn->prepare("INSERT INTO password_resets (email, otp_code, expires_at, created_at) VALUES (?, ?, ?, NOW())");
if (!$ins) {
    error_log('resend_reset_action prepare (insert): ' . $conn->error);
    $conn->close();
    setFlash('error', 'Could not generate new code. Please try again.');
    header('Location: /event-system/reset_password.php');
    exit();
}
$ins->bind_param('sss', $email, $otp, $expiresAt);
if (!$ins->execute()) {
    error_log('resend_reset_action execute (insert): ' . $ins->error);
    $ins->close();
    $conn->close();
    setFlash('error', 'Could not generate new code. Please try again.');
    header('Location: /event-system/reset_password.php');
    exit();
}
$ins->close();
$conn->close();

// Send email
if (sendResetEmail($email, $user['name'], $otp)) {
    setFlash('success', 'A new reset code has been sent to your email.');
} else {
    setFlash('error', 'Could not send email. Please try again.');
}

header('Location: /event-system/reset_password.php');
exit();