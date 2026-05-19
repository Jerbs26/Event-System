<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /event-system/register.php');
    exit();
}

$name            = sanitize($_POST['name']           ?? '');
$email           = sanitize($_POST['email']          ?? '');
$password        = $_POST['password']                 ?? '';
$confirmPassword = $_POST['confirm_password']         ?? '';

// Validation 
$errors = [];

if (strlen($name) < 2) {
    $errors[] = 'Full name must be at least 2 characters.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters.';
}
if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: /event-system/register.php');
    exit();
}

$conn = getConnection();

// Check if email already registered 
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    setFlash('error', 'An account with this email already exists. Please log in.');
    header('Location: /event-system/register.php');
    exit();
}
$stmt->close();

// Clean up expired/previous OTP attempts for this email 
$stmt = $conn->prepare("DELETE FROM otp_verifications WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->close();

// Generate OTP 
$otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
$hashed    = password_hash($password, PASSWORD_DEFAULT);

// Store pending registration 
$stmt = $conn->prepare(
    "INSERT INTO otp_verifications (email, name, password_hash, otp_code, expires_at)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssss", $email, $name, $hashed, $otp, $expiresAt);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    setFlash('error', 'Registration failed. Please try again later.');
    header('Location: /event-system/register.php');
    exit();
}
$stmt->close();
$conn->close();

// Send OTP email 
if (!sendOtpEmail($email, $name, $otp)) {
    setFlash('error', 'Could not send verification email. Please try again.');
    header('Location: /event-system/register.php');
    exit();
}

// Store email in session for the verify page 
$_SESSION['otp_email'] = $email;
$_SESSION['otp_name']  = $name;

setFlash('success', 'A 6-digit verification code has been sent to ' . $email);
header('Location: /event-system/verify_otp.php');
exit();