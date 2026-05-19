<?php

require_once __DIR__ . '/../includes/auth.php';   // session helpers
require_once __DIR__ . '/../config/db.php';        // getConnection()
require_once __DIR__ . '/../includes/mailer.php';  // sendResetEmail()

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /event-system/forgot_password.php');
    exit();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Basic validation
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['fp_error'] = 'Please enter a valid email address.';
    header('Location: /event-system/forgot_password.php');
    exit();
}

$conn = getConnection();

// Check if email exists 
$stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    error_log('forgot_password_action prepare (users): ' . $conn->error);
    $conn->close();
    $_SESSION['fp_error'] = 'Something went wrong. Please try again.';
    header('Location: /event-system/forgot_password.php');
    exit();
}
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Generic message 
if (!$user) {
    $conn->close();
    $_SESSION['fp_info'] = 'If that email is registered, a reset code has been sent.';
    header('Location: /event-system/forgot_password.php');
    exit();
}

// Rate-limit: prevent spam 
$rate = $conn->prepare("SELECT created_at FROM password_resets WHERE email = ? ORDER BY created_at DESC LIMIT 1");
if (!$rate) {
    error_log('forgot_password_action prepare (rate): ' . $conn->error);
    $conn->close();
    $_SESSION['fp_error'] = 'Something went wrong. Please try again.';
    header('Location: /event-system/forgot_password.php');
    exit();
}
$rate->bind_param('s', $email);
$rate->execute();
$existing = $rate->get_result()->fetch_assoc();
$rate->close();

if ($existing && (time() - strtotime($existing['created_at'])) < 60) {
    $conn->close();
    $_SESSION['fp_error'] = 'Please wait at least 60 seconds before requesting a new code.';
    header('Location: /event-system/forgot_password.php');
    exit();
}

// Delete any existing reset record for this email
$del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
if (!$del) {
    error_log('forgot_password_action prepare (delete): ' . $conn->error);
    $conn->close();
    $_SESSION['fp_error'] = 'Something went wrong. Please try again.';
    header('Location: /event-system/forgot_password.php');
    exit();
}
$del->bind_param('s', $email);
$del->execute();
$del->close();

// Generate a 6-digit OTP
$otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Insert new OTP record 
$ins = $conn->prepare("INSERT INTO password_resets (email, otp_code, expires_at, created_at) VALUES (?, ?, ?, NOW())");
if (!$ins) {
    error_log('forgot_password_action prepare (insert): ' . $conn->error);
    $conn->close();
    $_SESSION['fp_error'] = 'Something went wrong. Please try again.';
    header('Location: /event-system/forgot_password.php');
    exit();
}
$ins->bind_param('sss', $email, $otp, $expiresAt);
if (!$ins->execute()) {
    error_log('forgot_password_action execute (insert): ' . $ins->error);
    $ins->close();
    $conn->close();
    $_SESSION['fp_error'] = 'Could not save reset code. Please try again.';
    header('Location: /event-system/forgot_password.php');
    exit();
}
$ins->close();
$conn->close();

// Send OTP via email —
$sent = sendResetEmail($email, $user['name'], $otp);
if (!$sent) {
    error_log('forgot_password_action: sendResetEmail() failed for ' . $email);
    $_SESSION['fp_error'] = 'Could not send email. Please check your mailer config or try again.';
    header('Location: /event-system/forgot_password.php');
    exit();
}

// Store email in session so reset page knows who is resetting
$_SESSION['reset_email'] = $email;

$_SESSION['fp_info'] = 'A 6-digit reset code has been sent to your email. It expires in 10 minutes.';
header('Location: /event-system/reset_password.php');
exit();