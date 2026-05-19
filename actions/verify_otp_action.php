<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /event-system/verify_otp.php');
    exit();
}

if (empty($_SESSION['otp_email'])) {
    setFlash('error', 'Session expired. Please register again.');
    header('Location: /event-system/register.php');
    exit();
}

$email  = $_SESSION['otp_email'];
$action = $_POST['action'] ?? 'verify';

if ($action === 'resend') {
    $conn = getConnection();

    $stmt = $conn->prepare(
        "SELECT name, password_hash FROM otp_verifications WHERE email = ? ORDER BY created_at DESC LIMIT 1"
    );
    if (!$stmt) {
        $conn->close();
        setFlash('error', 'Something went wrong. Please try again.');
        header('Location: /event-system/verify_otp.php');
        exit();
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $conn->close();
        setFlash('error', 'Registration session not found. Please register again.');
        unset($_SESSION['otp_email'], $_SESSION['otp_name']);
        header('Location: /event-system/register.php');
        exit();
    }

    $newOtp    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $name      = $_SESSION['otp_name'] ?? $row['name'] ?? 'User';

    $upd = $conn->prepare(
        "UPDATE otp_verifications SET otp_code = ?, expires_at = ?, attempts = 0 WHERE email = ?"
    );
    if (!$upd) {
        $conn->close();
        setFlash('error', 'Could not regenerate code. Please try again.');
        header('Location: /event-system/verify_otp.php');
        exit();
    }
    $upd->bind_param('sss', $newOtp, $expiresAt, $email);
    $upd->execute();
    $upd->close();
    $conn->close();

    if (!sendOtpEmail($email, $name, $newOtp)) {
        setFlash('error', 'Could not send email. Please try again.');
        header('Location: /event-system/verify_otp.php');
        exit();
    }

    setFlash('success', 'A new verification code has been sent to ' . $email);
    header('Location: /event-system/verify_otp.php');
    exit();
}

$digits = '';
for ($i = 1; $i <= 6; $i++) {
    $digits .= preg_replace('/\D/', '', $_POST['otp_' . $i] ?? '');
}
$enteredOtp = substr($digits, 0, 6);

if (strlen($enteredOtp) !== 6) {
    setFlash('error', 'Please enter the complete 6-digit code.');
    header('Location: /event-system/verify_otp.php');
    exit();
}

$conn = getConnection();

$stmt = $conn->prepare(
    "SELECT id, name, password_hash, otp_code, expires_at, attempts
    FROM otp_verifications
    WHERE email = ?
    ORDER BY created_at DESC LIMIT 1"
);
if (!$stmt) {
    error_log('verify_otp_action prepare (fetch): ' . $conn->error);
    $conn->close();
    setFlash('error', 'Something went wrong. Please try again.');
    header('Location: /event-system/verify_otp.php');
    exit();
}
$stmt->bind_param('s', $email);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$record) {
    $conn->close();
    setFlash('error', 'No verification request found. Please register again.');
    unset($_SESSION['otp_email'], $_SESSION['otp_name']);
    header('Location: /event-system/register.php');
    exit();
}

// Check expiry
if (strtotime($record['expires_at']) < time()) {
    $del = $conn->prepare("DELETE FROM otp_verifications WHERE email = ?");
    $del->bind_param('s', $email);
    $del->execute();
    $del->close();
    $conn->close();
    setFlash('error', 'Your verification code has expired. Please register again.');
    unset($_SESSION['otp_email'], $_SESSION['otp_name']);
    header('Location: /event-system/register.php');
    exit();
}

// Check max attempts (5 tries)
if ($record['attempts'] >= 5) {
    $del = $conn->prepare("DELETE FROM otp_verifications WHERE email = ?");
    $del->bind_param('s', $email);
    $del->execute();
    $del->close();
    $conn->close();
    setFlash('error', 'Too many incorrect attempts. Please register again.');
    unset($_SESSION['otp_email'], $_SESSION['otp_name']);
    header('Location: /event-system/register.php');
    exit();
}

// Verify OTP 
if ($record['otp_code'] !== $enteredOtp) {
    $upd = $conn->prepare("UPDATE otp_verifications SET attempts = attempts + 1 WHERE id = ?");
    $upd->bind_param('i', $record['id']);
    $upd->execute();
    $upd->close();
    $conn->close();
    $remaining = 5 - ($record['attempts'] + 1);
    setFlash('error', "Incorrect code. {$remaining} attempt(s) remaining.");
    header('Location: /event-system/verify_otp.php');
    exit();
}

// Create the actual user account 
$name         = $record['name'];
$passwordHash = $record['password_hash'];

$ins = $conn->prepare(
    "INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())"
);
if (!$ins) {
    error_log('verify_otp_action prepare (insert user): ' . $conn->error);
    $conn->close();
    setFlash('error', 'Account creation failed. Please try again.');
    header('Location: /event-system/verify_otp.php');
    exit();
}
$ins->bind_param('sss', $name, $email, $passwordHash);

if (!$ins->execute()) {
    error_log('verify_otp_action insert user error: ' . $ins->error);
    $ins->close();
    $conn->close();
    setFlash('error', 'An account with this email already exists. Please log in.');
    unset($_SESSION['otp_email'], $_SESSION['otp_name']);
    header('Location: /event-system/login.php');
    exit();
}
$ins->close();

// Clean up the OTP record
$del = $conn->prepare("DELETE FROM otp_verifications WHERE email = ?");
$del->bind_param('s', $email);
$del->execute();
$del->close();
$conn->close();

$_SESSION['registered_email'] = $email;
unset($_SESSION['otp_email'], $_SESSION['otp_name']);

setFlash('success', 'Email verified! Your account has been created. Please sign in.');
header('Location: /event-system/login.php');
exit();